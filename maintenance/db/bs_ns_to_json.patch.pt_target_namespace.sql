-- Change target_namespace type from integer to text (for storing json)
ALTER TABLE /*$wgDBprefix*/bs_pagetemplate MODIFY `pt_target_namespace` TEXT