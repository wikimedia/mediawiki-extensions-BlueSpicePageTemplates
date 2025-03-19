<?php

namespace BlueSpice\PageTemplates\ContentImport;

use Wikimedia\Rdbms\IDatabase;

class PageTemplateDAO {

	/**
	 * Database connection object (DB_PRIMARY with write access needed)
	 *
	 * @var IDatabase
	 */
	private $db;

	/**
	 * @param IDatabase $db Database connection object (DB_PRIMARY with write access needed)
	 */
	public function __construct( IDatabase $db ) {
		$this->db = $db;
	}

	/**
	 * @param string $title
	 * @param int $namespace
	 *
	 * @return bool
	 */
	public function templateExists( string $title, int $namespace ): bool {
		$conds = [
			'pt_template_title' => $title,
			'pt_template_namespace' => $namespace
		];

		$isTemplatePersisted = $this->db->selectField(
			'bs_pagetemplate',
			'pt_id',
			$conds,
			__METHOD__
		);

		return $isTemplatePersisted;
	}

	/**
	 * @param string $label
	 * @param string $description
	 * @param string $title
	 * @param string $namespace
	 *
	 * @return void
	 */
	public function insertTemplate( string $label, string $description, string $title, string $namespace ): void {
		$set = [
			'pt_label' => $label,
			'pt_desc' => $description,
			'pt_target_namespace' => json_encode( [ -99 ] ),
			'pt_template_title' => $title,
			'pt_template_namespace' => $namespace
		];

		$this->db->insert(
			'bs_pagetemplate',
			$set,
			__METHOD__
		);
	}

	/**
	 * @param string $label
	 * @param string $description
	 * @param string $title
	 * @param string $namespace
	 *
	 * @return void
	 */
	public function updateTemplate( string $label, string $description, string $title, string $namespace ): void {
		$conds = [
			'pt_template_title' => $title,
			'pt_template_namespace' => $namespace
		];

		$set = [
			'pt_label' => $label,
			'pt_desc' => $description,
			'pt_target_namespace' => json_encode( [ -99 ] ),
			'pt_template_title' => $title,
			'pt_template_namespace' => $namespace
		];

		$this->db->update(
			'bs_pagetemplate',
			$set,
			$conds,
			__METHOD__
		);
	}
}
