<?php
namespace BlueSpice\PageTemplates\Hook;
use BlueSpice\Hook;

abstract class BSPageTemplatesModifyTargetUrl extends Hook {

	/**
	 * The target title to get the url for
	 * @var string
	 */
	protected $targetTitle = null;

	/**
	 * The title to preload text from
	 * @var string
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
	 * @param string $targetUrl
	 * @return boolean
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
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param string $path
	 * @param mixed $return
	 */
	public function __construct( $context, $config, $targetTitle, $preloadTitle, &$targetUrl ) {
		parent::__construct( $context, $config );

		$this->targetTitle = $targetTitle;
		$this->preloadTitle = $preloadTitle;
		$this->targetUrl = &$targetUrl;
	}
}
