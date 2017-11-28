<?php

namespace BlueSpice\PageTemplates\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;

class PageTemplatesHideIfNotInTargetNs extends BooleanSetting {
	public function getLabelMessageKey() {
		return 'bs-pagetemplates-pref-hideifnotintargetns';
	}
}