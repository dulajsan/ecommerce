<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  TPLFramework
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

/**
 * Autoload class file of JSN Template Framework.
 *
 * @param   string  $className  Name of class needs to be loaded.
 *
 * @return  boolean
 */
function jsnTplLoader ($className)
{
	if (strpos($className, 'JSNTpl') === 0)
	{
		$path  = strtolower(preg_replace('/([A-Z])/', '/\\1', substr($className, 6)));
		$fullPath = JSN_PATH_TPLFRAMEWORK_LIBRARIES_INSTALLER . '/' . $path . '.php';

		if (is_file($fullPath))
		{
			include_once $fullPath;
			return true;
		}

		return false;
	}
}

// Register jsnTplLoader for autoloading
spl_autoload_register('jsnTplLoader');
