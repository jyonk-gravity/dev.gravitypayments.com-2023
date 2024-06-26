<?php
namespace WPDRMS\ASP\Asset\Font;

/* Prevent direct access */

use WPDRMS\ASP\Asset\AssetManager;
use WPDRMS\ASP\Asset\ManagerInterface;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Utils\Html;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * Manager for the Font assets
 */
class Manager extends AssetManager implements ManagerInterface {
	use SingletonTrait;

	public function enqueue( $force = false ): void {}

	public function injectToBuffer( string $buffer, $instances = false ): string {
		if ( !$this->conflict() ) {
			$output = $this->get();
			if ( $output !== '' ) {
				Html::inject($output, $buffer);
			}
		}
		return $buffer;
	}

	public function printInline( $instances = array() ): void {
		if ( !$this->conflict() ) {
			echo $this->get();
		}
	}

	private function get(): string {
		$comp_options = wd_asp()->o['asp_compatibility'];
		$out          = '';
		if ( $comp_options['load_google_fonts'] == 1 ) {
			$generator = new Generator();
			$fonts     = $generator->generate();
			if ( count($fonts) > 0 ) {
				$stored_fonts = get_site_option('asp_fonts', array());
				$key          = md5(implode('|', $fonts));
				$fonts_css    = '';
				if ( isset($stored_fonts[ $key ]) ) {
					$fonts_css = $stored_fonts[ $key ];
				} else {
					$fonts_request = wp_safe_remote_get('https://fonts.googleapis.com/css?family=' . implode('|', $fonts) . '&display=swap');
					if ( !is_wp_error($fonts_request) ) {
						$fonts_css = wp_remote_retrieve_body($fonts_request);
						if ( $fonts_css != '' ) {
							$stored_fonts[ $key ] = $fonts_css;
							update_site_option('asp_fonts', $stored_fonts);
						}
					}
				}
				if ( !is_wp_error($fonts_css) && $fonts_css != '' ) {

					// Do NOT preload the fonts - it will give worst PagesPeed score. Preconnect is sufficient.
					$out = '
				<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
				<style>
					' . $fonts_css . '
				</style>';
				} else {
					$out = '
				<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
				<link rel="preload" as="style" href="//fonts.googleapis.com/css?family=' . implode('|', $fonts) . '&display=swap" />
				<link rel="stylesheet" href="//fonts.googleapis.com/css?family=' . implode('|', $fonts) . '&display=swap" media="all" />
				';
				}
			}
		}
		return $out;
	}
}
