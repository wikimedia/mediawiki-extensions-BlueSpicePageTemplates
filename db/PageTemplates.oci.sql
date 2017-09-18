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

CREATE SEQUENCE pagetemplates_pt_id_seq MINVALUE 0 START WITH 0;
CREATE TABLE /*$wgDBprefix*/bs_pagetemplate (
	pt_id NUMBER NOT NULL,
	pt_label VARCHAR2(255)    NOT NULL,
	pt_desc VARCHAR2(255)    NOT NULL,
	pt_target_namespace NUMBER NOT NULL,
	pt_template_title LONG NOT NULL,
	pt_template_namespace NUMBER NOT NULL,
	pt_sid NUMBER NOT NULL
);
ALTER TABLE /*$wgDBprefix*/bs_pagetemplate ADD CONSTRAINT /*$wgDBprefix*/bs_pagetemplate_pk PRIMARY KEY (pt_id);

/*$mw$*/
CREATE OR REPLACE TRIGGER /*$wgDBprefix*/bs_pt_id_increment
BEFORE INSERT ON /*$wgDBprefix*/bs_pagetemplate
FOR EACH ROW
BEGIN
	SELECT pagetemplates_pt_id_seq.nextval INTO :new.pt_id from dual;
END;
/*$mw$*/

