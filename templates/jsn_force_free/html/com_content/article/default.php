<?php
/**
 * @version		$Id: default.php 12858 2012-05-29 18:16:37Z tuva $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Load template framework
if (!defined('JSN_PATH_TPLFRAMEWORK')) {
	require_once JPATH_ROOT . '/plugins/system/jsntplframework/jsntplframework.defines.php';
	require_once JPATH_ROOT . '/plugins/system/jsntplframework/libraries/joomlashine/loader.php';
}

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
$jsnUtils   = JSNTplUtils::getInstance();
?>
<?php if ($jsnUtils->isJoomla3()):

	JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
	JHtmlBootstrap::dropdown('dropdown-toggle');
	?>
<?php else :
//JHtml::addIncludePath(JPATH_COMPONENT. DIRECTORY_SEPARATOR .'helpers');
	JHtml::addIncludePath(JPATH_THEMES. DIRECTORY_SEPARATOR .$template. DIRECTORY_SEPARATOR .'html'. DIRECTORY_SEPARATOR .'com_content');
	?>
<?php endif; ?>
<?php
// Create shortcuts to some parameters.
$params		= $this->item->params;
$canEdit	= $this->item->params->get('access-edit');
$user		= JFactory::getUser();
$images = json_decode($this->item->images);
$urls = json_decode($this->item->urls);
?>
<?php if ($jsnUtils->isJoomla3()): 
	$info    = $this->item->params->get('info_block_position', 0);
	JHtml::_('behavior.caption');
	$useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
		|| $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author'));

		?>
		<div class="item-page<?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Article">
			<meta itemprop="inLanguage" content="<?php echo ($this->item->language === '*') ? JFactory::getConfig()->get('language') : $this->item->language; ?>" />
			<?php if ($this->params->get('show_page_heading', 1)) : ?>
				<div class="page-header">
					<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
				</div>
			<?php endif;
			if (!empty($this->item->pagination) && $this->item->pagination && !$this->item->paginationposition && $this->item->paginationrelative)
			{
				echo $this->item->pagination;
			}
			?>

			<?php if (($params->get('show_title')) || ($params->get('show_author'))) : ?>
				<div class="page-header">
					<h2 itemprop="name">
						<?php if ($params->get('show_title')) : ?>
							<?php if ($params->get('link_titles') && !empty($this->item->readmore_link)) : ?>
								<a href="<?php echo $this->item->readmore_link; ?>" itemprop="url"> <?php echo $this->escape($this->item->title); ?></a>
							<?php else : ?>
								<?php echo $this->escape($this->item->title); ?>
							<?php endif; ?>
						<?php endif; ?>
					</h2>
					<?php if ($this->item->state == 0) : ?>
						<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
					<?php endif; ?>
					<?php if (strtotime($this->item->publish_up) > strtotime(JFactory::getDate())) : ?>
						<span class="label label-warning"><?php echo JText::_('JNOTPUBLISHEDYET'); ?></span>
					<?php endif; ?>
					<?php if ((strtotime($this->item->publish_down) < strtotime(JFactory::getDate())) && $this->item->publish_down != '0000-00-00 00:00:00') : ?>
						<span class="label label-warning"><?php echo JText::_('JEXPIRED'); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ($useDefList || $canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
				<div class="jsn-article-toolbar">
					<?php if ($useDefList && ($info == 0 || $info == 2)) : ?>
						<div class="article-info muted">
							<dl class="article-info">
								<dt class="article-info-term"><?php echo JText::_('COM_CONTENT_ARTICLE_INFO'); ?></dt>

								<?php if ($params->get('show_author') && !empty($this->item->author )) : ?>
									<dd class="createdby" itemprop="author" itemscope itemtype="http://schema.org/Person">
										<?php $author = $this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author; ?>
										<?php $author = '<span itemprop="name">' . $author . '</span>'; ?>
										<?php if (!empty($this->item->contact_link) && $params->get('link_author') == true) : ?>
											<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', JHtml::_('link', $this->item->contact_link, $author, array('itemprop' => 'url'))); ?>
										<?php else: ?>
											<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
										<?php endif; ?>
									</dd>
								<?php endif; ?>
								<?php if ($params->get('show_parent_category') && !empty($this->item->parent_slug)) : ?>
									<dd class="parent-category-name">
										<?php $title = $this->escape($this->item->parent_title); ?>
										<?php if ($params->get('link_parent_category') && !empty($this->item->parent_slug)) : ?>
											<?php $url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->parent_slug)) . '" itemprop="genre">' . $title . '</a>'; ?>
											<?php echo JText::sprintf('COM_CONTENT_PARENT', $url); ?>
										<?php else : ?>
											<?php echo JText::sprintf('COM_CONTENT_PARENT', '<span itemprop="genre">' . $title . '</span>'); ?>
										<?php endif; ?>
									</dd>
								<?php endif; ?>
								<?php if ($params->get('show_category')) : ?>
									<dd class="category-name">
										<?php $title = $this->escape($this->item->category_title); ?>
										<?php if ($params->get('link_category') && $this->item->catslug) : ?>
											<?php $url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug)) . '" itemprop="genre">' . $title . '</a>'; ?>
											<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
										<?php else : ?>
											<?php echo JText::sprintf('COM_CONTENT_CATEGORY', '<span itemprop="genre">' . $title . '</span>'); ?>
										<?php endif; ?>
									</dd>
								<?php endif; ?>

								<?php if ($params->get('show_publish_date')) : ?>
									<dd class="published">
										<span class="icon-calendar"></span>
										<time datetime="<?php echo JHtml::_('date', $this->item->publish_up, 'c'); ?>" itemprop="datePublished">
											<?php echo JText::sprintf('COM_CONTENT_PUBLISHED_DATE_ON', JHtml::_('date', $this->item->publish_up, JText::_('DATE_FORMAT_LC3'))); ?>
										</time>
									</dd>
								<?php endif; ?>

								<?php if ($info == 0) : ?>
									<?php if ($params->get('show_modify_date')) : ?>
										<dd class="modified">
											<span class="icon-calendar"></span>
											<time datetime="<?php echo JHtml::_('date', $this->item->modified, 'c'); ?>" itemprop="dateModified">
												<?php echo JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC3'))); ?>
											</time>
										</dd>
									<?php endif; ?>
									<?php if ($params->get('show_create_date')) : ?>
										<dd class="create">
											<span class="icon-calendar"></span>
											<time datetime="<?php echo JHtml::_('date', $this->item->created, 'c'); ?>" itemprop="dateCreated">
												<?php echo JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC3'))); ?>
											</time>
										</dd>
									<?php endif; ?>

									<?php if ($params->get('show_hits')) : ?>
										<dd class="hits">
											<span class="icon-eye-open"></span>
											<meta itemprop="interactionCount" content="UserPageVisits:<?php echo $this->item->hits; ?>" />
											<?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $this->item->hits); ?>
										</dd>
									<?php endif; ?>
								<?php endif; ?>
							</dl>
						</div>
					<?php endif; ?>
					<?php if (!$this->print) : ?>
						<?php if ($canEdit ||  $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
							<div class="btn-group pull-right">
								<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"> <i class="icon-cog"></i> <span class="caret"></span> </a>
								<?php // Note the actions class is deprecated. Use dropdown-menu instead. ?>
								<ul class="dropdown-menu actions">
									<?php if ($params->get('show_print_icon')) : ?>
										<li class="print-icon"> <?php echo JHtml::_('icon.print_popup',  $this->item, $params); ?> </li>
									<?php endif; ?>
									<?php if ($params->get('show_email_icon')) : ?>
										<li class="email-icon"> <?php echo JHtml::_('icon.email',  $this->item, $params); ?> </li>
									<?php endif; ?>
									<?php if ($canEdit) : ?>
										<li class="edit-icon"> <?php echo JHtml::_('icon.edit', $this->item, $params); ?> </li>
									<?php endif; ?>
								</ul>
							</div>
						<?php endif; ?>
					<?php else : ?>
						<div class="pull-right">
							<?php echo JHtml::_('icon.print_screen',  $this->item, $params); ?>
						</div>
					<?php endif; ?>
					<div class="clearfix"></div>
				</div>
			<?php endif; ?>

			<?php if ($info == 0 && $params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
				<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>

				<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
			<?php endif; ?>

			<?php  if (!$params->get('show_intro')) : echo $this->item->event->afterDisplayTitle; endif; ?>
			<?php echo $this->item->event->beforeDisplayContent; ?>

			<?php if (isset($urls) && ((!empty($urls->urls_position) && ($urls->urls_position == '0')) || ($params->get('urls_position') == '0' && empty($urls->urls_position)))
			|| (empty($urls->urls_position) && (!$params->get('urls_position')))) : ?>
			<?php echo $this->loadTemplate('links'); ?>
		<?php endif; ?>
		<?php if ($params->get('access-view')):?>
			<?php if (isset($images->image_fulltext) && !empty($images->image_fulltext)) : ?>
				<?php $imgfloat = (empty($images->float_fulltext)) ? $params->get('float_fulltext') : $images->float_fulltext; ?>
				<div class="pull-<?php echo htmlspecialchars($imgfloat); ?> item-image"> <img
					<?php if ($images->image_fulltext_caption):
					echo 'class="caption"'.' title="' .htmlspecialchars($images->image_fulltext_caption) . '"';
					endif; ?>
					src="<?php echo htmlspecialchars($images->image_fulltext); ?>" alt="<?php echo htmlspecialchars($images->image_fulltext_alt); ?>" itemprop="image"/> </div>
				<?php endif; ?>
				<?php
				if (!empty($this->item->pagination) && $this->item->pagination && !$this->item->paginationposition && !$this->item->paginationrelative):
					echo $this->item->pagination;
				endif;
				?>
				<?php if (isset ($this->item->toc)) :
				echo $this->item->toc;
				endif; ?>
				<div itemprop="articleBody">
					<?php echo $this->item->text; ?>
				</div>

				<?php
				if (!empty($this->item->pagination) && $this->item->pagination && $this->item->paginationposition && !$this->item->paginationrelative):
					echo $this->item->pagination;
				?>
			<?php endif; ?>
			<?php if (isset($urls) && ((!empty($urls->urls_position) && ($urls->urls_position == '1')) || ($params->get('urls_position') == '1'))) : ?>
				<?php echo $this->loadTemplate('links'); ?>
			<?php endif; ?>
			<?php // Optional teaser intro text for guests ?>
		<?php elseif ($params->get('show_noauth') == true && $user->get('guest')) : ?>
			<?php echo $this->item->introtext; ?>
			<?php //Optional link to let them register to see the whole article. ?>
			<?php if ($params->get('show_readmore') && $this->item->fulltext != null) :
			$link1 = JRoute::_('index.php?option=com_users&view=login');
			$link = new JUri($link1);?>
			<p class="readmore">
				<a href="<?php echo $link; ?>">
					<?php $attribs = json_decode($this->item->attribs); ?>
					<?php
					if ($attribs->alternative_readmore == null) :
						echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
					elseif ($readmore = $this->item->alternative_readmore) :
						echo $readmore;
					if ($params->get('show_readmore_title', 0) != 0) :
						echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
					endif;
					elseif ($params->get('show_readmore_title', 0) == 0) :
						echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
					else :
						echo JText::_('COM_CONTENT_READ_MORE');
					echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
					endif; ?>
				</a>
			</p>
		<?php endif; ?>
	<?php endif; ?>
	<?php
	if (!empty($this->item->pagination) && $this->item->pagination && $this->item->paginationposition && $this->item->paginationrelative) :
		echo $this->item->pagination;
	?>
<?php endif; ?>
<?php echo $this->item->event->afterDisplayContent; ?> </div>
<?php // End override HTML for J3 ?>

<?php else : ?>
	<?php
	$showParentCategory = $params->get('show_parent_category') && $this->item->parent_slug != '1:root';
	$showCategory = $params->get('show_category');
	$showInfo = ($params->get('show_author') OR $params->get('show_create_date') OR $params->get('show_publish_date') OR $params->get('show_hits'));
	$showTools = ($params->get('show_print_icon') || $canEdit || ($this->params->get( 'show_print_icon' ) || $this->params->get('show_email_icon')));
	?>
	<div class="com-content <?php echo $this->pageclass_sfx; ?>">
		<div class="article">
			<?php if ($this->params->get('show_page_heading', 1)) : ?>
				<h2 class="componentheading">
					<?php echo $this->escape($this->params->get('page_heading')); ?>
				</h2>
			<?php endif; ?>
			
			<?php if ($showParentCategory || $showCategory) : ?>
				<div class="jsn-article-metadata">
					<?php if ($showParentCategory) : ?>
						<span class="parent-category-name">
							<?php	$title = $this->escape($this->item->parent_title);
							$url = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->parent_slug)).'">'.$title.'</a>';?>
							<?php if ($params->get('link_parent_category') AND $this->item->parent_slug) : ?>
								<?php echo JText::sprintf('COM_CONTENT_PARENT', $url); ?>
							<?php else : ?>
								<?php echo JText::sprintf('COM_CONTENT_PARENT', $title); ?>
							<?php endif; ?>
						</span>
					<?php endif; ?>			
					<?php if ($showCategory) : ?>
						<span class="category-name">
							<?php 	$title = $this->escape($this->item->category_title);
							$url = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug)).'">'.$title.'</a>';?>
							<?php if ($params->get('link_category') AND $this->item->catslug) : ?>
								<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
							<?php else : ?>
								<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
							<?php endif; ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>	
			
			<?php if ($params->get('show_title')) : ?>
				<h2 class="contentheading">
					<?php if ($params->get('link_titles') && !empty($this->item->readmore_link)) : ?>
						<a href="<?php echo $this->item->readmore_link; ?>">
							<?php echo $this->escape($this->item->title); ?></a>
						<?php else : ?>
							<?php echo $this->escape($this->item->title); ?>
						<?php endif; ?>
					</h2>
				<?php endif; ?>
				
				<?php  if (!$params->get('show_intro')) :
				echo $this->item->event->afterDisplayTitle;
				endif; ?>

				<?php if ($showInfo || $showTools) : ?>
					<div class="jsn-article-toolbar">
						<?php if ($showTools) : ?>
							<ul class="jsn-article-tools pull-right">
								<?php if (!$this->print) : ?>
									<?php if ($params->get('show_print_icon')) : ?>
										<li class="jsn-article-print-button">
											<?php echo JHtml::_('icon.print_popup',  $this->item, $params); ?>
										</li>
									<?php endif; ?>
									<?php if ($params->get('show_email_icon')) : ?>
										<li class="jsn-article-email-button">
											<?php echo JHtml::_('icon.email',  $this->item, $params); ?>
										</li>
									<?php endif; ?>				
									<?php if ($canEdit) : ?>
										<li class="jsn-article-icon-edit">
											<?php echo JHtml::_('icon.edit', $this->item, $params); ?>
										</li>
									<?php endif; ?>				
								<?php else : ?>
									<li class="jsn-article-print-button">
										<?php echo JHtml::_('icon.print_screen',  $this->item, $params); ?>
									</li>
								<?php endif; ?>			
							</ul>
						<?php endif; ?>	
						
						<?php if ($showInfo) : ?>
							<div class="jsn-article-info">
								<?php if ($params->get('show_author') && !empty($this->item->author )) : ?>
									<p class="small author">
										<?php $author =  $this->item->author; ?>
										<?php $author = ($this->item->created_by_alias ? $this->item->created_by_alias : $author);?>

										<?php if (!empty($this->item->contactid ) &&  $params->get('link_author') == true):?>
											<?php 	echo JText::sprintf('COM_CONTENT_WRITTEN_BY' , 
											JHTML::_('link',JRoute::_('index.php?option=com_contact&view=contact&id='.$this->item->contactid),$author)); ?>

										<?php else :?>
											<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
										<?php endif; ?>
									</p>
								<?php endif; ?>	
								<?php if ($params->get('show_create_date')) : ?>
									<p class="createdate">
										<?php echo JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHTML::_('date',$this->item->created, JText::_('DATE_FORMAT_LC2'))); ?>
									</p>
								<?php endif; ?>
								<?php if ($params->get('show_publish_date')) : ?>
									<p class="publishdate">
										<?php echo JText::sprintf('COM_CONTENT_PUBLISHED_DATE_ON', JHTML::_('date',$this->item->publish_up, JText::_('DATE_FORMAT_LC2'))); ?>
									</p>
								<?php endif; ?>			
								<?php if ($params->get('show_hits')) : ?>
									<p class="hits">
										<?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $this->item->hits); ?>
									</p>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="clearbreak"></div>
					</div>
				<?php endif; ?>
				
				<?php echo $this->item->event->beforeDisplayContent; ?>
				
				<div class="jsn-article-content">
					<?php if (isset ($this->item->toc)) : ?>
						<?php echo $this->item->toc; ?>
					<?php endif; ?>
					<?php if (isset($urls) AND ((!empty($urls->urls_position) AND ($urls->urls_position=='0')) OR  ($params->get('urls_position')=='0' AND empty($urls->urls_position) ))
					OR (empty($urls->urls_position) AND (!$params->get('urls_position')))): ?>
					<?php echo $this->loadTemplate('links'); ?>
				<?php endif; ?>
				
				<?php if ($params->get('access-view')):?>
					<?php if (isset($images->image_fulltext) and !empty($images->image_fulltext)) : ?>
						<?php $imgfloat = (empty($images->float_fulltext)) ? $params->get('float_fulltext') : $images->float_fulltext; ?>
						<div class="img-fulltext-<?php echo htmlspecialchars($imgfloat); ?>">
							<img
							<?php if ($images->image_fulltext_caption):
							echo 'class="caption"'.' title="' .htmlspecialchars($images->image_fulltext_caption) .'"';
							endif; ?>
							src="<?php echo htmlspecialchars($images->image_fulltext); ?>" alt="<?php echo htmlspecialchars($images->image_fulltext_alt); ?>"/>
						</div>
					<?php endif; ?>
					<?php echo $this->item->text; ?>
					<?php 
				//optional teaser intro text for guests
					elseif ($params->get('show_noauth') == true AND  $user->get('guest') ) : 
						?>
					<?php echo $this->item->introtext; ?>
					<?php //Optional link to let them register to see the whole article. ?>
					<?php if ($params->get('show_readmore') && $this->item->fulltext != null) :
					$link1 = JRoute::_('index.php?option=com_users&view=login');
					$link = new JURI($link1);?>
					<a href="<?php echo $link; ?>" class="readon">
						<?php $attribs = json_decode($this->item->attribs);  ?> 
						<?php 
						if ($attribs->alternative_readmore == null) :
							echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
						elseif ($readmore = $this->item->alternative_readmore) :
							echo $readmore;
						if ($params->get('show_readmore_title', 0) != 0) :
							echo JHTML::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
						endif;
						elseif ($params->get('show_readmore_title', 0) == 0) :
							echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');	
						else :
							echo JText::_('COM_CONTENT_READ_MORE');
						echo JHTML::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
						endif; ?></a>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ($params->get('show_modify_date')) : ?>
					<p class="modifydate">
						<?php echo JText::sprintf('COM_CONTENT_LAST_UPDATED', JHTML::_('date',$this->item->modified, JText::_('DATE_FORMAT_LC2'))); ?>
					</p>
				<?php endif; ?>
			</div>
			<div class="clearbreak"></div>
			<?php
		// Pagenavigation config
			if (!empty($this->item->pagination) AND $this->item->pagination AND $this->item->paginationposition AND!$this->item->paginationrelative):
				echo $this->item->pagination;?>
		<?php endif; ?>
		<?php echo $this->item->event->afterDisplayContent; ?>
	</div>
</div>
<?php endif; ?>