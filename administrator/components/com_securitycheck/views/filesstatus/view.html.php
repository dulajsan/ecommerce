<?php
/**
* FilesStatus View para el Componente Securitycheck
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
// Chequeamos si el archivo est� incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

/**
* Securitycheckpros View
*
*/
class SecuritychecksViewFilesStatus extends JViewLegacy{

protected $state;
/**
* Securitycheckpros view m�todo 'display'
**/
function display($tpl = null)
{
JToolBarHelper::title( JText::_( 'Securitycheck' ).' | ' .JText::_('COM_SECURITYCHECK_FILEMANAGER_PANEL_TEXT'), 'securitycheck' );
JToolBarHelper::custom('redireccion_file_manager_control_panel','arrow-left','arrow-left','COM_SECURITYCHECK_REDIRECT_FILE_MANAGER_CONTROL_PANEL');

/* Cargamos el lenguaje del sitio */
$lang = JFactory::getLanguage();
$lang->load('com_securitycheck',JPATH_ADMINISTRATOR);

// Filtro por tipo de extensi�n
$this->state= $this->get('State');
$search = $this->state->get('filter.search');
$filter_kind = $this->state->get('filter.filemanager_kind');
$filter_permissions_status = $this->state->get('filter.filemanager_permissions_status');

// Establecemos el valor del filtro 'permissions_status' a cero para que muestre s�lo los permisos incorrectos
if ( $filter_permissions_status == ''){
	$this->state->set('filter.filemanager_permissions_status',0);
}

// Establecemos el valor del filtro 'kind' a 'File' para que muestre s�lo los ficheros
if ( $filter_kind == ''){
	$this->state->set('filter.filemanager_kind',$lang->_('COM_SECURITYCHECK_FILEMANAGER_FILE'));
}

$model = $this->getModel("filesstatus");
$items = $model->loadStack("permissions","file_manager");
$files_with_incorrect_permissions = $model->loadStack("filemanager_resume","files_with_incorrect_permissions");
$show_all = $this->state->get('showall',0);
$database_error = $model->get_campo_filemanager("estado");

// Ponemos los datos en el template
$this->assignRef('items', $items);
$this->assignRef('files_with_incorrect_permissions', $files_with_incorrect_permissions);
$this->assignRef('show_all', $show_all);
$this->assignRef('database_error', $database_error);

if ( !empty($items) ) {
	$pagination = $this->get('Pagination');
	$this->assignRef('pagination', $pagination);
}

parent::display($tpl);
}
}