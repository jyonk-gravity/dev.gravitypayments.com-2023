<?php
/*
  Plugin Name: Oasis Workflow Pro
  Plugin URI: https://www.oasisworkflow.com
  Description: Automate your WordPress Editorial Workflow with Oasis Workflow.
  Version: 10.4
  Author: Nugget Solutions Inc.
  Author URI: https://www.nuggetsolutions.com
  Text Domain: oasisworkflow
  ----------------------------------------------------------------------
  Copyright 2011-2026 Nugget Solutions Inc.
 */


define( 'OASISWF_VERSION', '10.4' );
define( 'OASISWF_DB_VERSION', '10.4' );
define( 'OASISWF_PATH', plugin_dir_path( __FILE__ ) ); //use for include files to other files
define( 'OASISWF_ROOT', dirname( __FILE__ ) );
define( 'OASISWF_FILE_PATH', OASISWF_ROOT . '/' . basename( __FILE__ ) );
define( 'OASISWF_URL', plugins_url( '/', __FILE__ ) );
define( 'OASISWF_SETTINGS_PAGE',
	esc_url( add_query_arg( 'page', 'ef-settings', get_admin_url( null, 'admin.php' ) ) ) );
define( 'OASISWF_STORE_URL', 'https://www.oasisworkflow.com' );
define( 'OASISWF_PRODUCT_NAME', 'Oasis Workflow Pro' );
define( 'OASISWF_EDIT_DATE_FORMAT', 'm-M d, Y' );
define( 'OASISWF_DATE_TIME_FORMAT', 'm-M d, Y @ H:i' );
define( 'OASIS_PER_PAGE', '50' );
load_plugin_textdomain( 'oasisworkflow', false, basename( dirname( __FILE__ ) ) . '/languages' );


/*
 * include utility classes
 */

if ( ! class_exists( 'OW_Utility' ) ) {
	include( OASISWF_PATH . 'includes/class-ow-utility.php' );
}
if ( ! class_exists( 'OW_Admin_Post' ) ) {
	include( OASISWF_PATH . 'includes/class-ow-admin-post.php' );
}
if ( ! class_exists( 'OW_Custom_Statuses' ) ) {
	include( OASISWF_PATH . 'includes/class-ow-custom-statuses.php' );
}
if ( ! class_exists( 'OW_Email_Settings_Helper' ) ) {
	include( OASISWF_PATH . 'includes/class-ow-email-settings-helper.php' );
}

/**
 * OW_Plugin_Init Class
 *
 * This class will set the plugin
 *
 * @since 2.0
 */
class OW_Plugin_Init {

	private $current_screen_pointers = array();

	/*
	 * Set things up.
	 *
	 * @since 2.0
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'register_table_names' ), 11 );

		//run on activation of plugin
		register_activation_hook( __FILE__, array( $this, 'oasis_workflow_activate' ) );

		//run on deactivation of plugin
		register_deactivation_hook( __FILE__, array( $this, 'oasis_workflow_deactivate' ) );

		//run on uninstall
		register_uninstall_hook( __FILE__, array( 'OW_Plugin_Init', 'oasis_workflow_uninstall' ) );

		add_action( 'admin_init', array( $this, 'validate_lite_version_exists' ) );

		// add custom interval for Auto Submit
		add_filter( 'cron_schedules', array( $this, 'custom_cron_interval' ) );

		// make workflow inbox as the landing page
		add_filter( 'login_redirect', array( $this, 'dashboard_redirect' ), 10, 3 );

		// load the js and css files
		add_action( 'init', array( $this, 'load_css_and_js_files' ) );
		add_action( 'admin_head', array( $this, 'add_css' ) );

		// load the classes
		add_action( 'init', array( $this, 'load_all_classes' ) );

		// register custom post types
		add_action( 'init', array( $this, 'register_custom_post_types' ) );

		// register custom post meta
		add_action( 'init', array( $this, 'register_custom_post_meta' ) );

		add_action( 'admin_menu', array( $this, 'register_menu_pages' ) );

		add_action( 'wpmu_new_blog', array( $this, 'run_on_add_blog' ), 10, 6 );
		add_action( 'delete_blog', array( $this, 'run_on_delete_blog' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'run_on_upgrade' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'show_welcome_message_pointers' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_workflow_tasks_summary_widget' ) );

		// Hook scripts function into block editor hook
		add_action( 'enqueue_block_assets', array( $this, 'ow_gutenberg_scripts' ) );

		// Hook for elementor scripts and css
		add_action( 'elementor/editor/footer', array( $this, 'ow_elementor_scripts' ) );

        // Add REST API support to 'wp_block' post type
        add_filter( 'register_post_type_args', array( $this, 'ow_wp_block_args' ), 10, 2 );

	}

	/**
	 *  Runs on plugin uninstall.
	 *  a static class method or function can be used in an uninstall hook
	 *
	 * @since 2.0
	 */

