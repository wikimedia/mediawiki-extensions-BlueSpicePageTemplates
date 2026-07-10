<?php

namespace BlueSpice\PageTemplates\Hook\NamespaceManagerGetMetaFields;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerGetMetaFields;

class RegisterMetaField extends NamespaceManagerGetMetaFields {

	/**
	 * @return bool
	 */
	protected function doProcess() {
		$this->metaFields[] = [
			'name' => 'pagetemplates',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-pagetemplates-nsm-label' )->text(),
			'filter' => [
				'type' => 'boolean'
			]
		];

		return true;
	}
}
