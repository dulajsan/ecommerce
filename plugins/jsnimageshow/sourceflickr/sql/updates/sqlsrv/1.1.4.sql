SET QUOTED_IDENTIFIER ON;
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[#__imageshow_external_source_flickr]') AND type in (N'U'))
BEGIN
ALTER TABLE [#__imageshow_external_source_flickr] ADD [flickr_thumbnail_size] [nvarchar](30) DEFAULT '100'
END;