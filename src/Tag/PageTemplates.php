<?php

namespace BlueSpice\PageTemplates\Tag;

use BlueSpice\Tag\Tag;

class PageTemplates extends Tag {
	/**
	 * @param mixed $processedInput
	 * @param array $processedArgs
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 *
	 * @return IHandler
	 */
	public function getHandler(
		$processedInput,
		array $processedArgs,
		\Parser $parser,
		\PPFrame $frame
		) {
		return new PageTemplatesHandler( $processedInput, $processedArgs, $parser, $frame );
	}

	/**
	 *
	 * @return \ParamDefinition[]
	 */
	public function getArgsDefinitions() {
		return [];
	}

	/**
	 * @return string[]
	 */
	public function getTagNames() {
		return [ 'bs:pagetemplates', 'pagetemplates' ];
	}

	/**
	 * @return bool
	 */
	public function needsDisabledParserCache() {
		return true;
	}
}
