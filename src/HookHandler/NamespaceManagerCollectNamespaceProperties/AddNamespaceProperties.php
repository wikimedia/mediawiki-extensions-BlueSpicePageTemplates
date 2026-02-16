<?php

namespace BlueSpice\PageTemplates\HookHandler\NamespaceManagerCollectNamespaceProperties;

class AddNamespaceProperties {

	/**
	 * @inheritDoc
	 */
	public function onNamespaceManagerCollectNamespaceProperties(
		int $namespaceId,
		array $globals,
		array &$properties
	): void {
		$properties['pagetemplates'] = !in_array(
			$namespaceId,
			$globals['bsgPageTemplatesExcludeNs'] ?? []
		);
	}

}
