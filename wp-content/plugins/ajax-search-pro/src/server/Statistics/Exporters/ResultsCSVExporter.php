<?php

namespace WPDRMS\ASP\Statistics\Exporters;

use Exception;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Statistics\StatisticsService;

/**
 * CSV Exporter for Top/Popular Results statistics
 */
class ResultsCSVExporter {
	use SingletonTrait;

	/**
	 * Export getPopularResults() to CSV
	 *
	 * @param array  $args     Same args as ResultsQuery::getPopularResults()
	 * @param string $filename Optional base filename (without .csv)
	 * @throws Exception
	 */
	public function getPopularResultsToCsv( array $args = array(), string $filename = 'popular-results' ): void {
		$data    = StatisticsService::instance()->result_query->getPopularResults( $args );
		$results = $data['results'] ?? array();
		$compare = $data['compare'] ?? null;

		// -----------------------------------------------------------------
		// 1. Set CSV headers + output stream
		// -----------------------------------------------------------------
		$filename = sanitize_file_name( $filename ) . '_' . date( 'Y-m-d_H-i-s' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// UTF-8 BOM for proper Excel support
		echo "\xEF\xBB\xBF";

		$output = fopen( 'php://output', 'w' );

		if ( $output === false ) {
			throw new Exception( "Couldn't open php://output for writing." );
		}

		$headers = array(
			'Title',
			'URL',
			'Result ID',
			'Result Type',
			'Views',
			'Interactions',
		);

		if ( $compare ) {
			$headers = array_merge(
				$headers,
				array(
					'Prev Views',
					'Prev Interactions',
					'Change % (Views)',
					'Change % (Interactions)',
				)
			);
		}

		fputcsv( $output, $headers, ',', '"', '\\' );

		// -----------------------------------------------------------------
		// 3. Write data rows
		// -----------------------------------------------------------------
		foreach ( $results as $row ) {
			$line = array(
				$row->title,
				$row->url,
				$row->result_id,
				$row->result_type_name,
				$row->views,
				$row->interactions,
			);

			if ( $compare ) {
				$views_change        = isset( $row->views_prev_change_percent ) ? $row->views_prev_change_percent . '%' : '0%';
				$interactions_change = isset( $row->interactions_prev_change_percent ) ? $row->interactions_prev_change_percent . '%' : '0%';

				$line = array_merge(
					$line,
					array(
						$row->views_prev ?? 0,
						$row->interactions_prev ?? 0,
						$views_change,
						$interactions_change,
					)
				);
			}

			fputcsv( $output, $line, ',', '"', '\\' );
		}

		// -----------------------------------------------------------------
		// 4. Optional: Add totals row
		// -----------------------------------------------------------------
		if ( ! empty( $results ) ) {
			$total_views        = array_sum( array_column( $results, 'views' ) );
			$total_interactions = array_sum( array_column( $results, 'interactions' ) );

			$total_line = array(
				'TOTAL',
				'',
				'',
				'',
				$total_views,
				$total_interactions,
			);

			if ( $compare ) {
				$prev_total_views        = array_sum( array_column( $results, 'views_prev' ) );
				$prev_total_interactions = array_sum( array_column( $results, 'interactions_prev' ) );

				$views_change = $prev_total_views > 0
					? round( ( $total_views - $prev_total_views ) / $prev_total_views * 100, 2 )
					: ( $total_views > 0 ? 100 : 0 );

				$interactions_change = $prev_total_interactions > 0
					? round( ( $total_interactions - $prev_total_interactions ) / $prev_total_interactions * 100, 2 )
					: ( $total_interactions > 0 ? 100 : 0 );

				$total_line = array_merge(
					$total_line,
					array(
						$prev_total_views,
						$prev_total_interactions,
						$views_change . '%',
						$interactions_change . '%',
					)
				);
			}

			fputcsv( $output, $total_line, ',', '"', '\\' );
		}

		fclose( $output ); // phpcs:ignore
		die();
	}

	/**
	 * Export getLatestResults() to CSV
	 *
	 * @param array  $args     Same args as ResultsQuery::getLatestResults()
	 * @param string $filename Optional base filename (without .csv)
	 * @throws Exception
	 */
	public function getLatestResultsToCsv( array $args = array(), string $filename = 'latest-results' ): void {
		$data    = StatisticsService::instance()->result_query->getLatestResults( $args );
		$results = $data['results'] ?? array();

		// -----------------------------------------------------------------
		// 1. Set CSV headers + output stream
		// -----------------------------------------------------------------
		$filename = sanitize_file_name( $filename ) . '_' . date( 'Y-m-d_H-i-s' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// UTF-8 BOM for proper Excel support
		echo "\xEF\xBB\xBF";

		$output = fopen( 'php://output', 'w' );

		if ( $output === false ) {
			throw new Exception( "Couldn't open php://output for writing." );
		}

		$headers = array(
			'ID',
			'Result ID',
			'Search ID',
			'Result Type',
			'Phrase',
			'Search Type',
			'Date',
			'User Name',
			'User ID',
			'ASP ID',
			'Page',
			'Referer',
			'Interactions',
			'Device Type',
			'Title',
			'URL',
		);

		fputcsv( $output, $headers, ',', '"', '\\' );

		// -----------------------------------------------------------------
		// 3. Write data rows
		// -----------------------------------------------------------------
		foreach ( $results as $row ) {
			$line = array(
				$row->id,
				$row->result_id,
				$row->search_id,
				$row->result_type_name,
				$row->phrase,
				$row->search_type,
				$row->date,
				$row->user_name ?? '',
				$row->user_id ?? '',
				$row->asp_id ?? '',
				$row->page ?? '',
				$row->referer ?? '',
				$row->interactions ?? 0,
				$row->device_type,
				$row->title,
				$row->url,
			);

			fputcsv( $output, $line, ',', '"', '\\' );
		}

		fclose( $output ); // phpcs:ignore
		die();
	}
}
