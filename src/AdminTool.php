<?php

namespace BlueSpice\PageTemplates;

use BlueSpice\IAdminTool;

class AdminTool implements IAdminTool {

	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'PageTemplatesAdmin' );
		return $tool->getLocalURL();
	}

	public function getDescription() {
		return wfMessage( 'bs-pagetemplates-desc' );
	}

	public function getName() {
		return wfMessage( 'bs-pagetemplatesadmin-label' );
	}

	public function getClasses() {
		$classes = array(
			'bs-icon-clipboard-checked'
		);

		return $classes;
	}

	public function getDataAttributes() {
		return [];
	}

	public function getPermissions() {
		$permissions = array(
			'pagetemplatesadmin-viewspecialpage'
		);
		return $permissions;
	}

}