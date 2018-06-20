/**
 * PageTemplates Panel
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage PageTemplates
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

Ext.define( 'BS.PageTemplates.Panel', {
	extend: 'BS.CRUDGridPanel',
	initComponent: function() {
		this.strMain = Ext.create( 'BS.store.BSApi', {
			apiAction: 'bs-pagetemplates-store',
			autoLoad: true,
			remoteSort: true,
			fields: [ 'id', 'label', 'desc', 'targetns', 'targetnsid', 'template', 'templatename' ],
			sortInfo: {
				field: 'id',
				direction: 'ASC'
			}
		});

		this.colLabel = Ext.create( 'Ext.grid.column.Template', {
			id: 'pg-label',
			header: mw.message('bs-pagetemplates-headerlabel').plain(),
			sortable: true,
			filter: {
				type: 'string'
			},
			dataIndex: 'label',
			tpl: '{label}'
		} );
		this.colDesc = Ext.create( 'Ext.grid.column.Column', {
			id: 'pg-desc',
			header: mw.message('bs-pagetemplates-label-desc').plain(),
			xtype: 'templatecolumn',
			sortable: true,
			filter: {
				type: 'string'
			},
			dataIndex: 'desc'
		} );
		this.colTargetns = Ext.create( 'Ext.grid.column.Column', {
			id: 'pg-targetns',
			header: mw.message('bs-pagetemplates-headertargetnamespace').plain(),
			xtype: 'templatecolumn',
			sortable: true,
			filter: {
				type: 'list'
			},
			dataIndex: 'targetns'
		} );
		this.colTemplate = Ext.create( 'Ext.grid.column.Column', {
			id: 'pg-template',
			header: mw.message('bs-pagetemplates-label-article').plain(),
			xtype: 'templatecolumn',
			sortable: true,
			filter: {
				type: 'string'
			},
			dataIndex: 'template'
		} );

		this.colMainConf.columns = [
			this.colLabel,
			this.colDesc,
			this.colTargetns,
			this.colTemplate
		];
		this.callParent( arguments );
	},
	makeSelModel: function(){
		this.smModel = Ext.create( 'Ext.selection.CheckboxModel', {
			mode: "MULTI",
			selType: 'checkboxmodel'
		});
		return this.smModel;
	},
	onBtnAddClick: function( oButton, oEvent ) {
		if ( !this.dlgTemplateAdd ) {
			this.dlgTemplateAdd = Ext.create( 'BS.PageTemplates.TemplateDialog' );
			this.dlgTemplateAdd.on( 'ok', this.onDlgTemplateAddOk, this );
		}

		this.active = 'add';
		this.dlgTemplateAdd.setTitle( mw.message( 'bs-pagetemplates-tipaddtemplate' ).plain() );
		this.dlgTemplateAdd.show();
		this.callParent( arguments );
	},
	onBtnEditClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		if ( !this.dlgTemplateEdit ) {
			this.dlgTemplateEdit = Ext.create( 'BS.PageTemplates.TemplateDialog' );
			this.dlgTemplateEdit.on( 'ok', this.onDlgTemplateEditOk, this );
		}

		this.active = 'edit';

		var editable = selectedRow[0].get( 'editable' );
		if ( editable === false ) {
			this.dlgTemplateEdit.tfNamespaceName.disable();
		}

		this.dlgTemplateEdit.setTitle( mw.message( 'bs-pagetemplates-tipeditdetails' ).plain() );
		this.dlgTemplateEdit.setData( selectedRow[0].getData() );
		this.dlgTemplateEdit.show();
		this.callParent( arguments );
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		bs.util.confirm(
			'PTremove',
			{
				text: mw.message( 'bs-pagetemplates-confirm-deletetpl', selectedRow.length ).text(),
				title: mw.message( 'bs-pagetemplates-tipdeletetemplate', selectedRow.length ).text()
			},
			{
				ok: this.onDlgTemplateRemoveOk,
				cancel: function() {},
				scope: this
			}
		);
	},
	onDlgTemplateAddOk: function( data, template ) {
		this.dlgTemplateAdd.setLoading();
		var me = this;

		//copy from bluespice.api.js, cause we need to set "loading" to false
		var cfg = {
			failure: function( response, module, task, $dfd, cfg ) {
				var message = response.message || '';
				if ( response.errors && response.errors.length > 0 ) {
					for ( var i in response.errors ) {
						if ( typeof( response.errors[i].message ) !== 'string' ) continue;
						message = message + '<br />' + response.errors[i].message;
					}
				}
				bs.util.alert( module + '-' + task + '-fail', {
						titleMsg: 'bs-extjs-title-warning',
						text: message
					}, {
						ok: function() {
							me.dlgTemplateAdd.setLoading( false );
					}}
				);
			}
		};
		bs.api.tasks.exec(
			'pagetemplates',
			'doEditTemplate',
			template,
			cfg
		).done( function() {
			me.dlgTemplateAdd.setLoading(false);
			me.dlgTemplateAdd.resetData();
			me.dlgTemplateAdd.close();
			me.strMain.reload();
		});
	},
	onDlgTemplateEditOk: function( data, template ) {
		this.dlgTemplateEdit.setLoading();
		var me = this;
		//copy from bluespice.api.js, cause we need to set "loading" to false
		var cfg = {
			failure: function( response, module, task, $dfd, cfg ) {
				var message = response.message || '';
				if ( response.errors.length > 0 ) {
					for ( var i in response.errors ) {
						if ( typeof( response.errors[i].message ) !== 'string' ) continue;
						message = message + '<br />' + response.errors[i].message;
					}
				}
				bs.util.alert( module + '-' + task + '-fail', {
						titleMsg: 'bs-extjs-title-warning',
						text: message
					}, {
						ok: function() {
							me.dlgTemplateAdd.setLoading( false );
					}}
				);
			}
		};
		bs.api.tasks.exec(
			'pagetemplates',
			'doEditTemplate',
			template,
			cfg
		).done( function() {
			me.dlgTemplateEdit.setLoading(false);
			me.dlgTemplateEdit.resetData();
			me.dlgTemplateEdit.close();
			me.strMain.reload();
		});
	},
	onDlgTemplateRemoveOk: function( data, namespace ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var ptIds = {};
		for (var i = 0; i < selectedRow.length; i++){
			ptIds[selectedRow[i].get( 'id' )] = selectedRow[i].get( 'label' );
		}
		var me = this;
		bs.api.tasks.exec(
			'pagetemplates',
			'doDeleteTemplates',
			{ "ids": ptIds }
		).done( function() {
			me.strMain.reload();
		});
	},
	reloadStore: function() {
		this.strMain.reload();
	},
	showDlgAgain: function() {
		if ( this.active === 'add' ) {
			this.dlgTemplateAdd.show();
		} else {
			this.dlgTemplateEdit.show();
		}
	}
} );
