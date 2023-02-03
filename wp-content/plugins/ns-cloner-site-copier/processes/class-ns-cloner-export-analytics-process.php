<?php
/**
 * Background process for analytics/usage telemetry.
 *
 * @package NS_Cloner
 */

/**
 * Class NS_Cloner_Export_Analytics_Process
 */
class NS_Cloner_Export_Analytics_Process extends WP_Background_Process {

	/**
	 * Trigger action slug for this process
	 *
	 * @var string
	 */
	protected $action = 'background_export_analytics';

	/**
	 * Process individual analytics entry
	 *
	 * @param mixed $item Array of data from a queued item.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		$row_data = $item['data'];
		$result   = ns_cloner_analytics()->export_result_to_client( $row_data );
		if ( $result ) {
			// Update row in DB to is_synced = true.
			$this->set_log_synced( $row_data['id'] );
		}

		return false;
	}

	/**
	 * Set log entry as synced
	 *
	 * @param int $id Primary ID for a log entry.
	 *
	 * @return bool
	 */
	protected function set_log_synced( $id ) {
		global $wpdb;

		return $wpdb->update(
			ns_cloner_analytics()->get_db_log_table(),
			array( 'is_synced' => true ),
			array( 'id' => $id )
		);
	}
}

// Instantiate class for background handling.
return new NS_Cloner_Export_Analytics_Process();
