<?php

namespace BlueSpice\PageTemplates\Hook\MediaWikiPerformAction;

use Article;
use BSPageTemplateList;
use MediaWiki;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Request\WebRequest;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

class PreventEditMode {
	/** @var string[] */
	protected static $editActions = [ 'edit', 'sourceedit', 'formedit' ];

	/**
	 *
	 * @var string[]
	 */
	protected static $contentModels = [ 'wikitext' ];

	/**
	 * @param OutputPage $output
	 * @param Article $article
	 * @param Title $title
	 * @param User $user
	 * @param WebRequest $request
	 * @param MediaWiki $wiki
	 * @return bool
	 */
	public static function callback( $output, $article, $title, $user, $request, $wiki ) {
		if ( $title->exists() ) {
			return true;
		}

		if ( isset( $request->getQueryValues()['preload'] ) ) {
			return true;
		}

		$action = $request->getVal( 'action', $request->getVal( 'veaction', null ) );
		if ( !$action || !in_array( $action, static::$editActions ) ) {
			return true;
		}
		if ( !in_array( $title->getContentModel(), static::$contentModels ) ) {
			return true;
		}

		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$excludeNs = $config->get( 'PageTemplatesExcludeNs' );
		if ( in_array( $title->getNamespace(), $excludeNs ) ) {
			return true;
		}

		if ( static::checkExcludeForTitle( $title->getPrefixedText() ) ) {
			return true;
		}

		$templateList = new BSPageTemplateList( $title );
		$availableTemplates = $templateList->getAll();
		if ( count( $availableTemplates ) === 1 && isset( $availableTemplates[-1] ) ) {
			// index -1 is "Empty page"
			$emptyPageTemplate = $availableTemplates[-1];
			$targetUrl = static::ensurePreloadParam( $emptyPageTemplate['target_url'] );
			$output->redirect( $targetUrl );
		} else {
			$output->redirect( $title->getLocalURL( [ 'action' => 'view' ] ) );
		}

		return true;
	}

	/**
	 * Make sure that "preload" param is present in the URL
	 * to prevent infinite loops
	 *
	 * @param string $target
	 * @return string
	 */
	private static function ensurePreloadParam( $target ) {
		if ( strpos( $target, 'preload=' ) === false ) {
			return wfAppendQuery( $target, [ 'preload' => '' ] );
		}

		return $target;
	}

	/**
	 * @param string $titleText
	 * @return bool
	 */
	private static function checkExcludeForTitle( $titleText ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$excludeRegex = $config->get( 'PageTemplatesExcludeRegex' );
		foreach ( $excludeRegex as $exclude ) {
			$excludePattern = '/' . $exclude . '/';
			if ( preg_match( $excludePattern, $titleText ) ) {
				return true;
			}
		}
		return false;
	}

}
