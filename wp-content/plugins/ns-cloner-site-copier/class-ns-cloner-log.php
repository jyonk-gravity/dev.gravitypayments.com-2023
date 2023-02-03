<?php
/**
 * Cloner Logging class.
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// We're going to use standard filesystem calls for logging, but phpcs will complain.
// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fopen
// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fwrite
// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fclose

/**
 * NS_Cloner_Log class.
 *
 * Utility class for creating debug logs while running cloning processes.
 */
class NS_Cloner_Log {

	/**
	 * Filepath to summary log
	 *
	 * @var string
	 */
	private $log_file;

	/**
	 * File pointer for current log from fopen
	 *
	 * @var resource
	 */
	private $log_handle;

	/**
	 * Whether 'all' hook has been hooked
	 *
	 * @var bool
	 */
	private $log_all_hooked = false;

	/**
	 * List of keys for verbose/sensitive data that should not be logged
	 *
	 * @var array
	 */
	public $hidden_keys = array();

	/**
	 * Regex for matching log filenames.
	 *
	 * @var string
	 */
	public $log_file_pattern = '/ns-cloner-(\d{8})-(\d{6})(?:-\w{8})?\.html/';

	/**
	 * A random hex color code defined for the current session, in order to differentiate
	 * different requests in the log where there may by multiple async requests weaving
	 * in and out of each other.
	 *
	 * @var string
	 */
	private $random_color;

	/**
	 * NS_Cloner_Log constructor.
	 */
	public function __construct() {
		// Prevent Kint from grouping all data in debug bar at bottom of screen.
		Kint\Renderer\RichRenderer::$folder = false;
	}

	/**
	 * Determine if logging is enabled or not
	 *
	 * @return bool
	 */
	public function is_debug() {
		$debug = (bool) ns_cloner_request()->get( 'debug' );
		return apply_filters( 'ns_cloner_is_debug', $debug );
	}

	/**
	 * Generate a new log filename
	 *
	 * @return string
	 */
	public function generate_file() {
		// Add a hash to the filename, to make it super hard to crawl for logs.
		$hash      = strtolower( wp_generate_password( 8, false ) );
		$timestamp = gmdate( 'Ymd-His' );
		return NS_CLONER_LOG_DIR . "ns-cloner-{$timestamp}-{$hash}.html";
	}

	/**
	 * Set the log file and open append pointer/handle
	 *
	 * @param string $filename Path to log file.
	 */
	public function set_file( $filename ) {
		$this->log_file = apply_filters( 'ns_cloner_log_file', $filename );
		ns_cloner_request()->set( 'log_file', $this->log_file );
		ns_cloner_request()->save();
		// Open file pointer for logging.
		if ( ! empty( $this->log_file ) && is_writeable( dirname( $this->log_file ) ) ) {
			$this->log_handle = fopen( $this->log_file, 'a' );
		}
	}

	/**
	 * Start logging - define the log file path, and add header if needed
	 *
	 * This should be called at the beginning of any process where logging
	 * should be performed, because log() function calls made before start()
	 * will be ignored. This enables easy control of when logging should happen
	 * and shouldn't - i.e. not causing logging during validation, other misc
	 * ajax requests, etc.
	 */
	public function start() {
		// Make sure debug/logging is on.
		if ( ! $this->is_debug() ) {
			return;
		}
		// Set up log if needed (don't bother if start() was already called in this session).
		if ( ! is_resource( $this->log_handle ) ) {
			// Define the log file.
			if ( ns_cloner_request()->get( 'log_file' ) ) {
				// Set log file to the saved request value, if present.
				// (Enables using the same log file between background processes).
				$this->set_file( ns_cloner_request()->get( 'log_file' ) );
			} else {
				// Define the default log file if it's not yet saved.
				$this->set_file( $this->generate_file() );
				$this->header();
			}
			// Set a random hex color for this session's log messages.
			$this->random_color = '#';
			foreach ( array( 'r', 'b', 'g' ) as $col ) {
				$hex    = dechex( wp_rand( 0, 255 ) );
				$padded = str_pad( $hex, 2, '0', STR_PAD_LEFT );
				// Concatenate each color.
				$this->random_color .= $padded;
			}
			// Define keys of sensitive data that should not be included in log.
			$this->hidden_keys = apply_filters( 'ns_cloner_hidden_keys', array() );
		}
		// Check if log needs split due to size (larger than 5MB) for performance.
		if ( is_resource( $this->log_handle ) && filesize( $this->log_file ) > 5 * 1024 * 1024 ) {
			$old = $this->log_file;
			$new = $this->generate_file();
			$this->log( 'CONTINUING IN: <a href="' . $this->get_url( $new ) . '"></a>' . $new . '</a>' );
			$this->end( false );
			$this->set_file( $new );
			$this->header();
			$this->log( 'CONTINUING FROM: <a href="' . $this->get_url( $old ) . '">' . $old . '</a>' );
		}
		// Hook to WP 'all' hook to automatically log all hooks that start with ns_cloner.
		if ( ! $this->log_all_hooked ) {
			add_action( 'all', array( $this, 'log_hook' ) );
			$this->log_all_hooked = true;
		}
	}

