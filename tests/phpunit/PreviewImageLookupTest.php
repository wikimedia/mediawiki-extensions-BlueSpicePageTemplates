<?php

namespace BlueSpice\PageTemplates\Tests;

use BlueSpice\PageTemplates\PreviewImageLookup;
use MediaWikiUnitTestCase;

/**
 * @group BlueSpice
 * @group BlueSpicePageTemplates
 *
 * @covers \BlueSpice\PageTemplates\PreviewImageLookup
 */
class PreviewImageLookupTest extends MediaWikiUnitTestCase {

	/** @var string */
	private $ownDir = '';

	/** @var string */
	private $otherDir = '';

	protected function setUp(): void {
		parent::setUp();

		$base = sys_get_temp_dir() . '/bs-pt-preview-' . uniqid();
		$this->otherDir = "$base/other/files";
		$this->ownDir = "$base/own/files";

		foreach ( [ $this->otherDir, $this->ownDir ] as $dir ) {
			mkdir( $dir, 0777, true );
		}

		// Shipped by another extension
		$this->writeImage( $this->otherDir, 'PT_Decision_matrix' );
		$this->writeImage( $this->otherDir, 'generic' );
		// Shipped by this extension
		$this->writeImage( $this->ownDir, 'PT_To-do_List' );
		$this->writeImage( $this->ownDir, 'generic' );
		$this->writeImage( $this->ownDir, 'empty' );
		$this->writeImage( $this->ownDir . '/Sub', 'Page', true );
	}

	protected function tearDown(): void {
		$this->removeRecursively( dirname( dirname( $this->ownDir ) ) );

		parent::tearDown();
	}

	/**
	 * @param string $path
	 */
	private function removeRecursively( string $path ) {
		if ( !is_dir( $path ) ) {
			if ( file_exists( $path ) ) {
				unlink( $path );
			}
			return;
		}
		foreach ( array_diff( scandir( $path ), [ '.', '..' ] ) as $entry ) {
			$this->removeRecursively( "$path/$entry" );
		}
		rmdir( $path );
	}

	/**
	 * @param string $dir
	 * @param string $name
	 * @param bool $createDir
	 */
	private function writeImage( string $dir, string $name, bool $createDir = false ) {
		if ( $createDir && !is_dir( $dir ) ) {
			mkdir( $dir, 0777, true );
		}
		file_put_contents( "$dir/$name.svg", '<svg xmlns="http://www.w3.org/2000/svg"/>' );
	}

	/**
	 * @return PreviewImageLookup
	 */
	private function newLookup(): PreviewImageLookup {
		// Own directory comes last, just like in production
		return new PreviewImageLookup( [
			$this->otherDir => '/w/extensions/Other/data/Content/PageTemplates/files',
			$this->ownDir => '/w/extensions/BlueSpicePageTemplates/data/Content/PageTemplates/files',
		] );
	}

	public function testTemplateWithOwnImage() {
		$this->assertSame(
			'/w/extensions/BlueSpicePageTemplates/data/Content/PageTemplates/files/PT_To-do_List.svg',
			$this->newLookup()->getPreviewUrl( 'PT_To-do_List' )
		);
	}

	public function testImageFromAnotherExtension() {
		$this->assertSame(
			'/w/extensions/Other/data/Content/PageTemplates/files/PT_Decision_matrix.svg',
			$this->newLookup()->getPreviewUrl( 'PT_Decision_matrix' )
		);
	}

	public function testFallsBackToGenericImage() {
		$this->assertSame(
			'/w/extensions/Other/data/Content/PageTemplates/files/generic.svg',
			$this->newLookup()->getPreviewUrl( 'Template_nobody_made_an_image_for' )
		);
	}

	public function testEmptyPageEntryUsesEmptyImage() {
		$this->assertSame(
			'/w/extensions/BlueSpicePageTemplates/data/Content/PageTemplates/files/empty.svg',
			$this->newLookup()->getPreviewUrl( null )
		);
	}

