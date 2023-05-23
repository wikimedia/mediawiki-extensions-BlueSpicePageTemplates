<?php

namespace BlueSpice\PageTemplates\Hook;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerBeforePersistSettingsHook;

class WriteNamespaceConfiguration implements NamespaceManagerBeforePersistSettingsHook {

	/**
	 * @inheritDoc
	 */
	public function onNamespaceManagerBeforePersistSettings(
		array &$configuration, int $id, array $definition, array $mwGlobals
	): void {
		$excludedNamespaces = $mwGlobals['bsgPageTemplatesExcludeNs'] ?? [];
		$currentlyExcluded = in_array( $id, $excludedNamespaces );

		$explicitlyDeactivated = false;
		if ( isset( $definition['pagetemplates'] ) && $definition['pagetemplates'] === false ) {
			$explicitlyDeactivated = true;
		}

		$explicitlyActivated = false;
		if ( isset( $definition['pagetemplates'] ) && $definition['pagetemplates'] === true ) {
			$explicitlyActivated = true;
		}

		if ( ( $currentlyExcluded && !$explicitlyActivated ) || $explicitlyDeactivated ) {
			$configuration['bsgPageTemplatesExcludeNs'][] = $id;
		}
	}
}
