<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  JSNTplFramework
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import necessary Joomla libraries
jimport('joomla.filesystem.folder');
jimport('joomla.registry.registry');

/**
 * This class contains common method will be used in
 * the template framework
 *
 * @package     JSNTplFramework
 * @since       1.0.0
 */
abstract class JSNTplHelper
{
	/**
	 * JVersion instance
	 *
	 * @var  JVersion
	 */
	private static $_version;

	private static $_versionData;

	/**
	 * Load templateDetails.xml using simplexml and return it
	 *
	 * @param   string  $template  The template name
	 *
	 * @return  object
	 */
	public static function getManifest ($template)
	{
		$registry = JRegistry::getInstance('JSNTplFramework');

		if ($registry->exists('template.manifest')) {
			return $registry->get('template.manifest');
		}

		$xmlDocument = simplexml_load_file(JPATH_SITE . "/templates/{$template}/templateDetails.xml");
		$registry->set('template.manifest', $xmlDocument);

		return $xmlDocument;
	}

	/**
	 * Retrieve cached manifest from the database
	 *
	 * @param   string  $template  Template name
	 * @return  array
	 */
	public static function getManifestCache ($template)
	{
		$registry = JRegistry::getInstance('JSNTplFramework');

		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);
		$query->select('manifest_cache')
			->from('#__extensions')
			->where('element LIKE \'' . $template . '\'');
		$dbo->setQuery($query);

