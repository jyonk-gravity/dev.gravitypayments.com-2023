<?php /** @noinspection PhpUnused */

namespace WPDRMS\ASP\Hooks\Filters;

use WPDRMS\ASP\Asset\Manager;

if ( !defined('ABSPATH') ) {
	die('-1');
}

class Asset extends AbstractFilter {

	/**
	 * hook: wp_enqueue_scripts
	 */
	function onPluginFrontendHead() {
		Manager::instance()->enqueue();
	}

	/**
	 * hook: wp_print_footer_scripts, priority 6
	 * hook: admin_print_footer_scripts, priority 6
	 */
	function onPluginFooter() {
		Manager::instance()->onPluginFooter();
	}

	/**
	 * Classic script enqueue for the plugin backend
	 *
	 * hook: admin_print_footer_scripts, priority 7
	 */
	function onPluginBackendFooter() {
		if ( wd_asp()->manager->getContext() == 'backend' ) {
			Manager::instance()->onPluginBackendFooter();
		}
	}

	/**
	 * hook: asp_ob_end
	 *
	 * @param $buffer
	 * @return mixed
	 */
	function injectToOutputBuffer( $buffer ) {
		return Manager::instance()->injectToBuffer($buffer);
	}

	/**
	 * Safety check, if the injections were not successful
	 *
	 * hook: shutdown (executed after asp_ob_end)
	 */
	function onShutdown() {
		if (
			wd_asp()->manager->getContext() == 'frontend' && !wp_is_json_request()
			&& !isset($_POST['ags_wc_filters_ajax_shop']) // divi shop live pagination
			&& !(
				isset($_SERVER['REQUEST_URI']) &&
				substr_compare($_SERVER['REQUEST_URI'], '.xml', -strlen('.xml')) === 0
			) // Skip for XML document requests
		) {
			Manager::instance()->printBackup();
		}
	}


	public function applySelectiveAssetLoader( bool $load ) {
		$comp_settings = wd_asp()->o['asp_compatibility'];
		if ( !$comp_settings['selective_enabled'] ) {
			return $load;
		}

		if ( is_front_page() && !$comp_settings['selective_front'] ) {
			return false;
		}

		if ( ( is_search() || is_archive() ) && !$comp_settings['selective_archive'] ) {
			return false;
		}

		if ( is_singular() ) {
			return $this->should_load_on_singular_page($load);
		}

		return $load;
	}

	private function should_load_on_singular_page( bool $load ) {
		$comp_settings = wd_asp()->o['asp_compatibility'];

		if ( $comp_settings['selective_exin'] === '' ) {
			return $load;
		}

		global $post;
		if ( !isset($post, $post->ID) ) {
			return $load;
		}

		$selective_ids = array_map('intval', wpd_comma_separated_to_array($comp_settings['selective_exin']));
		if ( empty($selective_ids) ) {
			return $load;
		}

		$post_id    = $post->ID;
		$is_exclude = $comp_settings['selective_exin_logic'] === 'exclude';
		$is_include = $comp_settings['selective_exin_logic'] === 'include';

		if ( ( $is_exclude && in_array($post_id, $selective_ids, true) ) ||
			( $is_include && !in_array($post_id, $selective_ids, true) ) ) {
			return false;
		}

		return $load;
	}

	function handle() {}
}
