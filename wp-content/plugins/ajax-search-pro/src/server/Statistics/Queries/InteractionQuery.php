<?php

namespace WPDRMS\ASP\Statistics\Queries;

use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Statistics\ORM\Interaction;
use WPDRMS\ASP\Statistics\ORM\Result;
use WPDRMS\ASP\Statistics\ORM\Search;

class InteractionQuery {
	use SingletonTrait;

	public function getLatestRInteractions( array $args = array() ): array {
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
              FROM $inter_table i
             LEFT JOIN $result_table r ON i.result_id = r.id
             LEFT JOIN $search_table s ON s.id = r.search_id
             LEFT JOIN {$wpdb->users} u ON u.ID = s.user_id
             WHERE $where_sql",
			$params
		);
		$total     = (int) $wpdb->get_var( $count_sql );

		// phpcs:ignore
		$sql = $wpdb->prepare(
			"SELECT
				i.id,
                r.result_id,
                r.search_id,
                r.result_type,
                s.phrase,
                CASE s.type WHEN 0 THEN 'redirect' WHEN 1 THEN 'live' ELSE 'live' END AS search_type,
                i.date,
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
             FROM $inter_table i
             LEFT JOIN $result_table r ON i.result_id = r.id    
             LEFT JOIN $search_table s ON s.id = r.search_id
             LEFT JOIN {$wpdb->users} u ON u.ID = s.user_id
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
			$details = ResultQuery::instance()->getResultDetails( $row->result_type, $row->result_id );

			$row->title            = $details['title'];
			$row->url              = $details['url'];
			$row->result_type_name = $details['result_type_name'];
		}

		// -----------------------------------------------------------------
		// 6. Return
		// -----------------------------------------------------------------
		return array(
			'results' => !is_wp_error($results) ? $results : array(),
			'total'   => $total,
		);
	}
}