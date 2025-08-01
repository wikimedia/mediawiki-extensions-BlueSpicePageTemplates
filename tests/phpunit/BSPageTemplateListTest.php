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

	protected function skipAssertTotal() {
		return true;
	}

	public function addDBData() {
		// addDBDataOnce fails with usage of @dataProvider...
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
	 *
	 * @param Title $targetTitle
	 * @param int $expectedTargetCount
	 * @param int $expectedOtherCount
	 *
	 * @dataProvider provideGroupingData
	 */
	public function testGrouping( $targetTitle, $expectedTargetCount, $expectedOtherCount ) {
		$list = new BSPageTemplateList( $targetTitle, [
			BSPageTemplateList::HIDE_IF_NOT_IN_TARGET_NS => false
		] );

		$groupedResult = $list->getAllGrouped();

		$this->assertEquals( $expectedTargetCount,
			$this->getWholeCount( $groupedResult['target'] ) );
		$this->assertEquals( $expectedOtherCount,
			$this->getWholeCount( $groupedResult['other'] ) );
		$this->assertCount( 1,
			$groupedResult['default'][BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID] );
		$this->assertCount( 2,
			$groupedResult['general'][BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID] );
	}

	public function provideGroupingData() {
		return [
			'namespace main' => [ Title::makeTitle( NS_MAIN, 'Dummy' ), 2, 4 ],
			'namespace help' => [ Title::makeTitle( NS_HELP, 'Dummy' ), 1, 5 ],
			'namespace file' => [ Title::makeTitle( NS_FILE, 'Dummy' ), 3, 3 ],
		];
	}

	protected function getWholeCount( $groupedResult ) {
		$count = 0;

		foreach ( $groupedResult as $nsId => $pageTemplates ) {
			$count += count( $pageTemplates );
		}

		return $count;
	}

	public function testForceNamespace() {
		$list = new BSPageTemplateList( Title::makeTitle( NS_MAIN, 'Dummy' ), [
			BSPageTemplateList::FORCE_NAMESPACE => true,
			BSPageTemplateList::HIDE_IF_NOT_IN_TARGET_NS => false
		] );

		$urlUtils = $this->getServiceContainer()->getUrlUtils();
		$groupedResult = $list->getAllGrouped();
		foreach ( $groupedResult['other'] as $nsId => $pageTemplates ) {
			foreach ( $pageTemplates as $pageTemplate ) {
				$url = $urlUtils->parse( $urlUtils->expand( $pageTemplate['target_url'] ) );
				$query = wfCgiToArray( $url['query'] );
				$this->assertEquals( $nsId, Title::newFromText( $query['title'] )->getNamespace() );
			}
		}
	}

	public function testHideIfNotInTargetNamespace() {
		$list = new BSPageTemplateList( Title::makeTitle( NS_MAIN, 'Dummy' ), [
			BSPageTemplateList::HIDE_IF_NOT_IN_TARGET_NS => false
		] );
		$groupedResult = $list->getAllGrouped();

		$this->assertEquals( 9, $list->getCount() );
		$this->assertNotEquals( 0, $this->getWholeCount( $groupedResult['other'] ) );

		$list2 = new BSPageTemplateList( Title::makeTitle( NS_MAIN, 'Dummy' ), [
			BSPageTemplateList::HIDE_IF_NOT_IN_TARGET_NS => true,
			BSPageTemplateList::UNSET_TARGET_NAMESPACES => false
		] );
		$groupedResult2 = $list2->getAllGrouped();

		$this->assertEquals( 5, $list2->getCount() );
		$this->assertSame( 0, $this->getWholeCount( $groupedResult2['other'] ) );
	}

	public function testHideDefaults() {
		$list = new BSPageTemplateList( Title::makeTitle( NS_MAIN, 'Dummy' ), [
			BSPageTemplateList::HIDE_DEFAULTS => false
		] );
		$groupedResult = $list->getAllGrouped();

		$this->assertEquals( 5, $list->getCount() );
		$this->assertNotEquals( 0, $this->getWholeCount( $groupedResult['default'] ) );

		$list2 = new BSPageTemplateList( Title::makeTitle( NS_MAIN, 'Dummy' ), [
			BSPageTemplateList::HIDE_DEFAULTS => true
		] );
		$groupedResult2 = $list2->getAllGrouped();

		$this->assertEquals( 4, $list2->getCount() );
		$this->assertSame( 0, $this->getWholeCount( $groupedResult2['default'] ) );
	}
}
