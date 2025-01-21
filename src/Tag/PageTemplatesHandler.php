<?php

namespace BlueSpice\PageTemplates\Tag;

use BlueSpice\Tag\Handler;
use BSPageTemplateList;
use BSPageTemplateListRenderer;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;

class PageTemplatesHandler extends Handler {

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->addModules( [ 'ext.bluespice.pageTemplates.tag' ] );
		$this->parser->getOutput()->addModuleStyles( [ 'ext.bluespice.pageTemplates.styles' ] );
		RequestContext::getMain()->getOutput()->enableOOUI();
		return $this->renderPageTemplates();
	}

	/**
	 * Renders the pagetemplates form which is displayed when creating a new article
	 * @return string The rendered output
	 */
	protected function renderPageTemplates() {
		$title = $this->frame->getTitle();
		// if we are not on a wiki page, return. This is important when calling
		// import scripts that try to create nonexistent pages, e.g. importImages
		if ( !is_object( $title ) ) {
			return true;
		}

		$config = MediaWikiServices::getInstance()->getConfigFactory()
				->makeConfig( 'bsg' );

		$pageTemplateList = new BSPageTemplateList( $title,
			[
			BSPageTemplateList::HIDE_IF_NOT_IN_TARGET_NS =>
			$config->get( 'PageTemplatesHideIfNotInTargetNs' ),
			BSPageTemplateList::FORCE_NAMESPACE =>
			$config->get( 'PageTemplatesForceNamespace' ),
			BSPageTemplateList::HIDE_DEFAULTS =>
			$config->get( 'PageTemplatesHideDefaults' )
			] );

		$pageTemplateListRenderer = new BSPageTemplateListRenderer();
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSPageTemplatesBeforeRender',
			[ $this, &$pageTemplateList, &$pageTemplateListRenderer, $title ]
		);
		return $pageTemplateListRenderer->render( $pageTemplateList );
	}

}
