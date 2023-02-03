<?php
/**
 * Main core of NS_Cloner plugin.
 *
 * @package NS_Cloner
 */

/**
 * NS Cloner core class.
 *
 * This class is an umbrella for all cloner components - managing instances of each of the other utility classes,
 * addons, sections, background processes, etc. and letting them refer to each other. It also handles all the basic
 * admin hooks for menus, assets, notices, templates, etc.
 */
final class NS_Cloner {

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version = '4.2.0';

	/**
	 * Menu Slug
	 *
	 * @var string
	 */
	public $menu_slug = 'ns-cloner';

	/**
	 * Capability required to access plugin on network admin
	 *
	 * @var string
	 */
	public $capability = '';

	/**
	 * List of notices to show in admin notice area
	 *
	 * @var array
	 */
	public $admin_notices = array();

	/**
	 * Addons
	 *
	 * @var array
	 */
	public $addons = array();

	/**
	 * Clone modes
	 *
	 * @var array
	 */
	public $clone_modes = array();

	/**
	 * Section objects
	 *
	 * @var array
	 */
	public $sections = array();

	/**
	 * Background process objects
	 *
	 * @var array
	 */
	public $processes = array();

	/**
	 * Instance of NS_Cloner_Process_Manager
	 *
	 * @var NS_Cloner_Process_Manager
	 */
	public $process_manager;

	/**
	 * Instance of NS_Cloner_Schedule
	 *
	 * @var NS_Cloner_Schedule
	 */
	public $schedule;

	/**
	 * Instance of NS_Cloner_Ajax
	 *
	 * @var NS_Cloner_Ajax
	 */
	public $ajax;

	/**
	 * Instance of NS_Cloner_Report
	 *
	 * @var NS_Cloner_Report
	 */
	public $report;

	/**
	 * Instance of NS_Cloner_Log
	 *
	 * @var NS_Cloner_Log object
	 */
	public $log;

	/**
	 * Shortcut reference to access $wpdb without declaring a global in every method
	 *
	 * @var wpdb object
	 */
	public $db;

	/**
	 * Prefix to add to temporary tables by modes that require them
	 *
	 * @var string
	 */
	public $temp_prefix = '_mig_';

	/**
	 * List of hooks that should not be logged, since by default all hooks beginning with ns_cloner are
	 *
	 * @var array
	 */
	public $hidden_hooks = array();

	/**
	 * Singleton instance of NS_Cloner
	 *
	 * @var NS_Cloner
	 */
	private static $instance = null;

