<?php

namespace BlueSpice\PageTemplates;

use MediaWiki\Registration\ExtensionRegistry;
use MWStake\MediaWiki\Component\ContentProvisioner\ManifestListProvider\StaticManifestProvider;

/**
 * Finds the preview image of a page template by convention - without any storage,
 * configuration or management involved.
 *
 * Every extension that ships page templates registers its content manifest in the
 * "DefaultPageTemplates" manifest list. Right next to that manifest it may provide a
 * "files" directory that holds one image per template, named after the template title:
 *
 *     data/Content/PageTemplates/manifest.json
 *     data/Content/PageTemplates/files/PT_How-to_article.svg
 *     data/Content/PageTemplates/files/generic.svg
 *
 * Adding a preview for a new template therefore means dropping a file into that
 * directory - nothing has to be registered or maintained anywhere else.
 *
 * Templates without an image of their own fall back to "generic.svg". The pseudo entry
 * for creating an empty page falls back to "empty.svg" and, if that does not exist
 * either, to "generic.svg" as well.
 */
class PreviewImageLookup {

	/**
	 * Name of the content manifest list page template manifests are registered in
	 */
	public const MANIFEST_LIST = 'DefaultPageTemplates';

	/**
	 * Directory next to a content manifest that holds the preview images
	 */
	public const FILES_DIR = 'files';

	/**
	 * Basename of the image used for templates without an image of their own
	 */
	public const GENERIC_NAME = 'generic';

	/**
	 * Basename of the image used for the "empty page" entry
	 */
	public const EMPTY_NAME = 'empty';

	/**
	 * File extension of preview images
	 */
	public const FILE_EXTENSION = 'svg';

	/**
	 * Directories to look in, in order of precedence.
	 * Local filesystem path (no trailing slash) => web path (no trailing slash)
	 *
	 * @var array<string,string>
	 */
	private $directories;

	/**
	 * Resolved URLs, keyed by image basename. Contains `null` for basenames
	 * that have been looked up without success.
	 *
	 * @var array<string,string|null>
	 */
	private $urlCache = [];

	/**
	 * @param array<string,string> $directories Local path => web path
	 */
	public function __construct( array $directories ) {
		$this->directories = $directories;
	}

	/**
	 * Builds the lookup from the registered page template manifests
	 *
	 * @return static
	 */
	public static function newFromGlobalState() {
		global $IP, $wgExtensionAssetsPath, $wgScriptPath;

		return static::newFromManifests(
			array_keys( ExtensionRegistry::getInstance()->getAllThings() ),
			$IP,
			$wgExtensionAssetsPath,
			$wgScriptPath
		);
	}

	/**
	 * Builds the lookup from the page template manifests of the given extensions
	 *
	 * @param string[] $enabledExtensions
	 * @param string $installPath
	 * @param string $extensionAssetsPath
	 * @param string $scriptPath
	 * @return static
	 */
	public static function newFromManifests(
		array $enabledExtensions, string $installPath, string $extensionAssetsPath, string $scriptPath
	) {
		// Same source of truth the page template provisioner uses. The attribute can not be
		// read from the ExtensionRegistry, as it is not bound to an actually loaded extension.
		$manifestListProvider = new StaticManifestProvider( $enabledExtensions, $installPath );
		$manifests = $manifestListProvider->provideManifests( static::MANIFEST_LIST );

		$directories = [];
		foreach ( $manifests as $manifest ) {
			$localPath = dirname( $manifest ) . '/' . static::FILES_DIR;
			$webPath = static::getWebPath(
				$localPath, $installPath, $extensionAssetsPath, $scriptPath
			);
			if ( $webPath !== null ) {
				$directories[$localPath] = $webPath;
			}
		}

		// Own directory as a last resort, so that the fallback images are available
		// even if this extension's manifest can not be found
		$extensionDir = dirname( __DIR__ );
		$ownPath = $extensionDir . '/data/Content/PageTemplates/' . static::FILES_DIR;
		if ( !isset( $directories[$ownPath] ) ) {
			$directories[$ownPath] = $extensionAssetsPath . '/' . basename( $extensionDir ) .
				'/data/Content/PageTemplates/' . static::FILES_DIR;
		}

		return new static( $directories );
	}

	/**
	 * Turns the local path of a preview image directory into a web path
	 *
	 * @param string $localPath
	 * @param string $installPath
	 * @param string $extensionAssetsPath
	 * @param string $scriptPath
	 * @return string|null Null if the directory is not served by the wiki
	 */
	private static function getWebPath(
		string $localPath, string $installPath, string $extensionAssetsPath, string $scriptPath
	): ?string {
		$installPath = rtrim( $installPath, '/' );
		if ( !str_starts_with( $localPath, "$installPath/" ) ) {
			return null;
		}

		$relativePath = substr( $localPath, strlen( $installPath ) + 1 );
		if ( str_starts_with( $relativePath, 'extensions/' ) ) {
			return $extensionAssetsPath . substr( $relativePath, strlen( 'extensions' ) );
		}

		return $scriptPath . '/' . $relativePath;
	}

	/**
	 * Returns the URL of the preview image to show for a page template
	 *
	 * @param string|null $templateTitle Title of the template page, in its database
	 * 		key form. `null` for the "empty page" entry.
	 * @return string|null URL, or `null` if there is no image at all
	 */
	public function getPreviewUrl( ?string $templateTitle ): ?string {
		$candidates = [];
		if ( $templateTitle !== null && $templateTitle !== '' ) {
			$candidates[] = $templateTitle;
		} else {
			$candidates[] = static::EMPTY_NAME;
		}
		$candidates[] = static::GENERIC_NAME;

		foreach ( $candidates as $candidate ) {
			$url = $this->findImage( $candidate );
			if ( $url !== null ) {
				return $url;
			}
		}

		return null;
	}

	/**
	 * @param string $name Basename of the image, without file extension
	 * @return string|null
	 */
	private function findImage( string $name ): ?string {
		if ( array_key_exists( $name, $this->urlCache ) ) {
			return $this->urlCache[$name];
		}

		$this->urlCache[$name] = null;
		if ( $this->isSafeName( $name ) ) {
			$filename = $name . '.' . static::FILE_EXTENSION;
			foreach ( $this->directories as $localPath => $webPath ) {
				if ( is_file( "$localPath/$filename" ) ) {
					$this->urlCache[$name] = $webPath . '/' . $this->encodePath( $filename );
					break;
				}
			}
		}

		return $this->urlCache[$name];
	}

	/**
	 * Makes sure a template title can not escape the preview image directories.
	 * Slashes are allowed, so that subpage templates can be mirrored by subdirectories.
	 *
	 * @param string $name
	 * @return bool
	 */
	private function isSafeName( string $name ): bool {
		if ( $name === '' || str_contains( $name, '\\' ) || str_contains( $name, "\0" ) ) {
			return false;
		}
		foreach ( explode( '/', $name ) as $segment ) {
			if ( $segment === '' || $segment === '.' || $segment === '..' ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function encodePath( string $path ): string {
		return implode( '/', array_map( 'rawurlencode', explode( '/', $path ) ) );
	}
}
