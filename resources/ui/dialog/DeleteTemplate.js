bs.util.registerNamespace( 'bs.pageTemplates.ui.dialog' );

bs.pageTemplates.ui.dialog.DeleteTemplateDialog = function ( cfg ) {
	bs.pageTemplates.ui.dialog.DeleteTemplateDialog.parent.call( this, cfg );
	this.ids = cfg.ids;
};

OO.inheritClass( bs.pageTemplates.ui.dialog.DeleteTemplateDialog, OO.ui.ProcessDialog );

bs.pageTemplates.ui.dialog.DeleteTemplateDialog.static.name = 'deleteTemplateDialog';
bs.pageTemplates.ui.dialog.DeleteTemplateDialog.static.title = '';
bs.pageTemplates.ui.dialog.DeleteTemplateDialog.static.actions = [
	{ action: 'delete', label: mw.msg( 'oojsplus-toolbar-delete' ), flags: [ 'primary', 'destructive' ] },
	{ action: 'cancel', label: mw.msg( 'oojsplus-toolbar-cancel' ), flags: [ 'safe' ] }
];

bs.pageTemplates.ui.dialog.DeleteTemplateDialog.prototype.initialize = function () {
	bs.pageTemplates.ui.dialog.DeleteTemplateDialog.parent.prototype.initialize.call( this );
	this.content = new OO.ui.PanelLayout( {
		expanded: false,
		padded: true
	} );

	this.content.$element.append(
		new OO.ui.MessageWidget( {
			type: 'warning',
			label: mw.message( 'bs-pagetemplates-confirm-deletetpl', this.ids.length ).parse()
		} ).$element
	);
	this.$body.append( this.content.$element );
};

bs.pageTemplates.ui.dialog.DeleteTemplateDialog.prototype.getSetupProcess = function () {
	return bs.pageTemplates.ui.dialog.DeleteTemplateDialog.parent.prototype.getSetupProcess.call( this ).next(
		function () {
			this.title
				.setLabel( mw.message( 'bs-pagetemplates-tipdeletetemplate', this.ids.length ).parse() )
				.setTitle( mw.message( 'bs-pagetemplates-tipdeletetemplate', this.ids.length ).parse() );
		}, this
	);
};

bs.pageTemplates.ui.dialog.DeleteTemplateDialog.prototype.getActionProcess = function ( action ) {
	return bs.pageTemplates.ui.dialog.DeleteTemplateDialog.parent.prototype.getActionProcess.call( this, action ).next(
		function () {
			if ( action === 'delete' ) {
				const dfd = $.Deferred();
				this.pushPending();

				bs.api.tasks.exec(
					'pagetemplates',
					'doDeleteTemplates',
					{
						ids: this.ids
					},
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

bs.pageTemplates.ui.dialog.DeleteTemplateDialog.prototype.getBodyHeight = function () {
	if ( !this.$errors.hasClass( 'oo-ui-element-hidden' ) ) {
		return this.$element.find( '.oo-ui-processDialog-errors' )[ 0 ].scrollHeight;
	}
	return this.$body[ 0 ].scrollHeight;
};

bs.pageTemplates.ui.dialog.DeleteTemplateDialog.prototype.onDismissErrorButtonClick = function () {
	this.hideErrors();
	this.updateSize();
};

bs.pageTemplates.ui.dialog.DeleteTemplateDialog.prototype.showErrors = function () {
	bs.pageTemplates.ui.dialog.DeleteTemplateDialog.parent.prototype.showErrors.call( this, arguments );
	this.updateSize();
};
