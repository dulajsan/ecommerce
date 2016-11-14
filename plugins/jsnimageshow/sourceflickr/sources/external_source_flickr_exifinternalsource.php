<?php
/**
 * @version    $Id: imageshow.php 16609 2012-10-02 09:23:05Z haonv $
 * @package    JSN.ImageShow
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) @JOOMLASHINECOPYRIGHTYEAR@ JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 *
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// Set the directory separator define if necessary.
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

jimport('joomla.filesystem.file');
include_once(JPATH_ADMINISTRATOR. DS . 'components'. DS . 'com_imageshow'. DS . 'classes'. DS . 'jsn_is_exifinternalsource.php');
class JSNExternalSourceFlickrExifInternalSource extends JSNISExifInternalSource
{
	function __construct()
	{
		parent::__construct();
	}

	function renderData($exifData)
	{
		$tmpExifData = array();

		if (count($exifData))
		{
			if (isset($exifData['model']) && $exifData['model'] != '' && isset($exifData['make']) && $exifData['make'] != '')
			{
				$tmpExifData [] = @$exifData['make'].'/'.@$exifData['model'];
			}
			if (isset($exifData['exposure']) && $exifData['exposure'] != '')
			{
				$tmpExifData [] = $exifData['exposure'];
			}
			if (isset($exifData['fstop']) && $exifData['fstop'] != '')
			{
				//$tmpExifData [] = 'f/'.$exifData['fstop'];
				$tmpExifData [] = $exifData['fstop'];
			}
			if (isset($exifData['focallength']) && $exifData['focallength'] != '')
			{
				$tmpExifData [] = (float) $exifData['focallength'].'mm';
			}
			if (isset($exifData['iso']) && $exifData['iso'] != '')
			{
				$tmpExifData [] = 'ISO-'.(int) $exifData['iso'];
			}
			if (isset($exifData['flash']) && $exifData['flash'] != '')
			{
				if (is_numeric($exifData['flash']))
				{
					$tmpExifData [] = @$this->flashData[$exifData['flash']];
				}
				else
				{
					$tmpExifData [] = $exifData['flash'];
				}
			}
			else
			{
				$tmpExifData [] = @$this->flashData[16];
			}
			if (count($tmpExifData))
			{
				return implode(', ', $tmpExifData);
			}
			else
			{
				return '';
			}
		}
		return '';
	}
}