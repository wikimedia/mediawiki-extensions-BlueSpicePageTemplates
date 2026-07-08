<?php

namespace BlueSpice\PageTemplates\Integration\Galaxy;

use BlueSpice\GalaxyDistributionConnector\NamespaceSettings\INamespaceSetting;
use MediaWiki\Message\Message;

class NamespaceSetting implements INamespaceSetting {

	/**
	 * @return Message
	 */
	public function getLabel(): Message {
		return Message::newFromKey( 'bs-pagetemplates-ns-setting-label' );
	}

	/**
	 * @return Message
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'bs-pagetemplates-ns-setting-help' );
	}

	/**
	 * @param int $namespace
	 * @param mixed $value
	 * @return void
	 */
	public function apply( int $namespace, mixed $value ): void {
		$GLOBALS['bsgPageTemplatesExcludeNs'] = $GLOBALS['bsgPageTemplatesExcludeNs'] ?? [];
		if ( !$value && !in_array( $namespace, $GLOBALS['bsgPageTemplatesExcludeNs'] ) ) {
			$GLOBALS['bsgPageTemplatesExcludeNs'][] = $namespace;
		} elseif ( $value && in_array( $namespace, $GLOBALS['bsgPageTemplatesExcludeNs'] ) ) {
			$GLOBALS['bsgPageTemplatesExcludeNs'] = array_diff(
				$GLOBALS['bsgPageTemplatesExcludeNs'],
				[ $namespace ]
			);
		}
	}
}
