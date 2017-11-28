<?php

namespace BlueSpice\PageTemplates\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;

class PageTemplatesForceNamespace extends BooleanSetting {
	public function getLabelMessageKey() {
		return 'bs-pagetemplates-pref-forcenamespace';
	}
}