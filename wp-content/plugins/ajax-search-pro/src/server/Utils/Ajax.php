<?php
namespace WPDRMS\ASP\Utils;

if ( !defined('ABSPATH') ) {
	die("You can't access this file directly.");
}

class Ajax {
	/**
	 * Prepares the headers for the ajax request
	 *
	 * @param string $content_type
	 */
	public static function prepareHeaders( string $content_type = 'text/plain' ): void {
		$content_type = apply_filters('asp/ajax/headers/content_type', $content_type);
		ob_end_clean();
		if ( !headers_sent() ) {
			header('Content-Type: ' . $content_type);
		}
	}
}
