<?php
/**
 * Cloner Request class.
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * NS_Cloner_Request class
 *
 * Utility class to define and access request data - settings for the current cloning operation
 * that are usually submitted via a POST request (but could be manually set) and then are saved
 * in a site option for the duration of that cloning process, and cleared at the end. Should
 * generally be the go-to source for POST and GET data rather than referring to those directly.
 *
 * Also handles calculating basic variables - like db prefixes, upload dirs, etc. - for cloning
 * operations and search/replace values.
 */
final class NS_Cloner_Request {

	/**
	 * Request Data
	 *
	 * @var array
	 */
	private $request = array();

	/**
	 * Option key to save stored request to
	 *
	 * @var string
	 */
	private static $option_key = 'ns_cloner_saved_request';

	/**
	 * List of default variables to be defined for source and target sites
	 *
	 * @var array
	 */
	private static $vars = array(
		'prefix',
		'upload_dir',
		'upload_url',
		'url',
		'url_short',
	);

	/**
	 * Singleton instance of this class
	 *
	 * @var NS_Cloner_Request
	 */
	private static $instance;

	/**
	 * NS_Cloner_Request constructor.
	 */
	private function __construct() {
		// Load request from saved request option if present, enabling background processes to stay in sync.
		$request = (array) get_site_option( 'ns_cloner_saved_request', array() );
		// Enable $_GET and $_POST to override / fill in gaps in the saved request.
		// Verifying the nonce here shouldn't be needed, because it is checked elsewhere before performing any actions.
		// However, this check is here as a safety precaution so the cloner request object can't ever somehow get injected.
		// phpcs:ignore WordPress.Security -- safely checking the nonce here.
		$nonce = isset( $_REQUEST['clone_nonce'] ) ? $_REQUEST['clone_nonce'] : '';
		if ( wp_verify_nonce( $nonce, 'ns_cloner' ) ) {
			$request = array_merge( $request, wp_unslash( $_GET ), wp_unslash( $_POST ) );
		}
		$this->request = $request;
	}

	/**
	 * Disable cloning
	 */
	private function __clone() {
	}

	/**
	 * Disable unserialize
	 */
	public function __wakeup() {
	}

	/**
	 * Get singleton instance
	 *
	 * @return NS_Cloner_Request
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Reload the request from the saved version in the database
	 *
	 * @return $this
	 */
	public function refresh() {
		$this->request = (array) get_site_option( 'ns_cloner_saved_request', array() );
		return $this;
	}

	/**
	 * Get all current request variables
	 *
	 * @return array
	 */
	public function get_request() {
		return $this->request;
	}

	/**
	 * Get a request variable
	 *
	 * @param string $key Key of request array.
	 * @param mixed  $default Default value.
	 *
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		return isset( $this->request[ $key ] ) ? $this->request[ $key ] : $default;
	}

	/**
	 * Set a request variable
	 *
	 * @param string $key Key of request array.
	 * @param string $value Value to set.
	 */
	public function set( $key, $value ) {
		$this->request[ $key ] = $value;
		ns_cloner()->log->log( array( "SETTING REQUEST VAR '$key' to:", $value ) );
	}

	/**
	 * Save the current request into site options for later reference by background processes
	 */
	public function save() {
		update_site_option( self::$option_key, $this->request );
		ns_cloner()->log->log( array( 'SAVING REQUEST:', $this->request ) );
	}

	/**
	 * Save the current request into site options for later reference by background processes
	 */
	public function delete() {
		delete_site_option( self::$option_key );
		ns_cloner()->log->log( 'DELETING REQUEST' );
	}

	/**
	 * Generate definitions for site variables
	 *
	 * If null, 'network', or another string is provided as the site_id, it defaults to the main site
	 * (either the only site for single installs, or the main site on the network for multisite).
	 * Teleport uses this to provide a string (remote site url) rather than an ID, and then uses
	 * the filter at the bottom to return the correct variables.
	 *
	 * IMPORTANT: This cannot be called for target values during the middle of a cloning operation,
	 * because the target options table could be empty and site_url() will return empty.
	 *
	 * @param int $site_id Blog id of site to get variables for.
	 * @return array
	 */
	public function define_vars( $site_id = null ) {
		$is_subsite = is_multisite() && ! is_null( $site_id ) && is_numeric( $site_id );
		if ( $is_subsite ) {
			switch_to_blog( $site_id );
		}
		// Get site url directly rather than with site_url(), because option/object
		// caching can result in a blank value for a newly created site.
		$option_q = 'SELECT option_value FROM ' . ns_cloner()->db->options . " WHERE option_name='siteurl'";
		$site_url = set_url_scheme( ns_cloner()->db->get_var( $option_q ) );
		// Past Cloner versions had manual checking/overrides for wp_upload_dir.
		// However, it seems that wp_upload_dir() is now more reliable, whereas the
		// overrides were beginning to cause problems. If a fix is needed on a case
		// by case basis for when wp_upload_dir() is overwritten by a filter (e.g.
		// compatibility with another plugin), we could add a small patch plugin OR
		// add a filter in ns-compatibility.php to filter ns_cloner_request_define_vars.
		$upload_dir = wp_upload_dir();
		// If the upload_url_path option is blank, _wp_upload_dir will use WP_CONTENT_URL,
		// with the domain set to the network domain, not the current blog's domain, so fix it.
		$upload_url = str_replace( WP_CONTENT_URL, $site_url, $upload_dir['baseurl'] );
		// These definitions should all work both for multisite (after using switch_blog above
		// so they have the correct sub-site values) as well as single site / whole network.
		$vars = array(
			'prefix'              => ns_cloner()->db->prefix,
			'upload_dir'          => $upload_dir['basedir'],
			'upload_dir_relative' => str_replace( ABSPATH, '', $upload_dir['basedir'] ),
			'upload_url'          => $upload_url,
			'upload_url_relative' => str_replace( $site_url, '', $upload_url ),
			'url'                 => $site_url,
			'url_short'           => untrailingslashit( preg_replace( '|^(https?:)?//|', '', $site_url ) ),
		);
		if ( $is_subsite ) {
			restore_current_blog();
		}
		return apply_filters( 'ns_cloner_request_define_vars', $vars, $site_id );
	}

