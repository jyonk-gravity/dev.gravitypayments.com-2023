<?php
namespace WPDRMS\ASP\Search;

use WPDRMS\ASP\Utils\Html;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Post;
use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') || die("You can't access this file directly.");

class SearchComments extends SearchPostTypes {

	/** @noinspection DuplicatedCode */
	protected function doSearch(): void {
		global $wpdb;

		$args = &$this->args;

		$sd = $args['_sd'] ?? array();

		$s  = $this->s;
		$_s = $this->_s;

		if ( $args['_limit'] > 0 ) {
			$limit = $args['_limit'];
		} elseif ( $args['_ajax_search'] ) {
				$limit = $args['comments_limit'];
		} else {
			$limit = $args['comments_limit_override'];
		}
		$query_limit = $limit * 3;

		if ( $limit <= 0 ) {
			return;
		}

		// Prefixes and suffixes
		$pre_field = '';
		$suf_field = '';
		$pre_like  = '';
		$suf_like  = '';
		$wcl       = '%'; // Wildcard Left
		$wcr       = '%'; // Wildcard right
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

		$kw_logic = $args['keyword_logic'];

		// ------------------------ Categories/taxonomies ----------------------
		$term_query = $this->buildTermQuery( $wpdb->comments . '.comment_post_ID', 'comment_post_type' );
		// ---------------------------------------------------------------------

		/*------------- Custom Fields with Custom selectors -------------*/
		$cf_select = $this->buildCffQuery( $wpdb->comments . '.comment_post_ID' );
		/*---------------------------------------------------------------*/

		/*----------------------- Date filtering ------------------------*/
		$date_query       = '';
		$date_query_parts = $this->get_date_query_parts( $wpdb->comments, 'comment_date' );

		if ( count($date_query_parts) > 0 ) {
			$date_query = ' AND (' . implode(' AND ', $date_query_parts) . ') ';
		}
		/*---------------------------------------------------------------*/

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
			/*---------------------- Content query --------------------------*/
			$parts           = array();
			$relevance_parts = array();
			$is_exact        = $args['_exact_matches'] || ( count($words) > 1 && $k === 0 && ( $kw_logic === 'or' || $kw_logic === 'and' ) );

			if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
				$parts[] = '( ' . $pre_field . $wpdb->comments . '.comment_content' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
			} else {
				$parts[] = '
			   (' . $pre_field . $wpdb->comments . '.comment_content' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
			OR  " . $pre_field . $wpdb->comments . '.comment_content' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
			OR  " . $pre_field . $wpdb->comments . '.comment_content' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
			OR  " . $pre_field . $wpdb->comments . '.comment_content' . $suf_field . " = '" . $word . "')";
			}

			if ( !$relevance_added && isset($_s[0]) ) {
				$relevance_parts[] = '(case when
		(' . $pre_field . $wpdb->comments . '.comment_content' . $suf_field . " LIKE '%" . $_s[0] . "%')
		 then " . w_isset_def($sd['contentweight'], 10) . ' else 0 end)';
			}
			/*---------------------------------------------------------------*/

			$this->parts[]   = array( $parts, $relevance_parts );
			$relevance_added = true;
		}

		/*------------------------ Exclude ids --------------------------*/
		if ( !empty($args['post_not_in']) ) {
			$exclude_posts = "AND ($wpdb->comments.comment_post_ID NOT IN (" . ( is_array($args['post_not_in']) ? implode(',', $args['post_not_in']) : $args['post_not_in'] ) . '))';
		} else {
			$exclude_posts = '';
		}
		if ( !empty($args['post_not_in2']) ) {
			$exclude_posts .= "AND ($wpdb->comments.comment_post_ID NOT IN (" . implode(',', $args['post_not_in2']) . '))';
		}
		/*---------------------------------------------------------------*/

		/*------------------------ Include ids --------------------------*/
		if ( !empty($args['post_in']) ) {
			$include_posts = "AND ($wpdb->comments.comment_post_ID IN (" . ( is_array($args['post_in']) ? implode(',', $args['post_in']) : $args['post_in'] ) . '))';
		} else {
			$include_posts = '';
		}
		/*---------------------------------------------------------------*/

		/*----------------------- Exclude USER id -----------------------*/
		$user_query = '';
		if ( isset($args['post_user_filter']['include']) ) {
			if ( !in_array(-1, $args['post_user_filter']['include']) ) { // @phpcs:ignore
				$user_query = "AND $wpdb->comments.user_id IN (" . implode(', ', $args['post_user_filter']['include']) . ')
			';
			}
		}
		if ( isset($args['post_user_filter']['exclude']) ) {
			if ( !in_array(-1, $args['post_user_filter']['exclude']) ) { // @phpcs:ignore
				$user_query = "AND $wpdb->comments.user_id NOT IN (" . implode(', ', $args['post_user_filter']['exclude']) . ') ';
			} else {
				return;
			}
		}
		/*---------------------------------------------------------------*/

