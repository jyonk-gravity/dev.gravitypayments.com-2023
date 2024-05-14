<?php
namespace WPDRMS\ASP\Asset;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * Common functions for CSS/Script/Font asset managers
 */
class AssetManager {
	/**
	 * Checks if any compatibility issues should be considered in the current request
	 *
	 * @return bool
	 */
	protected function conflict(): bool {
		// phpcs:disable
		// Widgets screen
		return (
			wp_is_json_request() || // Widgets screen
			wp_doing_ajax() || // Ajax Resuests in General
			isset($_GET['et_fb']) || // Divi frontend editor
			isset($_GET['vcv-ajax']) || // Visual Composer Frontend editor
			isset($_GET['fl_builder']) || // Beaver Builder Frontend editor
			isset($_GET['elementor-preview']) || // Elementor Frontend
			isset($_GET['breakdance']) || isset($_GET['_breakdance_doing_ajax']) || // Breakdance editor
			isset($_GET['wc-ajax']) || // WooCommerce Custom Ajax Request
			( isset($_GET['action']) && $_GET['action'] == 'elementor' ) // Elementor Parts editor
		);
	}
}
