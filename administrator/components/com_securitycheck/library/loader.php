<?php
/**
* Securitycheck Library
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// No Permission
defined('_JEXEC') or die('Restricted access');

// Import Joomla Libraries
jimport('joomla.html.parameter');

$library = dirname(__FILE__);

JLoader::register('SecuritycheckController', $library.'/controller.php');
JLoader::register('SecuritycheckModel', $library.'/model.php');
JLoader::register('SecuritycheckView', $library.'/view.php');