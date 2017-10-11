/**
 * PageTemplates extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @version    2.22.0
 * @package    Bluespice_Extensions
 * @subpackage PageTemplates
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.onReady( function(){
	Ext.Loader.setPath(
		'BS.PageTemplates',
		bs.em.paths.get( 'BlueSpicePageTemplates' ) + '/resources/BS.PageTemplates'
	);
	Ext.create( 'BS.PageTemplates.Panel', {
		renderTo: 'bs-pagetemplates-grid'
	} );
} );