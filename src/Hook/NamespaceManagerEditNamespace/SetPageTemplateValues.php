<?php

namespace BlueSpice\PageTemplates\Hook\NamespaceManagerEditNamespace;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerEditNamespace;

class SetPageTemplateValues extends NamespaceManagerEditNamespace {

	protected function doProcess() {
		if ( !$this->useInternalDefaults && isset( $this->additionalSettings['pagetemplates'] ) ) {
			$this->namespaceDefinition[$this->nsId][ 'pagetemplates' ] = $this->additionalSettings['pagetemplates'];
		}
		else {
			$this->namespaceDefinition[$this->nsId][ 'pagetemplates' ] = false;
		}
		return true;
	}

}
