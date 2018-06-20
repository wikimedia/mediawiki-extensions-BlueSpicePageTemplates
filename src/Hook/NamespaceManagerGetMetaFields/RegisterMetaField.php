<?php

namespace BlueSpice\PageTemplates\Hook\NamespaceManagerGetMetaFields;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerGetMetaFields;

class RegisterMetaField extends NamespaceManagerGetMetaFields {

	protected function doProcess() {
		$this->metaFields[] = [
			'name' => 'pagetemplates',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-pagetemplates-nsm-label' )->plain(),
			'filter' => [
				'type' => 'boolean'
			]
		];

		return true;
	}
}
