<?php

namespace BlueSpice\PageTemplates\Special;

use MediaWiki\Html\Html;
use OOJSPlus\Special\OOJSGridSpecialPage;

class PageTemplatesAdministration extends OOJSGridSpecialPage {
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
	public function doExecute( $subPage ) {
		$this->getOutput()->addModules( [ 'ext.bluespice.pageTemplates' ] );
		$this->getOutput()->addHTML(
			Html::element( 'div', [ 'id' => 'bs-pagetemplates-grid' ] )
		);
	}
}
