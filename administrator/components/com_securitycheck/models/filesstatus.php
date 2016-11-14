<?php
/**
* Modelo FilesStatus para el Componente Securitycheck
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
jimport( 'joomla.filesystem.file');

/**
* Modelo Filemanager
*/
class SecuritychecksModelFilesStatus extends JModelLegacy
{

/** @var object Pagination */
var $_pagination = null;

/** @var int Total number of files of Pagination */
var $total = 0;

/** @var array The files to process */
private $Stack = array();

/** @var int Total numbers of file/folders in this site */
public $files_scanned = 0;

/** @var int Numbers of files/folders with  incorrect permissions*/
public $files_with_incorrect_permissions = 0;

/** @var string Path to the folder where scans will be stored */
private $folder_path = '';

/** @var string 10 chars strings to add to the filemanager's name */
private $filemanager_name = '';


function __construct()
{
	parent::__construct();
	
	// Establecemos la ruta donde se almacenar�n los escaneos
	$this->folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheck'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
	
	// Obtenemos el nombre de los escaneos anteriores
	$db = $this->getDbo();
	$query = $db->getQuery(true)
		->select(array($db->quoteName('storage_value')))
		->from($db->quoteName('#__securitycheck_storage'))
		->where($db->quoteName('storage_key').' = '.$db->quote('filemanager_resume'));
	$db->setQuery($query);
	$stack = $db->loadResult();	
	$stack = json_decode($stack, true);
	
	if(!empty($stack)) {
		$this->filemanager_name = $stack['filename'];
	}
	
	// Establecemos el tama�o m�ximo de memoria que el script puede consumir
	$params = JComponentHelper::getParams('com_securitycheck');
	$memory_limit = $params->get('memory_limit','128M');
	ini_set('memory_limit',$memory_limit);

	$mainframe = JFactory::getApplication();
 
	// Obtenemos las variables de paginaci�n de la petici�n
	$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
	$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

	// En el caso de que los l�mites hayan cambiado, los volvemos a ajustar
	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

	/* Limitamos a 100 el nmero de archivos mostrados para evitar que el array desborde la memoria mxima establecida por PHP */
	if ( $limit == 0 ){
		$this->setState('limit', 100);
		$this->setState('showall', 1);
	} else {
		$this->setState('limit', $limit);
	}
	$this->setState('limitstart', $limitstart);	
}

/* Funci�n que obtiene un array con los datos que ser�n mostrados en la opci�n 'filestatus' */
function loadStack($opcion,$field)
{
	(int) $lower_limit = 0; 
	
	// Establecemos el tama�o m�ximo de memoria que el script puede consumir
	$params = JComponentHelper::getParams('com_securitycheck');
	$memory_limit = $params->get('memory_limit','128M');
	ini_set('memory_limit',$memory_limit);
	
	$db = $this->getDbo();
	
	switch ($opcion) {
		case "permissions":
			// Leemos el contenido del fichero
			if ( JFile::exists($this->folder_path.$this->filemanager_name) ) {
				$stack = JFile::read($this->folder_path.$this->filemanager_name);
				// Eliminamos la parte del fichero que evita su lectura al acceder directamente
				$stack = str_replace("#<?php die('Forbidden.'); ?>",'',$stack);
			}
			
			if(empty($stack)) {
				$this->Stack = array();
				return;
			}
			break;
		case "filemanager_resume":
			$query = $db->getQuery(true)
				->select(array($db->quoteName('storage_value')))
				->from($db->quoteName('#__securitycheck_storage'))
				->where($db->quoteName('storage_key').' = '.$db->quote('filemanager_resume'));
			$db->setQuery($query);
			$stack = $db->loadResult();
			
			if(empty($stack)) {
				$this->files_scanned = 0;
				$this->files_with_incorrect_permissions = 0;
				return;
			}
			break;
	
	}

	$stack = json_decode($stack, true);
	
	/* Obtenemos el n�mero de registros del array que hemos de mostrar. Si el l�mite superior es '0', entonces devolvemos todo el array */
	$upper_limit = $this->getState('limitstart');
	$lower_limit = $this->getState('limit');
		
	/* Obtenemos los valores de los filtros */
	$filter_permissions_status = $this->state->get('filter.filemanager_permissions_status');
	$filter_kind = $this->state->get('filter.filemanager_kind');
	$search = htmlentities($this->state->get('filter.search'));
	
	switch ($field) {
		case "file_manager":
			$filtered_array = array();
			/* Si el campo 'search' no est� vac�o, buscamos en todos los campos del array */			
			if (!empty($search) ) {
				$filtered_array = array_values(array_filter($stack['files_folders'], function ($element) use ($filter_permissions_status,$filter_kind,$search) { return ( ($element['safe'] == $filter_permissions_status) && ($element['kind'] == $filter_kind) && ( (strstr($element['path'],$search)) || (strstr($element['name'],$search)) || (strstr($element['extension'],$search)) || (strstr($element['size'],$search)) || (strstr($element['last_modified'],$search)) || (strstr($element['permissions'],$search)) ) );} ));
			} else {
				$filtered_array = array_values(array_filter($stack['files_folders'], function ($element) use ($filter_permissions_status,$filter_kind) { return ( ($element['safe'] == $filter_permissions_status) && ($element['kind'] == $filter_kind) );} ));
			}
			$this->total = count($filtered_array);
			/* Cortamos el array para mostrar s�lo los valores mostrados por la paginaci�n */
			$this->Stack = array_splice($filtered_array, $upper_limit, $lower_limit);
			return ($this->Stack);
		case "files_scanned":
			$this->files_scanned = $stack['files_scanned'];
			return ($this->files_scanned);
		case "files_with_incorrect_permissions":
			$this->files_with_incorrect_permissions = $stack['files_with_incorrect_permissions'];
			return ($this->files_with_incorrect_permissions);
	}

}

protected function populateState()
{
	// Inicializamos las variables
	$app		= JFactory::getApplication();

	$search = $app->getUserStateFromRequest('filter.search', 'filter_search');
	$this->setState('filter.search', $search);
	$filemanager_kind = $app->getUserStateFromRequest('filter.filemanager_kind', 'filter_filemanager_kind');
	$this->setState('filter.filemanager_kind', $filemanager_kind);
	$filemanager_permissions_status = $app->getUserStateFromRequest('filter.filemanager_permissions_status', 'filter_filemanager_permissions_status');
	$this->setState('filter.filemanager_permissions_status', $filemanager_permissions_status);
	$filemanager_permissions_status = $app->getUserStateFromRequest('filter.filemanager_permissions_status', 'filter_filemanager_permissions_status');
		
						
	parent::populateState();
}

/* Funci�n que obtiene el valor del campo 'last_check' de la tabla '#__securitycheck_file_manager' */
function get_last_timestamp()
{
	// Creamos el nuevo objeto query
	$db = $this->getDbo();
		
	// Construimos la consulta...
	$query = ' SELECT last_check FROM #__securitycheck_file_manager WHERE id=1 ';

	// ... y la lanzamos
	$db->setQuery( $query );	
	$result = $db->loadResult();
	
	// Devolvemos el resultado
	return $result;	
}

/* Funci�n para la paginaci�n */
function getPagination()
{
// Cargamos el contenido si es que no existe todav�a
if (empty($this->_pagination)) {
	jimport('joomla.html.pagination');
	$this->_pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit') );
}
return $this->_pagination;
}

/* Funci�n para establecer el valor de un campo de la tabla '#_securitycheck_file_manager' */
function set_campo_filemanager($campo,$valor)
{
	// Creamos el nuevo objeto query
	$db = $this->getDbo();
	$query = $db->getQuery(true);
	
	// Sanitizamos las entradas
	$campo_sanitizado = $db->escape($campo);
	$valor_sanitizado = $db->Quote($db->escape($valor));

	// Construimos la consulta...
	$query->update('#__securitycheck_file_manager');
	$query->set($campo_sanitizado .'=' .$valor_sanitizado);
	$query->where('id=1');

	// ... y la lanzamos
	$db->setQuery( $query );
	$db->execute();
}

/* Funci�n para obtener el valor de un campo de la tabla '#_securitycheck_file_manager' */
function get_campo_filemanager($campo)
{
	// Creamos el nuevo objeto query
	$db = $this->getDbo();
	$query = $db->getQuery(true);
	
	// Sanitizamos las entradas
	$campo_sanitizado = $db->Quote($db->escape($campo));
	
	// Construimos la consulta...
	$query->select($campo);
	$query->from('#__securitycheck_file_manager');
	$query->where('id=1');
	
	// ... y la lanzamos
	$db->setQuery( $query );
	$result = $db->loadResult();
	
	// Devolvemos el resultado
	return $result;	
}

}