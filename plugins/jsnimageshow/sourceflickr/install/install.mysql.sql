CREATE TABLE IF NOT EXISTS `#__imageshow_external_source_flickr` (
  `external_source_id` int(11) unsigned NOT NULL auto_increment,
  `external_source_profile_title` varchar(255) default NULL,
  `flickr_api_key` char(150) default NULL,
  `flickr_secret_key` char(150) default NULL,
  `flickr_username` char(50) default NULL,
  `flickr_caching` tinyint(1) default '0',
  `flickr_cache_expiration` char(30) default NULL,
  `flickr_thumbnail_size` char(30) default '100',
  `flickr_image_size` tinyint(2) default '0',
  PRIMARY KEY  (`external_source_id`)
) DEFAULT CHARSET=utf8;