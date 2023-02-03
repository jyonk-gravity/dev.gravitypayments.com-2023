<?php

/**
 * Processes database-related functionality.
 * @since      1.0
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

class BSR_DB {

	/**
	 * The maximum number of results to store when running a search/replace.
	 * @var int
	 */
	public $max_results;

	/**
	 * The page size used throughout the plugin.
	 * @var int
	 */
	public $page_size;

	/**
	 * The WordPress database class.
	 * @var WPDB
	 */
	private $wpdb;

	/**
	 * Initializes the class and its properties.
	 * @access public
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb         = $wpdb;
		$this->page_size    = $this->get_page_size();
		$this->max_results  = $this->get_max_results();
	}

	/**
	 * Generates a new salt to use for the filepath.
	 *
	 * @return string
	 */
	public function generate_salt() {
		$salt = date( 'YmdHis' ) . '-' . wp_generate_password( 5, false, false );
		update_option( 'bsr_backup_salt', $salt );
		return $salt;
	}

	/**
	 * Gets the current salt, returning false if it doesn't exist.
	 *
	 * @return string|bool
	 */
	public function get_salt() {
		return get_option( 'bsr_backup_salt', false );
	}

	/**
	 * Deletes the salt used to generate the backup filepath.
	 *
	 * @return bool
	 */
	public function delete_salt() {
		return delete_option( 'bsr_backup_salt' );
	}

	/**
	 * Gets the path to the BSR backup file.
	 *
	 * @return string
	 */
	public function get_file_path() {
		$salt = $this->get_salt();

		if ( ! $salt ) {
			$salt = $this->generate_salt();
		}

		return sprintf(
			'%sbsr_db_backup-%s.sql',
			get_temp_dir(),
			$salt
		);
	}

	/**
	 * Returns an array of tables in the database.
	 * @access public
	 * @param  $temp Whether we should use the temporary prefix.
	 * @return array
	 */
	public static function get_tables( $temp = false ) {
		global $wpdb;

		if ( true === $temp ) {
			$tables 		= $wpdb->get_col( "SHOW TABLES LIKE 'bsrtmp_%'" );
		} elseif ( function_exists( 'is_multisite' ) && is_multisite() ) {
			if ( is_main_site() ) {
				$tables 	= $wpdb->get_col( 'SHOW TABLES' );
			} else {
				$blog_id 	= get_current_blog_id();
				$tables 	= $wpdb->get_col( "SHOW TABLES LIKE '" . $wpdb->base_prefix . absint( $blog_id ) . "\_%'" );
			}
		} else {
			$tables 		= $wpdb->get_col( 'SHOW TABLES' );
		}

		return $tables;
	}

	/**
	 * Returns an array containing the size of each database table.
	 * @access public
	 * @return array
	 */
	public static function get_sizes() {
		global $wpdb;

		$sizes 	= array();
		$tables	= $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );

		if ( is_array( $tables ) && ! empty( $tables ) ) {

			foreach ( $tables as $table ) {
				$size = round( $table['Data_length'] / 1024 / 1024, 2 );
				$sizes[$table['Name']] = sprintf( __( '(%s MB)', 'better-search-replace' ), $size );
			}

		}

		return $sizes;
	}

	/**
	 * Returns the max number of results to store.
	 * @access public
	 * @return int
	 */
	public function get_max_results() {
		$max_results = get_option( 'bsr_max_results' ) ? get_option( 'bsr_max_results' ) : 60;
		return absint( $max_results );
	}

	/**
	 * Returns the current page size.
	 * @access public
	 * @return int
	 */
	public function get_page_size() {
		$page_size = get_option( 'bsr_page_size' ) ? get_option( 'bsr_page_size' ) : 20000;
		return absint( $page_size );
	}

	/**
	 * Returns the number of pages in a table.
	 * @access public
	 * @return int
	 */
	public function get_pages_in_table( $table ) {
		if ( false === $this->table_exists( $table ) ) {
			return 0;
		}

		$table 	= esc_sql( $table );
		$rows 	= $this->wpdb->get_var( "SELECT COUNT(*) FROM `$table`" );
		$pages 	= ceil( $rows / $this->page_size );
		return absint( $pages );
	}

	/**
	 * Gets the total number of pages in the DB.
	 * @access public
	 * @return int
	 */
	public function get_total_pages( $tables ) {
		$total_pages = 0;

		foreach ( $tables as $table ) {

			// Get the number of rows & pages in the table.
			$pages = $this->get_pages_in_table( $table );

			// Always include 1 page in case we have to create schemas, etc.
			if ( $pages == 0 ) {
				$pages = 1;
			}

			$total_pages += $pages;
		}

		return absint( $total_pages );
	}

	/**
	 * Gets the total number of pages in the sql file.
	 * @access public
	 * @return int
	 */
	public function get_total_pages_from_file( $file = '' ) {

		if ( $file === '' ) {
			$file = $this->get_file_path();
		}

		// The defaults.
		$fh 		= fopen( $file, 'r' );
		$queries 	= 0;
		$file_size	= filesize( $file );
		$page_size 	= $this->get_page_size();

		// Run through file and count number of queries.
		while( !feof( $fh ) ) {
			$query = trim( stream_get_line( $fh, $file_size, ';' . PHP_EOL ) );
			if ( empty( $query ) ) {
				continue;
			}
			$queries++;
		}

		// Calculate and return number of pages.
		$pages = ceil( $queries / $page_size );
		return $pages;
	}

	/**
	 * Backs up the provided database table.
	 *
	 * @access public
	 *
	 * @param 	string 	$table 	The name of the table to backup.
	 * @param 	int 	$page 	The page to resume the backup from.
	 * @param 	bool	$temp 	If we should backup a bsrtmp_$table
	 * @return 	array
	 */
	public function backup_table( $table, $page, $temp = false ) {

		// Bail if not authorized.
		if ( ! check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			return;
		}

		// Load up the default settings for this chunk.
		$table 			= esc_sql( $table );
		$current_page 	= absint( $page );
		$pages 			= $this->get_pages_in_table( $table );
		$results 		= '';
		$done 			= false;

		if ( $temp ) {
			$backup_table = str_replace( 'bsrtmp_', '', $table );
		} else {
			$backup_table = $table;
		}

		if ( false === $this->table_exists( $table ) ) {
			return array( 'table_complete' => 1, 'total_pages' => 0 );
		}

		if ( 0 === $current_page ) {

			// Get the table schema.
			$show_create 	= $this->wpdb->get_row( "SHOW CREATE TABLE `$table`" );
			$want 			= 'Create Table';

			// Store the table schema in memory.
			if ( $show_create ) {
				$results .= PHP_EOL . "DROP TABLE IF EXISTS `$backup_table`;" . PHP_EOL;

				if ( $temp ) {
					$results .= str_replace( 'bsrtmp_', '', $show_create->$want . ';' . PHP_EOL );
				} else {
					$results .= $show_create->$want . ';' . PHP_EOL;
				}

			}

		}

		// Get the data.
		$start 		= $current_page * $this->page_size;
		$end 		= $this->page_size;
		$query 		= "SELECT * FROM `$table` LIMIT $start, $end";
		$data 		= $this->wpdb->get_results( $query );
		$queries 	= array();

		// Iterate through results and transform into queries.
		foreach ( $data as $row ) {
			$vals = array();

			foreach ( get_object_vars( $row ) as $i => $v ) {
				$vals[] = sprintf( "'%s'", $this->wpdb->remove_placeholder_escape( esc_sql( $v ) ) );
			}

			$queries[] = "INSERT INTO `$backup_table` VALUES " . sprintf( "(%s)", implode( ', ', $vals ) ) . ';' . PHP_EOL;
		}

		if ( ! empty( $queries ) ) {

			if ( 0 === $current_page ) {
				$results .= "LOCK TABLES `$backup_table` WRITE;" . PHP_EOL;
			}

			$results .= implode( '', $queries );
			if ( $current_page == $pages - 1 ) {
				$results .= 'UNLOCK TABLES;' . PHP_EOL;
				$done = true;
			}

		} else {
			$done = true;
		}

		if ( true === $done && true === $temp ) {
			$this->wpdb->query( "DROP TABLE IF EXISTS $table;" );
		}

		// Store the contents and finish up the export.
		@file_put_contents( $this->get_file_path(), $results, FILE_APPEND );
		return array( 'table_complete' => $done, 'total_pages' => $pages );
	}

	/**
	 * Runs a database import.
	 * @access public
	 */
	public function run_import( $file = '', $page = 0 ) {

		// Bail if not authorized.
		if ( ! check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			return;
		}

		// Some defaults
		$fh 		= fopen( $file, 'r' );
		$size		= filesize( $file );
		$status		= array(
			'errors' 	=> 0,
			'updates' 	=> 0
		);

		$queries 	= 0;
		$done		= false;
		$start 		= $page * $this->page_size;

		while( !feof( $fh ) ) {

			$query = trim( stream_get_line( $fh, $size, ';' . PHP_EOL ) );
			if ( empty( $query ) ) {
				continue;
			}

			$queries++;

			if ( $queries >= $start ) {

				if ( $queries - $start == $this->page_size ) {
					$done = false;
					$page++;
					break;
				}

				// Let's run on temp tables!
				if ( substr( $query, 0, 13 ) === 'INSERT INTO `' ) {
					$query = $this->str_replace_first( 'INSERT INTO `', 'INSERT INTO `bsrtmp_', $query );
				} elseif ( substr( $query, 0, 14) === 'CREATE TABLE `' ) {
					$query = $this->str_replace_first( 'CREATE TABLE `', 'CREATE TABLE `bsrtmp_', $query );
				} elseif ( substr( $query, 0, 22 ) === 'DROP TABLE IF EXISTS `' ) {
					$query = $this->str_replace_first( 'DROP TABLE IF EXISTS `', 'DROP TABLE IF EXISTS `bsrtmp_', $query );
				} elseif ( substr( $query, 0, 13 ) === 'LOCK TABLES `' ) {
					$query = $this->str_replace_first( 'LOCK TABLES `', 'LOCK TABLES `bsrtmp_', $query );
				} else {
					// Nothing to do here.
				}

				if ( $this->wpdb->query( $query ) === false ) {
					$status['errors']++;
				} else {
					$status['updates']++;
				}

				$done = true;
			}

		}

		fclose( $fh );
		return array( 'import_complete' => $done );
	}

	/**
	 * Renames temporary database tables.
	 * @access public
	 */
	public function rename_temp_tables() {

		// Get the profiles and results; they're in the old tables.
		$profiles 			= BSR_Admin::get_profiles();
		$results 			= get_transient( 'bsr_results' );

		// Get the names of the tables.
		$tables_to_rename 	= $this->get_tables( true );
		$all_tables 		= $this->get_tables();

		foreach ( $tables_to_rename as $table ) {

			$table 		= esc_sql( $table );
			$new_name 	= substr( $table, 7 );
			$flipped 	= array_flip( $all_tables );

			// Drop the table if necessary.
			if ( array_key_exists( $new_name, $flipped ) ) {
				$this->wpdb->query( "DROP TABLE IF EXISTS `$new_name`" );
			}

			// Rename the temporary table.
			$this->wpdb->query( "RENAME TABLE `$table` TO `$new_name`" );
		}

		// Restore the existing profiles and results.
		wp_cache_delete( 'alloptions', 'options' );
		update_option( 'bsr_profiles', $profiles );
		delete_transient( 'bsr_results' );
		set_transient( 'bsr_results', $results );
	}

	/**
	 * Gets the columns in a table.
	 * @access public
	 * @param  string $table The table to check.
	 * @return array
	 */
	public function get_columns( $table ) {
		$primary_key 	= null;
		$columns 		= array();

		if ( false === $this->table_exists( $table ) ) {
			return array( $primary_key, $columns );
		}

		$fields  		= $this->wpdb->get_results( 'DESCRIBE ' . $table );

		if ( is_array( $fields ) ) {
			foreach ( $fields as $column ) {
				$columns[] = $column->Field;
				if ( $column->Key == 'PRI' ) {
					$primary_key = $column->Field;
				}
			}
		}

		return array( $primary_key, $columns );
	}

	/**
	 * Adapated from interconnect/it's search/replace script.
	 *
	 * Modified to use WordPress wpdb functions instead of PHP's native mysql/pdo functions,
	 * and to be compatible with batch processing via AJAX.
	 *
	 * @link https://interconnectit.com/products/search-and-replace-for-wordpress-databases/
	 *
	 * @access public
	 * @param  string 	$table 	The table to run the replacement on.
	 * @param  int 		$page  	The page/block to begin the query on.
	 * @param  array 	$args 	An associative array containing arguements for this run.
	 * @return array
	 */
	public function srdb( $table, $page, $args ) {

		// Load up the default settings for this chunk.
		$table 			= esc_sql( $table );
		$current_page 	= absint( $page );
		$pages 			= $this->get_pages_in_table( $table );
		$done 			= false;

		$args['search_for'] 	= str_replace( '#BSR_BACKSLASH#', '\\', $args['search_for'] );
		$args['replace_with'] 	= str_replace( '#BSR_BACKSLASH#', '\\', $args['replace_with'] );

		$table_report = array(
			'change' 	=> 0,
			'updates' 	=> 0,
			'start' 	=> microtime( true ),
			'end'		=> microtime( true ),
			'errors' 	=> array(),
			'changes'	=> array(),
			'skipped'	=> false
		);

		// Get a list of columns in this table.
		list( $primary_key, $columns ) = $this->get_columns( $table );

		// Bail out early if there isn't a primary key.
		if ( null === $primary_key ) {
			$table_report['skipped'] = true;
			return array( 'table_complete' => true, 'table_report' => $table_report );
		}

		$current_row 	= 0;
		$start 			= $page * $this->page_size;
		$end 			= $this->page_size;

		// Grab the content of the table.
		$data = $this->wpdb->get_results( "SELECT * FROM `$table` LIMIT $start, $end", ARRAY_A );

		// Loop through the data.
		foreach ( $data as $row ) {
			$current_row++;
			$update_sql = array();
			$where_sql 	= array();
			$upd 		= false;

			foreach( $columns as $column ) {

				$data_to_fix = $row[ $column ];

				if ( $column == $primary_key ) {
					$where_sql[] = $column . ' = "' .  $this->mysql_escape_mimic( $data_to_fix ) . '"';
					continue;
				}

				// Skip GUIDs by default.
				if ( 'on' !== $args['replace_guids'] && 'guid' == $column ) {
					continue;
				}

				if ( $this->wpdb->options === $table ) {

					// Skip any BSR options as they may contain the search field.
					if ( isset( $should_skip ) && true === $should_skip ) {
						$should_skip = false;
						continue;
					}

					// If the Site URL needs to be updated, let's do that last.
					if ( isset( $update_later ) && true === $update_later ) {
						$update_later 	= false;
						$edited_data 	= $this->recursive_unserialize_replace( $args['search_for'], $args['replace_with'], $data_to_fix, false, $args['case_insensitive'] );

						if ( $edited_data != $data_to_fix ) {
							$table_report['change']++;
							$table_report['updates']++;

							// Log changes
							if ( $table_report['change'] <= $this->max_results ) {

								$table_report['changes'][] = array(
									'row' 		=> $current_row,
									'column' 	=> $column,
									'from' 		=> $data_to_fix,
									'to' 		=> $edited_data
								);

							}

							update_option( 'bsr_update_site_url', $edited_data );
							continue;
						}
					}

					if ( '_transient_bsr_results' === $data_to_fix || 'bsr_profiles' === $data_to_fix || 'bsr_update_site_url' === $data_to_fix || 'bsr_data' === $data_to_fix ) {
						$should_skip = true;
					}

					if ( 'siteurl' === $data_to_fix && $args['dry_run'] !== 'on' ) {
						$update_later = true;
					}

				}

				// Run a search replace on the data that'll respect the serialisation.
				$edited_data = $this->recursive_unserialize_replace( $args['search_for'], $args['replace_with'], $data_to_fix, false, $args['case_insensitive'] );

				// Something was changed
				if ( $edited_data != $data_to_fix ) {
					$update_sql[] = $column . ' = "' . $this->mysql_escape_mimic( $edited_data ) . '"';
					$upd = true;

					$table_report['change']++;

					// Log changes
					if ( $table_report['change'] <= $this->max_results ) {

						$table_report['changes'][] = array(
							'row' 		=> $current_row,
							'column' 	=> $column,
							'from' 		=> $data_to_fix,
							'to' 		=> $edited_data
						);

					}

				}

			}

			// Determine what to do with updates.
			if ( $args['dry_run'] === 'on' ) {
				// Don't do anything if a dry run
			} elseif ( $upd && ! empty( $where_sql ) ) {
				// If there are changes to make, run the query.
				$sql 	= 'UPDATE ' . $table . ' SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );
				$result = $this->wpdb->query( $sql );

				if ( ! $result ) {
					$table_report['errors'][] = sprintf( __( 'Error updating row: %d.', 'better-search-replace' ), $current_row );
				} else {
					$table_report['updates']++;
				}

			}

		} // end row loop

		if ( $current_page >= $pages - 1 ) {
			$done = true;
		}

		// Flush the results and return the report.
		$table_report['end'] = microtime( true );
		$this->wpdb->flush();
		return array( 'table_complete' => $done, 'table_report' => $table_report );
	}

	/**
	 * Adapated from interconnect/it's search/replace script.
	 *
	 * @link https://interconnectit.com/products/search-and-replace-for-wordpress-databases/
	 *
	 * Take a serialised array and unserialise it replacing elements as needed and
	 * unserialising any subordinate arrays and performing the replace on those too.
	 *
	 * @access private
	 * @param  string 			$from       		String we're looking to replace.
	 * @param  string 			$to         		What we want it to be replaced with
	 * @param  array  			$data       		Used to pass any subordinate arrays back to in.
	 * @param  boolean 			$serialised 		Does the array passed via $data need serialising.
	 * @param  sting|boolean 	$case_insensitive 	Set to 'on' if we should ignore case, false otherwise.
	 *
	 * @return string|array	The original array with all elements replaced as needed.
	 */
	public function recursive_unserialize_replace( $from = '', $to = '', $data = '', $serialised = false, $case_insensitive = false ) {
		try {

			if ( is_string( $data ) && ! is_serialized_string( $data ) && ( $unserialized = $this->unserialize( $data ) ) !== false ) {
				$data = $this->recursive_unserialize_replace( $from, $to, $unserialized, true, $case_insensitive );
			}

			elseif ( is_array( $data ) ) {
				$_tmp = array( );
				foreach ( $data as $key => $value ) {
					$_tmp[ $key ] = $this->recursive_unserialize_replace( $from, $to, $value, false, $case_insensitive );
				}

				$data = $_tmp;
				unset( $_tmp );
			}

			// Submitted by Tina Matter
			elseif ( is_object( $data ) ) {
				if ('__PHP_Incomplete_Class' !== get_class($data)) {
					$_tmp = $data;
					$props = get_object_vars( $data );
					foreach ( $props as $key => $value ) {
						$_tmp->$key = $this->recursive_unserialize_replace( $from, $to, $value, false, $case_insensitive );
					}

					$data = $_tmp;
					unset( $_tmp );
 				}
			}

			elseif ( is_serialized_string( $data ) ) {
				$unserialized = $this->unserialize( $data );

				if ( $unserialized !== false ) {
					$data = $this->recursive_unserialize_replace( $from, $to, $unserialized, true, $case_insensitive );
				}
			}

			else {
				if ( is_string( $data ) ) {
					$data = $this->str_replace( $from, $to, $data, $case_insensitive );
				}
			}

			if ( $serialised ) {
				return serialize( $data );
			}

		} catch( Exception $error ) {

		}

		return $data;
	}

	/**
	 * Updates the Site URL if necessary.
	 * @access public
	 * @return boolean
	 */
	public function maybe_update_site_url() {
		$option = get_option( 'bsr_update_site_url' );

		if ( $option ) {
			update_option( 'siteurl', $option );
			delete_option( 'bsr_update_site_url' );
			return true;
		}

		return false;
	}

	/**
	 * Mimics the mysql_real_escape_string function. Adapted from a post by 'feedr' on php.net.
	 * @link   http://php.net/manual/en/function.mysql-real-escape-string.php#101248
	 * @access public
	 * @param  string $input The string to escape.
	 * @return string
	 */
	public function mysql_escape_mimic( $input ) {
	    if ( is_array( $input ) ) {
	        return array_map( __METHOD__, $input );
	    }
	    if ( ! empty( $input ) && is_string( $input ) ) {
	        return str_replace( array( '\\', "\0", "\n", "\r", "'", '"', "\x1a" ), array( '\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z' ), $input );
	    }

	    return $input;
	}

	/**
	 * Wrapper for str_replace
	 *
	 * @param string $from
	 * @param string $to
	 * @param string $data
	 * @param string|bool $case_insensitive
	 *
	 * @return string
	 */
	public function str_replace( $from, $to, $data, $case_insensitive = false ) {
		if ( 'on' === $case_insensitive ) {
			$data = str_ireplace( $from, $to, $data );
		} else {
			$data = str_replace( $from, $to, $data );
		}

		return $data;
	}

	/**
	 * Wrapper for replacing first instance of string.
	 * @access public
	 * @return string
	 */
	public function str_replace_first( $search, $replace, $string ) {
		$pos = strpos( $string, $search );
		if ( false !== $pos ) {
		    $string = substr_replace( $string, $replace, $pos, strlen( $search ) );
		}
		return $string;
	}

	/**
	 * Return unserialized object or array
	 *
	 * @param string $serialized_string Serialized string.
	 * @param string $method            The name of the caller method.
	 *
	 * @return mixed, false on failure
	 */
	public static function unserialize( $serialized_string ) {
		if ( ! is_serialized( $serialized_string ) ) {
			return false;
		}

		$serialized_string   = trim( $serialized_string );
		$unserialized_string = @unserialize( $serialized_string );

		return $unserialized_string;
	}

	/**
	 * Checks whether a table exists in DB.
	 *
	 * @param $table
	 *
	 * @return bool
	 */
	private function table_exists( $table ) {
		return in_array( $table, $this->get_tables() );
	}
}
