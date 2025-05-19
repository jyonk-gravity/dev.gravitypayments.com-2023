<?php

namespace WPDRMS\ASP\Utils;

/**
 * Wordpress Multisite related stuff
 */
class WPMU {
	/**
	 * Gets all the blogs from the multisite network
	 *
	 * @param bool $ids_only
	 * @return ($ids_only is true ? int[] : array<int, array{blog_id: int, domain:string, path:string}>)
	 */
	public static function getBlogList( bool $ids_only = false ): array {
		global $wpdb;
		if ( !isset($wpdb->blogs) ) {
			return array();
		}

		$blogs     = wp_cache_get('getBlogList', 'WPDRMS\ASP\Utils');
		if ( $blogs === false ) {
			$blogs = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = %d ORDER BY registered DESC",
					$wpdb->siteid
				),
				ARRAY_A
			);

			if ( is_wp_error($blogs) ) {
				return array();
			}

			// If no blogs are found, cache the empty result and return early
			if ( empty( $blogs ) ) {
				wp_cache_set( 'getBlogList', array(), 'WPDRMS\ASP\Utils' );
				return array();
			}

			wp_cache_set( 'getBlogList', $blogs, 'WPDRMS\ASP\Utils' );
		}

		// If only IDs are needed, extract them directly
		if ( $ids_only ) {
			$blog_ids = array_column( $blogs, 'blog_id' );
			return array_combine( $blog_ids, $blog_ids );
		}

		// For full details, map blog_id to details
		$blog_list = array();
		foreach ( $blogs as $details ) {
			$blog_list[ $details['blog_id'] ] = $details;
		}

		return $blog_list;
	}
}
