<?php
/**
 * Usage logging and telemetry class.
 *
 * @package NS_Cloner
 */

if ( ! class_exists( 'NS_Cloner_Export_Analytics_Process' ) ) {
	require_once NS_CLONER_V4_PLUGIN_DIR . 'processes/class-ns-cloner-export-analytics-process.php';
}

/**
 * Class NS_Cloner_Analytics
 */
class NS_Cloner_Analytics {
	/**
	 * Available analytics modes
	 *
	 * @var array
	 */
	protected $user_modes;

	/**
	 * Currently saved analytics mode
	 *
	 * @var mixed|void
	 */
	protected $user_saved_mode;

	/**
	 * Analytics saved mode option name
	 *
	 * @var string
	 */
	protected $user_saved_mode_option_name = 'ns_cloner_analytics_mode';

	/**
	 * Datetime analytics mode was last saved
	 *
	 * @var mixed|void
	 */
	protected $user_last_saved_mode;

	/**
	 * Datetime analytics mode was last saved option name
	 *
	 * @var string
	 */
	protected $user_last_saved_mode_option_name = 'ns_cloner_analytics_mode_last_saved';

	/**
	 * DB table name for analytics entries
	 *
	 * @var string
	 */
	protected $db_analytics_table;

	/**
	 * Service client endpoint URL for sending analytics rows
	 *
	 * @var string
	 */
	protected $service_client_url = 'https://fba.atouchpoint.com/api/send-cloner-analytics';

	/**
	 * Singleton instance
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return NS_Cloner_Analytics|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * NS_Cloner_Analytics constructor.
	 */
	public function __construct() {
		$this->user_modes           = array(
			'share'             => array(
				'text' => __( 'Yes, Share', 'ns-cloner-site-copier' ),
			),
			'share_anonymously' => array(
				'text'    => __( 'Yes, Share Anonymously', 'ns-cloner-site-copier' ),
				'tooltip' => __(
					'Your domain name will be anonymized and we won\'t know 
				where the stats are from (but they are still helpful to us!) Thank you!',
					'ns-cloner-site-copier'
				),
			),
			'no_share'          => array(
				'text' => __( 'No, Dismiss', 'ns-cloner-site-copier' ),
			),
		);
		$this->user_saved_mode      = get_site_option( $this->user_saved_mode_option_name, get_option( $this->user_saved_mode_option_name ) );
		$this->user_last_saved_mode = get_site_option( $this->user_last_saved_mode_option_name, get_option( $this->user_last_saved_mode_option_name ) );
		global $wpdb;
		$this->db_analytics_table = $wpdb->base_prefix . 'ns_cloner_logs';
		add_action( 'ns_cloner_before_render_main', array( $this, 'maybe_show_analytics_settings_modal' ) );
		add_action( 'wp_ajax_ns_cloner_save_analytics_mode', array( $this, 'save_selected_mode_ajax' ) );
		add_filter( 'ns_cloner_site_tables', array( $this, 'exclude_analytics_db_table_from_cloner' ) );
	}

	/**
	 * Public method to get analytics modes
	 *
	 * @return array
	 */
	public function get_user_modes() {
		return $this->user_modes;
	}

	/**
	 * Public method to get saved analytics mode
	 *
	 * @return mixed
	 */
	public function get_user_saved_mode() {
		return $this->user_saved_mode;
	}

	/**
	 * Show analytics mode selection modal if the conditions pass
	 */
	public function maybe_show_analytics_settings_modal() {
		if ( $this->is_sharable() ) {
			// Do not show analytics settings modal.
			return;
		}

		$should_remind = 'no_share' === $this->user_saved_mode && $this->is_time_to_show_settings_modal();
		if ( empty( $this->user_saved_mode ) || $should_remind ) {
			ns_cloner()->render( 'analytics-settings-modal' );
		}
	}

	/**
	 * Check whether 3 months pasted since analytics mode was last saved
	 *
	 * @return bool
	 */
	protected function is_time_to_show_settings_modal() {
		if ( empty( $this->user_last_saved_mode ) ) {
			return true;
		}

		return strtotime( $this->user_last_saved_mode . ' + 3 month' ) <= time();
	}

