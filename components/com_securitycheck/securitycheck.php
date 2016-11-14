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
//require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_securitycheckpro'.DS.'helpers'.DS.'encrypt.php');
// Many versions of PHP suffer from a brain-dead buggy JSON library. Let's
// load our own (actually it's PEAR's Services_JSON).
//require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_securitycheckpro'.DS.'helpers'.DS.'jsonlib.php');


$controller = 'json';
require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'json.php');

// Creamos el controlador
$classname = 'SecuritychecksController'.$controller;
$controller = new $classname( );
// Realizamos la tarea requerida
$controller->execute('json');