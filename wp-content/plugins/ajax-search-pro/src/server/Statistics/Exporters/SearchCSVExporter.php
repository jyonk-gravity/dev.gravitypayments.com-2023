<?php

namespace WPDRMS\ASP\Statistics\Exporters;

use Exception;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Statistics\StatisticsService;

class SearchCSVExporter {
	use SingletonTrait;

	/**
	 * Export getSearches() results to CSV.
	 *
	 * @param array  $args Same args as SearchStatisticsController::getSearches()
	 * @param string $filename Optional filename (without .csv)
	 * @throws Exception
	 */
	public function getSearchesToCsv( array $args = array(), string $filename = 'search-statistics' ): void {
		$data = StatisticsService::instance()->search_query->getSearches( $args );

		$results = $data['results'] ?? array();
		$compare = $data['compare'] ?? null;

		// -----------------------------------------------------------------
		// 1. Set CSV headers
		// -----------------------------------------------------------------
		$filename = sanitize_file_name( $filename ) . '_' . date( 'Y-m-d_H-i-s' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// UTF-8 BOM for Excel
		echo "\xEF\xBB\xBF";

		$output = fopen( 'php://output', 'w' );

		if ( $output === false ) {
			throw new Exception("Couldn't open the php://output for writing.");
		}

		// -----------------------------------------------------------------
		// 2. Determine columns
		// -----------------------------------------------------------------
		$variation = $args['variation'] ?? 'popular';

		if ( $variation === 'latest' ) {
			$headers = array(
				'ID',
				'Phrase',
				'Type',
				'Date',
				'Referer',
				'User Name',
				'User ID',
				'Search Instance ID',
				'Blog ID',
				'Page',
				'Language',
				'Results Shown',
				'Found Results',
				'Device',
			);
		} else { // popular
			$headers = array(
				'Queried Field',
				'Total Searches',
				'Avg Results per Search',
				'Avg Interactions per Search',
				'Total Users',
			);

			if ( $compare ) {
				$headers = array_merge(
					$headers,
					array(
						'Prev Total Searches',
						'Prev Avg Results',
						'Prev Avg Interactions',
						'Change %',
					) 
				);
			}
		}

		fputcsv( $output, $headers, ',', '"', '\\' );

		// -----------------------------------------------------------------
		// 3. Write rows
		// -----------------------------------------------------------------
		foreach ( $results as $row ) {
			if ( $variation === 'latest' ) {
				$line = array(
					$row->id,
					$row->phrase,
					$row->type,
					$row->date,
					$row->referer ?? '',
					$row->user_name ?? '',
					$row->user_id ?? '',
					$row->asp_id ?? '',
					$row->blog_id ?? '',
					$row->page ?? '',
					$row->lang ?? '',
					$row->results_count ?? 0,
					$row->found_results ?? 0,
					$row->device_type ?? 'unknown',
				);
			} else { // popular
				$line = array(
					$row->field,
					$row->total_searches,
					$row->average_results_per_search,
					$row->average_interaction_per_search,
					$row->total_users,
				);

				if ( $compare ) {
					$line = array_merge(
						$line,
						array(
							$row->total_searches_prev ?? 0,
							$row->average_results_per_search_prev ?? '0.00',
							$row->average_interaction_per_search_prev ?? '0.00',
							$row->change_percent ?? '0.00',
						) 
					);
				}
			}

			fputcsv( $output, $line, ',', '"', '\\' );
		}

		fclose( $output ); // phpcs:ignore
		die();
	}

	/**
	 * Export getSearchesVolumeToCsv() results to CSV.
	 *
	 * @param array  $args     Same args as SearchStatisticsController::getSearchesVolumeToCsv()
	 * @param string $filename Optional base filename (without .csv)
	 * @throws Exception
	 */
	public function getSearchesVolumeToCsv( array $args = array(), string $filename = 'daily-search-volume' ): void {
		$data = StatisticsService::instance()->search_query->getSearchesVolume( $args );

		$results = $data['results'] ?? array();
		$compare = $data['compare'] ?? null;
		$totals  = $data['totals'] ?? null;

		// -----------------------------------------------------------------
		// 1. Set CSV headers
		// -----------------------------------------------------------------
		$filename = sanitize_file_name( $filename ) . '_' . date( 'Y-m-d_H-i-s' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// UTF-8 BOM for Excel
		echo "\xEF\xBB\xBF";

		$output = fopen( 'php://output', 'w' );

		if ( $output === false ) {
			throw new Exception( "Couldn't open php://output for writing." );
		}

		// -----------------------------------------------------------------
		// 2. Build headers
		// -----------------------------------------------------------------
		$headers = array(
			'Date',
			'Searches',
			'Results Shown',
			'Interactions',
			'Avg Interactions per Search',
		);

		if ( $compare ) {
			$headers = array_merge(
				$headers,
				array(
					'Prev Period',
					'Prev Searches',
					'Prev Results Shown',
					'Prev Interactions',
					'Change % (Searches)',
					'Change % (Results)',
					'Change % (Interactions)',
				) 
			);
		}

		fputcsv( $output, $headers, ',', '"', '\\' );

		// -----------------------------------------------------------------
		// 3. Write rows
		// -----------------------------------------------------------------
		foreach ( $results as $row ) {
			$line = array(
				$row->date,
				$row->searches,
				$row->results_shown,
				$row->interactions,
				$row->avg_interaction_per_search,
			);

			if ( $compare ) {
				$line = array_merge(
					$line,
					array(
						$compare->period,
						$row->searches_prev,
						$row->results_shown_prev,
						$row->interactions_prev,
						$row->change_searches_percent . '%',
						$row->change_results_percent . '%',
						$row->change_interactions_percent . '%',
					) 
				);
			}

			fputcsv( $output, $line, ',', '"', '\\' );
		}

		// -----------------------------------------------------------------
		// 4. Optional: Add totals row at the bottom
		// -----------------------------------------------------------------
		if ( $totals ) {
			$total_line = array(
				'TOTAL',
				$totals->searches,
				$totals->results_shown,
				$totals->interactions,
				$totals->searches > 0
					? number_format($totals->interactions / $totals->searches, 2)
					: '0.00',
			);

			if ( $compare ) {
				// Recalculate previous period totals from compare data
				$prev_total_searches      = 0;
				$prev_total_results_shown = 0;
				$prev_total_interactions  = 0;

				foreach ( $compare->data as $day ) {
					$prev_total_searches      += $day->searches_prev ?? 0;
					$prev_total_results_shown += $day->results_shown_prev ?? 0;
					$prev_total_interactions  += $day->interactions_prev ?? 0;
				}

				$change_searches = $prev_total_searches > 0
					? round(( $totals->searches - $prev_total_searches ) / $prev_total_searches * 100, 2)
					: ( $totals->searches > 0 ? 100 : 0 );

				$change_results = $prev_total_results_shown > 0
					? round(( $totals->results_shown - $prev_total_results_shown ) / $prev_total_results_shown * 100, 2)
					: ( $totals->results_shown > 0 ? 100 : 0 );

				$change_interactions = $prev_total_interactions > 0
					? round(( $totals->interactions - $prev_total_interactions ) / $prev_total_interactions * 100, 2)
					: ( $totals->interactions > 0 ? 100 : 0 );

				$total_line = array_merge(
					$total_line,
					array(
						$compare->period,
						$prev_total_searches,
						$prev_total_results_shown,
						$prev_total_interactions,
						$change_searches . '%',
						$change_results . '%',
						$change_interactions . '%',
					)
				);
			}
			fputcsv( $output, $total_line, ',', '"', '\\' );
		}
		fclose( $output );
		die();
	}
}
