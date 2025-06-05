<?php

namespace BlueSpice\PageTemplates\HookHandler;

use MediaWiki\Cache\Hook\MessagesPreLoadHook;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Context\RequestContext;
use MediaWiki\Linker\Hook\HtmlPageLinkRendererBeginHook;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

class HandleShowingTemplateList implements MessagesPreLoadHook, HtmlPageLinkRendererBeginHook {

	/**
	 * @param ConfigFactory $configFactory
	 * @param TitleFactory $titleFactory
	 * @param PermissionManager $permissionManager
	 */
	public function __construct(
		private readonly ConfigFactory $configFactory,
		private readonly TitleFactory $titleFactory,
		private readonly PermissionManager $permissionManager
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onHtmlPageLinkRendererBegin( $linkRenderer, $target, &$text, &$customAttribs, &$query, &$ret ) {
		if ( $target instanceof Title && $target->isTalkPage() ) {
			return true;
		}
		if ( $target->isExternal() ) {
			return true;
		}
		if ( in_array( 'known', $customAttribs, true ) ) {
			return true;
		}
		if ( !in_array( 'broken', $customAttribs, true ) ) {
			// It's not marked as "known" and not as "broken" so we have to check
			$title = $this->titleFactory->makeTitle( $target->getNamespace(), $target->getText() );
			if ( $title->isKnown() ) {
				return true;
			}
		}

		$config = $this->configFactory->makeConfig( 'bsg' );
		$excludeNs = $config->get( 'PageTemplatesExcludeNs' );
		if ( in_array( $target->getNamespace(), $excludeNs ) ) {
			return true;
		}

		if ( !isset( $query['preload'] ) ) {
			$query['action'] = 'view';
		}

		return true;
	}

	/**
	 * Replace default message for non-exsting pages with list of PageTemplates, if supported
	 * @inheritDoc
	 */
	public function onMessagesPreLoad( $title, &$message, $code ) {
		if ( !str_contains( $title, 'Noarticletext' ) ) {
			return true;
		}
		$title = RequestContext::getMain()->getTitle();
		if ( !$title ) {
			return true;
		}
		if ( $title->isSpecialPage() ) {
			return true;
		}

		if ( $title->getContentModel() !== CONTENT_MODEL_WIKITEXT ) {
			return true;
		}

		$config = $this->configFactory->makeConfig( 'bsg' );

		$excludeNs = $config->get( 'PageTemplatesExcludeNs' );
		if ( in_array( $title->getNamespace(), $excludeNs ) ) {
			return true;
		}

		$user = RequestContext::getMain()->getUser();
		$editAllowedStatus = $this->permissionManager->getPermissionStatus( 'edit', $user, $title );
		$createAllowedStatus = $this->permissionManager->getPermissionStatus( 'createpage', $user, $title );
		if ( !$editAllowedStatus->isGood() || !$createAllowedStatus->isGood() ) {
			return true;
		}
		$message = '<bs:pagetemplates />';
		return true;
	}
}
