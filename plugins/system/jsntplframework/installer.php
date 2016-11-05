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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if ( ! function_exists('jsnTplLoader'))
{
	// Define base constants for the framework
	if ( ! defined('JSN_PATH_TPLFRAMEWORK_INSTALLER'))
	{
		define('JSN_PATH_TPLFRAMEWORK_INSTALLER', dirname(__FILE__));
	}

	// Define base constants for the framework
	if ( ! defined('JSN_PATH_TPLFRAMEWORK_LIBRARIES_INSTALLER'))
	{
		define('JSN_PATH_TPLFRAMEWORK_LIBRARIES_INSTALLER', JSN_PATH_TPLFRAMEWORK_INSTALLER . '/libraries/joomlashine');
	}

	// Import class loader
	require_once JSN_PATH_TPLFRAMEWORK_LIBRARIES_INSTALLER . '/loader.php';
}

// Import necessary Joomla libraries
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

// Load HTTP Request library if there is an older version of JSN template framework installed
if ( ! class_exists('JSNTplHttpRequest'))
{
	require_once dirname(__FILE__) . '/libraries/joomlashine/http/request.php';
}

/**
 * Class to implement hook for installation process
 *
 * @package     TPLFramework
 * @subpackage  Plugin
 * @since       1.0.0
 */
class PlgSystemJSNTPLFrameworkInstallerScript
{
	/**
	 * Template framework installation state.
	 *
	 * @var  boolean
	 */
	public static $tplFwInstalled = false;

	/**
	 * @var JApllication
	 */
	private static $_app;

	/**
	 * @var JDatabase
	 */
	private static $_dbo;

	/**
	 * @var JInstaller
	 */
	private static $_installer;

	/**
	 * @var SimpleXMLDocument
	 */
	private static $_manifest;

	/**
	 * @var string
	 */
	private static $_installPath;

	/**
	 * @var array
	 */
	private static $_manifestCache = array();

	/**
	 * @var string
	 */
	private static $_templateName;

	/**
	 * URL to download template framework
	 * @var string
	 */
	private $_frameworkUrl = 'http://www.joomlashine.com/index.php?option=com_lightcart&controller=remoteconnectauthentication&task=authenticate&tmpl=component&identified_name=tpl_framework&upgrade=yes&joomla_version=2.5';

	/**
	 * Implement preflight hook.
	 *
	 * This step will be verify permission for install/update process.
	 *
	 * @param   string  $mode    Install or update?
	 * @param   object  $parent  JInstaller object.
	 *
	 * @return  boolean
	 */
	public function preflight ($mode, $parent)
	{
		// Uninstall a template does not need pre-flight hook
		if ($mode == 'uninstall')
		{
			return true;
		}

		try
		{
			// Initialize necessary variables
			self::$_app				= JFactory::getApplication();
			self::$_dbo				= JFactory::getDBO();
			self::$_installer		= $parent->getParent();
			self::$_manifest		= self::$_installer->getManifest();
			self::$_installPath		= self::$_installer->getPath('source');
			self::$_templateName	= (string) self::$_manifest->template;
			self::$tplFwInstalled	= $this->_isInstalled('jsntplframework');

			if ($this->_checkPermission())
			{
				// Checking template installation
				if ($this->_isInstalled(self::$_templateName))
				{
					$this->_backupModifiedFiles(self::$_templateName);
				}

				// Checking framework installation
				if (self::$tplFwInstalled)
				{
					// Backup installed version of template framework
					JFolder::copy(JPATH_ROOT . '/plugins/system/jsntplframework', self::$_installPath . '/backups', '', true);
				}
			}
		}
		catch (Exception $ex)
		{
			self::$_app->enqueueMessage(JText::_($ex->getMessage()), 'error');

			return false;
		}
	}

