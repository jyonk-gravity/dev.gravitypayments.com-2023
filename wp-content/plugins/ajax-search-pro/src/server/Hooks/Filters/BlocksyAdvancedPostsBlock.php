<?php

namespace WPDRMS\ASP\Hooks\Filters;

use WPDRMS\ASP\Query\SearchQuery;
use WPDRMS\ASP\Utils\Search;

/**
 * GenerateBlocks plugin Query Loop live search and filter compatibility
 */
class BlocksyAdvancedPostsBlock extends AbstractFilter {
	/**
	 * Hooks into: blocksy:general:blocks:query:args
	 *
	 * @param array $query
	 * @param array $attributes
	 * @return array
	 */
	public function queryVars( array $query, array $attributes ) {
		if ( !isset($_GET['p_asid'], $_GET['unique_id']) ) {
			return $query;
		}
		$query_id = strval($attributes['uniqueId']) ?? '';
		if ( $query_id !== '' && strval($_GET['unique_id']) !== $query_id ) {
			return $query;
		}
		$query['asp_override'] = true;
		/**
		 * The $query_args['page'] should not be set as we want the full set
		 * and the loop will take care of the page.
		 */
		if ( isset($_GET['asp_force_reset_pagination']) ) {
			// For the correct pagination highlight
			$query['offset'] = 0;
		}
		return $query;
	}
}
