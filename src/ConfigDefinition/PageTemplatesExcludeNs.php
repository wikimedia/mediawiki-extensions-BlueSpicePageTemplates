<?php

namespace BlueSpice\PageTemplates\ConfigDefinition;

use BlueSpice\ConfigDefinition\ArraySetting;

class PageTemplatesExcludeNs extends ArraySetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_CONTENT_STRUCTURING . '/BlueSpicePageTemplates',
			static::MAIN_PATH_EXTENSION . '/BlueSpicePageTemplates/' . static::FEATURE_CONTENT_STRUCTURING ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpicePageTemplates',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-pagetemplates-pref-excludens';
	}

	public function getOptions() {
		$language = \RequestContext::getMain()->getLanguage();
		$exclude = array( NS_MEDIAWIKI, NS_SPECIAL, NS_MEDIA );
		foreach ( $language->getNamespaces() as $namespace ) {
			$nsIndx = $language->getNsIndex( $namespace );
			if( !\MWNamespace::isTalk( $nsIndx ) ) {
				continue;
			}
			$exclude[] = $nsIndx;
		}
		return \BsNamespaceHelper::getNamespacesForSelectOptions( $exclude );
	}
}
