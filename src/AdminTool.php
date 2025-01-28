<?php

namespace BlueSpice\PageTemplates;

use BlueSpice\IAdminTool;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return string String of the URL
	 */
	public function getURL() {
		$tool = SpecialPage::getTitleFor( 'PageTemplatesAdmin' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return Message
	 */
	public function getDescription() {
		return wfMessage( 'bs-pagetemplates-desc' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getName() {
		return wfMessage( 'pagetemplatesadmin' );
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
