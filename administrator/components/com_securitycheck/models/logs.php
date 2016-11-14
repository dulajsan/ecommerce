<?php
/**
* Modelo Logs para el Componente Securitycheck
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo est� inclu�do en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );
jimport( 'joomla.access.rule' );
/**
* Modelo Vulninfo
*/
class SecuritychecksModelLogs extends JModelLegacy
{
/**
* Array de datos
* @var array
*/
var $_data;
/**
/**
* Total items
* @var integer
*/
var $_total = null;
/**
* Objeto Pagination
* @var object
*/
var $_pagination = null;

function __construct()
{
	parent::__construct();
	
	
	$mainframe = JFactory::getApplication();
	
	// Obtenemos las variables de paginaci�n de la petici�n
	$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
	$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

	// En el caso de que los l�mites hayan cambiado, los volvemos a ajustar
	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

	$this->setState('limit', $limit);
	$this->setState('limitstart', $limitstart);
	
}

/***/
protected function populateState()
{
	// Inicializamos las variables
	$app		= JFactory::getApplication();
	
	$search = $app->getUserStateFromRequest('filter.search', 'filter_search');
	$this->setState('filter.search', $search);
	$description = $app->getUserStateFromRequest('filter.description', 'filter_description');
	$this->setState('filter.description', $description);
	$type = $app->getUserStateFromRequest('filter.type', 'filter_type');
	$this->setState('filter.type', $type);
	$leido = $app->getUserStateFromRequest('filter.leido', 'filter_leido');
	$this->setState('filter.leido', $leido);
	$datefrom = $app->getUserStateFromRequest('datefrom', 'datefrom');
	$this->setState('datefrom', $datefrom);
	$dateto = $app->getUserStateFromRequest('dateto', 'dateto');
	$this->setState('dateto', $dateto);
		
	parent::populateState();
}


/* 
* Funci�n para obtener el n�mero de registros de la BBDD 'securitycheck_logs'
*/
function getTotal()
{
// Cargamos el contenido si es que no existe todav�a
if (empty($this->_total)) {
	$query = $this->_buildQuery();
	$this->_total = $this->_getListCount($query);
}
return $this->_total;
}

/* 
* Funci�n para obtener el n�mero de registros de la BBDD 'securitycheck_logs' seg�n la opci�n escogida por el usuario
*/
function getFilterTotal()
{
// Cargamos el contenido si es que no existe todav�a
if (empty($this->_total)) {
	$query = $this->_buildFilterQuery();
	$this->_total = $this->_getListCount($query);
}
return $this->_total;
}

/* 
* Funci�n para la paginaci�n 
*/
function getPagination()
{
// Cargamos el contenido si es que no existe todav�a
if (empty($this->_pagination)) {
	jimport('joomla.html.pagination');
$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
}
return $this->_pagination;
}

/* 
* Funci�n para la paginaci�n filtrada seg�n la opci�n escogida por el usuario
*/
function getFilterPagination()
{
// Cargamos el contenido si es que no existe todav�a
if (empty($this->_pagination)) {
	jimport('joomla.html.pagination');
$this->_pagination = new JPagination($this->getFilterTotal(), $this->getState('limitstart'), $this->getState('limit') );
}
return $this->_pagination;
}

/*
* Devuelve todos los componentes almacenados en la BBDD 'securitycheck_logs'
*/
function _buildQuery()
{
$query = ' SELECT * '
. ' FROM #__securitycheck_logs ORDER BY id DESC'
;
return $query;
}

/*
* Devuelve todos los componentes almacenados en la BBDD 'securitycheck_logs' filtrados seg�n las opciones establecidas por el usuario
*/
function _buildFilterQuery()
{
	// Creamos el nuevo objeto query
	$db = $this->getDbo();
	$query = $db->getQuery(true);
	
	// Sanitizamos la entrada
	$search = $this->state->get('filter.search');
	$search = $db->Quote('%'.$db->escape($search, true).'%');
		
	$query->select('*');
	$query->from('#__securitycheck_logs AS a');
	$query->where('(a.ip LIKE '.$search.' OR a.time LIKE '.$search.' OR a.description LIKE '.$search.' OR a.uri LIKE '.$search.')');
	
	// Filtramos la descripcion
	if ($description = $this->getState('filter.description')) {
		$query->where('a.tag_description = '.$db->quote($description));
	}
	
	// Filtramos el tipo
	if ($log_type = $this->getState('filter.type')) {
		$query->where('a.type = '.$db->quote($log_type));
	}
		
	// Filtramos leido/no leido
	$leido = $this->getState('filter.leido');
	if (is_numeric($leido)) {
		$query->where('a.marked = '.(int) $leido);
	}
	
	// Filtramos el rango de fechas
	JLoader::import('joomla.utilities.date');

	$fltDateFrom = $this->getState('datefrom', null, 'string');
	if($fltDateFrom) {
		$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';
		if(!preg_match($regex, $fltDateFrom)) {
			$fltDateFrom = '2000-01-01 00:00:00';
			$this->setState('datefrom', '');
		}
		$date = new JDate($fltDateFrom);
		$query->where($db->quoteName('time').' >= '.$db->Quote($date->toSql()));
	}

	$fltDateTo = $this->getState('dateto', null, 'string');
	if($fltDateTo) {
		$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';
		if(!preg_match($regex, $fltDateTo)) {
			$fltDateTo = '2037-01-01 00:00:00';
			$this->setState('dateto', '');
		}
		$date = new JDate($fltDateTo);
		$query->where($db->quoteName('time').' <= '.$db->Quote($date->toSql()));
	}
	
	// Ordenamos el resultado
	$query = $query . ' ORDER BY a.id DESC';
return $query;
}

/**
 * M�todo para cargar todas las vulnerabilidades de los componentes
 */
function getData()
{
	// Cargamos los datos
	if (empty( $this->_data )) {
		$query = $this->_buildQuery();
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
	}
		
	return $this->_data;
}

/**
 * M�todo para cargar todas las vulnerabilidades de los componentes especificadas en los t�rminos de b�squeda
 */
function getFilterData()
{
	// Cargamos los datos
	if (empty( $this->_data )) {
		$query = $this->_buildFilterQuery();
		$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
	}
			
	return $this->_data;
}

/* Funci�n para cambiar el estado de un array de logs de no le�do a le�do */
function mark_read(){
	$uids = JRequest::getVar('cid', 0, '', 'array');
	
	JArrayHelper::toInteger($uids, array());
	
	$db = $this->getDbo();
	foreach($uids as $uid) {
		$sql = "UPDATE `#__securitycheck_logs` SET marked=1 WHERE id='{$uid}'";
		$db->setQuery($sql);
		$db->execute();	
	}
}

/* Funci�n para cambiar el estado de un array de logs de le�do a no le�do */
function mark_unread(){
	$uids = JRequest::getVar('cid', 0, '', 'array');
	
	JArrayHelper::toInteger($uids, array());
	
	$db = $this->getDbo();
	foreach($uids as $uid) {
		$sql = "UPDATE `#__securitycheck_logs` SET marked=0 WHERE id='{$uid}'";
		$db->setQuery($sql);
		$db->execute();	
	}
}

/* Funci�n para borrar un array de logs */
function delete(){
	$uids = JRequest::getVar('cid', 0, '', 'array');
	
	JArrayHelper::toInteger($uids, array());
	
	$db = $this->getDbo();
	foreach($uids as $uid) {
		$sql = "DELETE FROM `#__securitycheck_logs` WHERE id='{$uid}'";
		$db->setQuery($sql);
		$db->execute();	
	}
}

/* Funci�n para borrar todos los logs */
function delete_all(){
	
	$db = $this->getDbo();
	$sql = "TRUNCATE `#__securitycheck_logs`";
	$db->setQuery($sql);
	$db->execute();	
}

}