	/**
	 * Implement postflight hook
	 *
	 * @param   string  $mode    Install or update?
	 * @param   object  $parent  JInstaller object.
	 *
	 * @return  boolean
	 */
	public function postflight ($mode, $parent)
	{
		if ($mode == 'uninstall') {
			return true;
		}

		try
		{
			// Download framework package and extract it to plugin folder
			$this->_installFramework();

			// Install template after extracted framework package
			$this->_installTemplate();
			$this->_cleanup(JPATH_ROOT . '/templates/' . self::$_templateName);

			// Active template framework plugin and set it to protected
			self::$_dbo->setQuery("UPDATE #__extensions SET enabled=1, protected=1, ordering=9999 WHERE element LIKE 'jsntplframework' LIMIT 1");

			method_exists(self::$_dbo, 'execute') ? self::$_dbo->execute() : self::$_dbo->query();

			// Check template framework installation state
			if ( ! self::$tplFwInstalled)
			{
				self::$_app->enqueueMessage(JText::_('PLG_SYSTEM_JSNTPLFRAMEWORK_ERROR_CANNOT_DOWNLOAD_FRAMEWORK_PACKAGE'));
			}
		}
		catch (Exception $ex)
		{
			die($ex->getMessage());
		}
	}

	/**
	 * This method use to validate folder permission before
	 * start installation process
	 *
	 * @return  boolean
	 */
	private function _checkPermission ()
	{
		// Skip checking for folder permission if FTP Layer is enabled
		$config = JFactory::getConfig();

		if ($config->get('ftp_enable'))
		{
			return true;
		}

		// List of path that will scan all language folder
		$languageFolders = array(
			self::$_installPath . '/language',
			self::$_installPath . '/template/language',
		);

		// Variable to store all supported languages
		$supportedLanguages = array();

		// Scan folder to get language list
		foreach ($languageFolders AS $path)
		{
			// Find all folder in the path
			foreach (glob($path . '/*', GLOB_ONLYDIR) AS $languagePath)
			{
				$supportedLanguages[] = basename($languagePath);
			}
		}

		// Filter to give unique folder name
		$supportedLanguages = array_unique($supportedLanguages);

		// Check folder permissions
		$canInstallTemplate	= is_writable(JPATH_ROOT . '/templates');
		$canInstallPlugin	= is_writable(JPATH_ROOT . '/plugins/system');
		$canInstallLanguage	= is_writable(JPATH_ROOT . '/language');

		// Variable to store all unwritable paths
		$unwritablePaths = array();

		// Check permission for each language code
		foreach ($supportedLanguages as $languageCode)
		{
			$languagePath = JPATH_ROOT . '/language/' . $languageCode;

			// Check write permission for language path
			if (is_dir($languagePath) && !is_writable($languagePath))
			{
				$unwritablePaths[]	= $languagePath;
				$canInstallLanguage	= false;
			}
		}

		// Enqueue error message for plugin path
		$canInstallPlugin OR self::$_app->enqueueMessage(JText::_('PLG_SYSTEM_JSNTPLFRAMEWORK_ERROR_PLUGIN_FOLDER_PERMISSION'), 'error');

		// Enqueue error message for template path
		$canInstallTemplate OR self::$_app->enqueueMessage(JText::_('PLG_SYSTEM_JSNTPLFRAMEWORK_ERROR_TEMPLATE_FOLDER_PERMISSION'), 'error');

		// Enqueue error message for language path
		$canInstallLanguage OR self::$_app->enqueueMessage(JText::_('PLG_SYSTEM_JSNTPLFRAMEWORK_ERROR_LANGUAGE_FOLDER_PERMISSION'), 'error');

		// Enqueue error message for language code path
		foreach ($unwritablePaths AS $path)
		{
			self::$_app->enqueueMessage(JText::sprintf('PLG_SYSTEM_JSNTPLFRAMEWORK_ERROR_UNWRITABLE_PATH', $path), 'error');
		}

		return $canInstallPlugin && $canInstallLanguage && $canInstallTemplate;
	}

