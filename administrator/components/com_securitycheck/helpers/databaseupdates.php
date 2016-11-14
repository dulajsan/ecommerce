<?php
/**
* Modelo Securitycheckpros para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo est� inclu�do en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );
jimport( 'joomla.version' );
jimport( 'joomla.access.rule' );
jimport( 'joomla.application.component.helper' );
jimport('joomla.updater.update' );
jimport('joomla.installer.helper' );
jimport('joomla.installer.installer' );
jimport( 'joomla.application.component.controller' );

// Load library
require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheck'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'loader.php');

/**
* Modelo Securitycheck
*/
class SecuritychecksModelDatabaseUpdates extends SecuritycheckModel
{

// Variable que contendr� el tipo de componente de securitycheck instalado 
private $securitycheck_type = 'Not_defined';
// Variable que almacena la tabla en la que insertar las nuevas vulnerabilidades
private $vuln_table = 'Not_defined';
// Variable que contiene la versi�n de la bbdd local (contendr� el mayor valor del campo 'dbversion' del archivo xml le�do)
private $higher_database_version = '0.0.0';


function __construct()
{
	parent::__construct();

}

/* Chequea qu� tipo de componente de securitycheck est� instalado */
function check_securitycheck_type() {

	$db = JFactory::getDbo();
	
	// Consultamos si est� instalada la versi�n Pro
	$query = $db->getQuery(true)
		->select('COUNT(*)')
		->from($db->quoteName('#__extensions'))
		->where($db->quoteName('name').' = '.$db->quote('System - Securitycheck pro'));
	$db->setQuery($query);
	$result = $db->loadResult();
	
	// La extensi�n Pro est� instalada; actualizamos la variable $securitycheck_type y $table
	if ( $result == '1' ) {
		$this->securitycheck_type = 'com_securitycheckpro';
		$this->vuln_table = '#__securitycheckpro_db';
	} else {
		
		// Consultamos si est� instalada la versi�n free
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('name').' = '.$db->quote('System - Securitycheck'));
		$db->setQuery($query);
		$result = $db->loadResult();
		
		// La extensi�n free est� instalada; actualizamos la variable $securitycheck_type y $table
		if ( $result == '1' ) {
			$this->securitycheck_type = 'com_securitycheck';
			$this->vuln_table = '#__securitycheck_db';
		}	
	}
}

/* Funci�n que a�ade vulnerabilidades a la bbdd del componente securitycheck */
function add_vuln($array_complete,$local_database_version) {

	// La versi�n mayor de la bbdd corresponder�, al principio, a la almacenada.
	$this->higher_database_version = $this->get_database_version();
	
	// Comprobamos si hemos de insertar cada vulnerabilidad
	foreach ($array_complete as $vulnerability) {
		/* Consultamos la rama para la que es v�lida la vulnerabilidad. Para ello dividimos los strings en el formato array[0]=3, array[1]=0... As�, el primer valor contendr�
		la rama para la que es v�lida la vulnerabilidad y la rama de joomla instalada */
		$vulnerabillity_branch = explode(".",$vulnerability['jversion']);
		$local_joomla_branch = explode(".",JVERSION);			
		
		// La versi�n de la vulnerabilidad debe ser mayor que la de la bbdd local para almacenarla
		if ( version_compare($vulnerability['dbversion'],$local_database_version,'gt') ) {	
			// Actualizamos la variable que contiene la mayor versi�n de la bbdd le�da del xml. Este valor se almacenar� luego en la bbdd local.
			$this->higher_database_version = $vulnerability['dbversion'];
			// M�todo para insertar una vulnerabilidad
			$key_exists = array_key_exists("method",$vulnerability);
			if ( ( $key_exists && $vulnerability['method'] == 'add' ) || (!$key_exists) ) {
				// La vulnerabilidad debe corresponder con la rama de Joomla local
				if ( $vulnerabillity_branch[0] == $local_joomla_branch[0] ) {
					// Rellenamos el objeto que vamos a insertar en la tabla '#__securitycheck(pro)_db', seg�n la opci�n instalada
					if ( $this->securitycheck_type == 'com_securitycheckpro' ) {				
						$nueva_vulnerabilidad = (object) array(
							'Product' => $vulnerability['product'],
							'vuln_type' => $vulnerability['type'],
							'Vulnerableversion' => $vulnerability['vulnerableversion'],
							'modvulnversion' => $vulnerability['modvulnversion'],
							'Joomlaversion' => $vulnerability['joomlaversion'],
							'modvulnjoomla' => $vulnerability['modvulnjoomla'],
							'description' => $vulnerability['description'],
							'vuln_class' => $vulnerability['class'],
							'published' => $vulnerability['published'],
							'vulnerable' => $vulnerability['vulnerable'],
							'solution_type' => $vulnerability['solution_type'],
							'solution' => $vulnerability['solution'],
						);
					} else if ( $this->securitycheck_type == 'com_securitycheck' ) {
						$nueva_vulnerabilidad = (object) array(
							'Product' => $vulnerability['product'],
							'Type' => $vulnerability['type'],
							'Vulnerableversion' => $vulnerability['vulnerableversion'],
							'modvulnversion' => $vulnerability['modvulnversion'],
							'Joomlaversion' => $vulnerability['joomlaversion'],
							'modvulnjoomla' => $vulnerability['modvulnjoomla'],
						);
					}
					
					$insert_result = JFactory::getDbo()->insertObject($this->vuln_table, $nueva_vulnerabilidad, 'id');
				}	
			} else if ( ($key_exists) && ($vulnerability['method'] == 'delete') ) {
			// M�todo para eliminar una vulnerabilidad
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				
				$conditions = array(
					$db->quoteName('Product') . ' = ' . $db->quote($vulnerability['product']),
					$db->quoteName('published') . ' = ' . $db->quote($vulnerability['published'])
				);
				
				$query->delete($db->quoteName($this->vuln_table));
				$query->where($conditions);
				
				$db->setQuery($query);
				$delete_result = $db->execute();
			}
		}
	}

}

