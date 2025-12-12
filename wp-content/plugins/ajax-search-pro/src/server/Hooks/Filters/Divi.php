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
	 * Divi loops
	 *
	 * @param array<string, mixed> $args
	 * @param array<string, mixed> $atts
	 * @param array<string, mixed> $block
	 * @return array<string, mixed>
	 */
	public function loop( array $args, array $atts, $block ): array {
		$id = Search::overrideSearchId();
		if ( empty($id) ) {
			return $args;
		}

		if ( isset($atts['module']) ) { // divi 5
			$found = false;
			array_walk_recursive(
				$atts['module'],
				function ( $value, $key ) use ( $id, &$found ) {
					if ( str_contains('asp_es_' . $id, $value) ) {
						$found = true;
					}
				},
				$found
			);
			if ( !$found ) {
				return $args;
			}
		} else {
			return $args;
		}

		$args['query_args']['asp_override'] = true;
		return $args;
	}


	/**
	 * Handles override for Divi built-in blogs module
	 *
	 * @param WP_Query             $wp_query
	 * @param array<string, mixed> $atts
	 * @return WP_Query
	 */
	public function blog( WP_Query $wp_query, array $atts = array() ): WP_Query {
		$id = Search::overrideSearchId();

		if ( empty($id) ) {
			return $wp_query;
		}

		if ( isset($atts['module_class']) ) { // divi 4
			if ( !str_contains('asp_es_' . $id, $atts['module_class']) ) {
				return $wp_query;
			}
		} elseif ( isset($atts['module']) ) { // divi 5
			$found = false;
			array_walk_recursive(
				$atts['module'],
				function ( $value, $key ) use ( $id, &$found ) {
					if ( str_contains('asp_es_' . $id, $value) ) {
						$found = true;
					}
				},
				$found
			);
			if ( !$found ) {
				return $wp_query;
			}
		} else {
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

	/**
	 * Divi extras blog module
	 *
	 * @param array<string, mixed> $args
	 * @param array<string, mixed> $atts
	 * @return array<string, mixed>
	 */
	public function blogExtras( array $args, array $atts = array() ): array {
		$id = Search::overrideSearchId();

		if ( empty($id) ) {
			return $wp_query;
		}

		if ( !isset($atts['module_class']) || !str_contains('asp_es_' . $id, $atts['module_class']) ) {
			return $args;
		}
		$args['asp_override'] = true;
		return $args;
	}

	/**
	 * Divi Query Builder extras blog module
	 *
	 * @see https://divicoding.com/
	 *
	 * @param array<string, mixed> $query_vars
	 * @param array<string, mixed> $settings
	 * @return array<string, mixed>
	 */
	public function queryBuilder( array $query_vars, array $settings = array() ): array {
		$id = Search::overrideSearchId();
		if (
			$id > 0 &&
			isset($settings['module_class']) &&
			strpos($settings['module_class'], 'asp_es_' . $id) !== false
		) {
			if ( isset($_GET['asp_force_reset_pagination']) ) {
				// For the correct pagination highlight
				$query_vars['paged'] = 1;
			}
			$query_vars['asp_override'] = true;
		}

		return $query_vars;
	}
}
