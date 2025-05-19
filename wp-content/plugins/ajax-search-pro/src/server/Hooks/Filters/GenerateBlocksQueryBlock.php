<?php

namespace WPDRMS\ASP\Hooks\Filters;

use WPDRMS\ASP\Query\SearchQuery;
use WPDRMS\ASP\Utils\Search;

/**
 * GenerateBlocks plugin Query Loop live search and filter compatibility
 */
class GenerateBlocksQueryBlock extends AbstractFilter {
	/**
	 * Cache for POST ids for each query block
	 *
	 * This is needed because in case of pagination the search query should not execute twice
	 *
	 * @var array<int, int[]>
	 */
	private array $cached_results = array();

	/**
	 * Adds a custom 'isDecorative' attribute to all Image blocks.
	 *
	 * @param array  $args       The block arguments for the registered block type.
	 * @param string $block_type The block type name, including namespace.
	 * @return array             The modified block arguments.
	 */
	public function addAttributes( $args, $block_type ) {
		if ( $block_type === 'generateblocks/query' ) {
			if ( !isset( $args['attributes'] ) ) {
				$args['attributes'] = array();
			}
			if ( !isset( $args['provides_context'] ) ) {
				$args['provides_context'] = array();
			}

			$args['attributes']['asp_override_id'] = array(
				'type'    => 'integer',
				'default' => 0,
			);

			// To pass the attribute down as context variable
			$args['provides_context']['asp_override_id'] = 'asp_override_id';
		}

		/**
		 * This will make sure that the $block->context['asp_override_id']
		 * exists on child blocks within the Query Loop and all pagination blocks.
		 */
		if ( $block_type === 'generateblocks/looper'
			|| str_starts_with($block_type, 'generateblocks/query-page-numbers')
			|| str_starts_with($block_type, 'generateblocks/query-no-results')
		) {
			if ( !isset( $args['uses_context'] ) ) {
				$args['uses_context'] = array();
			}
			$args['uses_context'][] = 'asp_override_id';
		}
		return $args;
	}


	/**
	 * Hooks into: generateblocks_query_wp_query_args
	 *
	 * @param array $query
	 * @param array $attributes
	 * @return array
	 */
	public function queryVars( array $query, array $attributes ) {
		if ( !isset($_GET['p_asid'], $attributes['asp_override_id']) ) {
			return $query;
		}
		if ( intval($_GET['p_asid']) === intval($attributes['asp_override_id']) ) {
			$query_id = intval($attributes['uniqueId']) ?? 0;

			if ( $query_id > 0 && isset($this->cached_results[ $query_id ]) ) {
				$query['orderby']  = 'post__in';
				$query['post__in'] = $this->cached_results[ $query_id ];
				return $query;
			}

			$id                    = intval($_GET['p_asid']);
			$query['asp_override'] = false;
			$page                  = isset($_GET[ "query-$query_id-page" ]) ? intval($_GET[ "query-$query_id-page" ]) : 1;
			$phrase                = $_GET['asp_ls'] ?? $_GET['s'] ?? $wp_query->query_vars['s'] ?? '';
			$ids                   = array();
			$search_args           = array(
				's'              => $phrase,
				'_ajax_search'   => false,
				'search_type'    => array( 'cpt' ),
				// Do not recommend going over that, as the post__in argument will generate a
				// too long query to complete, as well as Elementor processes all of these
				// results, yielding a terrible loading time.
				'posts_per_page' => 500,
			);
			add_filter('asp_query_args', array( SearchOverride::getInstance(), 'getAdditionalArgs' ));

			if ( isset($_GET['asp_force_reset_pagination']) ) {
				// For the correct pagination highlight
				$search_args['page'] = 1;
				$query['offset']     = 0;
			} else {
				$search_args['page'] = $page;
			}
			$options = Search::getOptions();
			if ( $options === false || count($options) === 0 ) {
				$asp_query = new SearchQuery($search_args, $id);
			} else {
				$asp_query = new SearchQuery($search_args, $id, $options);
			}

			foreach ( $asp_query->posts as $r ) {
				$ids[] = $r->ID;
			}
			if ( count($ids) > 0 ) {
				$query['post__in']                 = $ids;
				$query['orderby']                  = 'post__in';
				$this->cached_results[ $query_id ] = $query['post__in'];
			} else {
				$query['post_type'] = '____non_existent';
			}
		}

		return $query;
	}
}
