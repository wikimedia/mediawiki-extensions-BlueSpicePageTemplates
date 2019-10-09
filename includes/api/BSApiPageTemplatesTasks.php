<?php
/**
 * Provides the page templates tasks api for BlueSpice.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

/**
 * GroupManager Api class
 * @package BlueSpice_Extensions
 */
class BSApiPageTemplatesTasks extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = [
		'doEditTemplate' => [
			'examples' => [
				[
					'label' => 'New template',
					'desc' => 'Some description',
					'template' => 'Template1'
				],
				[
					'label' => 'New template',
					'desc' => 'Some description',
					'template' => 'Template1',
					'targetns' => 123
				],
				[
					'label' => 'Edited template',
					'template' => 'Template1',
					'id' => 192
				]
			],
			'params' => [
				'desc' => [
					'desc' => 'Description for template - max. 255 characters',
					'type' => 'string',
					'required' => false
				],
				'label' => [
					'desc' => 'Label for template - max. 255 characters',
					'type' => 'string',
					'required' => true
				],
				'template' => [
					'desc' => 'Valid Title name',
					'type' => 'string',
					'required' => true
				],
				'targetns' => [
					'desc' => 'Valid Namespace ID',
					'type' => 'integer',
					'required' => false,
					'default' => 0
				],
				'id' => [
					'desc' => 'ID of template',
					'type' => 'integer',
					'required' => false
				]
			]
		],
		'doDeleteTemplates' => [
			'examples' => [
				[
					'ids' => [ 123, 19, 48 ]
				]
			],
			'params' => [
				'ids' => [
					'desc' => 'Array of IDs to delete',
					'type' => 'array',
					'required' => true
				]
			]
		]
	];

	/**
	 * Creates or changes a template
	 * @param stdClass $taskData
	 * @param array $params
	 * @return BSStandardAPIResponse
	 */
	protected function task_doEditTemplate( $taskData, $params ) {
		$oReturn = $this->makeStandardReturn();

		$sDesc = isset( $taskData->desc ) ? $taskData->desc : '';
		$sLabel = isset( $taskData->label ) ? $taskData->label : '';
		$sTemplateName = isset( $taskData->template ) ? $taskData->template : '';
		$iOldId = isset( $taskData->id ) ? $taskData->id : null;
		$targetNamespaces = isset( $taskData->targetns ) ? $taskData->targetns : [];

		if ( empty( $sDesc ) ) {
			$sDesc = ' ';
		}

		// TODO RBV (18.05.11 09:19): Use validators
		if ( strlen( $sDesc ) >= 255 ) {
			$oReturn->message = wfMessage( 'bs-pagetemplates-tpl-desc-toolong' )->plain();
			return $oReturn;
		}

		if ( strlen( $sLabel ) >= 255 ) {
			$oReturn->message = wfMessage( 'bs-pagetemplates-tpl-label-toolong' )->plain();
			return $oReturn;
		}

		if ( strlen( $sLabel ) == 0 ) {
			$oReturn->message = wfMessage( 'bs-pagetemplates-tpl-label-empty' )->plain();
			return $oReturn;
		}

		if ( strlen( $sTemplateName ) >= 255 ) {
			$oReturn->message = wfMessage( 'bs-pagetemplates-tpl-name-toolong' )->plain();
			return $oReturn;
		}

		if ( strlen( $sTemplateName ) == 0 ) {
			$oReturn->message = wfMessage( 'bs-pagetemplates-tpl-name-empty' )->plain();
			return $oReturn;
		}

		$oDbw = wfGetDB( DB_MASTER );

		$oTitle = Title::newFromText( $sTemplateName );
		if ( !$oTitle ) {
			$oReturn->message = wfMessage( 'compare-invalid-title' )->plain();
			return $oReturn;
		}
		// This is the add template part
		if ( empty( $iOldId ) ) {
			$oDbw->insert(
				'bs_pagetemplate',
				[
					'pt_label' => $sLabel,
					'pt_desc' => $sDesc,
					'pt_template_title' => $oTitle->getText(),
					'pt_template_namespace' => $oTitle->getNamespace(),
					'pt_target_namespace' => FormatJson::encode( $targetNamespaces ),
					'pt_sid' => 0,
				]
			);
			$oReturn->success = true;
			$oReturn->message = wfMessage( 'bs-pagetemplates-tpl-added' )->plain();
		// and here we have edit template
		} else {
			$rRes = $oDbw->select( 'bs_pagetemplate', 'pt_id', [ 'pt_id' => $iOldId ] );
			$iNumRow = $oDbw->numRows( $rRes );
			if ( !$iNumRow ) {
				$oReturn->message = wfMessage( 'bs-pagetemplates-nooldtpl' )->plain();
				return $oReturn;
			}

			// $oDbw = wfGetDB( DB_MASTER );
			$rRes = $oDbw->update(
				'bs_pagetemplate',
				[
					'pt_id' => $iOldId,
					'pt_label' => $sLabel,
					'pt_desc' => $sDesc,
					'pt_template_title' => $oTitle->getText(),
					'pt_template_namespace' => $oTitle->getNamespace(),
					'pt_target_namespace' => FormatJson::encode( $targetNamespaces )
				],
				[ 'pt_id' => $iOldId ]
			);

			if ( $rRes === false ) {
				$oReturn->message = wfMessage( 'bs-pagetemplates-dberror' )->plain();
				return $oReturn;
			}

			$oReturn->success = true;
			$oReturn->message = wfMessage( 'bs-pagetemplates-tpl-edited' )->plain();
		}

		return $oReturn;
	}

	/**
	 * Deletes one or several templates
	 * @param stdClass $taskData
	 * @param array $params
	 * @return BSStandardAPIResponse
	 */
	protected function task_doDeleteTemplates( $taskData, $params ) {
		$return = $this->makeStandardReturn();

		$ids = isset( $taskData->ids ) ? (array)$taskData->ids : [];

		if ( !is_array( $ids ) || count( $ids ) == 0 ) {
			$return->message = wfMessage( 'bs-pagetemplates-no-id' )->plain();
			return $return;
		}

		$output = [];

		foreach ( $ids as $id => $name ) {

			$dbw = wfGetDB( DB_MASTER );
			$res = $dbw->delete( 'bs_pagetemplate', [ 'pt_id' => $id ] );

			if ( $res === false ) {
				$return->message = wfMessage( 'bs-pagetemplates-dberror' )->plain();
				return $return;
			}

		}

		$return->success = true;
		$return->message = wfMessage( 'bs-pagetemplates-tpl-deleted' )->plain();

		return $return;
	}

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'doEditTemplate' => [ 'wikiadmin' ],
			'doDeleteTemplates' => [ 'wikiadmin' ],
		];
	}
}
