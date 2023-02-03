<?php
/**
 * Cloner AJAX class.
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * NS_Cloner_Ajax class.
 *
 * Centralizes AJAX hooks in one place and maps those hooks to their corresponding actions and results.
 */
class NS_Cloner_Ajax {

	/**
	 * NS_Cloner_Ajax constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_ns_cloner_search_sites', array( $this, 'search_sites' ) );
		add_action( 'wp_ajax_ns_cloner_validate_section', array( $this, 'validate_section' ) );
		add_action( 'wp_ajax_ns_cloner_process_init', array( $this, 'process_init' ) );
		add_action( 'wp_ajax_ns_cloner_get_progress', array( $this, 'get_progress' ) );
		add_action( 'wp_ajax_ns_cloner_process_finish', array( $this, 'process_finish' ) );
		add_action( 'wp_ajax_ns_cloner_process_exit', array( $this, 'process_exit' ) );
		add_action( 'wp_ajax_ns_cloner_delete_schedule', array( $this, 'delete_schedule' ) );
		add_action( 'wp_ajax_ns_cloner_delete_options', array( $this, 'delete_options' ) );
	}

	/**
	 * Output JSON encoded list of blogs matching a posted search term
	 */
	public function search_sites() {
		$this->check_nonce();
		$matching_sites = array();
		$search_term    = ns_cloner_request()->get( 'term' );
		$search_value   = esc_sql( ns_cloner()->db->esc_like( $search_term ) );
		$search_column  = is_subdomain_install() ? 'domain' : 'path';
		$blogs_table    = ns_cloner()->db->blogs;
		$results        = ns_cloner()->db->get_results( "SELECT blog_id FROM $blogs_table WHERE $search_column LIKE '%$search_value%'" );
		foreach ( $results as $result ) {
			$details          = get_blog_details( $result->blog_id );
			$matching_sites[] = array(
				'value' => $details->blog_id,
				'label' => "$details->blogname ($details->siteurl)",
			);
		}
		wp_send_json( $matching_sites );
	}

	/**
	 * Pre-validate a single section of the Cloner form
	 */
	public function validate_section() {
		$this->check_nonce();
		$section_id = ns_cloner_request()->get( 'section_id' );
		ns_cloner()->process_manager->validate( $section_id );
		$this->send_response();
	}

	/**
	 * Start the cloning process and associated background processes
	 */
	public function process_init() {
		$this->check_nonce();
		ns_cloner()->process_manager->init();
		$this->send_response();
	}

	/**
	 * Abort / cancel the current cloning process
	 */
	public function process_exit() {
		$this->check_nonce();
		ns_cloner()->process_manager->exit_processes( 'Process canceled by user' );
		// Clear reports so it doesn't pop up the next time that they load the Cloner page.
		ns_cloner()->report->clear_all_reports();
		// Use standard wp function here rather than $this->send_response because if it was aborted
		// manually, it should just end smoothly - don't show any errors in any case.
		wp_send_json_success();
	}

	/**
	 * Check the status / progress of the active process and return progress data
	 */
	public function get_progress() {
		$this->check_nonce();
		// Always try to see if it's complete and we should finish it.
		ns_cloner()->process_manager->maybe_finish();
		// Get results of progress.
		$progress = ns_cloner()->process_manager->get_progress();
		// Send results back to browser.
		if ( 'reported' === $progress['status'] ) {
			// Format report into html if cloning is done.
			$this->send_response( array( 'report' => ns_cloner()->report->get_html() ) );
		} else {
			// Otherwise send progress data to update the UI.
			$this->send_response( $progress );
		}
	}

	/**
	 * Clear an item or all items from the scheduled cloning operations queue
	 */
	public function delete_schedule() {
		$this->check_nonce();
		$index = ns_cloner_request()->get( 'index' );
		ns_cloner()->schedule->delete( $index );
		$this->send_response();
	}

	/**
	 * Clear all plugin options data (settings and current operation)
	 */
	public function delete_options() {
		$this->check_nonce();
		$options_table = is_multisite() ? ns_cloner()->db->sitemeta : ns_cloner()->db->options;
		$options_key   = is_multisite() ? 'meta_key' : 'option_name';
		ns_cloner()->db->query( "DELETE FROM $options_table WHERE $options_key LIKE 'ns_cloner%'" );
		wp_send_json_success();
	}

	/**
	 * Validate nonce AND check user capability
	 */
	private function check_nonce() {
		ns_cloner()->check_permissions();
	}

	/**
	 * Send appropriate json response after checking the process manager for errors.
	 *
	 * @param mixed $success_data Data to pass to wp_send_json_success(), if successful.
	 */
	private function send_response( $success_data = null ) {
		if ( ! empty( ns_cloner()->process_manager->get_errors() ) ) {
			// Clear reports so it doesn't pop up the next time that they load the Cloner page.
			ns_cloner()->report->clear_all_reports();
			wp_send_json_error( ns_cloner()->process_manager->get_errors() );
		} else {
			wp_send_json_success( $success_data );
		}
	}

}