	/**
	 * Add source and target vars to the current cloner request
	 *
	 * Take definitions from define_vars() for source and target ids, if applicable,
	 * and add them to the current cloner request array.
	 */
	public function set_up_vars() {
		$source_id = $this->get( 'source_id' );
		if ( $source_id ) {
			foreach ( $this->define_vars( $source_id ) as $key => $value ) {
				$this->set( "source_{$key}", $value );
			}
		}
		$target_id = $this->get( 'target_id' );
		if ( $target_id ) {
			foreach ( $this->define_vars( $target_id ) as $key => $value ) {
				$this->set( "target_{$key}", $value );
			}
		}
		if ( $source_id && $target_id ) {
			$this->set_up_search_replace( $source_id, $target_id );
		}
	}

	/**
	 * Set up search / replace value arrays
	 *
	 * @param int|null $source_id ID of source site.
	 * @param int|null $target_id ID of target site.
	 */
	public function set_up_search_replace( $source_id = null, $target_id = null ) {
		$source_id  = $source_id ?: $this->get( 'source_id' );
		$target_id  = $target_id ?: $this->get( 'target_id' );
		$option_key = "ns_cloner_search_{$source_id}_replace_{$target_id}";
		// Generate arrays and save if not.
		$search  = array(
			$this->request['source_upload_dir_relative'],
			$this->request['source_upload_url'],
			$this->request['source_url_short'],
			$this->request['source_prefix'] . 'user_roles',
		);
		$replace = array(
			$this->request['target_upload_dir_relative'],
			$this->request['target_upload_url'],
			$this->request['target_url_short'],
			$this->request['target_prefix'] . 'user_roles',
		);

		$search  = apply_filters( 'ns_cloner_search_items_before_sequence', $search );
		$replace = apply_filters( 'ns_cloner_replace_items_before_sequence', $replace );

		// Sort and filter replacements to intelligently avoid compounding replacement issues.
		ns_set_search_replace_sequence( $search, $replace );
		// Add filters that enable custom replacements to be applied.
		$search_replace = array(
			'search'  => apply_filters( 'ns_cloner_search_items', $search ),
			'replace' => apply_filters( 'ns_cloner_replace_items', $replace ),
		);
		// Save in settings for use by background processes.
		update_site_option( $option_key, $search_replace );
		ns_cloner()->log->log( array( "SETTING search/replace for source *$source_id* and target *$target_id*:", $search_replace ) );
	}

	/**
	 * Get saved search / replace value arrays
	 *
	 * @param int|null $source_id ID of source site.
	 * @param int|null $target_id ID of target site.
	 * @return array
	 */
	public function get_search_replace( $source_id = null, $target_id = null ) {
		$source_id  = $source_id ?: $this->get( 'source_id' );
		$target_id  = $target_id ?: $this->get( 'target_id' );
		$option_key = "ns_cloner_search_{$source_id}_replace_{$target_id}";
		return get_site_option( $option_key );
	}

	/**
	 * Shortcut to check if the current mode is equal to a provided one (or in a provided list).
	 *
	 * @param string|array $mode_id Mode id or array of them to compare to the current mode.
	 * @return bool
	 */
	public function is_mode( $mode_id ) {
		if ( is_array( $mode_id ) ) {
			return in_array( $this->get( 'clone_mode' ), $mode_id, true );
		} else {
			return $this->get( 'clone_mode' ) === $mode_id;
		}
	}
}

/**
 * Get the current singleton request instance
 *
 * @return NS_Cloner_Request
 */
function ns_cloner_request() {
	return NS_Cloner_Request::instance();
}

