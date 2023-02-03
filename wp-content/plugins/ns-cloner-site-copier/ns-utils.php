<?php
/**
 * Cloner utility functions.
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Organize a sequence of search/replace values.
 *
 * This orders values and adds new corrective search/replace pairs to avoid compounding replacement issues.
 * Note search and replace params are BY REFERENCE.
 *
 * @param array $search Strings of search text to sort/process.
 * @param array $replace Strings of replacement text to sort/process (keeping same order as $search of course so no mix ups).
 * @param bool  $case_sensitive Whether search/replace will be case sensitive.
 * @return void
 */
function ns_set_search_replace_sequence( &$search, &$replace, $case_sensitive = false ) {
	/*
	 * Sort string replacements by order longest to shortest to prevent a situation like
	 * Source Site w/ url="neversettle.it",upload_dir="/neversettle.it/wp-content/uploads" and
	 * Target Site w/url="blog.neversettle.it",upload_dir="/neversettle.it/wp-content/uploads/sites/2".
	 * This could result in target upload_dir being "/blog.neversettle.it/wp-content/uploads"
	 * id the url replacement is applied before upload_dir replacement.
	 */
	$search_replace = array_combine( $search, $replace );
	uksort(
		$search_replace,
		function( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}
	);
	$search      = array_keys( $search_replace );
	$new_search  = $search;
	$replace     = array_values( $search_replace );
	$new_replace = $replace;

	/*
	 * If any search terms are found in replace terms which have already been inserted (ie came earlier in the find/replace sequence), remove this search term plus
	 * its accompanying replacement from the string-based search/replace and a correction to change that replacement back again so replacements won't be compounded.
	 * This prevents a situation like Source Site w/ title="Brown",url="brown.com" and Target Site w/ title="Brown Subsite",url="subsite.brown.com"
	 * resulting in target urls like "subsite.Brown Subsite.com" when title replacement is applied after url replacement.
	 */
	$fix_insertion_index = 1;
	foreach ( $search as $index => $search_text ) {
		// Figure out what the desired replace text is from other array in case we need it.
		$replace_text = $replace[ $index ];
		// Get replacements earlier in array (which could've already been inserted into text so we need to watch out for them).
		$past_replacements = array_slice( $replace, 0, $index );
		// Identify any of those replacements which the search text and save as array of conflicts.
		$conflicting_replacements = array_filter(
			$past_replacements,
			function ( $past_replace_text ) use ( $search_text, $case_sensitive ) {
				$search_func = $case_sensitive ? 'strpos' : 'stripos';
				return false !== $search_func( $past_replace_text, $search_text );
			}
		);
		if ( ! empty( $conflicting_replacements ) ) {
			ns_cloner()->log->log( "Conflicting replacement found: search text '$search_text' appears in one or more previous replacement(s): '" . join( "','", $conflicting_replacements ) . "'" );
			foreach ( $conflicting_replacements as $conflicting_replacement ) {
				// If it's an exact match, assume it's supposed to happen and skip fixing it.
				if ( $conflicting_replacement === $search_text ) {
					ns_cloner()->log->log( "Replacement is same as search for: '$search_text'. Taking no action." );
					continue;
				}
				// Insert into the search/replace arrays right after the current item which will produce the bad replacement.
				$replace_func       = $case_sensitive ? 'str_replace' : 'str_ireplace';
				$conflicting_search = $replace_func( $search_text, $replace_text, $conflicting_replacement );
				array_splice( $new_search, $fix_insertion_index, 0, $conflicting_search );
				array_splice( $new_replace, $fix_insertion_index, 0, $conflicting_replacement );
				$fix_insertion_index ++;
			}
		}
		$fix_insertion_index ++;
	}

	// Update variables by reference - no return needed.
	$search  = $new_search;
	$replace = $new_replace;
}

/**
 * Recursively search and replace
 *
 * @param mixed $data           String or array which to do search/replace on - passed by reference.
 * @param array $search         Strings to look for.
 * @param array $replace        Replacements for $search values.
 * @param bool  $case_sensitive Whether string search should be case sensitive.
 *
 * @return int number of replacements made
 */
