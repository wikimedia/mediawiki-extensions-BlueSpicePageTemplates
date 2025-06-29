-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: extensions/BlueSpicePageTemplates/db/bs_pagetemplate.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/bs_pagetemplate (
  pt_id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  pt_label BLOB DEFAULT '' NOT NULL, pt_desc BLOB DEFAULT '' NOT NULL,
  pt_target_namespace BLOB DEFAULT '[-99]' NOT NULL,
  pt_template_title BLOB DEFAULT '' NOT NULL,
  pt_template_namespace INTEGER DEFAULT 0 NOT NULL,
  pt_sid INTEGER UNSIGNED DEFAULT 0 NOT NULL,
  pt_tags BLOB DEFAULT NULL
);
