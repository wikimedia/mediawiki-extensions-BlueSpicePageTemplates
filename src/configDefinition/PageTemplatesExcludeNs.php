<?php

namespace BlueSpice\PageTemplates\ConfigDefinition;

use BlueSpice\ConfigDefinition\HTMLSelectNamespace;

class PageTemplatesExcludesNs extends HTMLSelectNamespace {
	public function getLabelMessageKey() {
		return 'bs-pagetemplates-pref-excludens';
	}
}