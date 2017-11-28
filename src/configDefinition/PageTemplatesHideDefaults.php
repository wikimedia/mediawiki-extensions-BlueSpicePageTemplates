<?php

namespace BlueSpice\PageTemplates\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;

class PageTemplatesHideDefaults extends BooleanSetting {
	public function getLabelMessageKey() {
		return 'bs-pagetemplates-pref-hidedefaults';
	}
}