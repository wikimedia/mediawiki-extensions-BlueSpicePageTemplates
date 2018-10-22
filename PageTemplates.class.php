<?php
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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Linker\LinkTarget;

/**
 * Base class for PageTemplates extension
 * @package BlueSpice_Extensions
 * @subpackage PageTemplates
 */
class PageTemplates extends BsExtensionMW {

	/**
	 * Initialization of PageTemplates extension
	 */
	protected function initExt() {
		//Hooks
		$this->setHook( 'MessagesPreLoad' );
		$this->setHook( 'ParserFirstCallInit' );
	}

	/**
	 * Automatically modifies "noarticletext" message. Otherwise, you would
	 * have to modify MediaWiki:noarticletext in the wiki, wich causes
	 * installation overhead.
	 * @param string $sKey The message key. Note that it comes ucfirst and can be an i18n version (e.g. Noarticletext/de-formal)
	 * @param string $sMessage This variable is called by reference and modified.
	 * @return bool Success marker for MediaWiki Hooks. The message itself is returned in referenced variable $sMessage. Note that it cannot contain pure HTML.
	 * @throws PermissionsError
	 */
	public function onMessagesPreLoad( $sKey, &$sMessage ) {
		if ( strstr( $sKey, 'Noarticletext' ) === false ) {
			return true;
		}

		$oTitle = $this->getTitle();
		if ( !is_object( $oTitle ) ) {
			return true;
		}

		/*
		 * As we are in view mode but we present the user only links to
		 * edit/create mode we do a preemptive check wether or not th user
		 * also has edit/create permission
		 */
		if ( $oTitle->isSpecialPage() ) {
			return true;
		}
		if ( !$oTitle->userCan( 'edit' ) ) {
			throw new PermissionsError( 'edit' );
		} elseif ( !$oTitle->userCan( 'createpage' ) ) {
			throw new PermissionsError( 'createpage' );
		} else {
			$sMessage = '<bs:pagetemplates />';
		}

		return true;
	}

	/**
	 * Registers the pagetemplate tag with the parser
	 * @param Parser $parser The parser object of MediaWiki
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onParserFirstCallInit( &$parser ) {
		$parser->setHook( 'pagetemplates', array( $this, 'onTagPageTemplates' ) );
		$parser->setHook( 'bs:pagetemplates', array( $this, 'onTagPageTemplates' ) );
		return true;
	}

	/**
	 * Callback function that is triggered when the parser encounters a pagetemplate tag
	 * @param string $input innerHTML of the tag
	 * @param array $args tag attributes
	 * @param Parser $parser the parser object of MediaWiki
	 * @return string replacement HTML for the tag
	 */
	public function onTagPageTemplates( $input, $args, $parser ) {
		$parser->getOutput()->addModules( 'ext.bluespice.pageTemplates.tag' );
		$parser->getOutput()->addModuleStyles( 'ext.bluespice.pageTemplates.styles' );
		return $this->renderPageTemplates();
	}

	/**
	 * Renders the pagetemplates form which is displayed when creating a new article
	 * @param bool $bReturnHTML If set, the form is returned as HTML, otherwise as wiki code.
	 * @return string The rendered output
	 */
	protected function renderPageTemplates() {
		$oTitle = $this->getTitle();
		// if we are not on a wiki page, return. This is important when calling import scripts that try to create nonexistent pages, e.g. importImages
		if ( !is_object( $oTitle ) ) return true;

		$config = \MediaWiki\MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );

		$oPageTemplateList = new BSPageTemplateList( $oTitle, array(
			BSPageTemplateList::HIDE_IF_NOT_IN_TARGET_NS => $config->get( 'PageTemplatesHideIfNotInTargetNs' ),
			BSPageTemplateList::FORCE_NAMESPACE => $config->get( 'PageTemplatesForceNamespace' ),
			BSPageTemplateList::HIDE_DEFAULTS => $config->get( 'PageTemplatesHideDefaults' )
		) );

		$oPageTemplateListRenderer = new BSPageTemplateListRenderer();
		Hooks::run( 'BSPageTemplatesBeforeRender', [ $this, &$oPageTemplateList, &$oPageTemplateListRenderer, $oTitle ] );
		return $oPageTemplateListRenderer->render( $oPageTemplateList );
	}

	public static function onHtmlPageLinkRendererBegin( LinkRenderer $linkRenderer, LinkTarget $target, &$text, &$extraAttribs, &$query, &$ret ) {
		if ( in_array( 'known', $extraAttribs, true ) ) {
			return true;
		}
		if ( !in_array( 'broken', $extraAttribs, true ) ){ //It's not marked as "known" and not as "broken" so we have to check
			$title = \Title::makeTitle(
				$target->getNamespace(),
				$target->getText()
			);
			if ( !$title || $title->isKnown() ) {
				return true;
			}
		}

		$config = \MediaWiki\MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$aExNs = $config->get( 'PageTemplatesExcludeNs' );
		if ( in_array( $target->getNamespace(), $aExNs ) ) {
			return true;
		}

		if ( !isset( $query['preload'] ) ) {
			$query['action'] = 'view';
		}

		return true;
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array $aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public static function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['pagetemplates:templates'] = array(
			'class' => 'Database',
			'config' => array(
				'identifier' => 'bs-usagetracker-pagetemplates',
				'descKey' => 'bs-usagetracker-pagetemplates',
				'table' => 'bs_pagetemplate',
				'uniqueColumns' => array( 'pt_id' )
			)
		);
		return true;
	}

}