	/**
	 * Checks for a newer log file and switches to that one if so.
	 *
	 * Helpful for running in the middle of long background processes so
	 * logs don't way exceed the max log size.
	 */
	public function refresh() {
		$current_log = ns_cloner_request()->refresh()->get( 'log_file' );
		if ( ! empty( $current_log ) && $current_log !== $this->log_file ) {
			$this->end( false );
			$this->log_file   = $current_log;
			$this->log_handle = fopen( $this->log_file, 'a' );
		}
	}

	/**
	 * End logging - optionally add footer.
	 *
	 * Footer param is available because it will mess up formatting if we close a log
	 * that another background process is still writing to - if that's a possibility,
	 * don't worry about it and let the browser autoclose the tags.
	 *
	 * @param bool $do_footer Whether to output footer / closing tags.
	 */
	public function end( $do_footer = true ) {
		if ( $do_footer ) {
			$this->footer();
		}
		if ( is_resource( $this->log_handle ) ) {
			fclose( $this->log_handle );
		}
	}

	/**
	 * Run after a wpdb function call to check for and log any sql errors
	 *
	 * Also, log all queries in when additional debugging is on.
	 *
	 * @return void
	 */
	public function handle_any_db_errors() {
		if ( ! empty( ns_cloner()->db->last_error ) ) {
			// If there was an error, log it and the query it was for.
			$this->log( 'SQL ERROR: ' . ns_cloner()->db->last_error );
			$this->log( 'FOR QUERY: ' . ns_cloner()->db->last_query );
			ns_cloner()->process_manager->exit_processes( ns_cloner()->db->last_error );
		}
	}

	/*
	______________________________________
	|
	|  Log Outputs
	|_____________________________________
	*/

