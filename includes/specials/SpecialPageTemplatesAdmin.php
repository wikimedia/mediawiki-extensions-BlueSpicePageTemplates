<?php

use BlueSpice\Special\ManagerBase;

class SpecialPageTemplatesAdmin extends ManagerBase {

	public function __construct() {
		parent::__construct( 'PageTemplatesAdmin', 'pagetemplatesadmin-viewspecialpage' );
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-pagetemplates-grid';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [ 'ext.bluespice.pageTemplates' ];
	}
}