	public function testEmptyPageEntryFallsBackToGenericImage() {
		unlink( "$this->ownDir/empty.svg" );

		$this->assertSame(
			'/w/extensions/Other/data/Content/PageTemplates/files/generic.svg',
			$this->newLookup()->getPreviewUrl( null )
		);
	}

	public function testSubpageTemplate() {
		$this->assertSame(
			'/w/extensions/BlueSpicePageTemplates/data/Content/PageTemplates/files/Sub/Page.svg',
			$this->newLookup()->getPreviewUrl( 'Sub/Page' )
		);
	}

	/**
	 * @dataProvider provideUnsafeNames
	 * @param string $name
	 */
	public function testUnsafeNamesAreIgnored( string $name ) {
		// Falls back to the generic image instead of escaping the directory
		$this->assertSame(
			'/w/extensions/Other/data/Content/PageTemplates/files/generic.svg',
			$this->newLookup()->getPreviewUrl( $name )
		);
	}

	/**
	 * @return array
	 */
	public static function provideUnsafeNames() {
		return [
			'parent directory' => [ '../../other/files/generic' ],
			'current directory' => [ './generic' ],
			'backslash' => [ '..\\generic' ],
			'empty segment' => [ 'Sub//Page' ],
			'leading slash' => [ '/generic' ],
		];
	}

	public function testNoImageAtAll() {
		$lookup = new PreviewImageLookup( [ $this->ownDir . '/does-not-exist' => '/w/nowhere' ] );

		$this->assertNull( $lookup->getPreviewUrl( 'PT_To-do_List' ) );
		$this->assertNull( $lookup->getPreviewUrl( null ) );
	}

	/**
	 * The directories are discovered through the same manifest list the page template
	 * provisioner uses, so that every extension shipping page templates is covered
	 */
	public function testDirectoriesAreDiscoveredFromManifests() {
		$installPath = dirname( dirname( $this->ownDir ) ) . '/install';
		foreach ( [ 'FreeExt' => 'generic', 'ProExt' => 'PT_Decision_matrix' ] as $extension => $image ) {
			$dir = "$installPath/extensions/$extension/data/Content/PageTemplates";
			mkdir( "$dir/files", 0777, true );
			file_put_contents( "$dir/manifest.json", '{}' );
			$this->writeImage( "$dir/files", $image );
			file_put_contents( "$installPath/extensions/$extension/extension.json", json_encode( [
				'name' => $extension,
				'attributes' => [
					'MWStakeContentProvisioner' => [
						'ContentManifests' => [
							PreviewImageLookup::MANIFEST_LIST => [
								"extensions/$extension/data/Content/PageTemplates/manifest.json",
							],
						],
					],
				],
			] ) );
		}

		$lookup = PreviewImageLookup::newFromManifests(
			[ 'FreeExt', 'ProExt' ], $installPath, '/w/extensions', '/w'
		);

		$this->assertSame(
			'/w/extensions/ProExt/data/Content/PageTemplates/files/PT_Decision_matrix.svg',
			$lookup->getPreviewUrl( 'PT_Decision_matrix' ),
			'Template of another extension'
		);
		$this->assertSame(
			'/w/extensions/FreeExt/data/Content/PageTemplates/files/generic.svg',
			$lookup->getPreviewUrl( 'Template_without_an_image' ),
			'Fallback across extensions'
		);
	}

	public function testUrlIsEncoded() {
		$this->writeImage( $this->ownDir, 'Template with spaces & more' );

		$this->assertSame(
			'/w/extensions/BlueSpicePageTemplates/data/Content/PageTemplates/files/' .
				'Template%20with%20spaces%20%26%20more.svg',
			$this->newLookup()->getPreviewUrl( 'Template with spaces & more' )
		);

		unlink( "$this->ownDir/Template with spaces & more.svg" );
	}
}
