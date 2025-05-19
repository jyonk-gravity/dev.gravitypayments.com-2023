<?php

namespace WPDRMS\ASP\Hooks\Filters;

use WPDRMS\ASP\Models\SearchQueryArgs;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * Makes Search Exclude plugin exclusions compatible with Ajax Search Lite
 *
 * @see https://wordpress.org/plugins/search-exclude/
 */
class SearchExclude extends AbstractFilter {

	/**
	 * @param SearchQueryArgs $args
	 * @return SearchQueryArgs
	 */
	public function handleExclusions( SearchQueryArgs $args ): SearchQueryArgs {
		if ( class_exists('\QuadLayers\QLSE\Models\Settings') ) {
			/** @noinspection All */
			$excluded = \QuadLayers\QLSE\Models\Settings::instance()->get();
			if ( ! isset( $excluded ) ) {
				return $args;
			}
			/**
			 * Exclude posts by post type
			 */
			if ( isset( $excluded->entries ) ) {
				$post__not_in = array();
				foreach ( $excluded->entries as $post_type => $excluded_post_type ) {
					$post_type_ids = ! empty( $excluded_post_type['all'] ) ? $this->getAllPostTypeIds( $post_type ) : $excluded_post_type['ids'];
					$post__not_in  = array_merge( $post__not_in, $post_type_ids );
				}
				$args->post_not_in = array_unique( array_merge( $args->post_not_in, $post__not_in ) );
			}
		}
		return $args;
	}

	/**
	 * @param string $post_type
	 * @return int[]
	 */
	private function getAllPostTypeIds( string $post_type ): array {
		// @phpstan-ignore-next-line
		return get_posts(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
	}
}
