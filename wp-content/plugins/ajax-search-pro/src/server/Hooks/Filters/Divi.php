<?php
namespace WPDRMS\ASP\Hooks\Filters;

use WP_Query;
use WPDRMS\ASP\Models\SearchQueryArgs;
use WPDRMS\ASP\Utils\Search;

if ( !defined('ABSPATH') ) {
	die('-1');
}


class Divi extends AbstractFilter {
	public function handle() {}

	/**
	 * Handles override for Divi built-in blogs module
	 *
	 * @param WP_Query             $wp_query
	 * @param array<string, mixed> $atts
	 * @return WP_Query
	 */
	public function blog( WP_Query $wp_query, array $atts = array() ): WP_Query {
		$id = Search::overrideSearchId();
		if ( !isset($atts['module_class']) || !str_contains('asp_es_' . $id, $atts['module_class']) ) {
			return $wp_query;
		}

		add_filter(
			'asp_query_args',
			function ( SearchQueryArgs $args ) use ( $wp_query ) {
				$args->search_type         = array( 'cpt' );
				$args->_sd['shortcode_op'] = 'remove';
				$args->posts_per_page      = $wp_query->query_vars['posts_per_page'] ?? $args->posts_per_page;
				return $args;
			},
			100,
			1
		);

		$wp_query->query_vars['asp_override']    = true;
		$wp_query->query_vars['asp_not_archive'] = true;
		$wp_query->is_home                       = false; // This will prevent archive detection
		$new_query                               = SearchOverride::instance()->override(array(), $wp_query, 'wp_query');
		return $new_query;
	}
}
