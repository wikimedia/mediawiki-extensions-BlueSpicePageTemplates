<?php

namespace BlueSpice\PageTemplates\HookHandler;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\MediaWikiServices;
use SkinTemplate;

class AddPreloadForActions implements SkinTemplateNavigation__UniversalHook {

	/**
	 * @param SkinTemplate $sktemplate
	 * @return bool
	 */
	protected function skipProcessing( SkinTemplate $sktemplate ) {
		$title = $sktemplate->getTitle();
		if ( $title->exists() ) {
			return true;
		}
		if ( !MediaWikiServices::getInstance()->getPermissionManager()
			->userCan( 'edit', $sktemplate->getUser(), $title )
		) {
			return true;
		}
		return false;
	}

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if ( $this->skipProcessing( $sktemplate ) ) {
			return;
		}

		if ( isset( $links['views']['ve-edit'] ) ) {
			$links['views']['ve-edit']['href'] = wfAppendQuery(
				$links['views']['ve-edit']['href'],
				[ 'preload' => '' ]
			);
		}

		$links['views']['edit']['href'] = wfAppendQuery(
			$links['views']['edit']['href'],
			[ 'preload' => '' ]
		);
	}
}
