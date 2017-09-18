<?php
/**
 * Admin section for PageTemplates
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage PageTemplates
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for page template admin
 * @package BlueSpice_Extensions
 * @subpackage PageTemplates
 */
class PageTemplatesAdmin {

	/**
	 * Back reference to base extension.
	 * @var BsExtensionMW
	 */
	protected $oExtension;

	/**
	 * Constructor of PageTemplatesAdmin class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->oExtension = BsExtensionManager::getExtension( 'PageTemplates' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}
}