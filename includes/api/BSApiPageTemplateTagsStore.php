<?php

use MediaWiki\Json\FormatJson;

class BSApiPageTemplateTagsStore extends BSApiExtJSStoreBase {

	/**
	 *
	 * @param string $query
	 * @return array
	 */
	public function makeData( $query = '' ) {
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$res = $dbr->select(
			[ 'bs_pagetemplate' ],
			[ 'pt_tags' ],
			[],
			__METHOD__
		);

		$resultData = [];
		foreach ( $res as $row ) {
			if ( !$row ) {
				continue;
			}
			$tagsArray = FormatJson::decode( $row->pt_tags ?? '', true );
			if ( is_array( $tagsArray ) ) {
				$resultData = array_merge( $resultData, $tagsArray );
			}
		}

		// Remove duplicate tags from the result
		$resultData = array_unique( $resultData );
		if ( $resultData !== null ) {
			$resultData = $this->appendResults( $resultData );
		}

		return array_values( $resultData );
	}

	/**
	 *
	 * @param array $resultData
	 * @return array
	 */
	public function appendResults( $resultData ) {
		$aResults = [];
		foreach ( $resultData as $resultTag ) {
			$aResults[] = (object)[
				'text' => $resultTag
			];
		}
		return $aResults;
	}

}
