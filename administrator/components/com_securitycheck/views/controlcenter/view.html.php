<?php
/**
* ControlCenter View para el Componente Securitycheck
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

class SecuritychecksViewControlCenter extends SecuritycheckView
{
protected $state;

function __construct() 	{
	parent::__construct();	
}

/**
* Securitycheckpros view m�todo 'display'
**/
function display($tpl = null)
{
// Obtenemos el modelo
$model = $this->getModel();

//  Par�metros del plugin
$items= $model->getControlCenterConfig();

// Extraemos los elementos que nos interesan...
$control_center_enabled= null;
$secret_key= null;


if ( !is_null($items['control_center_enabled']) ) {
	$control_center_enabled = $items['control_center_enabled'];	
}

if ( !is_null($items['secret_key']) ) {
	$secret_key = $items['secret_key'];	
}

// ... y los ponemos en el template
$this->assignRef('control_center_enabled',$control_center_enabled);
$this->assignRef('secret_key',$secret_key);


parent::display($tpl);
}
}