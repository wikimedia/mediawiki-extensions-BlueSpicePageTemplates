<?php

namespace BlueSpice\PageTemplates\Special;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;

class PageTemplatesAdministration extends SpecialPage {
	/**
	 *
	 */
	public function __construct() {
		parent::__construct( 'PageTemplatesAdmin', 'pagetemplatesadmin-viewspecialpage' );
	}

	/**
	 * @param string $subPage
	 * @return void
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );
		$this->getOutput()->addModules( [ 'ext.bluespice.pageTemplates' ] );
		$this->getOutput()->addHTML(
			Html::element( 'div', [ 'id' => 'bs-pagetemplates-grid' ] )
		);
	}
}
