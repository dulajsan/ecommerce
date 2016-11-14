<?php
/**
* Protection View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

class SecuritychecksViewProtection extends JViewLegacy
{
protected $state;

/**
* Securitycheckpros view m�todo 'display'
**/
function display($tpl = null)
{

JToolBarHelper::title( JText::_( 'Securitycheck' ).' | ' .JText::_('COM_SECURITYCHECK_CPANEL_HTACCESS_PROTECTION_TEXT'), 'securitycheck' );
// Si existe el fichero .htaccess, mostramos la opci�n para borrarlo.
// Obtenemos el modelo
$model = $this->getModel();
// ... y el tipo de servidor web
$mainframe = JFactory::getApplication();
$server = $mainframe->getUserState("server",'apache');

if ( $server == 'apache' ) {
	if ( $model->ExistsFile('.htaccess') ) {
		JToolBarHelper::custom('delete_htaccess','file-remove','file-remove','COM_SECURITYCHECK_DELETE_HTACCESS');
	}
	JToolBarHelper::custom('protect','key','key','COM_SECURITYCHECK_PROTECT');
} else if ( $server == 'nginx' ) {
	JToolBarHelper::custom('generate_rules','key','key','COM_SECURITYCHECK_GENERATE_RULES');
}

JToolBarHelper::save();
JToolBarHelper::apply();
JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECK_REDIRECT_CONTROL_PANEL');
JToolBarHelper::custom('redireccion_system_info','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_SYSTEM_INFO');

// Obtenemos la configuraci�n actual...
$config = $model->getConfig();
// ... y la que hemos aplicado en el fichero .htaccess existente
$config_applied = $model->GetconfigApplied();

$this->assign('protection_config', $config);
$this->assign('config_applied', $config_applied);
$this->assign('ExistsHtaccess',	$model->ExistsFile('.htaccess'));
$this->assignRef('server', $server);

parent::display($tpl);
}
}