	/**
	 * Get singleton instance of NS_Cloner.
	 *
	 * @return NS_Cloner
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * NS_Cloner constructor.
	 */
	private function __construct() {
		// Set instance to prevent infinite loop.
		self::$instance = $this;

		// Create $wpdb access shortcut to save declaring global every place it's used.
		global $wpdb;
		$this->db = $wpdb;

		// Set required capability for cloner pages and operations.
		$default_capability = is_multisite() ? 'manage_sites' : 'manage_options';
		$this->capability   = apply_filters( 'ns_cloner_capability', $default_capability );

		// Add css for admin.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );

		// Add admin menus.
		add_action( 'network_admin_menu', array( $this, 'admin_menu_pages' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu_pages' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 21 );

		// Add quick-clone link.
		add_action( 'manage_sites_action_links', array( $this, 'admin_quick_clone_link' ), 10, 2 );

		// Install custom tables after cloner init.
		add_action( 'ns_cloner_init', array( $this, 'install_tables' ) );

		// Only load rest of plugin if it could be needed (not on frontend).
		$should_load = is_admin() || ( wp_doing_ajax() && is_user_logged_in() ) || ( defined( 'WP_CLI' ) && WP_CLI );
		if ( apply_filters( 'ns_cloner_should_load', $should_load ) ) {
			// Bootstrap full cloner and addons once translation/localization is ready.
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		}
	}

	/**
	 * Initialize Cloner modes, sections, UI, etc.
	 *
	 * The difference between this and the constructor is that anything that needs to use localization has to go here.
	 */
	public function init() {
		load_plugin_textdomain(
			'ns-cloner-site-copier',
			false,
			NS_CLONER_V4_PLUGIN_DIR . 'languages/'
		);

		// Setup class instances.
		$this->process_manager = new NS_Cloner_Process_Manager();
		$this->schedule        = new NS_Cloner_Schedule();
		$this->ajax            = new NS_Cloner_Ajax();
		$this->report          = new NS_Cloner_Report();
		$this->log             = new NS_Cloner_Log();

		// This doesn't need a reference since it's a singleton,
		// but it should be initialized here so it's vars will get set up.
		ns_cloner_request();

		/*
		 * Use this action to load addons, or any other files with need a guarantee that the cloner core classes will
		 * be present, but before any other cloner-specific hooks are run, like ns_cloner_core_modes below.
		 */
		do_action( 'ns_cloner_before_init' );

		// Define the standard, default clone mode.
		$core_modes = apply_filters(
			'ns_cloner_core_modes',
			array(
				'core' => array(
					'title'       => __( 'Standard Clone', 'ns-cloner-site-copier' ),
					'button_text' => __( 'Clone', 'ns-cloner-site-copier' ),
					'description' => __( 'Take an existing site and create a brand new copy of it at another url.', 'ns-cloner-site-copier' ),
					'steps'       => array(
						array( $this->process_manager, 'create_site' ),
						array( $this->process_manager, 'copy_tables' ),
						array( $this->process_manager, 'copy_files' ),
					),
					'report'      => function() {
						// Success message.
						ns_cloner()->report->add_report( '_message', __( 'Site cloned successfully!', 'ns-cloner-site-copier' ) );
						// Source site.
						$source_id = ns_cloner_request()->get( 'source_id' );
						ns_cloner()->report->add_report( __( 'Source Site', 'ns-cloner-site-copier' ), ns_site_link( $source_id ) );
						// Target site.
						$target_id = ns_cloner_request()->get( 'target_id' );
						ns_cloner()->report->add_report( __( 'Target Site', 'ns-cloner-site-copier' ), ns_site_link( $target_id ) );
					},
				),
			)
		);
		foreach ( $core_modes as $id => $details ) {
			$this->register_mode( $id, $details );
		}

		// Register core sections.
		$core_sections = apply_filters(
			'ns_cloner_core_sections',
			array(
				'select_source',
				'create_target',
				'advertise_pro',
				'additional_settings',
			)
		);
		foreach ( $core_sections as $core_section ) {
			$this->register_section( $core_section );
		}

		// Register background processes.
		$processes = apply_filters(
			'ns_cloner_core_processes',
			array(
				'tables',
				'rows',
				'files',
			)
		);
		foreach ( $processes as $process ) {
			$this->register_process( $process );
		}

		/*
		 * This action automatically triggers the init() function for each registered addon.
		 */
		do_action( 'ns_cloner_init' );

	}

	/*
	______________________________________
	|
	|  Admin Setup & Hooks
	|_____________________________________
	*/

	/**
	 * Load admin assets.
	 *
	 * Runs on admin_enqueue_scripts hook.
	 */
	public function admin_enqueue() {
		// Only load cloner assets when on the main cloner page or a subpage of it.
		if ( false !== strpos( get_current_screen()->id, 'ns-cloner' ) ) {
			// Add libs / dependent assets.
			wp_register_script( 'chosen', NS_CLONER_V4_PLUGIN_URL . 'vendor/harvesthq/chosen/chosen.jquery.min.js', array( 'jquery' ), '1.8.7', true );
			wp_register_style( 'chosen', NS_CLONER_V4_PLUGIN_URL . 'vendor/harvesthq/chosen/chosen.min.css', array(), '1.8.7' );
			// Add cloner assets.
			wp_enqueue_style( 'ns-cloner', NS_CLONER_V4_PLUGIN_URL . 'css/ns-cloner.css', array( 'chosen' ), $this->version );
			wp_enqueue_script( 'ns-cloner', NS_CLONER_V4_PLUGIN_URL . 'js/ns-cloner.js', array( 'chosen' ), $this->version, true );
			wp_localize_script(
				'ns-cloner',
				'ns_cloner',
				array(
					'nonce'       => wp_create_nonce( 'ns_cloner' ),
					'ajaxurl'     => admin_url( '/admin-ajax.php' ),
					'loading_img' => NS_CLONER_V4_PLUGIN_URL . 'images/spinner.gif',
					'in_progress' => $this->process_manager->is_in_progress( true ),
				)
			);
			// Run action so addons can easily enqueue scripts only on cloner pages without having to use conditionals.
			do_action( 'ns_cloner_enqueue_scripts' );
		}
	}

	/**
	 * Register admin pages.
	 *
	 * Runs on admin_menu_pages hook.
	 */
	public function admin_menu_pages() {
		add_menu_page(
			__( 'NS Cloner', 'ns-cloner-site-copier' ),
			__( 'NS Cloner', 'ns-cloner-site-copier' ),
			$this->capability,
			$this->menu_slug,
			function() {
				ns_cloner()->render( 'main' );
			},
			plugin_dir_url( __FILE__ ) . 'images/cloner-admin-icon.png',
			is_network_admin() ? 40 : 100
		);
		// Enable addons to register submenus.
		$submenu = apply_filters( 'ns_cloner_submenu', array() );
		// Add logs submenu at bottom.
		$submenu['ns-cloner-logs'] = array(
			$this->menu_slug,
			__( 'Logs / Status', 'ns-cloner-site-copier' ),
			__( 'Logs / Status', 'ns-cloner-site-copier' ),
			$this->capability,
			'ns-cloner-logs',
			function() {
				ns_cloner()->render( 'logs' );
			},
		);
		// Register each submenu item with WP.
		foreach ( apply_filters( 'ns_cloner_submenu', $submenu ) as $item ) {
			call_user_func_array( 'add_submenu_page', $item );
		}
	}

	/**
	 * Add link to admin bar network dropdown for Cloner
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar object.
	 */
	public function admin_bar_menu( $wp_admin_bar ) {
		if ( is_multisite() && current_user_can( 'manage_network' ) ) {
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'ns-cloner',
					'title'  => 'NS Cloner',
					'href'   => network_admin_url( 'admin.php?page=' . $this->menu_slug ),
					'parent' => 'network-admin',
				)
			);
		}
	}

