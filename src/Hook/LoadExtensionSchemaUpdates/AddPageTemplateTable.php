<?php

namespace BlueSpice\PageTemplates\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddPageTemplateTable extends LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_pagetemplate',
			"$dir/maintenance/db/bs_pagetemplate.sql"
		);

		$this->updater->modifyExtensionField(
			'bs_pagetemplate',
			'pt_target_namespace',
			"$dir/maintenance/db/bs_ns_to_json.patch.pt_target_namespace.sql"
		);

		$this->updater->addPostDatabaseUpdateMaintenance(
			'BSTransformNSData'
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}
}
