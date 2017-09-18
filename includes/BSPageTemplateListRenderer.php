<?php

class BSPageTemplateListRenderer {

	protected $sBuffer = '';

	/**
	 *
	 * @param BSPageTemplateList $oList
	 * @return string
	 */
	public function render( $oList ) {
		$this->renderHead( $oList->getCount() );

		$aGroupedLists = $oList->getAllGrouped();
		$this->renderDefaultSection( $aGroupedLists['default'] );
		$this->renderNamespaceSpecificSection( $aGroupedLists['target'], 'target' );
		$this->renderNamespaceSpecificSection( $aGroupedLists['other'], 'other' );
		$this->renderGeneralSection( $aGroupedLists['general'] );

		return $this->sBuffer;
	}

	protected $aOrdering = [];
	protected function initNamespaceSorting() {
		$oSortingTitle = Title::makeTitle( NS_MEDIAWIKI, 'PageTemplatesSorting' );
		$sContent = BsPageContentProvider::getInstance()->getContentFromTitle( $oSortingTitle );
		$this->aOrdering = array_map( 'trim', explode( '*',  $sContent ) );
	}

	protected function renderHead( $iCount ) {
		$this->sBuffer .= Html::rawElement(
			'div',
			[ 'id' => 'bs-pt-head' ],
			wfMessage( 'bs-pagetemplates-choose-template', $iCount )->parse()
		);
	}

	protected function renderGeneralSection( $aDataSets ) {
		$sSectionContent = $this->makeSection(
			wfMessage( 'bs-pagetemplates-general-section' )->plain(),
			$aDataSets[BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID],
			'general'
		);

		$this->appendContainer( $sSectionContent, 'general' );
	}

	protected function renderDefaultSection( $aTemplates ) {
		$sSectionContent = $this->makeSection(
			'',
			$aTemplates[BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID],
			'default'
		);

		$this->appendContainer( $sSectionContent, 'default' );
	}

	protected function makeTemplateItem( $aDataSet, $sAddtionalClass = '' ) {
		$sLink = Html::element(
			'a',
			[
				'class' => 'new bs-pt-link',
				'title' => $aDataSet['pt_template_title'],
				'href' => $aDataSet['target_url']
			],
			$aDataSet['pt_label']
		);
		$sDescription = Html::element(
			'div',
			[ 'class' => 'pt-desc' ],
			$aDataSet['pt_desc']
		);

		return Html::rawElement(
			'div',
			[ 'class' => implode(
					' ',
					[ 'bs-pt-item', $aDataSet['type'], $sAddtionalClass ]
				)
			],
			$sLink . $sDescription
		);
	}

	protected function renderNamespaceSpecificSection( $aTemplates, $sKey ) {
		$sSectionContent = '';
		foreach( $aTemplates as $iNamespaceId => $aDataSets ) {
			$sSectionContent .= $this->makeSection(
				BsNamespaceHelper::getNamespaceName( $iNamespaceId ),
				$aDataSets,
				$sKey
			);
		}

		$this->appendContainer( $sSectionContent, $sKey );
	}

	protected function appendContainer( $sSectionContent, $sKey ) {
		if( !empty( $sSectionContent ) ) {
			$this->sBuffer .= Html::rawElement(
				'div',
				[ 'id' => 'bs-pt-'.$sKey, 'class' => 'bs-pt-sect' ],
				$sSectionContent
			);
		}
	}

	protected function makeSection( $sHeading, $aDataSets, $sKey ) {
		if( empty( $aDataSets ) ) {
			return '';
		}

		$sHeadingElement = '';
		if( !empty( $sHeading ) ) {
			$sHeadingElement = Html::element( 'h3', [], $sHeading );
		}

		$sList = '';
		foreach( $aDataSets as $aDataSet ) {
			$sList .= $this->makeTemplateItem( $aDataSet, $sKey );
		}

		$sList = Html::rawElement(
			'div',
			[ 'class' => 'bs-pt-items' ],
			$sList
		);

		return Html::rawElement(
			'div',
			[ 'id' => 'bs-pt-subsect-'.$sKey, 'class' => 'bs-pt-subsect' ],
			$sHeadingElement . $sList
		);
	}

}