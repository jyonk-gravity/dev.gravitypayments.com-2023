<?php

namespace WPDRMS\Backend\Blocks;

use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Utils\Script;

if ( !defined('ABSPATH') ) {
	die("You can't access this file directly.");
}

/**
 * Full Site Editor and Gutenberg blocks controller
 */
class SearchBlocks {
	use SingletonTrait;

	/**
	 * Media query
	 *
	 * @var string
	 */
	private static string $media_query = '';

	private function __construct() {
		add_action('init', array( $this, 'register' ));
	}

	/**
	 * Server side registration of the blocks
	 *
	 * @hook init
	 * @return void
	 */
	public function register(): void {
		if ( !function_exists('register_block_type') ) {
			return;
		}

		$instances = wd_asp()->instances->getWithoutData();
		if ( count($instances) > 0 ) {
			$ids = array_keys($instances);
			if ( self::$media_query === '' ) {
				self::$media_query = ASP_DEBUG ? asp_gen_rnd_str() : get_site_option('asp_media_query', 'defn');
			}
			wp_register_script(
				'wd-asp-gutenberg',
				ASP_URL_NP . 'backend/Blocks/assets/search-block.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-server-side-render' ),
				self::$media_query,
				true
			);
			Script::objectToInlineScript(
				'wd-asp-gutenberg',
				'ASP_GUTENBERG',
				array(
					'ids'       => $ids,
					'instances' => $instances,
				)
			);
			wp_register_style(
				'wd-asp-gutenberg-css',
				ASP_URL_NP . 'backend/Blocks/assets/search-block.css',
				array( 'wp-edit-blocks' ),
				self::$media_query
			);
			register_block_type(
				'ajax-search-pro/block-asp-main',
				array(
					'editor_script'   => 'wd-asp-gutenberg',
					'editor_style'    => 'wd-asp-gutenberg-css',
					'render_callback' => array( $this, 'render' ),
					'attributes'      => array(
						'instance' => array(
							'default' => 1,
							'type'    => 'integer',
						),
						'scType'   => array(
							'default' => 1,
							'type'    => 'integer',
						),
					),
				)
			);
		}
	}

	/**
	 * How to render the ajax-search-pro/block-asp-main block via ServerSideRender JSX component
	 *
	 * @param array{scType: integer, instance: integer} $atts
	 * @return string
	 */
	public function render( array $atts ): string {
		// Editor render
		if ( isset($_GET['context']) && $_GET['context'] === 'edit' ) {
			if ( $atts['scType'] === 1 ) {
				return do_shortcode('[wd_asp id="' . $atts['instance'] . '" include_styles=1]');
			} else {
				return '';
			}
		} elseif ( $atts['scType'] === 1 ) {
			return do_shortcode("[wd_asp id={$atts['instance']}]");
		} elseif ( $atts['scType'] === 2 ) {
			return do_shortcode("[wpdreams_asp_settings id={$atts['instance']}]");
		} elseif ( $atts['scType'] === 3 ) {
			return do_shortcode("[wpdreams_ajaxsearchpro_results id={$atts['instance']}]");
		}
		return '';
	}
}
