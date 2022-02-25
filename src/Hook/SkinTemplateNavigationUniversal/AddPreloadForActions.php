<?php

namespace BlueSpice\PageTemplates\Hook\SkinTemplateNavigationUniversal;

use BlueSpice\Hook\SkinTemplateNavigationUniversal;

class AddPreloadForActions extends SkinTemplateNavigationUniversal {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$title = $this->sktemplate->getTitle();
		if ( $title->exists() ) {
			return true;
		}
		if ( !$this->getServices()
			->getPermissionManager()
			->userCan( 'edit', $this->sktemplate->getUser(), $title )
		) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		if ( isset( $this->links['views']['ve-edit'] ) ) {
			$this->links['views']['ve-edit']['href'] = wfAppendQuery(
				$this->links['views']['ve-edit']['href'],
				[ 'preload' => '' ]
			);
		}

		$this->links['views']['edit']['href'] = wfAppendQuery(
			$this->links['views']['edit']['href'],
			[ 'preload' => '' ]
		);
		return true;
	}
}
