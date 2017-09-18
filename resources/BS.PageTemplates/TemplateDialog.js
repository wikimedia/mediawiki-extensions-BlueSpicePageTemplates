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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.PageTemplates.TemplateDialog', {
	extend: 'BS.Window',
	currentData: {},
	selectedData: {},
	afterInitComponent: function() {
		this.tfLabel = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-pagetemplates-label-tpl' ).plain(),
			labelWidth: 135,
			labelAlign: 'right',
			name: 'namespacename',
			allowBlank: false
		});
		this.taDesc = Ext.create( 'Ext.form.field.TextArea', {
			fieldLabel: mw.message( 'bs-pagetemplates-label-desc' ).plain(),
			labelWidth: 135,
			labelAlign: 'right',
			name: 'ta-desc',
			checked: true,
			allowBlank: false
		});
		this.cbTragetNamespace = Ext.create( 'BS.form.NamespaceCombo', {
			labelWidth: 135,
			fieldLabel: mw.message( 'bs-pagetemplates-label-targetns' ).plain(),
			includeAll: true
		} );

		this.cbTemplate = Ext.create( 'BS.form.field.TitleCombo', {
			fieldLabel: mw.message( 'bs-pagetemplates-label-article' ).plain(),
			labelAlign: 'right'
		});

		this.items = [
			this.tfLabel,
			this.taDesc,
			this.cbTragetNamespace,
			this.cbTemplate
		];

		this.callParent(arguments);
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
		this.cbTragetNamespace.reset();
		this.cbTemplate.reset();

		this.callParent();
	},
	setData: function( obj ) {
		this.currentData = obj;

		this.tfLabel.setValue( this.currentData.label );
		this.taDesc.setValue( this.currentData.desc );
		this.cbTragetNamespace.setValue( this.currentData.targetns );
		this.cbTemplate.setValue( this.currentData.templatename );
	},
	getData: function() {
		this.selectedData.id = this.currentData.id;
		this.selectedData.label = this.tfLabel.getValue();
		this.selectedData.desc = this.taDesc.getValue();
		this.selectedData.targetns = this.cbTragetNamespace.getValue();
		this.selectedData.template = this.cbTemplate.getValue().getPrefixedText();

		return this.selectedData;
	}
} );