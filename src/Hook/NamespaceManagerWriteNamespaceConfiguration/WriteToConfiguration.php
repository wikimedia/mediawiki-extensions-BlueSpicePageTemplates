<?php

namespace BlueSpice\PageTemplates\Hook\NamespaceManagerWriteNamespaceConfiguration;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerWriteNamespaceConfiguration;

class WriteToConfiguration extends NamespaceManagerWriteNamespaceConfiguration {
	protected function skipProcessing() {
		if( $this->ns === null ) {
			return true;
		}
		return false;
	}

	/**
	 * Does the opposite of what would usually be done,
	 * so that only if ns is explicitly disabled it will be written to config
	 *
	 * @return boolean
	 */
	protected function doProcess() {
		$excludedNamespaces = $this->getConfig()->get( 'PageTemplatesExcludeNs' );

		$currentlyExcluded = in_array( $this->ns, $excludedNamespaces );

		$explicitlyDeactivated = false;
		if ( isset( $this->definition['pagetemplates'] ) && $this->definition['pagetemplates'] === false ) {
			$explicitlyDeactivated = true;
		}

		$explicitlyActivated = false;
		if ( isset( $this->definition['pagetemplates'] ) && $this->definition['pagetemplates'] === true ) {
			$explicitlyActivated = true;
		}

		if( ( $currentlyExcluded && !$explicitlyActivated ) || $explicitlyDeactivated ) {
			$this->saveContent .= "\$GLOBALS['bsgPageTemplatesExcludeNs'][] = {$this->constName};\n";
		}

		return true;
	}
}
