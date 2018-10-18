<?php

class SpecialPageTemplatesAdmin extends \BlueSpice\SpecialPage {

	public function __construct() {
		parent::__construct( 'PageTemplatesAdmin', 'pagetemplatesadmin-viewspecialpage' );

	}

	/**
	 *
	 * @global OutputPage $this->getOutput()
	 * @param type $sParameter
	 * @return type
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );
		$this->getOutput()->addModules('ext.bluespice.pageTemplates');
		$this->getOutput()->addHTML( '<div id="bs-pagetemplates-grid" class="bs-manager-container"></div>' );

	}

}
