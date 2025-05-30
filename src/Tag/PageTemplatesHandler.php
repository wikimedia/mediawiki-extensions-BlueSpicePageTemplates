<?php

namespace BlueSpice\PageTemplates\Tag;

use BSPageTemplateList;
use BSPageTemplateListRenderer;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Context\RequestContext;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class PageTemplatesHandler implements ITagHandler {

	/**
	 * @param HookContainer $hookContainer
	 * @param ConfigFactory $configFactory
	 */
	public function __construct(
		private readonly HookContainer $hookContainer,
		private readonly ConfigFactory $configFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$parser->getOutput()->addModuleStyles( [ 'ext.bluespice.pageTemplates.styles' ] );

		RequestContext::getMain()->getOutput()->enableOOUI();
		$title = $frame->getTitle();
		// if we are not on a wiki page, return. This is important when calling
		// import scripts that try to create nonexistent pages, e.g. importImages
		if ( !is_object( $title ) ) {
			return true;
		}

		$config = $this->configFactory->makeConfig( 'bsg' );

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
		$this->hookContainer->run( 'BSPageTemplatesBeforeRender',
			[ $this, &$pageTemplateList, &$pageTemplateListRenderer, $title ]
		);
		return $pageTemplateListRenderer->render( $pageTemplateList );
	}
}
