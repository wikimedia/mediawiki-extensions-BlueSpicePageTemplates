<?php

namespace BlueSpice\PageTemplates\ContentProvisioner;

use BlueSpice\PageTemplates\ContentImport\PageTemplateDAO;
use MediaWiki\Language\Language;
use MediaWiki\Languages\LanguageFallback;
use MediaWiki\Status\Status;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\ContentProvisioner\EntityKey;
use MWStake\MediaWiki\Component\ContentProvisioner\IContentProvisioner;
use MWStake\MediaWiki\Component\ContentProvisioner\IManifestListProvider;
use MWStake\MediaWiki\Component\ContentProvisioner\ImportLanguage;
use MWStake\MediaWiki\Component\ContentProvisioner\Output\NullOutput;
use MWStake\MediaWiki\Component\ContentProvisioner\OutputAwareInterface;
use MWStake\MediaWiki\Component\ContentProvisioner\OutputInterface;
use MWStake\MediaWiki\Component\ContentProvisioner\UpdateLogStorageTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Wikimedia\Rdbms\ILoadBalancer;

class PageTemplatesProvisioner implements
	LoggerAwareInterface,
	OutputAwareInterface,
	IContentProvisioner
{
	use UpdateLogStorageTrait;

	/**
	 * Logger object
	 *
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * Manifest list provider
	 *
	 * @var IManifestListProvider
	 */
	private $manifestListProvider;

	/**
	 * Wiki content language
	 *
	 * @var Language
	 */
	private $wikiLang;

	/**
	 * Language fallback service.
	 * Used to get fallback language for cases when ContentProvisioner does not support
	 * wiki content language. In such cases we need to find the most suitable "fallback" language.
	 *
	 * @var LanguageFallback
	 */
	private $languageFallback;

	/**
	 * @var TitleFactory
	 */
	private $titleFactory;

	/**
	 * Page templates "Database Access Object".
	 * Used to interact with "bs_pagetemplates" table.
	 *
	 * @var PageTemplateDAO
	 */
	private $dao;

	/**
	 * Helps to recognize manifests which should be processed by that provisioner.
	 * Used in {@link IManifestListProvider}.
	 *
	 * @var string
	 */
	private $manifestsKey;

	/**
	 * @param Language $wikiLang Wiki content language
	 * @param LanguageFallback $languageFallback Language fallback service
	 * @param TitleFactory $titleFactory Title factory service
	 * @param ILoadBalancer $lb Load balancer. Used to get necessary DB connection
	 * @param string $manifestsKey Manifests key
	 */
	public function __construct(
		Language $wikiLang,
		LanguageFallback $languageFallback,
		TitleFactory $titleFactory,
		ILoadBalancer $lb,
		string $manifestsKey
	) {
		$this->logger = new NullLogger();
		$this->output = new NullOutput();

		$this->manifestsKey = $manifestsKey;
		$this->wikiLang = $wikiLang;
		$this->languageFallback = $languageFallback;
		$this->titleFactory = $titleFactory;

		$db = $lb->getConnection( DB_PRIMARY );

		$this->dao = new PageTemplateDAO( $db );
	}

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @param OutputInterface $output
	 */
	public function setOutput( OutputInterface $output ): void {
		$this->output = $output;
	}

	/**
	 * @inheritDoc
	 */
	public function setManifestListProvider( IManifestListProvider $manifestListProvider ): void {
		$this->manifestListProvider = $manifestListProvider;
	}

	/**
	 * @inheritDoc
	 */
	public function provision(): Status {
		if ( $this->manifestListProvider === null ) {
			$this->logger->error( 'No manifest list provider set!' );

			return Status::newFatal( 'Import failed to begin. See logs for details.' );
		}

		$manifestsList = $this->manifestListProvider->provideManifests( $this->manifestsKey );

		$this->output->write( "...PageTemplatesProvisioner: Import of default BlueSpice templates started...\n" );

		if ( $manifestsList ) {
			foreach ( $manifestsList as $absoluteManifestPath ) {
				if ( file_exists( $absoluteManifestPath ) ) {
					$this->output->write( "...Processing manifest file: '$absoluteManifestPath' ...\n" );
					$this->processManifestFile( $absoluteManifestPath );
				} else {
					$this->logger->warning( "Manifest file does not exist: '$absoluteManifestPath'" );
				}
			}
		} else {
			$this->output->write( "No manifests to import...\n" );
		}

		return Status::newGood();
	}

	/**
	 * @param string $manifestPath
	 * @return void
	 */
	private function processManifestFile( string $manifestPath ): void {
		$manifestContents = file_get_contents( $manifestPath );
		if ( $manifestContents === false ) {
			$this->logger->error( "Could not retrieve manifest content: $manifestPath" );
			return;
		}

		$pagesList = json_decode( $manifestContents, true );
		if ( $pagesList === null ) {
			$this->logger->error( "Manifest could not be parsed: $manifestPath" );
			return;
		}

		$availableLanguages = [];
		foreach ( $pagesList as $pageTitle => $pageData ) {
			$availableLanguages[$pageData['lang']] = true;
		}

		$importLanguage = new ImportLanguage( $this->languageFallback, $this->wikiLang->getCode() );
		$importLanguageCode = $importLanguage->getImportLanguage(
			array_keys( $availableLanguages )
		);

		foreach ( $pagesList as $pageTitle => $pageData ) {
			$this->output->write( "... Processing page: $pageTitle\n" );

			if ( $pageData['lang'] !== $importLanguageCode ) {
				$this->output->write( "... Wrong page language. Skipping...\n" );
				continue;
			}

			$targetTitle = $pageData['target_title'];

			$title = $this->titleFactory->newFromText( $targetTitle, NS_MAIN );

			// Check "bs_pagetemplate" table and insert/update information about corresponding template
			$templateTitle = $title->getDBkey();
			$templateNamespace = $title->getNamespace();
			$templateExists = $this->dao->templateExists( $templateTitle, $templateNamespace );
			if ( !$templateExists ) {
				// This template was already imported, but does not exist now.
				// It should have been removed by user, so no need to import it again
				$entityKey = new EntityKey( 'PageTemplatesProvisioner', $title->getPrefixedDBkey() );
				if ( $this->entityWasSynced( $entityKey ) ) {
					$this->output->write( "Template was synced, but at some point removed by user. Skipping...\n" );
					continue;
				}

				$this->dao->insertTemplate(
					$pageData['label'],
					$pageData['description'],
					$templateTitle,
					$templateNamespace
				);

				$this->output->write( "Database entry in 'bs_pagetemplate' table was created\n" );
				// Add sync record in database
				$this->upsertEntitySyncRecord( $entityKey );
			} else {
				$this->dao->updateTemplate(
					$pageData['label'],
					$pageData['description'],
					$templateTitle,
					$templateNamespace
				);

				$this->output->write( "Database entry in 'bs_pagetemplate' table was updated\n" );

				$entityKey = new EntityKey( 'PageTemplatesProvisioner', $title->getPrefixedDBkey() );
				// Update sync record in database
				$this->upsertEntitySyncRecord( $entityKey );
			}
		}
	}

}
