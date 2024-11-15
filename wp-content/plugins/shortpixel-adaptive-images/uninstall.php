<?php
	/**
	 * Uninstall file
	 */

	( defined( 'ABSPATH' ) || defined( 'WP_UNINSTALL_PLUGIN' ) ) || die;

	include_once __DIR__ . '/short-pixel-ai.php';

	if ( class_exists( '\ShortPixel\AI\LQIP' ) && method_exists( '\ShortPixel\AI\LQIP', 'clearCache' ) ) {
		$is_cache_cleared = ShortPixel\AI\LQIP::clearCache();

        $uploadsSpaiDir = SHORTPIXEL_AI_WP_UPLOADS_DIR . DIRECTORY_SEPARATOR . SHORTPIXEL_AI_PLUGIN_BASEDIR;
		if ( $is_cache_cleared && !empty( SHORTPIXEL_AI_WP_UPLOADS_DIR ) && file_exists($uploadsSpaiDir) ) {
			rmdir( $uploadsSpaiDir );
		}
	}