function ns_recursive_search_replace( &$data, $search, $replace, $case_sensitive = false ) {
	$was_serialized    = is_serialized( $data );
	$data              = maybe_unserialize( $data );
	$replacement_count = 0;
	// Run through replacements for different data types.
	if ( is_array( $data ) ) {
		foreach ( $data as $key => $value ) {
			$replacement_count += ns_recursive_search_replace( $data[ $key ], $search, $replace, $case_sensitive );
		}
	} elseif ( is_object( $data ) ) {
		if ( $data instanceof \__PHP_Incomplete_Class ) {
			$array   = new ArrayObject( $data );
			$message = sprintf(
				'Skipping an uninitialized class "%s", replacements might not be complete.',
				$array['__PHP_Incomplete_Class_Name']
			);
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				\WP_CLI::warning( $message );
			}
			ns_cloner()->log->log( $message );
		} else {
			foreach ( $data as $key => $value ) {
				$replacement_count += ns_recursive_search_replace( $data->$key, $search, $replace, $case_sensitive );
			}
		}
	} elseif ( is_string( $data ) ) {
		$replace_func = $case_sensitive ? 'str_replace' : 'str_ireplace';
		$data         = $replace_func( $search, $replace, $data, $replacement_count );
	}
	// Reserialize if it was serialized originally.
	if ( $was_serialized ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- only leaving it how we found it.
		$data = serialize( $data );
	}
	// Return number of replacements made for informational/reporting purposes.
	return $replacement_count;
}

/**
 * Copy directories and files recursively by queueing them in a background process
 *
 * Skip directories called 'sites' to avoid copying all sites storage in WP > 3.5
 *
 * @param string                $src Source directory path.
 * @param string                $dst Destination directory path (Relative).
 * @param WP_Background_Process $process Background process to use for queueing files.
 * @param int                   $num File number in queue.
 * @return int Number of files found
 */
function ns_recursive_dir_copy_by_process( $src, $dst, $process, $num = 0 ) {
	if ( is_dir( $src ) ) {
		$files = scandir( $src );
		// Specify items to ignore when copying.
		$ignore = apply_filters( 'ns_cloner_dir_copy_ignore', array( 'sites', '.', '..' ) );
		// Recursively copy files that aren't in the ignore array.
		foreach ( $files as $file ) {
			if ( ! in_array( $file, $ignore, true ) ) {
				$num += ns_recursive_dir_copy_by_process( "$src/$file", "$dst/$file", $process, $num );
			}
		}
	} elseif ( file_exists( $src ) ) {
		$file = array(
			'number'      => ++ $num,
			'source'      => $src,
			'destination' => $dst,
		);
		$process->push_to_queue( $file );
	}

	return $num;
}

/**
 * Validate a potential new site and return an array of error messages.
 *
 * We used to use wpmu_validate_blog_signup here, but that caused too many issues due to the extra
 * validation place on front-end signups vs. admin creations (cloning should have the same rules
 * that apply to an admin in Sites > Add New, not front end registration).
 *
 * This is now a custom combination of logic from site-new.php and wpmu_validate_blog_signup().
 *
 * @param string $site_name Domain/subdirectory of new site.
 * @param string $site_title Title of new site.
 * @return array
 */
function ns_wp_validate_site( $site_name, $site_title ) {
	global $domain;
	$errors = array();
	// Preempt any spaces and uppercase chars.
	$site_name = strtolower( trim( $site_name ) );
	// Require some name.
	if ( empty( $site_name ) ) {
		$errors[] = __( 'Site URL is required.', 'ns-cloner-site-copier' );
	} elseif ( ! preg_match( '|^([a-z0-9-])+$|', $site_name ) ) {
		$errors[] = __( 'Site URLs can only contain letters (a-z), numbers and hyphens.', 'ns-cloner-site-copier' );
	}
	if ( is_multisite() ) {
		// Check if the domain/path has been used already.
		$current_network = get_network();
		$base            = $current_network->path;
		if ( is_subdomain_install() ) {
			$mydomain = $site_name . '.' . preg_replace( '|^www\.|', '', $domain );
			$path     = $base;
		} else {
			$mydomain = "$site_name";
			$path     = $base . $site_name . '/';
		}
		if ( domain_exists( $mydomain, $path, get_network()->id ) ) {
			$errors[] = __( 'Sorry, that site already exists!', 'ns-cloner-site-copier' );
		}
		// Validate against WP illegal / reserved names.
		$illegal_names  = get_site_option( 'illegal_names', array() );
		$illegal_dirs   = get_subdirectory_reserved_names();
		$illegal_values = is_subdomain_install() ? $illegal_names : array_merge( $illegal_names, $illegal_dirs );
		if ( in_array( $site_name, $illegal_values, true ) ) {
			$errors[] = __( 'That URL is reserved by WordPress.', 'ns-cloner-site-copier' );
		}
	}
	// Require some title.
	if ( empty( $site_title ) ) {
		$errors[] = __( 'A site title is required', 'ns-cloner-site-copier' );
	}
	return apply_filters( 'ns_cloner_validate_site_errors', $errors, $site_name, $site_title );
}

/**
 * Get a list of sites with formatted labels for select element
 *
 * @return array
 */
