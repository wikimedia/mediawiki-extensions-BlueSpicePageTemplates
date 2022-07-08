<?php

namespace BlueSpice\PageTemplates\HookHandler;

use BlueSpice\PageTemplates\Maintenance\PostDatabaseUpdate\PageTemplatesImport;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class SetupDefaultTemplates implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$updater->addPostDatabaseUpdateMaintenance(
			PageTemplatesImport::class
		);

		return true;
	}
}
