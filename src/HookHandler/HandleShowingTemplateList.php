<?php

namespace BlueSpice\PageTemplates\HookHandler;

use BSPageTemplateList;
use BSPageTemplateListRenderer;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Html\Html;
use MediaWiki\Linker\Hook\HtmlPageLinkRendererBeginHook;
use MediaWiki\Page\Hook\BeforeDisplayNoArticleTextHook;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

class HandleShowingTemplateList implements BeforeDisplayNoArticleTextHook, HtmlPageLinkRendererBeginHook {

	/**
	 * @param ConfigFactory $configFactory
	 * @param TitleFactory $titleFactory
	 * @param HookContainer $hookContainer
	 */
	public function __construct(
		private readonly ConfigFactory $configFactory,
		private readonly TitleFactory $titleFactory,
		private readonly HookContainer $hookContainer
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
	 * Replace the default "no article text" of non-existing pages
	 * with a list of available page templates, if supported
	 *
	 * @inheritDoc
	 */
	public function onBeforeDisplayNoArticleText( $article ) {
		$context = $article->getContext();
		$title = $article->getTitle();
		if ( !$title || $title->isSpecialPage() ) {
			return true;
		}
		if ( $title->getContentModel() !== CONTENT_MODEL_WIKITEXT ) {
			return true;
		}
		if ( $article->getOldID() ) {
			// Missing revision of an existing page, not a "create page" situation
			return true;
		}

		$config = $this->configFactory->makeConfig( 'bsg' );
		$excludeNs = $config->get( 'PageTemplatesExcludeNs' );
		if ( in_array( $title->getNamespace(), $excludeNs ) ) {
			return true;
		}

		$authority = $context->getAuthority();
		if ( !$authority->probablyCan( 'edit', $title ) || !$authority->probablyCan( 'createpage', $title ) ) {
			return true;
		}

		$out = $context->getOutput();
		$out->enableOOUI();
		$out->addModuleStyles( [ 'ext.bluespice.pageTemplates.styles' ] );
		$out->addModules( [ 'ext.bluespice.pageTemplates.tag' ] );

		$pageTemplateList = new BSPageTemplateList( $title, [
			BSPageTemplateList::HIDE_IF_NOT_IN_TARGET_NS =>
				$config->get( 'PageTemplatesHideIfNotInTargetNs' ),
			BSPageTemplateList::FORCE_NAMESPACE =>
				$config->get( 'PageTemplatesForceNamespace' ),
			BSPageTemplateList::HIDE_DEFAULTS =>
				$config->get( 'PageTemplatesHideDefaults' )
		] );

		$renderer = new BSPageTemplateListRenderer();
		$this->hookContainer->run( 'BSPageTemplatesBeforeRender',
			[ $this, &$pageTemplateList, &$renderer, $title ]
		);

		$dir = $context->getLanguage()->getDir();
		$out->addHTML( Html::rawElement( 'div', [
			'class' => "noarticletext bs-pagetemplates-list mw-content-$dir",
			'dir' => $dir,
			'lang' => $context->getLanguage()->getHtmlCode(),
		], $renderer->render( $pageTemplateList ) ) );

		// Prevent the default "no article text" from being displayed
		return false;
	}
}
