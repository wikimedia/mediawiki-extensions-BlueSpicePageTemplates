<?php

namespace BlueSpice\PageTemplates\Hook\BSApiNamespaceStoreMakeData;

use BlueSpice\NamespaceManager\Hook\BSApiNamespaceStoreMakeData;

class AddData extends BSApiNamespaceStoreMakeData {

	protected function doProcess() {
		$excludedNamespace = $this->getConfig()->get( 'PageTemplatesExcludeNs' );

		$readOnlyNS = [
			NS_FILE, NS_MEDIAWIKI, NS_TEMPLATE, NS_CATEGORY
		];

		//We want to mark namespaces NOT set in config var as enabled
		foreach( $this->results as $key => &$result ) {
			$result['pagetemplates'] = [
				'value' => !in_array( $result['id'], $excludedNamespace ),
				'read_only' => in_array( $result['id'], $readOnlyNS ),
				'disabled' => $result['isTalkNS']
			];
		}

		return true;
	}

}
