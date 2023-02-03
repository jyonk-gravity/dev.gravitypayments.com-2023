<?php
/**
 * Copy Rows Background Process
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * NS_Cloner_Rows Process class.
 *
 * Processes a queue of table rows and copies each one from a source table to a target table.
 */
class NS_Cloner_Rows_Process extends NS_Cloner_Process {

	/**
	 * Ajax action hook
	 *
	 * @var string
	 */
	protected $action = 'rows_process';

	/**
	 * Array of preloaded rows from table
	 *
	 * @var array
	 */
	protected $preloaded = array();

	/**
	 * Array of primary key column names and last touched value by table.
	 *
	 * @var array
	 */
	protected $primary_keys = array();

	/**
	 * SQL query string compiled to insert row data
	 *
	 * @var string
	 */
	protected $insert_query = '';

	/**
	 * Number of rows to include in a single insert statement.
	 *
	 * @var int
	 */
	protected $rows_per_query = 50;

	/**
	 * Number of rows added to current insert statement.
	 * Used in conjunction with $rows_per_query.
	 *
	 * @var int
	 */
	protected $rows_count = 0;

	/**
	 * Current table for insert query
	 * Used to track when to start a new insert statement when switching tables.
	 *
	 * @var string
	 */
	protected $current_table = '';

