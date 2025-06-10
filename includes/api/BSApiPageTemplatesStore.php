<?php

use MediaWiki\Json\FormatJson;
use MediaWiki\Title\Title;

/**
 * This class serves as a backend for the page templates store.
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
 * For further information visit https://bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 *
 * Example request parameters of an ExtJS store
 */
class BSApiPageTemplatesStore extends BSApiExtJSStoreBase {

	/**
	 *
	 * @param string $query
	 * @return array
	 */
	public function makeData( $query = '' ) {
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$res = $dbr->select(
			[ 'bs_pagetemplate' ],
			[
			'pt_id',
			'pt_label',
			'pt_desc',
			'pt_target_namespace',
			'pt_template_title',
			'pt_template_namespace',
			'pt_tags',
			], [], __METHOD__
		);

		$data = [];

		foreach ( $res as $row ) {
			$targetNamespacesIds = FormatJson::decode( $row->pt_target_namespace, true );
			$targetTags = FormatJson::decode( $row->pt_tags ?? '', true );
			if ( !is_array( $targetTags ) ) {
				$targetTags = [];
			}

			$tmp = new stdClass();
			$tmp->id = $row->pt_id;
			$tmp->label = $row->pt_label;
			$tmp->desc = $row->pt_desc;
			$tmp->targetns = implode( ', ', $this->getNamespacesByIds( $targetNamespacesIds ) );
			$tmp->targetnsid = $targetNamespacesIds;
			$title = Title::newFromText( $row->pt_template_title,
					$row->pt_template_namespace );
			$tmp->template = '<a href="' . $title->getFullURL() .
				'" target="_blank" ' .
				( $title->exists() ? '' : 'class="new"' ) .
				'>' . $title->getFullText() . '</a>';
			$tmp->templatename = $title->getFullText();
			$tmp->tags = implode( ', ', $targetTags );
			$data[] = (object)$tmp;
		}

		return $data;
	}

	/**
	 *
	 * @param stdClass $filter
	 * @param stdClass $dataSet
	 * @return bool
	 */
	public function filterString( $filter, $dataSet ) {
		if ( $filter->field !== 'template' ) {
			return parent::filterString( $filter, $dataSet );
		}

		/**
		 * 'template' contains the actual link and filters won't apply correctly
		 * 'templatename' is better filterable
		 */
		return BsStringHelper::filter( $filter->comparison, $dataSet->templatename, $filter->value );
	}

	/**
	 *
	 * @return array
	 */
	protected function getRequiredPermissions() {
		return [ 'wikiadmin' ];
	}

	/**
	 * @param array $targetNamespacesIds
	 * @return array
	 */
	protected function getNamespacesByIds( $targetNamespacesIds ) {
		$namespaces = [];

		if ( is_array( $targetNamespacesIds ) && count( $targetNamespacesIds ) > 0 ) {
			foreach ( $targetNamespacesIds as $nsId ) {
				$nsName = BsNamespaceHelper::getNamespaceName( $nsId, true );
				if ( $nsName ) {
					$namespaces[] = $nsName;
				}
			}
		}

		return $namespaces;
	}
}
