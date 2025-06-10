<?php

use MediaWiki\Html\Html;
use MediaWiki\Html\TemplateParser;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Sanitizer;
use MediaWiki\Title\Title;
use OOUI\ButtonGroupWidget;
use OOUI\SearchInputWidget;

class BSPageTemplateListRenderer {

	/** @var string */
	protected $buffer = '';

	/**
	 * @var TemplateParser
	 */
	private $templateParser = null;

	public function __construct() {
		$this->templateParser = new TemplateParser( dirname( __DIR__ ) . '/resources/templates' );
	}

	/**
	 *
	 * @param BSPageTemplateList $list
	 * @return string
	 */
	public function render( $list ) {
		$aGroupedLists = $list->getAllGrouped();
		$this->buffer .= wfMessage( 'bs-pagetemplates-choose-template-empty' )->parse();
		$this->renderDefaultSection( $aGroupedLists['default'] );
		$this->renderHead( $list->getCount() );
		$this->renderTagSpecificSection( $aGroupedLists['tags'] );
		$this->renderNamespaceSection( $aGroupedLists['namespaces'] );

		return $this->buffer;
	}

	/** @var string[] */
	protected $ordering = [];

	protected function initNamespaceSorting() {
		$sortingTitle = Title::makeTitle( NS_MEDIAWIKI, 'PageTemplatesSorting' );
		$content = BsPageContentProvider::getInstance()->getContentFromTitle( $sortingTitle );
		$this->ordering = array_map( 'trim', explode( '*', $content ) );
	}

	/**
	 *
	 * @param int $count
	 */
	protected function renderHead( $count ) {
		$this->buffer .= Html::openElement(
				'div',
				[ 'id' => 'bs-pt-head' ]
			);

		$textInput =
		'<div class="bs-template-search">' .
			new SearchInputWidget( [
				'id' => 'bs-template-search-input',
				'infusable' => true,
				'classes' => [ 'template-search-field' ],
				'placeholder' => wfMessage( 'bs-pagetemplates-search-template-placeholder' )->text(),
			] ) .
			new ButtonGroupWidget( [
				'id' => 'bs-template-search-namespace-tag-buttongroup',
				'classes' => [ 'template-namespace-tag-picker' ],
				'infusable' => true,
				'items' => [
					new OOUI\ButtonWidget( [
						'data' => 'visual',
						'active' => true,
						'infusable' => true,
						'label' => "Tags",
						'id' => 'bs-template-search-tag-button'
					] ),
					new OOUI\ButtonWidget( [
						'data' => 'visual',
						'label' => "Namespaces",
						'infusable' => true,
						'id' => 'bs-template-search-ns-button'
					] )
				]
			] ) .
			'</div>';
		$this->buffer .= $textInput;
		$this->buffer .= Html::closeElement( 'div' );
	}

	/**
	 *
	 * @param array $templates
	 */
	protected function renderDefaultSection( $templates ) {
		$sectionContent = $this->makeSection(
			'',
			$templates[BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID],
			'default',
			''
		);
		$this->appendContainer( $sectionContent, 'default' );
	}

	/**
	 *
	 * @param array $datasets
	 */
	protected function renderNamespaceSection( $datasets ) {
		$namespaceSectionContent = '';
		foreach ( $datasets as $namespaceId => $templates ) {
			$sectionContent = '';

			$nsName = BsNamespaceHelper::getNamespaceName( $namespaceId, true );
			if ( !$nsName ) {
				$nsName = Message::newFromKey( 'bs-pagetemplates-ns-undefined' )->text();
			}

			$messageKey = "bs-pagetemplates-namespace-$namespaceId";
			$message = Message::newFromKey( $messageKey );
			$label = $nsName;
			if ( $message->exists() ) {
				$label = $message->text();
			}

			$sectionContent .= $this->makeSection(
				$label,
				$templates,
				'namespace-template',
				'tag'
			);

			$namespaceSectionContent .= Html::rawElement(
				'div',
				[
					'id' => Sanitizer::escapeIdForAttribute( 'bs-ns-subsect-' . $nsName ),
					'class' => 'bs-ns-subsect row'
				],
				$sectionContent
			);
		}
		$this->appendContainer( $namespaceSectionContent, 'ns', 'display: none;' );
	}