	/**
	 * Add shortcut link to clone a site in the quick edit actions on the Network > Sites page.
	 *
	 * @param array $links Array of action links.
	 * @param int   $blog_id Blog ID of current row.
	 * @return array
	 */
	public function admin_quick_clone_link( $links, $blog_id ) {
		$clone_link     = 'admin.php?' . http_build_query(
			array(
				'page'        => $this->menu_slug,
				'source'      => $blog_id,
				'clone_nonce' => wp_create_nonce( 'ns_cloner' ),
			)
		);
		$links['clone'] = '<span class="clone"><a href="' . network_admin_url( $clone_link ) . '" target="_blank">Clone</a></span>';
		return $links;
	}

	/*
	______________________________________
	|
	|  Utility Functions
	|_____________________________________
	*/

	/**
	 * Include template.
	 *
	 * @param string $template Name of template (ns-template-{$template}.php).
	 * @param string $plugin_dir Path of directory containing the template.
	 */
	public static function render( $template, $plugin_dir = NS_CLONER_V4_PLUGIN_DIR ) {
		$template_file = apply_filters( 'ns_cloner_template_file', 'ns-template-' . $template . '.php', $template );
		$template_dir  = apply_filters( 'ns_cloner_template_dir', $plugin_dir . '/templates/', $template );
		do_action( "ns_cloner_before_render_{$template}", $plugin_dir );
		include_once $template_dir . $template_file;
		do_action( "ns_cloner_after_render_{$template}", $plugin_dir );
	}

