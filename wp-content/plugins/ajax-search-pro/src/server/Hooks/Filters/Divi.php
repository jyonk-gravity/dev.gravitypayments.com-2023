<?php

namespace WPDRMS\ASP\Hooks\Filters;

use WP_Query;
use WPDRMS\ASP\Models\SearchQueryArgs;
use WPDRMS\ASP\Utils\WPQueryUtils;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * Advanced Custom Fields related Hooks
 */
class Divi {
	private ?WP_Query $wp_query;

	public function __construct( ?WP_Query $wp_query ) {
		$this->wp_query = $wp_query;
	}

	/**
	 * Fixes ordering and other query arguments on Divi Filter Grid
	 *
	 * Hook: asp_query_args
	 *
	 * @param SearchQueryArgs $args
	 * @return SearchQueryArgs
	 */
	public function filterGridQueryArgs( SearchQueryArgs $args ): SearchQueryArgs {
		$wp_query = $this->wp_query;
		if ( !isset($wp_query, $wp_query->query_vars['dfg_context'], $wp_query->query_vars['asp_override']) ) {
			return $args;
		}

		if ( isset($_POST['module_data']['query_var']['s']) ) {
			$args->s = $_POST['module_data']['query_var']['s']; // @phpcs:ignore
		}

		$args = WPQueryUtils::toASPQueryOrdering($wp_query, $args);
		return WPQueryUtils::toASPQueryTaxFilters($wp_query, $args);
	}
}