function ns_wp_get_sites_list() {
	$list = array();
	if ( function_exists( 'get_sites' ) ) {
		$sites = get_sites( array( 'number' => 9999 ) );
	} else {
		// Not multisite, or really ancient.
		$sites = array();
	}
	// Loop through sites and prepare labels.
	if ( count( $sites ) > 500 ) {
		// For networks with more than 500 sites, show fewer details to avoid extra queries.
		foreach ( $sites as $site ) {
			$list[ $site->blog_id ] = ( is_subdomain_install() ? $site->domain : $site->path ) . ' - ID:' . $site->blog_id;
		}
	} else {
		// For smaller networks, show more detailed results.
		foreach ( $sites as $site ) {
			$details                = get_blog_details( $site->blog_id );
			$name                   = substr( $details->blogname, 0, 30 );
			$list[ $site->blog_id ] = $name . ' - ' . ns_short_url( $details->siteurl ) . ' - ID:' . $site->blog_id;
		}
	}
	return apply_filters( 'ns_cloner_sites_list', $list );
}

/**
 * Get allowed HTML elements and attributes to use with wp_kses
 *
 * @return array
 */
function ns_wp_kses_allowed() {
	return array(
		'a'    => array(
			'href'   => array(),
			'target' => array(),
			'class'  => array(),
		),
		'em'   => array(),
		'code' => array(),
	);
}

/**
 * Remove protocol and trailing slash from URL for display
 *
 * @param string $url URL to shorten.
 * @return string
 */
function ns_short_url( $url ) {
	return untrailingslashit( str_replace( array( 'https://', 'http://', '//' ), '', $url ) );
}

/**
 * Get the link for a specific site/blog, with blogname as the anchor text
 *
 * @param mixed $site_id ID of blog/site, or array of IDs.
 * @param bool  $html Whether to return an HTML link, or just the url.
 * @return string
 */
function ns_site_link( $site_id = null, $html = true ) {
	// Force html off for CLI report messages.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		$html = false;
	}
	if ( is_multisite() && ( is_numeric( $site_id ) || is_array( $site_id ) ) ) {
		// Multisite.
		$links    = array();
		$site_ids = is_array( $site_id ) ? $site_id : array( $site_id );
		foreach ( $site_ids as $id ) {
			$details = get_blog_details( $id );
			$links[] = $html ? "<a href='{$details->siteurl}' target='_blank'>{$details->blogname}</a>" : $details->siteurl;
		}
		return join( ', ', $links );
	} else {
		// Single site.
		return $html ? '<a href="' . site_url() . '" target="_blank">' . get_bloginfo( 'name' ) . '</a>' : site_url();
	}
}

/**
 * Generate a create statement for a table that's going to be cloned
 *
 * This removes any constraints / foreign keys from the query to prevent conflicts,
 * and then adds them back at the end with alter table statements. The prefixes have
 * to be provided separately, because we could have a temp target table name, but
 * still want to name the constraint properly with the final target prefix.
 *
 * @param string $source_table Name of table to use for defining the structure.
 * @param string $target_table Name of new table to create.
 * @param string $source_prefix DB prefix for source site.
 * @param string $target_prefix DB prefix for target site.
 * @return string
 */
function ns_sql_create_table_query( $source_table, $target_table, $source_prefix, $target_prefix ) {
	// Create cloned table structure.
	$query             = ns_cloner()->db->get_var( "SHOW CREATE TABLE `$source_table`", 1 );
	$newline           = '(?:\r|\n|\r\n)';
	$view              = '/^CREATE (.*) VIEW/';
	$constraint        = "/,$newline+\s*((?:CONSTRAINT|FOREIGN\s+KEY).+?)(?=,?$newline)/";
	$raw_target_prefix = $target_prefix;
	$target_prefix     = apply_filters( 'ns_cloner_target_table', $target_prefix );
	// Handle views, by creating at end after real tables are copied, and returning blank query for now.
	if ( preg_match( $view, $query ) ) {
		ns_cloner()->log->log( "DETECTING that table *$source_table* is a view. Skipping." );
		// Replace prefix for other table names that view refers to.
		if ( ! apply_filters( 'ns_cloner_skip_views', false ) ) {
			$view_query = str_replace( "`$source_prefix", "`$target_prefix", $query );
			ns_cloner()->process_manager->add_finish_query( $view_query, 200 );
		}
		return '';
	}
	// Match all constraints / foreign keys in create table query.
	preg_match_all( $constraint, $query, $constraint_matches );
	// Save constraints to be applied later in alter table queries.
	if ( ! apply_filters( 'ns_cloner_skip_constraints', false ) ) {
		foreach ( $constraint_matches[1] as $constraint_def ) {
			// Redefine final target table name based on source, instead of using $target_table,
			// because for teleport and clone over, $target_table will have a temp prefix that shouldn't be in alter query.
			$constraint_table = preg_replace( "|^$source_prefix|", $raw_target_prefix, $source_table );
			// Rename prefixes in constraint. Can't look for a backquote before the prefix (assume prefix is at beginning),
			// because some plugins like Woo add extra prefixes like fk_{wpdb_prefix}_something, etc.
			$constraint_def = str_replace( $source_prefix, $raw_target_prefix, $constraint_def );
			// Store alter query in site_options. Use high priority to make sure it executes after all table renames.
			ns_cloner()->process_manager->add_finish_query( "ALTER TABLE `$constraint_table` ADD $constraint_def;", 100 );
		}
	}
	// Now remove constraint statements from the create table query.
	$query = preg_replace( $constraint, '', $query );
	// And rename it to create the new target table.
	$query = str_replace( "$source_table", "$target_table", $query );
	ns_cloner()->log->log( array( "GENERATING create table query for *$target_table*:", $query ) );
	return $query;
}

