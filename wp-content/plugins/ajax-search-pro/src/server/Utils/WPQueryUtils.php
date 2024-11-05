<?php

namespace WPDRMS\ASP\Utils;

use WP_Query;
use WPDRMS\ASP\Models\SearchQueryArgs;

class WPQueryUtils {

	/**
	 * Converts WP_Query ordering params to ASP Query ordering parameters
	 *
	 * @param WP_Query        $wp_query
	 * @param SearchQueryArgs $args
	 * @return SearchQueryArgs
	 */
	public static function toASPQueryOrdering( WP_Query $wp_query, SearchQueryArgs $args ): SearchQueryArgs {
		if ( !isset($wp_query->query_vars['orderby']) ) {
			return $args;
		}

		$way = $wp_query->query_vars['order'] === 'ASC' ? 'ASC' : 'DESC';
		switch ( $wp_query->query_vars['orderby'] ) {
			case 'id':
			case 'ID':
				$args->post_primary_order = "id $way";
				break;
			case 'author':
				$args->post_primary_order = "author $way";
				break;
			case 'title':
			case 'name':
			case 'type':
				$args->post_primary_order = "post_title $way";
				break;
			case 'date':
				$args->post_primary_order = "post_date $way";
				break;
			case 'modified':
				$args->post_primary_order = "post_modified $way";
				break;
			case 'menu_order':
				$args->post_primary_order = "menu_order $way";
				break;
			case 'meta_value':
				$args->post_primary_order          = "customfp $way";
				$args->post_primary_order_metatype = 'string';
				$args->_post_primary_order_metakey = $wp_query->query_vars['meta_key'] ?? 'unknown';
				break;
			case 'meta_value_num':
				$args->post_primary_order          = "customfp $way";
				$args->post_primary_order_metatype = 'numeric';
				$args->_post_primary_order_metakey = $wp_query->query_vars['meta_key'] ?? 'unknown';
				break;
		}

		return $args;
	}

	/**
	 * Converts WP_Query taxonomy term params to ASP Query taxonomy parameters
	 *
	 * @param WP_Query        $wp_query
	 * @param SearchQueryArgs $args
	 * @return SearchQueryArgs
	 */
	public static function toASPQueryTaxFilters( WP_Query $wp_query, SearchQueryArgs $args ): SearchQueryArgs {
		if ( !isset($wp_query->query_vars['tax_query']) ) {
			return $args;
		}
		foreach ( $wp_query->query_vars['tax_query'] as $tax_query ) {
			if ( $tax_query['field'] !== 'term_id' ) {
				continue;
			}
			$args->post_tax_filter[] = array(
				'taxonomy'    => $tax_query['taxonomy'],
				'include'     => $tax_query['terms'],
				'exclude'     => array(),
				'allow_empty' => false,
			);
		}
		return $args;
	}
}
