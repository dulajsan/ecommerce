<?php
/**
 * @version    $Id: form_profile_flickr.php 17131 2012-10-17 04:45:59Z haonv $
 * @package    JSN.ImageShow
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) @JOOMLASHINECOPYRIGHTYEAR@ JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

defined('_JEXEC') or die('Restricted access');

$externalProfileID = JRequest::getInt('external_source_profile_id', 0);

$flickrThumbSize = array(
array('value' => 75, 'text' => '75'),
array('value' => 100, 'text' => '100'),
array('value' => 150, 'text' => '150'),
array('value' => 240, 'text' => '240'),
array('value' => 320, 'text' => '320')
);

$fickrImageSize = array(
array('value' => 0, 'text' => JText::_('FLICKR_MAINTENANCE_SOURCE_SMALL')),
array('value' => 1, 'text' => JText::_('FLICKR_MAINTENANCE_SOURCE_MEDIUM')),
array('value' => 2, 'text' => JText::_('FLICKR_MAINTENANCE_SOURCE_LARGE'))
);

?>
<script type="text/javascript">
function submitFormProfile()
{
	var form 				= jQuery('#frm-edit-source-profile');
	var params 				= {};
	params.profile_title	= jQuery('input[name="external_source_profile_title"]', form).val();
	params.api_key 	  		= jQuery('input[name="flickr_api_key"]', form).val();
	params.secret_key   	= jQuery('input[name="flickr_secret_key"]', form).val();
	params.screen_name  	= jQuery('input[name="flickr_username"]', form).val();

	if (params.config_title == '' || params.api_key  == '' || params.secret_key == '' || params.screen_name == '')
	{
		alert("<?php echo JText::_('FLICKR_MAINTENANCE_REQUIRED_FIELD_PROFILE_CANNOT_BE_LEFT_BLANK', true); ?>");
		return false;
	}
	else
	{
		var url  				= 'index.php?option=com_imageshow&controller=maintenance&task=checkEditProfileExist&source=flickr&external_source_profile_title=' + params.profile_title + '&external_source_id=0&rand=' + Math.random();
		params.validate_url 	= 'index.php?option=com_imageshow&controller=maintenance&task=validateProfile&source=flickr&validate_screen=showlist&flickr_api_key='+ params.api_key +'&flickr_secret_key=' + params.secret_key + '&flickr_username=' + params.screen_name + '&rand=' + Math.random();
		objISShowlist.checkEditedProfile(url, params);
	}
	return false;
}
</script>
<div class="control-group">
	<label class="control-label"><?php echo JText::_('FLICKR_MAINTENANCE_TITLE_PROFILE_TITLE');?>
		<a class="hint-icon jsn-link-action" href="javascript:void(0);">(?)</a>
	</label>
	<div class="controls">
		<div class="jsn-preview-hint-text">
			<div class="jsn-preview-hint-text-content clearafter">
			<?php echo JText::_('FLICKR_MAINTENANCE_DES_PROFILE_TITLE');?>
				<a href="javascript:void(0);"
					class="jsn-preview-hint-close jsn-link-action">[x]</a>
			</div>
		</div>
		<input class="jsn-master jsn-input-xxlarge-fluid" type="text"
			name="external_source_profile_title"
			id="external_source_profile_title" value="" size="80" />
	</div>
</div>
<div class="control-group">
	<label class="control-label"><?php echo JText::_('FLICKR_MAINTENANCE_TITLE_FLICKR_API_KEY');?>
		<a class="hint-icon jsn-link-action" href="javascript:void(0);">(?)</a>
	</label>
	<div class="controls">
		<div class="jsn-preview-hint-text">
			<div class="jsn-preview-hint-text-content clearafter">
			<?php echo JText::_('FLICKR_MAINTENANCE_DES_FLICKR_API_KEY');?>
				<a href="javascript:void(0);"
					class="jsn-preview-hint-close jsn-link-action">[x]</a>
			</div>
		</div>
		<input class="jsn-master jsn-input-xxlarge-fluid" type="text"
			name="flickr_api_key" id="" value="" size="80" />
	</div>
</div>
<div class="control-group">
	<label class="control-label"><?php echo JText::_('FLICKR_MAINTENANCE_TITLE_FLICKR_API_SECRET_KEY');?>
		<a class="hint-icon jsn-link-action" href="javascript:void(0);">(?)</a>
	</label>
	<div class="controls">
		<div class="jsn-preview-hint-text">
			<div class="jsn-preview-hint-text-content clearafter">
			<?php echo JText::_('FLICKR_MAINTENANCE_DES_FLICKR_API_SECRET_KEY');?>
				<a href="javascript:void(0);"
					class="jsn-preview-hint-close jsn-link-action">[x]</a>
			</div>
		</div>
		<input class="jsn-master jsn-input-xxlarge-fluid" type="text"
			name="flickr_secret_key" id="" value="" size="80" />
	</div>
</div>

<div class="control-group">
	<label class="control-label"><?php echo JText::_('FLICKR_MAINTENANCE_TITLE_FLICKR_SCREEN_NAME');?>
		<a class="hint-icon jsn-link-action" href="javascript:void(0);">(?)</a>
	</label>
	<div class="controls">
		<div class="jsn-preview-hint-text">
			<div class="jsn-preview-hint-text-content clearafter">
			<?php echo JText::_('FLICKR_MAINTENANCE_DES_FLICKR_SCREEN_NAME');?>
				<a href="javascript:void(0);"
					class="jsn-preview-hint-close jsn-link-action">[x]</a>
			</div>
		</div>
		<input class="jsn-master jsn-input-xxlarge-fluid" type="text"
			name="flickr_username" id="" value="" size="80" />
	</div>
</div>
<div class="control-group">
	<label class="control-label"><?php echo JText::_('FLICKR_MAINTENANCE_TITLE_THUMBNAIL_MAX_SIZE');?>
		<a class="hint-icon jsn-link-action" href="javascript:void(0);">(?)</a>
	</label>
	<div class="controls">
		<div class="jsn-preview-hint-text">
			<div class="jsn-preview-hint-text-content clearafter">
			<?php echo JText::_('FLICKR_MAINTENANCE_THUMBNAIL_MAX_SIZE_DESC');?>
				<a href="javascript:void(0);"
					class="jsn-preview-hint-close jsn-link-action">[x]</a>
			</div>
		</div>
		<?php echo JHTML::_('select.genericList', $flickrThumbSize, 'flickr_thumbnail_size', 'class="jsn-master jsn-input-xxlarge-fluid" '. '', 'value', 'text', '100'); ?>
	</div>
</div>
<div class="control-group">
	<label class="control-label"><?php echo JText::_('FLICKR_MAINTENANCE_TITLE_FLICKR_IMAGE_SIZE');?>
		<a class="hint-icon jsn-link-action" href="javascript:void(0);">(?)</a>
	</label>
	<div class="controls">
		<div class="jsn-preview-hint-text">
			<div class="jsn-preview-hint-text-content clearafter">
			<?php echo JText::_('FLICKR_MAINTENANCE_DES_FLICKR_IMAGE_SIZE');?>
				<a href="javascript:void(0);"
					class="jsn-preview-hint-close jsn-link-action">[x]</a>
			</div>
		</div>
		<?php echo JHTML::_('select.genericList', $fickrImageSize, 'flickr_image_size', 'class="jsn-master jsn-input-xxlarge-fluid" '. '', 'value', 'text', 1); ?>
	</div>
</div>
