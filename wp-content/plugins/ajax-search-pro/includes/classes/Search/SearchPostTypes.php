<?php

/**
 * @noinspection RegExpRedundantEscape
 * @noinspection RegExp
 * @noinspection DuplicatedCode
 *
 * @package Ajax Search Pro
 */

namespace WPDRMS\ASP\Search;

use WPDRMS\ASP\Misc\Priorities;
use WPDRMS\ASP\Utils\Html;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Post;
use WPDRMS\ASP\Utils\Str;
use WPDRMS\ASP\Utils\Taxonomy;
use WPDRMS\ASP\Utils\User;

defined( 'ABSPATH' ) || die( "You can't access this file directly." );

/**
 * Regular engine post type
 *
 * @phpstan-import-type ResultObj from AbstractSearch
 */
class SearchPostTypes extends AbstractSearch {

	/**
	 * @var array<int, array<int, array<string>>> of query parts
	 */
	protected array $parts = array();

	/**
	 * @var array<string> of custom field query parts
	 */
	protected array $cf_parts = array();

	/**
	 * @var string[]
	 */
	protected array $ordering = array(
		'primary'       => 'relevance DESC',
		'secondary'     => 'post_date DESC',
		'primary_field' => 'relevance',
	);

	/**
	 * Keep track of how many times the switch_blog was called
	 *
	 * @var int
	 */
	protected int $multisite_switch_count = 0;

	/**
	 * How many results were ignored from the start during search due to pagination
	 * This is needed to calculate the correct page in class-asp-query.php
	 * Used only with index table
	 *
	 * @var int
	 */
	public int $start_offset = 0;

	/**
	 * Gets the related taxonomy term count based on input post IDs and taxonomies and terms
	 *
	 * @param int[]                $post_ids Array of post IDs or Array of Objects with post IDs
	 * @param array<string, int[]> $taxonomy_terms ['taxonomy' => int[] terms]
	 * @param int                  $max_packet_size Maximum number of IDs per query
	 * @param int                  $max_call_count  Maximum number of queries overall
	 * @return int[] associative array of term_id => object_count
	 * @noinspection All
	 */
	public function get_relevant_tax_terms(
		array $post_ids,
		array $taxonomy_terms,
		int $max_packet_size = 1000,
		int $max_call_count = 2
	): array {
		$_ids    = array();
		$results = array();

		/*
		H
		if ( count($post_ids) <= 0 ) {
			return $results;
		}

		// Is this an object array?
		if ( isset($post_ids[0]->id) ) {
			foreach ( $post_ids as $o ) {
				$_ids[] = $o->id;
			}
		} else {
			$_ids = $post_ids;
		}
		for ( $i =0;$i <$max_call_count;$i++ ) {
			$pack = array_splice($_ids, $max_packet_size);

			// SELECT id FROM xxx WHERE post in $pack AND tax in ($taxonomies)
			// $results = array_merge($results ...)

			if ( count($_ids) <= 0 ) {
				break;
			}
		}
		*/

		return array_unique($results);
	}