		return json_decode($dbo->loadResult());
	}

	/**
	 * Retrieve current version of Joomla
	 *
	 * @return  string
	 */
	public static function getJoomlaVersion ($size = null, $includeDot = true)
	{
		$joomlaVersion = new JVersion();
		$versionPieces = explode('.', $joomlaVersion->getShortVersion());

		if (is_numeric($size) && $size > 0 && $size < count($versionPieces)) {
			$versionPieces = array_slice($versionPieces, 0, $size);
		}

		return implode($includeDot === true ? '.' : '', $versionPieces);
	}

	/**
	 * Return the template ID
	 *
	 * @param   string  $name  The template name
	 *
	 * @return  string
	 */
	public static function getTemplateId ($name)
	{
		$manifest = self::getManifest($name);

		if (isset($manifest->identifiedName)) {
			return (string) $manifest->identifiedName;
		}

		if (preg_match('/^jsn_(.*)_(free|pro)$/i', $name, $matched)) {
			return sprintf('tpl_%s', $matched[1]);
		}
	}

	/**
	 * Retrieve version of the template that determined by name
	 *
	 * @param   string  $name  The template name to retrieve version
	 * @return  string
	 */
	public static function getTemplateVersion ($name)
	{
		$registry = JRegistry::getInstance('JSNTplFramework');

		if ($registry->exists('template.version')) {
			return $registry->get('template.version');
		}

		$manifest = JSNTplHelper::getManifestCache($name);
		$version  = $manifest->version;

		$registry->set('template.version', $version);
		return $version;
	}

	/**
	 * Retrieve edition of the template that determined by name
	 *
	 * @param   string  $name  The template name to retrieve edition
	 * @return  string
	 */
	public static function getTemplateEdition ($name)
	{
		$registry = JRegistry::getInstance('JSNTplFramework');

		if ($registry->exists('template.edition')) {
			return $registry->get('template.edition');
		}

		$manifest = JSNTplHelper::getManifest($name);
		$edition  = isset($manifest->edition) ? (string) $manifest->edition : 'FREE';

		$registry->set('template.edition', $edition);
		return $edition;
	}

	/**
	 * Fetch all installed extensions from the database
	 *
	 * @return  array
	 */
	public static function findInstalledExtensions ()
	{
		$registry = JRegistry::getInstance('JSNTplFramework');
		$installedExtensions = $registry->get('extension.installed', array());

		if (empty($installedExtensions)) {
			$dbo = JFactory::getDBO();
			$dbo->setQuery('SELECT element, manifest_cache FROM #__extensions WHERE type IN ("component", "plugin", "module")');

			foreach ($dbo->loadObjectList() as $extension) {
				$installedExtensions[$extension->element] = json_decode($extension->manifest_cache);
			}

			$registry->set('extension.installed', $installedExtensions);
		}

		return $installedExtensions;
	}

	/**
	 * Return TRUE when PRO Edition of the template is installed
	 *
	 * @param   string  $template  The template name
	 *
	 * @return  boolean
	 */
	public static function isInstalledProEdition ($template)
	{
		if (preg_match('/^jsn_(.*)_(free|pro)$/i', $template, $matched)) {
			$nameOfProEdition = sprintf('jsn_%s_pro', $matched[1]);

			$dbo = JFactory::getDBO();
			$dbo->setQuery("SELECT count(*) FROM #__extensions WHERE type LIKE 'template' AND element LIKE '{$nameOfProEdition}'");

			return intval($dbo->loadResult()) > 0;
		}

		return false;
	}

	/**
	 * Return TRUE when extension with name=$name is installed
	 *
	 * @param   string   $name  The name of extension
	 *
	 * @return  boolean
	 */
	public static function isInstalledExtension ($name)
	{
		$installedExtensions = self::findInstalledExtensions();
		return isset($installedExtensions[$name]);
	}

	/**
	 * List all modified files of the template
	 *
	 * @param   string  $template  The template name
	 *
	 * @return  mixed
	 */
	public static function getModifiedFiles ($template)
	{
		$templatePath = JPATH_SITE . "/templates/{$template}";
		$checksumFile = $templatePath . '/template.checksum';

		if ( ! is_file($checksumFile))
		{
			return false;
		}

		$files = JFile::read($checksumFile);
		$hashTable = array();

		// Parse all hash data from checksum file
		foreach (explode("\n", $files) AS $line)
		{
			$line = trim($line);

			if ( ! empty($line) AND strpos($line, "\t") !== false)
			{
				list($path, $hash) = explode("\t", $line);
				$hashTable[$path] = $hash;
			}
		}

		// Find all files in template folder and check it state
		$files = JFolder::files($templatePath, '.', true, true, array('.checksum', '.svn', 'CVS', 'language'));

		$addedFiles = array();
		$changedFiles = array();
		$deletedFiles = array();
		$originalFiles = array();

		foreach ($files AS $file)
		{
			$fileMd5 = md5_file($file);
			$file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
			$file = ltrim(substr($file, strlen($templatePath)), '/');

			// Checking file is added
			if ( ! isset($hashTable[$file]))
			{
				$addedFiles[] = $file;
			}
			// Checking file is changed
			elseif (isset($hashTable[$file]) && $fileMd5 != $hashTable[$file])
			{
				$changedFiles[] = $file;
			}
			// Checking file is original
			elseif (isset($hashTable[$file]) && $fileMd5 == $hashTable[$file])
			{
				$originalFiles[] = $file;
			}
		}

		$templateFiles = array_merge($addedFiles, $changedFiles, $originalFiles);
		$templateFiles = array_unique($templateFiles);

		// Find all deleted files
		foreach (array_keys($hashTable) AS $item)
		{
			if ( ! in_array($item, $templateFiles))
			{
				$deletedFiles[] = $item;
			}
		}

		return array(
			'add' => $addedFiles,
			'delete' => $deletedFiles,
			'edit' => $changedFiles
		);
	}

	/**
	 * Download templates information data from JoomlaShine server
	 *
	 * @return  void
	 */
	public static function getVersionData ()
	{
		if (empty(self::$_versionData))
		{
			try
			{
				$response = JSNTplHttpRequest::get('http://www.joomlashine.com/versioning/product_version.php?category=cat_template');
			}
			catch (Exception $e)
			{
				throw new Exception('JSN_TPLFW_ERROR_CONTACTING_TO_JOOMLASHINE');
			}

			self::$_versionData = json_decode($response['body'], true);
		}

		// Return result
		return self::$_versionData;
	}

	/**
	 * Make a nested path , creating directories down the path
	 * recursion !!
	 *
	 * @param   string  $path  Path to create directories
	 *
	 * @return  void
	 */
	public static function makePath ($path)
	{
		// Check if directory already exists
		if (is_dir($path) OR empty($path))
		{
			return true;
		}

		// Ensure a file does not already exist with the same name
		$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);

		if (is_file($path))
		{
			trigger_error('A file with the same name already exists', E_USER_WARNING);
			return false;
		}

		// Crawl up the directory tree
		$nextPath = substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR));

		if (self::makePath($nextPath))
		{
			if ( ! is_dir($path))
			{
				return JFolder::create($path);
			}
		}

		return false;
	}
}
