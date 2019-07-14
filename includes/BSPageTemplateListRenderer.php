<?php

class BSPageTemplateListRenderer {

	protected $buffer = '';

	/**
	 *
	 * @param BSPageTemplateList $list
	 * @return string
	 */
	public function render( $list ) {
		$this->renderHead( $list->getCount() );

		$aGroupedLists = $list->getAllGrouped();
		$this->renderDefaultSection( $aGroupedLists['default'] );
		$this->renderNamespaceSpecificSection( $aGroupedLists['target'], 'target' );
		$this->renderNamespaceSpecificSection( $aGroupedLists['other'], 'other' );
		$this->renderGeneralSection( $aGroupedLists['general'] );

		return $this->buffer;
	}

	protected $ordering = [];

	protected function initNamespaceSorting() {
		$sortingTitle = Title::makeTitle( NS_MEDIAWIKI, 'PageTemplatesSorting' );
		$content = BsPageContentProvider::getInstance()->getContentFromTitle( $sortingTitle );
		$this->ordering = array_map( 'trim', explode( '*',  $content ) );
	}

	/**
	 *
	 * @param int $count
	 */
	protected function renderHead( $count ) {
		$this->buffer .= Html::rawElement(
			'div',
			[ 'id' => 'bs-pt-head' ],
			wfMessage( 'bs-pagetemplates-choose-template', $count )->parse()
		);
	}

	/**
	 *
	 * @param array $dataSets
	 */
	protected function renderGeneralSection( $dataSets ) {
		$sectionContent = $this->makeSection(
			wfMessage( 'bs-pagetemplates-general-section' )->plain(),
			$dataSets[BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID],
			'general'
		);

		$this->appendContainer( $sectionContent, 'general' );
	}

	/**
	 *
	 * @param array $templates
	 */
	protected function renderDefaultSection( $templates ) {
		$sectionContent = $this->makeSection(
			'',
			$templates[BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID],
			'default'
		);

		$this->appendContainer( $sectionContent, 'default' );
	}

	/**
	 *
	 * @param array $dataSet
	 * @param string $additionalClass
	 * @return string Raw HTML
	 */
	protected function makeTemplateItem( $dataSet, $additionalClass = '' ) {
		$link = Html::element(
			'a',
			[
				'class' => 'new bs-pt-link',
				'title' => $dataSet['pt_template_title'],
				'href' => $dataSet['target_url']
			],
			$dataSet['pt_label']
		);
		$description = Html::element(
			'div',
			[ 'class' => 'pt-desc' ],
			$dataSet['pt_desc']
		);

		return Html::rawElement(
			'div',
			[ 'class' => implode(
					' ',
					[ 'bs-pt-item', $dataSet['type'], $additionalClass ]
				)
			],
			$link . $description
		);
	}

	/**
	 *
	 * @param array $templates
	 * @param string $key
	 */
	protected function renderNamespaceSpecificSection( $templates, $key ) {
		$sectionContent = '';
		foreach ( $templates as $namespaceId => $dataSets ) {
			$sectionContent .= $this->makeSection(
				BsNamespaceHelper::getNamespaceName( $namespaceId ),
				$dataSets,
				$key
			);
		}

		$this->appendContainer( $sectionContent, $key );
	}

	/**
	 *
	 * @param array $sectionContent
	 * @param string $key
	 */
	protected function appendContainer( $sectionContent, $key ) {
		if ( !empty( $sectionContent ) ) {
			$this->buffer .= Html::rawElement(
				'div',
				[ 'id' => 'bs-pt-' . $key, 'class' => 'bs-pt-sect' ],
				$sectionContent
			);
		}
	}

	/**
	 *
	 * @param string $heading
	 * @param array $dataSets
	 * @param string $key
	 * @return string
	 */
	protected function makeSection( $heading, $dataSets, $key ) {
		if ( empty( $dataSets ) ) {
			return '';
		}

		$headingElement = '';
		if ( !empty( $heading ) ) {
			$headingElement = Html::element( 'h3', [], $heading );
		}

		$listRaw = '';
		foreach ( $dataSets as $aDataSet ) {
			$listRaw .= $this->makeTemplateItem( $aDataSet, $key );
		}

		$list = Html::rawElement(
			'div',
			[ 'class' => 'bs-pt-items' ],
			$listRaw
		);

		return Html::rawElement(
			'div',
			[ 'id' => 'bs-pt-subsect-' . $key, 'class' => 'bs-pt-subsect' ],
			$headingElement . $list
		);
	}

}