/* Funci�n que devuelve la hora y fecha actuales */
public function currentDateTime_func() {
    return (date('Y-m-d H:i:s'));
}

/* Devuelve la versi�n de la bbdd local */
function get_database_version() {
	
	$db = JFactory::getDbo();
	
	// Consultamos la �ltima comprobaci�n
	$query = $db->getQuery(true)
		->select($db->quoteName('version'))
		->from($db->quoteName('#__securitycheckpro_update_database'));
	$db->setQuery($query);
	$version = $db->loadResult();
	
	return $version;
}

/* Chequea la �ltima vez que se lanz� una comprobaci�n de nuevas versiones */
function last_check() {
	
	// Inicializamos las variables
	$last_check = null;

	$db = JFactory::getDbo();
	
	// Consultamos la �ltima comprobaci�n
	$query = $db->getQuery(true)
		->select($db->quoteName('last_check'))
		->from($db->quoteName('#__securitycheckpro_update_database'));
	$db->setQuery($query);
	$task_time = $db->loadResult();
	
	// Si el campo no esta vac�o, devolvemos la hora/d�a actual formateada
	if( (isset($task_time)) && (!empty($task_time)) ) {
		$last_check = new DateTime(date('Y-m-d H:i:s',strtotime($task_time)));
	} 
	
	return $last_check;
}