		if (
			strpos($args['post_primary_order'], 'customfp') !== false ||
			strpos($args['post_primary_order'], 'menu_order') !== false
		) {
			$orderby_primary = 'relevance DESC';
		} else {
			$orderby_primary = str_replace('post_', '', $args['post_primary_order']);
		}

		if (
			strpos($args['post_secondary_order'], 'customfs') !== false ||
			strpos($args['post_secondary_order'], 'menu_order') !== false
		) {
			$orderby_secondary = 'date DESC';
		} else {
			$orderby_secondary = str_replace('post_', '', $args['post_secondary_order']);
		}

		$this->query = "
		SELECT 
		$wpdb->comments.comment_ID as id,
		$this->c_blogid as `blogid`,
		$wpdb->comments.comment_post_ID as post_id,
		'post' as comment_post_type,
		$wpdb->comments.user_id as user_id,
		$wpdb->comments.comment_content as `title`,
		$wpdb->comments.comment_content as `content`,
		'comment' as `content_type`,
		'comments' as `g_content_type`,
		$wpdb->comments.comment_date as `date`,
		$wpdb->comments.user_id as user_id,
		{relevance_query} as `relevance`
		FROM $wpdb->comments
		WHERE
	  ($wpdb->comments.comment_approved=1)
	  $term_query
	  AND $cf_select
	  $date_query
	  $user_query
	  AND {like_query}
	  $exclude_posts
	  $include_posts
		ORDER BY $orderby_primary, $orderby_secondary
		LIMIT " . $query_limit;

		$querystr            = $this->buildQuery( $this->parts );
		$querystr            = apply_filters('asp_query_comments', $querystr, $args, $args['_id'], $args['_ajax_search']);
		$comment_results     = $wpdb->get_results($querystr); // @phpcs:ignore
		$this->results_count = count($comment_results);

		if ( !$args['_ajax_search'] && $this->results_count > $limit ) {
			$this->results_count = $limit;
		}

		$comment_results    = array_slice($comment_results, $args['_call_num'] * $limit, $limit);
		$this->results      = &$comment_results;
		$this->return_count = count($this->results);
	}

	/** @noinspection DuplicatedCode */
	protected function postProcess(): void {
		$r    = &$this->results;
		$args = $this->args;
		$s    = $this->s;
		$_s   = $this->_s;
		$sd   = $args['_sd'];

		foreach ( $r as $k => $v ) {
			$v->link   = get_comment_link($v->id);
			$v->author = get_comment_author($v->id);

			if ( MB::strlen($v->content) > 40 ) {
				$v->title = wd_substr_at_word($v->content, 40);
			} else {
				$v->title = $v->content;
			}

			if ( $sd['showdescription'] ) {
				$_content = Post::dealWithShortcodes($v->content, $sd['shortcode_op'] === 'remove');
				$_content = Html::stripTags($_content, $sd['striptagsexclude']);
				// Get the words from around the search phrase, or just the description
				if ( $sd['description_context'] && count( $_s ) > 0 && $s !== '' ) {
					$_content = Str::getContext($_content, $sd['descriptionlength'], $sd['description_context_depth'], $s, $_s);
				} elseif ( $_content !== '' && ( MB::strlen( $_content ) > $sd['descriptionlength'] ) ) {
					$_content = wd_substr_at_word($_content, $sd['descriptionlength']);
				}
				$v->content = Str::fixSSLURLs( wd_closetags($_content) );
			} else {
				$v->content = '';
			}

			/* Remove the results in polaroid mode */
			if ( $args['_ajax_search'] && empty($v->image) && isset($sd['resultstype']) &&
				$sd['resultstype'] === 'polaroid' && $sd['pifnoimage'] === 'removeres' ) {
				unset($this->results[ $k ]);
				continue;
			}

			// --------------------------------- DATE -----------------------------------
			if ( isset($sd['showdate']) && $sd['showdate'] ) {
				$post_time = strtotime($v->date);

				if ( $sd['custom_date'] ) {
					$date_format = w_isset_def($sd['custom_date_format'], 'Y-m-d H:i:s');
				} else {
					$date_format = get_option('date_format', 'Y-m-d') . ' ' . get_option('time_format', 'H:i:s');
				}

				$v->date = @date_i18n($date_format, $post_time); // @phpcs:ignore
			}
			// --------------------------------------------------------------------------
		}
	}
}
