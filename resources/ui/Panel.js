bs.util.registerNamespace( 'bs.pageTemplates.ui' );

bs.pageTemplates.ui.PageTemplatesPanel = function ( cfg ) {
	cfg = cfg || {};

	const columns = {
		label: {
			type: 'text',
			headerText: mw.message( 'bs-pagetemplates-headerlabel' ).text(),
			filter: { type: 'string' },
			sortable: true
		},
		desc: {
			type: 'text',
			headerText: mw.message( 'bs-pagetemplates-label-desc' ).text(),
			filter: { type: 'text' }
		},
		targetns: {
			type: 'text',
			headerText: mw.message( 'bs-pagetemplates-headertargetnamespace' ).text(),
			filter: { type: 'text' },
			sortable: true
		},
		template: {
			type: 'text',
			headerText: mw.message( 'bs-pagetemplates-label-article' ).text(),
			filter: { type: 'text' },
			sortable: true,
			valueParser: function ( value ) {
				return new OO.ui.HtmlSnippet( value );
			}
		},
		tags: {
			type: 'text',
			headerText: mw.message( 'bs-pagetemplates-headertags' ).text(),
			filter: { type: 'text' },
			sortable: true,
			valueParser: function ( value ) {
				return new OO.ui.HtmlSnippet( value );
			}
		}
	};

	columns.actionEdit = {
		type: 'action',
		title: mw.message( 'bs-pagetemplates-tipeditdetails' ).text(),
		actionId: 'edit',
		icon: 'edit',
		headerText: mw.message( 'bs-pagetemplates-tipeditdetails' ).text(),
		invisibleHeader: true,
		width: 30,
		visibleOnHover: true
	};
	columns.actionDelete = {
		type: 'action',
		title: mw.message( 'bs-pagetemplates-tipdeletetemplate' ).text(),
		actionId: 'delete',
		icon: 'trash',
		headerText: mw.message( 'bs-pagetemplates-tipdeletetemplate' ).text(),
		invisibleHeader: true,
		width: 30,
		visibleOnHover: true
	};
	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-pagetemplates-store'
	} );
	this.store.connect( this, {
		reload: function () {
			this.setAbilitiesOnSelection( null );
		}
	} );
	cfg.grid = {
		store: this.store,
		columns: columns,
		multiSelect: true,
		exportable: true,
		provideExportData: function () {
			const dfd = $.Deferred(),
				store = new OOJSPlus.ui.data.store.RemoteStore( {
					action: 'bs-pagetemplates-store',
					limit: 9999,
					pageSize: 9999
				} );
			store.load().done( ( response ) => {
				const $table = $( '<table>' );
				let $row = $( '<tr>' );

				for ( const key in columns ) {
					if ( columns.hasOwnProperty( key ) ) {
						const column = columns[ key ];
						if ( column.type === 'action' ) {
							continue;
						}
						const $cell = $( '<th>' );
						$cell.append( column.headerText );
						$row.append( $cell );
					}
				}

				$table.append( $row );
				for ( const id in response ) {
					if ( !response.hasOwnProperty( id ) ) {
						continue;
					}
					$row = $( '<tr>' );
					const record = response[ id ];
					for ( const key in columns ) {
						if ( !columns.hasOwnProperty( key ) ) {
							continue;
						}
						const column = columns[ key ];
						if ( column.type === 'action' ) {
							continue;
						}
						const $cell = $( '<td>' );
						$cell.append( record[ key ] );
						$row.append( $cell );
					}
					$table.append( $row );
				}

				dfd.resolve( '<table>' + $table.html() + '</table>' );
			} ).fail( () => {
				dfd.reject( 'Failed to load data' );
			} );

			return dfd.promise();
		}
	};
	bs.pageTemplates.ui.PageTemplatesPanel.parent.call( this, cfg );
};

OO.inheritClass( bs.pageTemplates.ui.PageTemplatesPanel, OOJSPlus.ui.panel.ManagerGrid );

bs.pageTemplates.ui.PageTemplatesPanel.prototype.getToolbarActions = function () {
	const actions = [];
	actions.push( this.getAddAction( { icon: 'add', flags: [ 'progressive' ], displayBothIconAndLabel: true } ) );
	actions.push( this.getEditAction( { displayBothIconAndLabel: true } ) );
	actions.push( this.getDeleteAction( { displayBothIconAndLabel: true } ) );
	return actions;
};

bs.pageTemplates.ui.PageTemplatesPanel.prototype.onAction = function ( action, row ) {
	const selected = this.grid.getSelectedRows();
	if ( action === 'edit' && ( selected.length === 1 || row ) ) {
		this.editTemplate( row || selected[ 0 ] );
	}
	if ( action === 'add' ) {
		this.addTemplate();
	}
	if ( action === 'delete' && ( selected.length > 0 || row ) ) {
		this.deleteTemplate( row ? [ row ] : selected );
	}
};

bs.pageTemplates.ui.PageTemplatesPanel.prototype.getInitialAbilities = function () {
	return {
		add: true,
		edit: false,
		delete: false
	};
};

bs.pageTemplates.ui.PageTemplatesPanel.prototype.onItemSelected = function ( item, selectedItems ) {
	this.setAbilitiesOnSelection( selectedItems || [] );
};

bs.pageTemplates.ui.PageTemplatesPanel.prototype.setAbilitiesOnSelection = function ( selectedItems ) {
	selectedItems = selectedItems || [];
	if ( selectedItems.length === 1 ) {
		this.setAbilities( { add: true, edit: true, delete: true } );
	} else if ( selectedItems.length > 1 ) {
		this.setAbilities( { add: true, edit: false, delete: true } );
	} else {
		this.setAbilities( { add: true, edit: false, delete: false } );
	}
};

bs.pageTemplates.ui.PageTemplatesPanel.prototype.addTemplate = function () {
	const dialog = new bs.pageTemplates.ui.dialog.EditTemplate( {
		isCreation: true
	} );
	this.openWindow( dialog );
};

bs.pageTemplates.ui.PageTemplatesPanel.prototype.editTemplate = function ( row ) {
	const dialog = new bs.pageTemplates.ui.dialog.EditTemplate( {
		isCreation: false,
		item: row
	} );
	this.openWindow( dialog );
};

bs.pageTemplates.ui.PageTemplatesPanel.prototype.deleteTemplate = function ( rows ) {
	const dialog = new bs.pageTemplates.ui.dialog.DeleteTemplateDialog( {
		ids: rows.map( ( row ) => row.id )
	} );
	this.openWindow( dialog );
};

bs.pageTemplates.ui.PageTemplatesPanel.prototype.openWindow = function ( dialog ) {
	if ( !this.windowManager ) {
		this.windowManager = new OO.ui.WindowManager();
		$( 'body' ).append( this.windowManager.$element );
	}
	this.windowManager.addWindows( [ dialog ] );
	this.windowManager.openWindow( dialog ).closed.then( ( data ) => {
		if ( data && data.reload ) {
			this.store.reload();
		}
		this.windowManager.clearWindows();
	} );
};
