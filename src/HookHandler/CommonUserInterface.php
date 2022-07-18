<?php

namespace BlueSpice\PageTemplates\HookHandler;

use BlueSpice\PageTemplates\GlobalActionsManager;
use MediaWiki\Hook\BeforePageDisplayHook;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents, BeforePageDisplayHook {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsManager',
			[
				'special-bluespice-pagetemplates' => [
					'factory' => static function () {
						return new GlobalActionsManager();
					}
				]
			]
		);
	}

	/**
	 * Add static styles for standard page template content
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$out->addModuleStyles( [ 'ext.bluespice.pageTemplates.standardtemplate.styles' ] );
	}
}