	/**
	 * Ajax handle for saving analytics mode
	 */
	public function save_selected_mode_ajax() {
		check_ajax_referer( 'ns_cloner', 'nonce' );

		$mode = isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : '';
		if ( ! empty( $mode ) && in_array( $mode, array_keys( $this->user_modes ), true ) ) {
			$this->user_saved_mode      = $mode;
			$this->user_last_saved_mode = current_time( 'mysql' );
			update_site_option( $this->user_saved_mode_option_name, $this->user_saved_mode );
			update_site_option( $this->user_last_saved_mode_option_name, $this->user_last_saved_mode );

			if ( 'share' === $mode || 'share_anonymously' === $mode ) {
				$this->share_mode_activated();
			}

			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Get DB table name for analytics rows
	 *
	 * @return string
	 */
	public function get_db_log_table() {
		return $this->db_analytics_table;
	}

	/**
	 * Hook handle to remove db analytics table from cloning
	 *
	 * @param array $tables Default list of tables.
	 *
	 * @return mixed
	 */
	public function exclude_analytics_db_table_from_cloner( $tables ) {
		if ( count( $tables ) > 0 ) {
			foreach ( $tables as $key => $table_name ) {
				if ( $this->db_analytics_table === $table_name ) {
					unset( $tables[ $key ] );
					break;
				}
			}
		}

		return $tables;
	}

	/**
	 * Is current mode provides sharing data
	 *
	 * @return bool
	 */
	public function is_sharable() {
		return 'share' === $this->user_saved_mode || 'share_anonymously' === $this->user_saved_mode;
	}

	/**
	 * Actions on share mode activated
	 */
	protected function share_mode_activated() {
		$not_sent_results = $this->get_not_synced_cloner_results();
		if ( is_array( $not_sent_results ) && count( $not_sent_results ) > 0 ) {
			// Start background export to client.
			$background_export = new NS_Cloner_Export_Analytics_Process();
			foreach ( $not_sent_results as $result ) {
				$background_export->push_to_queue(
					array(
						'data' => $result,
					)
				);
				$background_export->save()->dispatch();
			}
		}
	}

	/**
	 * Process actions on cloner operations completed
	 *
	 * @param array  $processes Array of progress data.
	 * @param string $cloner_mode Slug of the clone mode used.
	 * @param float  $cloner_time_spent Seconds of cloning time.
	 * @param string $cloner_error Error message if present, or blank string.
	 */
	public function process_cloner_result( $processes, $cloner_mode, $cloner_time_spent, $cloner_error ) {
		$tables_count = null;
		$rows_count   = null;
		$files_count  = null;
		$users_count  = null;
		if ( empty( $cloner_error ) && count( $processes ) > 0 ) {
			switch ( $cloner_mode ) {
				case 'clone_teleport':
					if ( isset( $processes['teleport_tables'] ) && isset( $processes['teleport_tables']['completed'] ) ) {
						$tables_count = $processes['teleport_tables']['completed'];
					}
					if ( isset( $processes['teleport_rows'] ) && isset( $processes['teleport_rows']['completed'] ) ) {
						$rows_count = $processes['teleport_rows']['completed'];
					}
					if ( isset( $processes['teleport_files'] ) && isset( $processes['teleport_files']['completed'] ) ) {
						$files_count = $processes['teleport_files']['completed'];
					}
					if ( isset( $processes['teleport_users'] ) && isset( $processes['teleport_users']['completed'] ) ) {
						$users_count = $processes['teleport_users']['completed'];
					}
					break;
				case 'search_replace':
					if ( isset( $processes['tables_search'] ) && isset( $processes['tables_search']['completed'] ) ) {
						$tables_count = $processes['tables_search']['completed'];
					}
					if ( isset( $processes['rows_search'] ) && isset( $processes['rows_search']['completed'] ) ) {
						$rows_count = $processes['rows_search']['completed'];
					}
					break;
				default:
					if ( isset( $processes['tables'] ) && isset( $processes['tables']['completed'] ) ) {
						$tables_count = $processes['tables']['completed'];
					}
					if ( isset( $processes['rows'] ) && isset( $processes['rows']['completed'] ) ) {
						$rows_count = $processes['rows']['completed'];
					}
					if ( isset( $processes['files'] ) && isset( $processes['files']['completed'] ) ) {
						$files_count = $processes['files']['completed'];
					}
					if ( isset( $processes['users'] ) && isset( $processes['users']['completed'] ) ) {
						$users_count = $processes['users']['completed'];
					}
					break;
			}
		}
		$wp_data = array( 'is_multisite' => is_multisite() );
		if ( is_multisite() ) {
			$wp_data['is_subdomain'] = ! is_main_site();
		}

		// Prepare analytics row data.
		$data = array(
			'version'            => defined( 'NS_CLONER_PRO_VERSION' ) ? 'pro' : 'free',
			'is_success'         => empty( $cloner_error ),
			'clone_mode'         => $cloner_mode,
			'date'               => current_time( 'mysql' ),
			'time_spent_sec'     => (int) $cloner_time_spent,
			'tables_count'       => $tables_count,
			'rows_count'         => $rows_count,
			'files_count'        => $files_count,
			'users_count'        => $users_count,
			'replacements_count' => (int) ns_cloner()->report->get_report( '_replacements' ),
			'wp_data'            => wp_json_encode( $wp_data ),
			'is_synced'          => false,
		);

		if ( $this->is_sharable() ) {
			// Send data to remote service.
			$exported = $this->export_result_to_client( $data );
			if ( $exported ) {
				$data['is_synced'] = true;
			}
		}

		// Save analytics row in WP DB table.
		$this->save_result_to_db( $data );
	}

	/**
	 * Save
	 *
	 * @param array $data Cloning operation result to save.
	 *
	 * @return false|int
	 */
	protected function save_result_to_db( array $data ) {
		global $wpdb;
		$data['is_synced'] = $this->is_sharable();

		return $wpdb->insert( $this->db_analytics_table, $data );
	}

	/**
	 * Get cloner results from DB that were not synced to client
	 *
	 * @return array|null
	 */
	protected function get_not_synced_cloner_results() {
		global $wpdb;

		return $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL -- static table name.
			"SELECT * FROM {$this->db_analytics_table} WHERE is_synced = 0",
			ARRAY_A
		);
	}

	/**
	 * Export cloner results to analytics service
	 *
	 * @param array $data Record of cloning operation.
	 *
	 * @return bool
	 */
	public function export_result_to_client( array $data ) {
		if ( ! $this->is_sharable() ) {
			return false;
		}

		// If the data comes from background process it contains id of row saved in WP DB and is_synced - exclude it.
		if ( isset( $data['id'] ) ) {
			unset( $data['id'] );
		}
		if ( isset( $data['is_synced'] ) ) {
			unset( $data['is_synced'] );
		}

		$is_anonymous = 'share_anonymously' === $this->user_saved_mode;
		$domain       = network_site_url();
		if ( $is_anonymous ) {
			// Anonymize domain.
			$domain = wp_parse_url( $domain, PHP_URL_SCHEME ) . '://***.**';
		}
		$data['domain'] = $domain;

		// Send data to remote endpoint.
		$result = wp_remote_request(
			$this->service_client_url,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				'method'  => 'POST',
				'body'    => wp_json_encode( $data ),
			)
		);

		if ( is_wp_error( $result ) ) {
			return false;
		}

		$body_decoded = json_decode( $result['body'], true );

		return isset( $body_decoded['success'] ) && $body_decoded['success'];
	}
}

/**
 * Class singleton function
 *
 * @return NS_Cloner_Analytics|null
 */
function ns_cloner_analytics() {
	return NS_Cloner_Analytics::get_instance();
}

// Instantiate analytics.
ns_cloner_analytics();
