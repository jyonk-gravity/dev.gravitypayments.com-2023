<?php

namespace WPDRMS\ASP\Statistics\Queries;

use WP_Error;
use WP_Post;
use WP_Term;
use WP_User;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Statistics\ORM\Interaction;
use WPDRMS\ASP\Statistics\ORM\Result;
use WPDRMS\ASP\Statistics\ORM\Search;

class ResultQuery {

	use SingletonTrait;

	/**
	 * Get most popular results (by views or interactions)
	 *
	 * @param array $args
	 * @return array [
	 *     'results' => array of stdClass,
	 *     'total'   => int,
	 *     'compare' => array|null
	 * ]
	 */
	public function getPopularResults( array $args = array() ): array {
		global $wpdb;

		$defaults = array(
			'start_date'    => date( 'Y-m-d H:i:s', strtotime( '-1 month' ) ),
			'end_date'      => date( 'Y-m-d H:i:s' ),
			'criteria'      => 'views', // 'views' | 'interactions'
			'phrase'        => '',
			'include_empty' => false,
			'user_id'       => array(),
			'blog_id'       => null,
			'asp_id'        => array(),
			'result_type'   => array(), // content type IDs: 1=pagepost, 2=term, etc.
			'search_type'   => null,    // null|1|2 (live/redirect)
			'device_type'   => array(),
			'referer'       => array(),
			'lang'          => array(),
			'limit'         => 20,
			'offset'        => 0,
			'compare'       => true,
		);
		$args     = array_merge( $defaults, $args );

		$args['include_empty'] = boolval( $args['include_empty'] );
		$args['limit']         = max( 0, (int) $args['limit'] );
		$args['offset']        = max( 0, (int) $args['offset'] );
		$args['compare']       = boolval( $args['compare'] );
		$args['search_type']   = $args['search_type'] === null ? null : (int) $args['search_type'];

		$start_date = date( 'Y-m-d 00:00:00', strtotime( $args['start_date'] ) );
		$end_date   = date( 'Y-m-d 23:59:59', strtotime( $args['end_date'] ) );

		$search_table = Search::getTableName();
		$result_table = Result::getTableName();
		$inter_table  = Interaction::getTableName();

		// -----------------------------------------------------------------
		// Build WHERE conditions
		// -----------------------------------------------------------------
		$where  = array( '1=1' );
		$params = array();

		$where[]  = 's.date BETWEEN %s AND %s';
		$params[] = $start_date;
		$params[] = $end_date;

		if ( $args['phrase'] !== '' ) {
			$where[]  = 's.phrase = %s';
			$params[] = $args['phrase'];
		}

		if ( ! $args['include_empty'] ) {
			$where[] = '(s.phrase IS NOT NULL AND s.phrase <> "")';
		}

		if ( ! empty( $args['user_id'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['user_id'] ), '%d' ) );
			$where[]      = "s.user_id IN ($placeholders)";
			$params       = array_merge( $params, $args['user_id'] );
		}

		if ( $args['blog_id'] !== null ) {
			$where[]  = 's.blog_id = %d';
			$params[] = $args['blog_id'];
		}

		if ( $args['search_type'] !== null ) {
			$where[]  = 's.type = %d';
			$params[] = $args['search_type'];
		}

