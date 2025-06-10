<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Json\FormatJson;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class BSPageTemplateList {
	public const HIDE_IF_NOT_IN_TARGET_NS = 0;
	public const FORCE_NAMESPACE = 1;
	public const HIDE_DEFAULTS = 2;
	public const ALL_NAMESPACES_PSEUDO_ID = -99;
	public const UNSET_TARGET_NAMESPACES = 101;

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 *
	 * @var array
	 */
	protected $config = [];

	/** @var MediaWikiServices */
	protected $services = [];

	/**
	 *
	 * @param Title $title
	 * @param array $config
	 */
	public function __construct( $title, $config = [] ) {
		$this->title = $title;
		$this->config = $config + [
			self::FORCE_NAMESPACE => false,
			self::HIDE_IF_NOT_IN_TARGET_NS => true,
			self::HIDE_DEFAULTS => false,
			self::UNSET_TARGET_NAMESPACES => false
		];
		$this->services = MediaWikiServices::getInstance();

		$this->init();
	}

	/**
	 *
	 */
	protected function init() {
		$this->fetchDB();
		$this->filterByPermissionAndAddTargetUrls();
		$this->addDefaultPageTemplate();
	}

	/** @var array[] */
	protected $dataSets = [];

	/**
	 *
	 */
	protected function fetchDB() {
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );

		$res = $dbr->select(
			'bs_pagetemplate',
			'*',
			[],
			__METHOD__,
			[ 'ORDER BY' => 'pt_label' ]
		);

		$namespaceInfo = $this->services->getNamespaceInfo();
		foreach ( $res as $row ) {
			if ( $this->config[self::HIDE_IF_NOT_IN_TARGET_NS] ) {

				$targetNamespaceIds = FormatJson::decode( $row->pt_target_namespace, true );

				if ( $this->skipDataSet( $this->title->getNamespace(), $targetNamespaceIds ) ) {
					continue;
				}
			}

			$dataSet = (array)$row;
			$dataSet['type'] = strtolower(
				$namespaceInfo->getCanonicalName( $row->pt_template_namespace )
			);
			$this->dataSets[$row->pt_id] = $dataSet;
		}
	}

	/**
	 * @param int $nsId
	 * @param array $row
	 * @return bool
	 */
	private function skipDataSet( $nsId, $row ) {
		if ( !in_array( self::ALL_NAMESPACES_PSEUDO_ID, $row ) && !in_array( $nsId, $row ) ) {
			return true;
		}

		return false;
	}

	protected function addDefaultPageTemplate() {
		if ( $this->config[self::HIDE_DEFAULTS] ) {
			return;
		}

		$targetUrl = $this->title->getLinkURL( [ 'action' => 'edit', 'preload' => '' ] );
		$this->services->getHookContainer()->run(
			'BSPageTemplatesModifyTargetUrl',
			[
				$this->title,
				null,
				&$targetUrl
			]
		);

		$this->dataSets[-1] = [
			'pt_template_title' => null,
			'pt_template_namespace' => null,
			'pt_label' => wfMessage( 'bs-pagetemplates-empty-page' )->text(),
			'pt_desc' => wfMessage( 'bs-pagetemplates-empty-page-desc' )->text(),
			// NS needs to be something non-existent,
			// but I did not want to use well known pseudo namespace ids
			'pt_target_namespace' => FormatJson::encode( [ -98 ] ),
			'pt_tags' => '',
			'target_url' => $targetUrl,
			'type' => 'empty'
		];
	}

	/**
	 *
	 */
	protected function filterByPermissionAndAddTargetUrls() {
		$pm = $this->services->getPermissionManager();
		// No context available
		$user = RequestContext::getMain()->getUser();
		foreach ( $this->dataSets as $id => &$dataSet ) {
			$preloadTitle = Title::makeTitle(
				$dataSet['pt_template_namespace'],
				$dataSet['pt_template_title']
			);

			$targetTitle = $this->title;
			$targetNamespaceIds = FormatJson::decode( $dataSet['pt_target_namespace'], true );

			foreach ( $targetNamespaceIds as $nsId ) {
				if ( $this->config[self::FORCE_NAMESPACE]
					&& (int)$nsId !== static::ALL_NAMESPACES_PSEUDO_ID ) {
					$targetTitle = Title::makeTitle(
						$nsId,
						$this->title->getText()
					);
				}

				// If a user can not create or edit a page in the target namespace, we hide the template
				if (
					$this->config[self::UNSET_TARGET_NAMESPACES] &&
					(
						!$pm->userCan( 'create', $user, $targetTitle ) ||
						!$pm->userCan( 'edit', $user, $targetTitle )
					)
				) {
					unset( $this->dataSets[$id] );
					continue;
				}

				$targetUrl = $targetTitle->getLinkURL( [
					'action' => 'edit',
					'preload' => $preloadTitle->getPrefixedDBkey()
				] );
				$this->services->getHookContainer()->run(
					'BSPageTemplatesModifyTargetUrl',
					[
						$targetTitle,
						$preloadTitle,
						&$targetUrl
					]
				);
				$dataSet['target_url'] = $targetUrl;
			}
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getAll() {
		return $this->dataSets;
	}

	/**
	 *
	 * @return array
	 */
	public function getAllGrouped() {
		return [
			'tags' => $this->getTagSortedTemplates(),
			'namespaces' => $this->getNamespaceTemplates(),
			'default' => $this->getAllForDefault(),
		];
	}

	/**
	 *
	 * @return array
	 */
	private function getNamespaceTemplates() {
		$filteredDataSets = [];
		foreach ( $this->dataSets as $id => $dataSet ) {
			// skip default templates
			if ( $id === -1 ) {
				continue;
			}
			$targetNamespaceIds = FormatJson::decode( $dataSet['pt_target_namespace'], true );
			foreach ( $targetNamespaceIds as $nsId ) {
				$filteredDataSets[$nsId][] = $dataSet;
			}
		}

		return $filteredDataSets;
	}

	/**
	 *
	 * @return array
	 */
	private function getTagSortedTemplates() {
		$filteredDataSets = [];
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
			$tagsArray = json_decode( $row->pt_tags ?? '', true );
			if ( is_array( $tagsArray ) ) {
				$resultData = array_merge( $resultData, $tagsArray );
			}
		}

		$resultData = array_unique( $resultData );
		foreach ( $this->dataSets as $id => $dataSet ) {
			foreach ( $resultData as $tag ) {
				$dataSetTags = json_decode( $dataSet['pt_tags'] ?? '', true );
				if ( !$dataSetTags ) {
					continue;
				}
				if ( in_array( $tag, $dataSetTags ) ) {
					$filteredDataSets[$tag][] = $dataSet;
				}
			}
		}

		// get untagged templates
		foreach ( $this->dataSets as $id => $dataSet ) {
			$tags = json_decode( $dataSet['pt_tags'] ?? '', true ) ?? [];
			if ( !$tags && $id !== -1 ) {
				$filteredDataSets['untagged'][] = $dataSet;
			}
		}

		foreach ( $filteredDataSets  as $id => $dataSet ) {
			// tags without templates would be hidden
			if ( count( $dataSet ) === 0 ) {
				unset( $filteredDataSets[$id] );
				continue;
			}
		}

		return $filteredDataSets;
	}

	/**
	 *
	 * @return array
	 */
	protected function getAllForDefault() {
		$filteredDataSets = [];
		foreach ( $this->dataSets as $id => $dataSet ) {
			if ( $id < 0 ) {
				$filteredDataSets[$id] = $dataSet;
			}
		}

		return [
			self::ALL_NAMESPACES_PSEUDO_ID => $filteredDataSets
		];
	}

	/**
	 *
	 * @return int
	 */
	public function getCount() {
		return count( $this->dataSets );
	}

	/**
	 *
	 * @param int $id
	 * @param array $data
	 */
	public function set( $id, $data ) {
		$this->dataSets[$id] = $data;
	}

	/**
	 * @return Title
	 */
	public function getTitle() {
		return $this->title;
	}
}
