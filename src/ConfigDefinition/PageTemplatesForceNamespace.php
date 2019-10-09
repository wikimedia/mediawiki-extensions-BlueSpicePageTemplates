<?php

namespace BlueSpice\PageTemplates\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;

class PageTemplatesForceNamespace extends BooleanSetting {

	/**
	 *
	 * @return array
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' .
			static::FEATURE_CONTENT_STRUCTURING .
			'/BlueSpicePageTemplates',
			static::MAIN_PATH_EXTENSION .
			'/BlueSpicePageTemplates/' .
			static::FEATURE_CONTENT_STRUCTURING,
			static::MAIN_PATH_PACKAGE . '/' .
			static::PACKAGE_FREE .
			'/BlueSpicePageTemplates',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-pagetemplates-pref-forcenamespace';
	}

}
