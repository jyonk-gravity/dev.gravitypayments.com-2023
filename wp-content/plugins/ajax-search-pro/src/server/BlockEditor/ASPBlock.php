<?php

namespace WPDRMS\ASP\BlockEditor;

use WPDRMS\ASP\Utils\Script;

if ( !defined('ABSPATH') ) {
	die("You can't access this file directly.");
}

/**
 * Full Site Editor and Gutenberg blocks controller
 */
class ASPBlock implements BlockInterface {
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
		register_block_type(
			'wpdreams-site-core/block-open-ticket',
			array(
				'editor_script_handles' => array( 'wpdreams-site-core-blocks-editor' ),
				'view_script_handles'   => array( 'wpdreams-site-core-blocks-frontend' ),
				'render_callback'       => array( $this, 'render' ),
			),
		);

		$instances = wd_asp()->instances->getWithoutData();
		if ( count($instances) === 0 ) {
			return;
		}

		$metadata = require_once ASP_PATH . '/build/js/block-editor.asset.php'; // @phpstan-ignore-line
		wp_register_script(
			'wdo-asp-block-editor',
			ASP_URL_NP . 'build/js/block-editor.js',
			$metadata['dependencies'],
			$metadata['version'],
			array(
				'in_footer' => true,
			)
		);

		$metadata = require_once ASP_PATH . '/build/css/block-editor.asset.php'; // @phpstan-ignore-line
		wp_register_style(
			'wdo-asp-block-editor-style',
			ASP_URL_NP . 'build/css/block-editor.css',
			$metadata['dependencies'],
			$metadata['version'],
		);
		register_block_type(
			'ajax-search-pro/block-asp-main',
			array(
				'editor_script'   => 'wdo-asp-block-editor',
				'editor_style'    => 'wdo-asp-block-editor-style',
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
