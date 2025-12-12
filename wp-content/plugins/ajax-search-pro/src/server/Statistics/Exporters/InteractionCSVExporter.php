<?php

namespace WPDRMS\ASP\Statistics\Exporters;

use Exception;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Statistics\StatisticsService;

/**
 * CSV Exporter for Top/Popular Results statistics
 */
class InteractionCSVExporter {
	use SingletonTrait;

	/**
	 * Export getLatestInteractions() to CSV
	 *
	 * @param array  $args     Same args as InteractionQuery::getLatestInteractions()
	 * @param string $filename Optional base filename (without .csv)
	 * @throws Exception
	 */
	public function getLatestInteractionsToCsv( array $args = array(), string $filename = 'latest-interactions' ): void {
		$data    = StatisticsService::instance()->interaction_query->getLatestRInteractions( $args );
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