	/**
	 * Initialize and set label
	 */
	public function __construct() {
		parent::__construct();
		$this->report_label = __( 'Rows', 'ns-cloner-site-copier' );

		// Load stored primary keys from past processes.
		$this->primary_keys = get_site_option( $this->identifier . '_primary_keys', array() );
		ns_cloner()->log->log( array( 'LOADING previous primary keys', $this->primary_keys ) );

		// Create dependency - this will auto-dispatch when table processing is complete.
		add_action( 'ns_cloner_tables_process_complete', array( $this, 'dispatch' ) );
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {

		$source_table = $item['source_table'];
		$target_table = $item['target_table'];
		$source_id    = $item['source_id'];
		$target_id    = $item['target_id'];
		$row_num      = $item['row_num'];
		$row          = $this->get_row( $source_id, $source_table, $row_num );

		// Skip if row is empty.
		if ( empty( $row ) ) {
			ns_cloner()->log->log( "SKIPPING row *$row_num* in *$source_table* because content was empty" );
			return parent::task( $item );
		}

		// Set flag to skip any junk rows which shouldn't/needn't be copied.
		$is_cloner_data = isset( $row['option_name'] ) && preg_match( '/^ns_cloner/', $row['option_name'] );
		$is_transient   = isset( $row['option_name'] ) && preg_match( '/(_transient_rss_|_transient_(timeout_)?feed_)/', $row['option_name'] );
		$is_edit_meta   = isset( $row['meta_key'] ) && preg_match( '/(ns_cloner|_edit_lock|_edit_last)/', $row['meta_key'] );
		$do_copy_row    = apply_filters( 'ns_cloner_do_copy_row', ( ! $is_cloner_data && ! $is_transient && ! $is_edit_meta ), $row, $item );
		if ( ! $do_copy_row ) {
			ns_cloner()->log->log( array( "SKIPPING row in *$source_table* because do_copy_row was false:", $row ) );
			return parent::task( $item );
		}

		// Perform replacements.
		$replaced_in_row   = 0;
		$search_replace    = ns_cloner_request()->get_search_replace( $source_id, $target_id );
		$is_upload_path    = isset( $row['option_name'] ) && 'upload_path' === $row['option_name'];
		$do_search_replace = apply_filters( 'ns_cloner_do_search_replace', ( ! $is_upload_path ), $row, $item );
		if ( $do_search_replace ) {
			foreach ( $row as $field => $value ) {
				$replaced_in_column = ns_recursive_search_replace(
					$value,
					$search_replace['search'],
					$search_replace['replace'],
					ns_cloner_request()->get( 'case_sensitive', false )
				);
				$replaced_in_row   += $replaced_in_column;
				$row[ $field ]      = $value;
			}
			if ( $replaced_in_row > 0 ) {
				ns_cloner()->log->log( "PERFORMED *$replaced_in_row* replacements in *$target_table*" );
				ns_cloner()->report->increment_report( '_replacements', $replaced_in_row );
			}
		} else {
			ns_cloner()->log->log( array( "SKIPPING row replacements in *$source_table* because do_copy_row was false:", $row ) );
		}

		// Remove primary key, if it's a table like wp_options where:
		// 1. The key isn't linked to anything else, so doesn't matter if it changes, and
		// 2. There's a possibility that the table will be added to during the clone process.
		$non_essential_keys = apply_filters(
			'ns_cloner_non_essential_primary_keys',
			array(
				'options'  => 'option_id',
				'sitemeta' => 'meta_id',
			)
		);
		foreach ( $non_essential_keys as $table => $key ) {
			if ( preg_match( "/_{$table}$/", $source_table ) ) {
				unset( $row[ $key ] );
			}
		}

		// Insert new row.
		$this->insert_row( $row, $target_table );

		return parent::task( $item );

	}

	/**
	 * Get the actual data for this row
	 *
	 * This preloads a number (default: 250) of rows ahead, so a query doesn't have to be run for each row.
	 * We don't want to load too many and risk maxing out memory, but we also don't want to query too often.
	 *
	 * @param int    $source_id ID of sources site.
	 * @param string $source_table Source table name.
	 * @param int    $row_num Index of row to be copied.
	 * @return array
	 */
	protected function get_row( $source_id, $source_table, $row_num ) {
		$source_prefix = is_multisite() ? ns_cloner()->db->get_blog_prefix( $source_id ) : ns_cloner()->db->prefix;

		// Make sure the table array is initialized.
		if ( ! isset( $this->preloaded[ $source_table ] ) ) {
			$this->preloaded[ $source_table ] = array();
		}

		// Get the primary key for this table.
		if ( ! isset( $this->primary_keys[ $source_table ] ) ) {
			// Be careful about multiple keys - this can cause issues for some tables like term_relationships.
			// Best to default to LIMIT fetching if there are multiple primary keys, even though that's slower.
			$key_data                            = ns_cloner()->db->get_results( "SHOW KEYS FROM $source_table WHERE Key_name = 'PRIMARY'" );
			$this->primary_keys[ $source_table ] = array(
				'name'  => $key_data && 1 === count( $key_data ) ? $key_data[0]->Column_name : false,
				'value' => 0,
			);
			ns_cloner()->log->log( array( "CHECKING primary key for *$source_table*", $key_data ) );
		}
		$primary_key_name = $this->primary_keys[ $source_table ]['name'];
		$primary_key_val  = $this->primary_keys[ $source_table ]['value'];

		// Try to preload the next set of data if the current row number isn't already preloaded.
		if ( ! isset( $this->preloaded[ $source_table ][ $row_num ] ) ) {
			$preload_amount = apply_filters( 'ns_cloner_rows_preload_amount', 250 );
			// Query the results - handle tables with primary keys more efficiently, but fallback to handle any strange ones that don't.
			if ( $primary_key_name ) {
				// Handle numeric vs non numeric primary keys - comparisons don't work reliably on non numeric,
				// so again fall back to limit statement if more efficient primary key comparison isn't possible.
				if ( is_numeric( $primary_key_val ) && $primary_key_val > 0 ) {
					$where = "WHERE `$primary_key_name` > $primary_key_val ";
					$limit = "LIMIT $preload_amount";
				} else {
					$where = 'WHERE 1=1';
					$limit = "LIMIT $row_num, $preload_amount";
				}
				$order = "ORDER BY `$primary_key_name` ASC";
			} else {
				$where = 'WHERE 1=1';
				$limit = "LIMIT $row_num, $preload_amount";
				$order = '';
			}
			// Compile query, and add filters so that content filtering can be applied at query
			// time for things like excluding certain post types, etc.
			$query = "SELECT $source_table.* FROM `$source_table`"
				. ' ' . apply_filters( 'ns_cloner_rows_where', $where, $source_table, $source_prefix )
				. ' ' . apply_filters( 'ns_cloner_rows_order', $order, $source_table, $source_prefix )
				. ' ' . apply_filters( 'ns_cloner_rows_limit', $limit, $source_table, $source_prefix );
			// Run it!
			ns_cloner()->log->log( array( "PRELOADING rows for *$source_table* with query:", $query ) );
			$rows = ns_cloner()->db->get_results( $query, ARRAY_A );
			// Assign the correct keys for the next preloaded batch, starting with the current row_num, not 0.
			$indexes = array_keys( array_fill( $row_num, count( $rows ), '' ) );
			// Store this batch of data in $preloaded - stays loaded as long as the current instance runs.
			$this->preloaded[ $source_table ] = array_combine( $indexes, $rows );
			ns_cloner()->log->log( 'PRELOADED *' . count( $this->preloaded[ $source_table ] ) . "* rows for *$source_table*" );
		}

		// Return the requested row now, since it should always be in the preloaded array now.
		// (still handle missing row possibility in case something went wrong with the preload query).
		if ( isset( $this->preloaded[ $source_table ][ $row_num ] ) ) {
			$row = $this->preloaded[ $source_table ][ $row_num ];
			if ( $primary_key_name ) {
				$this->primary_keys[ $source_table ]['value'] = $row[ $primary_key_name ];
			}
			return $row;
		} else {
			ns_cloner()->report->add_notice( "Missing row *$row_num* in *$source_table* - could not be preloaded" );
			return array();
		}
	}

	/**
	 * Perform the row insertion, or queue and insert together
	 *
	 * @param array  $row Row of data to insert.
	 * @param string $target_table Name of table to insert into.
	 */
	protected function insert_row( $row, $target_table ) {
		$row         = apply_filters( 'ns_cloner_insert_values', $row, $target_table );
		$field_names = array_map( 'ns_sql_backquote', array_keys( $row ) );
		$field_list  = implode( ', ', $field_names );

		// Add necessary syntax before row values are appended.
		if ( empty( $this->insert_query ) ) {
			// Start off insert statement if one hasn't been started yet.
			$this->insert_query = "INSERT INTO `$target_table` ( $field_list ) VALUES\n";
		} elseif ( ! empty( $this->current_table ) && $this->current_table !== $target_table ) {
			// Track current table and force start of new insert statement if needed.
			$this->insert_query .= ";\nINSERT INTO `$target_table` ( $field_list ) VALUES\n";
		} else {
			// If still under the maximum rows per query, just add a comma and keep using current insert.
			$this->insert_query .= ",\n";
		}

		// Prepare data and add this row to the query.
		$formats             = implode( ', ', ns_prepare_row_formats( $row, $target_table ) );
		$this->insert_query .= ns_cloner()->db->prepare( "( $formats )\n", $row );
		$this->current_table = $target_table;
		$this->rows_count++;

		// Insert the previous accumulated query and start new, if reaching max query size.
		if ( ! empty( $this->insert_query ) && $this->is_query_maxed() ) {
			$this->insert_batch();
		}
	}

	/**
	 * Insert the whole current group of accumulated row insertions.
	 */
	public function insert_batch() {
		ns_cloner()->log->log( "INSERTING $this->rows_count rows into $this->current_table" );
		// Break into single queries to handle servers where multiple insert statements in one query are not allowed.
		$inserts = preg_split( '/;(?=\sINSERT)/', $this->insert_query );
		foreach ( $inserts as $query ) {
			ns_cloner()->db->query( $query );
			// Handle any errors.
			if ( ! empty( ns_cloner()->db->last_error ) ) {
				if ( false !== strpos( ns_cloner()->db->last_error, 'Duplicate entry' ) ) {
					ns_cloner()->log->log( array( 'DUPLICATE entry for query:', $query ) );
					$is_duplicate_option = preg_match( "/Duplicate entry '([^'])' for key 'option_name'/", ns_cloner()->db->last_error, $db_error_matches );
					if ( $is_duplicate_option ) {
						// For duplicates in the options table, try deleting the row and reinserting.
						// (Often caused by a plugin that initializes values when site is partly cloned).
						$option_name = $db_error_matches[1];
						ns_cloner()->log->log( array( 'TRYING to remove duplicate option:', $option_name ) );
						ns_cloner()->db->last_error = '';
						ns_cloner()->db->query( "DELETE FROM $this->current_table WHERE option_name = '$option_name'" );
						// See if it worked.
						if ( ns_cloner()->db->last_error ) {
							ns_cloner()->log->log( 'FAILED to remove duplicate option' );
							ns_cloner()->report->add_notice( ns_cloner()->db->last_error . ' for table ' . $this->current_table );
							ns_cloner()->db->last_error = '';
						} else {
							ns_cloner()->log->log( 'WORKED to remove duplicate option' );
						}
					} else {
						// For other duplicate entries, warn but don't bail.
						ns_cloner()->report->add_notice( ns_cloner()->db->last_error . ' for table ' . $this->current_table );
						ns_cloner()->db->last_error = '';
					}
				} else {
					ns_cloner()->log->handle_any_db_errors();
				}
			}
		}
		// Reset.
		$this->insert_query = '';
		$this->rows_count   = 0;
	}

	/**
	 * Run parent after-handling, plus save the most recent primary key for current tables
	 */
	protected function after_handle() {
		// Run insert for the accumulated rows.
		if ( ! empty( $this->insert_query ) ) {
			$this->insert_batch();
		}
		// Save most recent primary key so we know where to pick up again.
		ns_cloner()->log->log( array( 'SAVING primary key data for rows process:', $this->primary_keys ) );
		update_site_option( $this->identifier . '_primary_keys', $this->primary_keys );
		parent::after_handle();
	}

	/**
	 * Run parent cancel, plus delete stored primary key data.
	 */
	public function cancel() {
		delete_site_option( $this->identifier . '_primary_keys' );
		parent::cancel();
	}

	/**
	 * Check if current insert query is close to max size, in rows or length
	 *
	 * @return bool
	 */
	protected function is_query_maxed() {
		$packet_max          = ns_get_sql_variable( 'max_allowed_packet', 50000 );
		$exceeded_packet_max = strlen( $this->insert_query ) >= .9 * $packet_max;
		$rows_per_query      = apply_filters( 'ns_cloner_rows_per_query', $this->rows_per_query, $this->identifier );
		$exceeded_row_max    = $this->rows_count >= $rows_per_query;
		return $exceeded_row_max || $exceeded_packet_max;
	}


}
