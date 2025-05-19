<?php

namespace WPDRMS\ASP\Options;

use WPDRMS\ASP\Asset\AssetInterface;
use WPDRMS\ASP\Patterns\SingletonTrait;

class OptionAssets implements AssetInterface {
	use SingletonTrait;

	/**
	 * @var string[]
	 */
	private array $registered = array();

	public function register(): void {
		if ( wd_asp()->manager->getContext() !== 'backend' ) {
			return;
		}
		$metadata = require_once ASP_PATH . '/build/js/admin-global.asset.php'; // @phpstan-ignore-line
		wp_enqueue_script(
			'wdo-asp-global-backend',
			ASP_URL_NP . 'build/js/admin-global.js',
			$metadata['dependencies'],
			$metadata['version'],
			array(
				'in_footer' => true,
			)
		);
		do_action('asp/asset/js/wdo-asp-global-backend');
	}

	public function deregister(): void {
		foreach ( $this->registered as $handle ) {
			wp_dequeue_script($handle);
		}
	}
}