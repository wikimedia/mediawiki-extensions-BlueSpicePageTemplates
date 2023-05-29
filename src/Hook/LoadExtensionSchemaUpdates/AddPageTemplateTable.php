<?php

namespace BlueSpice\PageTemplates\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddPageTemplateTable extends LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dbType = $this->updater->getDB()->getType();
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_pagetemplate',
			"$dir/maintenance/db/sql/$dbType/bs_pagetemplate-generated.sql"
		);
		if ( $dbType == 'mysql' ) {
			$this->updater->modifyExtensionField(
				'bs_pagetemplate',
				'pt_target_namespace',
				"$dir/maintenance/db/bs_ns_to_json.patch.pt_target_namespace.sql"
			);

			// BS 4.3.0: add tag field
			$this->updater->addExtensionField(
				'bs_pagetemplate',
				'pt_tags',
				"$dir/maintenance/db/bs_pagetemplate.patch.add_tags_col.sql"
			);
		}
		$this->updater->addPostDatabaseUpdateMaintenance( \BSTransformNSData::class );
	}

	/**
	 *
	 * @return string
	 */
	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}
}