/**
 * Add backquotes to tables and db names in SQL queries from phpMyAdmin.
 *
 * @param mixed $value Data to wrap in backquotes.
 * @return mixed
 */
function ns_sql_backquote( $value ) {
	if ( ! empty( $value ) && '*' !== $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'ns_sql_backquote', $value );
		} elseif ( 0 !== strpos( $value, '`' ) ) {
			return '`' . $value . '`';
		}
	}
	return $value;
}

/**
 * Quote/format value(s) correctly for being used in an insert query
 *
 * @param mixed $value Data to wrap in quotes.
 * @return mixed
 */
function ns_sql_quote( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'ns_sql_quote', $value );
	} else {
		if ( is_null( $value ) ) {
			return 'NULL';
		} else {
			return "'" . esc_sql( $value ) . "'";
		}
	}
}

/**
 * Get a MySQL variable. [Originally from the Diagnosis plugin by Gary Jones]
 *
 * @param string $variable MySQL variable name to get.
 * @param mixed  $default Default if variable isn't found.
 * @return string
 */
function ns_get_sql_variable( $variable, $default = '' ) {
	$result = ns_cloner()->db->get_row( "SHOW VARIABLES LIKE '$variable';", ARRAY_A );
	return isset( $result['Value'] ) && $result['Value'] ? $result['Value'] : $default;
}

/**
 * Auto-insert the proper multisite-compatible table and column values into an option query
 *
 * @param string $query SQL options/sitemeta query to make replacements on.
 * @param array  $args Arguments to pass to wpdb::prepare.
 * @return string|array
 */
function ns_prepare_option_query( $query, $args = array() ) {
	$details = array(
		'{table}' => is_multisite() ? ns_cloner()->db->sitemeta : ns_cloner()->db->options,
		'{id}'    => is_multisite() ? 'meta_id' : 'option_id',
		'{key}'   => is_multisite() ? 'meta_key' : 'option_name',
		'{value}' => is_multisite() ? 'meta_value' : 'option_value',
	);
	// Replace table/column references with actual values based on whether this is multisite or no.
	$query = str_replace( array_keys( $details ), array_values( $details ), $query );
	// Run normal db query prep.
	$query = ns_cloner()->db->prepare( $query, $args );
	return $query;
}

/**
 * Take a row of data and return the correct placeholders for a prepared statement.
 *
 * Use WPDB defined format for default wp table columns, or default to string.
 *
 * @param array  $row Data to get placeholders for.
 * @param string $table Table name.
 * @return array
 */
function ns_prepare_row_formats( &$row, $table ) {
	$formats = array();
	foreach ( $row as $field => $value ) {
		if ( is_null( $value ) ) {
			$formats[] = 'NULL';
			unset( $row[ $field ] );
		} else {
			$formats[] = apply_filters( 'ns_cloner_row_format', '%s', $field, $table );
		}
	}
	return $formats;
}

/**
 * Organize a list of tables in execution order.
 *
 * Right now that just means putting sitemeta or options first, so that its row count
 * can get counted accurately before the Cloner starts adding temp batch data to it.
 *
 * @param string[] $tables List of table names.
 * @return string[]
 */
function ns_reorder_tables( $tables ) {
	$options_index = null;
	foreach ( $tables as $i => $table ) {
		if ( preg_match( '/(options|sitemeta)$/', $table ) ) {
			$options_index = $i;
		}
	}
	if ( null !== $options_index ) {
		$value = $tables[ $options_index ];
		unset( $tables[ $options_index ] );
		array_unshift( $tables, $value );
	}
	return $tables;
}
