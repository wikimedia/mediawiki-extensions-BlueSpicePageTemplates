<?php

namespace BlueSpice\PageTemplates\HookHandler;

use BlueSpice\PageTemplates\GlobalActionsManager;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class Main implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsManager',
			[
				'special-bluespice-pagetemplates' => [
					'factory' => function () {
						return new GlobalActionsManager();
					}
				]
			]
		);
	}
}