	/**
	 * Checking files modification for installed template
	 *
	 * @return  void
	 */
	public function _backupModifiedFiles ($templateName)
	{
		$modifiedFiles = JSNTplHelper::getModifiedFiles($templateName);

		if (!empty($modifiedFiles['edit'])) {
			// Create temporary folder for store backup files
			$config		= JFactory::getConfig();
			$tmpPath	= $config->get('tmp_path');
			$backupPath = $tmpPath . "/{$templateName}_backup";
			$backupUrl  = JURI::root(true) . '/tmp/' . $templateName . '_backup.zip';

			$templatePath = JPATH_ROOT . "/templates/{$templateName}";

			if (!is_dir($backupPath)) {
				JFolder::create($backupPath);
			}

			$files = array();

			// Copy all modified files to backup folder
			foreach ($modifiedFiles['edit'] as $file) {
				if (strpos($file, '/') === false && strpos($file, '\\') === false) {
					$path = $backupPath;
				}
				else {
					$filePath = dirname($file);
					$path = "{$backupPath}/{$filePath}";
				}

				JSNTplHelper::makePath($path);
				JFile::copy("{$templatePath}/{$file}", "{$backupPath}/{$file}");

				$files[] = array(
					'name' => $file,
					'data' => JFile::read("{$backupPath}/{$file}")
				);
			}

			$archiver = new JSNTplArchiveZip();
			$archiver->create($backupPath . '.zip', $files);

			$this->_hasBackupFiles = true;
		}
	}

	/**
	 * Retrieve cached manifest data from database for an
	 * extension
	 *
	 * @param   string  $name  Name of the extension to load manifest
	 *
	 * @return  object
	 */
	private function _getMenifestCache ($name)
	{
		if (!isset(self::$_manifestCache[$name]) || self::$_manifestCache[$name] == null)
		{
			$query = self::$_dbo->getQuery(true);
			$query->select('manifest_cache')
				->from('#__extensions')
				->where('element LIKE \'' . self::$_dbo->escape($name) . '\'');
			self::$_dbo->setQuery($query);

			// Fetch manifest cache from database
			$result = self::$_dbo->loadResult();

			// Save loaded data to memory
			self::$_manifestCache[$name] = json_decode($result);
		}

		return self::$_manifestCache[$name];
	}

	/**
	 * Install template to the joomla system
	 *
	 * @return void
	 */
	private function _installTemplate ()
	{
		// Find all template inside templates folder and install it
		$templateInstaller = new JInstaller();
		$result = $templateInstaller->install(self::$_installPath . '/template');

		if ($result)
		{
			$executeMethod = method_exists(self::$_dbo, 'query') ? 'query' : 'execute';
			$templateName  = self::$_templateName;

			$this->_migrateParams($templateName);

			// Clean template cache
			$cacheDir = JPATH_ROOT . '/tmp/' . $templateName;

			if (is_dir($cacheDir)) {
				jimport('joomla.filesystem.folder');
				JFolder::delete($cacheDir);
			}

			// Update installed template to jsntemplate group
			self::$_dbo->setQuery("UPDATE #__extensions SET custom_data='jsntemplate' WHERE element='{$templateName}' AND type='template'");
			self::$_dbo->{$executeMethod}();

			// Make other template to not default
			self::$_dbo->setQuery("UPDATE #__template_styles SET home=0 WHERE client_id=0 AND home=1 LIMIT 1");
			self::$_dbo->{$executeMethod}();

			// Make installed template to default
			self::$_dbo->setQuery("UPDATE #__template_styles SET home=1 WHERE template LIKE '{$templateName}' LIMIT 1");
			self::$_dbo->{$executeMethod}();

			self::$_app->enqueueMessage(JText::sprintf('PLG_SYSTEM_JSNTPLFRAMEWORK_INSTALLED_TEMPLATE', JText::_(self::$_templateName)));
			self::$_installer->set('message', $templateInstaller->get('message'));

			if (isset($this->_hasBackupFiles) && $this->_hasBackupFiles == true)
			{
				// Create temporary folder for store backup files
				$config		= JFactory::getConfig();
				$tmpPath	= $config->get('tmp_path');
				$backupPath = $tmpPath . "/{$templateName}_backup.zip";
				$templateBackupPath = JPATH_ROOT . "/templates/{$templateName}/backups";

				if ( ! is_dir($templateBackupPath))
				{
					JFolder::create($templateBackupPath);
				}

				// Copy backup file to template folder
				JFile::copy($backupPath, $templateBackupPath . '/' . date('y-m-d') . '.zip');
			}
		}
	}

	/**
	 * Clean up files from old version
	 *
	 * @param  string  $path  Path to template directory
	 *
	 * @return void
	 */
	private function _cleanup ($path)
	{
		foreach (array('admin', 'elements', 'includes') as $name)
			if (JFolder::exists($path . '/' . $name))
				JFolder::delete($path . '/' . $name);

		foreach (glob($path . '/*.php') AS $file)
		{
			if (preg_match('/^jsn_/i', basename($file)))
			{
				JFile::delete($file);
			}
		}
	}

