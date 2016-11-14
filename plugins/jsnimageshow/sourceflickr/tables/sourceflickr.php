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

class TableSourceFlickr extends JTable
{
	var $external_source_id = null;
	var $external_source_profile_title = null;
	var $flickr_api_key = null;
	var $flickr_secret_key = null;
	var $flickr_username = null;
	var $flickr_caching = null;
	var $flickr_cache_expiration = null;
	var $flickr_thumbnail_size = null;
	var $flickr_image_size = null;

	function __construct(& $db) {
		parent::__construct('#__imageshow_external_source_flickr', 'external_source_id', $db);
	}
	function store($updateNulls = false)
	{
		$query = 'SELECT * FROM #__imageshow_external_source_flickr WHERE external_source_id ='.(int)$this->external_source_id;
		$this->_db->setQuery($query);
		$current = $this->_db->loadObject();
		$updateThumbnailSize = false;

		if ($current)
		{
			if ($this->flickr_thumbnail_size && $this->flickr_thumbnail_size != $current->flickr_thumbnail_size) {
				$updateThumbnailSize = $this->flickr_thumbnail_size;
			}
		}

		if (parent::store($updateNulls = false))
		{
			if (isset($updateThumbnailSize)) {
				$this->updateThumbnailSize($this->external_source_id, $updateThumbnailSize);
			}
		} else {
			return false;
		}
		return true;
	}
	public function getFlickrImage($imageInfo,$size){
		$maxWidth	= $imageInfo[0]['width'];
		$imgSrc		= $imageInfo[0]['source'];
		foreach ($imageInfo as $info)
		{
			if($info['width'] > $maxWidth && $info['width'] <= $size)
			{
				$maxWidth	= $info['width'];
				$imgSrc		= $info['source'];
			}
		}
		return $imgSrc;
	}
	function updateThumbnailSize($externalSourceId, $updateThumbnailSize = 130)
	{
		if (!$updateThumbnailSize || !$externalSourceId) return false;

		$objJSNShowlist = JSNISFactory::getObj('classes.jsn_is_showlist');
		$objJSNImages	= JSNISFactory::getObj('classes.jsn_is_images');
		$showlists 		= $objJSNShowlist->getListShowlistBySource($externalSourceId, 'flickr');
		include_once JPath::clean(dirname(dirname(__FILE__)).DS.'libs'.DS.'phpFlickr.php');
		$serviceFlickr = new phpFlickr($this->flickr_api_key, $this->flickr_secret_key);
		$db = JFactory::getDBO();
		foreach ($showlists as $showlist)
		{
			$images = $objJSNImages->getImagesByShowlistID($showlist->showlist_id);

			if ($images)
			{
				foreach ($images as $image)
				{
					$imageInfo	= $serviceFlickr->photos_getSizes($image->image_extid);
					$imageSmall = $this->getFlickrImage($imageInfo,$this->flickr_thumbnail_size);
					if($imageSmall!="error"&&$imageSmall!=""){
						$query = 'UPDATE #__imageshow_images
								  SET image_small = '.$this->_db->quote($imageSmall).'
								  WHERE showlist_id ='. (int)$showlist->showlist_id .'
								  AND image_id = '.$this->_db->quote($image->image_id).'
								  LIMIT 1';
						$db->setQuery($query);
						$db->query();
					}
				}
			}
		}
	}
}
?>