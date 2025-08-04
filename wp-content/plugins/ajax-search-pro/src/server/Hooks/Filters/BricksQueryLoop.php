<?php

namespace WPDRMS\ASP\Hooks\Filters;

use WP_Query;
use WPDRMS\ASP\Models\SearchQueryArgs;
use WPDRMS\ASP\Utils\Search;
use WPDRMS\ASP\Utils\WPQueryUtils;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * Bricks Query Loop Integration
 */
class BricksQueryLoop extends AbstractFilter {
	/**
	 * Hooks into bricks/posts/query_vars
	 */
	public function bricksPostsQueryVars( $query_vars, $settings, $element_id, $element_name ) {
		$id = Search::overrideSearchId();
		if (
			$id > 0 &&
			isset($settings['_cssClasses']) &&
			strpos($settings['_cssClasses'], 'asp_es_' . $id) !== false
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
