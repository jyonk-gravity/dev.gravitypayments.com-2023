<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Registers styles and scripts, adds the custom administration page,
 * and processes user input on the "search/replace" form.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.0.0
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 * @author     Expanded Fronts, LLC
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

class BSR_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $better_search_replace    The ID of this plugin.
	 */
	private $better_search_replace;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $better_search_replace       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $better_search_replace, $version ) {
		$this->better_search_replace = $better_search_replace;
		$this->version = $version;
	}

	/**
	 * Register any CSS and JS used by the plugin.
	 * @since    1.0.0
	 * @access 	 public
	 * @param    string $hook Used for determining which page(s) to load our scripts.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'tools_page_better-search-replace' === $hook ) {
            $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'better-search-replace', BSR_URL . "assets/css/better-search-replace$min.css", array(), $this->version, 'all' );
			wp_enqueue_style( 'jquery-style', BSR_URL . 'assets/css/jquery-ui.min.css', array(), $this->version, 'all' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'better-search-replace', BSR_URL . "assets/js/better-search-replace$min.js", array( 'jquery' ), $this->version, true );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );

			wp_localize_script( 'better-search-replace', 'bsr_object_vars', array(
				'page_size' 	=> get_option( 'bsr_page_size' ) ? absint( get_option( 'bsr_page_size' ) ) : 20000,
				'max_results'	=> get_option( 'bsr_max_results' ) ? absint( get_option( 'bsr_max_results' ) ) : 60,
				'endpoint' 		=> BSR_AJAX::get_endpoint(),
				'ajax_nonce' 	=> wp_create_nonce( 'bsr_ajax_nonce' ),
				'no_search' 	=> __( 'No search string was defined, please enter a URL or string to search for.', 'better-search-replace' ),
				'no_tables' 	=> __( 'Please select the tables that you want to update.', 'better-search-replace' ),
				'unknown' 		=> __( 'An error occurred processing your request. Try decreasing the "Max Page Size", or contact support.', 'better-search-replace' ),
				'processing'	=> __( 'Processing...', 'better-search-replace' )
			) );

		}
	}

	/**
	 * Register any menu pages used by the plugin.
	 * @since  1.0.0
	 * @access public
	 */
	public function bsr_menu_pages() {
		$cap = apply_filters( 'bsr_capability', 'manage_options' );
		add_submenu_page( 'tools.php', __( 'Better Search Replace Pro', 'better-search-replace' ), __( 'Better Search Replace Pro', 'better-search-replace' ), $cap, 'better-search-replace', array( $this, 'bsr_menu_pages_callback' ) );
	}

	/**
	 * The callback for creating a new submenu page under the "Tools" menu.
	 * @access public
	 */
	public function bsr_menu_pages_callback() {
		require_once BSR_PATH . 'templates/bsr-dashboard.php';
	}

	/**
	 * Renders the result or error onto the better-search-replace admin page.
	 * @access public
	 */
	public static function render_result() {

		if ( isset( $_GET['import'] ) ) {
			echo '<div class="updated bsr-updated" style="display: none;"><p>' . __( 'Database imported successfully.', 'better-search-replace' ) . '</p></div>';
		}

		if ( isset( $_GET['result'] ) && $result = get_transient( 'bsr_results' ) ) {

			if ( isset( $result['dry_run'] ) && $result['dry_run'] === 'on' ) {
				$msg = sprintf( __( '<p><strong>DRY RUN:</strong> <strong>%d</strong> tables were searched, <strong>%d</strong> cells were found that need to be updated, and <strong>%d</strong> changes were made.</p><p><a href="%s" class="thickbox" title="Dry Run Details">Click here</a> for more details, or use the form below to run the search/replace.</p>', 'better-search-replace' ),
					$result['tables'],
					$result['change'],
					$result['updates'],
					get_admin_url() . 'admin-post.php?action=bsr_view_details&TB_iframe=true&width=800&height=500'
				);
			} else {
				$msg = sprintf( __( '<p>During the search/replace, <strong>%d</strong> tables were searched, with <strong>%d</strong> cells changed in <strong>%d</strong> updates.</p><p><a href="%s" class="thickbox" title="Search/Replace Details">Click here</a> for more details.</p>', 'better-search-replace' ),
					$result['tables'],
					$result['change'],
					$result['updates'],
					get_admin_url() . 'admin-post.php?action=bsr_view_details&TB_iframe=true&width=800&height=500'
				);
			}

			echo '<div class="updated bsr-updated" style="display: none;">' . $msg . '</div>';

		}

	}

	/**
	 * Prefills the given value on the search/replace page (dry run, live run, from profile).
	 * @access public
	 * @param  string $value The value to check for.
	 * @param  string $type  The type of the value we're filling.
	 */
	public static function prefill_value( $value, $type = 'text' ) {

		// Grab the correct data to prefill.
		if ( isset( $_GET['result'] ) && get_transient( 'bsr_results' ) ) {
			$values = get_transient( 'bsr_results' );
		} elseif ( get_option( 'bsr_profiles' ) && isset( $_GET['bsr_profile'] ) ) {

			$profile  = stripslashes( $_GET['bsr_profile'] );
			$profiles = get_option( 'bsr_profiles' );

			if ( isset( $profiles[$profile] ) ) {
				$values = $profiles[$profile];
			} else {
				$values = array();
			}

		} else {
			$values = array();
		}

		// Prefill the value.
		if ( isset( $values[$value] ) ) {

			if ( 'checkbox' === $type && 'on' === $values[$value] ) {
				echo 'checked';
			} else {
				echo str_replace( '#BSR_BACKSLASH#', '\\', esc_attr( htmlentities( $values[$value] ) ) );
			}

		}

	}

	/**
	 * Loads the tables available to run a search replace, prefilling if already
	 * selected the tables.
	 * @access public
	 */
	public static function load_tables() {

		// Get the tables and their sizes.
		$tables 	= BSR_DB::get_tables();
		$sizes 		= BSR_DB::get_sizes();
		$profiles 	= get_option( 'bsr_profiles' );

		echo '<select id="bsr-table-select" name="select_tables[]" multiple="multiple" style="">';

		foreach ( $tables as $table ) {

			// Try to get the size for this specific table.
			$table_size = isset( $sizes[$table] ) ? $sizes[$table] : '';

			if ( isset( $_GET['result'] ) && get_transient( 'bsr_results' ) ) {

				$result = get_transient( 'bsr_results' );

				if ( isset( $result['table_reports'][$table] ) ) {
					echo "<option value='$table' selected>$table $table_size</option>";
				} else {
					echo "<option value='$table'>$table $table_size</option>";
				}

			} elseif ( isset( $_GET['bsr_profile'] ) && 'create_new' !== $_GET['bsr_profile'] ) {

				$profile        = stripslashes( $_GET['bsr_profile'] );
				$profile_tables = array_flip( $profiles[$profile]['select_tables'] );

				if ( isset( $profile_tables[$table] ) ) {
					echo "<option value='$table' selected>$table $table_size</option>";
				} else {
					echo "<option value='$table'>$table $table_size</option>";
				}

			} else {
				echo "<option value='$table'>$table $table_size</option>";
			}

		}

		echo '</select>';

	}

	/**
	 * Loads the result details (via Thickbox).
	 * @access public
	 */
	public function load_details() {

		if ( get_transient( 'bsr_results' ) ) {
			$results	    = get_transient( 'bsr_results' );
			$min 			= ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';
			$bsr_styles	    = BSR_URL . 'assets/css/better-search-replace.css?v=' . BSR_VERSION;
			$table		    = isset( $_GET['table'] ) ? esc_attr( $_GET['table'] ) : '';

			?>
			<link href="<?php echo esc_url( get_admin_url( null, '/css/common' . $min . '.css' ) ); ?>" rel="stylesheet" type="text/css" />
			<link href="<?php echo esc_url( $bsr_styles ); ?>" rel="stylesheet" type="text/css">

			<?php

				if ( isset( $_GET['changes'] ) && isset( $results['table_reports'][$table]['changes'] ) ) {

					printf( '<p id="bsr-back-to-overview"><strong><a href="%s">%s</a></strong></p>', get_admin_url() . 'admin-post.php?action=bsr_view_details', __( '<-- Back to overview', 'better-search-replace' ) );

					echo '<div id="bsr-details-view-wrap"><table id="bsr-details-view" class="widefat">';

					$search_for   = str_replace( '#BSR_BACKSLASH#', '\\', htmlentities( $results['search_for'] ) );
					$replace_with = str_replace( '#BSR_BACKSLASH#', '\\', htmlentities( $results['replace_with'] ) );

					foreach ( $results['table_reports'][$table]['changes'] as $change ) {

						// Escape HTML
						$from_str 	= htmlentities( $change['from'] );

						// Highlight the changes.
						if ( true == $results['case_insensitive'] ) {
							$from_str 	= str_ireplace( $search_for, '<span class="bsr-old-val">' . $search_for . '</span>', $from_str );
						} else {
							$from_str 	= str_replace( $search_for, '<span class="bsr-old-val">' . $search_for . '</span>', $from_str );
						}

						$to_str = str_replace( '<span class="bsr-old-val">' . $search_for . '</span>', '<span class="bsr-new-val">' . $replace_with . '</span>', $from_str );

						echo '<tr class="bsr-row-desc"><td><strong>' . sprintf( __( 'Row %d, Column \'%s\'', 'better-search-replace' ), $change['row'], $change['column'] ) . '</strong></td></tr>';
						echo '<tr><td class="bsr-change">' . $from_str . '</td><td class="bsr-change">' . $to_str . '</td></tr>';
					}
					echo '</table></div>';
				} else {
					?>
						<div style="padding: 32px; background-color: var(--color-white); min-height: 100%;">
							<table id="bsr-results-table" class="widefat">
								<thead>
									<tr><th class="bsr-first"><?php _e( 'Table', 'better-search-replace' ); ?></th><th class="bsr-second"><?php _e( 'Changes Found', 'better-search-replace' ); ?></th><th class="bsr-third"><?php _e( 'Rows Updated', 'better-search-replace' ); ?></th><th class="bsr-fourth"><?php _e( 'Time', 'better-search-replace' ); ?></th></tr>
								</thead>
								<tbody>
								<?php
									foreach ( $results['table_reports'] as $table_name => $report ) {
										$time = $report['end'] - $report['start'];

										if ( $report['change'] != 0 ) {
											$report['change'] = '<strong>' . $report['change'] . '</strong>';

											if ( is_array( $report['changes'] ) ) {
												$report['change'] .= ' <a href="?action=bsr_view_details&changes=true&table=' . $table_name . '">[' . __( 'View', 'better-search-replace' ) . ']</a>';
											}

										}

										if ( $report['updates'] != 0 ) {
											$report['updates'] = '<strong>' . $report['updates'] . '</strong>';
										}

										if ( 'bsrtmp_' === substr( $table_name, 0, 7 ) ) {
											$table_name = substr( $table_name, 7 );
										}

										echo '<tr><td class="bsr-first">' . $table_name . '</td><td class="bsr-second">' . $report['change'] . '</td><td class="bsr-third">' . $report['updates'] . '</td><td class="bsr-fourth">' . round( $time, 3 ) . __( ' seconds', 'better-search-replace' ) . '</td></tr>';
									}
								?>
								</tbody>
							</table>
						</div>

					<?php
				}
		}
	}

	/**
	 * Loads a profile.
	 * @access public
	 */
	public function process_load_profile() {
		$profiles = get_option( 'bsr_profiles' ) ? get_option( 'bsr_profiles' ) : array();
		$profile  = stripslashes( $_POST['bsr_profile'] );

		if ( isset( $profiles[ $profile ] ) ) {
			$url = get_admin_url() . 'tools.php?page=better-search-replace&bsr_profile=' . rawurlencode( $profile );
		} else {
			$url = get_admin_url() . 'tools.php?page=better-search-replace&bsr_profile=create_new';
 		}

 		wp_redirect( $url );
 		exit;
	}

	/**
	 * Deletes a profile.
	 * @access public
	 */
	public function process_delete_profile() {
		$profiles = get_option( 'bsr_profiles' );
		$profile  = stripslashes( $_POST['bsr_profile'] );

		if ( isset( $profiles[ $profile ] ) ) {
			unset( $profiles[ $profile ] );
		}
		update_option( 'bsr_profiles', $profiles );

		wp_redirect( get_admin_url() . 'tools.php?page=better-search-replace' );
		exit;
	}

	/**
	 * Gets an array of saved profiles.
	 * @access public
	 * @return array
	 */
	public static function get_profiles() {
		return get_option( 'bsr_profiles' ) ? get_option( 'bsr_profiles' ) : array();
	}

	/**
	 * Saves a profile to the options.
	 * @access public
	 * @param  array $profile An array containing the name and options of the profile.
	 * @return boolean
	 */
	public static function save_profile( $profile ) {
		$profiles 	= get_option( 'bsr_profiles' ) ? get_option( 'bsr_profiles' ) : array();
		$updated 	= array_merge( $profiles, $profile );
		return update_option( 'bsr_profiles', $updated );
	}

	/**
	 * Updates profiles to the current version.
	 * @access public
	 */
	public function upgrade_profiles() {
		if ( get_option( 'bsr_profile_version' ) !== '1.2' ) {

			$profiles = BSR_Admin::get_profiles();

			if ( empty( $profiles ) ) {
				return;
			}

			foreach ( $profiles as $profile_name => $values ) {

				if ( isset( $profiles[$profile_name]['search'] ) ) {
					$profiles[$profile_name]['search_for'] = $profiles[$profile_name]['search'];
					unset( $profiles[$profile_name]['search'] );
				}

				if ( isset( $profiles[$profile_name]['replace'] ) ) {
					$profiles[$profile_name]['replace_with'] 	= $profiles[$profile_name]['replace'];
					unset( $profiles[$profile_name]['replace'] );
				}

				if ( isset( $profiles[$profile_name]['tables'] ) ) {
					$profiles[$profile_name]['select_tables'] 	= $profiles[$profile_name]['tables'];
					unset( $profiles[$profile_name]['tables'] );
				}

				if ( isset( $profiles[$profile_name]['case_insensitive'] ) && $profiles[$profile_name]['case_insensitive'] == 1 ) {
					$profiles[$profile_name]['case_insensitive'] = 'on';
				} else {
					$profiles[$profile_name]['case_insensitive'] = 'off';
				}

				if ( isset( $profiles[$profile_name]['replace_guids'] ) && $profiles[$profile_name]['replace_guids'] == 1 ) {
					$profiles[$profile_name]['replace_guids'] = 'on';
				} else {
					$profiles[$profile_name]['replace_guids'] = 'off';
				}

			}

			update_option( 'bsr_profiles', $profiles );
			add_option( 'bsr_profile_version', '1.2' );
		}
	}

	/**
	 * Handles automatic updates.
	 * @access public
	 */
	public function updater() {

		// Get the license key from the DB.
		$license = trim( get_option( 'bsr_license_key' ) );

		// Setup the updater.
		$updater = new EDD_SL_Plugin_Updater( BSR_API_URL, BSR_FILE, array(
			'version' 	=> $this->version,
			'license' 	=> $license,
			'item_name' => BSR_NAME,
			'author' 	=> 'Expanded Fronts, LLC'
			)
		);

	}

	/**
	 * Registers our settings in the options table.
	 * @access public
	 */
	public function register_option() {
		register_setting( 'bsr_settings_fields', 'bsr_license_key', array( $this, 'sanitize_license' ) );
		register_setting( 'bsr_settings_fields', 'bsr_page_size', 'absint' );
		register_setting( 'bsr_settings_fields', 'bsr_max_results', 'absint' );
		register_setting( 'bsr_settings_fields', 'bsr_enable_gzip', 'esc_attr' );
	}


	public function upgrade_routine() {
		$old_version = get_option( 'bsr_version' );

		if ( $old_version !== BSR_VERSION ) {
			if ( version_compare( '1.3.6', $old_version, '>' ) ) {				
				$upload_dir  = wp_upload_dir();
				$old_file = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'bsr_db_backup.sql';
				@unlink( $old_file );
				@unlink( $old_file . '.gz' );
			}
	
			update_option( 'bsr_version', BSR_VERSION );
		}
	}

	public function sanitize_license( $new ) {
		$old = get_option( 'bsr_license_key' );

		if ( ( $old && $old != $new ) || ! $old ) {
			delete_option( 'bsr_license_status' );

			if( isset( $_POST['bsr_license_deactivate'] ) ) {
				return false;
			}

			$this->activate_license( $new );
		}

		return $new;
	}

	/**
	 * Activates the license.
     *
	 * @param bool|string $new_license New license key to activate (optional).
	 */
	public function activate_license( $new_license = false ) {
		if( ! isset( $_POST['bsr_license_activate'] ) && ! $new_license ) {
			return;
		}

		if( ! $new_license && ! check_admin_referer( 'bsr_license_nonce', 'bsr_license_nonce' ) ) {
			return;
		}

		$license = trim( get_option( 'bsr_license_key' ) );
		if ( $new_license ) {
			$license = trim( $new_license );
		}

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( BSR_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( BSR_API_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! isset( $license_data->license ) ) {
			return false;
		}

		// $license_data->license will be either "valid" or "invalid"
		update_option( 'bsr_license_status', $license_data->license );

        return $license_data->license === 'valid';
	}

	/**
	 * Deactivates the license.
	 * @access public
	 */
	public function deactivate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['bsr_license_deactivate'] ) ) {

			// run a quick security check
		 	if( ! check_admin_referer( 'bsr_license_nonce', 'bsr_license_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( 'bsr_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action'=> 'deactivate_license',
				'license' 	=> $license,
				'item_name' => urlencode( BSR_NAME ), // the name of our product in EDD
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( BSR_API_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if ( $license_data->license == 'deactivated' ) {
				delete_option( 'bsr_license_status' );
				delete_option( 'bsr_license_key' );
			}
		}
	}

	/**
	 * Downloads the backup file.
	 * @access public
	 */
	public function download_backup() {
		$cap = apply_filters( 'bsr_capability', 'manage_options' );
		if ( ! current_user_can( $cap ) ) {
			return;
		}

		$db   = new BSR_DB();
		$file = $db->get_file_path();

		if ( '' !== get_option( 'bsr_enable_gzip' ) && file_exists( $file . '.gz' ) ) {
			$file = $file . '.gz';
			$name = 'bsr_db_backup.sql.gz';
		} else {
			$name = 'bsr_db_backup.sql';
		}

		nocache_headers();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . $name . '"' );

		readfile( $file );
		self::delete_file();
		die();
	}

	/**
	 * Downloads the system info file for support.
	 * @access public
	 */
	public function download_sysinfo() {
		check_admin_referer( 'bsr_download_sysinfo', 'bsr_sysinfo_nonce' );

		$cap = apply_filters( 'bsr_capability', 'manage_options' );
		if ( ! current_user_can( $cap ) ) {
			return;
		}

		nocache_headers();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="bsr-system-info.txt"' );

		echo wp_strip_all_tags( $_POST['bsr-sysinfo'] );
		die();
	}

	/**
	 * Compresses a file.
	 * @access public
	 *
	 * @param 	string 	$file 	The filename to compress.
	 * @param 	string 	$level 	The compression level to use.
	 * @return 	string|boolean
	 */
	public static function compress_file( $file, $level = 9 ) {
		$dest 	= $file . '.gz';
		$mode 	= 'wb' . $level;
		$error 	= false;

		if ( $fp_out = gzopen( $dest, $mode ) ) {

	        if ( $fp_in = fopen( $file,'rb' ) ) {
	        	while ( ! feof( $fp_in ) ) {
	                gzwrite( $fp_out, fread( $fp_in, 1024 * 512 ) );
	        	}
	            fclose( $fp_in );
	        } else {
	            $error = true;
	        }

	        gzclose( $fp_out );
		} else {
			$error = true;
		}

		if ( $error ) {
			return false;
		}

		return $dest;
	}

	/**
	 * Uncompress a file.
	 * @access public
	 *
	 * @param  string $file The file to uncompress.
	 * @param  string $dest The destination of the uncompressed file.
	 * @return string|boolean
	 */
	public static function decompress_file( $file, $dest ) {

		$error = false;

		if ( $fp_in = gzopen( $file, 'rb' ) ) {

			if ( $fp_out = fopen( $dest, 'w' ) ) {
				while( ! gzeof( $fp_in ) ) {
					$string = gzread( $fp_in, '4096' );
					fwrite( $fp_out, $string, strlen( $string ) );
				}
				fclose( $fp_out );
			} else {
				$error = true;
			}

			gzclose( $fp_in );
		} else {
			$error = true;
		}

		if ( $error ) {
			return false;
		}

		return $dest;
	}

	/**
	 * Deletes the current backup file(s) if they exist,
	 * as well as the salt associated with them.
	 *
	 * @return bool
	 */
	public static function delete_file() {
		$db = new BSR_DB();

		// Only delete if we already know the full filename.
		if ( ! $db->get_salt() ) {
			return false;
		}

		$file   = $db->get_file_path();
		$result = false;

		if ( file_exists( $file ) ) {
			$result = unlink( $file );
		}

		if ( file_exists( $file . '.gz' ) ) {
			$result = unlink( $file . '.gz' );
		}

		$db->delete_salt();

		return $result;
	}

}
