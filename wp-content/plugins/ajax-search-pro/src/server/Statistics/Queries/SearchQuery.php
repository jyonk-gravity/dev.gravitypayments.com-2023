<?php

namespace WPDRMS\ASP\Statistics\Queries;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use WPDRMS\ASP\Core\Models\SearchInstance;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Statistics\ORM\Interaction;
use WPDRMS\ASP\Statistics\ORM\Result;
use WPDRMS\ASP\Statistics\ORM\Search;

class SearchQuery {
	use SingletonTrait;

	/**
	 * Get real-time search statistics for the last 60 minutes and 24 hours, grouped by intervals
	 *
	 * @return array {
	 *     @type array $last_60_minutes Array of ['time' => int, 'devices' => array(['device_type' => string, 'count' => int])]
	 *     @type array $last_24_hours Array of ['time' => int, 'devices' => array(['device_type' => string, 'count' => int])]
	 * }
	 */
	public function getRealtimeStats(): array {
		global $wpdb;

		// Last 60 minutes (1-minute intervals)
		$last_60_minutes_condition = 'date >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)';
		// phpcs:disable
		$last_60_minutes           = $wpdb->get_results(
			"SELECT 
            FLOOR(TIMESTAMPDIFF(MINUTE, date, NOW()) / 1) * 1 as time,
            CASE device_type 
                WHEN 1 THEN 'desktop' 
                WHEN 2 THEN 'tablet' 
                WHEN 3 THEN 'mobile' 
                ELSE 'unknown' 
            END as device_type,
            COUNT(*) as count
         FROM " . Search::getTableName() . " 
         WHERE $last_60_minutes_condition
         GROUP BY 
            FLOOR(TIMESTAMPDIFF(MINUTE, date, NOW()) / 1),
            device_type
         ORDER BY time ASC, device_type"
		);

		// Last 24 hours (hourly intervals)
		$last_24_hours_condition = 'date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)';
		$last_24_hours           = $wpdb->get_results(
			"SELECT 
            TIMESTAMPDIFF(HOUR, date, NOW()) as time,
            CASE device_type 
                WHEN 1 THEN 'desktop' 
                WHEN 2 THEN 'tablet' 
                WHEN 3 THEN 'mobile' 
                ELSE 'unknown' 
            END as device_type,
            COUNT(*) as count
         FROM " . Search::getTableName() . " 
         WHERE $last_24_hours_condition
         GROUP BY 
            TIMESTAMPDIFF(HOUR, date, NOW()),
            device_type
         ORDER BY time ASC, device_type"
		);

		$last_20_searches = $this->getSearches(
			array(
				'variation' => 'latest',
				'limit'     => 20,
			)
		)['results'];

		// Group last_60_minutes by time
		$grouped_60_minutes = array();
		foreach ( $last_60_minutes as $row ) {
			$time = (int) $row->time + 1; // Represent end of 1-minute interval
			if ( !isset($grouped_60_minutes[ $time ]) ) {
				$grouped_60_minutes[ $time ] = array(
					'time'    => $time,
					'devices' => array(),
				);
			}
			$grouped_60_minutes[ $time ]['devices'][] = array(
				'device_type' => $row->device_type,
				'count'       => (int) $row->count,
			);
		}
		$last_60_minutes_grouped = array_values($grouped_60_minutes);
		// phpcs:enable

		// Group last_24_hours by time
		$grouped_24_hours = array();
		foreach ( $last_24_hours as $row ) {
			$time = (int) $row->time + 1; // Represent end of hour
			if ( !isset($grouped_24_hours[ $time ]) ) {
				$grouped_24_hours[ $time ] = array(
					'time'    => $time,
					'devices' => array(),
				);
			}
			$grouped_24_hours[ $time ]['devices'][] = array(
				'device_type' => $row->device_type,
				'count'       => (int) $row->count,
			);
		}
		$last_24_hours_grouped = array_values($grouped_24_hours);

		return array(
			'last_60_minutes'  => $last_60_minutes_grouped,
			'last_24_hours'    => $last_24_hours_grouped,
			'last_20_searches' => $last_20_searches,
		);
	}

