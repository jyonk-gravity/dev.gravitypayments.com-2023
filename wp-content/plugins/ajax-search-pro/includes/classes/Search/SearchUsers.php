<?php
/**
 * @noinspection DuplicatedCode
 * @noinspection RegExpRedundantEscape
 * @noinspection RegExp
 */

namespace WPDRMS\ASP\Search;

use WPDRMS\ASP\Utils\Html;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Post;
use WPDRMS\ASP\Utils\Str;
use WPDRMS\ASP\Utils\User;

defined('ABSPATH') || die("You can't access this file directly.");

class SearchUsers extends SearchPostTypes {
	protected function doSearch(): void {
		global $wpdb;

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

		// Keyword logics
		$kw_logic = $args['keyword_logic'];

		$s  = $this->s; // full keyword
		$_s = $this->_s; // array of keywords

		if ( $args['_limit'] > 0 ) {
			$limit = $args['_limit'];
		} elseif ( $args['_ajax_search'] ) {
			$limit = $args['users_limit'];
		} else {
			$limit = $args['users_limit_override'];
		}
		if ( $limit <= 0 ) {
			return;
		}
		$query_limit = $limit * $this->remaining_limit_mod;

		$bp_cf_select = '';

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
			$is_exact        = $args['_exact_matches'] || ( count($words) > 1 && $k === 0 && ( $kw_logic === 'or' || $kw_logic === 'and' ) );

			/*---------------------- Login Name query ------------------------*/
			if ( $args['user_login_search'] ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
					$parts[] = '( ' . $pre_field . $wpdb->users . '.user_login' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = '
					   (' . $pre_field . $wpdb->users . '.user_login' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . '.user_login' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . '.user_login' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->users . '.user_login' . $suf_field . " = '" . $word . "')";
				}

				if ( !$relevance_added ) {
					if ( isset($_s[0]) ) {
						$relevance_parts[] = '(case when
						(' . $pre_field . $wpdb->users . '.user_login' . $suf_field . " LIKE '%" . $_s[0] . "%')
						 then " . w_isset_def($sd['titleweight'], 10) . ' else 0 end)';
					}
					$relevance_parts[] = '(case when
					(' . $pre_field . $wpdb->users . '.user_login' . $suf_field . " LIKE '%$s%')
					 then " . w_isset_def($sd['titleweight'], 10) . ' else 0 end)';
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Display Name query ------------------------*/
			if ( $args['user_display_name_search'] ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
					$parts[] = '( ' . $pre_field . $wpdb->users . '.display_name' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = '
					   (' . $pre_field . $wpdb->users . '.display_name' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . '.display_name' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . '.display_name' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->users . '.display_name' . $suf_field . " = '" . $word . "')";
				}

				if ( !$relevance_added ) {
					if ( isset($_s[0]) ) {
						$relevance_parts[] = '(case when
						(' . $pre_field . $wpdb->users . '.display_name' . $suf_field . " LIKE '%" . $_s[0] . "%')
						 then " . w_isset_def($sd['titleweight'], 10) . ' else 0 end)';
					}
					$relevance_parts[] = '(case when
					(' . $pre_field . $wpdb->users . '.display_name' . $suf_field . " LIKE '$s%')
					 then " . ( w_isset_def($sd['titleweight'], 10) * 2 ) . ' else 0 end)';
					$relevance_parts[] = '(case when
					(' . $pre_field . $wpdb->users . '.display_name' . $suf_field . " LIKE '%$s%')
					 then " . w_isset_def($sd['titleweight'], 10) . ' else 0 end)';
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- First Name query -----------------------*/
			if ( $args['user_first_name_search'] ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
					$parts[] = "( $wpdb->usermeta.meta_key = 'first_name' AND ( " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like ) )";
				} else {
					$parts[] = "( $wpdb->usermeta.meta_key = 'first_name' AND
					   (" . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " = '" . $word . "') )";
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Last Name query ------------------------*/
			if ( $args['user_last_name_search'] ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
					$parts[] = "( $wpdb->usermeta.meta_key = 'last_name' AND ( " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like ) )";
				} else {
					$parts[] = "( $wpdb->usermeta.meta_key = 'last_name' AND
					   (" . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " = '" . $word . "') )";
				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Email query ------------------------*/
			if ( $args['user_email_search'] ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
					$parts[] = '( ' . $pre_field . $wpdb->users . '.user_email' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
				} else {
					$parts[] = '
					   (' . $pre_field . $wpdb->users . '.user_email' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . '.user_email' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->users . '.user_email' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->users . '.user_email' . $suf_field . " = '" . $word . "')";

				}
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Biography query ------------------------*/
			if ( $args['user_bio_search'] ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
					$parts[] = "( $wpdb->usermeta.meta_key = 'description' AND ( " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like ) )";
				} else {
					$parts[] = "( $wpdb->usermeta.meta_key = 'description' AND 
					   (" . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " = '" . $word . "') )";
				}
			}
			/*---------------------------------------------------------------*/

			/*-------------------- Other selected meta ----------------------*/     
			$args['user_search_meta_fields'] = !is_array($args['user_search_meta_fields']) ? array( $args['user_search_meta_fields'] ) : $args['user_search_meta_fields'];
			if ( count( $args['user_search_meta_fields']) > 0 ) {
				$cf_parts = array();
				foreach ( $args['user_search_meta_fields'] as $cfield ) {
					$key_part = "$wpdb->usermeta.meta_key='$cfield' AND ";

					if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
						$cf_parts[] = "( $key_part " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
					} else {
						$cf_parts[] = "( $key_part 
						(" . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
						OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
						OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
						OR  " . $pre_field . $wpdb->usermeta . '.meta_value' . $suf_field . " = '" . $word . "') )";
					}               
				}
				$parts[] = "( EXISTS (SELECT 1 FROM $wpdb->usermeta WHERE (" . implode(' OR ', $cf_parts) . ") AND $wpdb->users.ID = $wpdb->usermeta.user_id) )";
			}
			/*---------------------------------------------------------------*/

			/*------------------ BP Xprofile field meta ---------------------*/
			$args['user_search_bp_fields'] = !is_array($args['user_search_bp_fields']) ? array( $args['user_search_bp_fields'] ) : $args['user_search_bp_fields'];
			$bp_meta_table                 = $wpdb->base_prefix . 'bp_xprofile_data';

			if ( count($args['user_search_bp_fields']) > 0 && $wpdb->get_var("SHOW TABLES LIKE '$bp_meta_table'") === $bp_meta_table ) { // @phpcs:ignore
				$bp_cf_parts = array();
				foreach ( $args['user_search_bp_fields'] as $field_id ) {
					$key_part = "$bp_meta_table.field_id = '" . $field_id . "' AND ";
					if ( $kw_logic === 'or' || $kw_logic === 'and' || $is_exact ) {
						$bp_cf_parts[] = "( $key_part " . $pre_field . $bp_meta_table . '.value' . $suf_field . " LIKE $pre_like'$wcl" . $word . "$wcr'$suf_like )";
					} else {
						$bp_cf_parts[] = "( $key_part 
						(" . $pre_field . $bp_meta_table . '.value' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
						OR  " . $pre_field . $bp_meta_table . '.value' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
						OR  " . $pre_field . $bp_meta_table . '.value' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
						OR  " . $pre_field . $bp_meta_table . '.value' . $suf_field . " = '" . $word . "') )";
					}               
				}
				$parts[] = "( EXISTS (SELECT 1 FROM $bp_meta_table WHERE (" . implode(' OR ', $bp_cf_parts) . ") AND $bp_meta_table.user_id = $wpdb->users.ID ) )";
			}

