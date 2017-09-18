-- Database definition for PageTemplates
--
-- Part of BlueSpice MediaWiki
--
-- @author     Markus Glaser <glaser@hallowelt.com>

-- @package    BlueSpice_Extensions
-- @subpackage PageTemplates
-- @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

/* CREATE SEQUENCE pt_id_seq MINVALUE 1 START WITH 1; */
CREATE TABLE /*$wgDBprefix*/bs_pagetemplate (
  pt_id                 serial          NOT NULL,
  pt_label              varchar(255)    NOT NULL DEFAULT '',
  pt_desc               varchar(255)    NOT NULL DEFAULT '',
  pt_target_namespace   int             NOT NULL DEFAULT -99,
  pt_template_title     text            NOT NULL DEFAULT '', /* foreign key to page_title */
  pt_template_namespace int             NOT NULL DEFAULT 0,  /* foreign key to page_namespace */
  pt_sid                int             NOT NULL DEFAULT 0
);
