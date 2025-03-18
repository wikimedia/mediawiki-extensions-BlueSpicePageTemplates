<?php

use MediaWiki\Json\FormatJson;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;

$extDir = dirname( dirname( __DIR__ ) );

require_once "$extDir/BlueSpiceFoundation/maintenance/BSMaintenance.php";

class BSTransformNSData extends LoggedUpdateMaintenance {

	/** @var stdClass[] */
	protected $data = [];

	protected function readData() {
		$res = $this->getDB( DB_REPLICA )->select(
			'bs_pagetemplate',
			'*',
			'',
			__METHOD__
		);
		if ( $res->numRows() < 1 ) {
			return true;
		}

		foreach ( $res as $row ) {
			$this->data[$row->pt_id] = $row;
		}
	}

	protected function doDBUpdates() {
		$this->output( "... bs_pagetemplate -> transforming namespaces from int to json ...\n" );

		$this->readData();
		if ( count( $this->data ) < 1 ) {
			$this->output( "Nothing to migrate\n" );
			return true;
		}
		$this->output( count( $this->data ) . " pagetemplates\n" );
		foreach ( $this->data as $pageId => $pageTemplate ) {

			$targetNs = FormatJson::encode( [ (int)$pageTemplate->pt_target_namespace ] );

			$this->output( "\n" );

			$this->getDB( DB_PRIMARY )->update(
				'bs_pagetemplate',
				[ 'pt_target_namespace' => $targetNs ],
				[ 'pt_id' => $pageId ],
				__METHOD__
			);
		}

		return true;
	}

	/**
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_pagetemplate-transform_ns_data';
	}

}
