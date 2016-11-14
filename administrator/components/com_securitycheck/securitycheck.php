<?php
/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load library
require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheck'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'loader.php');

// Require el controlador espec�fico si es requerido
if($controller = JRequest::getWord('controller')) {
	$path = JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = 'cpanel';
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'cpanel.php');
	}
} else {
	$controller = 'cpanel';
	require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'cpanel.php');
	// Handle Live Update requests
	require_once(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'liveupdate'.DIRECTORY_SEPARATOR.'liveupdate.php');
	if(JRequest::getCmd('view','') == 'liveupdate') {
		LiveUpdate::handleRequest();
		return;
	}
}

// Creamos el controlador
$classname = 'SecuritychecksController'.$controller;
$controller = new $classname( );
// Realizamos la tarea requerida
$controller->execute(JRequest::getCmd('task','display'));
// Redirecci�n si es establecida por el controlador
$controller->redirect();