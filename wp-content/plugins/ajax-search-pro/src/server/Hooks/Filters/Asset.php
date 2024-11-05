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

	public function applySelectiveAssetLoader( $exit ) {
		$comp_settings = wd_asp()->o['asp_compatibility'];

		if ( $comp_settings['selective_enabled'] ) {
			if ( is_front_page() ) {
				if ( $comp_settings['selective_front'] == 0 ) {
					$exit = true;
				}
			} elseif ( is_archive() ) {
				if ( $comp_settings['selective_archive'] == 0 ) {
					$exit = true;
				}
			} elseif ( is_singular() ) {
				if ( $comp_settings['selective_exin'] != '' ) {
					global $post;
					if ( isset($post, $post->ID) ) {
						$_ids = wpd_comma_separated_to_array($comp_settings['selective_exin']);
						if ( !empty($_ids) ) {
							if ( $comp_settings['selective_exin_logic'] == 'exclude' && in_array($post->ID, $_ids) ) {
								$exit = true;
							} elseif ( $comp_settings['selective_exin_logic'] == 'include' && !in_array($post->ID, $_ids) ) {
								$exit = true;
							}
						}
					}
				}
			}
		}

		return $exit;
	}

	function handle() {}
}