/* Funci�n que realiza todo el proceso de comprobaci�n de nuevas vulnerabilidades */
function tarea_comprobacion() {
		
	// Inicializamos las variables
	$result = true;
	$downloadid = null;
	
	// Chequeamos el tipo de componente instalado
	$this->check_securitycheck_type();
	
	if ( $this->securitycheck_type == 'Not_defined' ) {
		// No hay ninguna versi�n de Securitycheck instalada!
		$result = false;
	} else {
	
		// Buscamos el Download ID 
		$plugin = JPluginHelper::getPlugin('system', 'securitycheckpro_update_database');
		if ( !empty($plugin) ) {
			$params = new JRegistry($plugin->params);
			$downloadid = $params->get('downloadid');
		}
		
		// Si el 'Download ID' est� vac�o, escribimos una entrada en el campo 'message' y no realizamos ninguna acci�n
		if ( empty($downloadid) ) {
			$this->set_campo_bbdd('message', 'COM_SECURITYCHECKPRO_UPDATE_DATABASE_DOWNLOAD_ID_EMPTY');
			$result = false;
		} else {
			
			// Url que contendr� el fichero xml (debe contener el Download ID del usuario para poder acceder a ella)
			$xmlfile = "https://securitycheck.protegetuordenador.com/index.php/downloads/securitycheck-pro-database-updates-xml/securitycheck-pro-database-updates-xml-1-0-0/databases-xml?dlid=" . $downloadid;
			
			// Array que contendr� todo el archivo xml 
			$array_complete = array();
			
			// Leemos el contenido del archivo xml (si existe la funci�n curl_init)
			if ( function_exists('curl_init') ) {
				$ch = curl_init($xmlfile);
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				curl_setopt($ch, CURLOPT_FAILONERROR, true);				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				
				$xmlresponse = curl_exec($ch);				
				$xml=simplexml_load_string($xmlresponse);				
			} else {
				JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_CURL_NOT_DEFINED'));
			}
			
			// Si hay alg�n fallo a la hora de leer el fichero lo intentamos con otro m�todo
			if ( empty($xml) ) {				
				$xml = JFactory::getXML($xmlfile,true);
			}
			
			// Comprobamos que hemos leido el archivo xml (esta variable ser� FALSE, por ejemplo, si no puede conectar con el servidor)
			if ( $xml ) {
				// Obtenemos todos los nodos hijos del archivo xml
				$children  = $xml->children();
					
				foreach ( $children as $child) {
					
					// Inicializamos el array de elementos de cada vulnerabilidad
					$element = array();
					
					foreach ($child as $key => $value) {
						
						// Para cada elemento, convertimos el par clave - valor en un string para poder manejarlo
						(string) $valores = $key . "#" . $value;
						$valores = explode("#",$valores);
									
						// Guardamos los elementos en el array , de tal forma que cada array tendr� los conjuntos clave -valor de cada vulnerabilidad
						$element[$valores[0]] = $valores[1];					
						
					}		
					// Guardamos todo el contenido del array en el array global
					array_push($array_complete,$element);	
				}
				
				//Extraemos la versi�n de la bbdd local
				$local_database_version = $this->get_database_version();
				
				// A�adimos las nuevas vulnerabilidades a la BBDD
				$this->add_vuln($array_complete,$local_database_version);	
			} else {
				$result = false;
			}
			
			// Si el proceso ha sido correcto, actualizamos la bbdd
			if ( $result ) {
				// Actualizamos la fecha de la �ltima comprobaci�n y la versi�n de la bbdd local
				$this->set_campo_bbdd('last_check',date('Y-m-d H:i:s'));
				$this->set_campo_bbdd('version',$this->higher_database_version);
				$this->set_campo_bbdd('message','PLG_SECURITYCHECKPRO_UPDATE_DATABASE_DATABASE_UPDATED');			
			}
		}
	}	
}

/* Funci�n que actualiza un campo de la bbdd '#_securitycheckpro_update_database' con el valor pasado como argumento */
function set_campo_bbdd($campo,$valor)
{
	// Creamos el nuevo objeto query
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	
	// Sanitizamos las entradas
	$campo_sanitizado = $db->escape($campo);
	$valor_sanitizado = $db->Quote($db->escape($valor));

	// Construimos la consulta...
	$query->update('#__securitycheckpro_update_database');
	$query->set($campo_sanitizado .'=' .$valor_sanitizado);
	$query->where('id=1');

	// ... y la lanzamos
	$db->setQuery( $query );
	$db->execute();
}

	
function check_for_updates(){
	// Inicializamos las variables
	$interval = 0;
	
	// �ltimo chequeo realizado
	$last_check = $this->last_check();
	// Si no hay consultas previas, establecemos el intervalo a '2' para lanzar una.
	if( (!isset($last_check)) || (empty($last_check)) ) {
		$interval = 20;
	} else {
		$now = new DateTime(date('Y-m-d',strtotime($this->currentDateTime_func())));
		// Extraemos las horas que han pasado desde el �ltimo chequeo
		$diff = $now->diff($last_check);	
		(int) $hours = $diff->h;
		$interval = $hours + ($diff->days*24);		
	}
		
	if ( $interval > 12 ) {
		// Comprobamos si existen nuevas actualizaciones
		$this->tarea_comprobacion();		
	}
			
}

/* Funci�n para determinar si el plugin pasado como argumento ('1' -> Securitycheck Pro, '2' -> Securitycheck Pro Cron, '3' -> Securitycheck Pro Update Database) est� habilitado o deshabilitado. Tambi�n determina si el plugin Securitycheck Pro Update Database (opci�n 4)  est� instalado */
function PluginStatus($opcion) {
		
	$db = JFactory::getDBO();
	if ( $opcion == 1 ) {
		$query = 'SELECT enabled FROM #__extensions WHERE name="System - Securitycheck Pro"';
	} else if ( $opcion == 2 ) {
		$query = 'SELECT enabled FROM #__extensions WHERE name="System - Securitycheck Pro Cron"';
	} else if ( $opcion == 3 ) {
		$query = 'SELECT enabled FROM #__extensions WHERE name="System - Securitycheck Pro Update Database"';
	} else if ( $opcion == 4 ) {
		$query = 'SELECT COUNT(*) FROM #__extensions WHERE name="System - Securitycheck Pro Update Database"';
	}
	
	$db->setQuery( $query );
	$db->execute();
	$enabled = $db->loadResult();
	
	return $enabled;
}


}