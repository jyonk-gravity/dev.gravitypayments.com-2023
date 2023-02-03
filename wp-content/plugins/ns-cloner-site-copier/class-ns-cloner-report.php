<?php
/**
 * Cloner Reporting class.
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * NS_Cloner_Report class.
 *
 * Utility class to store pieces of data about the cloning process (objects cloned, etc)
 * that should be reported to the user at the end of the process.
 */
class NS_Cloner_Report {

	/**
	 * Options key to save report under
	 *
	 * @var string
	 */
	private $report_key = 'ns_cloner_report';

	/**
	 * Save a single report item
	 *
	 * @param string $key Key of report data item.
	 * @param mixed  $value Value of report data item.
	 *
	 * @return bool
	 */
	public function add_report( $key, $value ) {
		$reports         = $this->get_all_reports();
		$reports[ $key ] = $value;
		return update_site_option( $this->report_key, $reports );
	}

	/**
	 * Increase the count for a numeric report item
	 *
	 * @param string $key Key of report item.
	 * @param int    $value Amount to increase it.
	 */
	public function increment_report( $key, $value ) {
		$reports = $this->get_all_reports();
		if ( isset( $reports[ $key ] ) ) {
			$value = $reports[ $key ] + $value;
		}
		$this->add_report( $key, $value );
	}

	/**
	 * Add a non-fatal warning to the report
	 *
	 * @param string $message Text to display in notice.
	 */
	public function add_notice( $message ) {
		ns_cloner()->log->log( array( 'WRITING notice:', $message ) );
		$notices   = $this->get_report( '_notices' ) ?: array();
		$notices[] = $message;
		$this->add_report( '_notices', $notices );
	}

	/**
	 * Get single report item
	 *
	 * @param string $key Key of report data item.
	 * @return mixed|null
	 */
	public function get_report( $key ) {
		$reports = $this->get_all_reports();
		return isset( $reports[ $key ] ) ? $reports[ $key ] : null;
	}

	/**
	 * Get an array of all saved report items
	 *
	 * @return array
	 */
	public function get_all_reports() {
		return get_site_option( $this->report_key );
	}

	/**
	 * Get an HTML template containing rendered report data
	 *
	 * @return string
	 */
	public function get_html() {
		ob_start();
		ns_cloner()->render( 'report' );
		return ob_get_clean();
	}

	/**
	 * Delete all saved report items
	 *
	 * @return void
	 */
	public function clear_all_reports() {
		delete_site_option( $this->report_key );
	}

	/**
	 * Save the start time for this cloning process
	 */
	public function set_start_time() {
		$this->add_report( '_start_time', microtime( true ) );
	}

	/**
	 * Get the start time for this cloning process
	 *
	 * @param bool $prepared Whether to format the raw timestamp before returning.
	 * @return string
	 */
	public function get_start_time( $prepared = true ) {
		$start_time = $this->get_report( '_start_time' );
		return $prepared ? $this->prepare_time( $start_time ) : $start_time;
	}

	/**
	 * Save the end time for this cloning process
	 */
	public function set_end_time() {
		$this->add_report( '_end_time', microtime( true ) );
	}

	/**
	 * Get the end time for this cloning process
	 *
	 * @param bool $prepared Whether to format the raw timestamp before returning.
	 * @return string
	 */
	public function get_end_time( $prepared = true ) {
		$end_time = $this->get_report( '_end_time' );
		return $prepared ? $this->prepare_time( $end_time ) : $end_time;
	}

	/**
	 * Get the amount of time elapsed since the saved start time
	 *
	 * @return float
	 */
	public function get_elapsed_time() {
		return microtime( true ) - $this->get_start_time( false );
	}

	/**
	 * Get date from miliseconds
	 *
	 * @param int $time Raw time value (in ms) to format.
	 * @return string
	 */
	public function prepare_time( $time ) {
		$date = DateTime::createFromFormat( 'U.u', $time );
		return $date ? $date->format( 'Y-m-d H:i:s' ) : '';
	}

}
