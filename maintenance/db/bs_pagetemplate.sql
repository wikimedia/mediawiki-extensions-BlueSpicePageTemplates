-- Database definition for PageTemplates
--
-- Part of BlueSpice MediaWiki
--
-- @author     Markus Glaser <glaser@hallowelt.com>

-- @package    BlueSpice_Extensions
-- @subpackage PageTemplates
-- @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
-- @filesource

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_pagetemplate (
  pt_id                 int(10) unsigned    NOT NULL PRIMARY KEY AUTO_INCREMENT,
  pt_label              varchar(255)        NOT NULL DEFAULT '',
  pt_desc               varchar(255)        NOT NULL DEFAULT '',
  pt_target_namespace   int(11)             NOT NULL DEFAULT -99,
  pt_template_title     varbinary(255)      NOT NULL DEFAULT '', /* foreign key to page_title */
  pt_template_namespace int(11)             NOT NULL DEFAULT 0,  /* foreign key to page_namespace */
  pt_sid                int(10) unsigned    NOT NULL DEFAULT 0
)/*$wgDBTableOptions*/;
