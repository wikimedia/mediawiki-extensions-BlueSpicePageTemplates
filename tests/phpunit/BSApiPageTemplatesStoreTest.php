<?php

use BlueSpice\Tests\BSApiExtJSStoreTestBase;

/**
 * @group medium
 * @group Database
 * @group API
 * @group BlueSpice
 * @group BlueSpicePageTemplates
 */
class BSApiPageTemplatesStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 8;
	protected $tablesUsed = [ 'bs_pagetemplate' ];

	protected function skipAssertTotal() {
		return true;
	}

	protected function getStoreSchema () {
		return [
			'id' => [
				'type' => 'integer'
			],
			'label' => [
				'type' => 'string'
			],
			'desc' => [
				'type' => 'string'
			],
			'targetns' => [
				'type' => 'string'
			],
			'targetnsid' => [
				'type' => 'integer'
			],
			'template' => [
				'type' => 'string'
			],
			'templatename' => [
				'type' => 'string'
			]
		];
	}

	protected function createStoreFixtureData() {
		return;
	}

	public function addDBData() {
		$oPageTemplateFixtures = new BSPageTemplateFixtures();
		foreach( $oPageTemplateFixtures->makeDataSets() as $dataSet ) {
			$this->db->insert( 'bs_pagetemplate', $dataSet );
		}
		return;
	}

	protected function getModuleName () {
		return 'bs-pagetemplates-store';
	}

	public function provideSingleFilterData () {
		return [
			'Filter by label' => [ 'string', 'eq', 'label', 'Test 01', 1]
		];
	}

	public function provideMultipleFilterData() {
		return [
			'Filter by targetnsid and templatename' => [
				[
					[
						'type' => 'integer',
						'comparison' => 'eq',
						'field' => 'targetnsid',
						'value' => NS_FILE
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'label',
						'value' => '01'
					]
				],
				1
			]
		];
	}
}