	/**
	 * Get daily search volume with full metrics: searches, results shown, interactions.
	 *
	 * @param Array<string, mixed> $args
	 * @return array
	 * @throws Exception
	 */
	public function getSearchesVolume( array $args = array() ): array {
		global $wpdb;

		$defaults = array(
			'start_date'        => date( 'Y-m-d H:i:s', strtotime( '-1 month' ) ),
			'end_date'          => date( 'Y-m-d H:i:s' ),
			'user_id'           => array(),
			'phrase'            => '',
			'blog_id'           => null,
			'asp_id'            => array(),
			'type'              => null,
			'device_type'       => array(),
			'referer'           => array(),
			'lang'              => array(),
			'has_interaction'   => null,
			'has_found_results' => null,
			'compare'           => true,
			'show_empty_days'   => false,
		);
		$args     = array_merge( $defaults, $args );

		$args['compare']           = boolval( $args['compare'] );
		$args['type']              = $args['type'] === null ? null : intval( $args['type'] );
		$args['phrase']            = $args['phrase'] === null ? null : strval( $args['phrase'] );
		$args['has_interaction']   = $args['has_interaction'] === null ? null : boolval( $args['has_interaction'] );
		$args['has_found_results'] = $args['has_found_results'] === null ? null : boolval( $args['has_found_results'] );
		$args['show_empty_days']   = boolval($args['show_empty_days']);

		$start_date = date( 'Y-m-d 00:00:00', strtotime( $args['start_date'] ) );
		$end_date   = date( 'Y-m-d 23:59:59', strtotime( $args['end_date'] ) );

		$search_table = Search::getTableName();
		$result_table = Result::getTableName();
		$inter_table  = Interaction::getTableName();

		// -----------------------------------------------------------------
		// Build shared WHERE
		// -----------------------------------------------------------------
		$where  = array( '1=1' );
		$params = array();

		$where[]  = 's.date BETWEEN %s AND %s';
		$params[] = $start_date;
		$params[] = $end_date;

		if ( ! empty( $args['user_id'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['user_id'] ), '%d' ) );
			$where[]      = "s.user_id IN ($placeholders)";
			$params       = array_merge( $params, $args['user_id'] );
		}
		if ( $args['blog_id'] !== null ) {
			$where[]  = 's.blog_id = %d';
			$params[] = $args['blog_id'];
		}
		if ( $args['type'] !== null ) {
			$where[]  = 's.type = %d';
			$params[] = $args['type'];
		}
		if ( $args['phrase'] !== null && $args['phrase'] !== '' ) {
			$where[]  = 's.phrase = %s';
			$params[] = $args['phrase'];
		}
		if ( ! empty( $args['asp_id'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['asp_id'] ), '%d' ) );
			$where[]      = "s.asp_id IN ($placeholders)";
			$params       = array_merge( $params, $args['asp_id'] );
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
			if ( in_array('', $args['referer'], true) ) {
				$where[] = "(s.referer IS NULL OR s.referer IN ($placeholders))";
			} else {
				$where[] = "s.referer IN ($placeholders)";
			}
			$params = array_merge( $params, $args['referer'] );
		}
		if ( $args['has_found_results'] === true ) {
			$where[] = 's.found_results > 0';
		} elseif ( $args['has_found_results'] === false ) {
			$where[] = 's.found_results = 0';
		}

		$where_sql = implode( ' AND ', $where );

		// Interaction filter (EXISTS / NOT EXISTS)
		$interaction_sub = '';
		if ( $args['has_interaction'] === true ) {
			$interaction_sub = " AND EXISTS (
            SELECT 1 FROM $result_table r
            JOIN $inter_table i ON i.result_id = r.id
            WHERE r.search_id = s.id
        )";
		} elseif ( $args['has_interaction'] === false ) {
			$interaction_sub = " AND NOT EXISTS (
            SELECT 1 FROM $result_table r
            JOIN $inter_table i ON i.result_id = r.id
            WHERE r.search_id = s.id
        )";
		}

		// -----------------------------------------------------------------
		// 1. Current period – daily aggregates
		// -----------------------------------------------------------------
		$inter_sub = "
        SELECT r.search_id, COUNT(*) AS interaction_count
        FROM $inter_table i
        JOIN $result_table r ON r.id = i.result_id
        GROUP BY r.search_id
    ";

		// phpcs:disable
		$current_sql = $wpdb->prepare(
			"SELECT
            DATE(s.date)                                      AS search_date,
            COUNT(*)                                          AS searches,
            COALESCE(SUM(s.found_results), 0)                 AS results_shown,
            COALESCE(SUM(inter.interaction_count), 0)         AS interactions

         FROM $search_table s
         LEFT JOIN ($inter_sub) inter ON inter.search_id = s.id
         WHERE $where_sql $interaction_sub
         GROUP BY DATE(s.date)
         ORDER BY search_date DESC",
			$params
		);

		$current_results = $wpdb->get_results( $current_sql );
		// phpcs:enable

		// Fill missing days + calculate totals
		$date_range = new DatePeriod(
			new DateTime( $start_date ),
			new DateInterval( 'P1D' ),
			( new DateTime( $end_date ) )
		);

		$filled              = array();
		$current_map         = array_column( $current_results, null, 'search_date' );
		$total_interactions  = 0;
		$total_results_shown = 0;
		$total_searches      = 0;

		foreach ( $date_range as $date ) {
			$d   = $date->format( 'Y-m-d' );
			$row = $current_map[ $d ] ?? (object) array(
				'searches'      => 0,
				'results_shown' => 0,
				'interactions'  => 0,
			);

			$searches     = (int) $row->searches;
			$shown        = (int) $row->results_shown;
			$interactions = (int) $row->interactions;

			$total_searches      += $searches;
			$total_results_shown += $shown;
			$total_interactions  += $interactions;

			$avg_int = $searches > 0 ? round( $interactions / $searches, 2 ) : 0;

			$filled[] = (object) array(
				'date'                       => $d,
				'searches'                   => $searches,
				'results_shown'              => $shown,
				'interactions'               => $interactions,
				'avg_interaction_per_search' => $avg_int,
				'searches_prev'              => 0,
				'interactions_prev'          => 0,
			);
		}

		// -----------------------------------------------------------------
		// 2. Compare period
		// -----------------------------------------------------------------
		$compare_data = null;
		if ( $args['compare'] ) {
			$period_seconds  = strtotime( $end_date ) - strtotime( $start_date );
			$prev_end_date   = date( 'Y-m-d 23:59:59', strtotime( $start_date ) - 1 );
			$prev_start_date = date( 'Y-m-d 00:00:00', strtotime( $prev_end_date ) - $period_seconds );

			$prev_where = str_replace(
				array( $start_date, $end_date ),
				array( $prev_start_date, $prev_end_date ),
				$where_sql
			);

			$prev_params = array_merge( array( $prev_start_date, $prev_end_date ), array_slice( $params, 2 ) );

			// phpcs:disable
			$prev_sql = $wpdb->prepare(
				"SELECT
                DATE(s.date)                                      AS search_date,
                COUNT(*)                                          AS searches_prev,
                COALESCE(SUM(s.found_results), 0)                 AS results_shown_prev,
                COALESCE(SUM(inter.interaction_count), 0)         AS interactions_prev
             FROM $search_table s
             LEFT JOIN ($inter_sub) inter ON inter.search_id = s.id
             WHERE $prev_where $interaction_sub
             GROUP BY DATE(s.date)",
				$prev_params
			);
			$prev_results = $wpdb->get_results( $prev_sql );
			// phpcs:enable

			$prev_map = array();
			foreach ( $prev_results as $r ) {
				$prev_map[ $r->search_date ] = $r;
			}

			// Calculate offset once
			$offset_days = ( strtotime( $start_date ) - strtotime( $prev_start_date ) ) / 86400;

			foreach ( $filled as $row ) {
				$corresponding_prev_date = date( 'Y-m-d', strtotime( $row->date ) - $offset_days * 86400 );
				$prev                    = $prev_map[ $corresponding_prev_date ] ?? null;

				$row->searches_prev      = (int) ( $prev->searches_prev ?? 0 );
				$row->results_shown_prev = (int) ( $prev->results_shown_prev ?? 0 );
				$row->interactions_prev  = (int) ( $prev->interactions_prev ?? 0 );

				// Change percentages
				$row->change_searches_percent = $row->searches_prev > 0
					? round( ( $row->searches - $row->searches_prev ) / $row->searches_prev * 100, 2 )
					: ( $row->searches > 0 ? 100 : 0 );

				$row->change_results_percent = $row->results_shown_prev > 0
					? round( ( $row->results_shown - $row->results_shown_prev ) / $row->results_shown_prev * 100, 2 )
					: ( $row->results_shown > 0 ? 100 : 0 );

				$row->change_interactions_percent = $row->interactions_prev > 0
					? round( ( $row->interactions - $row->interactions_prev ) / $row->interactions_prev * 100, 2 )
					: ( $row->interactions > 0 ? 100 : 0 );
			}

			$compare_data = (object) array(
				'period' => "$prev_start_date to $prev_end_date",
				'data'   => $prev_map,
			);
		}

		foreach ( $filled as $k => $row ) {
			if ( !$args['show_empty_days'] && $row->searches_prev === 0 && $row->searches === 0 ) {
				unset($filled[ $k ]);
				continue;
			}
		}

		return array(
			'results' => array_reverse($filled),
			'total'   => $total_searches,
			'totals'  => (object) array(
				'searches'      => $total_searches,
				'results_shown' => $total_results_shown,
				'interactions'  => $total_interactions,
			),
			'compare' => $compare_data,
		);
	}

	/**
	 * Get popular or latest searches with total count for pagination.
	 *
	 * @param array $args
	 * @return array [
	 *     'results' => array of stdClass,
	 *     'total'   => int,
	 *     'compare' => array|null  // Only when compare=true and variation=popular
	 * ]
	 */
	public function getSearches( array $args = array() ): array {
		global $wpdb;

		// -----------------------------------------------------------------
		// 1. Default + Sanitize args
		// -----------------------------------------------------------------
		$defaults              = array(
			'start_date'                  => date( 'Y-m-d H:i:s', strtotime( '-1 month' ) ),
			'end_date'                    => date( 'Y-m-d H:i:s' ),
			'phrase'                      => '',
			'search_id'                   => null, // null|int
			'variation'                   => 'popular', // 'popular' | 'latest'
			'popular_field'               => 'phrase', // phrase|referer|asp_id|type|device_type|lang
			'popular_field_include_empty' => true, // to include empty values (such as empty phrase) for popular variations
			'order_by'                    => 'id',
			'order'                       => 'desc',
			'user_id'                     => array(),
			'blog_id'                     => null,
			'asp_id'                      => array(),
			'type'                        => null,  // null/1/2, null = all, 1=live, 2=redirect
			'device_type'                 => array(),
			'referer'                     => array(), // string[]
			'lang'                        => array(),
			'has_interaction'             => null,
			'has_found_results'           => null,
			'limit'                       => 20,
			'offset'                      => 0,
			'compare'                     => true,
		);
		$args                  = array_merge( $defaults, $args );
		$allowed_popular_field = array( 'phrase', 'referer', 'asp_id', 'type', 'device_type', 'lang' );

		$args['search_id']                   = $args['search_id'] === null ? $args['search_id'] : intval($args['search_id']);
		$args['popular_field']               = strval($args['popular_field']);
		$args['popular_field']               =
			!in_array($args['popular_field'], $allowed_popular_field, true) ?
				'phrase' : $args['popular_field'];
		$args['popular_field_include_empty'] = boolval($args['popular_field_include_empty']);

		$args['limit']             = max( 0, (int) $args['limit'] );
		$args['offset']            = max( 0, (int) $args['offset'] );
		$args['compare']           = boolval($args['compare']);
		$args['type']              = $args['type'] === null ? $args['type'] : intval($args['type']);
		$args['has_interaction']   = $args['has_interaction'] === null ? $args['has_interaction'] : boolval($args['has_interaction']);
		$args['has_found_results'] = $args['has_found_results'] === null ? $args['has_found_results'] : boolval($args['has_found_results']);
		$args['device_type']       = is_array($args['device_type']) ? $args['device_type'] : array( $args['device_type'] );
		$variation                 = $args['variation'] === 'latest' ? 'latest' : 'popular';

		$allowed_order_by = array( 'id', 'phrase', 'asp_id', 'type', 'device_type', 'user_name', 'referer' );
		$args['order_by'] = !in_array($args['order_by'], $allowed_order_by, true) ? 'id' : $args['order_by'];
		$args['order']    = strtolower($args['order']) === 'asc' ? 'ASC' : 'DESC';

		// Normalize dates
		$start_date = date( 'Y-m-d 00:00:00', strtotime( $args['start_date'] ) );
		$end_date   = date( 'Y-m-d 23:59:59', strtotime( $args['end_date'] ) );

		// Table names
		$search_table = Search::getTableName();
		$result_table = Result::getTableName();
		$inter_table  = Interaction::getTableName();

		// -----------------------------------------------------------------
		// 2. Build WHERE + params (shared)
		// -----------------------------------------------------------------
		$where  = array( '1=1' );
		$params = array();

		$where[]  = 's.date BETWEEN %s AND %s';
		$params[] = $start_date;
		$params[] = $end_date;

		if ( $variation === 'latest' && $args['phrase'] !== '' ) {
			$where[]  = 's.phrase = %s';
			$params[] = $args['phrase'];
		}

		if ( $variation === 'popular' && !$args['popular_field_include_empty'] ) {
			$where[] = "(s.{$args['popular_field']} IS NOT NULL AND s.{$args['popular_field']} <> '')";
		}

		if ( ! empty( $args['user_id'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['user_id'] ), '%d' ) );
			$where[]      = "s.user_id IN ($placeholders)";
			$params       = array_merge( $params, $args['user_id'] );
		}

		if ( $args['search_id'] !== null ) {
			$where[]  = 's.id = %d';
			$params[] = $args['search_id'];
		}

		if ( $args['blog_id'] !== null ) {
			$where[]  = 's.blog_id = %d';
			$params[] = $args['blog_id'];
		}

		if ( $args['type'] !== null ) {
			$where[]  = 's.type = %d';
			$params[] = $args['type'];
		}

		if ( ! empty( $args['asp_id'] ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $args['asp_id'] ), '%d' ) );
			$where[]      = "s.asp_id IN ($placeholders)";
			$params       = array_merge( $params, $args['asp_id'] );
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
			if ( in_array('', $args['referer'], true) ) {
				$where[] = "(s.referer IS NULL OR s.referer IN ($placeholders))";
			} else {
				$where[] = "s.referer IN ($placeholders)";
			}
			$params = array_merge( $params, $args['referer'] );
		}

		if ( $args['has_found_results'] === true ) {
			$where[] = 's.found_results > 0';
		} elseif ( $args['has_found_results'] === false ) {
			$where[] = 's.found_results = 0';
		}

		$where_sql    = implode( ' AND ', $where );
		$count_params = $params;

		$interaction_sub = '';
		if ( $args['has_interaction'] === true ) {
			$interaction_sub = " AND EXISTS (
                SELECT 1 FROM $result_table r
                JOIN $inter_table i ON i.result_id = r.id
                WHERE r.search_id = s.id
            )";
		} elseif ( $args['has_interaction'] === false ) {
			$interaction_sub = " AND NOT EXISTS (
                SELECT 1 FROM $result_table r
                JOIN $inter_table i ON i.result_id = r.id
                WHERE r.search_id = s.id
            )";
		}

		// -----------------------------------------------------------------
		// 3. COUNT QUERY
		// -----------------------------------------------------------------
		if ( $variation === 'popular' ) {
			// phpcs:disable
			$count_sql = $wpdb->prepare(
				"SELECT COUNT(DISTINCT s.{$args['popular_field']}) FROM $search_table s WHERE $where_sql $interaction_sub",
				$count_params
			);
			// phpcs:enable
		} else {
			// phpcs:disable
			$count_sql = $wpdb->prepare(
				"SELECT COUNT(*) FROM $search_table s WHERE $where_sql $interaction_sub",
				$count_params
			);
			// phpcs:enable
		}

		$total = (int) $wpdb->get_var( $count_sql ); // phpcs:ignore

		// -----------------------------------------------------------------
		// 4. DATA QUERY
		// -----------------------------------------------------------------
		if ( $variation === 'popular' ) {

			$inter_sub = "
        SELECT r.search_id, COUNT(*) AS interaction_count
        FROM $inter_table i
        JOIN $result_table r ON r.id = i.result_id
        GROUP BY r.search_id
    ";

			// phpcs:disable
			$sql = $wpdb->prepare(
				"SELECT
            s.{$args['popular_field']} as field,
            s.{$args['popular_field']} as field_raw,
            COUNT(*)                                            AS total_searches,
            AVG(s.found_results)                                AS average_results_per_search,
            COALESCE(
                SUM(COALESCE(inter.interaction_count,0)) / COUNT(*),
                0
            )                                                   AS average_interaction_per_search,
            COUNT(DISTINCT s.user_id)+1                         AS total_users

         FROM $search_table s
         LEFT JOIN ($inter_sub) inter ON inter.search_id = s.id
         WHERE $where_sql $interaction_sub
         GROUP BY s.{$args['popular_field']}
         ORDER BY total_searches DESC
         LIMIT %d OFFSET %d",
				array_merge( $params, array( $args['limit'], $args['offset'] ) )
			);
			$results = $wpdb->get_results( $sql );
			// phpcs:enable

			/* ----  FORMAT NUMBERS  ---- */
			foreach ( $results as $r ) {
				$r->average_results_per_search     = number_format( (float) $r->average_results_per_search, 2 );
				$r->average_interaction_per_search = number_format( (float) $r->average_interaction_per_search, 2 );
			}

			$compare_data = null;
			if ( $args['compare'] && ! empty( $results ) ) {
				$period_seconds  = strtotime( $end_date ) - strtotime( $start_date );
				$prev_end_date   = date( 'Y-m-d 23:59:59', strtotime( $start_date ) - 1 );
				$prev_start_date = date( 'Y-m-d 00:00:00', strtotime( $prev_end_date ) - $period_seconds );

				$prev_where = str_replace(
					array( $start_date, $end_date ),
					array( $prev_start_date, $prev_end_date ),
					$where_sql
				);

				$prev_params = array_merge( array( $prev_start_date, $prev_end_date ), array_slice( $params, 2 ) );

				// phpcs:disable
				$prev_sql = $wpdb->prepare(
					"SELECT
                s.{$args['popular_field']} as field,
                s.{$args['popular_field']} as field_raw,
                COUNT(*)                                            AS total_searches_prev,
                AVG(s.found_results)                                AS average_results_per_search_prev,

                COALESCE(
                    SUM(COALESCE(inter.interaction_count,0)) / COUNT(*),
                    0
                )                                                   AS average_interaction_per_search_prev

             FROM $search_table s
             LEFT JOIN ($inter_sub) inter ON inter.search_id = s.id
             WHERE $prev_where $interaction_sub
             GROUP BY s.{$args['popular_field']}",
					$prev_params
				);

				$prev_results = $wpdb->get_results( $prev_sql );
				// phpcs:enable

				$prev_map = array();
				foreach ( $prev_results as $row ) {
					$prev_map[ $row->field ] = $row;
				}

				foreach ( $results as $row ) {
					$prev = $prev_map[ $row->field ] ?? null;

					$row->total_searches_prev                 = $prev->total_searches_prev ?? 0;
					$row->average_results_per_search_prev     = number_format( (float) ( $prev->average_results_per_search_prev ?? 0 ), 2 );
					$row->average_interaction_per_search_prev = number_format( (float) ( $prev->average_interaction_per_search_prev ?? 0 ), 2 );

					$change = 0;
					if ( $prev && $prev->total_searches_prev > 0 ) {
						$change = ( ( $row->total_searches - $prev->total_searches_prev ) / $prev->total_searches_prev ) * 100;
					} elseif ( $row->total_searches > 0 ) {
						$change = 100; // new field
					}
					$row->change_percent = round( $change, 2 );
				}

				// Final post process
				foreach ( $results as $row ) {
					$row->field = $this->getFormattedField($args['popular_field'], $row->field);
				}
				if ( isset($compare_data['data']) ) {
					foreach ( $compare_data['data'] as $row ) {
						$row->field = $this->getFormattedField($args['popular_field'], $row->field);
					}
				}

				$compare_data = array(
					'period' => "$prev_start_date to $prev_end_date",
					'data'   => $prev_map,
				);
			}
		} else { // latest

			// phpcs:disable
			$sql = $wpdb->prepare(
				"SELECT
                s.id,
                s.phrase,
                CASE s.type WHEN 0 THEN 'redirect' WHEN 1 THEN 'live' ELSE 'live' END AS type,
                s.date,
                u.display_name AS user_name,
                s.user_id,
                s.asp_id,
                s.blog_id,
                s.page,
                s.lang,
                s.referer,
                (SELECT COUNT(1) FROM $result_table r WHERE r.search_id = s.id) AS results_count,
                s.found_results,
                CASE s.device_type
                    WHEN 1 THEN 'desktop'
                    WHEN 2 THEN 'tablet'
                    WHEN 3 THEN 'mobile'
                    ELSE 'unknown'
                END AS device_type
             FROM $search_table s
             LEFT JOIN {$wpdb->users} u ON u.ID = s.user_id
             WHERE $where_sql $interaction_sub
             ORDER BY s.{$args['order_by']} {$args['order']}
             LIMIT %d OFFSET %d",
				array_merge( $params, array( $args['limit'], $args['offset'] ) )
			);

			$results = $wpdb->get_results( $sql );
			// phpcs:enable
		}

		// -----------------------------------------------------------------
		// 6. Return
		// -----------------------------------------------------------------
		$return = array(
			'results' => !is_wp_error($results) ? $results : array(),
			'total'   => $total,
		);

		if ( isset($compare_data) ) {
			$return['compare'] = $compare_data;
		}

		return $return;
	}

	/**
	 * @param string           $popular_field_type
	 * @param string|int|float $value
	 * @return string|null
	 */
	private function getFormattedField( string $popular_field_type, $value ): ?string {
		$ret = $value;
		switch ( $popular_field_type ) {
			case 'type':
				$ret = intval($value) === 0 ? __('Live', 'ajax-search-pro') : __('Redirect', 'ajax-search-pro');
				break;
			case 'device_type':
				$value = intval($value);

				if ( $value === 1 ) {
					$ret = __('Desktop', 'ajax-search-pro');
				} elseif ( $value === 2 ) {
					$ret = __('Tablet', 'ajax-search-pro');
				} elseif ( $value === 3 ) {
					$ret = __('Mobile', 'ajax-search-pro');
				} else {
					$ret = __('Unknown', 'ajax-search-pro');
				}
				break;
			case 'asp_id':
				/**
				 * @var SearchInstance|empty $instance
				 */
				$instance = wd_asp()->instances->get(intval($value));
				if ( !empty($instance) ) {
					$ret = "[{$instance->id}] " . $instance->name;
				}
				break;
		}

		return $ret;
	}
}
