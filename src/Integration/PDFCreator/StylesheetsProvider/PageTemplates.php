<?php

namespace BlueSpice\PageTemplates\Integration\PDFCreator\StylesheetsProvider;

use MediaWiki\Extension\PDFCreator\IStylesheetsProvider;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;

class PageTemplates implements IStylesheetsProvider {

	/**
	 * @inheritDoc
	 */
	public function execute( string $module, ExportContext $context ): array {
		$dir = dirname( __FILE__, 5 );

		return [
			'bluespice.pageTemplates.standardtemplate.css' =>
				$dir . '/resources/bluespice.pageTemplates.standardtemplate.css'
		];
	}
}