			$this->parts[]   = array( $parts, $relevance_parts );
			$relevance_added = true;
		}

		/*------------------ BP Xprofile field meta ---------------------*/
		$args['user_search_bp_fields'] = !is_array($args['user_search_bp_fields']) ? array( $args['user_search_bp_fields'] ) : $args['user_search_bp_fields'];
		$bp_meta_table                 = $wpdb->base_prefix . 'bp_xprofile_data';
		$bp_cf_parts                   = array();

		if ( count($args['user_search_bp_fields']) > 0 && $wpdb->get_var("SHOW TABLES LIKE '$bp_meta_table'") === $bp_meta_table ) { // @phpcs:ignore
			foreach ( $args['user_search_bp_fields'] as $field_id ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' ) {
					$op = strtoupper($kw_logic);
					if ( count($_s) > 0 ) {
						$_like = implode("%'$suf_like " . $op . ' ' . $pre_field . $bp_meta_table . '.value' . $suf_field . " LIKE $pre_like'%", $words);
					} else {
						$_like = $s;
					}
					$bp_cf_parts[] = "( $bp_meta_table.field_id = " . $field_id . ' AND ( ' . $pre_field . $bp_meta_table . '.value' . $suf_field . " LIKE $pre_like'$wcl" . $_like . "$wcr'$suf_like ) )";
				} else {
					$_like = array();
					$op    = $kw_logic === 'andex' ? 'AND' : 'OR';
					foreach ( $words as $word ) {
						$_like[] = '
					   (' . $pre_field . $bp_meta_table . '.value' . $suf_field . " LIKE $pre_like'% " . $word . " %'$suf_like
					OR  " . $pre_field . $bp_meta_table . '.value' . $suf_field . " LIKE $pre_like'" . $word . " %'$suf_like
					OR  " . $pre_field . $bp_meta_table . '.value' . $suf_field . " LIKE $pre_like'% " . $word . "'$suf_like
					OR  " . $pre_field . $bp_meta_table . '.value' . $suf_field . " = '" . $word . "')";
					}
					$bp_cf_parts[] = "( $bp_meta_table.field_id = " . $field_id . ' AND (' . implode(' ' . $op . ' ', $_like) . ') )';
				}
			}

			$parts[]       = "( EXISTS (SELECT 1 FROM $bp_meta_table WHERE (" . implode(' OR ', $bp_cf_parts) . ") AND $bp_meta_table.user_id = $wpdb->users.ID ) )";
			$this->parts[] = array( $parts, array() );
		}
		/*---------------------------------------------------------------*/

		/*------------------------ Exclude Roles ------------------------*/
		$roles_query                       = '';
		$args['user_search_exclude_roles'] = !is_array($args['user_search_exclude_roles']) ? array( $args['user_search_exclude_roles'] ) : $args['user_search_exclude_roles'];
		if ( count($args['user_search_exclude_roles']) > 0 ) {
			$role_parts = array();
			foreach ( $args['user_search_exclude_roles'] as $role ) {
				$role_parts[] = $wpdb->usermeta . '.meta_value LIKE \'%"' . $role . '"%\'';
			}
			// Capabilities meta field is prefixed with the DB prefix
			$roles_query = "AND $wpdb->users.ID NOT IN (
				SELECT DISTINCT($wpdb->usermeta.user_id)
				FROM $wpdb->usermeta
				WHERE $wpdb->usermeta.meta_key='" . $wpdb->base_prefix . "capabilities' AND (" . implode(' OR ', $role_parts) . ')
			)';
		}
		/*---------------------------------------------------------------*/

		/*------------- Custom Fields with Custom selectors -------------*/
		$cf_select = $this->buildCffQuery( $wpdb->users . '.ID' );
		/*---------------------------------------------------------------*/

		/*------------- Exclude and Include Users by ID -----------------*/
		$exclude_query = '';
		if ( count($args['user_search_exclude_ids']) > 0 ) {
			$exclude_query .= " AND $wpdb->users.ID NOT IN(" . implode(',', $args['user_search_exclude_ids']) . ') ';
		}
		$include_query = '';
		if ( count($args['user_search_include_ids']) ) {
			$include_query .= " AND $wpdb->users.ID IN(" . implode(',', $args['user_search_include_ids']) . ') ';
		}
		/*---------------------------------------------------------------*/

		/*----------------------- Title Field ---------------------------*/
		switch ( w_isset_def($sd['user_search_title_field'], 'display_name') ) {
			case 'login':
				$uname_select = "$wpdb->users.user_login";
				break;
			default:
				$uname_select = "$wpdb->users.display_name";
				break;
		}
		/*---------------------------------------------------------------*/

		/*-------------- Additional Query parts by Filters --------------*/
		/**
		 * Use these filters to add additional parts to the select, join or where
		 * parts of the search query.
		 */
		$add_select = apply_filters('asp_user_query_add_select', '', $args, $s, $_s);
		$add_join   = apply_filters('asp_user_query_add_join', '', $args, $s, $_s);
		$add_where  = apply_filters('asp_user_query_add_where', '', $args, $s, $_s);
		/*---------------------------------------------------------------*/

		/*---------------- Primary custom field ordering ----------------*/
		$custom_field_selectp = '1 ';
		if (
			strpos($args['user_primary_order'], 'customfp') !== false &&
			$args['_user_primary_order_metakey'] !== false
		) {
			// @phpstan-ignore-next-line
			$custom_field_selectp = "(SELECT IF(meta_value IS NULL, 0, meta_value)
			FROM $wpdb->usermeta
			WHERE
				$wpdb->usermeta.meta_key='" . esc_sql($args['_user_primary_order_metakey']) . "' AND
				$wpdb->usermeta.user_id=$wpdb->users.ID
			LIMIT 1
			) ";
		}
		/*---------------------------------------------------------------*/

		/*--------------- Secondary custom field ordering ---------------*/
		$custom_field_selects = '1 ';
		if (
			strpos($args['user_secondary_order'], 'customfs') !== false &&
			$args['_user_secondary_order_metakey'] !== false
		) {
			// @phpstan-ignore-next-line
			$custom_field_selects = "(SELECT IF(meta_value IS NULL, 0, meta_value)
			FROM $wpdb->usermeta
			WHERE
				$wpdb->usermeta.meta_key='" . esc_sql($args['_user_secondary_order_metakey']) . "' AND
				$wpdb->usermeta.user_id=$wpdb->users.ID
			LIMIT 1
			) ";
		}
		/*---------------------------------------------------------------*/

		$orderby_primary   = $args['user_primary_order'];
		$orderby_secondary = $args['user_secondary_order'];
		if ( $args['user_primary_order_metatype'] === 'numeric' ) {
			$orderby_primary = str_replace('customfp', 'CAST(customfp as SIGNED)', $orderby_primary);
		}

		if ( $args['user_secondary_order_metatype'] === 'numeric' ) {
			$orderby_secondary = str_replace('customfs', 'CAST(customfs as SIGNED)', $orderby_secondary);
		}

		$this->query = "
		SELECT
			$add_select
			{args_fields}
			$wpdb->users.ID as id,
			$this->c_blogid as `blogid`,
			$uname_select as `title`,
			$wpdb->users.user_registered as `date`,
			'' as `author`,
			'' as `content`,
			'user' as `content_type`,
			'users' as `g_content_type`,
			{relevance_query} as `relevance`,
			$wpdb->users.user_login as `user_login`,
			$wpdb->users.user_nicename as `user_nicename`,
			$wpdb->users.display_name as `user_display_name`,
			$custom_field_selectp as `customfp`,
			$custom_field_selects as `customfs`
		FROM
			$wpdb->users
			LEFT JOIN $wpdb->usermeta ON $wpdb->usermeta.user_id = $wpdb->users.ID
			$add_join
			{args_join}
		WHERE
			(
			  {like_query}
			  $bp_cf_select
			)
			$add_where
			$roles_query
			AND $cf_select
			$exclude_query
			$include_query
			{args_where}
		GROUP BY 
			{args_groupby}
		ORDER BY {args_orderby} $orderby_primary, $orderby_secondary, id DESC 
		LIMIT $query_limit";

		// Place the argument query fields
		if ( isset($args['user_query']) && is_array($args['user_query']) ) {
			$this->query = str_replace(
				array( '{args_fields}', '{args_join}', '{args_where}', '{args_orderby}' ),
				array( $args['user_query']['fields'], $args['user_query']['join'], $args['user_query']['where'], $args['user_query']['orderby'] ),
				$this->query
			);
		} else {
			$this->query = str_replace(
				array( '{args_fields}', '{args_join}', '{args_where}', '{args_orderby}' ),
				'',
				$this->query
			);
		}
		if ( isset($args['user_query']['groupby']) && $args['user_query']['groupby'] !== '' ) {
			$this->query = str_replace('{args_groupby}', $args['user_query']['groupby'], $this->query);
		} else {
			$this->query = str_replace('{args_groupby}', 'id', $this->query);
		}

		$querystr            = $this->buildQuery( $this->parts );
		$querystr            = apply_filters('asp_query_users', $querystr, $args, $args['_id'], $args['_ajax_search']);
		$userresults         = $wpdb->get_results($querystr); // @phpcs:ignore
		$this->results_count = count($userresults);

		if ( !$args['_ajax_search'] && $this->results_count > $limit ) {
			$this->results_count = $limit;
		}
		$userresults = array_slice($userresults, $args['_call_num'] * $limit, $limit);

		$this->results      = $userresults;
		$this->return_count = count($this->results);
	}

	/**
	 * @param string $post_id_field
	 * @return string
	 * @noinspection PhpDuplicateSwitchCaseBodyInspection
	 */
	protected function buildCffQuery( string $post_id_field ): string {
		global $wpdb;
		$args  = $this->args;
		$parts = array();

		$post_meta_allow_missing = $args['post_meta_allow_missing'];

		foreach ( $args['user_meta_filter'] as $data ) {

			$operator = $data['operator'];
			$posted   = $data['value'];
			$field    = $data['key'];

			// Is this a special case of date operator?
			if ( strpos($operator, 'datetime') === 0 ) {
				switch ( $operator ) {
					case 'datetime =':
						$current_part = "($wpdb->usermeta.meta_value BETWEEN '$posted 00:00:00' AND '$posted 23:59:59')";
						break;
					case 'datetime <>':
						$current_part = "($wpdb->usermeta.meta_value NOT BETWEEN '$posted 00:00:00' AND '$posted 23:59:59')";
						break;
					case 'datetime <':
						$current_part = "($wpdb->usermeta.meta_value < '$posted 00:00:00')";
						break;
					case 'datetime <=':
						$current_part = "($wpdb->usermeta.meta_value <= '$posted 23:59:59')";
						break;
					case 'datetime >':
						$current_part = "($wpdb->usermeta.meta_value > '$posted 23:59:59')";
						break;
					case 'datetime >=':
						$current_part = "($wpdb->usermeta.meta_value >= '$posted 00:00:00')";
						break;
					default:
						$current_part = "($wpdb->usermeta.meta_value < '$posted 00:00:00')";
						break;
				}
				// Is this a special case of timestamp?
			} elseif ( strpos($operator, 'timestamp') === 0 ) {
				switch ( $operator ) {
					case 'timestamp =':
						$current_part = "($wpdb->usermeta.meta_value BETWEEN $posted AND " . ( $posted + 86399 ) . ')';
						break;
					case 'timestamp <>':
						$current_part = "($wpdb->usermeta.meta_value NOT BETWEEN $posted AND " . ( $posted + 86399 ) . ')';
						break;
					case 'timestamp <':
						$current_part = "($wpdb->usermeta.meta_value < $posted)";
						break;
					case 'timestamp <=':
						$current_part = "($wpdb->usermeta.meta_value <= " . ( $posted + 86399 ) . ')';
						break;
					case 'timestamp >':
						$current_part = "($wpdb->usermeta.meta_value > " . ( $posted + 86399 ) . ')';
						break;
					case 'timestamp >=':
						$current_part = "($wpdb->usermeta.meta_value >= $posted)";
						break;
					default:
						$current_part = "($wpdb->usermeta.meta_value < $posted)";
						break;
				}
				// Check BETWEEN first -> range slider
			} elseif ( $operator === 'BETWEEN' ) {
				$current_part = "($wpdb->usermeta.meta_value BETWEEN " . $posted[0] . ' AND ' . $posted[1] . ' )';
				// If not BETWEEN but value is array, then drop-down or checkboxes
			} elseif ( is_array($posted) ) {
				// Is there a logic sent?
				$logic  = $data['logic'] ?? 'OR';
				$values = '';
				if ( $operator === 'IN' ) {
					$val = implode("','", $posted);
					if ( !empty($val) ) {
						$values .= "$wpdb->usermeta.meta_value $operator ('" . $val . "')";
					}
				} else {
					foreach ( $posted as $v ) {
						if ( $operator === 'ELIKE' || $operator === 'NOT ELIKE' ) {
							$_op = $operator === 'ELIKE' ? 'LIKE' : 'NOT LIKE';
							if ( $values !== '' ) {
								$values .= " $logic $wpdb->usermeta.meta_value $_op '" . $v . "'";
							} else {
								$values .= "$wpdb->usermeta.meta_value $_op '" . $v . "'";
							}
						} elseif ( $operator === 'NOT LIKE' || $operator === 'LIKE' ) {
							if ( $values !== '' ) {
								$values .= " $logic $wpdb->usermeta.meta_value $operator '%" . $v . "%'";
							} else {
								$values .= "$wpdb->usermeta.meta_value $operator '%" . $v . "%'";
							}
						} elseif ( $values !== '' ) {
								$values .= " $logic $wpdb->usermeta.meta_value $operator " . $v;
						} else {
							$values .= "$wpdb->usermeta.meta_value $operator " . $v;
						}
					}
				}

				$values       = $values === '' ? '0' : $values;
				$current_part = "($values)";
				// String operations
			} elseif ( $operator === 'NOT LIKE' || $operator === 'LIKE' ) {
				$current_part = "($wpdb->usermeta.meta_value $operator '%" . $posted . "%')";
			} elseif ( $operator === 'ELIKE' || $operator === 'NOT ELIKE' ) {
				$_op          = $operator === 'ELIKE' ? 'LIKE' : 'NOT LIKE';
				$current_part = "($wpdb->usermeta.meta_value $_op '$posted')";
				// Numeric operations or problematic stuff left
			} else {
				$current_part = "($wpdb->usermeta.meta_value $operator $posted  )";
			}

			// Finally, add the current part to the parts array
			if ( $current_part !== '' ) {
				$allowance = $data['allow_missing'] ?? $post_meta_allow_missing;

				$parts[] = array( $field, $current_part, $allowance );
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
		 * COUNT(post_id) is a MUST in the nested IF() statement !! Otherwise, the query will return empty rows, no idea why either
		 */

		foreach ( $parts as $part ) {
			$field           = $part[0];          // Field name
			$def             = $part[2] ? "(
				SELECT IF((meta_key IS NULL OR meta_value = ''), -1, COUNT(umeta_id))
				FROM $wpdb->usermeta
				WHERE $wpdb->usermeta.user_id = $post_id_field AND $wpdb->usermeta.meta_key='$field'
				LIMIT 1
			  ) = -1
			 OR" : '';                  // Allowance
			$qry             = $part[1];            // Query condition
			$cf_select_arr[] = "
			(
			  $def
			  (
				SELECT COUNT(umeta_id) as mtc
				FROM $wpdb->usermeta
				WHERE $wpdb->usermeta.user_id = $post_id_field AND $wpdb->usermeta.meta_key='$field' AND $qry
				GROUP BY umeta_id
				ORDER BY mtc
				LIMIT 1
			  ) >= 1
			)";
		}
		if ( count($cf_select_arr) ) {
			// Connect them based on the meta logic
			$cf_select = '( ' . implode( $args['post_meta_filter_logic'], $cf_select_arr ) . ' )';
		}

		return $cf_select;
	}

	/** @noinspection PhpUndefinedFunctionInspection */
	protected function postProcess(): void {
		$userresults = $this->results;

		$s    = $this->s;
		$_s   = $this->_s;
		$args = &$this->args;

		if ( !isset($args['_sd']) ) {
			return;
		}
		$sd          = $args['_sd'];
		$com_options = wd_asp()->o['asp_compatibility'];

		foreach ( $userresults as $k => &$r ) {

			if ( $args['_ajax_search'] ) {
				// If no image and defined, remove the result here, to perevent JS confusions
				if ( empty($r->image) && $sd['resultstype'] === 'isotopic' && $sd['i_ifnoimage'] === 'removeres' ) {
					unset($userresults[ $k ]);
					continue;
				}
				/* Same for polaroid mode */
				if ( empty($r->image) && isset($sd['resultstype']) &&
					$sd['resultstype'] === 'polaroid' && $sd['pifnoimage'] === 'removeres'
				) {
					unset($userresults[ $k ]);
					continue;
				}
			}

			/*--------------------------- Link ------------------------------*/
			switch ( $sd['user_search_url_source'] ) {
				case 'bp_profile':
					if ( function_exists('bp_core_get_user_domain') ) {
						$r->link = bp_core_get_user_domain($r->id);
					} else {
						$r->link = get_author_posts_url($r->id);
					}
					break;
				case 'custom':
					$r->link  = function_exists('pll_home_url') ? @pll_home_url() : home_url('/'); // @phpcs:ignore
					$r->link .= str_replace(
						array( '{USER_ID}', '{USER_LOGIN}', '{USER_NICENAME}', '{USER_DISPLAYNAME}' ),
						array( $r->id, $r->user_login, $r->user_nicename, $r->user_display_name ),
						$sd['user_search_custom_url']
					);
					if ( strpos($r->link, '{USER_NICKNAME}') !== false ) {
						$r->link = str_replace('{USER_NICKNAME}', get_user_meta( $r->id, 'nickname', true ), $r->link);
					}
					break;
				default:
					$r->link = get_author_posts_url($r->id);
			}
			/*---------------------------------------------------------------*/

			/*-------------------------- Image ------------------------------*/
			if ( $sd['user_search_display_images'] ) {
				if ( $sd['user_search_image_source'] === 'buddypress' &&
					function_exists('bp_core_fetch_avatar')
				) {
					$im = bp_core_fetch_avatar(
						array(
							'item_id' => $r->id,
							'html'    => false,
							'width'   => intval($sd['user_image_width']),
							'height'  => intval($sd['user_image_height']),
						)
					);
				} else {
					$im = get_avatar_url(
						$r->id,
						array(
							'size' => intval($sd['user_image_width']),
						)
					);
				}

				$image_settings = $sd['image_options'];
				if ( !empty($im) ) {
					$r->image = $im;
					if ( !$image_settings['image_cropping'] ) {
						$r->image = $im;
					} elseif ( strpos( $im, 'mshots/v1' ) === false && strpos( $im, '.gif' ) === false ) {
						$bfi_params = array(
							'width'  => $image_settings['image_width'],
							'height' => $image_settings['image_height'],
							'crop'   => true,
						);
						if ( !$image_settings['image_transparency'] ) {
							$bfi_params['color'] = wpdreams_rgb2hex( $image_settings['image_bg_color'] );
						}
						$r->image = asp_bfi_thumb( $im, $bfi_params );
					} else {
						$r->image = $im;
					}
				}

				// Default, if defined and available
				if ( empty($r->image) && !empty($sd['user_image_default']) ) {
					$r->image = $sd['user_image_default'];
				}
			}
			/*---------------------------------------------------------------*/

			if ( !empty($sd['user_search_advanced_title_field']) ) {
				$r->title = $this->advField(
					array(
						'main_field_slug'  => 'titlefield',
						'main_field_value' => $r->title,
						'r'                => $r,
						'field_pattern'    => stripslashes( $sd['user_search_advanced_title_field'] ),
					),
					$com_options['use_acf_getfield']
				);
			}

			/*---------------------- Description ----------------------------*/
			switch ( $sd['user_search_description_field'] ) {
				case 'buddypress_last_activity':
					$update = get_user_meta($r->id, 'bp_latest_update', true);
					if ( is_array($update) && isset($update['content']) ) {
						$r->content = $update['content'];
					}
					break;
				case 'nothing':
					$r->content = '';
					break;
				default:
					$content = get_user_meta($r->id, 'description', true);
					if ( $content !== '' ) {
						$r->content = $content;
					}
			}

			$_content           = Html::stripTags($r->content, $sd['striptagsexclude']);
			$description_length = $sd['user_res_descriptionlength'];

			// Get the words from around the search phrase, or just the description
			if ( $sd['description_context'] && count( $_s ) > 0 && $s !== '' ) {
				$_content = Str::getContext($_content, $description_length, $sd['description_context_depth'], $s, $_s);
			} elseif ( $_content !== '' && ( MB::strlen( $_content ) > $description_length ) ) {
				$_content = wd_substr_at_word($_content, $description_length);
			}

			if ( !empty($sd['user_search_advanced_description_field']) ) {
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
				add_filter('asp_user_advanced_field_value', $cb, 10, 4);
				$_content = $this->advField(
					array(
						'main_field_slug'  => 'descriptionfield',
						'main_field_value' => $_content,
						'r'                => $r,
						'field_pattern'    => stripslashes( $sd['user_search_advanced_description_field'] ),
					),
					$com_options['use_acf_getfield']
				);
				remove_filter('asp_user_advanced_field_value', $cb);
			}

			$r->content = wd_closetags( $_content );
			/*---------------------------------------------------------------*/

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

		$this->results = $userresults;
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
					if ( isset($field_args['maxlength']) ) {
						$val = wd_substr_at_word($val, $field_args['maxlength']);
					}
					// value, field name, post object, field arguments
					$val           = apply_filters('asp_user_advanced_field_value', $val, $field, $f_args['r'], $f_args);
					$field_pattern = str_replace( '{' . $complete_field . '}', $val, $field_pattern );
				} else {
					$val = User::getCFValue($field, $f_args['r'], $use_acf, $field_args);
					// For the recursive call to break, if any of the fields is empty
					if ( $empty_on_missing && $val === '' ) {
						return '';
					}
					$val           = Str::fixSSLURLs($val);
					$val           = apply_filters('asp_user_advanced_field_value', $val, $field, $f_args['r'], $f_args);
					$field_pattern = str_replace( '{' . $field . '}', $val, $field_pattern );
				}
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
}
