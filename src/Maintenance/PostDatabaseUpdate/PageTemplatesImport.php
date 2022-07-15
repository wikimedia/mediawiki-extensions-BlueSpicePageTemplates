<?php

namespace BlueSpice\PageTemplates\Maintenance\PostDatabaseUpdate;

use BlueSpice\DistributionConnector\ContentImport\ImportLanguage;
use BlueSpice\PageTemplates\ContentImport\PageTemplateDAO;
use ExtensionRegistry;
use LoggedUpdateMaintenance;
use MediaWiki\MediaWikiServices;

class PageTemplatesImport extends LoggedUpdateMaintenance {

	/**
	 * @var MediaWikiServices
	 */
	private $services;

	/**
	 * Database access object for operations with "bs_pagetemplate" table.
	 *
	 * @var PageTemplateDAO
	 */
	private $dao;

	/**
	 * Code of language which is used to import.
	 *
	 * @var string
	 */
	private $importLanguageCode;

	/**
	 * Name of the attribute, where we can get paths to manifests from.
	 *
	 * @var string
	 */
	private $attributeName = 'BlueSpicePageTemplatesDefaultPageTemplates';

	/**
	 * @inheritDoc
	 */
	protected function doDBUpdates() {
		// phpcs:ignore MediaWiki.NamingConventions.ValidGlobalName.allowedPrefix
		global $IP;

		$this->services = MediaWikiServices::getInstance();

		$lb = $this->services->getDBLoadBalancer();
		$db = $lb->getConnection( DB_PRIMARY );

		$this->dao = new PageTemplateDAO( $db );

		$manifestsList = ExtensionRegistry::getInstance()->getAttribute( $this->attributeName );

		if ( $manifestsList ) {
			$this->output( "...Import of default BlueSpice templates started...\n" );
			foreach ( $manifestsList as $manifestPath ) {
				$absoluteManifestPath = $IP . '/' . $manifestPath;
				if ( file_exists( $absoluteManifestPath ) ) {
					$this->output( "...Processing manifest file: '$absoluteManifestPath' ...\n" );
					$this->processManifestFile( $absoluteManifestPath );
				} else {
					$this->output( "...Manifest file does not exist: '$absoluteManifestPath'\n" );
				}
			}
		} else {
			$this->output( "No manifests to import..." );
		}

		return true;
	}

	/**
	 * @param string $manifestPath
	 * @return void
	 */
	private function processManifestFile( string $manifestPath ): void {
		$pagesList = json_decode( file_get_contents( $manifestPath ), true );
		$availableLanguages = [];
		foreach ( $pagesList as $pageTitle => $pageData ) {
			$availableLanguages[$pageData['lang']] = true;
		}
		$wikiLang = $this->services->getContentLanguage();
		$languageFallback = $this->services->getLanguageFallback();

		$importLanguage = new ImportLanguage( $languageFallback, $wikiLang->getCode() );
		$this->importLanguageCode = $importLanguage->getImportLanguage(
			array_keys( $availableLanguages )
		);

		foreach ( $pagesList as $pageTitle => $pageData ) {
			$this->output( "... Processing page: $pageTitle\n" );

			if ( $pageData['lang'] !== $this->importLanguageCode ) {
				$this->output( "... Wrong page language. Skipping...\n" );
				continue;
			}

			$targetTitle = $pageData['target_title'];

			$titleFactory = $this->services->getTitleFactory();
			$title = $titleFactory->newFromText( $targetTitle, NS_MAIN );

			// Check "bs_pagetemplate" table and insert/update information about corresponding template
			$templateTitle = $title->getDBkey();
			$templateNamespace = $title->getNamespace();
			$templateExists = $this->dao->templateExists( $templateTitle, $templateNamespace );
			if ( !$templateExists ) {
				$this->dao->insertTemplate(
					$pageData['label'],
					$pageData['description'],
					$templateTitle,
					$templateNamespace
				);

				$this->output( "... Database entry in 'bs_pagetemplate' table was created\n" );
			} else {
				$this->dao->updateTemplate(
					$pageData['label'],
					$pageData['description'],
					$templateTitle,
					$templateNamespace
				);

				$this->output( "... Database entry in 'bs_pagetemplate' table was updated\n" );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function getUpdateKey() {
		return 'PageTemplatesImport_' . $this->calculateManifestsHash();
	}

	/**
	 * @inheritDoc
	 */
	protected function updateSkippedMessage() {
		return 'PageTemplatesImport: No changes in manifests. Skipping...';
	}

	/**
	 * Concatenates content of all registered manifests and calculates its MD5 hash.
	 * It is used to create dynamic "update key".
	 * So update key will stay the same (so this script will be skipped) until some manifest will change.
	 *
	 * @return string MD5 hash
	 */
	private function calculateManifestsHash(): string {
		// phpcs:ignore MediaWiki.NamingConventions.ValidGlobalName.allowedPrefix
		global $IP;

		$manifestsContent = '';

		$manifestsList = ExtensionRegistry::getInstance()->getAttribute( $this->attributeName );
		foreach ( $manifestsList as $manifestPath ) {
			$absoluteManifestPath = $IP . '/' . $manifestPath;

			$manifestsContent .= file_get_contents( $absoluteManifestPath );
		}

		return md5( $manifestsContent );
	}
}
