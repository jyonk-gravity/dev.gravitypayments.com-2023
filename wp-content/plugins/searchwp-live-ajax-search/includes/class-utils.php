<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SearchWP_Live_Search_Utils.
 *
 * @since 1.7.0
 */
class SearchWP_Live_Search_Utils {

	/**
	 * Check if SearchWP plugin is active.
	 *
	 * @since 1.7.0
	 */
	public static function is_searchwp_active() {

		return class_exists( 'SearchWP' );
	}

	/**
	 * Helper function to determine if loading a Live Ajax Search admin settings page.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public static function is_settings_page() {

		if ( ! is_admin() ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_REQUEST['page'] ) ? sanitize_key( $_REQUEST['page'] ) : '';

		if ( empty( $page ) ) {
			return false;
		}

		return $page === 'searchwp-live-search';
	}

	/**
	 * Sanitize array/string of CSS classes.
	 *
	 * @since 1.7.0
	 *
	 * @param array|string $classes
	 * @param array        $args {
	 *     Optional arguments.
	 *
	 *     @type bool       $convert Whether to suppress filters. Default true.
	 * }
	 *
	 * @return string|array
	 */
	public static function sanitize_classes( $classes, $args = [] ) {

		$is_array = is_array( $classes );
		$convert  = ! empty( $args['convert'] );
		$css      = [];

		if ( ! empty( $classes ) ) {
			$classes = $is_array ? $classes : explode( ' ', trim( $classes ) );
			foreach ( $classes as $class ) {
				if ( ! empty( $class ) ) {
					$css[] = sanitize_html_class( $class );
				}
			}
		}

		if ( $is_array ) {
			return $convert ? implode( ' ', $css ) : $css;
		}

		return $convert ? $css : implode( ' ', $css );
	}
}