	/**
	 * Retrieve list of database tables for a specific site.
	 *
	 * @param int  $site_id Database prefix of the site.
	 * @param bool $exclude_global Exclude global tables from the list (only relevant for main site).
	 * @return array
	 */
	public function get_site_tables( $site_id, $exclude_global = true ) {
		$wp_global_tables  = $this->db->tables( 'global', false, $site_id );
		$all_global_tables = apply_filters( 'ns_cloner_global_tables', $wp_global_tables );
		$global_pattern    = "/^{$this->db->base_prefix}(" . implode( '|', $all_global_tables ) . ')$/';
		$subsite_pattern   = "/^{$this->db->base_prefix}\d+_/";
		$temp_pattern      = '/^' . ns_cloner()->temp_prefix . '/';
		$has_base_prefix   = $this->db->get_blog_prefix( $site_id ) === $this->db->base_prefix;

		if ( empty( $site_id ) || ! is_multisite() ) {
			// All tables - don't filter by any id.
			$prefix = $this->db->esc_like( $this->db->base_prefix );
			$tables = $this->db->get_col( "SHOW TABLES LIKE '{$prefix}%'" );
		} elseif ( ! is_main_site( $site_id ) ) {
			// Sub site tables - a prefix like wp_2_ so we can get all matches without having to filter out global tables.
			$prefix = $this->db->esc_like( $this->db->get_blog_prefix( $site_id ) );
			$tables = $this->db->get_col( "SHOW TABLES LIKE '{$prefix}%'" );
			// Handle special case of a site that USED to be a main site, but then the main site got changed.
			// That means that it will have the base prefix and match everything, so we have to exclude tables
			// from other subsites, from temp tables, and from the global table list.
			if ( $has_base_prefix ) {
				$tables = array_filter(
					$tables,
					function( $table ) use ( $global_pattern, $subsite_pattern, $temp_pattern ) {
						return ! preg_match( $global_pattern, $table )
							&& ! preg_match( $subsite_pattern, $table )
							&& ! preg_match( $temp_pattern, $table );
					}
				);
			}
		} else {
			// Root site tables - a main prefix like wp_ requires that we filter out both global and other subsites' tables.
			$prefix     = $this->db->esc_like( $this->db->base_prefix );
			$all_tables = $this->db->get_col( "SHOW TABLES LIKE '{$prefix}%'" );
			$tables     = array_filter(
				$all_tables,
				function( $table ) use ( $global_pattern, $subsite_pattern, $temp_pattern, $site_id, $exclude_global ) {
					if ( $this->db->base_prefix !== $this->db->get_blog_prefix( $site_id ) ) {
						// For sites where main ID != 1 (example: base = wp_, main site prefix = wp_2_),
						// anything that matches the prefix (example: wp_2_) counts as a site table.
						return preg_match( '/^' . $this->db->get_blog_prefix( $site_id ) . '/', $table )
							|| ( preg_match( $global_pattern, $table ) && ! $exclude_global );
					} else {
						// For normal WP sites where base prefix and main site prefix are the same (example: wp_ for both),
						// we need to exclude all the other temp tables, subsite tables like wp_3_, and possibly global
						// tables (depending on the $exclude_global parameter) that would get otherwise be included.
						return ! preg_match( $subsite_pattern, $table )
							&& ! preg_match( $temp_pattern, $table )
							&& ( ! preg_match( $global_pattern, $table ) || ! $exclude_global );
					}
				}
			);
		}
		// Apply optional filter and return.
		return apply_filters( 'ns_cloner_site_tables', $tables, $site_id );
	}