	public static function oasis_workflow_uninstall() {
		global $wpdb;
		OW_Plugin_Init::run_on_uninstall();
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					OW_Plugin_Init::clear_scheduled_hooks();
					OW_Plugin_Init::delete_for_site();
				}
				restore_current_blog();
			}

			return;
		}
		OW_Plugin_Init::clear_scheduled_hooks();
		OW_Plugin_Init::delete_for_site();
	}

	/**
	 * Called on uninstall - deletes site_options
	 *
	 * @since 2.0
	 */
	private static function run_on_uninstall() {
		if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			exit();
		}

		global $wpdb; //required global declaration of WP variable
		delete_site_option( 'oasiswf_info' );
		delete_site_option( 'oasiswf_process' );
		delete_site_option( 'oasiswf_path' );
		delete_site_option( 'oasiswf_status' );
		delete_site_option( 'oasiswf_placeholders' );

		// delete the dismissed_wp_pointers entry for this plugin
		$blog_users = get_users( 'role=administrator' );
		foreach ( $blog_users as $user ) {
			$dismissed = explode( ',', (string) get_user_meta( $user->ID, 'dismissed_wp_pointers', true ) );
			if ( ( $key = array_search( "owf_install", $dismissed ) ) !== false ) {
				unset( $dismissed[ $key ] );
			}

			$updated_dismissed = implode( ",", $dismissed );
			update_user_meta( $user->ID, "dismissed_wp_pointers", $updated_dismissed );
		}
	}

	/**
	 * Called on uninstall OR blog delete to clear the scheduled hooks
	 *
	 * @since 2.0
	 */
	private static function clear_scheduled_hooks() {
		global $wpdb;
		/*
		 * Mail schedule remove
		 */
		wp_clear_scheduled_hook( 'oasiswf_email_schedule' );

		/*
		 * Auto Submit schedule remove
		*/
		wp_clear_scheduled_hook( 'oasiswf_auto_submit_schedule' );

		/*
		 * Email digest schedule remove
		*/
		wp_clear_scheduled_hook( 'oasiswf_email_digest_schedule' );

		/*
		 * Auto delete history schedule remove
		 */
		wp_clear_scheduled_hook( 'oasiswf_auto_delete_history_schedule' );

		/*
		 * Scheduled update of revised articles
		*/
		/*
		 * If you created a scheduled job using a hook and arguments
		 * you cannot delete it by supplying only the hook.
		 * Similarly if you created a set of scheduled jobs that share a
		 * hook but have different arguments you cannot delete them using
		 * only the hook name,
		 * you have to delete them all individually using the hook name and arguments.
		*/
		wp_clear_scheduled_hook( 'oasiswf_schedule_revision_update' );
		OW_Plugin_Init::clear_scheduled_revision_hooks();

	}

	/**
	 * to clear/delete all the scheduled revision update hooks.
	 *
	 * @since 2.0
	 */
	private static function clear_scheduled_revision_hooks() {
		$scheduled_posts = get_posts( array( 'post_type' => 'owf_scheduledrev' ) );

		foreach ( $scheduled_posts as $post ) {
			$args                     = array( $post->ID, "owf_scheduledrev" );
			$revision_update_schedule = wp_get_schedule( 'oasiswf_schedule_revision_update', $args );
			if ( $revision_update_schedule && ! empty( $revision_update_schedule ) ) {
				wp_clear_scheduled_hook( 'oasiswf_schedule_revision_update', $args );
			}
		}
	}

	/**
	 * Called on uninstall - deletes site specific options
	 *
	 * @since 2.0
	 */
	private static function delete_for_site() {
		global $wpdb;

		// deactivate the license
		$license = trim( get_option( 'oasiswf_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( OASISWF_PRODUCT_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( OASISWF_STORE_URL, array( 'timeout' => 15, 'body' => $api_params ) ); // phpcs:ignore

		delete_option( 'oasiswf_license_status' );
		delete_option( 'oasiswf_license_key' );

		/*
		 * Include the custom capability class
		 */
		if ( ! class_exists( 'OW_Custom_Capabilities' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-custom-capabilities.php' );
		}

		$ow_custom_capabilities = new OW_Custom_Capabilities();
		$ow_custom_capabilities->remove_capabilities();

		delete_option( 'oasiswf_activate_workflow' );
		delete_option( 'oasiswf_doc_revision_title_prefix' );
		delete_option( 'oasiswf_doc_revision_title_suffix' );
		delete_option( 'oasiswf_default_due_days' );
		delete_option( 'oasiswf_reminder_days' );
		delete_option( 'oasiswf_show_wfsettings_on_post_types' );
		delete_option( 'oasiswf_reminder_days_after' );
		delete_option( 'oasiswf_auto_submit_settings' );

		delete_option( 'oasiswf_delete_revision_on_copy' );
		delete_option( 'oasiswf_copy_children_on_revision' );
		delete_option( 'oasiswf_activate_revision_process' );
		delete_option( 'oasiswf_revise_post_make_revision_overlay' );
		delete_option( 'oasiswf_preserve_revision_of_revised_article' );

		// delete all the post meta created by this plugin
		delete_post_meta_by_key( '_oasis_original' );
		delete_post_meta_by_key( '_oasis_is_in_workflow' );
		delete_post_meta_by_key( '_oasis_current_revision' );
		delete_post_meta_by_key( '_oasis_is_in_team' );
		delete_post_meta_by_key( '_oasis_task_priority' );

		delete_option( 'oasiswf_email_settings' );
		delete_option( 'oasiswf_roles_can_bulk_approval' );
		delete_option( 'oasiswf_hide_compare_button' );
		delete_option( 'oasiswf_custom_workflow_terminology' );
		delete_option( 'oasiswf_priority_setting' );
		delete_option( 'oasiswf_comments_setting' );
		delete_option( 'oasiswf_revise_post_roles' );
		delete_option( 'oasiswf_participating_roles_setting' );
		delete_option( 'oasiswf_publish_date_setting' );
		delete_option( 'oasiswf_step_due_date_settings' );
		delete_option( 'oasiswf_login_redirect_roles_setting' );
		delete_option( 'oasiswf_auto_delete_history_setting' );
		delete_option( 'oasiswf_post_publish_email_settings' );
		delete_option( 'oasiswf_revised_post_email_settings' );
		delete_option( 'oasiswf_unauthorized_update_email_settings' );
		delete_option( 'oasiswf_task_claim_email_settings' );
		delete_option( 'oasiswf_post_submit_email_settings' );
		delete_option( 'oasiswf_workflow_abort_email_settings' );
		delete_option( 'oasiswf_external_user_settings' );


		$wpdb->query( "DELETE FROM {$wpdb->prefix}options WHERE option_name like 'workflow_%'" );

		$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fc_emails" );
		$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fc_action_history" );
		$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fc_action" );
		$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fc_workflow_steps" );
		$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "fc_workflows" );
	}

	/**
	 * Activate the plugin
	 *
	 * @since 2.0
	 */
	public function oasis_workflow_activate( $network_wide ) {
		global $wpdb;
		$this->run_on_activation();
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if ( $network_wide ) {
				// Get all blog ids
				$blogids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->run_for_site();
					restore_current_blog();
				}

				return;
			}
		}

		// for non-network sites only
		$this->run_for_site();
	}

	/**
	 * Called on activation.
	 * Creates the site_options (required for all the sites in a multi-site setup)
	 * If the current version doesn't match the new version, runs the upgrade
	 * Also created the cron schedules for - auto submit and reminder emails
	 *
	 * @since 2.0
	 */
	private function run_on_activation() {
		$plugin_options = get_site_option( 'oasiswf_info' );
		if ( false === $plugin_options ) {
			$oasiswf_info = array(
				'version'    => OASISWF_VERSION,
				'db_version' => OASISWF_DB_VERSION
			);

			$oasiswf_process_info = array(
				'assignment' => OASISWF_URL . 'img/assignment.gif',
				'review'     => OASISWF_URL . 'img/review.gif',
				'publish'    => OASISWF_URL . 'img/publish.gif'
			);

			$oasiswf_path_info = array(
				'success' => array( esc_html__( 'Success', 'oasisworkflow' ), 'blue' ),
				'failure' => array( esc_html__( 'Failure', 'oasisworkflow' ), 'red' )
			);

			$oasiswf_status = array(
				'assignment' => esc_html__( 'In Progress', 'oasisworkflow' ),
				'review'     => esc_html__( 'In Review', 'oasisworkflow' ),
				'publish'    => esc_html__( 'Ready to Publish', 'oasisworkflow' )
			);

			$oasiswf_placeholders = array(
				'%first_name%'         => esc_html__( 'first name', 'oasisworkflow' ),
				'%last_name%'          => esc_html__( 'last name', 'oasisworkflow' ),
				'%post_title%'         => esc_html__( 'post title', 'oasisworkflow' ),
				'%post_id%'            => esc_html__( 'post ID', 'oasisworkflow' ),
				'%category%'           => esc_html__( 'category', 'oasisworkflow' ),
				'%last_modified_date%' => esc_html__( 'last modified date', 'oasisworkflow' ),
				'%post_author%'        => esc_html__( 'post author', 'oasisworkflow' ),
				'%blog_name%'          => esc_html__( 'blog name', 'oasisworkflow' ),
				'%post_submitter%'     => esc_html__( 'post submitter', 'oasisworkflow' )
			);

			$oasiswf_email_placeholders = array(
				'{first_name}'         => esc_html__( 'First Name', 'oasisworkflow' ),
				'{last_name}'          => esc_html__( 'Last Name', 'oasisworkflow' ),
				'{post_title}'         => esc_html__( 'Post Title, this will be displayed as a link', 'oasisworkflow' ),
				'{post_id}'            => esc_html__( 'Post ID', 'oasisworkflow' ),
				'{category}'           => esc_html__( 'Category', 'oasisworkflow' ),
				'{last_modified_date}' => esc_html__( 'Last Modified Date', 'oasisworkflow' ),
				'{publish_date}'       => esc_html__( 'Publish Date', 'oasisworkflow' ),
				'{post_author}'        => esc_html__( 'Post Author', 'oasisworkflow' ),
				'{blog_name}'          => esc_html__( 'Blog Name', 'oasisworkflow' ),
				'{current_user}'       => esc_html__( 'Current User', 'oasisworkflow' )
			);

			update_site_option( 'oasiswf_info', $oasiswf_info );
			update_site_option( 'oasiswf_process', $oasiswf_process_info );
			update_site_option( 'oasiswf_path', $oasiswf_path_info );
			update_site_option( 'oasiswf_status', $oasiswf_status );
			update_site_option( 'oasiswf_placeholders', $oasiswf_placeholders );
			update_site_option( 'oasiswf_email_placeholders', $oasiswf_email_placeholders );
		} elseif ( OASISWF_VERSION != $plugin_options['version'] ) {
			$this->run_on_upgrade();
		}

		if ( ! wp_next_scheduled( 'oasiswf_email_schedule' ) ) {
			wp_schedule_event( time(), 'daily', 'oasiswf_email_schedule' );
		}

		if ( ! wp_next_scheduled( 'oasiswf_auto_submit_schedule' ) ) {
			wp_schedule_event( time(), 'hourly', 'oasiswf_auto_submit_schedule' );
		}

		if ( ! wp_next_scheduled( 'oasiswf_email_digest_schedule' ) ) {
			wp_schedule_event( time(), 'hourly', 'oasiswf_email_digest_schedule' );
		}

		if ( ! wp_next_scheduled( 'oasiswf_auto_delete_history_schedule' ) ) {
			wp_schedule_event( time(), 'daily', 'oasiswf_auto_delete_history_schedule' );
		}

		if ( ! wp_next_scheduled( 'oasiswf_revision_delete_schedule' ) ) {
			wp_schedule_event( time(), 'hourly', 'oasiswf_revision_delete_schedule' );
		}
	}

    /**
     * Add REST API support to 'wp_block' post type
     */
    public function ow_wp_block_args( $args, $post_type ) {

        $allowed_post_types = get_option( 'oasiswf_show_wfsettings_on_post_types' );

        if ( 
            isset( $allowed_post_types ) && 
            ! empty( $allowed_post_types ) && 
            in_array( 'wp_block', $allowed_post_types ) && 
            'wp_block' === $post_type 
        ) {
            $args['show_in_rest'] = true;
            array_push( $args['supports'], 'custom-fields' );
            array_push( $args['supports'], 'revisions' );
        }
       
        return $args;
    }

	/**
	 * called on upgrade. checks the current version and applies the necessary upgrades from that version onwards
	 *
	 * @since 2.0
	 */
	public function run_on_upgrade() {
		$plugin_options = get_site_option( 'oasiswf_info' );

		if ( $plugin_options['version'] == '4.0' ) {
			$this->upgrade_database_41();
			$this->upgrade_database_42();
			$this->upgrade_database_44();
			$this->upgrade_database_45();
			$this->upgrade_database_46();
			$this->upgrade_database_49();
			$this->upgrade_database_50();
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '4.1' ) {
			$this->upgrade_database_42();
			$this->upgrade_database_44();
			$this->upgrade_database_45();
			$this->upgrade_database_46();
			$this->upgrade_database_49();
			$this->upgrade_database_50();
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '4.2' ) {
			$this->upgrade_database_44();
			$this->upgrade_database_45();
			$this->upgrade_database_46();
			$this->upgrade_database_49();
			$this->upgrade_database_50();
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '4.3' ) {
			$this->upgrade_database_44();
			$this->upgrade_database_45();
			$this->upgrade_database_46();
			$this->upgrade_database_49();
			$this->upgrade_database_50();
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '4.4' ) {
			$this->upgrade_database_45();
			$this->upgrade_database_46();
			$this->upgrade_database_49();
			$this->upgrade_database_50();
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '4.5' ) {
			$this->upgrade_database_46();
			$this->upgrade_database_49();
			$this->upgrade_database_50();
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '4.6' ) {
			$this->upgrade_database_49();
			$this->upgrade_database_50();
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '4.7' ) {
			$this->upgrade_database_49();
			$this->upgrade_database_50();
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '4.8' ) {
			$this->upgrade_database_49();
			$this->upgrade_database_50();
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '4.9' ) {
			$this->upgrade_database_50();
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '5.0' ) {
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '5.1' ) {
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '5.2' ) {
			$this->upgrade_database_53();
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '5.3' ) {
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '5.4' ) {
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '5.5' ) {
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '5.6' ) {
			$this->upgrade_database_57();
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '5.7' ) {
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '5.8' ) {
			$this->upgrade_database_60();
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '6.0' ) {
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '6.1' ) {
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '6.2' ) {
			$this->upgrade_database_63();
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '6.3' ) {
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '6.4' ) {
			$this->upgrade_database_65();
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '6.5' ) {
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '6.6' ) {
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '6.7' ) {
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '6.8' ) {
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '6.9' ) {
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '7.0' ) {
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '7.1' ) {
			$this->upgrade_database_72();
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '7.2' ) {
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '7.3' ) {
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '7.4' ) {
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '7.5' ) {
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '7.6' ) {
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '7.7' ) {
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '7.8' ) {
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '7.9' ) {
			$this->upgrade_database_73();
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '8.0' ) {
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '8.1' ) {
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '8.2' ) {
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '8.3' ) {
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '8.4' ) {
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '8.5' ) {
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '8.6' ) {
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '8.7' ) {
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '8.8' ) {
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '8.9' ) {
			$this->upgrade_database_74();
		} elseif ( $plugin_options['version'] == '9.0' ) {
			$this->upgrade_database_74();
		}

		// update the version value
		$oasiswf_info = array(
			'version'    => OASISWF_VERSION,
			'db_version' => OASISWF_DB_VERSION
		);
		update_site_option( 'oasiswf_info', $oasiswf_info );
	}

	/**
	 * Upgrade helper for v4.1 upgrade function
	 *
	 * Replace postmeta meta_key oasis_original,oasis_is_in_workflow,ow_task_priority,oasis_is_in_team,oasis_current_revision
	 * to _oasis_original, _oasis_is_in_workflow, _oasis_task_priority, _oasis_is_in_team,_oasis_current_revision
	 *
	 * @since 4.1
	 */
	private function upgrade_database_41() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_41();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_41();

	}

	/**
	 * Upgrade helper for v4.1 upgrade function
	 *
	 * 1) Replace postmeta meta_key oasis_original,oasis_is_in_workflow,ow_task_priority,oasis_is_in_team,oasis_current_revision to _oasis_original, _oasis_is_in_workflow, _oasis_task_priority, _oasis_is_in_team,_oasis_current_revision.
	 * 2) add new setting review_approval to step_info in fc_wokflow_steps table.
	 *
	 * @since 4.1
	 */
	private function upgrade_helper_41() {
		global $wpdb;

		// update custom postmeta values
		// adding underscore will hide the custom post meta from the UI
		$meta_keys = array(
			'oasis_original'         => '_oasis_original',
			'oasis_is_in_workflow'   => '_oasis_is_in_workflow',
			'ow_task_priority'       => '_oasis_task_priority',
			'oasis_is_in_team'       => '_oasis_is_in_team',
			'oasis_current_revision' => '_oasis_current_revision'
		);

		foreach ( $meta_keys as $old_meta_key => $new_meta_key ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s",
				$new_meta_key, $old_meta_key ) );
		}

		// get current review settings
		$review_setting = get_option( 'oasiswf_review_process_setting' );

		// add review_approval settings on existing review processes

		$steps = $wpdb->get_results( "SELECT ID, step_info FROM " . $wpdb->fc_workflow_steps );
		foreach ( $steps as $step ) {
			$step_info = json_decode( $step->step_info );
			if ( $step_info->process === 'review' ) {
				// add review approval and set it's value to
				$step_info->review_approval = $review_setting;
				$step->step_info            = wp_json_encode( $step_info );
				$wpdb->update( $wpdb->fc_workflow_steps, array(
					'step_info' => $step->step_info
				), array(
					'ID' => $step->ID
				) );
			}
		}

		// now delete the option, since we do not need it anymore
		delete_option( 'oasiswf_review_process_setting' );
	}

	/**
	 * Upgrade helper for v4.2 upgrade function
	 *
	 *
	 * @since 4.2
	 */
	private function upgrade_database_42() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_42();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_42();

	}

	private function upgrade_helper_42() {
		global $wpdb;

		//fc_action_history table  - add history_meta field
		$wpdb->query( "ALTER TABLE {$wpdb->fc_action_history} ADD history_meta longtext" );

		//fc_action table  - add history_meta field
		$wpdb->query( "ALTER TABLE {$wpdb->fc_action} ADD history_meta longtext" );
	}

	/**
	 * Upgrade helper for v4.4 upgrade function
	 *
	 *
	 * @since 4.4
	 */
	private function upgrade_database_44() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_44();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_44();

	}

	private function upgrade_helper_44() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles(); // phpcs:ignore
			}
		}

		// add ow_submit_to_workflow and ow_sign_off_step to all the existing roles.
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );
		}

		//Get workflow participating roles
		$participating_roles = OW_Utility::instance()->get_participating_roles();

		if ( ! get_option( 'oasiswf_participating_roles_setting' ) ) {
			update_option( "oasiswf_participating_roles_setting", $participating_roles );
		}

		// Publish date Setting
		if ( ! get_option( 'oasiswf_publish_date_setting' ) ) {
			update_option( "oasiswf_publish_date_setting", '' );
		}
	}

	/**
	 * Upgrade helper for v4.5 upgrade function
	 *
	 *
	 * @since 4.5
	 */
	private function upgrade_database_45() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_45();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_45();

	}

	private function upgrade_helper_45() {

		if ( ! get_option( 'oasiswf_login_redirect_roles_setting' ) ) {
			update_option( "oasiswf_login_redirect_roles_setting", '' );
		}
	}

	/**
	 * Upgrade helper for v4.6 upgrade function
	 *
	 * @since 4.6
	 */
	private function upgrade_database_46() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_46();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_46();

	}

	private function upgrade_helper_46() {
		// Schedules a hook for email digest
		if ( ! wp_next_scheduled( 'oasiswf_email_digest_schedule' ) ) {
			wp_schedule_event( time(), 'hourly', 'oasiswf_email_digest_schedule' );
		}

		// Migrate email settings to new email settings
		$ow_email_settings_helper = new OW_Email_Settings_Helper();

		$email_settings = get_site_option( 'oasiswf_email_settings' );

		$post_publish_settings = array(
			'is_active'        => $email_settings['post_publish_emails'],
			'user_roles'       => array( 'post_author' ),
			'additional_users' => '',
			'subject'          => $ow_email_settings_helper->get_post_publish_subject(),
			'content'          => $ow_email_settings_helper->get_post_publish_content()
		);

		$revised_post_publish_settings = array(
			'is_active'        => $email_settings['post_publish_emails'],
			'user_roles'       => array( 'post_author' ),
			'additional_users' => '',
			'subject'          => $ow_email_settings_helper->get_revised_post_publish_subject(),
			'content'          => $ow_email_settings_helper->get_revised_post_publish_content()
		);

		$unauthorized_update_settings = array(
			'is_active'        => $email_settings['unauthorized_post_update_emails'],
			'user_roles'       => array( 'current_task_assignees' ),
			'additional_users' => '',
			'subject'          => $ow_email_settings_helper->get_unauthorized_update_subject(),
			'content'          => $ow_email_settings_helper->get_unauthorized_update_content()
		);

		$tasked_claimed_settings = array(
			'is_active'        => $email_settings['assignment_emails'],
			'user_roles'       => array( 'current_task_assignees' ),
			'additional_users' => '',
			'subject'          => $ow_email_settings_helper->get_task_claimed_subject(),
			'content'          => $ow_email_settings_helper->get_task_claimed_content()
		);

		$post_submit_settings = array(
			'is_active'        => $email_settings['submit_to_workflow_email'],
			'user_roles'       => array( 'post_author' ),
			'additional_users' => '',
			'subject'          => $ow_email_settings_helper->get_post_submit_subject(),
			'content'          => $ow_email_settings_helper->get_post_submit_content()
		);

		$abort_workflow_settings = array(
			'is_active'        => $email_settings['abort_email_to_author'],
			'user_roles'       => array( 'post_author' ),
			'additional_users' => '',
			'subject'          => $ow_email_settings_helper->get_workflow_abort_subject(),
			'content'          => $ow_email_settings_helper->get_workflow_abort_content()
		);

		if ( ! get_option( 'oasiswf_post_publish_email_settings' ) ) {
			update_option( "oasiswf_post_publish_email_settings", $post_publish_settings );
		}
		if ( ! get_option( 'oasiswf_revised_post_email_settings' ) ) {
			update_option( "oasiswf_revised_post_email_settings", $revised_post_publish_settings );
		}
		if ( ! get_option( 'oasiswf_unauthorized_update_email_settings' ) ) {
			update_option( "oasiswf_unauthorized_update_email_settings", $unauthorized_update_settings );
		}
		if ( ! get_option( 'oasiswf_task_claim_email_settings' ) ) {
			update_option( "oasiswf_task_claim_email_settings", $tasked_claimed_settings );
		}
		if ( ! get_option( 'oasiswf_post_submit_email_settings' ) ) {
			update_option( "oasiswf_post_submit_email_settings", $post_submit_settings );
		}
		if ( ! get_option( 'oasiswf_workflow_abort_email_settings' ) ) {
			update_option( "oasiswf_workflow_abort_email_settings", $abort_workflow_settings );
		}

		// Add new option digest email in email settings
		$new_email_settings = array(
			'from_name'          => $email_settings['from_name'],
			'from_email_address' => $email_settings['from_email_address'],
			'assignment_emails'  => $email_settings['assignment_emails'],
			'reminder_emails'    => $email_settings['reminder_emails'],
			'digest_emails'      => ''
		);
		update_option( "oasiswf_email_settings", $new_email_settings );

		// Add new email placeholders
		$oasiswf_email_placeholders = array(
			'{first_name}'         => esc_html__( 'First Name', "oasisworkflow" ),
			'{last_name}'          => esc_html__( 'Last Name', "oasisworkflow" ),
			'{post_title}'         => esc_html__( 'Post Title, this will be displayed as a link', "oasisworkflow" ),
			'{category}'           => esc_html__( 'Category', "oasisworkflow" ),
			'{last_modified_date}' => esc_html__( 'Last Modified Date', "oasisworkflow" ),
			'{post_author}'        => esc_html__( 'Post Author', 'oasisworkflow' ),
			'{blog_name}'          => esc_html__( 'Blog Name', 'oasisworkflow' ),
			'{current_user}'       => esc_html__( 'Current User', 'oasisworkflow' )
		);
		if ( ! get_site_option( 'oasiswf_email_placeholders' ) ) {
			update_site_option( "oasiswf_email_placeholders", $oasiswf_email_placeholders );
		}

		// Auto delete history
		$auto_delete_history = array(
			'enable' => '',
			'period' => 'one-month-ago',
		);
		if ( ! get_option( 'oasiswf_auto_delete_history_setting' ) ) {
			update_option( "oasiswf_auto_delete_history_setting", $auto_delete_history );
		}

		// Schedules a hook for auto delete history
		if ( ! wp_next_scheduled( 'oasiswf_auto_delete_history_schedule' ) ) {
			wp_schedule_event( time(), 'daily', 'oasiswf_auto_delete_history_schedule' );
		}
	}

	/**
	 * Upgrade helper for v4.9 upgrade function
	 *
	 * @since 4.9
	 */
	private function upgrade_database_49() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_49();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_49();

	}

	private function upgrade_helper_49() {
		global $wpdb;
		// upgrade script for auto submit settings
		$applicable_workflow  = array();
		$auto_submit_settings = get_option( 'oasiswf_auto_submit_settings' );

		// Set the cron interval
		if ( wp_next_scheduled( 'oasiswf_auto_submit_schedule' ) ) {
			$schedule = wp_get_schedule( 'oasiswf_auto_submit_schedule' );
		} else {
			$schedule = "hourly";
		}
		$auto_submit_settings['auto_submit_interval'] = $schedule;

		// Set the applicable workflows
		$workflows = $wpdb->get_results( "SELECT * FROM " . $wpdb->fc_workflows . " WHERE (end_date = '0000-00-00' OR end_date >= CURDATE())
				AND is_auto_submit = 1
				AND is_valid = 1
				ORDER BY ID" );

		foreach ( $workflows as $wf ) {
			$wf_id                = $wf->ID;
			$auto_submit_info     = unserialize( $wf->auto_submit_info );
			$keyword_array        = $auto_submit_info['keywords'];
			$auto_submit_keywords = implode( ',', $keyword_array );

			if ( ! empty( $auto_submit_keywords ) ) {
				$applicable_workflow[ $wf_id ] = $auto_submit_keywords;
			}
		}

		$auto_submit_settings['auto_submit_workflows'] = $applicable_workflow;
		update_option( "oasiswf_auto_submit_settings", $auto_submit_settings );

		// Add new placeholder %blog_name% and %publish_date%
		$oasiswf_placeholders = array(
			'%first_name%'         => esc_html__( 'first name', "oasisworkflow" ),
			'%last_name%'          => esc_html__( 'last name', "oasisworkflow" ),
			'%post_title%'         => esc_html__( 'post title', "oasisworkflow" ),
			'%category%'           => esc_html__( 'category', "oasisworkflow" ),
			'%last_modified_date%' => esc_html__( 'last modified date', "oasisworkflow" ),
			'%post_author%'        => esc_html__( 'post author', 'oasisworkflow' ),
			'%blog_name%'          => esc_html__( 'blog name', 'oasisworkflow' )
		);

		update_site_option( 'oasiswf_placeholders', $oasiswf_placeholders );

		// Add new email placeholders {publish_date}
		$oasiswf_email_placeholders = array(
			'{first_name}'         => esc_html__( 'First Name', "oasisworkflow" ),
			'{last_name}'          => esc_html__( 'Last Name', "oasisworkflow" ),
			'{post_title}'         => esc_html__( 'Post Title, this will be displayed as a link', "oasisworkflow" ),
			'{category}'           => esc_html__( 'Category', "oasisworkflow" ),
			'{last_modified_date}' => esc_html__( 'Last Modified Date', "oasisworkflow" ),
			'{publish_date}'       => esc_html__( 'Publish Date', 'oasisworkflow' ),
			'{post_author}'        => esc_html__( 'Post Author', 'oasisworkflow' ),
			'{blog_name}'          => esc_html__( 'Blog Name', 'oasisworkflow' ),
			'{current_user}'       => esc_html__( 'Current User', 'oasisworkflow' )
		);
		update_site_option( "oasiswf_email_placeholders", $oasiswf_email_placeholders );
	}

	/**
	 * Upgrade helper for v5.0 upgrade function
	 *
	 * @since 5.0
	 */
	private function upgrade_database_50() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_50();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_50();

	}

	private function upgrade_helper_50() {
		// Add new placeholder %post_submitter%
		$oasiswf_placeholders = array(
			'%first_name%'         => esc_html__( 'first name', "oasisworkflow" ),
			'%last_name%'          => esc_html__( 'last name', "oasisworkflow" ),
			'%post_title%'         => esc_html__( 'post title', "oasisworkflow" ),
			'%category%'           => esc_html__( 'category', "oasisworkflow" ),
			'%last_modified_date%' => esc_html__( 'last modified date', "oasisworkflow" ),
			'%post_author%'        => esc_html__( 'post author', 'oasisworkflow' ),
			'%blog_name%'          => esc_html__( 'blog name', 'oasisworkflow' ),
			'%post_submitter%'     => esc_html__( 'post submitter', 'oasisworkflow' )
		);

		update_site_option( 'oasiswf_placeholders', $oasiswf_placeholders );
	}

	/**
	 * Upgrade helper for v5.3 upgrade function
	 *
	 * @since 5.3
	 */
	private function upgrade_database_53() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_53();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_53();
	}

	private function upgrade_helper_53() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles(); // phpcs:ignore
			}
		}

		// Add admin capabilities
		$wp_roles->add_cap( 'administrator', 'ow_duplicate_post' );
	}

	/**
	 * Upgrade helper for v5.7 upgrade function
	 *
	 * @since 5.7
	 */
	private function upgrade_database_57() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_57();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_57();
	}

	private function upgrade_helper_57() {
		global $wpdb;
		$wpdb->query( "ALTER TABLE {$wpdb->fc_action_history} MODIFY ID bigint(20) NOT NULL AUTO_INCREMENT, MODIFY post_id bigint(20), MODIFY from_id bigint(20)" );

		$wpdb->query( "ALTER TABLE {$wpdb->fc_action} MODIFY ID bigint(20) NOT NULL AUTO_INCREMENT, MODIFY action_history_id bigint(20)" );

		$wpdb->query( "ALTER TABLE {$wpdb->fc_emails} MODIFY history_id bigint(20)" );
	}

	/**
	 * Upgrade helper for v6.0 upgrade function
	 *
	 * @since 6.0
	 */
	private function upgrade_database_60() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_60();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_60();
	}

	private function upgrade_helper_60() {
		// Schedule hook to delete revision ( copy-of ) post
		if ( ! wp_next_scheduled( 'oasiswf_revision_delete_schedule' ) ) {
			wp_schedule_event( time(), 'hourly', 'oasiswf_revision_delete_schedule' );
		}
	}

	/**
	 * Upgrade helper for v9.3 upgrade function
	 *
	 * @since 9.3
	 */
	private function upgrade_database_74() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_74();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_74();
	}

	private function upgrade_helper_74() {
		global $wpdb;
		
		$dbname = $wpdb->dbname;

		$email_table = $wpdb->prefix . "fc_emails";

		$is_cc_col = $wpdb->get_results(  "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS`    WHERE `table_name` = '{$email_table}' AND `TABLE_SCHEMA` = '{$dbname}' AND `COLUMN_NAME` = 'cc_users'"  );

		if( empty($is_cc_col) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}fc_emails ADD cc_users longtext NOT NULL, ADD bcc_users longtext NOT NULL" );
		}
	}

	/**
	 * Upgrade helper for v6.0 upgrade function
	 *
	 * @since 6.0
	 */
	private function upgrade_database_63() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_63();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_63();
	}

	private function upgrade_helper_63() {
		$auto_submit_settings = get_option( 'oasiswf_auto_submit_settings' );

		$auto_submit_settings['search_post_taxonomies'] = 'no';
		update_option( "oasiswf_auto_submit_settings", $auto_submit_settings );

		// Sidebar settings for gutenberg editor
		if ( ! get_option( 'oasiswf_sidebar_display_setting' ) ) {
			update_option( 'oasiswf_sidebar_display_setting', 'show' );
		}
	}

	/**
	 * Upgrade helper for v6.5 upgrade function
	 *
	 * @since 6.5
	 */
	private function upgrade_database_65() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_65();
				}
				restore_current_blog();
			}
		}

		$this->upgrade_helper_65();
	}

	private function upgrade_helper_65() {
		global $wpdb;

		//fc_action table  - add step_id and next_assign_actors field
		$wpdb->query( "ALTER TABLE {$wpdb->fc_action} MODIFY COLUMN next_assign_actors text NULL, MODIFY COLUMN step_id int(11) NULL " );
	}

	private function upgrade_database_72() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_72();
				}
				restore_current_blog();
			}
		} else {
			$this->upgrade_helper_72();
		}
	}

	/**
	 * Upgrade helper for v7.2 upgrade function
	 *
	 * @since 7.2
	 */
	private function upgrade_helper_72() {
		// 1) Upgrade post publish option settings
		$post_publish_options = get_option( "oasiswf_post_publish_email_settings" );
		//get post publish email assignees
		if ( $post_publish_options ) {
			$post_publish_email_assignee = $this->get_email_assignees( $post_publish_options );
			update_option( "oasiswf_post_publish_email_settings", $post_publish_email_assignee );
		} else {
			update_option( "oasiswf_post_publish_email_settings", array() );
		}

		// 2) Upgrade revised post publish option settings
		$revised_post_publish_options = get_option( "oasiswf_revised_post_email_settings" );
		//get post publish email assignees
		if ( $revised_post_publish_options ) {
			$revised_post_publish_email_assignee = $this->get_email_assignees( $revised_post_publish_options );
			update_option( "oasiswf_revised_post_email_settings", $revised_post_publish_email_assignee );
		} else {
			update_option( "oasiswf_revised_post_email_settings", array() );
		}

		// 3) Upgrade unauthorized email option settings
		$unauthorized_email_options = get_option( "oasiswf_unauthorized_update_email_settings" );
		//get post publish email assignees
		if ( $unauthorized_email_options ) {
			$unauthorized_email_assignee = $this->get_email_assignees( $unauthorized_email_options );
			update_option( "oasiswf_unauthorized_update_email_settings", $unauthorized_email_assignee );
		} else {
			update_option( "oasiswf_unauthorized_update_email_settings", array() );
		}

		// 4) Upgrade task claimed email option settings
		$task_claimed_email_options = get_option( "oasiswf_task_claim_email_settings" );
		//get post publish email assignees
		if ( $task_claimed_email_options ) {
			$task_claimed_email_assignee = $this->get_email_assignees( $task_claimed_email_options );
			update_option( "oasiswf_task_claim_email_settings", $task_claimed_email_assignee );
		} else {
			update_option( "oasiswf_task_claim_email_settings", array() );
		}

		// 5) Upgrade post submit email option settings
		$post_submit_email_options = get_option( "oasiswf_post_submit_email_settings" );
		//get post publish email assignees
		if ( $post_submit_email_options ) {
			$post_submit_email_assignee = $this->get_email_assignees( $post_submit_email_options );
			update_option( "oasiswf_post_submit_email_settings", $post_submit_email_assignee );
		} else {
			update_option( "oasiswf_post_submit_email_settings", array() );
		}

		// 6) Upgrade workflow abort email option settings
		$workflow_abort_email_options = get_option( "oasiswf_workflow_abort_email_settings" );
		//get post publish email assignees
		if ( $workflow_abort_email_options ) {
			$workflow_abort_email_assignee = $this->get_email_assignees( $workflow_abort_email_options );
			update_option( "oasiswf_workflow_abort_email_settings", $workflow_abort_email_assignee );
		} else {
			update_option( "oasiswf_workflow_abort_email_settings", array() );
		}

		// Add settings for external users
		if ( ! get_option( 'oasiswf_external_user_settings' ) ) {
			update_option( 'oasiswf_external_user_settings', array() );
		}
	}

	public function get_email_assignees( $email_options ) {
		$email_assignees = array();

		$selected_user_roles   = $email_options['user_roles'];
		$additional_recipients = $email_options['additional_users'];

		// Get email user roles
		if ( $selected_user_roles ) {
			$email_assignees["roles"] = $selected_user_roles;
		} else {
			$email_assignees["roles"] = array();
		}

		// Get additional users
		if ( $additional_recipients ) {
			$email_assignees["users"] = $additional_recipients;
		} else {
			$email_assignees["users"] = array();
		}

		// Add blank array for external users
		$email_assignees["external_users"] = array();

		// unset and create new set of array and update
		unset( $email_options['user_roles'] );
		unset( $email_options['additional_users'] );

		$email_options["email_assignees"] = $email_assignees;
		$email_options["email_cc"]        = array(
			"roles"          => array(),
			"users"          => array(),
			"external_users" => array()
		);
		$email_options["email_bcc"]       = array(
			"roles"          => array(),
			"users"          => array(),
			"external_users" => array()
		);

		return $email_options;
	}

	private function upgrade_database_73() {
		global $wpdb;

		// look through each of the blogs and upgrade the DB
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( $wpdb->query( "SHOW TABLES FROM " . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'" ) ) {
					$this->upgrade_helper_73();
				}
				restore_current_blog();
			}
		} else {
			$this->upgrade_helper_73();
		}
	}

	/**
	 * Upgrade helper for v7.3 upgrade function
	 *
	 * @since 7.3
	 */
	private function upgrade_helper_73() {
		// Add setting for mandatory comment
		if ( ! get_option( 'oasiswf_comments_setting' ) ) {
			update_option( 'oasiswf_comments_setting', "" );
		}
	}

	/**
	 * Called on activation.
	 * Creates the options and DB (required by per site)
	 *
	 * @since 2.0
	 */
	private function run_for_site() {

		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles(); // phpcs:ignore
			}
		}

		// add ow_submit_to_workflow and ow_sign_off_step to all the existing roles.
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );
		}

		/*
		 * Include the custom capability class
		 */
		if ( ! class_exists( 'OW_Custom_Capabilities' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-custom-capabilities.php' );
		}

		$ow_custom_capabilities = new OW_Custom_Capabilities();
		$ow_custom_capabilities->add_capabilities();

		$auto_submit_stati = array( 'draft' );

		$auto_submit_settings = array(
			'auto_submit_stati'      => $auto_submit_stati,
			'auto_submit_due_days'   => '1',
			'auto_submit_comment'    => '',
			'auto_submit_post_count' => '10',
			'auto_submit_enable'     => false,
			'search_post_title'      => 'yes',
			'search_post_tags'       => 'yes',
			'search_post_categories' => 'yes'
		);

		/*
		 * Add out of the box custom statuses
		 */
		if ( ! class_exists( 'OW_Custom_Statuses' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-custom-statuses.php' );
		}
		$ow_custom_statuses = new OW_Custom_Statuses();
		$ow_custom_statuses->register_custom_taxonomy();

		$custom_statuses = array(
			'Pitch'            => esc_html__( 'New idea proposed.', 'oasisworkflow' ),
			'With Author'      => esc_html__( 'An author has been assigned to the post.', 'oasisworkflow' ),
			'Ready to Publish' => esc_html__( 'The post is ready for publication.', 'oasisworkflow' )
		);

		foreach ( $custom_statuses as $custom_status => $desc ) {

			// phpcs:ignore
			if ( term_exists( $custom_status ) ) {
				continue;
			}

			$args     = array(
				'description' => $desc,
				'slug'        => sanitize_title( $custom_status )
			);
			$response = wp_insert_term( $custom_status, 'post_status', $args );
		}

		$show_wfsettings_on_post_types = array( 'post', 'page' );

		// Copy children when Make Revision
		$copy_children_on_revision = "";

		//Priority Setting - initial setting is enabled
		$priority_setting = "enable_priority";

		//Get workflow participating roles
		$participating_roles = OW_Utility::instance()->get_participating_roles();

		// Auto delete history initial settings
		$auto_delete_history = array(
			'enable' => '',
			'period' => 'one-month-ago',
		);

		// default roles for bulk actions
		$bulk_approval_roles = array( 'administrator' );

		// default message for make revision overlay
		$doc_revision_make_revision_overlay_message
			= esc_html__( 'You may not make changes to this published content. You must first make a revision and then submit your changes for approval.',
			'oasisworkflow' );

		if ( ! get_option( 'oasiswf_show_wfsettings_on_post_types' ) ) {
			update_option( "oasiswf_show_wfsettings_on_post_types", $show_wfsettings_on_post_types );
		}

		if ( ! get_option( 'oasiswf_auto_submit_settings' ) ) {
			update_option( "oasiswf_auto_submit_settings", $auto_submit_settings );
		}

		// document revision site options
		if ( ! get_option( 'oasiswf_doc_revision_title_prefix' ) ) {
			update_option( "oasiswf_doc_revision_title_prefix", esc_html__( 'Copy of -', "oasisworkflow" ) );
		}

		if ( ! get_option( 'oasiswf_doc_revision_title_suffix' ) ) {
			update_option( "oasiswf_doc_revision_title_suffix", '' );
		}

		if ( ! get_option( 'oasiswf_copy_children_on_revision' ) ) {
			update_option( "oasiswf_copy_children_on_revision", $copy_children_on_revision );
		}

		if ( ! get_option( 'oasiswf_priority_setting' ) ) {
			update_option( "oasiswf_priority_setting", $priority_setting );
		}

		if ( ! get_option( 'oasiswf_comments_setting' ) ) {
			update_option( "oasiswf_comments_setting", "" );
		}

		// Step due date Setting
		if ( ! get_option( 'oasiswf_step_due_date_settings' ) ) {
			update_option( "oasiswf_step_due_date_settings", '' );
		}

		// Publish date Setting
		if ( ! get_option( 'oasiswf_publish_date_setting' ) ) {
			update_option( "oasiswf_publish_date_setting", '' );
		}

		// Sidebar settings for gutenberg editor
		if ( ! get_option( 'oasiswf_sidebar_display_setting' ) ) {
			update_option( 'oasiswf_sidebar_display_setting', 'show' );
		}

		if ( ! get_option( 'oasiswf_participating_roles_setting' ) ) {
			update_option( "oasiswf_participating_roles_setting", $participating_roles );
		}

		if ( ! get_option( 'oasiswf_login_redirect_roles_setting' ) ) {
			update_option( "oasiswf_login_redirect_roles_setting", '' );
		}

		if ( ! get_option( 'oasiswf_auto_delete_history_setting' ) ) {
			update_option( "oasiswf_auto_delete_history_setting", $auto_delete_history );
		}

		$enable_revision_process = "active";

		if ( ! get_option( 'oasiswf_activate_revision_process' ) ) {
			update_option( "oasiswf_activate_revision_process", $enable_revision_process );
		}

		$delete_revision_on_copy = "yes";

		if ( ! get_option( 'oasiswf_delete_revision_on_copy' ) ) {
			update_option( "oasiswf_delete_revision_on_copy", $delete_revision_on_copy );
		}

		$email_settings = array(
			'from_name'          => '',
			'from_email_address' => '',
			'assignment_emails'  => 'no',
			'reminder_emails'    => 'no',
			'digest_emails'      => ''
		);
		if ( ! get_option( 'oasiswf_email_settings' ) ) {
			update_option( "oasiswf_email_settings", $email_settings );
		}

		// Add settings for other email notification
		if ( ! get_option( 'oasiswf_post_publish_email_settings' ) ) {
			update_option( "oasiswf_post_publish_email_settings", array() );
		}

		if ( ! get_option( 'oasiswf_revised_post_email_settings' ) ) {
			update_option( "oasiswf_revised_post_email_settings", array() );
		}

		if ( ! get_option( 'oasiswf_unauthorized_update_email_settings' ) ) {
			update_option( "oasiswf_unauthorized_update_email_settings", array() );
		}

		if ( ! get_option( 'oasiswf_task_claim_email_settings' ) ) {
			update_option( "oasiswf_task_claim_email_settings", array() );
		}

		if ( ! get_option( 'oasiswf_post_submit_email_settings' ) ) {
			update_option( "oasiswf_post_submit_email_settings", array() );
		}

		if ( ! get_option( 'oasiswf_workflow_abort_email_settings' ) ) {
			update_option( "oasiswf_workflow_abort_email_settings", array() );
		}

		if ( ! get_option( 'oasiswf_roles_can_bulk_approval' ) ) {
			update_option( "oasiswf_roles_can_bulk_approval", $bulk_approval_roles );
		}

		if ( ! get_option( 'oasiswf_revise_post_make_revision_overlay' ) ) {
			update_option( 'oasiswf_revise_post_make_revision_overlay', $doc_revision_make_revision_overlay_message );
		}

		// Add settings for external users
		if ( ! get_option( 'oasiswf_external_user_settings' ) ) {
			update_option( 'oasiswf_external_user_settings', array() );
		}

		$this->install_admin_database();
		$this->install_site_database();
	}

	/**
	 * Create workflow tables and create the default workflow
	 *
	 * @since 2.0
	 */
	private function install_admin_database() {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		//fc_workflows table
		$table_name = OW_Utility::instance()->get_workflows_table_name();
		$query      = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		// Didn't find it, so try to create it.
		if ( $wpdb->get_var( $query ) != $table_name ) {
			$sql = "CREATE TABLE IF NOT EXISTS {$table_name}  (
			ID int(11) NOT NULL AUTO_INCREMENT,
			name varchar(200) NOT NULL,
			description mediumtext,
			wf_info longtext,
			version int(3) NOT NULL default 1,
			parent_id int(11) NOT NULL default 0,
			start_date date DEFAULT NULL,
			end_date date DEFAULT NULL,
			is_auto_submit int(2) NOT NULL default 0,
			auto_submit_info mediumtext,
			is_valid int(2) NOT NULL default 0,
			create_datetime datetime DEFAULT NULL,
			update_datetime datetime DEFAULT NULL,
			wf_additional_info mediumtext DEFAULT NULL,
			PRIMARY KEY (ID)
			){$charset_collate};";

			dbDelta( $sql );
		}

		//fc_workflow_steps table
		$table_name = OW_Utility::instance()->get_workflow_steps_table_name();

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( $wpdb->get_var( $query ) != $table_name ) {
			$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			ID int(11) NOT NULL AUTO_INCREMENT,
			step_info text NOT NULL,
			process_info longtext NOT NULL,
			workflow_id int(11) NOT NULL,
			create_datetime datetime DEFAULT NULL,
			update_datetime datetime DEFAULT NULL,
			PRIMARY KEY (ID),
			KEY workflow_id (workflow_id)
			){$charset_collate};";
			dbDelta( $sql );
		}

		$this->populate_default_workflows();
	}

	/**
	 * Add default data - Create a default workflow
	 */
	private function populate_default_workflows() {
		global $wpdb;

		// insert into workflow table
		$table_name = OW_Utility::instance()->get_workflows_table_name();
		$row        = $wpdb->get_row( "SELECT max(ID) as maxid FROM $table_name" );
		if ( is_numeric( $row->maxid ) ) { //data already exists, do not insert another row.
			return;
		}
		$workflow_info   = stripcslashes( '{"steps":{"step0":{"fc_addid":"step0","fc_label":"Author Assignment","fc_dbid":"2","fc_process":"assignment","fc_position":["326px","568px"]},"step1":{"fc_addid":"step1","fc_label":"First Level Review","fc_dbid":"1","fc_process":"review","fc_position":["250px","358px"]},"step2":{"fc_addid":"step2","fc_label":"Second Level Review and Publish","fc_dbid":"3","fc_process":"publish","fc_position":["119px","622px"]}},"conns":{"0":{"sourceId":"step2","targetId":"step0","post_status":"draft","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"red"}}},"1":{"sourceId":"step1","targetId":"step0","post_status":"draft","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"red"}}},"2":{"sourceId":"step0","targetId":"step1","post_status":"pending","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"blue"}}},"3":{"sourceId":"step2","targetId":"step1","post_status":"pending","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"red"}}},"4":{"sourceId":"step1","targetId":"step2","post_status":"ready-to-publish","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"blue"}}}},"first_step":[{"step":"step1","post_status":"draft"}]}' );
		$additional_info = stripcslashes( 'a:4:{s:16:"wf_for_new_posts";i:1;s:20:"wf_for_revised_posts";i:1;s:12:"wf_for_roles";a:0:{}s:17:"wf_for_post_types";a:0:{}}' );

		$data        = array(
			'name'               => 'Multi Level Review Workflow',
			'description'        => 'Multi Level Review Workflow',
			'wf_info'            => $workflow_info,
			'start_date'         => gmdate( "Y-m-d", current_time( 'timestamp' ) ),
			'end_date'           => gmdate( "Y-m-d", current_time( 'timestamp' ) + YEAR_IN_SECONDS ),
			'is_valid'           => 1,
			'create_datetime'    => current_time( 'mysql' ),
			'update_datetime'    => current_time( 'mysql' ),
			'wf_additional_info' => $additional_info
		);
		$workflow_id = OW_Utility::instance()->insert_to_table( $table_name, $data );

		// insert steps
		$workflow_step_table = OW_Utility::instance()->get_workflow_steps_table_name();

		// step 1 - review
		$review_step_info
			                 = '{"process":"review","step_name":"First Level Review","assign_to_all":0,"task_assignee":{"roles":["editor"],"users":[],"groups":[]},"review_approval":"everyone"}';
		$review_process_info = '{"assign_subject":"","assign_content":"","reminder_subject":"","reminder_content":""}';
		$wpdb->insert(
			$workflow_step_table, array(
				'step_info'       => stripcslashes( $review_step_info ),
				'process_info'    => stripcslashes( $review_process_info ),
				'create_datetime' => current_time( 'mysql' ),
				'update_datetime' => current_time( 'mysql' ),
				'workflow_id'     => $workflow_id
			)
		);

		// step 2 - assignment
		$assignment_step_info    = '{"process":"assignment","step_name":"Author Assignment","assign_to_all":0,"task_assignee":{"roles":["author"],"users":[],"groups":[]}}';
		$assignment_process_info = '{"assign_subject":"","assign_content":"","reminder_subject":"","reminder_content":""}';
		$wpdb->insert(
			$workflow_step_table, array(
				'step_info'       => stripcslashes( $assignment_step_info ),
				'process_info'    => stripcslashes( $assignment_process_info ),
				'create_datetime' => current_time( 'mysql' ),
				'update_datetime' => current_time( 'mysql' ),
				'workflow_id'     => $workflow_id
			)
		);

		// step 3 - publish
		$publish_step_info    = '{"process":"publish","step_name":"Second Level Review and Publish","assign_to_all":0,"task_assignee":{"roles":["administrator"],"users":[],"groups":[]}}';
		$publish_process_info = '{"assign_subject":"","assign_content":"","reminder_subject":"","reminder_content":""}';

		$wpdb->insert(
			$workflow_step_table, array(
				'step_info'       => stripcslashes( $publish_step_info ),
				'process_info'    => stripcslashes( $publish_process_info ),
				'create_datetime' => current_time( 'mysql' ),
				'update_datetime' => current_time( 'mysql' ),
				'workflow_id'     => $workflow_id
			)
		);

		$this->install_single_level_review_workflow();
	}

	/**
	 * Add default Single Level Review Workflow
	 *
	 * @since 7.3
	 */
	private function install_single_level_review_workflow() {
		global $wpdb;

		// insert into workflow table
		$table_name = OW_Utility::instance()->get_workflows_table_name();
		$row        = $wpdb->get_row( "SELECT max(ID) as maxid FROM $table_name" );

		if ( is_numeric( $row->maxid ) && ( $row->maxid >= 2 ) ) { //data already exists, do not insert another row.
			return;
		}

		$workflow_info   = stripcslashes( '{"steps":{"step0":{"fc_addid":"step0","fc_label":"Review and Publish","fc_dbid":"4","fc_process":"publish","fc_position":["169px","135px"]},"step1":{"fc_addid":"step1","fc_label":"Author Assignment","fc_dbid":"5","fc_process":"assignment","fc_position":["168px","506px"]}},"conns":{"0":{"sourceId":"step0","targetId":"step1","post_status":"with-author","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"red"}}},"1":{"sourceId":"step1","targetId":"step0","post_status":"ready-to-publish","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"blue"}}}},"first_step":[{"step":"step0","post_status":"pending"}]}' );
		$additional_info = stripcslashes( 'a:4:{s:16:"wf_for_new_posts";i:1;s:20:"wf_for_revised_posts";i:1;s:12:"wf_for_roles";a:0:{}s:17:"wf_for_post_types";a:0:{}}' );
		$data            = array(
			'name'               => 'Single Level Review Workflow',
			'description'        => 'Single Level Review Workflow',
			'wf_info'            => $workflow_info,
			'start_date'         => gmdate( "Y-m-d", current_time( 'timestamp' ) ),
			'end_date'           => gmdate( "Y-m-d", current_time( 'timestamp' ) + YEAR_IN_SECONDS ),
			'is_valid'           => 1,
			'create_datetime'    => current_time( 'mysql' ),
			'update_datetime'    => current_time( 'mysql' ),
			'wf_additional_info' => $additional_info
		);

		$workflow_id = OW_Utility::instance()->insert_to_table( $table_name, $data );

		// insert steps
		$workflow_step_table = OW_Utility::instance()->get_workflow_steps_table_name();

		// step 1 - review and publish
		$publish_step_info
			                  = '{"process":"publish","step_name":"Review and Publish","assign_to_all":0,"task_assignee":{"roles":["administrator"],"users":[],"groups":[]},"assignee":{},"status":""}';
		$publish_process_info = '{"assign_subject":"","assign_content":"","reminder_subject":"","reminder_content":""}';

		$wpdb->insert(
			$workflow_step_table, array(
				'step_info'       => stripcslashes( $publish_step_info ),
				'process_info'    => stripcslashes( $publish_process_info ),
				'create_datetime' => current_time( 'mysql' ),
				'update_datetime' => current_time( 'mysql' ),
				'workflow_id'     => $workflow_id
			)
		);

		// step 2 - assignment
		$assignment_step_info    = '{"process":"assignment","step_name":"Author Assignment","assign_to_all":0,"task_assignee":{"roles":["owfpostsubmitter"],"users":[],"groups":[]},"assignee":{},"status":""}';
		$assignment_process_info = '{"assign_subject":"","assign_content":"","reminder_subject":"","reminder_content":""}';
		$wpdb->insert(
			$workflow_step_table, array(
				'step_info'       => stripcslashes( $assignment_step_info ),
				'process_info'    => stripcslashes( $assignment_process_info ),
				'create_datetime' => current_time( 'mysql' ),
				'update_datetime' => current_time( 'mysql' ),
				'workflow_id'     => $workflow_id
			)
		);
	}

	/**
	 * Create workflow action tables
	 *
	 * @since 2.0
	 */
	private function install_site_database() {
		global $wpdb;
		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		//fc_emails table
		$table_name = OW_Utility::instance()->get_emails_table_name();
		$query      = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( $wpdb->get_var( $query ) != $table_name ) {
			// action - 1 indicates not send, 0 indicates email sent
			$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			ID int(11) NOT NULL AUTO_INCREMENT,
			subject mediumtext,
			message mediumtext,
			from_user int(11),
			to_user int(11),
			cc_users longtext NOT NULL,
			bcc_users longtext NOT NULL,
			action int(2) DEFAULT 1,
			history_id bigint(20),
			send_date date DEFAULT NULL,
			create_datetime datetime DEFAULT NULL,
			PRIMARY KEY (ID)
			){$charset_collate};";
			dbDelta( $sql );
		}

		//fc_action_history table
		$table_name = OW_Utility::instance()->get_action_history_table_name();
		$query      = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
		if ( $wpdb->get_var( $query ) != $table_name ) {
			$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			ID bigint(20) NOT NULL AUTO_INCREMENT,
			action_status varchar(20) NOT NULL,
			comment longtext NOT NULL,
			step_id int(11) NOT NULL,
			assign_actor_id int(11) NOT NULL,
			post_id bigint(20) NOT NULL,
			from_id bigint(20) NOT NULL,
			due_date date DEFAULT NULL,
			history_meta longtext DEFAULT NULL,
			reminder_date date DEFAULT NULL,
			reminder_date_after date DEFAULT NULL,
			create_datetime datetime NOT NULL,
			PRIMARY KEY (ID)
			){$charset_collate};";
			dbDelta( $sql );
		}

		//fc_action table
		$table_name = OW_Utility::instance()->get_action_table_name();
		$query      = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
		if ( $wpdb->get_var( $query ) != $table_name ) {
			$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			ID bigint(20) NOT NULL AUTO_INCREMENT,
			review_status varchar(20) NOT NULL,
			actor_id int(11) NOT NULL,
			next_assign_actors text DEFAULT NULL,
			step_id int(11) DEFAULT NULL,
			comments mediumtext,
			due_date date DEFAULT NULL,
			action_history_id bigint(20) NOT NULL,
			history_meta longtext DEFAULT NULL,
			update_datetime datetime NOT NULL,
			PRIMARY KEY (ID)
			){$charset_collate};";
			dbDelta( $sql );
		}
	}

	/**
	 * deactivate the plugin
	 *
	 * @param $network_wide
	 *
	 * @since 3.4
	 */
	public function oasis_workflow_deactivate( $network_wide ) {
		global $wpdb;

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if ( $network_wide ) {
				// Get all blog ids
				$blogids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->base_prefix}blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->run_on_deactivation();
					restore_current_blog();
				}

				return;
			}
		}

		// for non-network sites only
		$this->run_on_deactivation();
	}

	/**
	 * Run on deactivation
	 *
	 * Removes the custom capabilities added by the plugin
	 *
	 * @since 3.4
	 */
	private function run_on_deactivation() {
		/*
		 * Include the custom capability class
		 */
		if ( ! class_exists( 'OW_Custom_Capabilities' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-custom-capabilities.php' );
		}

		$ow_custom_capabilities = new OW_Custom_Capabilities();
		$ow_custom_capabilities->remove_capabilities();
	}

	/**
	 * Validate Oasis Workflow Free Version exist and activated
	 *
	 * @access public
	 * @since  5.2
	 */
	public function validate_lite_version_exists() {
		$plugin = plugin_basename( __FILE__ );
		if ( is_plugin_active( 'oasis-workflow/oasiswf.php' ) ||
		     ( file_exists( plugin_dir_path( __DIR__ ) . 'oasis-workflow/oasiswf.php' ) ) ) {
			add_action( 'admin_notices', array( $this, 'show_lite_version_incompatible_message' ) );
			add_action( 'network_admin_notices', array( $this, 'show_lite_version_incompatible_message' ) );
			deactivate_plugins( $plugin );
			if ( isset( $_GET['activate'] ) ) :
				unset( $_GET['activate'] );
			endif;
		}
	}

	/**
	 * If Oasis Workflow Free is installed or activated
	 * then throw the error
	 *
	 * @access public
	 * @return mixed error_message, an array containing the error message
	 * @since  5.2
	 */
	public function show_lite_version_incompatible_message() {
		$class   = 'notice notice-error';
		$message = __( 'Please deactivate and uninstall the Oasis Workflow "free" version, before installing the "Pro" version.', 'oasisworkflow' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	public function add_workflow_tasks_summary_widget() {
		wp_add_dashboard_widget( 'task_dashboard', esc_html__( 'Workflow Tasks At a Glance', 'oasisworkflow' ), array(
			$this,
			'tasks_summary_dashboard_content'
		) );
	}

	public function tasks_summary_dashboard_content() {
		include( OASISWF_PATH . "includes/pages/workflow-dashboard-widget.php" );
	}

	public function register_table_names() {
		global $wpdb;
		$wpdb->fc_workflows      = $wpdb->prefix . "fc_workflows";
		$wpdb->fc_workflow_steps = $wpdb->prefix . "fc_workflow_steps";
		$wpdb->fc_action_history = $wpdb->prefix . "fc_action_history";
		$wpdb->fc_action         = $wpdb->prefix . "fc_action";
		$wpdb->fc_emails         = $wpdb->prefix . "fc_emails";
	}

	/**
	 * Create/Register menu items for the plugin.
	 *
	 * @since 2.0
	 */
	public function register_menu_pages() {
		$current_role = OW_Utility::instance()->get_current_user_role();

		// Lets check if filter is exist then trigger the user defined position
		if ( has_filter( 'ow_workflow_menu_position' ) ) {
			$position = apply_filters( 'ow_workflow_menu_position', '' );
		} else {
			$position = $this->get_menu_position( ".8" );
		}

		$ow_process_flow = new OW_Process_Flow();
		$inbox_count     = $ow_process_flow->get_assigned_post_count();
		$count           = ( $inbox_count ) ? '<span class="update-plugins count"><span class="plugin-count">' .
		                                      $inbox_count . '</span></span>' : '';

		// top level menu for Workflows
		add_menu_page(
			esc_html__( 'Workflows', 'oasisworkflow' ),
			esc_html__( 'Workflows', 'oasisworkflow' ) . $count, $current_role,
			'oasiswf-inbox',
			array( $this, 'workflow_inbox_page_content' ),
			'',
			$position );

		// Inbox menu
		add_submenu_page( 'oasiswf-inbox',
			esc_html__( 'Inbox', 'oasisworkflow' ),
			esc_html__( 'Inbox', 'oasisworkflow' ) . $count, $current_role,
			'oasiswf-inbox',
			array( $this, 'workflow_inbox_page_content' ) );

		$workflow_terminology_options = get_option( 'oasiswf_custom_workflow_terminology' );
		$workflow_history_label       = ! empty( $workflow_terminology_options['workflowHistoryText'] )
			? $workflow_terminology_options['workflowHistoryText'] : esc_html__( 'Workflow History' );

		// Workflow history menu - it can have a custom label, as defined in Settings -> Terminology
		if ( current_user_can( 'ow_view_workflow_history' ) ) {
			add_submenu_page( 'oasiswf-inbox',
				$workflow_history_label,
				$workflow_history_label,
				$current_role,
				'oasiswf-history',
				array( $this, 'workflow_history_page_content' ) );
		}

		// Reports
		if ( current_user_can( 'ow_view_reports' ) ) {
			add_submenu_page( 'oasiswf-inbox',
				esc_html__( 'Reports', 'oasisworkflow' ),
				esc_html__( 'Reports', 'oasisworkflow' ),
				$current_role,
				'oasiswf-reports',
				array( $this, 'workflow_reports_page_content' ) );
		}

		// Revision page - hidden from the menu, but called when "Compare Revision" is called
		add_submenu_page( 'oasiswf-revision',
			esc_html__( 'Revisions', 'oasisworkflow' ),
			esc_html__( 'Revisions', 'oasisworkflow' ),
			$current_role,
			'oasiswf-revision',
			array( $this, 'revision_compare_page_content' ) );

		// All Workflows - will display the workflow list
		if ( current_user_can( 'ow_create_workflow' ) || current_user_can( 'ow_edit_workflow' ) ) {
			add_submenu_page( 'oasiswf-inbox',
				esc_html__( 'All Workflows', 'oasisworkflow' ),
				esc_html__( 'All Workflows', 'oasisworkflow' ),
				'ow_create_workflow',
				'oasiswf-admin',
				array( $this, 'list_workflows_page_content' ) );
		}

		// Add New Workflow
		if ( current_user_can( 'ow_create_workflow' ) ) {
			add_submenu_page( 'oasiswf-inbox',
				esc_html__( 'Add New Workflow', 'oasisworkflow' ),
				esc_html__( 'Add New Workflow', 'oasisworkflow' ),
				'ow_create_workflow',
				'oasiswf-add',
				array( $this, 'create_workflow_page_content' ) );
		}

		if ( current_user_can( 'ow_create_workflow' ) ) {
			add_submenu_page( 'oasiswf-inbox',
				esc_html__( 'Custom Statuses', 'oasisworkflow' ),
				esc_html__( 'Custom Statuses', 'oasisworkflow' ),
				$current_role,
				'oasiswf-custom-statuses',
				array( $this, 'custom_statuses_page_content' ) );
		}

		if ( current_user_can( 'ow_export_import_workflow' ) ) {
			add_submenu_page( 'oasiswf-inbox',
				esc_html__( 'Tools', 'oasisworkflow' ),
				esc_html__( 'Tools', 'oasisworkflow' ),
				$current_role,
				'oasiswf-tools',
				array( $this, 'display_workflow_tools' ) );
		}

		// to add sub menus for add ons
		do_action( 'owf_add_submenu' );

		//show list of workflow add-ons
		add_submenu_page( 'oasiswf-inbox',
			esc_html__( 'Add-ons', 'oasisworkflow' ),
			esc_html__( 'Add-ons', 'oasisworkflow' ),
			'edit_theme_options',
			'oasiswf-addons',
			array( $this, 'addons_page_content' ) );

	}

	/**
	 * Put the "Workflows" main menu after the "Comments" menu
	 *
	 * @since 2.0
	 */
	private function get_menu_position( $decimal_loc ) {
		global $menu;

		$end_position   = 0;
		$start_position = 0;

		foreach ( $menu as $k => $v ) {
			if ( $v[2] == "edit-comments.php" ) { // find position of Comments menu
				$start_position = $k;
			}
			if ( $v[2] == "themes.php" ) { // find position of Appearance menu
				$end_position = $k;
			}
			$menu_position[] = $k;
		}

		// place the Workflows menu in between Comments and Appearance menu
		for ( $i = $start_position; $i < $end_position; $i ++ ) {
			// find a menu location which hasn't been used.
			if ( ! in_array( $i, $menu_position ) ) {
				$final_position = $i .
				                  $decimal_loc; // looks like we found one, so lets add the decimal position to it for uniqueness.

				return $final_position;
			}
		}
	}

	public function custom_statuses_page_content() {
		include( OASISWF_PATH . "includes/pages/ow-custom-statuses.php" );
	}

	public function display_workflow_tools() {
		include( OASISWF_PATH . "includes/pages/workflow-tools.php" );
	}

	public function addons_page_content() {
		include( OASISWF_PATH . "includes/pages/workflow-addons.php" );
	}

	public function load_css_and_js_files() {
		add_action( 'admin_print_styles', array( $this, 'add_css_files' ) );
		add_action( 'admin_print_scripts', array( $this, 'add_js_files' ) );
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'add_elementor_js_var' ) );
		add_action( 'admin_footer', array( $this, 'load_js_files_footer' ) );
	}

	/**
	 * Show the welcome message on plugin activation.
	 *
	 * @since 3.2
	 */
	public function show_welcome_message_pointers() {
		// Don't run on WP < 3.3
		if ( get_bloginfo( 'version' ) < '3.3' ) {
			return;
		}

		// only show this message to the users who can activate plugins
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$pointers = $this->get_current_screen_pointers();

		// No pointers? Don't do anything
		if ( empty( $pointers ) || ! is_array( $pointers ) ) {
			return;
		}

		// Get dismissed pointers.
		// Note : dismissed pointers are stored by WP in the "dismissed_wp_pointers" user meta.

		$dismissed      = explode( ',',
			(string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		$valid_pointers = array();

		// Check pointers and remove dismissed ones.
		foreach ( $pointers as $pointer_id => $pointer ) {
			// Sanity check
			if ( in_array( $pointer_id, $dismissed ) || empty( $pointer ) || empty( $pointer_id ) ||
			     empty( $pointer['target'] ) || empty( $pointer['content'] ) ) {
				continue;
			}

			// Add the pointer to $valid_pointers array
			$valid_pointers[ $pointer_id ] = $pointer;
		}

		// No valid pointers? Stop here.
		if ( empty( $valid_pointers ) ) {
			return;
		}

		// Set our class variable $current_screen_pointers
		$this->current_screen_pointers = $valid_pointers;

		// Add our javascript to handle pointers
		add_action( 'admin_print_footer_scripts', array( $this, 'display_pointers' ) );

		// Add pointers style and javascript to queue.
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
	}

	/**
	 * Retrieves pointers for the current admin screen. Use the 'owf_admin_pointers' hook to add your own pointers.
	 *
	 * @return array Current screen pointers
	 * @since 3.2
	 */
	private function get_current_screen_pointers() {
		$pointers = '';

		$screen    = get_current_screen();
		$screen_id = $screen->id;

		// Format : array( 'screen_id' => array( 'pointer_id' => array([options : target, content, position...]) ) );

		$welcome_title     = esc_html__( "Welcome to Oasis Workflow", "oasisworkflow" );
		$img_html          = "<img src='" . OASISWF_URL . "img/small-arrow.gif" . "' style='border:0px;' />";
		$welcome_message_1 = esc_html__( "To get started with Oasis Workflow follow the steps listed below.", "oasisworkflow" );
		$welcome_message_2
		                   = sprintf( __( "1. Activate the plugin by providing a valid license key on Workflows %s Settings, License tab.",
			"oasisworkflow" ), $img_html );
		$welcome_message_3
		                   = esc_html__( "2. Create a new workflow OR modify/use the sample workflows that come with the plugin.",
			"oasisworkflow" );
		$welcome_message_4
		                   = sprintf( __( "3. Activate the workflow process by going to Workflows %s Settings, Workflow tab.",
			"oasisworkflow" ), $img_html );
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$default_pointers = array(
				'toplevel_page_oasiswf-inbox' => array(
					'owf_install' => array(
						'target'   => '#toplevel_page_oasiswf-inbox',
						'content'  => '<h3>' . $welcome_title . '</h3> <p>' . $welcome_message_1 . '</p><p>' .
						              $welcome_message_2 . '</p><p>' . $welcome_message_3 . '</p><p>' .
						              $welcome_message_4 . '</p>',
						'position' => array( 'edge' => 'left', 'align' => 'center' ),
					)
				)
			);
		} else {
			$default_pointers = array(
				'plugins' => array(
					'owf_install' => array(
						'target'   => '#toplevel_page_oasiswf-inbox',
						'content'  => '<h3>' . $welcome_title . '</h3> <p>' . $welcome_message_1 . '</p><p>' .
						              $welcome_message_2 . '</p><p>' . $welcome_message_3 . '</p><p>' .
						              $welcome_message_4 . '</p>',
						'position' => array( 'edge' => 'left', 'align' => 'center' ),
					)
				)
			);
		}

		if ( ! empty( $default_pointers[ $screen_id ] ) ) {
			$pointers = $default_pointers[ $screen_id ];
		}

		return apply_filters( 'owf_admin_pointers', $pointers, $screen_id );
	}

	/**
	 * Finally prints the javascript that'll make our pointers alive.
	 *
	 * @since 3.2
	 */
	public function display_pointers() {
		if ( ! empty( $this->current_screen_pointers ) ):
			?>
           <script type="text/javascript">// <![CDATA[
             jQuery(document).ready(function ($) {
               if (typeof (jQuery().pointer) != 'undefined') {
				   <?php foreach ( $this->current_screen_pointers as $pointer_id => $data): ?>
                 $('<?php echo esc_js( $data['target'] ) ?>').pointer({
                   content: '<?php echo wp_kses_post( addslashes( $data['content'] ) ); ?>',
                   position: {
                     edge: '<?php echo esc_js( $data['position']['edge'] ) ?>',
                     align: '<?php echo esc_js( $data['position']['align'] ) ?>'
                   },
                   close: function () {
                     $.post(ajaxurl, {
                       pointer: '<?php echo esc_js( $pointer_id ) ?>',
                       action: 'dismiss-wp-pointer'
                     })
                   }
                 }).pointer('open')
				   <?php endforeach ?>
               }
             })
             // ]]></script>
		<?php
		endif;
	}

	/**
	 * If free version exists display an error asking the user to deactivate and delete the free version
	 * Deactivate the "Pro" version
	 *
	 */
	public function validate_free_version_exists() {
		$plugin = plugin_basename( __FILE__ );
		if ( is_plugin_active( 'oasis-workflow/oasiswf.php' ) ) {
			add_action( 'admin_notices', array( $this, 'deactivate_free_version_notice' ) );
			add_action( 'network_admin_notices', array( $this, 'deactivate_free_version_notice' ) );
			deactivate_plugins( $plugin );
			if ( isset( $_GET['activate'] ) ) { // phpcs:ignore
				// Do not sanitize it because we are destroying the variables from URL
				unset( $_GET['activate'] ); // phpcs:ignore
			}

			return;
		}
	}

	/**
	 * Admin notice for deactivating the "free" version
	 */
	public function deactivate_free_version_notice() {
		?>
       <div class="notice notice-error is-dismissible">
          <p><?php echo sprintf( esc_html__( 'In order to use the "Pro" version, you need to deactivate and delete the free/lite version of Oasis Workflow plugin on the %splugins page%s',
				  'oasisworkflow' ),
				  '<a href="' .
				  esc_url( wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=oasis-workflow%2Foasiswf.php&amp;plugin_status=all&amp;paged=1&amp;s=',
					  'deactivate-plugin_oasis_workflow/oasiswf.php' ) ) . '">', '</a>' );
			  ?>
          </p>
       </div>
		<?php
	}

	/**
	 * Workflows List Page action.
	 * This method is called when the menu item "All Workflows" is clicked.
	 *
	 * @since 2.0
	 */
	public function list_workflows_page_content() {

		// phpcs:ignore
		$workflow_id = isset( $_GET['wf_id'] ) ? intval( sanitize_text_field( $_GET["wf_id"] ) ) : "";
		if ( ! empty ( $workflow_id ) ) {
			$this->create_workflow_page_content();
		} else {
			include( OASISWF_PATH . "includes/pages/workflow-list.php" );
		}
	}

	/**
	 * New Workflow create action.
	 * This method is called when the menu item "Add New Workflow" is clicked.
	 *
	 * @since 2.0
	 */
	public function create_workflow_page_content() {
		include( OASISWF_PATH . "includes/pages/workflow-create.php" );
	}

	/**
	 * Inbox page action.
	 * This method is called when the menu item "Inbox" is clicked.
	 *
	 * @since 2.0
	 */
	public function workflow_inbox_page_content() {
		include( OASISWF_PATH . "includes/pages/workflow-inbox.php" );
	}

	/**
	 * Workflow History page action.
	 * This method is called when the menu item "History" is clicked
	 *
	 * @since 2.0
	 */
	public function workflow_history_page_content() {
		include( OASISWF_PATH . "includes/pages/workflow-history.php" );
		include( OASISWF_PATH . "includes/pages/subpages/delete-history.php" );
	}

	/**
	 * Workflow revision compare
	 * This method is called when "Revision Compare" is clicked.
	 *
	 * @since 2.0
	 */
	public function revision_compare_page_content() {
		include( OASISWF_PATH . "includes/pages/revision-compare.php" );
	}

	/**
	 * Reports page.
	 * This method is called when "Reports" is clicked.
	 */
	public function workflow_reports_page_content() {
		include( OASISWF_PATH . "includes/pages/workflow-reports.php" );
	}

	/**
	 * Custom cron interval - for auto submit.
	 * TODO: yet to be implemented.
	 *
	 * @since 3.0
	 */
	public function custom_cron_interval( $interval ) {

		$interval['minutes_15'] = array(
			'interval' => 15 * 60,
			'display'  => esc_html__( '15 minutes', 'oasisworkflow' )
		);
		$interval['minutes_30'] = array(
			'interval' => 30 * 60,
			'display'  => esc_html__( '30 minutes', 'oasisworkflow' )
		);
		$interval['minutes_45'] = array(
			'interval' => 45 * 60,
			'display'  => esc_html__( '45 minutes', 'oasisworkflow' )
		);
		$interval['hours_4']    = array(
			'interval' => 240 * 60,
			'display'  => esc_html__( '4 hours', 'oasisworkflow' )
		);
		$interval['hours_8']    = array(
			'interval' => 480 * 60,
			'display'  => esc_html__( '8 hours', 'oasisworkflow' )
		);
		$interval['hours_12']   = array(
			'interval' => 720 * 60,
			'display'  => esc_html__( '12 hours', 'oasisworkflow' )
		);

		// Filter to add the custom cron interval
		$interval = apply_filters( "owf_auto_submit_custom_interval", $interval );

		return $interval;
	}

	/**
	 * Redirect user to workflow inbox page if the user role is selected at
	 * workflow setting tab to make user redirect to inbox page else redirect to dashboard.
	 *
	 * @since 4.5
	 */
	public function dashboard_redirect( $url, $request, $user ) {
		if ( isset( $user->roles ) && ( ! empty( $user->roles ) ) ) {
			$login_redirect_roles = get_option( 'oasiswf_login_redirect_roles_setting' );
			/* If login redirect option is enabled
          * than redirect user to workflows inbox page
          */
			if ( is_array( $login_redirect_roles ) && array_key_exists( $user->roles[0], $login_redirect_roles ) ) {
				$login_url = admin_url() . 'admin.php?page=oasiswf-inbox';

				return $login_url;
			} else {
				// return to the requested url
				return $url;
			}
		} else {
			return $url;
		}
	}

	/**
	 * Load all the classes - as part of init action hook
	 *
	 * @since 2.0
	 */
	public function load_all_classes() {

		/**
		 * Logs a message using ml_log_message if available, or falls back to error_log.
		 *
		 * @param string $message The message to log.
		 * @param string $context Optional context/category for the message (only for ml_log_message).
		 * 
		 * @since 10.2
		 */
		function ml_maybe_log_message( $message ) {
			if ( function_exists( 'ml_log_message' ) ) {
				ml_log_message( $message );
			} else {
				error_log( print_r($message, true) );
			}
		}

		/*
		 * include model classes
		 */

		if ( ! class_exists( 'OW_Workflow_Step' ) ) {
			include( OASISWF_PATH . 'includes/models/class-ow-workflow-step.php' );
		}

		if ( ! class_exists( 'OW_Workflow' ) ) {
			include( OASISWF_PATH . 'includes/models/class-ow-workflow.php' );
		}

		if ( ! class_exists( 'OW_Action_History' ) ) {
			include( OASISWF_PATH . 'includes/models/class-ow-action-history.php' );
		}

		if ( ! class_exists( 'OW_Review_History' ) ) {
			include( OASISWF_PATH . 'includes/models/class-ow-review-history.php' );
		}

		/*
		 * include service classes
		 */
		if ( ! class_exists( 'OW_Email' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-email.php' );
		}

		if ( ! class_exists( 'OW_Place_Holders' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-placeholders.php' );
		}

		if ( ! class_exists( 'OW_Workflow_Validator' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-workflow-validator.php' );
		}

		if ( ! class_exists( 'OW_Workflow_Service' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-workflow-service.php' );
		}

		if ( ! class_exists( 'OW_Process_Flow' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-process-flow.php' );
		}

		if ( ! class_exists( 'OW_History_Service' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-history-service.php' );
		}

		if ( ! class_exists( 'OW_Inbox_Service' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-inbox-service.php' );
		}

		if ( ! class_exists( 'OW_License_Service' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-license-service.php' );
		}

		if ( ! class_exists( 'OW_Revision_Service' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-revision-service.php' );
		}

		if ( ! class_exists( 'OW_Report_Service' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-report-service.php' );
		}

		if ( ! class_exists( 'OW_Auto_Submit_Service' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-auto-submit-service.php' );
		}

		if ( ! class_exists( 'OW_Duplicate_Service' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-duplicate-service.php' );
		}

		/*
		 * Settings classes
		 */
		if ( ! class_exists( 'OW_Settings_Base' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-settings-base.php' );
		}

		if ( ! class_exists( 'OW_License_Settings' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-license-settings.php' );
		}

		if ( ! class_exists( 'OW_Email_Settings' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-email-settings.php' );
		}

		if ( ! class_exists( 'OW_Workflow_Settings' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-workflow-settings.php' );
		}

		if ( ! class_exists( 'OW_Auto_Submit_Settings' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-auto-submit-settings.php' );
		}

		if ( ! class_exists( 'OW_Workflow_Terminology_Settings' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-workflow-terminology-settings.php' );
		}

		if ( ! class_exists( 'OW_Document_Revision_Settings' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-document-revision-settings.php' );
		}

		if ( ! class_exists( '$ow_external_user_settings' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-external-user-settings.php' );
		}

		if ( ! class_exists( 'OW_Tools_Service' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-tools-service.php' );
		}

		if ( ! class_exists( 'OW_Custom_Capabilities' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-custom-capabilities.php' );
		}

		/**
		 * Include API classes
		 */
		if ( ! class_exists( 'OWAPI' ) ) {
			include( OASISWF_PATH . 'includes/class-ow-api.php' );
		}

		include( OASISWF_PATH . 'includes/api/api-usercap.php' );
		include( OASISWF_PATH . 'includes/api/api-settings.php' );
		include( OASISWF_PATH . 'includes/api/api-workflow.php' );
		include( OASISWF_PATH . 'includes/api/api-utility.php' );
	}

	/**
	 * Register custom post statuses - used by the revision process.
	 * And call the upgrade action.
	 *
	 * @since 2.0
	 */
	public function register_custom_post_types() {

		register_post_status( 'usedrev', array(
			'label'                     => esc_html__( 'Used Revision', 'oasisworkflow' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false
		) );

		register_post_status( 'currentrev', array(
			'label'                     => esc_html__( 'Current Revision', 'oasisworkflow' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false
		) );

		register_post_status( 'owf_scheduledrev', array(
			'label'                     => esc_html__( 'Scheduled Revision', 'oasisworkflow' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Scheduled Revision <span class="count">(%s)</span>',
				'Scheduled Revision <span class="count">(%s)</span>', 'oasisworkflow' )
		) );


		$this->run_on_upgrade();
	}

	/**
	 * register post meta, so that it's recognized on the front end/gutenberg
	 */
	public function register_custom_post_meta() {

		register_meta( "post", "_oasis_is_in_workflow", array(
			"show_in_rest"  => true,
			"single"        => true,
			"type"          => "integer",
			"auth_callback" => function () {
				return current_user_can( 'edit_posts' );
			}
		) );

		register_meta( "post", "_oasis_original", array(
			"show_in_rest"  => true,
			"single"        => true,
			"type"          => "integer",
			"auth_callback" => function () {
				return current_user_can( 'edit_posts' );
			}
		) );

	   register_meta( "post", "_oasis_task_priority", array(
		   "show_in_rest"  => true,
		   "single"        => true,
		   "type"          => "string",
		   "auth_callback" => function () {
			   return current_user_can( 'edit_posts' );
		   }
	   ) );

	}

	/**
	 * Invoked when a new blog is added in a multi-site setup
	 *
	 * @since 2.0
	 */
	public function run_on_add_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		global $wpdb;
		if ( is_plugin_active_for_network( basename( dirname( __FILE__ ) ) . '/oasis-workflow-pro.php' ) ) {
			$old_blog = $wpdb->blogid;
			switch_to_blog( $blog_id );
			$this->run_for_site();
			restore_current_blog();
		}
	}

	/**
	 * Invoked with a blog is deleted in a multi-site setup
	 *
	 * @since 2.0
	 */
	public function run_on_delete_blog( $blog_id, $drop ) {
		global $wpdb;
		switch_to_blog( $blog_id );
		$this->delete_for_site();
		restore_current_blog();
	}

	/**
	 * enqueue CSS files
	 *
	 * @since 2.0
	 */
	public function add_css_files( $page ) {
		// ONLY load OWF scripts on OWF plugin pages
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : ""; // phpcs:ignore
		if ( is_admin() && preg_match_all( '/page=ow-settings(.*)|page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/',
				$request_uri, $matches ) ) {
			wp_enqueue_style( 'owf-css', OASISWF_URL . 'css/pages/context-menu.css', false, OASISWF_VERSION, 'all' );
			wp_enqueue_style( 'owf-modal-css', OASISWF_URL . 'css/lib/modal/simple-modal.css', false, OASISWF_VERSION,
				'all' );
			wp_enqueue_style( 'owf-calendar-css', OASISWF_URL . 'css/lib/calendar/datepicker.css', false,
				OASISWF_VERSION, 'all' );
			wp_enqueue_style( 'owf-oasis-workflow-css', OASISWF_URL . 'css/pages/oasis-workflow.css', false,
				OASISWF_VERSION, 'all' );

			/**
			 * enqueue status dropdown js
			 *
			 * @since 4.0
			 */
			wp_register_script( 'owf-post-statuses', OASISWF_URL . 'js/pages/ow-status-dropdown.js', array( 'jquery' ),
				OASISWF_VERSION );
			wp_enqueue_script( 'owf-post-statuses' );
		}
	}

	/**
	 * enqueue CSS files for dashboard
	 *
	 * @since 2.0
	 */
	public function add_css() {
		wp_enqueue_style( 'owf-css', OASISWF_URL . 'css/pages/workflow-dashboard-widget.css', false, OASISWF_VERSION,
			'all' );
	}

	/**
	 * enqueue javascripts
	 *
	 * @since 2.0
	 */
	public function add_js_files() {
		// phpcs:ignore
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : "";

		// ONLY load OWF scripts on OWF plugin pages
		if ( is_admin() &&
		     preg_match_all( '/page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $request_uri, $matches ) ) {
			echo "<script type='text/javascript'>
   					var wf_structure_data = '' ;
   					var wfeditable = '' ;
   					var wfPluginUrl  = '" . esc_url( OASISWF_URL ) . "' ;
                  var flag_compare_button = true;
                  var owf_process = '';
                  var wfaction = '';
                  var elementor_is_in_workflow = 'false';
   				</script>";
		}

		if ( is_admin() && isset( $_GET['page'] ) && ( $_GET["page"] == "oasiswf-inbox" ||
		                                               $_GET["page"] == "oasiswf-history" ) ) {
			OW_Plugin_Init::enqueue_and_localize_inbox_script();
		}
	}

	/**
	 * JS Variable for Elementor
	 *
	 * @since 2.0
	 */
	public function add_elementor_js_var() {
		echo "<script type='text/javascript'>
				var wf_structure_data = '' ;
				var wfeditable = '' ;
				var owElementorEditor = 'true';
				var wfPluginUrl  = '" . esc_url( OASISWF_URL ) . "' ;
				var flag_compare_button = true;
				var owf_process = '';
				var wfaction = '';
				var elementor_is_in_workflow = 'false';
			</script>";
	}

	public static function enqueue_and_localize_inbox_script() {

		$ow_process_flow = new OW_Process_Flow();
		wp_enqueue_script( 'owf-workflow-inbox', OASISWF_URL . 'js/pages/workflow-inbox.js', array( 'jquery' ),
			OASISWF_VERSION );
		wp_enqueue_script( 'owf-workflow-history', OASISWF_URL . 'js/pages/workflow-history.js', array( 'jquery' ),
			OASISWF_VERSION );

		wp_localize_script( 'owf-workflow-inbox', 'owf_workflow_inbox_vars', array(
			'workflowTeamsAvailable' => $ow_process_flow->is_teams_available(),
			'dateFormat'             => OW_Utility::instance()
			                                      ->owf_date_format_to_jquery_ui_format( get_option( 'date_format' ) ),
			'editDateFormat'         => OW_Utility::instance()
			                                      ->owf_date_format_to_jquery_ui_format( OASISWF_EDIT_DATE_FORMAT ),
			'abortWorkflowConfirm'   => esc_html__( 'Are you sure to abort the workflow?', 'oasisworkflow' ),
			'isCommentsMandotory'    => get_option( "oasiswf_comments_setting" ),
			'reminderEmailMessage'   => esc_html__( 'A reminder email about this assignment was sent successfully to the user.', 'oasisworkflow' ),
			'emptyComments'          => esc_html__( 'Please add comments.', 'oasisworkflow' )
		) );
	}

	/**
	 * load/enqueue javascripts as part of the footer
	 */
	public function load_js_files_footer() {
		// phpcs:ignore
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : "";
		$current_screen = get_current_screen();
		
		// ONLY load OWF scripts on OWF plugin pages
		if ( is_admin() &&
		     preg_match_all( '/page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $request_uri, $matches ) ) {
			//wp_enqueue_script( 'thickbox' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-widget' );
			wp_enqueue_script( 'jquery-ui-mouse' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-json', OASISWF_URL . 'js/lib/jquery.json.js', '', '2.3', true );
			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-droppable' );
		}

		if ( is_admin() &&
		     ( isset( $_GET['page'] ) && ( $_GET["page"] == "oasiswf-admin" || $_GET["page"] == "oasiswf-add" ) ) ) {
			wp_enqueue_style( 'select2-style', OASISWF_URL . 'css/lib/select2/select2.css', false, OASISWF_VERSION,
				'all' );
			wp_enqueue_script( 'jsPlumb', OASISWF_URL . 'js/lib/jquery.jsPlumb-all-min.js', array(
				'jquery-ui-core',
				'jquery-ui-draggable',
				'jquery-ui-droppable'
			), '1.4.1', true );
			wp_enqueue_script( 'drag-drop-jsplumb', OASISWF_URL . 'js/pages/drag-drop-jsplumb.js', array( 'jsPlumb' ),
				OASISWF_VERSION, true );
			wp_enqueue_script( 'select2-js', OASISWF_URL . 'js/lib/select2/select2.min.js', array( 'jquery' ),
				OASISWF_VERSION, true );
			wp_localize_script( 'drag-drop-jsplumb', 'drag_drop_jsplumb_vars', array(
				'clearAllSteps'      => esc_html__( 'Do you really want to clear all the steps?', 'oasisworkflow' ),
				'removeStep'         => esc_html__( 'This step is already defined. Do you really want to remove this step?',
					'oasisworkflow' ),
				'postStatusRequired' => esc_html__( 'Please select Post Status.', 'oasisworkflow' ),
				'pathBetween'        => esc_html__( 'The path between', 'oasisworkflow' ),
				'stepAnd'            => esc_html__( 'step and', 'oasisworkflow' ),
				'incorrect'          => esc_html__( 'step is incorrect.', 'oasisworkflow' ),
				'stepHelp'           => esc_html__( 'To edit/delete the step, right click on the step to access the step menu.',
					'oasisworkflow' ),
				'connectionHelp'     => esc_html__( 'To connect to another step drag a line from the "dot" to the next step.',
					'oasisworkflow' ),
				'postStatusLabel'    => esc_html__( 'Post Status', 'oasisworkflow' )
			) );
		}

		if ( is_admin() &&
		     preg_match_all( '/page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $request_uri, $matches ) &&
			 ! ( true === $current_screen->is_block_editor() ) ) { 

			wp_enqueue_script( 'owf-workflow-create', OASISWF_URL . 'js/pages/workflow-create.js', '', OASISWF_VERSION,
				true );
			wp_enqueue_script( 'owf-workflow-delete', OASISWF_URL . 'js/pages/workflow-delete.js', '', OASISWF_VERSION,
				true );

			wp_localize_script( 'owf-workflow-create', 'owf_workflow_create_vars', array(
				'alreadyExistWorkflow' => esc_html__( 'There is an existing workflow with the same name. Please choose another name.',
					'oasisworkflow' ),
				'unsavedChanges'       => esc_html__( 'You have unsaved changes.', 'oasisworkflow' ),
				'dateFormat'           => OW_Utility::instance()
				                                    ->owf_date_format_to_jquery_ui_format( get_option( 'date_format' ) ),
				'editDateFormat'       => OW_Utility::instance()
				                                    ->owf_date_format_to_jquery_ui_format( OASISWF_EDIT_DATE_FORMAT )
			) );
			wp_localize_script( 'owf-workflow-delete', 'owf_workflow_delete_vars', array(
				'workflow_delete_nonce' => wp_create_nonce( 'workflow_delete_nonce' )
			) );

			wp_enqueue_script( 'jquery-simplemodal', OASISWF_URL . 'js/lib/modal/jquery.simplemodal.js', '', '1.4.6',
				true );
			wp_enqueue_script( 'owf-workflow-util', OASISWF_URL . 'js/pages/workflow-util.js', '', OASISWF_VERSION,
				true );
			wp_enqueue_script( 'owf-escapechars', OASISWF_URL . 'js/pages/escapeChars.js', '', OASISWF_VERSION,
				true );
			wp_localize_script( 'owf-workflow-util', 'owf_workflow_util_vars', array(
				'dueDateInPast' => esc_html__( 'Due date cannot be in the past.', 'oasisworkflow' )
			) );

			wp_enqueue_script( 'text-edit-whizzywig', OASISWF_URL . 'js/lib/textedit/whizzywig63.js', '', '63', true );
			wp_enqueue_script( 'owf-workflow-step-info', OASISWF_URL . 'js/pages/subpages/step-info.js',
				array( 'text-edit-whizzywig' ), OASISWF_VERSION, true );
			wp_enqueue_script( 'owf-workflow-step-info', OASISWF_URL . 'js/pages/subpages/step-info.js', '',
				OASISWF_VERSION, true );
			wp_localize_script( 'owf-workflow-step-info', 'owf_workflow_step_info_vars', array(
				'stepNameRequired'      => esc_html__( 'Step name is required.', 'oasisworkflow' ),
				'stepNameAlreadyExists' => esc_html__( 'Step name already exists. Please use a different name.',
					'oasisworkflow' ),
				'selectAssignees'       => esc_html__( 'Please select assignee(s).', 'oasisworkflow' ),
				'selectPlaceholder'     => esc_html__( 'Please select a placeholder.', 'oasisworkflow' ),
				'numericDueDate'        => esc_html__( 'Please enter a numeric value for default due date.', 'oasisworkflow' ),
				'stepDueDate'           => esc_html__( 'Please enter the number of days for default due date..',
					'oasisworkflow' ),
			) );
		}

		if ( is_admin() && preg_match_all( '/edit\.(.*)/', $request_uri, $matches ) ) {
			include( OASISWF_PATH . "includes/pages/subpages/make-revision.php" );
			wp_enqueue_script( 'owf-workflow-util', OASISWF_URL . 'js/pages/workflow-util.js', '', OASISWF_VERSION,
				true );
			wp_enqueue_script( 'owf_make_revision', OASISWF_URL . 'js/pages/subpages/make-revision.js',
				array( 'jquery' ), OASISWF_VERSION, true );
			wp_enqueue_script( 'owf_duplicate_post', OASISWF_URL . 'js/pages/subpages/ow-duplicate-post.js',
				array( 'jquery' ), OASISWF_VERSION, true );
			wp_enqueue_style( 'owf-oasis-workflow-css', OASISWF_URL . 'css/pages/oasis-workflow.css', false,
				OASISWF_VERSION, 'all' );
			OW_Plugin_Init::enqueue_and_localize_simple_modal_script();
			$ow_process_flow = new OW_Process_Flow();
			$ow_process_flow->enqueue_and_localize_make_revision_script();

			/**
			 * enqueue status dropdown js
			 *
			 * @since 2.1
			 */
			wp_register_script( 'owf-post-statuses', OASISWF_URL . 'js/pages/ow-status-dropdown.js', array( 'jquery' ),
				OASISWF_VERSION );
			wp_enqueue_script( 'owf-post-statuses' );
		}

		if ( is_admin() &&
		     preg_match_all( '/page=ow-settings&tab=email_settings(.*)/', $request_uri, $matches ) ) {
			wp_enqueue_script( 'owf-email-settings', OASISWF_URL . 'js/pages/email-settings.js', array( 'jquery' ),
				OASISWF_VERSION );
		}
		if ( is_admin() &&
		     preg_match_all( '/page=ow-settings&tab=auto_submit_settings(.*)/', $request_uri, $matches ) ) {
			wp_enqueue_script( 'owf-auto-submit-settings', OASISWF_URL . 'js/pages/subpages/auto-submit.js',
				array( 'jquery' ), OASISWF_VERSION );
		}

		if ( is_admin() && isset( $_GET['tab'] ) && $_GET["tab"] == "email_settings" ) {
			wp_enqueue_style( 'select2-style', OASISWF_URL . 'css/lib/select2/select2.css', false, OASISWF_VERSION,
				'all' );
			wp_enqueue_script( 'select2-js', OASISWF_URL . 'js/lib/select2/select2.min.js', array( 'jquery' ),
				OASISWF_VERSION, true );
		}

		if ( is_admin() && preg_match_all( '/page=ow-settings&tab=external_user_settings(.*)/', $request_uri,
				$matches ) ) {
			wp_enqueue_script( 'owf-external-users-settings', OASISWF_URL . 'js/pages/subpages/external-user.js',
				array( 'jquery' ), OASISWF_VERSION );
		}

	}

	/**
	 * Enqueue and Localize the simple modal script
	 *
	 * @since 3.3 initial version
	 */
	public static function enqueue_and_localize_simple_modal_script() {
		wp_enqueue_script( 'jquery-simplemodal', OASISWF_URL . 'js/lib/modal/jquery.simplemodal.js', '', '1.4.6',
			true );
		wp_enqueue_style( 'owf-modal-css', OASISWF_URL . 'css/lib/modal/simple-modal.css', false, OASISWF_VERSION,
			'all' );
	}

	/**
	 * Enqueue oasis workflow gutenberg JavaScript and CSS
	 */
	public function ow_gutenberg_scripts() {
		if ( is_admin() ) {

			// load `owf_acf_validator` script so it can be used as `ow-gutenberg-sidebar-js` dependency
			$ow_process_flow = new OW_Process_Flow();
			$ow_process_flow->enqueue_acf_validator_script();

			$blockPath = '/dist/ow-gutenberg.js';
			$stylePath = '/dist/ow-gutenberg.css';

			$dependencies = [
				'wp-i18n',
				'wp-edit-post',
				'wp-element',
				'wp-editor',
				'wp-components',
				'wp-data',
				'wp-date',
				'utils',
				'wp-plugins',
				'wp-compose',
				'wp-edit-post',
				'wp-api-fetch',
				'wp-api'
			];

			if ( defined('ACF_VERSION') ) {
				$dependencies[] = 'owf_acf_validator';
			}

			// Enqueue the bundled block JS file
			wp_enqueue_script(
				'ow-gutenberg-sidebar-js',
				plugins_url( $blockPath, __FILE__ ),
				$dependencies,
				filemtime( plugin_dir_path( __FILE__ ) . $blockPath ),
				true // since v10.2 to load in footer
			);

			//      if ( function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			//         $locale  = gutenberg_get_jed_locale_data( 'oasisworkflow' );
			//         $content = 'wp.i18n.setLocaleData( ' . json_encode( $locale ) . ', "oasisworkflow" );';
			//         OW_Utility::instance()->logger($content);
			//         wp_script_add_data( 'ow-gutenberg-sidebar-js', 'data', $content );
			//      }

			// Enqueue frontend and editor block styles
			wp_enqueue_style(
				'ow-gutenberg-sidebar-css',
				plugins_url( $stylePath, __FILE__ ),
				'',
				filemtime( plugin_dir_path( __FILE__ ) . $stylePath )
			);

			$value = wp_set_script_translations( 'ow-gutenberg-sidebar-js', 'oasisworkflow',
				OASISWF_PATH . 'languages' );

			wp_localize_script('ow-gutenberg-sidebar-js', 'ow_gutenberg_sidebar_vars', array(
				'wpVersion' => get_bloginfo('version'),
			));
		}
	}

	public function ow_elementor_scripts() {

		// check if user logged in if not then return immediately
		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_style(
			'ow-elementor-custom-css',
			OASISWF_URL . 'css/pages/ow-elementor.css',
			OASISWF_VERSION, 'all' );

		wp_enqueue_script( 'ow-elementor-custom-js',
			OASISWF_URL . 'js/pages/ow-elementor.js',
			array( 'jquery', 'elementor-editor' ),
			true );

		$ow_process_flow = new OW_Process_Flow();
		$ow_history_service = new OW_History_Service();

		// Enqueue required files
		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_script( 'owf-alert',
			OASISWF_URL . 'js/lib/sweetalert.min.js',
			'',
			OASISWF_VERSION,
			true );

		wp_enqueue_script( 'jquery-simplemodal',
			OASISWF_URL . 'js/lib/modal/jquery.simplemodal.js',
			'',
			'1.4.6',
			true );

		wp_enqueue_style( 'owf-modal-css',
			OASISWF_URL . 'css/lib/modal/simple-modal.css',
			false,
			OASISWF_VERSION,
			'all' );

		wp_enqueue_style( 'owf-calendar-css',
			OASISWF_URL . 'css/lib/calendar/datepicker.css',
			false,
			OASISWF_VERSION,
			'all' );

		wp_enqueue_style( 'owf-oasis-workflow-css',
			OASISWF_URL . 'css/pages/oasis-workflow.css',
			false,
			OASISWF_VERSION,
			'all' );

		wp_enqueue_script( 'owf-workflow-util',
			OASISWF_URL . 'js/pages/workflow-util.js',
			'',
			OASISWF_VERSION,
			true );

		// wp_localize_script( 'owf-workflow-util', 'owf_workflow_util_vars', array(
		// 	'dueDateInPast' => __( 'Due date cannot be in the past.', 'oasisworkflow' )
		// ) );

		// check if role is applicable to submit to workflow
		$post_id   = get_the_ID();
		$post_type = get_post_type();

		$post_status = get_post_status( $post_id );

		$is_workflow_enabled = false;

		$action_histories = $ow_history_service->get_action_history_by_status( "assignment", $post_id );
		
		$current_history_id = isset( $_GET['oasiswf'] ) ? intval( $_GET['oasiswf'] ) : 0;
		if ( empty( $current_history_id ) ) {
			$current_history_id = isset( $action_histories[0] ) && ! empty( $action_histories[0] ) && isset( $action_histories[0]->ID ) ? $action_histories[0]->ID : 0;
		}

		$is_claim = false;

		if ( 
			! empty( $current_history_id ) &&
			$ow_process_flow->check_for_claim( $current_history_id )
		) {
			$is_claim = true;
		}

		$selected_user = get_current_user_id();
		
		// check whether user is assigned to post or not
		$assignResult = $ow_process_flow->is_user_assigned_to_post( $selected_user, $post_id );

		// initialize the return array
		$elementor_vars = array(
			"post_id"     => $post_id,
			"post_type"    => $post_type,
			"post_status"  => $post_status,
			"current_history_id"  => $current_history_id,
			"current_user_id"  => $selected_user,
			"is_role_applicable"     => false,
			"can_skip_workflow"      => current_user_can( 'ow_skip_workflow' ),
			"can_submit_to_workflow" => current_user_can( 'ow_submit_to_workflow' )
		);

		$is_activated_workflow = get_option( 'oasiswf_activate_workflow' );

		$allowed_post_types = get_option( 'oasiswf_show_wfsettings_on_post_types' );

		$off_revsion_4_workflow = get_option( 'oasiswf_disable_workflow_4_revision' );
		$elementor_vars["disable_workflow_4_revision"] = ! empty( $off_revsion_4_workflow ) ? true : false;

		if ( $allowed_post_types && in_array( $post_type, $allowed_post_types ) ) {
			$is_workflow_enabled = true;
		}

		$is_oasis_original = get_post_meta( $post_id, '_oasis_original', true );
		$oasis_is_in_workflow = get_post_meta( $post_id, '_oasis_is_in_workflow', true );
		if ( $oasis_is_in_workflow == 1 ) {
			$elementor_vars["is_role_applicable"] = true;
		} else {
			$is_role_applicable = $ow_process_flow->check_is_role_applicable( $post_id );

			if ( $is_activated_workflow === "active" && $is_workflow_enabled && $is_role_applicable ) {
				$elementor_vars["is_role_applicable"] = true;
			}
		}

		$show_workflow_for_post_types = get_option( 'oasiswf_show_wfsettings_on_post_types' );
		$is_role_applicable           = $ow_process_flow->check_is_role_applicable( $post_id );
		$hide_publish_option          = false;

		if ( is_array( $show_workflow_for_post_types ) && in_array( $post_type, $show_workflow_for_post_types ) ) {
			// Display ootb publish section based on applicable roles and post type
			if ( $is_role_applicable == true ) {
				$hide_publish_option = true;
			}
		}

		// Check if post is in workflow
        $oasis_is_in_workflow = empty( $oasis_is_in_workflow ) ? false : (boolean) $oasis_is_in_workflow;

		//Post is in workflow then hide publish button
		if ( $oasis_is_in_workflow === true ) {
			echo "<script type='text/javascript'>
                  var elementor_is_in_workflow = 'true';
   				</script>";
		} else {
			echo "<script type='text/javascript'>
                  var elementor_is_in_workflow = 'false';
   				</script>";
		}

		$elementor_vars['is_in_workflow'] = $oasis_is_in_workflow;
		$elementor_vars['is_oasis_original'] = $is_oasis_original;

		// If submit to workflow
		if ( get_option( "oasiswf_activate_workflow" ) == "active" && current_user_can( 'ow_submit_to_workflow' ) &&
		     is_admin() && $hide_publish_option && $post_status !== "publish" && $oasis_is_in_workflow != 1 ) {
			include( OASISWF_PATH . "includes/pages/subpages/submit-workflow.php" );
			$ow_process_flow->enqueue_and_localize_submit_workflow_script();
			wp_enqueue_script( 'owf_elementor_submit', OASISWF_URL . 'js/pages/subpages/ow-elementor-submit.js',
				array( 'jquery' ), OASISWF_VERSION, true );

			echo "<script type='text/javascript'>
                  var owf_process = 'submit';
                  var wfaction = 'elementor';
   				</script>";
		}
		
		wp_localize_script( 'ow-elementor-custom-js', 'owf_elementor_vars', $elementor_vars);
		
		// If sign-off step
		if ( 
            get_option( "oasiswf_activate_workflow" ) == "active" && 
            isset( $post_id ) && 
            ! empty( $post_id ) &&
            is_admin() && 
            $post_status !== "publish" &&
            true === $oasis_is_in_workflow
         ) {

            $script = false;

            if( $is_claim !== true && current_user_can( 'ow_abort_workflow' ) ) {
                $script = true;
                echo "<script type='text/javascript'>
                var owf_abort = 'abort-workflow';
                  </script>";
            }
  
			if( $is_claim !== true && $assignResult !== false && ( isset( $_GET['oasiswf'] ) || ! empty( $current_history_id ) ) ) {
				if( current_user_can( 'ow_sign_off_step' ) ) {
					$script = true;
					echo "<script type='text/javascript'>
					var flag_compare_button = false;
					  var owf_process = 'sign-off';
					  </script>";
				}
				if( current_user_can( 'ow_reassign_task' ) ) {
					$script = true;
					echo "<script type='text/javascript'>
					var owf_reassign = 'reassign-task';
					  </script>";
				}
			} elseif( $is_claim === true ) {
				$script = true;
				echo "<script type='text/javascript'>
				var owf_claim = 'claim-task';
				  </script>";
			}

            if( $script ) {
				include( OASISWF_PATH . "includes/pages/subpages/submit-step.php" );
                $ow_process_flow->enqueue_and_localize_submit_step_script();
            }
		}

	}

	/**
	 * Plugin Update notifier
	 */
	public function ow_plugin_updater() {
		if ( ! class_exists( 'OW_Plugin_Updater' ) ) {
			include( OASISWF_PATH . "includes/class-ow-plugin-updater.php" );

			// setup the plugin updater
			$edd_updater = new OW_Plugin_Updater( OASISWF_STORE_URL, __FILE__, array(
					'version'   => OASISWF_VERSION,
					// current version number
					'license'   => trim( get_option( 'oasiswf_license_key' ) ),
					// license key (used get_option above to retrieve from DB)
					'item_name' => OASISWF_PRODUCT_NAME,
					// name of this plugin
					'author'    => 'Nugget Solutions Inc.'
					// author of this plugin
				)
			);
		}
	}

}

// initialize the plugin
$ow_plugin_init = new OW_Plugin_Init();
add_action( 'admin_init', array( $ow_plugin_init, 'ow_plugin_updater' ) );

?>