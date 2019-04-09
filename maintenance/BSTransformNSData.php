<?php
$IP = dirname(dirname(dirname(__DIR__)));
require_once( "$IP/maintenance/Maintenance.php" );

class BSTransformNSData extends LoggedUpdateMaintenance {

	protected $data = [];

	public function execute() {
		$db = $this->getDB( DB_MASTER );
		$key = $this->getUpdateKey();

		if ( !$this->doDBUpdates() ) {
			return false;
		}

		if ( $db->insert( 'updatelog', [ 'ul_key' => $key ], __METHOD__, 'IGNORE' ) ) {
			return true;
		} else {
			$this->output( $this->updatelogFailedMessage() . "\n" );

			return false;
		}
	}

	protected function readData() {
		$res = $this->getDB( DB_REPLICA )->select(
			'bs_pagetemplate',
			'*'
		);
		foreach( $res as $row ) {
			$this->data[$row->pt_id] = $row;
		}
	}

	protected function doDBUpdates() {
		$this->output( "... bs_pagetemplate -> transforming namespaces from int to json ...\n" );

		$this->readData();
		$this->output( count($this->data). " pagetemplates\n" );
		foreach( $this->data as $pageId => $pageTemplate ) {
            if ( is_numeric( $pageTemplate->pt_target_namespace ) ) {

                $targetNs = FormatJson::encode( [ (int)$pageTemplate->pt_target_namespace ] );

                $this->output( ", " );

                $this->getDB( DB_MASTER )->update(
                    'bs_pagetemplate',
                    [ 'pt_target_namespace' => $targetNs ],
                    [ 'pt_id' => $pageId ],
                    __METHOD__
                );
            }
		}

		return true;
	}


	protected function getUpdateKey() {
		return 'bs_pagetemplate-transform_ns_data';
	}

}