	/**
	 *
	 * @param array $dataSet
	 * @param string $additionalClass
	 * @param string $key
	 * @return string Raw HTML
	 */
	protected function makeTemplateItem( $dataSet, $additionalClass = '', $key = '' ) {
		$link = Html::element(
			'a',
			[
				'class' => 'new bs-pt-link',
				'title' => $dataSet['pt_template_title'],
				'href' => $dataSet['target_url']
			],
			$dataSet['pt_label']
		);

		$badges = [];
		if ( $key === 'tag' ) {
			$badges = json_decode( $dataSet['pt_tags'] ?? '', true );
		} elseif ( $key === 'ns' ) {
			$badges = json_decode( $dataSet['pt_target_namespace'], true );
		}

		if ( !$badges ) {
			$badges = [];
		}

		$badges = array_values( $badges );
		$badges = array_map( static function ( $badge ) {
			$nsName = BsNamespaceHelper::getNamespaceName( $badge, true );
			if ( !$nsName ) {
				return $badge;
			}
			return $nsName;
		}, $badges );

		return $this->templateParser->processTemplate( 'tile', [
			'id' => Sanitizer::escapeIdForAttribute( 'bs-pt-item-' . $dataSet['pt_label'] ),
			'link' => $link,
			'desc' => $dataSet['pt_desc'],
			'badges' => $badges,
			'has-badges' => count( $badges ) > 0,
		] );
	}

	/**
	 *
	 * @param array $templates
	 */
	protected function renderTagSpecificSection( $templates ) {
		$tagSectionContent = '';
		foreach ( $templates as $tagsId => $templates ) {
			$sectionContent = '';

			$messageKey = "bs-pagetemplates-tag-$tagsId";
			$message = Message::newFromKey( $messageKey );
			$label = $tagsId;

			if ( $message->exists() ) {
				$label = $message->escaped();
			}

			if ( $tagsId === 'untagged' ) {
				$label = "<em>$label</em>";
			}
			$sectionContent .= $this->makeSection(
				$label,
				$templates,
				'tag-templates',
				'ns'
			);

			$tagSectionContent .= Html::rawElement(
				'div',
				[
					'id' => Sanitizer::escapeIdForAttribute( 'bs-tags-subsect-' . $tagsId ),
					'class' => 'bs-tag-subsect row'
				],
				$sectionContent
			);
		}
		$this->appendContainer( $tagSectionContent, 'tag' );
	}

	/**
	 *
	 * @param array $sectionContent
	 * @param string $key
	 * @param string $additionalStyles
	 */
	protected function appendContainer( $sectionContent, $key, $additionalStyles = '' ) {
		if ( !empty( $sectionContent ) ) {
			$this->buffer .= Html::rawElement(
				'div',
				[ 'id' => 'bs-' . $key . '-container', 'class' => 'bs-pt-sect', 'style' => $additionalStyles ],
				$sectionContent
			);
		}
	}

	/**
	 *
	 * @param string $heading
	 * @param array $dataSets
	 * @param string $additionalClasses
	 * @param string $key
	 * @return string
	 */
	protected function makeSection( $heading, $dataSets, $additionalClasses, $key ) {
		if ( empty( $dataSets ) ) {
			return '';
		}

		$headingElement = '';
		if ( !empty( $heading ) ) {
			$headingElement = Html::rawElement( 'h3', [], $heading );
		}

		$listRaw = '';
		foreach ( $dataSets as $aDataSet ) {
			$listRaw .= $this->makeTemplateItem( $aDataSet, $additionalClasses, $key );
		}

		$list = Html::rawElement(
			'div',
			[ 'class' => 'row row-cols-3 g-4' ],
			$listRaw
		);

		if ( !$heading ) {
			$heading = $key;
		}
		return $headingElement . $list;
	}

}
