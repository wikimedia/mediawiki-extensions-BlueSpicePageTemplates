<?php

namespace BlueSpice\PageTemplates\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddPageTemplatesTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['pagetemplates:templates'] = [
			'class' => 'Database',
			'config' => [
				'identifier' => 'bs-usagetracker-pagetemplates',
				'descKey' => 'bs-usagetracker-pagetemplates',
				'table' => 'bs_pagetemplate',
				'uniqueColumns' => [ 'pt_id' ]
			]
		];
	}

}