	/**
	 * Check whether the current user can run a clone operation and whether nonce is valid, then optionally die or return false.
	 *
	 * @param bool $die Whether to die on failure.
	 * @return bool
	 */
	public function check_permissions( $die = true ) {
		$current_action      = ns_cloner_request()->get( 'action', 'default' );
		$required_capability = apply_filters( "ns_cloner_capability_{$current_action}", $this->capability );
		// Check that current user has sufficient permissions.
		if ( ! current_user_can( $required_capability ) ) {
			if ( $die ) {
				if ( wp_doing_ajax() ) {
					wp_die( -1, 403 );
				} else {
					wp_die( esc_html( __( 'You don\'t have sufficient permissions for this action.', 'ns-cloner-site-copier' ) ) );
				}
			} else {
				return false;
			}
		}
		// Check that there is a valid nonce present.
		$valid_nonce = check_ajax_referer( 'ns_cloner', 'clone_nonce', $die );
		if ( ! $valid_nonce ) {
			return false;
		}
		return true;
	}

	/*
	______________________________________
	|
	|  Addon Registration
	|_____________________________________
	*/

	/**
	 * Addons call this to load and register themselves with the Cloner.
	 *
	 * This has no direct impact on functionality, but just makes it easier to refer to the
	 * addon instance elsewhere without re-instantiating or using a global or singleton.
	 *
	 * @param string $id Lowercase, underscore separated unique part of addon classname.
	 *                   Example: 'some_thing' for the class NS_Cloner_Addon_Some_Thing.
	 * @param string $dir Grandparent path (containing /addons/{file}.php).
	 * @return bool
	 */
	public function register_addon( $id, $dir = NS_CLONER_V4_PLUGIN_DIR ) {
		$filename  = str_replace( '_', '-', strtolower( "class-ns-cloner-addon-{$id}.php" ) );
		$path      = apply_filters( 'ns_cloner_addon_path', untrailingslashit( $dir ) . "/addons/{$filename}", $id );
		$suffix    = str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $id ) ) );
		$classname = "NS_Cloner_Addon_{$suffix}";
		include_once $path;
		if ( class_exists( $classname ) ) {
			$this->addons[ $id ] = new $classname();
			return true;
		}
		return false;
	}

	/**
	 * Get the instance of an addon class by its id
	 *
	 * @param string $id Lowercase, underscore separated unique part of addon classname.
	 *                   Example: 'some_thing' for the class NS_Cloner_Addon_Some_Thing.
	 * @return object|bool
	 */
	public function get_addon( $id ) {
		return isset( $this->addons[ $id ] ) ? $this->addons[ $id ] : false;
	}

	/*
	______________________________________
	|
	|  Background Process Registration
	|_____________________________________
	*/

	/**
	 * Includes and registers a background process with the cloner.
	 *
	 * Similar to register_addon, this has no functional impact other than providing an
	 * easy way to reference a background process instance.
	 *
	 * @param string $id Lowercase, underscore separated unique part of process classname.
	 *                   Example: 'some_thing' for the class NS_Cloner_Some_Thing_Process.
	 * @param string $dir Grandparent path (containing /processes/{file}.php).
	 * @return bool
	 */
	public function register_process( $id, $dir = NS_CLONER_V4_PLUGIN_DIR ) {
		$filename  = str_replace( '_', '-', strtolower( "class-ns-cloner-{$id}-process.php" ) );
		$path      = apply_filters( 'ns_cloner_process_path', trailingslashit( $dir ) . "processes/{$filename}", $id );
		$suffix    = implode(
			'_',
			array_map(
				function ( $word ) {
					return ucfirst( $word );
				},
				explode( '_', $id )
			)
		);
		$classname = "NS_Cloner_{$suffix}_Process";
		include_once $path;
		if ( class_exists( $classname ) ) {
			$this->processes[ $id ] = new $classname();
			return true;
		}
		return false;
	}

	/**
	 * Get the instance of an background process class by its id.
	 *
	 * @param string $id Lowercase, underscore separated unique part of process classname.
	 *                   Example: 'some_thing' for the class NS_Cloner_Some_Thing_Process.
	 * @return NS_Cloner_Process
	 */
	public function get_process( $id ) {
		return isset( $this->processes[ $id ] ) ? $this->processes[ $id ] : new $id();
	}

	/*
	______________________________________
	|
	|  Section Registration
	|_____________________________________
	*/

	/**
	 * Addons (or core) call this to include and register new section classes.
	 *
	 * Sections may in turn add ui, validation, processing (new steps or hooking to actions in existing steps), reporting, etc.
	 * Addons can also use the ns_cloner_section_pat filter to override an existing section.
	 *
	 * @param string $id The $id property of the section (must match classname and filename).
	 *                   Example: 'some_thing' for the class NS_Cloner_Section_Some_Thing.
	 * @param string $dir Grandparent path (containing /sections/{file}.php).
	 * @return bool
	 */
	public function register_section( $id, $dir = NS_CLONER_V4_PLUGIN_DIR ) {
		$filename  = str_replace( '_', '-', strtolower( "class-ns-cloner-section-{$id}.php" ) );
		$path      = apply_filters( 'ns_cloner_section_path', trailingslashit( $dir ) . "sections/{$filename}", $id );
		$suffix    = str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $id ) ) );
		$classname = "NS_Cloner_Section_{$suffix}";
		include_once $path;
		if ( class_exists( $classname ) ) {
			$this->sections[ $id ] = new $classname();
			return true;
		}
		return false;
	}

	/**
	 * Get the instance of an section class by its id.
	 *
	 * @param string $id The $id property of the section (must match classname and filename).
	 *                   Example: 'some_thing' for the class NS_Cloner_Section_Some_Thing.
	 * @return object|bool
	 */
	public function get_section( $id ) {
		return isset( $this->sections[ $id ] ) ? $this->sections[ $id ] : false;
	}

	/*
	______________________________________
	|
	|  Cloning Mode Registration
	|_____________________________________
	*/

	/**
	 * Addons call this to register a new mode in the dropdown (or override/replace an existing one).
	 *
	 * Note that details are provided as an array but stored (and thus later returned) as an object
	 * for ease of reference. This also fills in defaults so the mode properties can be safely referred
	 * to elsewhere without having to check isset().
	 *
	 * @param string $id Slug of section, separated by underscores.
	 * @param array  $details {
	 *     Properties of mode.
	 *     @type string $title User-friendly title of mode.
	 *     @type string description Describes what the mode does.
	 *     @type string $button_text Text for submit button on main cloner page when mode is active.
	 *     @type boolean $multisite_only Show this mode only on multisite installations.
	 *     @type array $steps Array of functions to call when this mode is running - cloning steps.
	 *     @type callable $report Function that adds mode-specific report items to the summary shown after running mode.
	 * }
	 */
	public function register_mode( $id, $details ) {
		$defaults = array(
			'title'          => '',
			'button_text'    => '',
			'description'    => '',
			'multisite_only' => true,
			'steps'          => array(),
			'report'         => function(){},
		);
		// Register by adding to the clone_modes array.
		$this->clone_modes[ $id ] = (object) wp_parse_args( $details, $defaults );
		// Auto-register any provided steps for this clone mode.
		foreach ( $this->clone_modes[ $id ]->steps as $index => $callback ) {
			// Make 1st step priority 10, 2nd is 20, etc. - makes it easier to add new steps in between existing ones.
			$priority = ( $index + 1 ) * 10;
			$this->register_step( $callback, $id, $priority );
		}
	}

	/**
	 * Get the data assigned to a specific mode
	 *
	 * @param string $id ID of clone mode.
	 * @return object|bool
	 */
	public function get_mode( $id = '' ) {
		$id = ! empty( $id ) ? $id : ns_cloner_request()->get( 'clone_mode' );
		return isset( $this->clone_modes[ $id ] ) ? $this->clone_modes[ $id ] : false;
	}

	/**
	 * Get array of registered clone modes, optionally filtered to only those that should be visible.
	 *
	 * @param bool $show_hidden Whether to include modes that should not be publicly available.
	 * @return array
	 */
	public function get_modes( $show_hidden = false ) {
		$modes = array();
		foreach ( $this->clone_modes as $mode_id => $details ) {
			// Skip clone modes that don't support single site usage, if this is not network admin.
			if ( ! $show_hidden && ! is_network_admin() && true === $details->multisite_only ) {
				continue;
			}
			// Skip hidden clone modes beginning with an underscore (similar convention to hidden post_meta keys).
			if ( ! $show_hidden && substr( $mode_id, 0, 1 ) === '_' ) {
				continue;
			}
			$modes[ $mode_id ] = $details;
		}
		return $modes;
	}

	/*
	______________________________________
	|
	|  Cloning Step Registration
	|_____________________________________
	*/

	/**
	 * Adds a new step to existing clone mode(s)
	 *
	 * This works as a wrapper for add_action, with the key addition that it enables easy addition
	 * of a step for 1 OR multiple clone modes. Then by adding an action for each step, every step
	 * (including default steps for each mode, which will have register_step() called automatically by
	 * register_mode()) will be called according to its priority when NS_Cloner_Processes::process_init
	 * runs the ns_cloner_process_{clone_mode} action.
	 *
	 * @see NS_Cloner_Process_Manager::init()
	 * @param callable     $callback Function to call during process_init.
	 * @param array|string $clone_mode Mode or modes that support this step (and should call it).
	 * @param int          $priority Priority for add_action().
	 */
	public function register_step( $callback, $clone_mode, $priority = 100 ) {
		$clone_modes = (array) $clone_mode;
		foreach ( $clone_modes as $clone_mode_id ) {
			add_action(
				"ns_cloner_process_{$clone_mode_id}",
				function() use ( $callback ) {
					// Get the function name to refer to and log it.
					$callback_name = is_array( $callback ) ? $callback[1] : $callback;
					$callback_ref  = is_array( $callback ) ? get_class( $callback[0] ) . '::' . $callback[1] : $callback;
					$this->log->log_break();
					$this->log->log( "STARTING clone step {$callback_ref}." );
					// Make sure a previous step didn't trigger an error, and that this step isn't disabled by filters.
					$do_step = empty( $this->report->get_report( '_error' ) ) && ! get_site_option( 'ns_cloner_exited' );
					if ( apply_filters( "ns_cloner_do_step_{$callback_name}", $do_step ) ) {
						call_user_func( $callback );
					} else {
						$this->log->log( "SKIPPING because ns_cloner_do_step_{$callback_name} was false." );
					}
				},
				$priority
			);
		}
	}

	/**
	 * Install custom Cloner tables
	 */
	public function install_tables() {
		if ( get_site_option( 'ns_cloner_installed_version' ) !== $this->version ) {
			// Required for using dbDelta function.
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$table_name = ns_cloner_analytics()->get_db_log_table();
			global $wpdb;
			$query = "
	            CREATE TABLE `{$table_name}` (
	            id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT, 
	            version VARCHAR(50) NOT NULL,
	            is_success INT(1) NOT NULL,
	            clone_mode VARCHAR(100) NOT NULL,
	            date DATETIME NOT NULL,
	            time_spent_sec INT(20) NOT NULL,
	            tables_count int(20) DEFAULT NULL,
	            rows_count INT(20) DEFAULT NULL,
	            files_count INT(20) DEFAULT NULL,
	            users_count INT(20) DEFAULT NULL,
	            replacements_count INT(20) DEFAULT NULL,
	            wp_data TEXT DEFAULT NULL,
	            is_synced INT(1) NOT NULL,
	            PRIMARY KEY (id)
	            ) {$wpdb->get_charset_collate()};
	        ";
			dbDelta( $query );
			update_site_option( 'ns_cloner_installed_version', $this->version );
		}
	}
}

/**
 * Return singleton instance of NS_Cloner (replaces the global $ns_cloner variable used in previous versions)
 *
 * @return NS_Cloner
 */
function ns_cloner() {
	return NS_Cloner::get_instance();
}
