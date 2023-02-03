<?php
/**
 * Cloner Addon base class.
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Base class for all NS Cloner addons
 *
 * Add-ons used to be their own standalone plugins - they are now just logical containers
 * for groups of related functionality (modes, settings, and their supporting functions)
 */
abstract class NS_Cloner_Addon {

	/**
	 * Display name for the addon (not currently used/displayed).
	 *
	 * This was used in the previous architecture where each addon was a standalone plugin,
	 * and extensions were bought separately. Leaving here in case we ever use it again.
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Path to the plugin directory that contains the addon.
	 *
	 * Should be defined in the constructor of the child class.
	 * Useful for registering sections and background processes
	 * (provide path to NS_Cloner functions autoload them).
	 *
	 * @var string
	 */
	public $plugin_path = '';

	/**
	 * URL to the plugin directory that contains the addon.
	 *
	 * Should be defined in the constructor of the child class.
	 * Useful for enqueueing assets
	 *
	 * @var string
	 */
	public $plugin_url = '';

	/**
	 * Registers actions to save repetition for descendant classes
	 */
	public function __construct() {
		add_action( 'ns_cloner_init', array( $this, 'init' ) );
		add_action( 'ns_cloner_enqueue_scripts', array( $this, 'admin_enqueue' ) );
	}

	/**
	 * Runs after core modes and sections are loaded - use this to register new modes and sections
	 */
	public function init() {
	}

	/**
	 * Runs on wp_enqueue_scripts, but only on Cloner admin pages
	 */
	public function admin_enqueue() {
	}

}

