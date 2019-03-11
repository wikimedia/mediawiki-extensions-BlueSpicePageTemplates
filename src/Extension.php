<?php

namespace BlueSpice\PageTemplates;

/**
 * PageTemplates extension for BlueSpice
 *
 * Displays a list of templates marked as page templates when creating a new article.
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
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage PageTemplates
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Linker\LinkTarget;
use PermissionsError;

/**
 * Base class for PageTemplates extension
 * @package BlueSpice_Extensions
 * @subpackage PageTemplates
 */
class Extension extends \BlueSpice\Extension {

	/**
	 * Automatically modifies "noarticletext" message. Otherwise, you would
	 * have to modify MediaWiki:noarticletext in the wiki, wich causes
	 * installation overhead.
	 * @param string $key The message key. Note that it comes ucfirst and can
	 * be an i18n version (e.g. Noarticletext/de-formal)
	 * @param string &$message This variable is called by reference and modified.
	 * @return bool Success marker for MediaWiki Hooks. The message itself is
	 * returned in referenced variable $sMessage. Note that it cannot contain pure HTML.
	 * @throws PermissionsError
	 */
	public static function onMessagesPreLoad( $key, &$message ) {
		if ( strstr( $key, 'Noarticletext' ) === false ) {
			return true;
		}

		$title = \RequestContext::getMain()->getTitle();
		if ( !is_object( $title ) ) {
			return true;
		}

		/*
		 * As we are in view mode but we present the user only links to
		 * edit/create mode we do a preemptive check wether or not th user
		 * also has edit/create permission
		 */
		if ( $title->isSpecialPage() ) {
			return true;
		}
		if ( !$title->userCan( 'edit' ) ) {
			throw new PermissionsError( 'edit' );
		} elseif ( !$title->userCan( 'createpage' ) ) {
			throw new PermissionsError( 'createpage' );
		} else {
			$message = '<bs:pagetemplates />';
		}

		return true;
	}

	/**
	 *
	 * @param \LinkRenderer $linkRenderer
	 * @param \LinkTarget $target
	 * @param null | string | \HtmlArmor &$text
	 * @param array &$extraAttribs
	 * @param string &$query
	 * @param string &$ret
	 * @return bool
	 */
	public static function onHtmlPageLinkRendererBegin( LinkRenderer $linkRenderer,
		LinkTarget $target, &$text, &$extraAttribs, &$query, &$ret ) {
		if ( in_array( 'known', $extraAttribs, true ) ) {
			return true;
		}
		if ( !in_array( 'broken', $extraAttribs, true ) ) {
			// It's not marked as "known" and not as "broken" so we have to check
			$title = \Title::makeTitle(
					$target->getNamespace(), $target->getText()
			);
			if ( !$title || $title->isKnown() ) {
				return true;
			}
		}

		$config = \BlueSpice\Services::getInstance()->getConfigFactory()
				->makeConfig( 'bsg' );
		$excludeNs = $config->get( 'PageTemplatesExcludeNs' );
		if ( in_array( $target->getNamespace(), $excludeNs ) ) {
			return true;
		}

		if ( !isset( $query['preload'] ) ) {
			$query['action'] = 'view';
		}

		return true;
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array &$collectorsConfig
	 * @return bool Always true to keep hook running
	 */
	public static function onBSUsageTrackerRegisterCollectors( &$collectorsConfig ) {
		$collectorsConfig['pagetemplates:templates'] = [
			'class' => 'Database',
			'config' => [
				'identifier' => 'bs-usagetracker-pagetemplates',
				'descKey' => 'bs-usagetracker-pagetemplates',
				'table' => 'bs_pagetemplate',
				'uniqueColumns' => [ 'pt_id' ]
			]
		];
		return true;
	}

}
