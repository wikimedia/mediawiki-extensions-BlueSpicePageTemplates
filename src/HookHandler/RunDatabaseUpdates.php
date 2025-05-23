<?php

namespace BlueSpice\PageTemplates\HookHandler;

use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class RunDatabaseUpdates implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$dbType = $updater->getDB()->getType();
		$dir = dirname( __DIR__, 2 );

		$updater->addExtensionTable(
			'bs_pagetemplate',
			"$dir/db/$dbType/bs_pagetemplate.sql"
		);

		$updater->addExtensionTable(
			'bs_pagetemplate',
			"$dir/db/$dbType/bs_pagetemplate_add_tags_col_patch.sql"
		);
	}
}
