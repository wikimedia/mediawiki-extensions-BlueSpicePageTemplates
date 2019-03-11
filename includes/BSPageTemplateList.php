<?php

class BSPageTemplateList {
	const HIDE_IF_NOT_IN_TARGET_NS = 0;
	const FORCE_NAMESPACE = 1;
	const HIDE_DEFAULTS = 2;
	const ALL_NAMESPACES_PSEUDO_ID = -99;

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
			self::HIDE_DEFAULTS => false
		];

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

	protected $dataSets = [];

	/**
	 *
	 */
	protected function fetchDB() {
		$dbr = wfGetDB( DB_REPLICA );

		$conds = [];
		if ( $this->config[self::HIDE_IF_NOT_IN_TARGET_NS] ) {
			$conds[] = 'pt_target_namespace IN (' .
				$this->title->getNamespace() .
				', -99)';
		}

		$res = $dbr->select(
			'bs_pagetemplate',
			'*',
			$conds,
			__METHOD__,
			[ 'ORDER BY' => 'pt_label' ]
		);

		foreach ( $res as $row ) {
			$dataSet = (array)$row;
			$dataSet['type'] = strtolower(
				MWNamespace::getCanonicalName( $row->pt_template_namespace )
			);
			$this->dataSets[$row->pt_id] = $dataSet;
		}
	}

	/**
	 *
	 * @return type
	 */
	protected function addDefaultPageTemplate() {
		if ( $this->config[self::HIDE_DEFAULTS] ) {
			return;
		}

		$targetUrl = $this->title->getLinkURL( [ 'action' => 'edit' ] );
		Hooks::run( 'BSPageTemplatesModifyTargetUrl', [ $this->title, null, &$targetUrl ] );

		$this->dataSets[-1] = [
			'pt_template_title' => null,
			'pt_template_namespace' => null,
			'pt_label' => wfMessage( 'bs-pagetemplates-empty-page' )->plain(),
			'pt_desc' => wfMessage( 'bs-pagetemplates-empty-page-desc' )->plain(),
			// NS needs to be something non-existent,
			// but I did not want to use well known pseudo namespace ids
			'pt_target_namespace' => -98,
			'target_url' => $targetUrl,
			'type' => 'empty'
		];
	}

	/**
	 *
	 */
	protected function filterByPermissionAndAddTargetUrls() {
		foreach ( $this->dataSets as $id => &$dataSet ) {
			$preloadTitle = Title::makeTitle(
				$dataSet['pt_template_namespace'],
				$dataSet['pt_template_title']
			);

			$targetTitle = $this->title;
			if ( $this->config[self::FORCE_NAMESPACE]
				&& (int)$dataSet['pt_target_namespace'] !== static::ALL_NAMESPACES_PSEUDO_ID ) {
				$targetTitle = Title::makeTitle(
					$dataSet['pt_target_namespace'],
					$this->title->getText()
				);
			}

			// If a user can not create or edit a page in the target namespace, we hide the template
			if ( !$targetTitle->userCan( 'create' ) || !$targetTitle->userCan( 'edit' ) ) {
				unset( $this->dataSets[$id] );
				continue;
			}

			$targetUrl = $targetTitle->getLinkURL( [
				'action' => 'edit',
				'preload' => $preloadTitle->getPrefixedDBkey()
			] );
			Hooks::run( 'BSPageTemplatesModifyTargetUrl', [ $targetTitle, $preloadTitle, &$targetUrl ] );
			$dataSet['target_url'] = $targetUrl;
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
			'default' => $this->getAllForDefault(),
			'target' => $this->getAllForTargetNamespace(),
			'other' => $this->getAllForOtherNamespaces(),
			'general' => $this->getAllForAllNamespaces()
		];
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
	 * @return array
	 */
	protected function getAllForAllNamespaces() {
		$filteredDataSets = [];
		foreach ( $this->dataSets as $id => $dataSet ) {
			if ( (int)$dataSet['pt_target_namespace'] === self::ALL_NAMESPACES_PSEUDO_ID ) {
				$filteredDataSets[$id] = $dataSet;
			}
		}

		return [
			self::ALL_NAMESPACES_PSEUDO_ID => $filteredDataSets
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function getAllForTargetNamespace() {
		$filteredDataSets = [];
		foreach ( $this->dataSets as $id => $dataSet ) {
			if ( (int)$dataSet['pt_target_namespace'] === $this->title->getNamespace() ) {
				$filteredDataSets[$id] = $dataSet;
			}
		}

		return [
			$this->title->getNamespace() => $filteredDataSets
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function getAllForOtherNamespaces() {
		$filteredDataSets = [];
		foreach ( $this->dataSets as $id => $dataSet ) {
			// "Empty page" template
			if ( $id === -1 ) {
				continue;
			}

			if ( (int)$dataSet['pt_target_namespace'] === self::ALL_NAMESPACES_PSEUDO_ID ) {
				continue;
			}

			if ( (int)$dataSet['pt_target_namespace'] === $this->title->getNamespace() ) {
				continue;
			}

			if ( !isset( $filteredDataSets[$dataSet['pt_target_namespace']] ) ) {
				$filteredDataSets[$dataSet['pt_target_namespace']] = [];
			}

			$filteredDataSets[$dataSet['pt_target_namespace']][$id] = $dataSet;
		}

		return $filteredDataSets;
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
}
