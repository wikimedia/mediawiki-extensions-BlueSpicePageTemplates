<?php

namespace BlueSpice\PageTemplates\Hook\BSUEModulePDFBeforeAddingStyleBlocks;

use BlueSpice\UEModulePDF\Hook\BSUEModulePDFBeforeAddingStyleBlocks;

class AddStandardTemplatesStyles extends BSUEModulePDFBeforeAddingStyleBlocks {

	protected function doProcess() {
		$styles = file_get_contents(
			__DIR__ . '/../../../resources/bluespice.pageTemplates.standardtemplate.css'
		);

		$this->styleBlocks[ 'PageTemplates' ] = $styles;
		return true;
	}

}