	/**
	 * Content search function
	 *
	 * @return void
	 */
	protected function doSearch(): void {
		global $wpdb;
		global $q_config;

		$args = &$this->args;

		$sd = $args['_sd'] ?? array();

		// Prefixes and suffixes
		$pre_field = $this->pre_field;
		$suf_field = $this->suf_field;
		$pre_like  = $this->pre_like;
		$suf_like  = $this->suf_like;

		$wcl = '%'; // Wildcard Left
		$wcr = '%'; // Wildcard right
		if ( $args['_exact_matches'] ) {
			if ( $args['_exact_match_location'] === 'start' ) {
				$wcl = '';
			} elseif ( $args['_exact_match_location'] === 'end' ) {
				$wcr = '';
			} elseif ( $args['_exact_match_location'] === 'full' ) {
				$wcr = '';
				$wcl = '';
			}
		}

		$kw_logic             = $args['keyword_logic'];
		$q_config['language'] = $args['_qtranslate_lang'];

		$s  = $this->s; // full keyword
		$_s = $this->_s; // array of keywords

		if ( $args['_limit'] > 0 ) {
			$this->remaining_limit = $args['_limit'];
		} elseif ( $args['_ajax_search'] ) {
				$this->remaining_limit = $args['posts_limit'];
		} else {
			$this->remaining_limit = $args['posts_limit_override'];
		}
		$query_limit = $this->remaining_limit * $this->remaining_limit_mod;

		if ( $this->remaining_limit <= 0 ) {
			return;
		}

		$group_priority_select  = $this->buildPgpQuery( $wpdb->posts . '.ID' );
		$group_priority_select  = $group_priority_select . ' AS group_priority,';
		$group_priority_orderby = 'group_priority DESC, ';

		/**
		 * Determine if the priorities table should be used or not.
		 */
		$priority_select = Priorities::count() > 0 ? '
		IFNULL((
			SELECT
			aspp.priority
			FROM ' . wd_asp()->db->table('priorities') . " as aspp
			WHERE aspp.post_id = $wpdb->posts.ID AND aspp.blog_id = " . get_current_blog_id() . '
		), 100)
		' : 100;

		/*------------------------- Statuses ----------------------------*/
		$post_statuses    = '';
		$allowed_statuses = "'publish'"; // used later!
		if ( count($args['post_status']) > 0 ) {
			$allowed_statuses = "'" . implode( "','", $args['post_status'] ) . "'";
			$post_statuses    = 'AND (' . $pre_field . $wpdb->posts . '.post_status' . $suf_field . " IN ($allowed_statuses) )";
		}
		/*---------------------------------------------------------------*/

		/*------------------------- Paswword ----------------------------*/
		$post_password_query = '';
		if ( !$args['has_password'] ) {
			$post_password_query = " AND ( $wpdb->posts.post_password = '' )";
		}
		/*---------------------------------------------------------------*/

		/*----------------------- Gather Types --------------------------*/
		$page_q = '';
		if ( !empty($args['_exclude_page_parent_child']) ) {
			$page_q = " AND (
				$wpdb->posts.post_parent NOT IN (" . implode( ',', $args['_exclude_page_parent_child'] ) . ") AND
				$wpdb->posts.ID NOT IN (" . implode( ',', $args['_exclude_page_parent_child'] ) . ')
			)';
		}

		// If no post types selected, well then return
		if ( count( $args['post_type'] ) < 1 && $page_q === '' ) {
			return;
		} else {
			$words = implode( "','", $args['post_type'] );
			if ( in_array('product_variation', $args['post_type'], true) ) {
				$_post_types = $args['post_type'];
				$_post_types = array_diff($_post_types, array( 'product_variation' ));
				if ( count($_post_types) > 0 ) {
					$or_ptypes = "OR $wpdb->posts.post_type IN ('" . implode("', '", $_post_types) . "')";
				} else {
					$or_ptypes = '';
				}
				$post_types = "
				((
					(
						$wpdb->posts.post_type = 'product_variation' AND 
						EXISTS(SELECT 1 FROM $wpdb->posts par WHERE par.ID = $wpdb->posts.post_parent AND par.post_status IN($allowed_statuses) ) 
					)  $or_ptypes
				) $page_q)";
			} else {
				$post_types = "($wpdb->posts.post_type IN ('$words') $page_q)";
			}
		}
		/*---------------------------------------------------------------*/

		// ------------------------ Categories/tags/taxonomies ----------------------
		$term_query = $this->buildTermQuery( $wpdb->posts . '.ID', $wpdb->posts . '.post_type' );
		// ---------------------------------------------------------------------

		/*------------- Custom Fields with Custom selectors -------------*/
		$cf_select = $this->buildCffQuery( $wpdb->posts . '.ID' );
		/*---------------------------------------------------------------*/

		/*----------------------- Exclude USER id -----------------------*/
		$user_query = '';
		if ( isset($args['post_user_filter']['include']) ) {
			if ( !in_array(-1, $args['post_user_filter']['include']) ) { // @phpcs:ignore
				$user_query = "AND $wpdb->posts.post_author IN (" . implode(', ', $args['post_user_filter']['include']) . ')
				';
			}
		}
		if ( isset($args['post_user_filter']['exclude']) ) {
			if ( !in_array(-1, $args['post_user_filter']['exclude']) ) { // @phpcs:ignore
				$user_query = "AND $wpdb->posts.post_author NOT IN (" . implode(', ', $args['post_user_filter']['exclude']) . ') ';
			} else {
				return;
			}
		}
		/*---------------------------------------------------------------*/

		/*------------------------ Exclude ids --------------------------*/
		if ( !empty($args['post_not_in']) ) {
			$exclude_posts = "AND ($wpdb->posts.ID NOT IN (" . ( is_array($args['post_not_in']) ? implode(',', $args['post_not_in']) : $args['post_not_in'] ) . '))';
		} else {
			$exclude_posts = '';
		}
		if ( !empty($args['post_not_in2']) ) {
			$exclude_posts .= "AND ($wpdb->posts.ID NOT IN (" . implode(',', $args['post_not_in2']) . '))';
		}
		/*---------------------------------------------------------------*/

		/*------------------------ Include ids --------------------------*/
		if ( !empty($args['post_in']) ) {
			$include_posts = "AND ($wpdb->posts.ID IN (" . ( is_array($args['post_in']) ? implode(',', $args['post_in']) : $args['post_in'] ) . '))';
		} else {
			$include_posts = '';
		}
		/*---------------------------------------------------------------*/

		// ------------------------ Term JOIN -------------------------  //
		// If the search in terms is not active, we don't need this unnecessary big join
		$term_join = '';
		if ( in_array('terms', $args['post_fields'], true) ) {
			$term_join = "
			LEFT JOIN $wpdb->term_relationships ON $wpdb->posts.ID = $wpdb->term_relationships.object_id
			LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
			LEFT JOIN $wpdb->terms ON $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id";
		}
		/*---------------------------------------------------------------*/

		/*------------------------- WPML filter -------------------------*/
		$wpml_query = '(1)';
		if ( $args['_wpml_lang'] !== '' ) {
			global $sitepress;
			$site_lang_selected  = false;
			$wpml_post_types_arr = array();

			foreach ( $args['post_type'] as $tt ) {
				$wpml_post_types_arr[] = 'post_' . $tt;
			}
			$wpml_post_types = implode( "','", $wpml_post_types_arr );

			// Let us get the default site language if possible
			if ( is_object($sitepress) && method_exists($sitepress, 'get_default_language') ) {
				$site_lang_selected = $sitepress->get_default_language() === $args['_wpml_lang'];
			}

			$_wpml_query_id_field = "$wpdb->posts.ID";
			// Product variations are not translated, so we need to use the parent ID (product) field to compare
			if ( in_array('product_variation', $args['post_type'], true) ) {
				$_wpml_query_id_field = "(IF($wpdb->posts.post_type='product_variation', $wpdb->posts.post_parent, $wpdb->posts.ID))";
			}

			$wpml_query = '
			EXISTS (
				SELECT DISTINCT(wpml.element_id)
				FROM ' . $wpdb->prefix . "icl_translations as wpml
				WHERE
					$_wpml_query_id_field = wpml.element_id AND
					wpml.language_code = '" . Str::escape( $args['_wpml_lang'] ) . "' AND
					wpml.element_type IN ('$wpml_post_types')
			)";

			/**
			 * For missing translations...
			 * If the site language is used, the translation can be non-existent
			 */
			if ( $args['_wpml_allow_missing_translations'] && $site_lang_selected ) {
				$wpml_query = '
				NOT EXISTS (
					SELECT DISTINCT(wpml.element_id)
					FROM ' . $wpdb->prefix . "icl_translations as wpml
					WHERE
						$_wpml_query_id_field = wpml.element_id AND
						wpml.element_type IN ('$wpml_post_types')
				) OR
				" . $wpml_query;
			}
		}
		/*---------------------------------------------------------------*/

		/*----------------------- POLYLANG filter -----------------------*/
		$polylang_query = '';
		if ( $args['_polylang_lang'] !== '' && function_exists('pll_default_language') ) {
			$languages = get_terms(
				array(
					'taxonomy'   => 'language',
					'hide_empty' => false,
					'fields'     => 'ids',
					'orderby'    => 'term_group',
					'slug'       => $args['_polylang_lang'],
				)
			);
			if ( !empty($languages) && !is_wp_error($languages) && isset($languages[0]) ) {
				if ( in_array('product_variation', $args['post_type'], true) && class_exists('WooCommerce') ) {
					$poly_field = "IF($wpdb->posts.post_type = 'product_variation', $wpdb->posts.post_parent, $wpdb->posts.ID)";
				} else {
					$poly_field = "$wpdb->posts.ID";
				}
				$polylang_empty_query = '';
				/** @noinspection PhpUndefinedFunctionInspection */
				if ( $args['_polylang_lang'] === pll_default_language() ) {
					$polylang_empty_query = "
						NOT EXISTS (
							SELECT *
							FROM $wpdb->term_relationships as xt
							INNER JOIN $wpdb->term_taxonomy as tt ON ( xt.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'language')
							WHERE
								xt.object_id = $wpdb->posts.ID
						) OR ";
				}
				$polylang_query = " AND (
					$polylang_empty_query
					$poly_field IN ( SELECT DISTINCT(tr.object_id)
						FROM $wpdb->term_relationships AS tr
						LEFT JOIN $wpdb->term_taxonomy as tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'language')
						WHERE tt.term_id = $languages[0]
					 ) 
				 )";
			}
		}
		/*---------------------------------------------------------------*/

		/*--------------------- Post parent IDs -------------------------*/
		$post_parents = '';
		if ( count($args['post_parent']) > 0 ) {
			$post_parents = "AND $wpdb->posts.post_parent IN (" . implode(',', $args['post_parent']) . ') ';
		}
		/*---------------------------------------------------------------*/

		/*---------------- Post parent IDs exclude ----------------------*/
		$post_parents_exclude = '';
		if ( count($args['post_parent_exclude']) > 0 ) {
			$post_parents_exclude = "AND $wpdb->posts.post_parent NOT IN (" . implode(',', $args['post_parent_exclude']) . ') ';
		}
		/*---------------------------------------------------------------*/

		// --------------------- Other Query stuff ----------------------- //
		// Do not select the content field, if it is not used at all
		$select_content = $args['_post_get_content'] ? $wpdb->posts . '.post_content' : "''";

		// Do not select excerpt if it's not used at all
		$select_excerpt = $args['_post_get_excerpt'] ? $wpdb->posts . '.post_excerpt' : "''";
		/*---------------------------------------------------------------*/

		/*----------------------- Date filtering ------------------------*/
		$date_query       = '';
		$date_query_parts = $this->get_date_query_parts();
		if ( count($date_query_parts) > 0 ) {
			$date_query = ' AND (' . implode(' AND ', $date_query_parts) . ') ';
		}
		/*---------------------------------------------------------------*/

		/*-------------- Additional Query parts by Filters --------------*/
		/**
		 * Use these filters to add additional parts to the select, join or where
		 * parts of the search query.
		 * Params:
		 *  array() $args - search arguments
		 *  string $s - the full search keyword
		 *  array() $_s - the array of unique search keywords separated
		 */
		$add_select = apply_filters('asp_cpt_query_add_select', '', $args, $s, $_s);
		$add_join   = apply_filters('asp_cpt_query_add_join', '', $args, $s, $_s);
		$add_where  = apply_filters('asp_cpt_query_add_where', '', $args, $s, $_s);
		/*---------------------------------------------------------------*/

		/*------------------- Post type based ordering ------------------*/
		$p_type_priority = '';
		if ( w_isset_def($sd['use_post_type_order'], 0) ) { // @phpcs:ignore
			foreach ( $sd['post_type_order'] as $pk => $p_order ) {
				$p_type_priority .= "
				WHEN '$p_order' THEN $pk ";
			}
			if ( $p_type_priority !== '' ) {
				$p_type_priority = "
					CASE $wpdb->posts.post_type
				" . ' ' . $p_type_priority . '
					  ELSE 999
					END ';
			} else {
				$p_type_priority = '1';
			}
		} else {
			$p_type_priority = '1';
		}
		/*---------------------------------------------------------------*/

		/*---------------- Primary custom field ordering ----------------*/
		$custom_field_selectp = '1 ';
		if (
			strpos($args['post_primary_order'], 'customfp') !== false &&
			$args['_post_primary_order_metakey'] !== ''
		) {
			// @phpstan-ignore-next-line
			$custom_field_selectp = "(SELECT IF(meta_value IS NULL, 0, meta_value)
			FROM $wpdb->postmeta
			WHERE
				$wpdb->postmeta.meta_key='" . esc_sql($args['_post_primary_order_metakey']) . "' AND
				$wpdb->postmeta.post_id=$wpdb->posts.ID
			LIMIT 1
			) ";
		}
		/*---------------------------------------------------------------*/

		/*--------------- Secondary custom field ordering ---------------*/
		$custom_field_selects = '1 ';
		if (
			strpos($args['post_secondary_order'], 'customfs') !== false &&
			$args['_post_secondary_order_metakey'] !== ''
		) {
			// @phpstan-ignore-next-line
			$custom_field_selects = "(SELECT IF(meta_value IS NULL, 0, meta_value)
			FROM $wpdb->postmeta
			WHERE
				$wpdb->postmeta.meta_key='" . esc_sql($args['_post_secondary_order_metakey']) . "' AND
				$wpdb->postmeta.post_id=$wpdb->posts.ID
			LIMIT 1
			) ";
		}
		/*---------------------------------------------------------------*/

		/**
		 * This is the main query.
		 *
		 * The ttid field is a bit tricky as the term_taxonomy_id doesn't always equal term_id,
		 * so we need the LEFT JOINS :(
		 */

		$orderby_primary   = str_replace( 'post_', $wpdb->posts . '.post_', $args['post_primary_order'] );
		$orderby_secondary = str_replace( 'post_', $wpdb->posts . '.post_', $args['post_secondary_order'] );

		if ( $args['post_primary_order_metatype'] === 'numeric' ) {
			$orderby_primary = str_replace('customfp', 'CAST(customfp as SIGNED)', $orderby_primary);
		}

		if ( $args['post_secondary_order_metatype'] === 'numeric' ) {
			$orderby_secondary = str_replace('customfs', 'CAST(customfs as SIGNED)', $orderby_secondary);
		}

		$this->query = "
		SELECT
			$add_select
			{args_fields}
			$wpdb->posts.post_title as `title`,
			$wpdb->posts.post_title as `post_title`,
			$wpdb->posts.ID as id,
			$this->c_blogid as `blogid`,
			$wpdb->posts.post_date as `date`,
			$wpdb->posts.post_date as `post_date`,
			$wpdb->posts.post_modified as post_modified,
			$select_content as `content`,
			$select_excerpt as `excerpt`,
			$wpdb->posts.post_type as `post_type`,
			'pagepost' as `content_type`,
			'post_page_cpt' as `g_content_type`,
			(SELECT
				$wpdb->users." . w_isset_def( $sd['author_field'], 'display_name' ) . " as `author`
				FROM $wpdb->users
				WHERE $wpdb->users.ID = $wpdb->posts.post_author
			) as `author`,
			$wpdb->posts.post_author as `post_author`,
			$wpdb->posts.post_type as `post_type`,
			$priority_select as `priority`,
			$p_type_priority as `p_type_priority`,
			$group_priority_select
			{relevance_query} as `relevance`,
			$custom_field_selectp as `customfp`,
			$custom_field_selects as `customfs`
		FROM $wpdb->posts
			$term_join
			$add_join
			{args_join}
		WHERE
			$post_types
			$term_query
			$user_query
			AND $cf_select
			$post_statuses
			$post_password_query
			AND {like_query}
			$exclude_posts
			$include_posts
			$post_parents
			$post_parents_exclude
			AND ( $wpml_query )
			$polylang_query
			$date_query
			$add_where
			{args_where}
		GROUP BY
			{args_groupby}
		ORDER BY {args_orderby} $group_priority_orderby priority DESC, p_type_priority ASC, $orderby_primary, $orderby_secondary, id DESC
		LIMIT $query_limit";

		// Place the argument query fields
		if ( isset($args['cpt_query']) && is_array($args['cpt_query']) ) {
			$this->query = str_replace(
				array( '{args_fields}', '{args_join}', '{args_where}', '{args_orderby}' ),
				array( $args['cpt_query']['fields'], $args['cpt_query']['join'], $args['cpt_query']['where'], $args['cpt_query']['orderby'] ),
				$this->query
			);
		} else {
			$this->query = str_replace(
				array( '{args_fields}', '{args_join}', '{args_where}', '{args_orderby}' ),
				'',
				$this->query
			);
		}
		if ( isset($args['cpt_query']['groupby']) && $args['cpt_query']['groupby'] !== '' ) {
			$this->query = str_replace('{args_groupby}', $args['cpt_query']['groupby'], $this->query);
		} else {
			$this->query = str_replace('{args_groupby}', "$wpdb->posts.ID", $this->query);
		}

		$words = $args['_exact_matches'] && $s !== '' ? array( $s ) : $_s;
		/**
		 * Ex.: When the minimum word count is 2, and the user enters 'a' then $_s is empty.
		 *      But $s is not actually empty, thus the wrong query will be executed.
		 */
		if ( count($words) === 0 && $s !== '' ) {
			$words = array( $s );
			// Allow only beginnings
			if ( !$args['_exact_matches'] ) {
				$wcl = '';
			}
		}
		if ( $s !== '' ) {
			$words = !in_array($s, $words, true) ? array_merge(array( $s ), $words) : $words;
		}

		$relevance_added = false;
		foreach ( $words as $k => $word ) {
			$parts           = array();
			$relevance_parts = array();
			$is_exact        = $args['_exact_matches'] ||
				( count($words) > 1 && $k === 0 && ( $kw_logic === 'or' || $kw_logic === 'and' ) );

			/*----------------------- Title query ---------------------------*/
			if ( in_array('title', $args['post_fields'], true) ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
					$parts[] = '( ' . $pre_field . $wpdb->posts . '.post_title' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = '
						   ( ' . $pre_field . $wpdb->posts . '.post_title' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
						OR  " . $pre_field . $wpdb->posts . '.post_title' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
						OR  " . $pre_field . $wpdb->posts . '.post_title' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
						OR  " . $pre_field . $wpdb->posts . '.post_title' . $suf_field . " = '" . $word . "')";
				}
				if ( !$relevance_added ) {
					$relevance_parts[] = '(case when
				(' . $pre_field . $wpdb->posts . '.post_title' . $suf_field . " LIKE '$s')
				 then " . ( w_isset_def($sd['etitleweight'], 10) * 4 ) . ' else 0 end)';

					$relevance_parts[] = '(case when
				(' . $pre_field . $wpdb->posts . '.post_title' . $suf_field . " LIKE '$s%')
				 then " . ( w_isset_def($sd['etitleweight'], 10) * 2 ) . ' else 0 end)';

					$relevance_parts[] = '(case when
				(' . $pre_field . $wpdb->posts . '.post_title' . $suf_field . " LIKE '%$s%')
				 then " . w_isset_def($sd['titleweight'], 10) . ' else 0 end)';

					// The first word relevance is higher
					if ( isset($_s[0]) ) {
						$relevance_parts[] = '(case when
				  (' . $pre_field . $wpdb->posts . '.post_title' . $suf_field . " LIKE '%" . $_s[0] . "%')
				   then " . w_isset_def($sd['etitleweight'], 10) . ' else 0 end)';
					}
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Content query --------------------------*/
			if ( in_array('content', $args['post_fields'], true) ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
					$parts[] = '( ' . $pre_field . $wpdb->posts . '.post_content' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
					/**
					 * Exact matching multi line + word boundary with REGEXP
					 *
					 * $parts[] = "( " . $pre_field . $wpdb->posts . ".post_content" . $suf_field . " REGEXP '([[:blank:][:punct:]]|^|\r\n)" . $word . "([[:blank:][:punct:]]|$|\r\n)' )";
					 */
				} else {
					$parts[] = '
					   (' . $pre_field . $wpdb->posts . '.post_content' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->posts . '.post_content' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->posts . '.post_content' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->posts . '.post_content' . $suf_field . " = '" . $word . "')";
				}
				if ( !$relevance_added ) {
					if ( isset($_s[0]) ) {
						$relevance_parts[] = '(case when
						(' . $pre_field . $wpdb->posts . '.post_content' . $suf_field . " LIKE '%" . $_s[0] . "%')
						 then " . w_isset_def($sd['contentweight'], 10) . ' else 0 end)';
					}

					$relevance_parts[] = '(case when
				(' . $pre_field . $wpdb->posts . '.post_content' . $suf_field . " LIKE '%$s%')
				 then " . w_isset_def($sd['econtentweight'], 10) . ' else 0 end)';
				}
			}
			/*---------------------------------------------------------------*/

			/*----------------- Permalink/Post name query -------------------*/
			if ( in_array('permalink', $args['post_fields'], true) ) {
				$parts[] = '( ' . $pre_field . $wpdb->posts . '.post_name' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Excerpt query --------------------------*/
			if ( in_array('excerpt', $args['post_fields'], true) ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
					$parts[] = '( ' . $pre_field . $wpdb->posts . '.post_excerpt' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = '
					   (' . $pre_field . $wpdb->posts . '.post_excerpt' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->posts . '.post_excerpt' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->posts . '.post_excerpt' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->posts . '.post_excerpt' . $suf_field . " = '" . $word . "')";
				}
				if ( !$relevance_added ) {
					if ( isset($_s[0]) ) {
						$relevance_parts[] = '(case when
						(' . $pre_field . $wpdb->posts . '.post_excerpt' . $suf_field . " LIKE '%" . $_s[0] . "%')
						 then " . w_isset_def($sd['excerptweight'], 10) . ' else 0 end)';
					}

					$relevance_parts[] = '(case when
				(' . $pre_field . $wpdb->posts . '.post_excerpt' . $suf_field . " LIKE '%$s%')
				 then " . w_isset_def($sd['eexcerptweight'], 10) . ' else 0 end)';
				}
			}
			/*---------------------------------------------------------------*/

			/*------------------------ Term query ---------------------------*/
			if ( in_array('terms', $args['post_fields'], true) ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
					$parts[] = '( ' . $pre_field . $wpdb->terms . '.name' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = '
					   (' . $pre_field . $wpdb->terms . '.name' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->terms . '.name' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->terms . '.name' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->terms . '.name' . $suf_field . " = '" . $word . "')";
				}

				if ( !$relevance_added ) {
					$relevance_parts[] = '(case when
				(' . $pre_field . $wpdb->terms . '.name' . $suf_field . " = '$s')
				 then " . w_isset_def($sd['etermsweight'], 10) . ' else 0 end)';
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Custom Fields --------------------------*/
			if ( $args['post_custom_fields_all'] ) {
				$args['post_custom_fields'] = array( 'all' );
			}

			if ( count($args['post_custom_fields']) > 0 ) {
				$cf_parts = array();
				foreach ( $args['post_custom_fields'] as $cfield ) {
					$key_part = $args['post_custom_fields_all'] ? '' : "$wpdb->postmeta.meta_key='$cfield' AND ";

					if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
						$cf_parts[] = "( $key_part " . $pre_field . $wpdb->postmeta . '.meta_value' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
					} else {
						$cf_parts[] = "( $key_part 
						(" . $pre_field . $wpdb->postmeta . '.meta_value' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
						OR  " . $pre_field . $wpdb->postmeta . '.meta_value' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
						OR  " . $pre_field . $wpdb->postmeta . '.meta_value' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
						OR  " . $pre_field . $wpdb->postmeta . '.meta_value' . $suf_field . " = '" . $word . "') )";
					}               
				}
				$parts[] = "( EXISTS (SELECT 1 FROM $wpdb->postmeta WHERE (" . implode(' OR ', $cf_parts) . ") AND $wpdb->posts.ID = $wpdb->postmeta.post_id) )";
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Post CPT IDs ---------------------------*/
			if ( in_array('ids', $args['post_fields'], true) ) {
				$parts[] = "($wpdb->posts.ID LIKE '$word')";
			}
			/*---------------------------------------------------------------*/

			$this->parts[]   = array( $parts, $relevance_parts );
			$relevance_added = true;
		}

		$querystr      = $this->buildQuery( $this->parts );
		$querystr      = apply_filters('asp_query_cpt', $querystr, $args, $args['_id'], $args['_ajax_search']);
		$all_pageposts = $wpdb->get_results( $querystr ); // @phpcs:ignore

		// Get the real count, up to 500
		$this->results_count = count($all_pageposts);
		// For non-ajax search, results count needs to be limited to the maximum limit,
		// ...as nothing is parsed beyond that
		if ( !$args['_ajax_search'] && $this->results_count > $this->remaining_limit ) {
			$this->results_count = $this->remaining_limit;
		}
		// Slice the needed ones only
		$all_pageposts = array_slice($all_pageposts, $args['_call_num'] * $this->remaining_limit, $this->remaining_limit);

		$this->results      = $all_pageposts;
		$this->return_count = count($this->results);
	}

	protected function buildPgpQuery( string $post_id_field ): string {
		global $wpdb;
		$query = '1';
		$s     = $this->s;
		$args  = $this->args;

		$group_query_arr = array();
		foreach ( wd_asp()->priority_groups->getSorted() as $group ) {
			// Rule affects this instance?
			if ( $group['instance'] != 0 && $group['instance'] != $args['_id'] ) { // @phpcs:ignore
				continue;
			}
			// Rule affects this phrase?
			if ( MB::strlen($group['phrase']) > 0 ) {
				switch ( $group['phrase_logic'] ) {
					case 'any':
						if ( MB::strpos($s, $group['phrase']) === false ) {
							continue 2;
						}
						break;
					case 'exact':
						if ( $s !== $group['phrase'] ) {
							continue 2;
						}
						break;
					case 'start':
						if ( MB::strpos($s, $group['phrase']) !== 0 ) {
							continue 2;
						}
						break;
					case 'end':
						if ( !( MB::substr($s, -MB::strlen($group['phrase'])) === $group['phrase'] ) ) {
							continue 2;
						}
						break;
				}
			}
			$rule_query_arr = array();
			foreach ( $group['rules'] as $rule ) {
				switch ( $rule['field'] ) {
					case 'tax':
						$tax_term_query_arr = array();
						foreach ( $rule['values'] as $taxonomy => $terms ) {
							if ( count($terms) < 1 ) {
								continue;
							}
							$term_ids = implode(',', $terms);
							if ( $rule['operator'] === 'in' ) {
								$operator = 'EXISTS';
							} else {
								$operator = 'NOT EXISTS';
							}
							$tax_term_query_arr[] = "(
							$operator ( SELECT 1
									FROM $wpdb->term_relationships AS gptr
									LEFT JOIN $wpdb->term_taxonomy as gptt ON (gptr.term_taxonomy_id = gptt.term_taxonomy_id AND gptt.taxonomy = '$taxonomy')
									WHERE gptt.term_id IN ($term_ids) AND gptr.object_id = $post_id_field
							 ) )";
						}
						if ( count($tax_term_query_arr) ) {
							$tax_term_query   = '( ' . implode(' AND ', $tax_term_query_arr) . ' )';
							$rule_query_arr[] = $tax_term_query;
						}
						break;
					case 'cf':
						// Get the field and the values
						foreach ( $rule['values'] as $field => $values ) {
							$field = Str::escape($field);

							$numeric_operators = array( '=', '<>', '>', '>=', '<', '<=' );
							$nkey              = array_search($rule['operator'], $numeric_operators, true);
							if ( false !== $nkey ) {
								$operator = $numeric_operators[ $nkey ];
								$values   = Str::forceNumeric($values);
								$qry      = ' gp_pm.meta_value' . $operator . $values[0];
							} elseif (
								$rule['operator'] === 'between' &&
								isset($values[0], $values[1])
							) {
								$qry = ' gp_pm.meta_value BETWEEN ' . $values[0] . ' AND ' . $values[1];
							} elseif ( $rule['operator'] === 'like' ) {
								$qry = " gp_pm.meta_value LIKE '%" . $values[0] . "%'";
							} elseif ( $rule['operator'] === 'not like' ) {
								$qry = " gp_pm.meta_value NOT LIKE '%" . $values[0] . "%'";
							} elseif ( $rule['operator'] === 'elike' ) {
								$qry = " gp_pm.meta_value LIKE '" . $values[0] . "'";
							} else {
								// None of the operators above?
								continue 3;
							}

							$cf_query         = "
							(EXISTS(
								SELECT 1
								FROM $wpdb->postmeta as gp_pm
								WHERE gp_pm.post_id = $post_id_field AND gp_pm.meta_key='$field' AND $qry
								GROUP BY gp_pm.meta_id
								ORDER BY gp_pm.meta_id
								LIMIT 1
							))";
							$rule_query_arr[] = $cf_query;
							break;
						}
				}
			}

			// Construct the WHEN-THEN for this case
			if ( count($rule_query_arr) > 0 ) {
				$priority = $group['priority'] + 0;
				if ( $group['logic'] === 'and' ) {
					$rule_logic = ' AND ';
				} else {
					$rule_logic = ' OR ';
				}
				$condition         = implode($rule_logic, $rule_query_arr);
				$group_query_arr[] = "WHEN $condition THEN $priority";
			}       
		}

		if ( count($group_query_arr) > 0 ) {
			$query = implode(' ', $group_query_arr);
			$query = "(CASE
				$query
				ELSE 1
			END)";
		}

		return $query;
	}

	protected function buildTermQuery( string $post_id_field, string $post_type_field ): string {
		global $wpdb;
		$args = $this->args;

		if ( isset($_GET['ignore_op']) ) { // @phpcs:ignore
			return '';
		}

		$term_query       = '';
		$term_query_parts = array();

		foreach ( $args['post_tax_filter'] as $item ) {
			$tax_term_query = '';
			$taxonomy       = $item['taxonomy'];

			// Is there an argument set to allow empty items for this taxonomy filter?
			$allow_empty_tax_term = $item['allow_empty'] ?? ( $taxonomy === 'post_tag' ? $args['_post_tags_empty'] : $args['_post_allow_empty_tax_term'] );

			if ( $allow_empty_tax_term ) {
				$empty_terms_query = "
				NOT EXISTS (
					SELECT *
					FROM $wpdb->term_relationships as xt
					INNER JOIN $wpdb->term_taxonomy as tt ON ( xt.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = '$taxonomy')
					WHERE
						xt.object_id = $post_id_field
				) OR ";
			} else {
				$empty_terms_query = '';
			}

			// Quick explanation for the AND
			// ... MAIN SELECT: selects all object_ids that are not in the array
			// ... SUBSELECT:   excludes all the object_ids that are part of the array
			// This is used because of multiple object_ids (posts in more than 1 tag)
			if ( !empty($item['exclude']) ) {
				$words          = implode( ',', $item['exclude'] );
				$tax_term_query = " (
					$empty_terms_query

					$post_id_field IN (
						SELECT DISTINCT(tr.object_id)
							FROM $wpdb->term_relationships AS tr
							LEFT JOIN $wpdb->term_taxonomy as tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = '$taxonomy')
											WHERE
												tt.term_id NOT IN ($words)
												AND tr.object_id NOT IN (
													SELECT DISTINCT(trs.object_id)
													FROM $wpdb->term_relationships AS trs
								LEFT JOIN $wpdb->term_taxonomy as tts ON (trs.term_taxonomy_id = tts.term_taxonomy_id AND tts.taxonomy = '$taxonomy')
													WHERE tts.term_id IN ($words)
												)
									)
								)";
			}
			if ( !empty($item['include']) ) {
				$words = implode( ',', $item['include'] );
				if ( !empty($tax_term_query) ) {
					$tax_term_query .= ' AND ';
				}
				if ( isset($item['logic']) && $item['logic'] === 'andex' ) {
					$tax_term_query .= "(
						$empty_terms_query

						" . count($item['include']) . " = ( SELECT COUNT(tr.object_id)
							FROM $wpdb->term_relationships AS tr
							LEFT JOIN $wpdb->term_taxonomy as tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = '$taxonomy')
							WHERE tt.term_id IN ($words) AND tr.object_id = $post_id_field
					  ) )";
				} else {
					$tax_term_query .= "(
						$empty_terms_query

						$post_id_field IN ( SELECT DISTINCT(tr.object_id)
							FROM $wpdb->term_relationships AS tr
							LEFT JOIN $wpdb->term_taxonomy as tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = '$taxonomy')
							WHERE tt.term_id IN ($words)
					  ) )";
				}
			}

			/**
			 * POST TAG SPECIFIC ONLY
			 *
			 * At this point we need to check if the user wants to hide the empty tags but the $tag_query
			 * turned out to be empty. (if not all tags are used and all of them are selected).
			 * If so, then return true on every post type other than 'post' OR check if any tags
			 * are associated with the post.
			 */
			if (
				$taxonomy === 'post_tag' &&
				$args['_post_tags_active'] &&
				$tax_term_query === '' &&
				!$args['_post_tags_empty']
			) {
				$tax_term_query = "
				(
					($post_type_field != 'post') OR

					EXISTS (
						SELECT *
						FROM $wpdb->term_relationships as xt
						INNER JOIN $wpdb->term_taxonomy as tt ON ( xt.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'post_tag')
						WHERE
							xt.object_id = $post_id_field
					)
				)";
			}
			// ----------------------------------------------------

			if ( !empty($tax_term_query) ) {
				$term_query_parts[] = '(' . $tax_term_query . ')';
			}
		}

		if ( !empty($term_query_parts) ) {
			$term_query = 'AND (' . implode(' ' . strtoupper($args['_taxonomy_group_logic']) . ' ', $term_query_parts) . ') ';
		}

		return $term_query;
	}

	protected function buildCffQuery( string $post_id_field ): string {
		global $wpdb;
		$args  = $this->args;
		$parts = array();

		$post_meta_allow_missing = $args['post_meta_allow_missing'];
		foreach ( $args['post_meta_filter'] as $data ) {
			$operator                         = $data['operator'];
			$posted                           = $data['value'];
			$field                            = $data['key'];
			$logic_and_separate_custom_fields = $data['logic_and_separate_custom_fields'];
			$values_count                     = 1;

			// Is this a special case of date operator?
			if ( strpos($operator, 'datetime') === 0 ) {
				$date_add = $post_meta_allow_missing ? '' : "$wpdb->postmeta.meta_value<>'' AND ";

				switch ( $operator ) {
					case 'datetime =':
						$current_part = "($date_add $wpdb->postmeta.meta_value BETWEEN '$posted 00:00:00' AND '$posted 23:59:59')";
						break;
					case 'datetime <>':
						$current_part = "($date_add $wpdb->postmeta.meta_value NOT BETWEEN '$posted 00:00:00' AND '$posted 23:59:59')";
						break;
					/** @noinspection PhpDuplicateSwitchCaseBodyInspection */
					case 'datetime <':
						$current_part = "($date_add $wpdb->postmeta.meta_value < '$posted 00:00:00')";
						break;
					case 'datetime <=':
						$current_part = "($date_add $wpdb->postmeta.meta_value <= '$posted 23:59:59')";
						break;
					case 'datetime >':
						$current_part = "($date_add $wpdb->postmeta.meta_value > '$posted 23:59:59')";
						break;
					case 'datetime >=':
						$current_part = "($date_add $wpdb->postmeta.meta_value >= '$posted 00:00:00')";
						break;
					default:
						$current_part = "($date_add $wpdb->postmeta.meta_value < '$posted 00:00:00')";
						break;
				}
				// Is this a special case of timestamp?
			} elseif ( strpos($operator, 'timestamp') === 0 ) {
				$date_add = $post_meta_allow_missing ? '' : "$wpdb->postmeta.meta_value<>'' AND ";
				switch ( $operator ) {
					case 'timestamp =':
						$current_part = "($date_add $wpdb->postmeta.meta_value BETWEEN $posted AND " . ( $posted + 86399 ) . ')';
						break;
					case 'timestamp <>':
						$current_part = "($date_add $wpdb->postmeta.meta_value NOT BETWEEN $posted AND " . ( $posted + 86399 ) . ')';
						break;
					/** @noinspection PhpDuplicateSwitchCaseBodyInspection */
					case 'timestamp <':
						$current_part = "($date_add $wpdb->postmeta.meta_value < $posted)";
						break;
					case 'timestamp <=':
						$current_part = "($date_add $wpdb->postmeta.meta_value <= " . ( $posted + 86399 ) . ')';
						break;
					case 'timestamp >':
						$current_part = "($date_add $wpdb->postmeta.meta_value > " . ( $posted + 86399 ) . ')';
						break;
					case 'timestamp >=':
						$current_part = "($date_add $wpdb->postmeta.meta_value >= $posted)";
						break;
					default:
						$current_part = "($date_add $wpdb->postmeta.meta_value < $posted)";
						break;
				}
				// Check BETWEEN first -> range slider
			} elseif ( $operator === 'BETWEEN' ) {
				$current_part = "($wpdb->postmeta.meta_value BETWEEN " . $posted[0] . ' AND ' . $posted[1] . ' )';
				// If not BETWEEN but value is array, then drop-down or checkboxes
			} elseif ( $operator === 'ACF_POST_OBJECT' ) {
				if ( is_array($posted) ) {
					$values = array();
					foreach ( $posted as $v ) {
						$values[] = "(
							$wpdb->postmeta.meta_value LIKE '" . $v . "' OR 
							$wpdb->postmeta.meta_value LIKE '%\"" . $v . "\"%'
						)";
					}
					$logic        = $data['logic'] ?? 'OR';
					$current_part = '(' . implode(' ' . $logic . ' ', $values) . ')';
				} else {
					$current_part = "(
						$wpdb->postmeta.meta_value LIKE '" . $posted . "' OR 
						$wpdb->postmeta.meta_value LIKE '%\"" . $posted . "\"%'
					)";
				}
			} elseif ( is_array($posted) ) {
				// Is there a logic sent?
				$logic        = $data['logic'] ?? 'OR';
				$values_count = count($posted);
				if ( $logic_and_separate_custom_fields && $logic === 'AND' ) {
					$logic = 'OR';
				}
				$values = '';
				if ( $operator === 'IN' ) {
					$val = implode("','", $posted);
					if ( !empty($val) ) {
						$values .= "$wpdb->postmeta.meta_value $operator ('" . $val . "')";
					}
				} else {
					foreach ( $posted as $v ) {
						if ( $operator === 'ELIKE' || $operator === 'NOT ELIKE' ) {
							$_op = $operator === 'ELIKE' ? 'LIKE' : 'NOT LIKE';
							if ( $values !== '' ) {
								$values .= " $logic $wpdb->postmeta.meta_value $_op '" . $v . "'";
							} else {
								$values .= "$wpdb->postmeta.meta_value $_op '" . $v . "'";
							}
						} elseif ( $operator === 'NOT LIKE' || $operator === 'LIKE' ) {
							if ( $values !== '' ) {
								$values .= " $logic $wpdb->postmeta.meta_value $operator '%" . $v . "%'";
							} else {
								$values .= "$wpdb->postmeta.meta_value $operator '%" . $v . "%'";
							}
						} elseif ( $values !== '' ) {
								$values .= " $logic $wpdb->postmeta.meta_value $operator " . $v;
						} else {
							$values .= "$wpdb->postmeta.meta_value $operator " . $v;
						}
					}
				}

				$values       = $values === '' ? '0' : $values;
				$current_part = "($values)";
				// String operations
			} elseif ( $operator === 'NOT LIKE' || $operator === 'LIKE' ) {
				$current_part = "($wpdb->postmeta.meta_value $operator '%" . $posted . "%')";
			} elseif ( $operator === 'ELIKE' || $operator === 'NOT ELIKE' ) {
				$_op          = $operator === 'ELIKE' ? 'LIKE' : 'NOT LIKE';
				$current_part = "($wpdb->postmeta.meta_value $_op '$posted')";
			} else {
				// Numeric operations or problematic stuff left
				$current_part = "($wpdb->postmeta.meta_value $operator $posted  )";
			}

			// Finally, add the current part to the parts array
			if ( $current_part !== '' ) {
				$allowance = $data['allow_missing'] ?? $post_meta_allow_missing;
				$parts[]   = array( $field, $current_part, $allowance, $logic_and_separate_custom_fields, $values_count );
			}
		}

		// The correct count is the unique fields count
		// $meta_count = count( $unique_fields );

		$cf_select     = '(1)';
		$cf_select_arr = array();

		/**
		 * NOTE 1:
		 * With the previous NOT EXISTS(...) subquery solution the search would hang in some cases
		 * when checking if empty values are allowed. No idea why though...
		 * Eventually using separate sub-queries for each field is the best.
		 *
		 * NOTE 2:
		 * COUNT(post_id) is a MUST in the nested IF() statement !! Otherwise, the query will return empty rows, no idea why either...
		 */

		foreach ( $parts as $part ) {
			$field = $part[0];          // Field name
			$def   = $part[2] ? "(
				SELECT IF((meta_key IS NULL OR meta_value = ''), -1, COUNT(meta_id))
				FROM $wpdb->postmeta
				WHERE $wpdb->postmeta.post_id = $post_id_field AND $wpdb->postmeta.meta_key='$field'
				LIMIT 1
			  ) = -1
			 OR" : '';                  // Allowance
			$qry   = $part[1];            // Query condition
			if ( $part[3] ) {           // AND logic in for separated fields?
				$compare_to = intval($part[4]);
			} else {
				$compare_to = 1;
			}
			$cf_select_arr[] = "
			(
			  $def
			  (
				SELECT COUNT(meta_id) as mtc
				FROM $wpdb->postmeta
				WHERE $wpdb->postmeta.post_id = $post_id_field AND $wpdb->postmeta.meta_key='$field' AND $qry
				ORDER BY mtc
				LIMIT 1
			  )  >= $compare_to
			)";
		}
		if ( count($cf_select_arr) ) {
			// Connect them based on the meta logic
			$cf_select = '( ' . implode( $args['post_meta_filter_logic'], $cf_select_arr ) . ' )';
		}

		return $cf_select;
	}

	/**
	 * @param string $table_alias
	 * @param string $date_field
	 * @return array<string>
	 */
	protected function get_date_query_parts( string $table_alias = '', string $date_field = 'post_date' ): array {
		global $wpdb;
		$args = $this->args;

		if ( empty($table_alias) ) {
			$table_alias = $wpdb->posts;
		}

		$date_query_parts = array();

		foreach ( $args['post_date_filter'] as $date_filter ) {
			$date = $date_filter['date'] ?? $date_filter['year'] . '-' . sprintf('%02d', $date_filter['month']) . '-' . sprintf('%02d', $date_filter['day']);

			if ( $date_filter['interval'] === 'before' ) {
				$op                 = $date_filter['operator'] === 'exclude' ? '>' : '<=';
				$date_query_parts[] = "$table_alias.$date_field $op '" . $date . " 23:59:59'";
			} else {
				$op                 = $date_filter['operator'] === 'exclude' ? '<' : '>=';
				$date_query_parts[] = "$table_alias.$date_field $op '" . $date . " 00:00:00'";
			}
		}
		return $date_query_parts;
	}

	/**
	 * @param ResultObj[]          $posts
	 * @param array<string, mixed> $args
	 * @return ResultObj[]
	 */
	public static function orderBy( array &$posts, array $args = array() ): array {
		$args            = wp_parse_args(
			$args,
			array(
				'engine'                      => 'regular',
				'primary_ordering'            => 'relevance DESC',
				'primary_ordering_metatype'   => 'numeric',
				'secondary_ordering'          => 'relevance DESC',
				'secondary_ordering_metatype' => 'numeric',
			)
		);
		$primary_field   = explode(' ', $args['primary_ordering'])[0];
		$primary_field   = $primary_field === 'RAND()' ? 'primary_order' : $primary_field;
		$secondary_field = explode(' ', $args['secondary_ordering'])[0];

		for ( $i = 0; $i < 2; $i++ ) {
			$is_secondary = $i >0;

			if ( $is_secondary && $primary_field === $secondary_field ) {
				break;
			}

			if ( $i === 0 ) {
				$order          = $args['primary_ordering'];
				$order_metatype = $args['primary_ordering_metatype'];
			} else {
				$ii = 0;
				foreach ( $posts as $pp ) {
					$pp->primary_order = $ii;
					++$ii;
				}
				$order          = $args['secondary_ordering'];
				$order_metatype = $args['secondary_ordering_metatype'];
			}
			usort(
				$posts,
				function ( $a, $b ) use ( $order, $order_metatype, $is_secondary, $primary_field, $args ) {
					// Only compare objects with the same post type priority, so the groups don't get messed up
					if ( $a->p_type_priority === $b->p_type_priority ) {

						if ( $a->group_priority === $b->group_priority ) {

							if ( $a->priority === $b->priority ) {

								if ( !$is_secondary || $a->$primary_field === $b->$primary_field ) {
									switch ( $order ) {
										case 'average_rating DESC':
											// ceil() is very important here!! as this expects 1, 0, -1 but no values inbetween
											$diff = ceil( (float) $b->average_rating - (float) $a->average_rating);
											break;
										/** @noinspection PhpDuplicateSwitchCaseBodyInspection */
										case 'relevance DESC':
											$diff = $b->relevance - $a->relevance;
											break;
										case 'post_date DESC':
											$diff = strtotime($b->post_date) - strtotime($a->post_date);
											if ( $diff === 0 ) {
												$diff = $b->id - $a->id;
											}
											break;
										case 'post_date ASC':
											$diff = strtotime($a->post_date) - strtotime($b->post_date);
											if ( $diff === 0 ) {
												$diff = $a->id - $b->id;
											}
											break;
										case 'post_modified DESC':
											$diff = strtotime($b->post_modified) - strtotime($a->post_modified);
											if ( $diff === 0 ) {
												$diff = $b->id - $a->id;
											}
											break;
										case 'post_modified ASC':
											$diff = strtotime($a->post_modified) - strtotime($b->post_modified);
											if ( $diff === 0 ) {
												$diff = $a->id - $b->id;
											}
											break;
										case 'post_title DESC':
											$diff = MB::strcasecmp($b->title, $a->title);
											break;
										case 'post_title ASC':
											$diff = MB::strcasecmp($a->title, $b->title);
											break;
										case 'id DESC':
											$diff = $b->id - $a->id;
											break;
										case 'id ASC':
											$diff = $a->id - $b->id;
											break;
										case 'menu_order DESC':
											$diff = $b->menu_order - $a->menu_order;
											break;
										case 'menu_order ASC':
											$diff = $a->menu_order - $b->menu_order;
											break;
										case 'customfp DESC':
											if ( $order_metatype === 'numeric' ) {
												$diff = floatval($b->customfp) - floatval($a->customfp);
												$diff = $diff < 0 ? floor($diff) : ceil($diff);
											} else {
												$diff = MB::strcasecmp($b->customfp, $a->customfp);
											}
											break;
										case 'customfp ASC':
											if ( $order_metatype === 'numeric' ) {
												$diff = floatval($a->customfp) - floatval($b->customfp);
												$diff = $diff < 0 ? floor($diff) : ceil($diff);
											} else {
												$diff = MB::strcasecmp($a->customfp, $b->customfp);
											}
											break;
										case 'customfs DESC':
											if ( $order_metatype === 'numeric' ) {
												$diff = floatval($b->customfs) - floatval($a->customfs);
												$diff = $diff < 0 ? floor($diff) : ceil($diff);
											} else {
												$diff = MB::strcasecmp($b->customfs, $a->customfs);
											}
											break;
										case 'customfs ASC':
											if ( $order_metatype === 'numeric' ) {
												$diff = floatval($a->customfs) - floatval($b->customfs);
												$diff = $diff < 0 ? floor($diff) : ceil($diff);
											} else {
												$diff = MB::strcasecmp($a->customfs, $b->customfs);
											}
											break;
										case 'RAND()':
											$diff = $args['engine'] === 'regular' ? 0 : wp_rand( -1, 1 );
											break;
										default:
											$diff = $b->relevance - $a->relevance;
											break;
									}
									return $diff;
								}
								/**
								 * If the primary fields are not equal, then leave it as it is.
								 * The primary sorting already sorted the items out so return 0 - as equals
								 */
								if ( isset($a->primary_order, $b->primary_order) ) {
									return $a->primary_order - $b->primary_order;
								} else {
									return 0;
								}
							}

							return $b->priority - $a->priority;
						}

						return $b->group_priority - $a->group_priority;
					}

					// Post type priority is not equivalent, results should be treated as equals, no change
					return $a->p_type_priority - $b->p_type_priority;
				}
			);
		}

		return $posts;
	}

	protected function postProcess(): void {
		$pageposts = $this->results;
		$s         = $this->s;
		$_s        = $this->_s;
		$args      = $this->args;

		do_action('asp_start_post_processing');

		// No post-processing if the search data param is missing or explicitly set
		if ( empty($args['_sd']) || !$args['_post_process'] ) {
			$this->results = $pageposts;
			return;
		}

		$sd          = $args['_sd'];
		$com_options = wd_asp()->o['asp_compatibility'];

		// --------------------- For Image Parser -----------------------//
		// Do not select the content field, if it is not used at all
		$get_content = !(
			( $sd['showdescription'] ) ||
			( $sd['resultstype'] === 'isotopic' && $sd['i_ifnoimage'] === 'description' ) ||
			( $sd['resultstype'] === 'polaroid' && ( $sd['pifnoimage'] === 'descinstead' || $sd['pshowdesc'] ) )
		);

		// Do not select excerpt if it's not used at all
		$get_excerpt    = !(
			$sd['primary_titlefield'] ||
			$sd['secondary_titlefield'] ||
			$sd['primary_descriptionfield'] ||
			$sd['secondary_descriptionfield']
		);
		$image_settings = $sd['image_options'];

		if ( $args['_ajax_search'] || strpos(get_called_class(), 'SearchIndex') !== false ) {
			$start = 0;
			$end   = count($pageposts);
		} else {
			$start = $args['posts_per_page'] * ( $args['page'] - 1 );
			$end   = $start + $args['posts_per_page'];
			$end   = $end > count($pageposts) ? count($pageposts) : $end;
			if ( ( $end - $start ) <= 0 ) {
				$this->results = $pageposts;
				return;
			}
		}

		for ( $k = $start; $k <$end; $k++ ) {
			$r = &$pageposts[ $k ];
			// Attachment post-process uses this section, but it has a separate image parser, so skip that
			if ( $r->post_type === 'attachment' ) {
				continue;
			}

			$this->switchMultisiteBlog($r->blogid);

			if ( $image_settings['show_images'] ) {
				$image_args = array(
					'get_content'        => $get_content,
					'get_excerpt'        => $get_excerpt,
					'image_sources'      => array(
						$image_settings['image_source1'],
						$image_settings['image_source2'],
						$image_settings['image_source3'],
						$image_settings['image_source4'],
						$image_settings['image_source5'],
					),
					'image_source_size'  => $image_settings['image_source_featured'] === 'original' ? 'full' : $image_settings['image_source_featured'],
					'image_default'      => $image_settings['image_default'],
					'image_number'       => $sd['image_parser_image_number'],
					'image_custom_field' => $image_settings['image_custom_field'],
					'exclude_filenames'  => $sd['image_parser_exclude_filenames'],
					'image_width'        => $image_settings['image_width'],
					'image_height'       => $image_settings['image_height'],
					'apply_the_content'  => $image_settings['apply_content_filter'],
					'image_cropping'     => $image_settings['image_cropping'],
					'image_transparency' => $image_settings['image_transparency'],
					'image_bg_color'     => $image_settings['image_bg_color'],
				);
				$r->image   = Post::parseImage($r, $image_args);
				if ( $r->image === '' && $r->post_type === 'product_variation' && function_exists('wc_get_product') ) {
					$wc_prod_var_o = wc_get_product( $r->id ); // @phpstan-ignore-line
					$r->image      = Post::parseImage(wc_get_product( $wc_prod_var_o->get_parent_id() ), $image_args); // @phpstan-ignore-line
				}
			}
		}
		$this->restoreMultisiteBlog();
		/*---------------------------------------------------------------*/

		if ( $args['_ajax_search'] ) {
			// VC 4.6+ fix: Shortcodes are not loaded in ajax responses
			// class_exists() is mandatory, some PHP versions fail
			// @phpstan-ignore-next-line
			if ( class_exists('\\WPBMap') && method_exists('\\WPBMap', 'addAllMappedShortcodes') ) {
				/** @noinspection All */
				\WPBMap::addAllMappedShortcodes();
			}

			$this->deregisterShortcodes();
		}

		global $sitepress;

		/* Images, title, desc */
		for ( $k =$start; $k <$end; $k++ ) {
			$r = &$pageposts[ $k ];

			$this->switchMultisiteBlog($r->blogid);

			// ---- URL FIX for WooCommerce product variations
			$wc_prod_var_o = null; // Reset for each loop
			if ( $r->post_type === 'product_variation' && function_exists( 'wc_get_product' ) ) {
				$wc_prod_var_o = wc_get_product( $r->id );
				$r->link       = $wc_prod_var_o->get_permalink();
			} else {
				$r->link = get_permalink( $r->id );
			}
			// Filter it though WPML
			if ( defined('ICL_LANGUAGE_CODE') ) {
				if ( $args['_wpml_lang'] !== '' ) {
					/** @noinspection All */
					if ( ICL_LANGUAGE_CODE !== $args['_wpml_lang'] ) {
						$r->link = apply_filters('wpml_permalink', $r->link, $args['_wpml_lang'], true);
					}
				} elseif (
					is_object($sitepress) && method_exists($sitepress, 'get_default_language')
				) {
					$l = apply_filters( 'wpml_post_language_details', null, $r->id );
					if ( is_array($l) && isset($l['language_code']) ) {
						/** @noinspection All */
						$_lang = ICL_LANGUAGE_CODE;
						do_action( 'wpml_switch_language', $l['language_code'] );
						$r->link = get_permalink($r->id);
						$r->link = apply_filters('wpml_permalink', $r->link, $l['language_code'], true);
						do_action( 'wpml_switch_language', $_lang );
					}
				}
			}

			// If no image and defined, remove the result here, to perevent JS confusions
			if ( empty($r->image) && $sd['resultstype'] === 'isotopic' && $sd['i_ifnoimage'] === 'removeres' ) {
				unset($pageposts[ $k ]);
				continue;
			}
			/* Same for polaroid mode */
			if ( empty($r->image) && isset($sd['resultstype']) &&
				$sd['resultstype'] === 'polaroid' && $sd['pifnoimage'] === 'removeres' ) {
				unset($pageposts[ $k ]);
				continue;
			}

			$_t_keys = array( 'primary_titlefield', 'secondary_titlefield' );
			foreach ( $_t_keys as $tk ) {
				$sd[ $tk ] = $sd[ $tk ] . ''; // Convert for the switch statement
				if ( $sd[ $tk ] === '-1' ) {
					continue;
				}

				switch ( $sd[ $tk ] ) {
					case '0':
						$r->title = get_the_title($r->id);
						break;
					case '1':
						if ( MB::strlen( $r->excerpt ) >= 200 ) {
							$r->title = wd_substr_at_word( $r->excerpt, 200 );
						} else {
							$r->title = $r->excerpt;
						}
						break;
					case 'c__f':
						if ( $sd[ $tk . '_cf' ] === '' ) {
							$r->title = get_the_title( $r->id );
						} else {
							$field_val = Post::getCFValue($sd[ $tk . '_cf' ], $r, $com_options['use_acf_getfield'], $args);
							if ( $field_val !== '' ) {
								$r->title = wd_strip_tags_ws( $field_val, $sd['striptagsexclude'] );
							} else {
								$r->title = get_the_title($r->id);
							}
						}
						break;
					default:
						$r->title = get_the_title($r->id);
				}

				if ( $r->title !== '' ) {
					break;
				}
			}

			if ( !empty($sd['advtitlefield']) ) {
				$r->title = $this->advField(
					array(
						'main_field_slug'  => 'titlefield',
						'main_field_value' => $r->title,
						'r'                => $r,
						'field_pattern'    => stripslashes( $sd['advtitlefield'] ),
					),
					$com_options['use_acf_getfield']
				);
			}

			if ( ! isset( $sd['striptagsexclude'] ) ) {
				$sd['striptagsexclude'] = '<a><span>';
			}

			$_t_keys  = array( 'primary_descriptionfield', 'secondary_descriptionfield' );
			$_content = '';
			foreach ( $_t_keys as $tk ) {
				$sd[ $tk ] = $sd[ $tk ] . ''; // Convert for the switch statement
				if ( $sd[ $tk ] === '-1' ) {
					continue;
				}

				switch ( $sd[ $tk ] ) {
					case '1':
						if ( function_exists( 'qtranxf_use' ) && $args['_qtranslate_lang'] !== '' ) {
							/** @noinspection All */
							$r->excerpt = qtranxf_use($args['_qtranslate_lang'], $r->excerpt, false);
						}
						$_content = $r->excerpt;
						break;
					case '2':
						$_content = strip_tags( get_the_title( $r->id ), $sd['striptagsexclude'] );
						break;
					case 'c__f':
						if ( $sd[ $tk . '_cf' ] !== '' ) {
							$field_val = Post::getCFValue($sd[ $tk . '_cf' ], $r, $com_options['use_acf_getfield'], $args);
							if ( $field_val !== '' ) {
								$_content = $field_val;
							}
						}
						break;
					default: // including option '0', alias content
						if ( function_exists( 'qtranxf_use' ) && $args['_qtranslate_lang'] !== '' ) {
							/** @noinspection PhpUndefinedFunctionInspection */
							$r->content = qtranxf_use($args['_qtranslate_lang'], $r->content, false);
						}
						// For product variations, do something special
						if ( isset($wc_prod_var_o) ) {
							$r->content = $wc_prod_var_o->get_description();
							if ( $r->content === '' ) {
								$_pprod     = wc_get_product($wc_prod_var_o->get_parent_id()); // @phpstan-ignore-line
								$r->content = $_pprod->get_description();
							}
						}
						$_content = $r->content;
						break;
				}

				$_content = Post::dealWithShortcodes($_content, $sd['shortcode_op'] === 'remove');

				if ( $_content !== '' ) {
					break;
				}
			}

			$_content           = Html::stripTags($_content, $sd['striptagsexclude']);
			$description_length = $sd['descriptionlength'];

			// Get the words from around the search phrase, or just the description
			if ( $sd['description_context'] && count( $_s ) > 0 && $s !== '' ) {
				$_content = Str::getContext($_content, $description_length, $sd['description_context_depth'], $s, $_s);
			} elseif ( $_content !== '' && ( MB::strlen( $_content ) > $description_length ) ) {
				$_content = wd_substr_at_word($_content, $description_length);
			}

			if ( !empty($sd['advdescriptionfield']) ) {
				$cb = function ( $value, $field, $results, $field_args ) use( $description_length, $sd, $s, $_s ) {
					$value      = Post::dealWithShortcodes($value, $sd['shortcode_op'] === 'remove');
					$strip_tags = $field_args['strip_tags'] ?? 1;
					if ( strpos($field, 'html') === false && $strip_tags ) {
						$value = Html::stripTags($value, $sd['striptagsexclude']);
						if ( $sd['description_context'] && count( $_s ) > 0 && $s !== '' ) {
							$value = Str::getContext($value, $description_length, $sd['description_context_depth'], $s, $_s);
						} elseif ( $value !== '' && ( MB::strlen( $value ) > $description_length ) ) {
							$value = wd_substr_at_word($value, $description_length);
						}
					}
					return $value;
				};
				add_filter('asp_cpt_advanced_field_value', $cb, 10, 4);
				$_content = $this->advField(
					array(
						'main_field_slug'  => 'descriptionfield',
						'main_field_value' => $_content,
						'r'                => $r,
						'field_pattern'    => stripslashes( $sd['advdescriptionfield'] ),
					),
					$com_options['use_acf_getfield']
				);
				remove_filter('asp_cpt_advanced_field_value', $cb);
			}

			$r->content = wd_closetags( $_content );

			// --------------------------------- DATE -----------------------------------
			if ( $sd['showdate'] ) {
				$post_time = strtotime($r->date);

				if ( $sd['custom_date'] ) {
					$date_format = w_isset_def($sd['custom_date_format'], 'Y-m-d H:i:s');
				} else {
					$date_format = get_option('date_format', 'Y-m-d') . ' ' . get_option('time_format', 'H:i:s');
				}

				$r->date = @date_i18n($date_format, $post_time); // @phpcs:ignore
			}
			// --------------------------------------------------------------------------
		}

		$this->restoreMultisiteBlog();
		$this->results = $pageposts;
	}


	/**
	 * Generates the final field, based on the advanced field pattern
	 *
	 * @param array<string, mixed> $f_args Field related arguments
	 * @param bool                 $use_acf If true, uses ACF get_field() function to get the meta
	 * @param bool                 $empty_on_missing If true, returns an empty string if any of the fields is empty.
	 * @param integer              $depth Recursion depth
	 *
	 * @return string Final post title
	 */
	protected function advField( array $f_args, bool $use_acf = false, bool $empty_on_missing = false, int $depth = 0 ): string {
		$args = &$this->args;

		$specials = array(
			'__id',
			'__title',
			'__content',
			'__link',
			'__url',
			'__image',
			'__date',
			'__author',
			'__post_type',
		);

		$f_args  = wp_parse_args(
			$f_args,
			array(
				'main_field_slug'  => 'titlefield',  // The 'slug', aka the original field name
				'main_field_value' => '',            // The default field value
				'r'                => null,                        // Result object
				'field_pattern'    => '{titlefield}',   // The field pattern
			)
		);
		$_f_args = $f_args;

		if ( $f_args['field_pattern'] === '' ) {
			return $f_args['field_value'];
		}
		$field_pattern = $f_args['field_pattern']; // Let's not make changes to arguments, shall we.

		// Handle shortcode patterns
		if ( $depth === 0 && strpos($field_pattern, '[[') !== false ) {
			$do_shortcodes = true;
			$field_pattern = str_replace(
				array( '[[', ']]' ),
				array( '____shortcode_start____', '____shortcode_end____' ),
				$field_pattern
			);
		} else {
			$do_shortcodes = false;
		}

		// Find conditional patterns, like [prefix {field} suffix}
		preg_match_all( '/(\[.*?\])/', $field_pattern, $matches );
		if ( isset( $matches[0] ) && isset( $matches[1] ) && is_array( $matches[1] ) ) {
			foreach ( $matches[1] as $fieldset ) {
				// Pass on each section to this function again, the code will never get here
				$_f_args['field_pattern'] = str_replace(array( '[', ']' ), '', $fieldset);
				$processed_fieldset       = $this->advField(
					$_f_args,
					$use_acf,
					true,
					$depth + 1
				);
				// Replace the original with the processed version, first occurrence, in case of duplicates
				$field_pattern = Str::replaceFirst($fieldset, $processed_fieldset, $field_pattern);
			}
		}

		preg_match_all( '/{(.*?)}/', $field_pattern, $matches );
		if ( isset( $matches[0] ) && isset( $matches[1] ) && is_array( $matches[1] ) ) {
			foreach ( $matches[1] as $complete_field ) {
				$field_args = shortcode_parse_atts($complete_field);
				if ( is_array($field_args) && isset($field_args[0]) ) {
					$field = array_shift($field_args);
				} else {
					continue;
				}
				if ( $field === $f_args['main_field_slug'] ) {
					$val = $f_args['main_field_value'];
					// value, field name, post object, field arguments
				} else {
					if ( in_array($field, $specials, true) ) {
						$r   = $f_args['r'];
						$val = '';
						switch ( $field ) {
							case '__id':
								$val = $r->id ?? '';
								break;
							case '__title':
								$val = $r->title ?? '';
								break;
							case '__content':
								$val = $r->content ?? '';
								break;
							case '__post_type':
								if ( isset($r->post_type) ) {
									$post_type_obj = get_post_type_object( $r->post_type );
									$val           = !is_null($post_type_obj) ? $post_type_obj->labels->singular_name : '';
								}
								break;
							case '__link':
							case '__url':
								$val = $r->link ?? '';
								break;
							case '__image':
								$val = $r->image ?? '';
								break;
							case '__date':
								if ( isset($r->date) ) {
									if ( isset($field_args['date_format']) ) {
										$val = date_i18n($field_args['date_format'], strtotime($r->date));
									} else {
										$val = $r->date;
									}
								}
								break;
							case '__author':
								$val = isset($r->author) && $r->author !== '' ?
									$r->author :
									get_the_author_meta(
										'display_name',
										intval(get_post_field('post_author', $r->id))
									);
								break;
						}
					} elseif ( strpos($field, '_taxonomy_') === 0 || strpos($field, '__tax_') === 0 ) {
						$taxonomy = str_replace(array( '_taxonomy_', '__tax_' ), '', $field);
						if ( $taxonomy === '' ) {
							continue;
						}
						$count        = isset($field_args['count']) ? $field_args['count'] + 0 : 5;
						$count        = $count === 0 ? 5 : $count;
						$separator    = $field_args['separator'] ?? ', ';
						$orderby      = $field_args['orderby'] ?? 'name';
						$order        = $field_args['order'] ?? 'ASC';
						$exclude      = isset($field_args['exclude']) ? '1, ' . $field_args['exclude'] : '1';
						$hide_empty   = !isset($field_args['hide_empty']) || $field_args['hide_empty'];
						$hierarchical = !isset($field_args['hierarchical']) || $field_args['hierarchical'];
						$childless    = isset($field_args['childless']) && $field_args['childless'];
						$val          = Taxonomy::getTermsList(
							$taxonomy,
							$count,
							$separator,
							array(
								'orderby'      => $orderby,
								'order'        => $order,
								'object_ids'   => $f_args['r']->id,
								'exclude'      => $exclude,
								'hide_empty'   => $hide_empty,
								'hierarchical' => $hierarchical,
								'childless'    => $childless,
							)
						);
					} elseif ( strpos($field, '_pods_') === 0 || strpos($field, '_pod_') === 0 ) {
						// PODs field
						$val = Post::getPODsValue($field, $f_args['r']);
					} elseif ( strpos($field, '__um_') === 0 ) {
						// User Meta Field
						$um_field = str_replace('__um_', '', $field);
						$author   = get_post_field( 'post_author', $f_args['r']->id );
						$val      = User::getCFValue($um_field, intval($author), $use_acf);
					} else {
						// Probably a custom field?
						$val = Post::getCFValue($field, $f_args['r'], $use_acf, $args, $field_args);
					}
					// For the recursive call to break, if any of the fields is empty
					if ( $empty_on_missing && trim($val) === '' ) {
						return '';
					}
					$val = Str::fixSSLURLs($val);

					// value, field name, post object, field arguments
				}
				if ( isset($field_args['maxlength']) ) {
					$val = wd_substr_at_word($val, $field_args['maxlength']);
				}
				if ( isset($field_args['type']) && $val !== '' ) {
					if ( $field_args['type'] === 'number' ) {
						$decimals           = $field_args['decimals'] ?? '0';
						$thousand_separator = $field_args['thousand_separator'] ?? ',';
						$decimal_separator  = $field_args['decimal_separator'] ?? '.';
						$val                = number_format(floatval($val), $decimals, $decimal_separator, $thousand_separator);
					}
				}
				
				$val           = apply_filters('asp_cpt_advanced_field_value', $val, $field, $f_args['r'], $f_args);
				$field_pattern = str_replace( '{' . $complete_field . '}', $val, $field_pattern );
			}
		}

		// On depth=0 and if tags were found $do_shortcodes is true
		if ( $do_shortcodes ) {
			$field_pattern = str_replace(
				array( '____shortcode_start____', '____shortcode_end____' ),
				array( '[', ']' ),
				$field_pattern
			);
			$field_pattern = do_shortcode($field_pattern);
		}

		return $field_pattern;
	}

	protected function switchMultisiteBlog( int $blog_id ): void {
		if ( $this->args['_switch_on_preprocess'] && is_multisite() && get_current_blog_id() !== $blog_id ) {
			switch_to_blog($blog_id);
			++$this->multisite_switch_count;
		}
	}

	protected function restoreMultisiteBlog(): void {
		while ( $this->multisite_switch_count > 0 ) {
			restore_current_blog();
			--$this->multisite_switch_count;
		}
	}

	protected function deregisterShortcodes(): void {
		// Remove shortcodes with contents
		$ignore_shortcodes = array(
			'wpdreams_ajaxsearchpro',
			'wd_asp',
			'embed',
		);
		foreach ( $ignore_shortcodes as $shortcode ) {
			remove_shortcode( $shortcode );
			add_shortcode( $shortcode, array( $this, 'returnEmptyString' ) );
		}
	}

	/**
	 * An empty function to override individual shortcodes, this must be public
	 *
	 * @return string
	 */
	public function returnEmptyString(): string {
		return '';
	}
}
