<?php

class BSPageTemplateFixtures {

	public function __construct() {
	}

	public function makeDataSets() {
		return [
			[
				'pt_template_title' => 'Test_01',
				'pt_template_namespace' => NS_TEMPLATE,
				'pt_label' => 'Test 01',
				'pt_desc' => 'Lorem ipsum',
				'pt_target_namespace' => NS_MAIN
			],
			[
				'pt_template_title' => 'Test_02',
				'pt_template_namespace' => NS_TEMPLATE,
				'pt_label' => 'Test 02',
				'pt_desc' => 'Lorem ipsum',
				'pt_target_namespace' => NS_MAIN
			],
			[
				'pt_template_title' => 'Test_03',
				'pt_template_namespace' => NS_HELP,
				'pt_label' => 'Test 03',
				'pt_desc' => 'Lorem ipsum',
				'pt_target_namespace' => NS_HELP
			],
			[
				'pt_template_title' => 'Test_04',
				'pt_template_namespace' => NS_FILE,
				'pt_label' => 'Test 04',
				'pt_desc' => 'Lorem ipsum',
				'pt_target_namespace' => NS_FILE
			],
			[
				'pt_template_title' => 'Test_05',
				'pt_template_namespace' => NS_FILE,
				'pt_label' => 'Test 05',
				'pt_desc' => 'Lorem ipsum',
				'pt_target_namespace' => NS_FILE
			],
			[
				'pt_template_title' => 'Test_06',
				'pt_template_namespace' => NS_FILE,
				'pt_label' => 'Test 06',
				'pt_desc' => 'Lorem ipsum',
				'pt_target_namespace' => NS_FILE
			],

			[
				'pt_template_title' => 'Test_07',
				'pt_template_namespace' => NS_TEMPLATE,
				'pt_label' => 'Test 07',
				'pt_desc' => 'Lorem ipsum',
				'pt_target_namespace' => BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID
			],

			[
				'pt_template_title' => 'Test_08',
				'pt_template_namespace' => NS_TEMPLATE,
				'pt_label' => 'Test 08',
				'pt_desc' => 'Lorem ipsum',
				'pt_target_namespace' => BSPageTemplateList::ALL_NAMESPACES_PSEUDO_ID
			],
		];
	}
}