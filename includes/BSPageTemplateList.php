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
	protected $oTitle = null;

	/**
	 *
	 * @var array
	 */
	protected $aConfig = [];


	public function __construct( $oTitle, $aConfig = [] ) {
		$this->oTitle = $oTitle;
		$this->aConfig = $aConfig + [
			self::FORCE_NAMESPACE => false,
			self::HIDE_IF_NOT_IN_TARGET_NS => true,
			self::HIDE_DEFAULTS => false
		];

		$this->init();
	}

	protected function init() {
		$this->fetchDB();
		$this->filterByPermissionAndAddTargetUrls();
		$this->addDefaultPageTemplate();
	}

	protected $aDataSets = [];

	protected function fetchDB() {
		$dbr = wfGetDB( DB_REPLICA );

		$aConds = [];
		if ( $this->aConfig[self::HIDE_IF_NOT_IN_TARGET_NS] ) {
			$aConds[] = 'pt_target_namespace IN (' . $this->oTitle->getNamespace() . ', -99)';
		}

		$res = $dbr->select(
			'bs_pagetemplate',
			'*',
			$aConds,
			__METHOD__,
			array( 'ORDER BY' => 'pt_label' )
		);

		foreach( $res as $row ) {
			$aDataSet = (array)$row;
			$aDataSet['type'] = strtolower(
				MWNamespace::getCanonicalName( $row->pt_template_namespace )
			);
			$this->aDataSets[$row->pt_id] = $aDataSet;
		}
	}

	protected function addDefaultPageTemplate() {
		if( $this->aConfig[self::HIDE_DEFAULTS] ) {
			return;
		}

		$targetUrl = $this->oTitle->getLinkURL( [ 'action' => 'edit' ] );
		Hooks::run( 'BSPageTemplatesModifyTargetUrl', array( $this->oTitle, null, &$targetUrl ) );

		$this->aDataSets[-1] = [
			'pt_template_title' => null,
			'pt_template_namespace' => null,
			'pt_label' => wfMessage( 'bs-pagetemplates-empty-page' )->plain(),
			'pt_desc' => wfMessage( 'bs-pagetemplates-empty-page-desc' )->plain(),
			'pt_target_namespace' => -98, //Needs to be something non-existent, but I did not want to use well known pseudo namespace ids
			'target_url' => $targetUrl,
			'type' => 'empty'
		];

	}

	protected function filterByPermissionAndAddTargetUrls() {
		foreach( $this->aDataSets as $iId => &$aDataSet ) {
			$oPreloadTitle = Title::makeTitle(
				$aDataSet['pt_template_namespace'],
				$aDataSet['pt_template_title']
			);

			$oTargetTitle = $this->oTitle;
			if( $this->aConfig[self::FORCE_NAMESPACE]
				&& (int)$aDataSet['pt_target_namespace'] !== static::ALL_NAMESPACES_PSEUDO_ID ) {
				$oTargetTitle = Title::makeTitle(
					$aDataSet['pt_target_namespace'],
					$this->oTitle->getText()
				);
			}

			//If a user can not create or edit a page in the target namespace, we hide the template
			if( !$oTargetTitle->userCan( 'create' ) || !$oTargetTitle->userCan( 'edit' ) ) {
				unset( $this->aDataSets[$iId] );
				continue;
			}

			$targetUrl = $oTargetTitle->getLinkURL( [
				'action' => 'edit',
				'preload' => $oPreloadTitle->getPrefixedDBkey()
			] );
			Hooks::run( 'BSPageTemplatesModifyTargetUrl', array( $oTargetTitle, $oPreloadTitle, &$targetUrl ) );
			$aDataSet['target_url'] = $targetUrl;
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getAll() {
		return $this->aDataSets;
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
		$aFilteredDataSets = [];
		foreach( $this->aDataSets as $iId => $aDataSet ) {
			if( $iId < 0 ) {
				$aFilteredDataSets[$iId] = $aDataSet;
			}
		}

		return [
			self::ALL_NAMESPACES_PSEUDO_ID => $aFilteredDataSets
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function getAllForAllNamespaces() {
		$aFilteredDataSets = [];
		foreach( $this->aDataSets as $iId => $aDataSet ) {
			if( (int)$aDataSet['pt_target_namespace'] === self::ALL_NAMESPACES_PSEUDO_ID ) {
				$aFilteredDataSets[$iId] = $aDataSet;
			}
		}

		return [
			self::ALL_NAMESPACES_PSEUDO_ID => $aFilteredDataSets
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function getAllForTargetNamespace() {
		$aFilteredDataSets = [];
		foreach( $this->aDataSets as $iId => $aDataSet ) {
			if( (int)$aDataSet['pt_target_namespace'] === $this->oTitle->getNamespace() ) {
				$aFilteredDataSets[$iId] = $aDataSet;
			}
		}

		return [
			$this->oTitle->getNamespace() => $aFilteredDataSets
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function getAllForOtherNamespaces() {
		$aFilteredDataSets = [];
		foreach( $this->aDataSets as $iId => $aDataSet ) {
			if( $iId === -1 ) { //"Empty page" template
				continue;
			}

			if( (int)$aDataSet['pt_target_namespace'] === self::ALL_NAMESPACES_PSEUDO_ID ) {
				continue;
			}

			if( (int)$aDataSet['pt_target_namespace'] === $this->oTitle->getNamespace() ) {
				continue;
			}

			if( !isset( $aFilteredDataSets[$aDataSet['pt_target_namespace']] ) ) {
				$aFilteredDataSets[$aDataSet['pt_target_namespace']] = [];
			}

			$aFilteredDataSets[$aDataSet['pt_target_namespace']][$iId] = $aDataSet;
		}

		return $aFilteredDataSets;
	}

	/**
	 *
	 * @return int
	 */
	public function getCount() {
		return count( $this->aDataSets );
	}

	/**
	 *
	 * @param int $iId
	 * @param array $aData
	 */
	public function set( $iId, $aData ) {
		$this->aDataSets[$iId] = $aData;
	}
}