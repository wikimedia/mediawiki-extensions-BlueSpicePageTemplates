<?php

 use BlueSpice\Tests\BSApiTasksTestBase;
use MediaWiki\Json\FormatJson;

/**
 * @group medium
 * @group Database
 * @group API
 * @group BlueSpice
 * @group BlueSpicePageTemplates
 * @covers BSApiPageTemplatesTasks
 */
class BSApiPageTemplatesTasksTest extends BSApiTasksTestBase {

	/**
	 * @return bool
	 */
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
	 * @return string
	 */
	protected function getModuleName() {
		return 'bs-pagetemplates-tasks';
	}

	/**
	 * @covers \BSApiPageTemplatesTasks::task_doEditTemplate
	 */
	public function testDoEditTemplate() {
		// add template
		$oData = $this->executeTask(
			'doEditTemplate',
			[
				'desc' => 'Dummy template',
				'label' => 'Dummy 1',
				'template' => 'Dummy 1 title',
				'targetns' => [ NS_FILE ]
			]
		);

		$this->assertTrue( $oData->success );
		$this->newSelectQueryBuilder()
			->select( [ 'pt_id', 'pt_template_title', 'pt_target_namespace' ] )
			->from( 'bs_pagetemplate' )
			->where( [ 'pt_label' => 'Dummy 1' ] )
			->assertRowValue( [ 9, 'Dummy 1 title', FormatJson::encode( [ NS_FILE ] ) ] );

		$iIDAdded = 9;

		// edit template
		$oData = $this->executeTask(
			'doEditTemplate',
			[
				'id' => $iIDAdded,
				'desc' => 'Faux template',
				'label' => 'Faux 1',
				'template' => 'Faux 1 title',
				'targetns' => [ NS_MAIN ]
			]
		);

		$this->assertTrue( $oData->success );

		$this->newSelectQueryBuilder()
			->select( [ 'pt_template_title', 'pt_target_namespace' ] )
			->from( 'bs_pagetemplate' )
			->where( [ 'pt_id' => 9 ] )
			->assertRowValue( [ 'Faux 1 title', FormatJson::encode( [ NS_MAIN ] ) ] );
	}

	/**
	 * @covers \BSApiPageTemplatesTasks::task_doDeleteTemplates
	 */
	public function testDoDeleteTemplates() {
		$idsToDelete = [ 1, 8 ];

		foreach ( $idsToDelete as $id ) {
			$this->assertFalse( $this->isDeleted( $id ) );
		}

		$data = $this->executeTask(
			'doDeleteTemplates',
			[
				'ids' => $idsToDelete
			]
		);

		$this->assertTrue( $data->success );

		foreach ( $idsToDelete as $id ) {
			$this->assertTrue( $this->isDeleted( $id ) );
		}
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	protected function isDeleted( $id ) {
		$db = $this->getDb();
		$res = $db->select(
			'bs_pagetemplate',
			[ 'pt_id' ],
			[ 'pt_id' => $id ],
			__METHOD__
		);

		return $res->numRows() === 0;
	}
}
