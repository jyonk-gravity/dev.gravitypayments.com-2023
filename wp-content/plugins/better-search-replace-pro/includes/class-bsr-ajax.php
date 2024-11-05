<?php

/**
 * AJAX-specific functionality for the plugin.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.2
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 * @author     Expanded Fronts, LLC
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

class BSR_AJAX {

	/**
	 * Initiate our custom ajax handlers.
	 * @access public
	 */
	public function init() {
		add_action( 'init', array( $this, 'define_ajax' ), 1 );
		add_action( 'init', array( $this, 'do_bsr_ajax' ), 2 );
		$this->add_ajax_actions();
	}

	/**
	 * Gets our custom endpoint.
	 * @access public
	 * @return string
	 */
	public static function get_endpoint() {
		return esc_url_raw( get_admin_url() . 'tools.php?page=better-search-replace&bsr-ajax=' );
	}

	/**
	 * Set BSR AJAX constant and headers.
	 * @access public
	 */
	public function define_ajax() {

		if ( isset( $_GET['bsr-ajax'] ) && ! empty( $_GET['bsr-ajax'] ) ) {

			// Define the WordPress "DOING_AJAX" constant.
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}

			// Prevent notices from breaking AJAX functionality.
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 );
			}

			// Send the headers.
			send_origin_headers();
			@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
			@header( 'X-Robots-Tag: noindex' );
			send_nosniff_header();
			nocache_headers();

		}

	}

	/**
	 * Check if we're doing AJAX and fire the related action.
	 * @access public
	 */
	public function do_bsr_ajax() {
		global $wp_query;

		if ( isset( $_GET['bsr-ajax'] ) && ! empty( $_GET['bsr-ajax'] ) ) {
			$wp_query->set( 'bsr-ajax', sanitize_text_field( $_GET['bsr-ajax'] ) );
		}

		if ( $action = $wp_query->get( 'bsr-ajax' ) ) {
			do_action( 'bsr_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	/**
	 * Adds any AJAX-related actions.
	 * @access public
	 */
	public function add_ajax_actions() {
		$actions = array(
			'process_search_replace',
			'process_backup',
			'process_import',
			'upload_import'
		);

		foreach ( $actions as $action ) {
			add_action( 'bsr_ajax_' . $action, array( $this, $action ) );
		}
	}

	/**
	 * Processes the search/replace form submitted by the user.
	 * @access public
	 */
	public function process_search_replace() {
		// Bail if not authorized.
		if ( ! check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			return;
		}

		// Initialize the DB class.
		$db   = new BSR_DB();
		$step = absint( $_REQUEST['bsr_step'] );
		$page = isset( $_REQUEST['bsr_page'] ) ? absint( $_REQUEST['bsr_page'] ) : 0;

		// Any operations that should only be performed at the beginning.
		if ( $step === 0 && $page === 0 ) {
			$args = array();
			parse_str( $_POST['bsr_data'], $args );

			// Build the arguments for this run.
			if ( ! isset( $args['select_tables'] ) || ! is_array( $args['select_tables'] ) ) {
				$args['select_tables'] = array();
			}

			$args = array(
				'select_tables'    => array_map( 'trim', $args['select_tables'] ),
				'case_insensitive' => isset( $args['case_insensitive'] ) ? $args['case_insensitive'] : 'off',
				'replace_guids'    => isset( $args['replace_guids'] ) ? $args['replace_guids'] : 'off',
				'dry_run'          => isset( $args['dry_run'] ) ? $args['dry_run'] : 'off',
				'search_for'       => isset( $args['search_for'] ) ? stripslashes( $args['search_for'] ) : '',
				'replace_with'     => isset( $args['replace_with'] ) ? stripslashes( $args['replace_with'] ) : '',
				'completed_pages'  => isset( $args['completed_pages'] ) ? absint( $args['completed_pages'] ) : 0,
				'is_import'        => isset( $args['is_import'] ) ? $args['is_import'] : false,
				'is_backup'        => isset( $args['is_backup'] ) ? $args['is_backup'] : false,
				'save_profile'     => isset( $args['profile_name'] ) ? $args['profile_name'] : '',
			);

			if ( $args['is_import'] || $args['is_backup'] ) {
				$args['select_tables'] = $db->get_tables( true );
				$args['dry_run']       = 'off';
			}

			$args['total_pages'] = isset( $args['total_pages'] ) ? absint( $args['total_pages'] ) : $db->get_total_pages( $args['select_tables'] );

			// Clear the results of the last run.
			delete_transient( 'bsr_results' );
			delete_option( 'bsr_data' );

			// Save the profile if necessary.
			if ( '' !== $args['save_profile'] ) {

				$profile                  = array();
				$profile_name             = $args['save_profile'];
				$profile[ $profile_name ] = array(
					'search_for'       => $args['search_for'],
					'replace_with'     => $args['replace_with'],
					'select_tables'    => $args['select_tables'],
					'case_insensitive' => $args['case_insensitive'],
					'replace_guids'    => $args['replace_guids'],
				);
				BSR_Admin::save_profile( $profile );
			}
		} else {
			$args = get_option( 'bsr_data' );
		}

		// Start processing data.
		if ( isset( $args['select_tables'][$step] ) ) {

			$result = $db->srdb( $args['select_tables'][$step], $page, $args );
			$this->append_report( $args['select_tables'][$step], $result['table_report'], $args );

			if ( false === $result['table_complete'] ) {
				$page++;
			} else {
				$step++;
				$page = 0;
			}

			// Check if isset() again as the step may have changed since last check.
			if ( isset( $args['select_tables'][$step] ) ) {

				$msg_tbl = esc_html( $args['select_tables'][$step] );

				if ( $args['is_import'] || $args['is_backup'] ) {
					$msg_tbl = str_replace( 'bsrtmp_', '', $msg_tbl );
				}

				$message = sprintf(
					__( 'Processing table %d of %d: %s', 'better-search-replace' ),
					$step + 1,
					count( $args['select_tables'] ),
					$msg_tbl
				);
			}

			$args['completed_pages']++;
			$percentage = $args['completed_pages'] / $args['total_pages'] * 100 . '%';

		} else {

			if ( $args['is_backup'] ) {
				$result = array(
					'step' 			=> 'done',
					'page' 			=> 0,
					'percentage'	=> '100%',
					'next_action' 	=> 'process_backup',
					'bsr_data' 		=> build_query( array( 'search_replace_complete' => 'true' ) )
				);

				echo json_encode( $result );
				exit;
			}

			if ( $args['is_import'] ) {
				$db->rename_temp_tables();
			}

			$db->maybe_update_site_url();

			$step = 'done';
			$percentage = '100%';
		}

		update_option( 'bsr_data', $args );

		// Store results in an array.
		$result = array(
			'step' 				=> $step,
			'page' 				=> $page,
			'percentage'		=> $percentage,
			'url' 				=> get_admin_url() . 'tools.php?page=better-search-replace&tab=bsr_search_replace&result=true',
			'bsr_data' 			=> build_query( $args )
		);

		if ( isset( $message ) ) {
			$result['message'] = $message;
		}

		if ( $args['is_import'] ) {
			$result['url'] = $result['url'] . '&import=true';
		}

		// Send output as JSON for processing via AJAX.
		echo json_encode( $result );
		exit;

	}

	/**
	 * Helper function for assembling the BSR Results.
	 * @access public
	 * @param  string 	$table 	The name of the table to append to.
	 * @param  array  	$report The report for that table.
	 * @param  array 	$args 	An array of arguements used for this run.
	 * @return boolean
	 */
	public function append_report( $table, $report, $args ) {

		// Bail if not authorized.
		if ( ! check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			return;
		}

		// Retrieve the existing transient.
		$results 		= get_transient( 'bsr_results' ) ? get_transient( 'bsr_results') : array();
		$num_results 	= get_option( 'bsr_max_results' ) ? absint( get_option( 'bsr_max_results' ) ) : 20;

		// Grab any values from the run args.
		$results['search_for'] 			= isset( $args['search_for'] ) ? $args['search_for'] : '';
		$results['replace_with'] 		= isset( $args['replace_with'] ) ? $args['replace_with'] : '';
		$results['dry_run'] 			= isset( $args['dry_run'] ) ? $args['dry_run'] : 'off';
		$results['case_insensitive'] 	= isset( $args['case_insensitive'] ) ? $args['case_insensitive'] : 'off';
		$results['replace_guids'] 		= isset( $args['replace_guids'] ) ? $args['replace_guids'] : 'off';

		// Sum the values of the new and existing reports.
		$results['change'] 	= isset( $results['change'] ) ? $results['change'] + $report['change'] : $report['change'];
		$results['updates'] = isset( $results['updates'] ) ? $results['updates'] + $report['updates'] : $report['updates'];

		// Append the table report, or create a new one if necessary.
		if ( isset( $results['table_reports'] ) && isset( $results['table_reports'][$table] ) ) {

			$results['table_reports'][$table]['change'] 	= $results['table_reports'][$table]['change'] + $report['change'];
			$results['table_reports'][$table]['updates'] 	= $results['table_reports'][$table]['updates'] + $report['updates'];
			$results['table_reports'][$table]['end'] 		= $report['end'];

			if ( count( $results['table_reports'][$table]['changes'] ) < $num_results ) {
				$results['table_reports'][$table]['changes'] = array_merge( $results['table_reports'][$table]['changes'], $report['changes'] );
			}

		} else {
			$results['table_reports'][$table] = $report;
		}

		// Count the number of tables.
		$results['tables'] = count( $results['table_reports'] );

		// Update the transient.
		if ( ! set_transient( 'bsr_results', $results, DAY_IN_SECONDS ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Processes a database backup.
	 * @access public
	 */
	public function process_backup() {

		// Bail if not authorized.
		if ( ! check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			return;
		}

		$args = array();
		parse_str( $_REQUEST['bsr_data'], $args );

		// Initialize the DB class.
		$db 						= new BSR_DB();
		$step 						= isset( $_REQUEST['bsr_step'] ) ? absint( $_REQUEST['bsr_step'] ) : 0;
		$page 						= isset( $_REQUEST['bsr_page'] ) ? absint( $_REQUEST['bsr_page'] ) : 0;
		$tables 					= isset( $args['tables'] ) ? $args['tables'] : $db->get_tables();
		$total_pages 				= isset( $args['total_pages'] ) ? absint( $args['total_pages'] ) : $db->get_total_pages( $tables );
		$completed_pages 			= isset( $args['completed_pages'] ) ? absint( $args['completed_pages'] ) : 0;
		$profile 					= isset( $args['bsr_backup_profile'] ) ? $args['bsr_backup_profile'] : false;
		$search_replace_complete 	= isset( $args['search_replace_complete'] ) ? $args['search_replace_complete'] : false;

		$args = array(
			'tables' 					=> $tables,
			'total_pages' 				=> $total_pages,
			'completed_pages' 			=> $completed_pages,
			'profile'					=> $profile,
			'search_replace_complete' 	=>  $search_replace_complete,
		);

		// Any operations that should only be performed at the beginning.
		if ( $step === 0 && $page === 0 ) {

			// Cleanup from any past failures.
			BSR_Admin::delete_file();

			// Generate a new salt for this backup.
			$db->generate_salt();

			// Maybe create temp tables and run a search/replace.
			if ( $args['profile'] && ! $args['search_replace_complete'] ) {

				$profile = $args['profile'];

				$profiles = BSR_Admin::get_profiles();

				if ( isset( $profiles[$profile] ) ) {
					global $wpdb;

					foreach ( $tables as $table ) {
						$table = esc_sql( $table );
						$wpdb->query( "CREATE TABLE bsrtmp_$table LIKE $table;" );
						$wpdb->query( "INSERT INTO bsrtmp_$table SELECT * FROM $table;" );
					}

					$profiles[$profile]['is_backup'] = true;

					$result = array(
						'step'			=> 'done',
						'page'			=> '0',
						'next_action'	=> 'process_search_replace',
						'message'		=> __( 'Starting search/replace...', 'better-search-replace' ),
						'bsr_data'		=> build_query( $profiles[$profile] )
					);

					echo json_encode( $result );
					exit;
				}
			} elseif ( $args['search_replace_complete'] ) {
				$tables = $args['tables'] = $db->get_tables( true );
			}

		}

		if ( isset( $tables[$step] ) ) {

			if ( $args['search_replace_complete'] ) {
				$rename = true;
			} else {
				$rename = false;
			}

			$backup = $db->backup_table( $tables[$step], $page, $rename );

			if ( false == $backup['table_complete'] ) {
				$page++;
			} else {
				$step++;
				$page = 0;
			}

			if ( isset( $tables[$step] ) ) {
				$msg_tbl = $tables[$step];

				if ( $rename ) {
					$msg_tbl = str_replace( 'bsrtmp_', '', $msg_tbl );
				}

				$message = sprintf( __( 'Backing up table: "%s"...', 'better-search-replace' ), $msg_tbl );
			}

			$args['completed_pages']++;
			$percentage = $args['completed_pages'] / $total_pages * 100 . '%';

		} else {
			$step 			= 'done';
			$percentage 	= '100%';

			if ( '' !== get_option( 'bsr_enable_gzip' ) ) {
				BSR_Admin::compress_file( $db->get_file_path() );
			}

		}

		$download_url = wp_nonce_url( get_admin_url() . 'admin-post.php?action=bsr_download_backup', 'bsr_download_backup', 'bsr_download_backup' );

		// Store results in an array.
		$result = array(
			'step' 				=> $step,
			'page' 				=> $page,
			'percentage'		=> $percentage,
			'url' 				=> $download_url,
			'bsr_data' 			=> build_query( $args ),
		);

		if ( isset( $message ) ) {
			$result['message'] = $message;
		}

		// Send output as JSON for processing via AJAX.
		echo json_encode( $result );
		exit;
	}

	/**
	 * Processes a database import.
	 * @access public
	 */
	public function process_import() {

		// Bail if not authorized.
		if ( ! check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			return;
		}

		if ( is_array( $_POST['bsr_data'] ) ) {
			$args = $_POST['bsr_data'];
		} else {
			$args = array();
			parse_str( $_POST['bsr_data'], $args );
		}

		$db 				= new BSR_DB();
		$page 				= isset( $_REQUEST['bsr_page'] ) ? absint( $_REQUEST['bsr_page'] ) : 0;
		$file 				= isset( $args['file'] ) ? $args['file'] : '';
		$total_pages 		= isset( $args['total_pages'] ) ? absint( $args['total_pages'] ) : $db->get_total_pages_from_file( $file );
		$completed_pages 	= isset( $args['completed_pages'] ) ? absint( $args['completed_pages'] ) : 0;
		$profile 			= isset( $args['profile'] ) ? $args['profile'] : '';
		$import 			= $db->run_import( $file, $page );

		$completed_pages++;

		if ( false == $import['import_complete'] ) {
			$page++;
			$percentage = $completed_pages / $total_pages * 100 . '%';
			$step = 0;
		} else {
			$step = 'done';
			$page = 'done';
			$percentage ='100%';

			// Remove import file.
			BSR_Admin::delete_file();
		}

		$bsr_data = array(
			'total_pages' 		=> $total_pages,
			'completed_pages' 	=> $completed_pages,
			'file' 				=> $file,
			'profile' 			=> $profile
		);

		$result = array(
			'step'				=> $step,
			'page' 				=> $page,
			'percentage' 		=> $percentage,
			'url'				=> get_admin_url() . 'tools.php?page=better-search-replace&import=true&result=true',
			'bsr_data'			=> $bsr_data
		);

		if ( 'done' === $step ) {

			// Maybe run a search/replace.
			if ( 'undefined' !== $profile && '' !== $profile ) {

				$profiles = BSR_Admin::get_profiles();

				if ( isset( $profiles[$profile] ) ) {
					$profiles[$profile]['is_import'] = true;
					$result['next_action'] 	= 'process_search_replace';
					$result['message'] 		= __( 'Running search/replace...', 'better-search-replace' );
					$result['bsr_data'] 	= build_query( $profiles[$profile] );
				} else {
					// Rename the temporary tables.
					$db->rename_temp_tables();
				}

			} else {
				// Rename the temporary tables.
				$db->rename_temp_tables();
			}

		}

		echo json_encode( $result );
		exit;
	}

	/**
	 * Handles file uploads via AJAX.
	 * @access public
	 */
	public function upload_import() {
		// Bail if not authorized.
		if ( ! check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			return;
		}

		BSR_Admin::delete_file();
		$db = new BSR_DB;
		$db->generate_salt();

		$file        = $db->get_file_path();
		$upload_dir  = wp_upload_dir();
		$manual_file = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'bsr_db_backup.sql';

		// Figure out which file we want to import.
		if ( isset( $_FILES['bsr_import_file' ] ) ) {

			$ext = substr( $_FILES['bsr_import_file']['name'], -3 );

			if ( 'sql' === $ext || '.gz' === $ext ) {
				$temp = $_FILES['bsr_import_file']['tmp_name'];
				$dest = $file;
				$upload_method 	= 'ajax';

				if ( '.gz' === $ext ) {
					$dest = $dest . '.gz';
				}

				move_uploaded_file( $temp, $dest );

				if ( '.gz' === $ext ) {
					BSR_Admin::decompress_file( $dest, $file );
				}
			}

		} elseif ( file_exists( $manual_file ) ) {
			$upload_method = 'manual';
			$file = $manual_file;
		} else {
			$file = 'not_found';
			$upload_method = 'none';
		}

		$result = array(
			'file' 			=> $file,
			'upload_method' => $upload_method
		);

		if ( isset( $_POST['profile'] ) && ! empty( $_POST['profile' ] ) ) {
			$result['profile'] = sanitize_text_field( $_POST['profile'] );
		}

		// Return results as JSON for AJAX.
		echo json_encode( $result );
		exit;
	}

}

$bsr_ajax = new BSR_AJAX;
$bsr_ajax->init();
