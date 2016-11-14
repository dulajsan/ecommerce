SET QUOTED_IDENTIFIER ON;
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__imageshow_external_source_flickr]') AND type in (N'U'))
BEGIN
CREATE TABLE [#__imageshow_external_source_flickr](
	[external_source_id] [int] IDENTITY(1,1) NOT NULL,  
    [external_source_profile_title] [nvarchar](255) NULL, 
    [flickr_api_key] [nvarchar](255) NULL,
    [flickr_secret_key] [nvarchar](255) NULL,
    [flickr_username] [nvarchar](255) NULL,
	[flickr_caching] [nvarchar](255) NULL,
	[flickr_cache_expiration] [nvarchar](255) NULL,
	[flickr_thumbnail_size] [nvarchar](30) DEFAULT '100',
	[flickr_image_size] [smallint] DEFAULT '0'
 CONSTRAINT [PK_#__imageshow_external_source_flickr_external_source_id] PRIMARY KEY CLUSTERED 
(
	[external_source_id] ASC
)WITH (STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF)
)
END;
