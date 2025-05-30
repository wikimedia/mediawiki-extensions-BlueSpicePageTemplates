<?php

namespace BlueSpice\PageTemplates\Tag;

use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\GenericTagHandler\MarkerType;

class PageTemplates extends GenericTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'bs:pagetemplates', 'pagetemplates' ];
	}

	/**
	 * @return bool
	 */
	public function hasContent(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new PageTemplatesHandler(
			$services->getHookContainer(),
			$services->getConfigFactory()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getMarkerType(): MarkerType {
		return new MarkerType\NoWiki();
	}

	/**
	 * @inheritDoc
	 */
	public function getResourceLoaderModules(): ?array {
		return [
			'ext.bluespice.pageTemplates.tag',
			'ext.bluespice.pageTemplates.styles'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		return null;
	}
}