		if ( ! empty( $args['asp_id'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['asp_id'] ), '%d' ) );
			$where[]      = "s.asp_id IN ($placeholders)";
			$params       = array_merge( $params, $args['asp_id'] );
		}

		if ( ! empty( $args['result_type'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['result_type'] ), '%d' ) );
			$where[]      = "r.result_type IN ($placeholders)";
			$params       = array_merge( $params, $args['result_type'] );
		}

		if ( ! empty( $args['device_type'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['device_type'] ), '%d' ) );
			$where[]      = "s.device_type IN ($placeholders)";
			$params       = array_merge( $params, $args['device_type'] );
		}

		if ( ! empty( $args['lang'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['lang'] ), '%s' ) );
			$where[]      = "s.lang IN ($placeholders)";
			$params       = array_merge( $params, $args['lang'] );
		}

		if ( ! empty( $args['referer'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['referer'] ), '%s' ) );
			if ( in_array( '', $args['referer'], true ) ) {
				$where[] = "(s.referer IS NULL OR s.referer IN ($placeholders))";
			} else {
				$where[] = "s.referer IN ($placeholders)";
			}
			$params = array_merge( $params, $args['referer'] );
		}

		$where_sql = implode( ' AND ', $where );

		// -----------------------------------------------------------------
		// Count total distinct results
		// -----------------------------------------------------------------
		$count_sql = $wpdb->prepare(
			"SELECT COUNT(DISTINCT r.result_id, r.result_type) 
             FROM $result_table r
             JOIN $search_table s ON s.id = r.search_id
             WHERE $where_sql",
			$params
		);
		$total     = (int) $wpdb->get_var( $count_sql );

		$order_field = $args['criteria'] === 'views' ? 'views' : 'interactions';

		// phpcs:ignore
		$sql = $wpdb->prepare(
			"SELECT
                r.result_id,
                r.result_type,
                COUNT(r.id) AS views,
                COUNT(i.id) AS interactions
             FROM $result_table r
             JOIN $search_table s ON s.id = r.search_id
             LEFT JOIN $inter_table i ON i.result_id = r.id
             WHERE $where_sql
             GROUP BY r.result_id, r.result_type
             ORDER BY $order_field DESC, views DESC
             LIMIT %d OFFSET %d",
			array_merge( $params, array( $args['limit'], $args['offset'] ) )
		);

		$results = $wpdb->get_results( $sql ); //phpcs:ignore

		// -----------------------------------------------------------------
		// Enrich with title + URL
		// -----------------------------------------------------------------
		foreach ( $results as $row ) {
			$details = $this->getResultDetails( $row->result_type, $row->result_id );

			$row->title            = $details['title'];
			$row->url              = $details['url'];
			$row->result_type_name = $details['result_type_name'];
			$row->views            = (int) $row->views;
			$row->interactions     = (int) $row->interactions;
		}

		// -----------------------------------------------------------------
		// Compare period
		// -----------------------------------------------------------------
		$compare_data = null;
		if ( $args['compare'] && ! empty( $results ) ) {
			$period_seconds  = strtotime( $end_date ) - strtotime( $start_date );
			$prev_end_date   = date( 'Y-m-d 23:59:59', strtotime( $start_date ) - 1 );
			$prev_start_date = date( 'Y-m-d 00:00:00', strtotime( $prev_end_date ) - $period_seconds );

			$prev_where  = str_replace(
				array( $start_date, $end_date ),
				array( $prev_start_date, $prev_end_date ),
				$where_sql
			);
			$prev_params = array_merge( array( $prev_start_date, $prev_end_date ), array_slice( $params, 2 ) );

			$prev_sql = $wpdb->prepare(
				"SELECT
                    r.result_id,
                    r.result_type,
	                COUNT(r.id) AS views,
	                COUNT(i.id) AS interactions
                 FROM $result_table r
                 JOIN $search_table s ON s.id = r.search_id
                 LEFT JOIN $inter_table i ON i.result_id = r.id
                 WHERE $prev_where
                 GROUP BY r.result_id, r.result_type",
				$prev_params
			);

			$prev_results = $wpdb->get_results( $prev_sql ); //phpcs:ignore
			$prev_map     = array();
			foreach ( $prev_results as $pr ) {
				$key              = $pr->result_id . '-' . $pr->result_type;
				$prev_map[ $key ] = array( (int) ( $pr->views ?? 0 ), (int) ( $pr->interactions ?? 0 ) );
			}

			foreach ( $results as $row ) {
				$key = $row->result_id . '-' . $row->result_type;
				foreach ( array( 'views', 'interactions' ) as $k => $field ) {
					$prev   = isset($prev_map[ $key ]) ? $prev_map[ $key ][ $k ] : 0;
					$change = 0;
					if ( $prev > 0 ) {
						$change = ( ( $row->{$field} - $prev ) / $prev ) * 100;
					} elseif ( $row->{$field} > 0 ) {
						$change = 100; // new result
					}
					$row->{ $field . '_prev' }                = $prev;
					$row->{ $field . '_prev_change_percent' } = round( $change, 2 );
				}
			}

			$compare_data = array(
				'period' => "$prev_start_date to $prev_end_date",
				'data'   => $prev_map,
			);
		}

		return array(
			'results' => $results,
			'total'   => $total,
			'compare' => $compare_data,
		);
	}

	public function getLatestResults( array $args = array() ): array {
		global $wpdb;

		$defaults = array(
			'start_date'    => date('Y-m-d H:i:s', strtotime('-1 month')),
			'end_date'      => date('Y-m-d H:i:s'),
			'phrase'        => '',
			'search_id'     => null,
			'include_empty' => false,
			'user_id'       => array(),
			'blog_id'       => null,
			'asp_id'        => array(),
			'result_type'   => array(), // content type IDs: 1=pagepost, 2=term, etc.
			'search_type'   => null,    // null|1|2 (live/redirect)
			'device_type'   => array(),
			'referer'       => array(),
			'lang'          => array(),
			'page'          => null,
			'limit'         => 20,
			'offset'        => 0,
			'compare'       => true,
			'order'         => 'desc', // 'desc' or 'asc'
			'order_by'      => 'id',   // 'id', 'phrase', 'asp_id', 'type', 'device_type', 'user_name', 'referer'
		);

		$args = array_merge( $defaults, $args );

		$args['search_id']     = $args['search_id'] === null ? null : (int) $args['search_id'];
		$args['include_empty'] = boolval( $args['include_empty'] );
		$args['limit']         = max( 0, (int) $args['limit'] );
		$args['offset']        = max( 0, (int) $args['offset'] );
		$args['compare']       = boolval( $args['compare'] );
		$args['search_type']   = $args['search_type'] === null ? null : (int) $args['search_type'];
		$args['page']          = $args['page'] === null ? null : (int) $args['page'];

		$start_date = date( 'Y-m-d 00:00:00', strtotime( $args['start_date'] ) );
		$end_date   = date( 'Y-m-d 23:59:59', strtotime( $args['end_date'] ) );

		$search_table = Search::getTableName();
		$result_table = Result::getTableName();
		$inter_table  = Interaction::getTableName();

		$allowed_order_by = array( 'id', 'phrase', 'asp_id', 'type', 'device_type', 'user_name', 'referer' );
		$args['order_by'] = !in_array($args['order_by'], $allowed_order_by, true) ? 'id' : $args['order_by'];
		$args['order']    = strtolower($args['order']) === 'asc' ? 'ASC' : 'DESC';

		// -----------------------------------------------------------------
		// Build WHERE conditions
		// -----------------------------------------------------------------
		$where  = array( '1=1' );
		$params = array();

		$where[]  = 's.date BETWEEN %s AND %s';
		$params[] = $start_date;
		$params[] = $end_date;

		if ( $args['phrase'] !== '' ) {
			$where[]  = 's.phrase = %s';
			$params[] = $args['phrase'];
		}

		if ( $args['search_id'] !== null ) {
			$where[]  = 'r.search_id = %d';
			$params[] = $args['search_id'];
		}

		if ( $args['page'] !== null ) {
			$where[]  = 's.page = %d';
			$params[] = $args['page'];
		}

		if ( ! $args['include_empty'] ) {
			$where[] = '(s.phrase IS NOT NULL AND s.phrase <> "")';
		}

		if ( ! empty( $args['user_id'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['user_id'] ), '%d' ) );
			$where[]      = "s.user_id IN ($placeholders)";
			$params       = array_merge( $params, $args['user_id'] );
		}

		if ( $args['blog_id'] !== null ) {
			$where[]  = 's.blog_id = %d';
			$params[] = $args['blog_id'];
		}

		if ( $args['search_type'] !== null ) {
			$where[]  = 's.type = %d';
			$params[] = $args['search_type'];
		}

		if ( ! empty( $args['asp_id'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['asp_id'] ), '%d' ) );
			$where[]      = "s.asp_id IN ($placeholders)";
			$params       = array_merge( $params, $args['asp_id'] );
		}

		if ( ! empty( $args['result_type'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['result_type'] ), '%d' ) );
			$where[]      = "r.result_type IN ($placeholders)";
			$params       = array_merge( $params, $args['result_type'] );
		}

		if ( ! empty( $args['device_type'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['device_type'] ), '%d' ) );
			$where[]      = "s.device_type IN ($placeholders)";
			$params       = array_merge( $params, $args['device_type'] );
		}

		if ( ! empty( $args['lang'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['lang'] ), '%s' ) );
			$where[]      = "s.lang IN ($placeholders)";
			$params       = array_merge( $params, $args['lang'] );
		}

		if ( ! empty( $args['referer'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['referer'] ), '%s' ) );
			if ( in_array( '', $args['referer'], true ) ) {
				$where[] = "(s.referer IS NULL OR s.referer IN ($placeholders))";
			} else {
				$where[] = "s.referer IN ($placeholders)";
			}
			$params = array_merge( $params, $args['referer'] );
		}

		$where_sql = implode( ' AND ', $where );

		// -----------------------------------------------------------------
		// Count total distinct results
		// -----------------------------------------------------------------
		$count_sql = $wpdb->prepare(
			"SELECT COUNT(*) 
              FROM $result_table r
             LEFT JOIN $search_table s ON s.id = r.search_id
             LEFT JOIN {$wpdb->users} u ON u.ID = s.user_id
             LEFT JOIN $inter_table i ON i.result_id = r.id
             WHERE $where_sql",
			$params
		);
		$total     = (int) $wpdb->get_var( $count_sql );

		// phpcs:ignore
		$sql = $wpdb->prepare(
			"SELECT
				r.id,
                r.result_id,
                r.search_id,
                r.result_type,
                s.phrase,
                CASE s.type WHEN 0 THEN 'redirect' WHEN 1 THEN 'live' ELSE 'live' END AS search_type,
                s.date,
                u.display_name AS user_name,
                s.user_id,
                s.asp_id,
                s.page,
                s.referer,
                COUNT(i.id) AS interactions,
                CASE s.device_type
                    WHEN 1 THEN 'desktop'
                    WHEN 2 THEN 'tablet'
                    WHEN 3 THEN 'mobile'
                    ELSE 'unknown'
                END AS device_type
             FROM $result_table r
             LEFT JOIN $search_table s ON s.id = r.search_id
             LEFT JOIN {$wpdb->users} u ON u.ID = s.user_id
             LEFT JOIN $inter_table i ON i.result_id = r.id
             WHERE $where_sql
             GROUP BY r.id
             ORDER BY {$args['order_by']} {$args['order']}, id DESC
             LIMIT %d OFFSET %d",
			array_merge( $params, array( $args['limit'], $args['offset'] ) )
		);

		$results = $wpdb->get_results( $sql ); //phpcs:ignore

		// -----------------------------------------------------------------
		// Enrich with title + URL
		// -----------------------------------------------------------------
		foreach ( $results as $row ) {
			$details = $this->getResultDetails( $row->result_type, $row->result_id );

			$row->title            = $details['title'];
			$row->url              = $details['url'];
			$row->result_type_name = $details['result_type_name'];
		}

		// -----------------------------------------------------------------
		// 6. Return
		// -----------------------------------------------------------------
		$return = array(
			'results' => !is_wp_error($results) ? $results : array(),
			'total'   => $total,
		);

		return $return;
	}

	/**
	 * @param  int $type
	 * @param  int $id
	 * @return array{
	 *     title: non-falsy-string,
	 *     url: string,
	 *     result_type_name: string,
	 * }
	 */
	public function getResultDetails( int $type, int $id ): array {
		$result_type_name = 'Unknown';
		switch ( $type ) {
			case 2:
				/**
				 * @var WP_Term|WP_Error $term
				 */
				$term = get_term($id);
				if ( $term instanceof WP_Error ) {
					$url   = get_home_url();
					$title = '[Deleted taxonomy term]';
				} else {
					$url   = get_term_link($term);
					$url   = $url instanceof WP_Error ? '[Unknown term]' : $url;
					$title = "[taxonomy: $term->taxonomy] " . $term->name;
				}
				$result_type_name = 'Taxonomy term';
				break;

			case 3:
				/**
				 * @var WP_User|WP_Error $user
				 */
				$user = get_user_by('id', $id );
				if ( $user instanceof WP_Error ) {
					$url   = get_home_url();
					$title = '[Deleted user]';
				} else {
					$url   = $user->user_url;
					$title = '[user] ' . $user->display_name;
				}
				$result_type_name = 'User';
				break;

			case 4:
				if ( !function_exists('get_blog_details') ) {
					$url   = get_home_url();
					$title = '[Unknown blog]';
				} else {
					$blog = get_blog_details($id);
					if ( $blog === false ) {
						$url   = get_home_url();
						$title = '[Deleted blog]';
					} else {
						$url   = $blog->siteurl;
						$title = "[Blog] $blog->blogname";
					}
				}
				$result_type_name = 'MU Blog';
				break;
			case 5:
				if (
					class_exists('\\BP_Groups_Group') &&
					function_exists('bp_get_group_url') &&
					function_exists( 'bp_get_group_name')
				) {
					/** @noinspection PhpUndefinedClassInspection */
					$group = new \BP_Groups_Group($id);
					/** @noinspection PhpUndefinedFunctionInspection */
					$url = bp_get_group_url($group);
					/** @noinspection PhpUndefinedFunctionInspection */
					$title = bp_get_group_name($group);
				} else {
					$url   = get_home_url();
					$title = '[Unknown or Deleted BuddyPress group]';
				}
				$result_type_name = 'BP User';
				break;

			case 6:
				$title = '[BuddyPress activity]';
				if (
					function_exists('bp_activity_get_permalink')
				) {
					/** @noinspection PhpUndefinedFunctionInspection */
					$url = bp_activity_get_permalink($id);
				} else {
					$url = get_home_url();
				}
				$result_type_name = 'BP Activity';
				break;

			case 7:
				$url   = get_comment_link($id);
				$title = wd_substr_at_word(get_comment_text($id), 40);
				if ( $title === '' || $url === '' ) {
					$url   = get_home_url();
					$title = '[Unknown or Deleted comment]';
				} else {
					$title = '[comment] ' . $title;
				}
				$result_type_name = 'Comment';
				break;

			case 8:
				$url   = wp_get_attachment_url($id);
				$title = get_the_title($id);
				if ( $title === '' || $url === false ) {
					$url   = get_home_url();
					$title = '[Unknown or Deleted media file]';
				} else {

					$title = '[media: ' . get_post_mime_type($id) . '] ' . $title;
				}
				$result_type_name = 'Media';
				break;

			default:
				/**
				 * @var WP_Post|WP_Error|null $p
				 */
				$p = get_post($id);
				if ( $p instanceof WP_Error || $p === null ) {
					$url   = get_home_url();
					$title = '[Unknown or Deleted post]';
				} else {
					$url              = get_permalink($p);
					$url              = $url === false ? '[Unknown or Deleted post]' : $url;
					$title            = "[cpt: $p->post_type] " . get_the_title($id);
					$result_type_name = 'Post Type';
				}       
		}

		return array(
			'title'            => $title,
			'url'              => $url,
			'result_type_name' => $result_type_name,
		);
	}


	/**
	 * @param  Result $result
	 * @return array{
	 *     title: non-falsy-string,
	 *     url: string,
	 *     id: int,
	 *     result_id: int,
	 *     search_id: int,
	 *     result_type: int,
	 * }
	 */
	public function getResultDetailsForResultObj( Result $result ): array {
		$details = $this->getResultDetails( $result->result_type, $result->result_id );

		return array(
			'title'            => $details['title'],
			'url'              => $details['url'],
			'id'               => $result->id,
			'result_id'        => $result->result_id,
			'result_type'      => $result->result_type,
			'result_type_name' => $details['result_type_name'],
			'search_id'        => $result->search_id,
		);
	}
}