	/**
	 * Migrating existing parameters from old version of template
	 *
	 * @param   string  $templateName  Template name to be migrated
	 *
	 * @return  void
	 */
	private function _migrateParams ($templateName)
	{
		$executeMethod = method_exists(self::$_dbo, 'query') ? 'query' : 'execute';

		$query = self::$_dbo->getQuery(true);

		$query->select('id, params');
		$query->from('#__template_styles');
		$query->where('template=' . self::$_dbo->quote($templateName));

		self::$_dbo->setQuery($query);

		$paramsMap = array(
			// Logo settings
			'logoPath'						=> 'logoFile',
			'logoLink'						=> 'logoLink',
			'logoSlogan'					=> 'logoSlogan',
			'enableColoredLogo'				=> 'logoColored',

			// Layout settings
			'templateWidth'					=> 'layoutWidth',
			'narrowWidth'					=> 'layoutNarrowWidth',
			'wideWidth'						=> 'layoutWideWidth',
			'floatWidth'					=> 'layoutFloatWidth',
			'showFrontpage'					=> 'showFrontpage',

			// Mobile settings
			'enableMobileSupport'			=> 'mobileSupport',
			'enableMobileMenuSticky'		=> 'menuSticky',
			'showDesktopSwitcher'			=> 'desktopSwitcher',
			'mobileLogoPath'				=> 'mobileLogo',

			// Color & Style settings
			'templateColor'					=> 'templateColor',
			'templateTextStyle'				=> 'templateStyle',
			'templateTextSize'				=> 'textSize',
			'templateSpecialFont'			=> 'useSpecialFont',
			'enableCSS3Effect'				=> 'useCSS3Effect',

			// Menu & Sitetools settings
			'mmWidth'						=> 'mainMenuWidth',
			'smWidth'						=> 'sideMenuWidth',
			'sitetoolsPresentation'			=> 'sitetoolStyle',
			'enableTextSizer'				=> 'textSizeSelector',
			'enableWidthSelector'			=> 'widthSelector',
			'enableColorSelector'			=> 'colorSelector',

			'promoLeftWidth'				=> 'columnPromoLeft',
			'promoRightWidth'				=> 'columnPromoRight',
			'leftWidth'						=> 'columnLeft',
			'rightWidth'					=> 'columnRight',
			'innerleftWidth'				=> 'columnInnerleft',
			'innerrightWidth'				=> 'columnInnerRight',

			// SEO & System settings
			'enableTopH1'					=> 'enableH1',
			'enableGotopLink'				=> 'gotoTop',
			'enableIconLinks'				=> 'autoIconLink',
			'enablePrintingOptimization'	=> 'printOptimize',
			'analyticsCodePosition'			=> 'codePosition',
			'analyticsCode'					=> 'codeAnalytic',
			'customCSS'						=> 'cssFiles',
			'cssJsCompression'				=> 'compression',
			'enableSqueezebox'				=> 'useSqueezeBox'
		);

		foreach (self::$_dbo->loadObjectList() AS $row)
		{
			$params = @json_decode($row->params, true);
			$newParams = array();
			$curParams = array();

			if ($params == null)
			{
				continue;
			}

			foreach ($params AS $key => $value)
			{
				if (isset($paramsMap[$key]))
				{
					$newParams[$paramsMap[$key]] = $value;
				}
				else
				{
					$curParams[$key] = $value;
				}
			}

			if (count($newParams))
			{
				// Prepare template params
				$params = json_encode(array_merge($newParams, $curParams));
				$params = addslashes($params);

				$query = self::$_dbo->getQuery(true);

				$query->update('#__template_styles');
				$query->set("params = '" . $params . "'");
				$query->where('id = ' . $row->id);

				self::$_dbo->setQuery($query);
				self::$_dbo->{$executeMethod}();
			}
		}
	}