	/**
	 * Write data to log file
	 *
	 * This fails silently if the log file is not writable, so it's up to the caller to check
	 * for a writable log file and alert the user if there is a problem. Note that this accepts
	 * an array for the message. This is useful for including a string label to describe a
	 * variable (1st array element) followed by the variable itself for debugging (2nd el).
	 *
	 * @param mixed $message String or data to log.
	 * @param bool  $raw Whether to include timestamp and tr/td tags.
	 * @return bool
	 */
	public function log( $message, $raw = false ) {
		// Remove this temporarily to prevent infinite loop.
		remove_action( 'all', array( $this, 'log_hook' ) );

		// If debug is off or the log directory isn't writable, don't log.
		if ( ! is_resource( $this->log_handle ) ) {
			return false;
		}

		// Shortcut if raw - don't bother formatting it.
		if ( $raw ) {
			fwrite( $this->log_handle, $message );
			return true;
		}

		// Calculate current time into process and set up message.
		$time      = number_format( ns_cloner()->report->get_elapsed_time(), 4 );
		$formatted = '';
		foreach ( (array) $message as $message_part ) {
			if ( is_string( $message_part ) ) {
				// Auto convert asterisks to bold tags.
				$text       = preg_replace( '/(?<=\s)\*(.+?)\*(?=\s|:|$)/', ' <b>$1</b>', $message_part );
				$formatted .= "<span>$text</span>";
			} else {
				// Remove any sensitive data.
				if ( is_array( $message_part ) ) {
					foreach ( $this->hidden_keys as $key ) {
						if ( isset( $message_part[ $key ] ) ) {
							$message_part[ $key ] = '[redacted from log]';
						}
					}
				}
				// Use Kint formatting for non-strings, except for during WP CLI because it goes crazy.
				ob_start();
				if ( defined( 'WP_CLI' ) && WP_CLI ) {
					echo '<pre class="no-kint">';
					var_dump( $message_part ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- intentional logging.
					echo '</pre>';
				} else {
					Kint::dump( $message_part );
				}
				$formatted .= ob_get_clean();
			}
		}

		// Log it!
		$time_cell    = "<td>{$time}s</td>";
		$color_cell   = "<td style='color:{$this->random_color}'>&#11044;</td>";
		$message_cell = "<td>{$formatted}</td>";
		$message_row  = "<tr>{$time_cell}{$color_cell}{$message_cell}</tr>";
		fwrite( $this->log_handle, $message_row );
		add_action( 'all', array( $this, 'log_hook' ) );
		return true;

	}

	/**
	 * Add separator line to log
	 * "break" name of the method class is not compatible with php 5.6
	 */
	public function log_break() {
		$this->log( '-----------------------------------------------------------------------------------------------------------' );
	}

	/**
	 * Called on 'all' hook in order to catch and record any hook starting with ns_cloner
	 */
	public function log_hook() {
		global $wp_actions;
		$args = func_get_args();
		$hook = $args[0];
		// Skip if not a cloner hook, if the logger is not loaded yet, or if no hooks are registered.
		if ( 0 !== strpos( $hook, 'ns_cloner_' ) ) {
			return;
		}
		// Skip if the hook has been marked as private (not logged).
		if ( in_array( $hook, ns_cloner()->hidden_hooks, true ) ) {
			return;
		}
		// Log as hook or action.
		$is_action = isset( $wp_actions[ $hook ] );
		if ( $is_action ) {
			$this->log( "DOING ACTION: {$hook}" );
		} elseif ( defined( 'WP_NS_CLONER_DEBUG' ) && WP_NS_CLONER_DEBUG ) {
			// Filters are much more verbose an can contain more sensitive info in args so skip by default.
			$this->log( array( "APPLYING FILTER: {$hook} with args:", array_slice( $args, 1 ) ) );
		}
	}

	/**
	 * Open the HTML for a detail log and auto-log environment info
	 */
	public function header() {

		// Begin html document.
		$open = '
		<html>
			<head>
				<style>
					table{ width:100%; border: solid 1px #ddd; }
					td{ padding: 0 .9em; border-bottom: solid 1px #ddd; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; }
					td:first-child{ background:#ddd; font:bold .9em monospace; text-align:center; color:#555; width:3em; }
					td span { float:left; display:inline-block; padding: 4px 6px 4px 0; }
					.no-kint { clear: left;  border: solid 1px #ddd; padding: 10px 20px;margin-bottom: 10px;background: #eee; }
					.kint{ display:inline-block; margin: 0 0 0 6px !important; }
					.kint footer{ display:none; }
				</style>
			</head>
			<body>
				<table cellspacing="0" cellpadding="4" width="100%">';
		$this->log( $open, true );

		global $wp_version, $wp_db_version, $required_php_version, $required_mysql_version;
		$this->log( 'ENVIRONMENT DIAGNOSTICS:' );
		$this->log( 'PHP Version Required:   ' . $this->b( $required_php_version ) );
		$this->log( 'PHP Version Current:    ' . $this->b( phpversion() ) );
		$this->log( 'MySQL Version Required: ' . $this->b( $required_mysql_version ) );
		$this->log( 'MySQL Version Current:  ' . $this->b( ns_get_sql_variable( 'version' ) ) );
		$this->log( 'WP Version:             ' . $this->b( $wp_version ) );
		$this->log( 'WP DB Version:          ' . $this->b( $wp_db_version ) );
		$this->log( 'WP Memory Limit:        ' . $this->b( WP_MEMORY_LIMIT ) );
		$this->log( 'WP Debug Mode:          ' . $this->b( WP_DEBUG ) );
		$this->log( 'WP Multisite:           ' . $this->b( MULTISITE ) );
		$this->log( 'WP Subdomain Install:   ' . $this->b( defined( 'SUBDOMAIN_INSTALL ' ) ? SUBDOMAIN_INSTALL : false ) );
		$this->log( 'PHP Post Max Size:      ' . $this->b( ini_get( 'post_max_size' ) ) );
		$this->log( 'PHP Upload Max Size:    ' . $this->b( ini_get( 'upload_max_size' ) ) );
		$this->log( 'PHP Memory Limit:       ' . $this->b( ini_get( 'memory_limit' ) ) );
		$this->log( 'PHP Max Input Vars:     ' . $this->b( ini_get( 'max_input_vars' ) ) );
		$this->log( 'PHP Max Execution Time: ' . $this->b( ini_get( 'max_execution_time' ) ) );

		$this->log_break();

		$this->log( 'PLUGIN DIAGNOSTICS:' );
		foreach ( get_plugins() as $plugin_file => $data ) {
			$network = true === $data['Network'] ? ' Network Enabled' : '';
			$this->log( $data['Name'] . ' ' . $data['Version'] . ' by ' . $data['Author'] . ' ' . $network );
		}

		$this->log_break();

		$this->log( 'REQUEST DIAGNOSTICS:' );
		$this->log( array( 'RAW REQUEST:', $_POST ) ); // phpcs:ignore -- intentional logging
		$this->log( array( 'FILTERED REQUEST:', ns_cloner_request()->get_request() ) );

		$this->log_break();

		$this->log( 'START TIME:     ' . ns_cloner()->report->get_start_time() );
		$this->log( 'CLONER VERSION: ' . ns_cloner()->version );
		$this->log( 'ACTION:         ' . ns_cloner_request()->get( 'action' ) );
		$this->log( 'CLONING MODE:   ' . ns_cloner_request()->get( 'clone_mode' ) );

		$this->log_break();
	}

	/**
	 * Close the HTML of the log file - call when all logging is complete
	 */
	public function footer() {
		$close = '</table></body></html>';
		$this->log( $close, true );
	}

	/**
	 * Wrap a string in bold tags
	 *
	 * @param string $string Text to wrap.
	 * @return string
	 */
	public function b( $string ) {
		return "<strong> $string </strong>";
	}

	/*
	______________________________________
	|
	|  Management / UI methods
	|_____________________________________
	*/

	/**
	 * Get the URL for the current log file
	 *
	 * @param string|null $file Filename, defaults to current log file.
	 * @return string
	 */
	public function get_url( $file = null ) {
		$log_file    = $file ?: $this->log_file;
		$log_dir_url = NS_CLONER_V4_PLUGIN_URL . basename( NS_CLONER_LOG_DIR );
		return $log_dir_url . '/' . basename( $log_file );
	}

	/**
	 * Retrieves an array of urls for detail log files from X number of past days
	 *
	 * @return array
	 */
	public function get_logs() {
		$logs = array();
		if ( is_dir( NS_CLONER_LOG_DIR ) ) {
			// Reverse to put newest first.
			foreach ( scandir( NS_CLONER_LOG_DIR ) as $file ) {
				// Only include matching html files, and parse timestamp from them.
				if ( preg_match( $this->log_file_pattern, $file, $date_matches ) ) {
					$timestamp = strtotime( "$date_matches[1] $date_matches[2]" );
					// Add data for display in logs table.
					$logs[] = array(
						'file' => $file,
						'date' => $timestamp,
						'url'  => $this->get_url( $file ),
						'size' => size_format( filesize( NS_CLONER_LOG_DIR . "/$file" ) ),
					);
				}
			}
		}
		krsort( $logs );
		return $logs;
	}

	/**
	 * Retrieves an array of urls for detail log files from X number of past days
	 *
	 * @param int $days Number of days to get logs from.
	 * @return array
	 */
	public function get_recent_logs( $days = 7 ) {
		$recent_logs = array();
		if ( is_dir( NS_CLONER_LOG_DIR ) ) {
			foreach ( scandir( NS_CLONER_LOG_DIR ) as $file ) {
				if ( preg_match( $this->log_file_pattern, $file, $date_matches ) ) {
					// Check if it's in the requested time period.
					$seconds_since_this_log = strtotime( 'now' ) - strtotime( "$date_matches[1] $date_matches[2]" );
					if ( $seconds_since_this_log <= $days * DAY_IN_SECONDS ) {
						$recent_logs[] = NS_CLONER_LOG_DIR . $file;
					}
				}
			}
		}
		return $recent_logs;
	}

	/**
	 * Delete all log files from the logs directory
	 */
	public function delete_logs() {
		if ( is_dir( NS_CLONER_LOG_DIR ) ) {
			foreach ( scandir( NS_CLONER_LOG_DIR ) as $file ) {
				// Only include html log files.
				if ( ! preg_match( '/html$/', $file ) ) {
					continue;
				}
				unlink( NS_CLONER_LOG_DIR . "$file" );
			}
		}
	}

}
