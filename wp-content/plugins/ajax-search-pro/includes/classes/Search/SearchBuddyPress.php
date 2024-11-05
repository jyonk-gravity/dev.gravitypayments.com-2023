<?php
namespace WPDRMS\ASP\Search;

use WPDRMS\ASP\Utils\Html;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Str;
use WPDRMS\ASP\Utils\Post;

defined('ABSPATH') || die("You can't access this file directly.");

class SearchBuddyPress extends AbstractSearch {

	/**
	 * @noinspection PhpUndefinedClassInspection
	 * @noinspection PhpUndefinedFunctionInspection
	 * @noinspection DuplicatedCode
	 */
	protected function doSearch(): void {
		global $wpdb;
		global $q_config;

		$args = &$this->args;
		$sd   = $args['_sd'] ?? array();

		$kw_logic             = $args['keyword_logic'];
		$q_config['language'] = $args['_qtranslate_lang'];

		$s  = $this->s; // full keyword
		$_s = $this->_s; // array of keywords

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

		if ( $args['_limit'] > 0 ) {
			$limit = $args['_limit'];
		} elseif ( $args['_ajax_search'] ) {
				$limit = $args['buddypress_limit'];
		} else {
			$limit = $args['buddypress_limit_override'];
		}

		if ( $limit <= 0 ) {
			return;
		}

		$replies_results  = array();
		$group_results    = array();
		$activity_results = array();

		$words = $args['_exact_matches'] ? array( $s ) : $_s;

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

		if (
			!function_exists('bp_is_active') ||
			!function_exists('bp_core_get_user_domain') ||
			!function_exists('bp_core_fetch_avatar') ||
			!function_exists('bp_get_group_permalink')
		) {
			return;
		}

		/*----------------------- Groups query --------------------------*/
		if ( $args['bp_groups_search'] && bp_is_active('groups') ) {
			if ( class_exists('\\BP_Groups_Group') ) {
				return;
			}
			$parts           = array();
			$relevance_parts = array();
			/*------------------------- Statuses ----------------------------*/
			$statuses = array();
			if ( $args['bp_groups_search_public'] ) {
				$statuses[] = 'public';
			}
			if ( $args['bp_groups_search_private'] ) {
				$statuses[] = 'private';
			}
			if ( $args['bp_groups_search_hidden'] ) {
				$statuses[] = 'hidden';
			}
			if ( count($statuses) < 1 ) {
				return;
			}
			$swords         = implode("','", $statuses);
			$group_statuses = '( ' . $wpdb->prefix . "bp_groups.status IN ('$swords') )";
			/*---------------------------------------------------------------*/

			if ( $s !== '' ) {
				/*------------------------- Title query -------------------------*/
				if ( $kw_logic === 'or' || $kw_logic === 'and' ) {
					$op = strtoupper($kw_logic);
					if ( count($_s) > 0 ) {
						$_like = implode("%' " . $op . ' ' . $wpdb->prefix . "bp_groups.name LIKE '%", $words);
					} else {
						$_like = $s;
					}
					$parts[] = '( ' . $wpdb->prefix . "bp_groups.name LIKE '$wcl" . $_like . "$wcr' )";
				} else {
					$_like = array();
					$op    = $kw_logic === 'andex' ? 'AND' : 'OR';
					foreach ( $words as $word ) {
						$_like[] = '
					   ( ' . $wpdb->prefix . "bp_groups.name LIKE '% " . $word . " %'
					OR  " . $wpdb->prefix . "bp_groups.name LIKE '" . $word . " %'
					OR  " . $wpdb->prefix . "bp_groups.name LIKE '% " . $word . "'
					OR  " . $wpdb->prefix . "bp_groups.name = '" . $word . "')";
					}
					$parts[] = '(' . implode(' ' . $op . ' ', $_like) . ')';
				}

				if ( count($_s) > 0 ) {
					$relevance_parts[] = '(case when
				  ( ' . $wpdb->prefix . "bp_groups.name LIKE '%$_s[0]%')
				   then " . w_isset_def($sd['titleweight'], 10) . ' else 0 end)';
				}
				$relevance_parts[] = '(case when
				( ' . $wpdb->prefix . "bp_groups.name LIKE '%$s%')
				 then " . w_isset_def($sd['etitleweight'], 10) . ' else 0 end)';

				/*---------------------------------------------------------------*/

				/*---------------------- Description query ----------------------*/
				if ( $kw_logic === 'or' || $kw_logic === 'and' ) {
					$op = strtoupper($kw_logic);
					if ( count($_s) > 0 ) {
						$_like = implode("%' " . $op . ' lower(' . $wpdb->prefix . "bp_groups.description) LIKE '%", $words);
					} else {
						$_like = $s;
					}
					$parts[] = '( lower(' . $wpdb->prefix . "bp_groups.description) LIKE '$wcl" . $_like . "$wcr' )";
				} else {
					$_like = array();
					$op    = $kw_logic === 'andex' ? 'AND' : 'OR';
					foreach ( $words as $word ) {
						$_like[] = '
					   (lower(' . $wpdb->prefix . "bp_groups.description) LIKE '% " . $word . " %'
					OR  lower(" . $wpdb->prefix . "bp_groups.description) LIKE '" . $word . " %'
					OR  lower(" . $wpdb->prefix . "bp_groups.description) LIKE '% " . $word . "'
					OR  lower(" . $wpdb->prefix . "bp_groups.description) = '" . $word . "')";
					}
					$parts[] = '(' . implode(' ' . $op . ' ', $_like) . ')';
				}

				if ( count($_s) > 0 ) {
					$relevance_parts[] = '(case when
				  ( ' . $wpdb->prefix . "bp_groups.description LIKE '%$_s[0]%')
				   then " . w_isset_def($sd['contentweight'], 10) . ' else 0 end)';
				}
				$relevance_parts[] = '(case when
				( ' . $wpdb->prefix . "bp_groups.description LIKE '%$s%')
				 then " . w_isset_def($sd['econtentweight'], 10) . ' else 0 end)';
				/*---------------------------------------------------------------*/
			}

			/*------------------------- Build like --------------------------*/
			$like_query = implode(' OR ', $parts);
			if ( $like_query === '' ) {
				$like_query = '(1)';
			} else {
				$like_query = "($like_query)";
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Build relevance ------------------------*/
			$relevance = implode(' + ', $relevance_parts);
			if ( !$args['_post_use_relevance'] || $relevance === '' ) {
				$relevance = '(1)';
			} else {
				$relevance = "($relevance)";
			}
			/*---------------------------------------------------------------*/

			$querystr = '
		 SELECT
			{args_fields}
		   ' . $wpdb->prefix . "bp_groups.id as id,
		   $this->c_blogid as `blogid`,
		   " . $wpdb->prefix . 'bp_groups.name as `title`,
		   ' . $wpdb->prefix . 'bp_groups.description as `content`,
		   ' . $wpdb->prefix . "bp_groups.date_created as `date`,
		   $wpdb->users.user_nicename as `author`,
		   'bp_group' as `content_type`,
		   'bp_groups' as `g_content_type`,
		   $relevance as `relevance`
		 FROM
		   " . $wpdb->prefix . "bp_groups
		 LEFT JOIN $wpdb->users ON $wpdb->users.ID = " . $wpdb->prefix . "bp_groups.creator_id
		 {args_join}
		 WHERE
			  $group_statuses
			  {args_where}
		  AND $like_query
		  GROUP BY {args_groupby}
		  ORDER BY {args_orderby} $orderby_primary, $orderby_secondary
		  LIMIT " . $limit;

			// Place the argument query fields
			if ( isset($args['buddypress_groups_query']) && is_array($args['buddypress_groups_query']) ) {
				$querystr = str_replace(
					array( '{args_fields}', '{args_join}', '{args_where}', '{args_orderby}' ),
					array(
						$args['buddypress_groups_query']['fields'],
						$args['buddypress_groups_query']['join'],
						$args['buddypress_groups_query']['where'],
						$args['buddypress_groups_query']['orderby'],
					),
					$querystr
				);
			} else {
				$querystr = str_replace(
					array( '{args_fields}', '{args_join}', '{args_where}', '{args_orderby}' ),
					'',
					$querystr
				);
			}
			if ( isset($args['buddypress_groups_query']['groupby']) && $args['buddypress_groups_query']['groupby'] !== '' ) {
				$querystr = str_replace('{args_groupby}', $args['buddypress_groups_query']['groupby'], $querystr);
			} else {
				$querystr = str_replace('{args_groupby}', $wpdb->prefix . 'bp_groups.id', $querystr);
			}

			$group_results = $wpdb->get_results($querystr); // @phpcs:ignore
			if ( isset($sd['image_options']) ) {
				foreach ( $group_results as $k => &$v ) {
					/* @noinspection All */
					$group   = new \BP_Groups_Group($v->id); // @phpstan-ignore
					$v->link = bp_get_group_permalink($group);
					if ( $sd['image_options']['show_images'] ) {
						$avatar_options = array(
							'item_id' => $v->id,
							'object'  => 'group',
							'type'    => 'full',
							'html'    => false,
						);
						$im             = bp_core_fetch_avatar($avatar_options);

						if ( $im !== '' ) {
							$v->image = Str::fixSSLURLs($im);
						}
					}
					/* Remove the results in polaroid mode */
					if ( $args['_ajax_search'] && empty($v->image) && isset($sd['resultstype']) &&
						$sd['resultstype'] === 'polaroid' && $sd['pifnoimage'] === 'removeres' ) {
						unset($group_results[ $k ]);
						continue;
					}

					$_content = Post::dealWithShortcodes($v->content, $sd['shortcode_op'] === 'remove');
					$_content = Html::stripTags($_content, $sd['striptagsexclude']);
					// Get the words from around the search phrase, or just the description
					if ( $sd['description_context'] && count( $_s ) > 0 && $s !== '' ) {
						$_content = Str::getContext($_content, $sd['descriptionlength'], $sd['description_context_depth'], $s, $_s);
					} elseif ( $_content !== '' && ( MB::strlen( $_content ) > $sd['descriptionlength'] ) ) {
						$_content = wd_substr_at_word($_content, $sd['descriptionlength']);
					}
					$v->content = $_content;

					// --------------------------------- DATE -----------------------------------
					if ( $sd['showdate'] ) {
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
			/*---------------------------------------------------------------*/

			/*----------------------- Activity query ------------------------*/

		if ( $args['bp_activities_search'] && bp_is_active('activity') ) {
			if ( !function_exists('bp_activity_get_permalink') ) {
				return;
			}
			$parts           = array();
			$relevance_parts = array();
			/*---------------------- Description query ----------------------*/
			if ( $s !== '' ) {
				if ( $kw_logic === 'or' || $kw_logic === 'and' ) {
					$op = strtoupper($kw_logic);
					if ( count($_s) > 0 ) {
						$_like = implode("%' " . $op . ' lower(' . $wpdb->prefix . "bp_activity.content) LIKE '%", $words);
					} else {
						$_like = $s;
					}
					$parts[] = '( ' . $wpdb->prefix . "bp_activity.content LIKE '$wcl" . $_like . "$wcr' )";
				} else {
					$_like = array();
					$op    = $kw_logic === 'andex' ? 'AND' : 'OR';
					foreach ( $words as $word ) {
						$_like[] = '
					   ( ' . $wpdb->prefix . "bp_activity.content LIKE '% " . $word . " %'
					OR  " . $wpdb->prefix . "bp_activity.content LIKE '" . $word . " %'
					OR  " . $wpdb->prefix . "bp_activity.content LIKE '% " . $word . "'
					OR  " . $wpdb->prefix . "bp_activity.content = '" . $word . "')";
					}
					$parts[] = '(' . implode(' ' . $op . ' ', $_like) . ')';
				}

				if ( count($_s) > 0 ) {
					$relevance_parts[] = '(case when
				  ( ' . $wpdb->prefix . "bp_activity.content LIKE '%$_s[0]%')
				   then " . w_isset_def($sd['econtentweight'], 10) . ' else 0 end)';
				}
				$relevance_parts[] = '(case when
					( ' . $wpdb->prefix . "bp_activity.content LIKE '%$s%')
					 then " . w_isset_def($sd['contentweight'], 10) . ' else 0 end)';
			}
			/*---------------------------------------------------------------*/

			/*------------------------- Build like --------------------------*/
			$like_query = implode(' OR ', $parts);
			if ( $like_query === '' ) {
				$like_query = '(1)';
			} else {
				$like_query = "($like_query)";
			}
			/*---------------------------------------------------------------*/

			/*---------------------- Build relevance ------------------------*/
			$relevance = implode(' + ', $relevance_parts);
			if ( !$args['_post_use_relevance'] || $relevance === '' ) {
				$relevance = '(1)';
			} else {
				$relevance = "($relevance)";
			}
			/*---------------------------------------------------------------*/
			$querystr = '
			 SELECT
				{args_fields}
			   ' . $wpdb->prefix . "bp_activity.id as id,
			   $this->c_blogid as `blogid`,
			   $wpdb->users.display_name as `title`,
			   " . $wpdb->prefix . 'bp_activity.content as `content`,
			   ' . $wpdb->prefix . "bp_activity.date_recorded as `date`,
			   $wpdb->users.display_name as `author`,
			   " . $wpdb->prefix . "bp_activity.user_id as author_id,
			   'bp_activity' as `content_type`,
			   'bp_activities' as `g_content_type`,
			   $relevance as `relevance`
			 FROM
			   " . $wpdb->prefix . "bp_activity
			 LEFT JOIN $wpdb->users ON $wpdb->users.ID = " . $wpdb->prefix . 'bp_activity.user_id
			 {args_join}
			 WHERE
			   ' . $wpdb->prefix . "bp_activity.component IN ('activity', 'groups')
			   AND " . $wpdb->prefix . 'bp_activity.is_spam = 0
			   AND ' . $wpdb->prefix . "bp_activity.hide_sitewide = 0
			   AND $like_query
			   {args_where}
			   GROUP BY {args_groupby}
			   ORDER BY {args_orderby} $orderby_primary, $orderby_secondary
			   LIMIT " . $limit;

			// Place the argument query fields
			if ( isset($args['buddypress_activities_query']) && is_array($args['buddypress_activities_query']) ) {
				$querystr = str_replace(
					array( '{args_fields}', '{args_join}', '{args_where}', '{args_orderby}' ),
					array(
						$args['buddypress_activities_query']['fields'],
						$args['buddypress_activities_query']['join'],
						$args['buddypress_activities_query']['where'],
						$args['buddypress_activities_query']['orderby'],
					),
					$querystr
				);
			} else {
				$querystr = str_replace(
					array( '{args_fields}', '{args_join}', '{args_where}', '{args_orderby}' ),
					'',
					$querystr
				);
			}
			if ( isset($args['buddypress_activities_query']['groupby']) && $args['buddypress_activities_query']['groupby'] !== '' ) {
				$querystr = str_replace('{args_groupby}', $args['buddypress_activities_query']['groupby'], $querystr);
			} else {
				$querystr = str_replace('{args_groupby}', $wpdb->prefix . 'bp_activity.id', $querystr);
			}

			$activity_results = $wpdb->get_results($querystr); // phpcs:ignore

			foreach ( $activity_results as $k => &$v ) {
				$v->link  = bp_activity_get_permalink($v->id);
				$v->image = Str::fixSSLURLs(
					bp_core_fetch_avatar(
						array(
							'item_id' => $v->author_id,
							'html'    => false,
						)
					) 
				);

				$_content = Post::dealWithShortcodes($v->content, $sd['shortcode_op'] === 'remove');
				$_content = Html::stripTags($_content, $sd['striptagsexclude']);
				// Get the words from around the search phrase, or just the description
				if ( $sd['description_context'] && count( $_s ) > 0 && $s !== '' ) {
					$_content = Str::getContext($_content, $sd['descriptionlength'], $sd['description_context_depth'], $s, $_s);
				} elseif ( $_content !== '' && ( MB::strlen( $_content ) > $sd['descriptionlength'] ) ) {
					$_content = wd_substr_at_word($_content, $sd['descriptionlength']);
				}
				$v->content = $_content;
			
				// --------------------------------- DATE -----------------------------------
				if ( isset($sd['showdate']) && $sd['showdate'] ) {
					$post_time = strtotime($v->date);
					/* Remove the results in polaroid mode */
					if ( $args['_ajax_search'] && empty($v->image) && isset($sd['resultstype']) &&
						$sd['resultstype'] === 'polaroid' && $sd['pifnoimage'] === 'removeres' ) {
						unset($activity_results[ $k ]);
						continue;
					}
					if ( $sd['custom_date'] ) {
						$date_format = w_isset_def($sd['custom_date_format'], 'Y-m-d H:i:s');
					} else {
						$date_format = get_option('date_format', 'Y-m-d') . ' ' . get_option('time_format', 'H:i:s');
					}

					$v->date = @date_i18n($date_format, $post_time); // phpcs:ignore
				}
				// --------------------------------------------------------------------------
			}
		}

		do_action('bbpress_init');

		$this->results_count = count($group_results) + count($activity_results);

		if ( !$args['_ajax_search'] && $this->results_count > $limit ) {
			$this->results_count = $limit;
		}

		$this->results = array(
			'repliesresults'  => $replies_results,
			'groupresults'    => $group_results,
			'activityresults' => $activity_results,
		);

		$this->return_count = count($group_results) + count($activity_results);
	}

	protected function postProcess(): void {}
}
