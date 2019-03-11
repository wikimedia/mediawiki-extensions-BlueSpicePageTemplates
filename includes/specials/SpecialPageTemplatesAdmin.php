<?php

class SpecialPageTemplatesAdmin extends \BlueSpice\SpecialPage {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct( 'PageTemplatesAdmin', 'pagetemplatesadmin-viewspecialpage' );
	}

	/**
	 *
	 * @global OutputPage $this->getOutput()
	 * @param string | false $parameter
	 */
	public function execute( $parameter ) {
		parent::execute( $parameter );
		$this->getOutput()->addModules( 'ext.bluespice.pageTemplates' );
		$this->getOutput()->addHTML(
			'<div id="bs-pagetemplates-grid" class="bs-manager-container"></div>' );
	}

}
