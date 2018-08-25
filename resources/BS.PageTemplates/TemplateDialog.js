/**
 * PageTemplates TemplateDialog
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage NamespaceManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

Ext.define( 'BS.PageTemplates.TemplateDialog', {
	extend: 'MWExt.Dialog',

	currentData: {},
	selectedData: {},

	initComponent: function() {
		this.callParent( arguments );
		this.btnOK.disable();
	},

	makeItems: function() {
		this.tfLabel = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-pagetemplates-label-tpl' ).plain(),
			name: 'namespacename',
			allowBlank: false
		});
		this.taDesc = Ext.create( 'Ext.form.field.TextArea', {
			fieldLabel: mw.message( 'bs-pagetemplates-label-desc' ).plain(),
			name: 'ta-desc',
			checked: true,
			allowBlank: false
		});
		this.cbTargetNamespace = Ext.create( 'BS.form.NamespaceCombo', {
			fieldLabel: mw.message( 'bs-pagetemplates-label-targetns' ).plain(),
			includeAll: true,
			allowBlank: false
		} );

		this.cbTemplate = Ext.create( 'BS.form.field.TitleCombo', {
			fieldLabel: mw.message( 'bs-pagetemplates-label-article' ).plain(),
			allowBlank: false
		});

		return [
			this.tfLabel,
			this.taDesc,
			this.cbTargetNamespace,
			this.cbTemplate
		];
	},
	storePagesReload: function( combo, records, eOpts ) {
		this.strPages.load( { params: { ns: records[0].get( 'id' ) } } );
	},
	onBtnOKClick: function() {
		this.fireEvent( 'ok', this, this.getData() );
	},
	resetData: function() {
		this.tfLabel.reset();
		this.taDesc.reset();
		this.cbTargetNamespace.reset();
		this.cbTemplate.reset();

		this.callParent();
	},
	setData: function( obj ) {
		this.currentData = obj;

		this.tfLabel.setValue( this.currentData.label );
		this.taDesc.setValue( this.currentData.desc );
		this.cbTargetNamespace.setValue( this.currentData.targetns );
		this.cbTemplate.setValue( this.currentData.templatename );
	},
	getData: function() {
		var selectedTemplate = this.cbTemplate.getValue();

		this.selectedData.id = this.currentData.id;
		this.selectedData.label = this.tfLabel.getValue();
		this.selectedData.desc = this.taDesc.getValue();
		this.selectedData.targetns = this.cbTargetNamespace.getValue();
		this.selectedData.template = selectedTemplate.getPrefixedText();

		return this.selectedData;
	}
} );