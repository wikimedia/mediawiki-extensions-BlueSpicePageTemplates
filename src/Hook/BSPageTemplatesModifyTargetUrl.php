<?php
namespace BlueSpice\PageTemplates\Hook;

use BlueSpice\Hook;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Title\Title;

abstract class BSPageTemplatesModifyTargetUrl extends Hook {

	/**
	 * The target title to get the url for
	 * @var Title
	 */
	protected $targetTitle = null;

	/**
	 * The title to preload text from
	 * @var Title
	 */
	protected $preloadTitle = null;

	/**
	 * The target url to be modified
	 * @var string
	 */
	protected $targetUrl = null;

	/**
	 * Located in BsConfig::get. Enables modification of the value of the
	 * BSConfig variable specified by path.
	 * @param Title $targetTitle
	 * @param Title $preloadTitle
	 * @param string &$targetUrl
	 * @return bool
	 */
	public static function callback( $targetTitle, $preloadTitle, &$targetUrl ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$targetTitle,
			$preloadTitle,
			$targetUrl
		);
		return $hookHandler->process();
	}

	/**
	 * @param IContextSource $context
	 * @param Config $config
	 * @param Title $targetTitle
	 * @param Title $preloadTitle
	 * @param string &$targetUrl
	 */
	public function __construct( $context, $config, $targetTitle, $preloadTitle, &$targetUrl ) {
		parent::__construct( $context, $config );

		$this->targetTitle = $targetTitle;
		$this->preloadTitle = $preloadTitle;
		$this->targetUrl = &$targetUrl;
	}
}
