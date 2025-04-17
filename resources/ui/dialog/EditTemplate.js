bs.util.registerNamespace( 'bs.pageTemplates.ui.dialog' );

bs.pageTemplates.ui.dialog.EditTemplate = function ( cfg ) {
	bs.pageTemplates.ui.dialog.EditTemplate.parent.call( this, cfg );
	this.isCreation = cfg.isCreation || false;
	this.item = cfg.item || {};
};

OO.inheritClass( bs.pageTemplates.ui.dialog.EditTemplate, OO.ui.ProcessDialog );

bs.pageTemplates.ui.dialog.EditTemplate.static.name = 'editTemplate';
bs.pageTemplates.ui.dialog.EditTemplate.static.title = mw.msg( 'bs-pagetemplates-tipeditdetails' );
bs.pageTemplates.ui.dialog.EditTemplate.static.actions = [
	{ action: 'save', label: mw.msg( 'oojsplus-toolbar-save' ), flags: [ 'primary', 'progressive' ] },
	{ action: 'cancel', label: mw.msg( 'oojsplus-toolbar-cancel' ), flags: [ 'safe' ] }
];

bs.pageTemplates.ui.dialog.EditTemplate.prototype.getSetupProcess = function () {
	return bs.pageTemplates.ui.dialog.EditTemplate.parent.prototype.getSetupProcess.call( this ).next(
		function () {
			if ( this.isCreation ) {
				this.title
					.setLabel( mw.msg( 'bs-pagetemplates-tipaddtemplate' ) )
					.setTitle( mw.msg( 'bs-pagetemplates-tipaddtemplate' ) );
			}
			this.actions.setAbilities( { save: !this.isCreation } );
		}, this
	);
};

bs.pageTemplates.ui.dialog.EditTemplate.prototype.initialize = function () {
	bs.pageTemplates.ui.dialog.EditTemplate.parent.prototype.initialize.call( this );

	this.content = new OO.ui.PanelLayout( {
		expanded: false,
		padded: true
	} );

	this.nameInput = new OO.ui.TextInputWidget( {
		required: true,
		value: this.item.label || ''
	} );
	this.nameInput.connect( this, {
		change: 'checkValidity'
	} );
	this.descriptionInput = new OO.ui.MultilineTextInputWidget( {
		rows: 3,
		value: this.item.desc || '',
		required: true
	} );
	this.descriptionInput.connect( this, {
		change: 'checkValidity'
	} );
	let namespaces = this.item.targetnsid || null;
	if ( Array.isArray( namespaces ) && namespaces.length === 0 ) {
		namespaces = null;
	}
	this.namespaceInput = new OOJSPlus.ui.widget.NamespaceMultiSelectWidget( {
		$overlay: this.$overlay,
		specialOptionAll: true,
		menu: { hideOnChoose: true },
		groups: [ 'content' ]
	} );
	this.namespaceInput.connect( this, {
		change: 'updateSize'
	} );
	this.namespaceInput.setValue( namespaces );
	this.tagsInput = new OOJSPlus.ui.widget.StoreDataTagMultiselectWidget( {
		queryAction: 'bs-pagetemplate-tags-store',
		labelField: 'text',
		allowArbitrary: true
	} );
	this.tagsInput.setValue( this.item && this.item.tags ? this.item.tags.split( ',' ) : [] );
	this.tagsInput.connect( this, {
		change: 'updateSize'
	} );
	this.templateInput = new OOJSPlus.ui.widget.TitleInputWidget( {
		$overlay: this.$overlay,
		value: this.item.templatename || '',
		contentPagesOnly: false,
		required: true
	} );
	this.templateInput.connect( this, {
		change: 'checkValidity'
	} );

	this.content.$element.append(
		new OO.ui.FieldLayout( this.nameInput, {
			label: mw.msg( 'bs-pagetemplates-label-tpl' ),
			align: 'left'
		} ).$element,
		new OO.ui.FieldLayout( this.descriptionInput, {
			label: mw.msg( 'bs-pagetemplates-label-desc' ),
			align: 'left'
		} ).$element,
		new OO.ui.FieldLayout( this.tagsInput, {
			label: mw.msg( 'bs-pagetemplates-label-tags' ),
			align: 'left'
		} ).$element,
		new OO.ui.FieldLayout( this.namespaceInput, {
			label: mw.msg( 'bs-pagetemplates-label-targetns' ),
			align: 'left'
		} ).$element,
		new OO.ui.FieldLayout( this.templateInput, {
			label: mw.msg( 'bs-pagetemplates-label-article' ),
			align: 'left'
		} ).$element
	);

	this.actions.setAbilities( { save: !this.isCreation } );
	this.$body.append( this.content.$element );
	setTimeout( () => {
		this.updateSize();
	}, 1 );
};

bs.pageTemplates.ui.dialog.EditTemplate.prototype.checkValidity = async function () {
	if ( this.validityTimeout ) {
		clearTimeout( this.validityTimeout );
	}
	this.validityTimeout = setTimeout( async () => {
		try {
			await this.nameInput.getValidity();
			await this.descriptionInput.getValidity();
			await this.templateInput.getValidity();
			this.onValidityCheck( true );
		} catch ( e ) {
			this.onValidityCheck( false );
		}
	}, 500 );
};

bs.pageTemplates.ui.dialog.EditTemplate.prototype.onValidityCheck = function ( valid ) {
	this.actions.setAbilities( { save: valid } );
};

bs.pageTemplates.ui.dialog.EditTemplate.prototype.getActionProcess = function ( action ) {
	return bs.pageTemplates.ui.dialog.EditTemplate.parent.prototype.getActionProcess.call( this, action ).next(
		function () {
			if ( action === 'save' ) {
				const data = {
					id: this.item ? parseInt( this.item.id ) : null,
					label: this.nameInput.getValue(),
					desc: this.descriptionInput.getValue(),
					tags: this.tagsInput.getValue(),
					targetns: this.namespaceInput.getValue() || [ -99 ],
					template: this.templateInput.getValue()
				};
				// If -99 is set to targetns, but others are set as well, remove it
				if ( data.targetns.length > 1 && data.targetns.indexOf( -99 ) !== -1 ) {
					data.targetns.splice( data.targetns.indexOf( -99 ), 1 );
				}
				const dfd = $.Deferred();
				this.pushPending();

				bs.api.tasks.exec(
					'pagetemplates',
					'doEditTemplate',
					data,
					{
						success: function () {
							this.close( { reload: true } );
						}.bind( this ),
						failure: function ( e ) {
							this.popPending();
							dfd.reject( new OO.ui.Error( e.message ) );
						}.bind( this )
					}
				);
				return dfd.promise();
			} else {
				this.close( { reload: false } );
			}
		}, this
	);
};

bs.pageTemplates.ui.dialog.EditTemplate.prototype.getBodyHeight = function () {
	if ( !this.$errors.hasClass( 'oo-ui-element-hidden' ) ) {
		return this.$element.find( '.oo-ui-processDialog-errors' )[ 0 ].scrollHeight;
	}
	return this.$body[ 0 ].scrollHeight;
};

bs.pageTemplates.ui.dialog.EditTemplate.prototype.onDismissErrorButtonClick = function () {
	this.hideErrors();
	this.updateSize();
};

bs.pageTemplates.ui.dialog.EditTemplate.prototype.showErrors = function () {
	bs.pageTemplates.ui.dialog.EditTemplate.parent.prototype.showErrors.call( this, arguments );
	this.updateSize();
};