	/**
	 * Download framework package from Joomlashine server and
	 * install to Joomla system
	 *
	 * @return void
	 */
	private function _installFramework ()
	{
		if (@is_file(self::$_installPath . '/backups/jsntplframework.xml'))
		{
			$frameworkXml = simplexml_load_file(self::$_installPath . '/backups/jsntplframework.xml');
			$frameworkVersion = (string) $frameworkXml->version;

			// Check if template framework is up to date
			$this->_getLatestFrameworkVersion();

			if (empty($this->latest) || version_compare($frameworkVersion, $this->latest, '>='))
			{
				// Restore backup-ed template framework
				JFolder::copy(self::$_installPath . '/backups', JPATH_ROOT . '/plugins/system/jsntplframework', '', true);

				// Update framework version
				$this->_updateFrameworkVersion($frameworkVersion);

				// State that template framework is installed
				self::$tplFwInstalled = true;

				return;
			}
		}

		// Set time limit to zero will avoid error "Maximum execution time" when downloading template framework
		set_time_limit(0);

		try
		{
			// Download template framework from JoomlaShine server
			$downloadResult = JSNTplHttpRequest::get($this->_frameworkUrl, self::$_installPath . '/framework.zip');

			// Check download response headers
			if ($downloadResult['header']['content-type'] == 'application/zip')
			{
				// Unpack downloaded file and install it
				$frameworkUnpacked = JInstallerHelper::unpack(self::$_installPath . '/framework.zip');

				// Copy framework files to plugin folder
				JFolder::copy($frameworkUnpacked['dir'], JPATH_ROOT . '/plugins/system/jsntplframework', '', true);

				// Copy language files
				JFolder::copy($frameworkUnpacked['dir'] . '/language', JPATH_ADMINISTRATOR . '/language', '', true);

				// Parse manifest file for just installed framework version
				$frameworkXml = simplexml_load_file(JPATH_ROOT . '/plugins/system/jsntplframework/jsntplframework.xml');
				$frameworkVersion = (string) $frameworkXml->version;

				// Update framework version
				$this->_updateFrameworkVersion($frameworkVersion);

				// Enqueue message
				self::$_app->enqueueMessage(JText::_('PLG_SYSTEM_JSNTPLFRAMEWORK_INSTALLED_FRAMEWORK'));

				// State that template framework is installed
				self::$tplFwInstalled = true;
			}
		}
		catch (Exception $e)
		{
			// Do nothing
		}
	}

	/**
	 * Update version number of template framework in
	 * manifest cache
	 *
	 * @param   string  $version  Version number to be updated
	 *
	 * @return  void
	 */
	private function _updateFrameworkVersion ($version)
	{
		$executeMethod = method_exists(self::$_dbo, 'query') ? 'query' : 'execute';

		$query = self::$_dbo->getQuery(true);
		$query->select('manifest_cache')
			->from('#__extensions')
			->where('element=\'jsntplframework\'');

		self::$_dbo->setQuery($query);
		$manifestCache = json_decode(self::$_dbo->loadResult());
		$manifestCache->version = $version;

		self::$_dbo->setQuery("UPDATE #__extensions SET manifest_cache=" . self::$_dbo->quote(json_encode($manifestCache)) . " WHERE element='jsntplframework'");
		self::$_dbo->{$executeMethod}();
	}

	/**
	 * Check an extension is installed to joomla system
	 *
	 * @param   string  $name  Name of the extension
	 *
	 * @return  boolean
	 */
	private function _isInstalled ($name)
	{
		$query = self::$_dbo->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__extensions')
			->where('element LIKE \'' . self::$_dbo->escape($name) . '\'');
		self::$_dbo->setQuery($query);

		return intval(self::$_dbo->loadResult()) > 0;
	}

	/**
	 * Get latest framework version.
	 *
	 * @return  string
	 */
	private function _getLatestFrameworkVersion()
	{
		if ( ! isset($this->latest))
		{
			$this->latest = null;

			try
			{
				// Establish an internet connection to JoomlaShine server to get latest framework version
				$response = JSNTplHttpRequest::get('http://www.joomlashine.com/versioning/product_version.php?category=cat_template');
				$data = json_decode($response['body']);

				foreach ($data->items AS $product)
				{
					if ($product->identified_name == 'tpl_framework')
					{
						$this->latest = $product->version;

						break;
					}
				}
			}
			catch (Exception $e)
			{
				// Do nothing
			}
		}
	}
}
