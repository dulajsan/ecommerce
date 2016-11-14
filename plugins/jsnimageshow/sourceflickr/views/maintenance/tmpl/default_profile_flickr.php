<?php
/**
 * @version    $Id: default_profile_flickr.php 17131 2012-10-17 04:45:59Z haonv $
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

$externalSourceID = JRequest::getInt('external_source_id');

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

$params = JSNUtilsLanguage::getTranslated(array(
		'JSN_IMAGESHOW_SAVE',
		'JSN_IMAGESHOW_CLOSE',
		'JSN_IMAGESHOW_CONFIRM'));
?>
<script type="text/javascript">
var objISMaintenance = null;
require(['imageshow/joomlashine/maintenance'], function (JSNISMaintenance) {
	objISMaintenance = new JSNISMaintenance({
		language: <?php echo json_encode($params); ?>
	});
});

require(['jquery'], function ($) {
	$(function () {
		function onSubmit(ciframe, imageSourceLink)
		{
			var form 				= $('#frm-edit-source-profile');
			var params 				= {};
			params.profile_title	= $('input[name="external_source_profile_title"]', form).val();
			params.api_key 	  		= $('input[name="flickr_api_key"]', form).val();
			params.secret_key   	= $('input[name="flickr_secret_key"]', form).val();
			params.screen_name  	= $('input[name="flickr_username"]', form).val();

			if (params.config_title == '' || params.api_key  == '' || params.secret_key == '' || params.screen_name == '')
			{
				alert("<?php echo JText::_('FLICKR_MAINTENANCE_REQUIRED_FIELD_PROFILE_CANNOT_BE_LEFT_BLANK', true); ?>");
				return false;
			}
			else
			{
				var url  				= 'index.php?option=com_imageshow&controller=maintenance&task=checkEditProfileExist&source=flickr&external_source_profile_title=' + params.profile_title + '&external_source_id=' + <?php echo $this->sourceInfo->external_source_id; ?>;
				params.validate_url 	= 'index.php?option=com_imageshow&controller=maintenance&task=validateProfile&source=flickr&validate_screen=maintenance&flickr_api_key='+ params.api_key +'&flickr_secret_key=' + params.secret_key + '&flickr_username=' + params.screen_name;
				objISMaintenance.checkEditedProfile(url, params, ciframe, imageSourceLink);
			}
		}

		function submitForm ()
		{
			var form = $('#frm-edit-source-profile');
			form.submit();
		}

		parent.gIframeOnSubmitFunc = onSubmit;
		gIframeSubmitFunc = submitForm;
	});
});
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
		<input type="text" name="external_source_profile_title"
			class="jsn-master jsn-input-xxlarge-fluid"
			id="external_source_profile_title"
			value="<?php echo @$this->sourceInfo->external_source_profile_title;?>" />
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
		<input type="text" name="flickr_api_key" id=""
			value="<?php echo @$this->sourceInfo->flickr_api_key;?>"
			<?php echo ($this->countShowlist) ? 'disabled="disabled" class="jsn-readonly jsn-master jsn-input-xxlarge-fluid"' : 'class="jsn-master jsn-input-xxlarge-fluid"'; ?> />
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
		<input type="text" name="flickr_secret_key" id=""
			value="<?php echo @$this->sourceInfo->flickr_secret_key;?>"
			<?php echo ($this->countShowlist) ? 'disabled="disabled" class="jsn-readonly jsn-master jsn-input-xxlarge-fluid"' : 'class="jsn-master jsn-input-xxlarge-fluid"'; ?> />
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
		<input type="text" name="flickr_username" id=""
			value="<?php echo @$this->sourceInfo->flickr_username;?>"
			<?php echo ($this->countShowlist) ? 'disabled="disabled" class="jsn-readonly jsn-master jsn-input-xxlarge-fluid"' : 'class="jsn-master jsn-input-xxlarge-fluid"'; ?> />
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
		<?php echo JHTML::_('select.genericList', $flickrThumbSize, 'flickr_thumbnail_size', 'class="jsn-master jsn-input-xxlarge-fluid"'. '', 'value', 'text', $this->sourceInfo->flickr_thumbnail_size); ?>
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
		<?php echo JHTML::_('select.genericList', $fickrImageSize, 'flickr_image_size', 'class="jsn-master jsn-input-xxlarge-fluid"'. '', 'value', 'text', $this->sourceInfo->flickr_image_size); ?>
	</div>
</div>
<input
	type="hidden" name="option" value="com_imageshow" />
<input
	type="hidden" name="controller" value="maintenance" />
<input
	type="hidden" name="task" value="saveprofile" id="task" />
<input type="hidden"
	name="source" value="flickr" />
<input
	type="hidden" name="external_source_id"
	value="<?php echo $externalSourceID; ?>" id="external_source_id" />
<?php echo JHTML::_( 'form.token' ); ?>