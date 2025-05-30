<?php

namespace BlueSpice\PageTemplates\HookHandler;

use BlueSpice\PageTemplates\Tag\PageTemplates;
use MWStake\MediaWiki\Component\GenericTagHandler\Hook\MWStakeGenericTagHandlerInitTagsHook;

class RegisterTags implements MWStakeGenericTagHandlerInitTagsHook {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeGenericTagHandlerInitTags( array &$tags ) {
		$tags[] = new PageTemplates();
	}
}
