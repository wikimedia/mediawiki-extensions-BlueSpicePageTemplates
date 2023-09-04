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
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
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
		this.cbTemplateTags = new Ext.form.field.Tag( {
			fieldLabel: mw.message( 'bs-pagetemplates-label-tags' ).plain(),
			emptyText: mw.message( 'bs-pagetemplates-placeholder-tags' ).plain(),
			createNewOnEnter: true,
			forceSelection: false,
			labelAlign: 'right',
			multiSelect: true,
			queryMode: 'remote',
			collapseOnSelect: true,
			autoSelect: true,
			typeAhead: false,
			store: new BS.store.BSApi( {
				apiAction: 'bs-pagetemplate-tags-store',
				fields: [ 'text' ]
			} ),
			valueField: "text",
			allowBlank: false,
			createNewOnEnter: false,
			listeners: {
				blur: function ( field ) {
					var inputEl = field.inputEl.dom;
					var enteredText = inputEl.value.trim();
					if ( enteredText !== "" ) {
						var tag = { text: enteredText };
						field.getStore().add( tag );
					}
				}
			}
		});

		this.cbTargetNamespace = Ext.create( 'BS.form.field.NamespaceTag', {
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
			this.cbTemplateTags,
			this.cbTargetNamespace,
			this.cbTemplate
		];
	},
	show: function() {
		this.callParent( arguments );
		var tagsLabel = Ext.get( 'tagfield-1033-placeholderLabel' );
		if ( tagsLabel ) {
			tagsLabel.addCls( 'x-form-text-default' );
		}
		var tagsDropdownArrow = document.getElementById('tagfield-1033-trigger-picker');
		if ( tagsDropdownArrow ) {
			tagsDropdownArrow.style.display = 'none';
		}
		var namespaceLabel = Ext.get( 'ext-comp-1034-placeholderLabel' );
		if ( namespaceLabel ) {
			namespaceLabel.addCls( 'x-form-text-default' );
		}
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
		this.cbTemplateTags.reset();
		this.cbTargetNamespace.reset();
		this.cbTemplate.reset();

		this.callParent();
	},
	setData: function( obj ) {
		this.currentData = obj;

		this.tfLabel.setValue( this.currentData.label );
		this.taDesc.setValue( this.currentData.desc );
		this.cbTemplateTags.setValue( this.currentData.tags );
		this.cbTargetNamespace.setValue( this.currentData.targetnsid );
		this.cbTemplate.setValue( this.currentData.templatename );
	},
	getData: function() {
		var selectedTemplate = this.cbTemplate.getValue();

		this.selectedData.id = this.currentData.id;
		this.selectedData.label = this.tfLabel.getValue();
		this.selectedData.desc = this.taDesc.getValue();
		this.selectedData.tags = this.cbTemplateTags.getValue().map(tag => tag.trim());
		this.selectedData.targetns = this.cbTargetNamespace.getValue();
		this.selectedData.template = selectedTemplate.getPrefixedText();

		return this.selectedData;
	}
} );