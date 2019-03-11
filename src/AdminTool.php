<?php

namespace BlueSpice\PageTemplates;

use BlueSpice\IAdminTool;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return string String of the URL
	 */
	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'PageTemplatesAdmin' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return \Message
	 */
	public function getDescription() {
		return wfMessage( 'bs-pagetemplates-desc' );
	}

	/**
	 *
	 * @return \Message
	 */
	public function getName() {
		return wfMessage( 'bs-pagetemplatesadmin-label' );
	}

	/**
	 *
	 * @return array
	 */
	public function getClasses() {
		$classes = [
			'bs-icon-clipboard-checked'
		];

		return $classes;
	}

	/**
	 *
	 * @return array
	 */
	public function getDataAttributes() {
		return [];
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions() {
		$permissions = [
			'pagetemplatesadmin-viewspecialpage'
		];
		return $permissions;
	}

}
