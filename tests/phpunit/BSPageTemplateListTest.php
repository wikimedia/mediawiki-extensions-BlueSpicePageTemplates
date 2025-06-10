<?php

use MediaWiki\Title\Title;

/**
 * @group medium
 * @group Database
 * @group BlueSpice
 * @group BlueSpicePageTemplates
 *
 * @covers BSPageTemplateList
 */
class BSPageTemplateListTest extends MediaWikiIntegrationTestCase {

	public function addDBData() {
		$oPageTemplateFixtures = new BSPageTemplateFixtures();
		foreach ( $oPageTemplateFixtures->makeDataSets() as $dataSet ) {
			$this->getDb()->insert(
				'bs_pagetemplate',
				$dataSet,
				__METHOD__
			);
		}
	}

	/**
	 * @dataProvider provideGroupingData
	 */
	public function testGrouping(
		Title $title,
		array $expectedNamespaces,
		array $expectedTags,
		int $expectedDefault
	) {
		$list = new BSPageTemplateList( $title, [
			BSPageTemplateList::HIDE_IF_NOT_IN_TARGET_NS => false
		] );

		$groupedResult = $list->getAllGrouped();

		foreach ( $expectedNamespaces as $nsId => $expectedCount ) {
			$this->assertCount( $expectedCount, $groupedResult['namespaces'][$nsId] );
		}

		foreach ( $expectedTags as $tag => $expectedCount ) {
			$this->assertCount( $expectedCount, $groupedResult['tags'][$tag] );
		}

		$this->assertCount(
			$expectedDefault,
			$groupedResult['default'][BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID]
		);
	}

	public function provideGroupingData() {
		return [
			[
				Title::makeTitle( NS_MAIN, 'Dummy' ),
				[
					NS_MAIN => 2,
					BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID => 2
				],
				[
					'example' => 4,
					'general' => 1,
					'untagged' => 4,
				],
				1
			],
			[
				Title::makeTitle( NS_HELP, 'HelpPage' ),
				[
					NS_HELP => 1,
					BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID => 2
				],
				[
					'example' => 4,
					'general' => 1,
					'untagged' => 4,
				],
				1
			]
		];
	}

	public function testTargetUrlsContainCorrectPreload() {
		$list = new BSPageTemplateList( Title::makeTitle( NS_MAIN, 'Example' ), [
			BSPageTemplateList::FORCE_NAMESPACE => false,
			BSPageTemplateList::HIDE_IF_NOT_IN_TARGET_NS => false
		] );

		foreach ( $list->getAll() as $dataSet ) {
			$url = $dataSet['target_url'];
			$this->assertStringContainsString( 'preload=', $url );
		}
	}

	public function testForceNamespaceAffectsTargetTitle() {
		$list = new BSPageTemplateList( Title::makeTitle( NS_MAIN, 'Dummy' ), [
			BSPageTemplateList::FORCE_NAMESPACE => true,
			BSPageTemplateList::HIDE_IF_NOT_IN_TARGET_NS => false
		] );

		$urlUtils = $this->getServiceContainer()->getUrlUtils();
		$groupedResult = $list->getAllGrouped();
		$found = 0;

		foreach ( $groupedResult['namespaces'] as $nsId => $pageTemplates ) {
			foreach ( $pageTemplates as $pageTemplate ) {
				$url = $urlUtils->parse( $urlUtils->expand( $pageTemplate['target_url'] ) );
				$query = wfCgiToArray( $url['query'] );
				$title = Title::newFromText( $query['title'] );

				$expectedNs = ( $nsId === BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID )
					? $list->getTitle()->getNamespace()
					: $nsId;

				$this->assertEquals( $expectedNs, $title->getNamespace() );
				$found++;
			}
		}

		$this->assertGreaterThan( 0, $found );
	}

	public function testHideDefaults() {
		$list = new BSPageTemplateList( Title::makeTitle( NS_MAIN, 'Dummy' ), [
			BSPageTemplateList::HIDE_DEFAULTS => false
		] );
		$this->assertArrayHasKey(
			BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID,
			$list->getAllGrouped()['default']
		);

		$list2 = new BSPageTemplateList( Title::makeTitle( NS_MAIN, 'Dummy' ), [
			BSPageTemplateList::HIDE_DEFAULTS => true
		] );
		$this->assertSame(
			[],
			$list2->getAllGrouped()['default'][BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID] ?? []
		);
	}

	public function testTagSortingIncludesUntagged() {
		$list = new BSPageTemplateList( Title::makeTitle( NS_MAIN, 'Dummy' ) );
		$grouped = $list->getAllGrouped();
		$this->assertArrayHasKey( 'untagged', $grouped['tags'] );
	}

}
