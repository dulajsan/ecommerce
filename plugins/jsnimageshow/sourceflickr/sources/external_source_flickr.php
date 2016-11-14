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

include_once JPATH_PLUGINS . DS . 'jsnimageshow' . DS . 'sourceflickr' . DS . 'sources' . DS . 'external_source_flickr_exifinternalsource.php';
class JSNExternalSourceFlickr extends JSNImagesSourcesExternal
{
	protected $_serviceFlickr 	= false;
	protected $_nsid 			= false;

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->loadPhpFlickrClasses();

		if (isset($this->_source['sourceTable']))
		{
			$this->getService();
			$this->getNsid();
		}
	}

	private function loadPhpFlickrClasses()
	{
		$path = JPath::clean(dirname(dirname(__FILE__)) . DS . 'libs' . DS . 'phpFlickr.php');

		include_once $path;
	}

	private function getNsid()
	{
		if ($this->_nsid == false)
		{
			$flickrUsername = (isset($this->_source['sourceTable']->flickr_username)) ? $this->_source['sourceTable']->flickr_username : '';
			$this->_nsid	= $this->_serviceFlickr->people_findByUsername($flickrUsername);
		}

		return $this->_nsid;
	}

	private function getService()
	{
		if ($this->_serviceFlickr == false) {
			$this->_serviceFlickr = new phpFlickr($this->_source['sourceTable']->flickr_api_key, $this->_source['sourceTable']->flickr_secret_key);
		}

		return $this->_serviceFlickr;
	}

	function getValidation($config = array())
	{
		$config = array_merge(array('validate_screen' => 'SHOWLIST_FLEX'), $config);
		if (!isset($config['flickr_api_key'])
		|| !isset($config['flickr_secret_key'])
		|| !isset($config['flickr_username']))
		{
			$this->_errorMsg = JText::_('FLICKR_MISS_REQUIRED_INFORMATIONS');
			return false;
		}

		$prefixString = '';

		if (($config['validate_screen']) != '') {
			$prefixString = strtoupper($config['validate_screen']);
		}

		$service = new phpFlickr($config['flickr_api_key'], $config['flickr_secret_key']);
		$nsid 	 = $service->people_findByUsername($config['flickr_username']);
		$msg     = JText::_('FLICKR_INVALID_INFORMATIONS').' '. JText::_('FLICKR_'.$prefixString.'_ERROR_CODE_'.$service->getErrorCode());
		$this->_errorMsg = $msg;

		return ($nsid == false) ? false : true;
	}

	public function getCategories($config = array())
	{
		$photoSets 	= $this->_serviceFlickr->photosets_getList($this->_nsid['id']);

		//$xml = "<node label='Not In set' isBranch='true' data='0'></node>\n";

		if (count($photoSets['photoset']))
		{

			$xml .= "<node label='Image Set(s)' data=''>\n";

			foreach($photoSets['photoset'] as $album)
			{
				$album['title'] = htmlspecialchars ($album['title'], ENT_QUOTES);
				$album['id']    = htmlspecialchars ($album['id'], ENT_QUOTES);
				$xml .= "<node label='{$album['title']}' data='{$album['id']}'></node>\n";
			}

			$xml .= "</node>";
		}

		return $xml;

	}

	public function getFlickrImage($images,$imageSize)
	{
		$imgSrc='';
		switch ($imageSize)
		{
			case '320':
				if(isset($images['url_n'])){
					$imgSrc = $images['url_n'];
					break;
				}
			case '240':
				if(isset($images['url_s'])){
					$imgSrc = $images['url_s'];
					break;
				}
			case '150':
				if(isset($images['url_q'])){
					$imgSrc = $images['url_q'];
					break;
				}
			case '100':
				if(isset($images['url_t'])){
					$imgSrc = $images['url_t'];
					break;
				}
			case '75':
				if(isset($images['url_sq'])){
					$imgSrc = $images['url_sq'];
					break;
				}
		}
		return $imgSrc;
	}

	public function loadImages($config = array())
	{
		$albumId 			= $config['album'];
		$album				= $this->getArrayAlbums();
		$arrayAlbumID 		= array();
		$arrayPhotoInSet	= array();
		$photoArray 		= array();
		$objJSNFlickr 		= JSNISFactory::getObj('sourceflickr.classes.jsn_is_flickr', null, null, 'jsnplugin');
		$flickrParams		= json_decode($objJSNFlickr->getSourceParameters());

		if(isset($config['sync']))
		{
			$perPage	= NULL;
			$page		= NULL;
		}else
		{
			$perPage	= (isset($flickrParams->number_of_images_on_loading) && (is_int($flickrParams->number_of_images_on_loading)||ctype_digit($flickrParams->number_of_images_on_loading)))? $flickrParams->number_of_images_on_loading: '20';
			$page		= ($config['offset']/$perPage)+1;
		}

		$data 				= new stdClass();
		$owner				= '';

		if ($albumId == 0)
		{
			if (count($album))
			{
				foreach ($album as $value) {
					$arrayAlbumID[] = $value['id'];
				}
			}

			if (count($arrayAlbumID)) {
				$arrayPhotoInSet = $this->getPhotosInAlbum($arrayAlbumID);
			}

			$photos 	= $this->_serviceFlickr->people_getPublicPhotos($this->_nsid['id'], NULL, 'url_sq, url_t, url_s, url_q, url_n, url_m, url_l, url_o,description',2000);
			$photoArray = $photos["photos"]['photo'];
			$owner		= $photoArray[0]['owner'];
		}
		else
		{
			$photos 	= $this->_serviceFlickr->photosets_getPhotos($albumId,'url_sq, url_t, url_s, url_q, url_n, url_m, url_l, url_o,description', NULL, $perPage,$page);
			$photoArray = $photos["photoset"]['photo'];
			$owner		= $photos["photoset"]['owner'];
		}

		$photosList = array();

		if (count($photoArray))
		{
			if ($albumId == 0)
			{
				foreach ($photoArray as $photo)
				{
					if (in_array($photo['id'], $arrayPhotoInSet) == false)
					{
						$photoObject 				= new stdClass();
						$photoObject->image_title 	= $photo['title'];
						$photoObject->image_alt_text= $photo['title'];
						$photoObject->image_extid 	= $photo['id'];
						$imageSmallSrc				= $this->getFlickrImage($photo,$this->_source['sourceTable']->flickr_thumbnail_size);
						$photoObject->image_small 	= $imageSmallSrc;
						$photoObject->image_medium 	= @$photo['url_s'];
						$photoObject->image_big 	= @$photo['url_s'].','.@$photo['url_m'].','.@$photo['url_l'].','.@$photo['url_o'];
						$photoObject->album_extid	= $albumId;
						$photoObject->image_link    = 'https://www.flickr.com/photos/'.$owner.'/'.$photo['id'].'/';
						$photoObject->image_description = $photo['description'];
						$photoObject->exif_data 		= '';
						$photosList[] 	= $photoObject;
					}
				}
			}
			else
			{
				foreach ($photoArray as $photo)
				{
					$photoObject 				= new stdClass();
					$photoObject->image_title 	= $photo['title'];
					$photoObject->image_alt_text= $photo['title'];
					$photoObject->image_extid 	= $photo['id'];
					$imageSmallSrc				= $this->getFlickrImage($photo,$this->_source['sourceTable']->flickr_thumbnail_size);
					$photoObject->image_small 	= $imageSmallSrc;
					$photoObject->image_medium 	= @$photo['url_s'];
					$photoObject->image_big 	= @$photo['url_s'].','.@$photo['url_m'].','.@$photo['url_l'].','.@$photo['url_o'];
					$photoObject->album_extid	= $albumId;
					$photoObject->image_link    = 'https://www.flickr.com/photos/'.$owner.'/'.$photo['id'].'/';
					$photoObject->image_description = $photo['description'];
					$photoObject->exif_data 		= '';
					$photosList[] 	= $photoObject;
				}
			}
		}

		$data->images = $photosList;
		return $data;

	}
	public function getSyncImages($config = array())
	{
		$config 	 = array_merge(array('limitEdition' => true), $config);
		$objJSNUtils = JSNISFactory::getObj('classes.jsn_is_utils');
		$db 		 = JFactory::getDBO();

		$query = 'SELECT i.album_extid
				  FROM #__imageshow_images as i
				  INNER JOIN #__imageshow_showlist as sl ON sl.showlist_id = i.showlist_id
				  WHERE i.sync = 1
				  AND sl.published = 1
				  AND i.showlist_id = '.(int)$config['showlist_id']. '
				  GROUP BY i.album_extid
				  ORDER BY i.image_id';

		$db->setQuery($query);

		$albums 	 = $db->loadObjectList();
		$images		 = array();
		$limitStatus = $objJSNUtils->checkLimit();

		if (count($albums) > 0)
		{
			$albumLimit = 0;
			foreach ($albums as $album)
			{

				$data 		  = $this->loadImages(array('album' => $album->album_extid,'sync'=>true));
				$imagesFolder = $data->images;

				if (is_array($imagesFolder)) {
					$images = array_merge($images , $imagesFolder);
				}

				$albumLimit++;
				if ($limitStatus == true && $albumLimit >= 3 && $config['limitEdition'] == true) {
					break;
				}
			}

			if (count($images) > 0 && $limitStatus == true && $config['limitEdition'] == true) {
				$images = array_splice($images, 0, 10);
			}
		}

		$this->_data['images'] = $images;
	}

	public function countImages($albumId){
		if($albumId==0){
			$count = 0;
			$album = $this->getArrayAlbums();
			$arrayAlbumID	= array();
			$photoArray 	= array();
			if (count($album))
			{
				foreach ($album as $value) {
					$arrayAlbumID[] = $value['id'];
				}
			}

			if (count($arrayAlbumID)) {
				$arrayPhotoInSet = $this->getPhotosInAlbum($arrayAlbumID);
			}

			$photos 	= $this->_serviceFlickr->people_getPublicPhotos($this->_nsid['id'], NULL, 'url_sq, url_t, url_s, url_m, url_o',2000);
			$photoArray = $photos["photos"]['photo'];
			foreach ($photoArray as $photo)
			{
				if (in_array($photo['id'], $arrayPhotoInSet) == false)
				{
					$count++;
				}
			}
			return $count;
		}else{
			$photoSets 	= $this->_serviceFlickr->photosets_getInfo($albumId);
			return isset($photoSets['photos'])?$photoSets['photos']:0;
		}
	}
	public function getArrayAlbums()
	{
		$photoSets 	= $this->_serviceFlickr->photosets_getList($this->_nsid['id']);
		$albumsList = $photoSets['photoset'];

		return $albumsList;
	}

	public function getPhotosInAlbum($albumIDs)
	{
		if (count($albumIDs))
		{
			$photosList = array();

			foreach ($albumIDs as $albumID)
			{
				$photos = $this->_serviceFlickr->photosets_getPhotos($albumID);

				if (count($photos["photoset"]['photo']))
				{
					foreach ($photos["photoset"]['photo'] as $photo) {
						$photosList[] = $photo['id'];
					}
				}
			}

			return $photosList;
		}

		return false;
	}

	public function getOriginalInfoImages($config = array())
	{
		$arrayImageInfo = array();
		if (isset($config['image_extid']) && is_array($config['image_extid']))
		{
			foreach ($config['image_extid'] as $imgExtID)
			{
				$photoInfoOriginal 	= $this->getInfoPhoto($imgExtID);
				$imageObj 				= new stdClass();
				$imageObj->album_extid	= (string)$config['album_extid'];
				$imageObj->image_extid 	= (string)$imgExtID;
				$imageObj->title 		= ($photoInfoOriginal['photo']['title']) ? $photoInfoOriginal['photo']['title'] : '';
				$imageObj->description 	= ($photoInfoOriginal['photo']['description']) ? $photoInfoOriginal['photo']['description'] : '';
				$imageObj->link			= ($photoInfoOriginal['photo']['urls']['url'][0]['_content']) ? $photoInfoOriginal['photo']['urls']['url'][0]['_content'] : '';
				$arrayImageInfo[] 		= $imageObj;
			}
		}
		return $arrayImageInfo;
	}

	public function getInfoPhoto($photoId)
	{
		$photoInfo 	= $this->_serviceFlickr->photos_getInfo($photoId, $this->_source['sourceTable']->flickr_secret_key);
		return $photoInfo;
	}

	public function saveImages($config = array())
	{
		parent::saveImages($config);

		$config 	= $this->_data['saveImages'];
		$imgExtID 	= $config['imgExtID'];

		if (count($imgExtID))
		{
			$objJSNImages 	= JSNISFactory::getObj('classes.jsn_is_images');
			$ordering 		= $objJSNImages->getMaxOrderingByShowlistID($config['showlistID']);
			$imagesTable 	= JTable::getInstance('images', 'Table');

			if (count($ordering) < 0 or is_null($ordering)) {
				$ordering = 1;
			} else {
				$ordering = $ordering[0] + 1;
			}

			for ($i = 0 ; $i < count($imgExtID); $i++)
			{
				$imagesTable->showlist_id 		= $config['showlistID'];
				$imagesTable->image_extid 		= $imgExtID[$i];
				$imagesTable->album_extid 		= $config['albumID'][$imgExtID[$i]];
				$imagesTable->image_small 		= $config['imgSmall'][$imgExtID[$i]];
				$imagesTable->image_medium 		= $config['imgMedium'][$imgExtID[$i]];
				$imagesTable->image_big			= $config['imgBig'][$imgExtID[$i]];
				$imagesTable->image_title   	= $config['imgTitle'][$imgExtID[$i]];
				if (isset($config['imgAltText'][$imgExtID[$i]]))
				{
					$imagesTable->image_alt_text   	= $config['imgAltText'][$imgExtID[$i]];
				}				
				$imagesTable->image_description = $config['imgDescription'][$imgExtID[$i]];
				$imagesTable->image_link 		= $config['imgLink'][$imgExtID[$i]];
				$imagesTable->ordering			= $ordering;
				$imagesTable->custom_data 		= $config['customData'][$imgExtID[$i]];
				$imagesTable->exif_data 		= $this->getExifInfoPhoto($imgExtID[$i]);
				$result = $imagesTable->store();

				$imagesTable->image_id = null;

				$ordering ++;
			}

			if ($result) {
				return true;
			}

			return false;
		}

		return false;
	}

	public function getImages2JSON($config = array())
	{
		parent::getImages2JSON($config);

		$objJSNUtils 	= JSNISFactory::getObj('classes.jsn_is_utils');
		$arrayImage 	= array();

		if (count($this->_data['images']))
		{
			foreach ($this->_data['images'] as $image)
			{
				$image							= (array) $image;
				$imageDetailObj 				= new stdClass();
				$imageDetailObj->thumbnail		= $image['image_small'];
				$imageBig 						= explode(',', $image['image_big']);
				$imageLink 						= $objJSNUtils->checkValueArray($imageBig, $this->_source['sourceTable']->flickr_image_size);
				$imageDetailObj->image 			= $imageLink;
				$imageDetailObj->title 			= $image['image_title'];
				if (isset($image['image_alt_text']))
				{
					$imageDetailObj->alt_text 			= $image['image_alt_text'];
				}
				else
				{
					$imageDetailObj->alt_text 			= $image['image_title'];
				}				
				$imageDetailObj->description 	= (!is_null($image['image_description'])) ? $image['image_description'] : '';
				$imageDetailObj->link 			= $image['image_link'];
				$imageDetailObj->exif_data		= $image['exif_data'];
				$arrayImage[] 					= $imageDetailObj;
			}
		}

		return $arrayImage;
	}

	public function getImageSrc($config = array('image_big' => '', 'URL' => ''))
	{
		$imageSrc = '';
		$arrayImg = explode(',', $config['image_big']);

		if (count($arrayImg))
		{
			$objJSNUtils = JSNISFactory::getObj('classes.jsn_is_utils');
			$imageSrc = $objJSNUtils->checkValueArray($arrayImg, $this->_source['sourceTable']->flickr_image_size);
		}

		return $imageSrc;
	}

	function addOriginalInfo($config = array())
	{
		$data = array();

		if (is_array($this->_data['images']))
		{
			foreach ($this->_data['images'] as $img)
			{
				if ($img->custom_data == 1)
				{
					$info	= $this->getInfoPhoto($img->image_extid);
					$img->original_title 		= $info['photo']['title'];
					$img->original_description 	= $info['photo']['description'];
					$img->original_link			= $info['photo']['urls']['url'][0]['_content'];
				}
				else
				{
					$img->original_title 		= $img->image_title;
					$img->original_description 	= $img->image_description;
					$img->original_link			= $img->image_link;
				}

				$data[] = $img;
			}
		}

		$this->_data['images'] = $data;
	}

	protected function getExifInfoPhoto($photoID)
	{
		$data	 	= $this->_serviceFlickr->photos_getExif($photoID, $this->_source['sourceTable']->flickr_secret_key);
		$exifInfo 	= array();
		if ($data ['camera'] != '')
		{
			$exifs =  $data['exif'];

			foreach ($exifs as $exif)
			{
				if ($exif['tagspace'] == 'IFD0' || $exif['tagspace'] == 'TIFF')
				{
					if ($exif['label'] == 'Make')
					{
						$exifInfo['make']= $exif['raw'];
					}
					if ($exif['label'] == 'Model')
					{
						$exifInfo['model']= $exif['raw'];
					}
				}
				if ($exif['tagspace'] == 'ExifIFD' || $exif['tagspace'] == 'EXIF')
				{
					if ($exif['label'] == 'Exposure')
					{
						$exifInfo['exposure']= $exif['raw'];
					}
					if ($exif['label'] == 'Flash')
					{
						$exifInfo['flash']= $exif['raw'];
					}
					if ($exif['label'] == 'Focal Length')
					{
						$exifInfo['focallength']= $exif['clean'];
					}
					if ($exif['label'] == 'ISO Speed')
					{
						$exifInfo['iso']= $exif['raw'];
					}
					if ($exif['label'] == 'Aperture')
					{
						if (isset($exif['clean']) && $exif['clean'] != '')
						{
							$exifInfo['fstop']= $exif['clean'];
						}
					}
				}
			}
		}
		$objExif = new JSNExternalSourceFlickrExifInternalSource();
		return $objExif->renderData($exifInfo);
	}
}