<?php

/*
 * Plugin Name: Oasis Workflow
 *   Plugin URI: http://www.oasisworkflow.com
 *   Description: Automate your WordPress Editorial Workflow with Oasis Workflow.
 *   Version: 6.5.4
 *   Author: Nugget Solutions Inc.
 *   Author URI: http://www.nuggetsolutions.com
 *   Text Domain: oasisworkflow
 *   ----------------------------------------------------------------------
 *   Copyright 2011-2026 Nugget Solutions Inc.
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

define('OASISWF_VERSION', '6.5.4');
define('OASISWF_DB_VERSION', '6.5.4');
define('OASISWF_PATH', plugin_dir_path(__FILE__));  // use for include files to other files
define('OASISWF_ROOT', dirname(__FILE__));
define('OASISWF_FILE_PATH', OASISWF_ROOT . '/' . basename(__FILE__));
define('OASISWF_BASE_NAME', plugin_basename(__FILE__));
define('OASISWF_URL', plugins_url('/', __FILE__));
define('OASISWF_STORE_URL', 'https://www.oasisworkflow.com');
define('OASISWF_SETTINGS_PAGE', esc_url(add_query_arg('page', 'ef-settings', get_admin_url(null, 'admin.php'))));
define('OASISWF_EDIT_DATE_FORMAT', 'm-M d, Y');
define('OASISWF_DATE_TIME_FORMAT', 'm-M d, Y @ H:i');
define('OASIS_PER_PAGE', '50');
define('IS_OASISWF_ACTIVATED', true);
load_plugin_textdomain('oasisworkflow', false, basename(dirname(__FILE__)) . '/languages');

/*
 * include utility classes
 */
if (!class_exists('OW_Utility')) {
    include (OASISWF_PATH . 'includes/class-ow-utility.php');
}
if (!class_exists('OW_Custom_Statuses')) {
    include (OASISWF_PATH . 'includes/class-ow-custom-statuses.php');
}

/**
 * OW_Plugin_Init Class
 *
 * This class will set the plugin
 *
 * @since 2.0
 */
class OW_Plugin_Init
{
    private $current_screen_pointers = array();

    /*
     * Set things up.
     *
     * @since 2.0
     */
    public function __construct()
    {
        // run on activation of plugin
        register_activation_hook(__FILE__, array($this, 'oasis_workflow_activate'));

        // run on deactivation of plugin
        register_deactivation_hook(__FILE__, array($this, 'oasis_workflow_deactivate'));

        // run on uninstall
        register_uninstall_hook(__FILE__, array('OW_Plugin_Init', 'oasis_workflow_uninstall'));

        // load the js and css files
        add_action('init', array($this, 'load_css_and_js_files'));

        // load the classes
        add_action('init', array($this, 'load_all_classes'));

        // register custom post meta
        add_action('init', array($this, 'register_custom_post_meta'));

        add_action('admin_menu', array($this, 'register_menu_pages'));

        add_action('wpmu_new_blog', array($this, 'run_on_add_blog'), 10, 6);
        add_action('delete_blog', array($this, 'run_on_delete_blog'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'show_welcome_message_pointers'));
        add_action('admin_init', array($this, 'run_on_upgrade'));

        // redirect to newsletter sign up page on plugin install
        add_action('admin_init', array($this, 'newsletter_signup_redirect'));

        // display workflow assignment widget
        add_action('wp_dashboard_setup', array($this, 'add_workflow_tasks_summary_widget'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_wp_dashboard_style'));
        // Add custom link for our plugin
        add_filter('plugin_action_links_' . OASISWF_BASE_NAME, array($this, 'oasiswf_plugin_action_links'));

        // Hook scripts function into block editor hook
        add_action('enqueue_block_assets', array($this, 'ow_gutenberg_scripts'));

        // add_filter( 'register_post_type_args', array( $this, 'update_custom_post_type_args' ), 10, 2 );
    }

    /**
     * Activate the plugin
     *
     * @since 2.0
     */
    public function oasis_workflow_activate($network_wide)
    {
        global $wpdb;
        $this->run_on_activation();
        if (function_exists('is_multisite') && is_multisite()) {
            // check if it is a network activation - if so, run the activation function for each blog id
            if ($network_wide) {
                // Get all blog ids
                $blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");  // phpcs:ignore
                foreach ($blogids as $blog_id) {
                    switch_to_blog($blog_id);
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
     * deactivate the plugin
     *
     * @since 2.0
     */
    public function oasis_workflow_deactivate($network_wide)
    {
        global $wpdb;

        if (function_exists('is_multisite') && is_multisite()) {
            // check if it is a network activation - if so, run the activation function for each blog id
            if ($network_wide) {
                // Get all blog ids
                $blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");  // phpcs:ignore
                foreach ($blogids as $blog_id) {
                    switch_to_blog($blog_id);
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
     * Runs on plugin uninstall.
     *  a static class method or function can be used in an uninstall hook
     *
     * @since 2.0
     */
    public static function oasis_workflow_uninstall()
    {
        global $wpdb;
        OW_Plugin_Init::run_on_uninstall();
        if (function_exists('is_multisite') && is_multisite()) {
            // Get all blog ids; foreach them and call the uninstall procedure on each of them
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");  // phpcs:ignore

            // Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                // phpcs:ignore
                if ($wpdb->query('SHOW TABLES FROM ' . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'")) {
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
     * Create/Register menu items for the plugin.
     *
     * @since 2.0
     */
    public function register_menu_pages()
    {
        $current_role = OW_Utility::instance()->get_current_user_role();

        // use this hook to change the menu position of workflow menu
        $position = apply_filters('ow_workflow_menu_position', $this->get_menu_position('.8'));

        $ow_process_flow = new OW_Process_Flow();
        $inbox_count = $ow_process_flow->get_assigned_post_count();
        $count = ($inbox_count) ? '<span class="update-plugins count"><span class="plugin-count">' . $inbox_count . '</span></span>' : '';

        // top level menu for Workflows
        add_menu_page(
            esc_html__('Workflows', 'oasisworkflow'),
            esc_html__('Workflows', 'oasisworkflow') . $count, $current_role,
            'oasiswf-inbox',
            array($this, 'workflow_inbox_page_content'),
            '',
            $position
        );

        // Inbox menu
        add_submenu_page('oasiswf-inbox',
            esc_html__('Inbox', 'oasisworkflow'),
            esc_html__('Inbox', 'oasisworkflow') . $count, $current_role,
            'oasiswf-inbox',
            array($this, 'workflow_inbox_page_content'));

        $workflow_history_label = OW_Utility::instance()->get_custom_workflow_terminology('workflowHistoryText');

        // Workflow history menu - it can have a custom label, as defined in Settings -> Terminology
        if (current_user_can('ow_view_workflow_history')) {
            add_submenu_page('oasiswf-inbox',
                $workflow_history_label,
                $workflow_history_label,
                $current_role,
                'oasiswf-history',
                array($this, 'workflow_history_page_content'));
        }

        // Reports
        if (current_user_can('ow_view_reports')) {
            add_submenu_page('oasiswf-inbox',
                esc_html__('Reports', 'oasisworkflow'),
                esc_html__('Reports', 'oasisworkflow'),
                $current_role,
                'oasiswf-reports',
                array($this, 'workflow_reports_page_content'));
        }

        // All Workflows - will display the workflow list
        if (current_user_can('ow_create_workflow') || current_user_can('ow_edit_workflow')) {
            add_submenu_page('oasiswf-inbox',
                esc_html__('All Workflows', 'oasisworkflow'),
                esc_html__('All Workflows', 'oasisworkflow'),
                'ow_create_workflow',
                'oasiswf-admin',
                array($this, 'list_workflows_page_content'));
        }

        // Add New Workflow
        if (current_user_can('ow_create_workflow')) {
            add_submenu_page('oasiswf-inbox',
                esc_html__('Add New Workflow', 'oasisworkflow'),
                esc_html__('Add New Workflow', 'oasisworkflow'),
                'ow_create_workflow',
                'oasiswf-add',
                array($this, 'create_workflow_page_content'));
        }

        if (current_user_can('ow_edit_workflow')) {
            add_submenu_page('oasiswf-inbox',
                esc_html__('Custom Statuses', 'oasisworkflow'),
                esc_html__('Custom Statuses', 'oasisworkflow'),
                $current_role,
                'oasiswf-custom-statuses',
                array($this, 'custom_statuses_page_content'));
        }

        if (current_user_can('ow_export_import_workflow')) {
            add_submenu_page('oasiswf-inbox',
                esc_html__('Tools', 'oasisworkflow'),
                esc_html__('Tools', 'oasisworkflow'),
                $current_role,
                'oasiswf-tools',
                array($this, 'display_workflow_tools'));
        }

        // Stay Informed page - hidden from the menu
        if (current_user_can('ow_edit_workflow')) {
            add_submenu_page('oasiswf-stay-informed',
                esc_html__('Stay Informed', 'oasisworkflow'),
                esc_html__('Stay Informed', 'oasisworkflow'),
                $current_role,
                'oasiswf-stay-informed',
                array($this, 'news_letter_page_content'));
        }

        // to add sub menus for add ons
        do_action('owf_add_submenu');
    }

    public function custom_statuses_page_content()
    {
        include (OASISWF_PATH . 'includes/pages/ow-custom-statuses.php');
    }

    public function display_workflow_tools()
    {
        include (OASISWF_PATH . 'includes/pages/workflow-tools.php');
    }

    public function news_letter_page_content()
    {
        include (OASISWF_PATH . 'includes/pages/subscribe-form.php');
    }

    public function run_on_upgrade()
    {
        $pluginOptions = get_site_option('oasiswf_info');

        if ($pluginOptions['version'] == '1.3') {
            $this->upgrade_database_14();
            $this->upgrade_database_15();
            $this->upgrade_database_16();
            $this->upgrade_database_17();
            $this->upgrade_database_19();
            $this->upgrade_database_20();
            $this->upgrade_database_22();
            $this->upgrade_database_23();
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '1.4') {
            $this->upgrade_database_15();
            $this->upgrade_database_16();
            $this->upgrade_database_17();
            $this->upgrade_database_19();
            $this->upgrade_database_20();
            $this->upgrade_database_22();
            $this->upgrade_database_23();
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '1.5') {
            $this->upgrade_database_16();
            $this->upgrade_database_17();
            $this->upgrade_database_19();
            $this->upgrade_database_20();
            $this->upgrade_database_22();
            $this->upgrade_database_23();
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '1.6') {
            $this->upgrade_database_17();
            $this->upgrade_database_19();
            $this->upgrade_database_20();
            $this->upgrade_database_22();
            $this->upgrade_database_23();
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '1.7') {
            $this->upgrade_database_19();
            $this->upgrade_database_20();
            $this->upgrade_database_22();
            $this->upgrade_database_23();
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '1.8') {
            $this->upgrade_database_19();
            $this->upgrade_database_20();
            $this->upgrade_database_22();
            $this->upgrade_database_23();
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '1.9') {
            $this->upgrade_database_20();
            $this->upgrade_database_22();
            $this->upgrade_database_23();
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '2.0') {
            $this->upgrade_database_22();
            $this->upgrade_database_23();
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '2.1') {
            $this->upgrade_database_22();
            $this->upgrade_database_23();
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '2.2') {
            $this->upgrade_database_23();
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '2.3') {
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '2.4') {
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '2.5') {
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '2.6') {
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '2.7') {
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '2.8') {
            $this->upgrade_database_29();
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '2.9') {
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '3.0') {
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '3.1') {
            $this->upgrade_database_33();
        } else if ($pluginOptions['version'] == '3.2') {
            $this->upgrade_database_33();
        }

        // update the version value
        $oasiswf_info = array(
            'version' => OASISWF_VERSION,
            'db_version' => OASISWF_DB_VERSION
        );
        update_site_option('oasiswf_info', $oasiswf_info);
    }

    public function upgrade_database_14()
    {
        global $wpdb;

        // look through each of the blogs and upgrade the DB
        if (function_exists('is_multisite') && is_multisite()) {
            // Get all blog ids; foreach them and call the uninstall procedure on each of them
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");  // phpcs:ignore

            // Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                // phpcs:ignore
                if ($wpdb->query('SHOW TABLES FROM ' . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'")) {
                    $this->upgrade_helper_14();
                }
                restore_current_blog();
            }
            return;
        }
        $this->upgrade_helper_14();
    }

    public function upgrade_helper_14()
    {
        global $wpdb;
        $action_history_table = OW_Utility::instance()->get_action_history_table_name();

        // phpcs:ignore
        $wpdb->update(
            $action_history_table,
            array(
                'action_status' => 'abort_no_action_1'
            ),
            array(
                'action_status' => 'aborted'
            )
        );

        // phpcs:ignore
        $wpdb->update(
            $action_history_table,
            array(
                'action_status' => 'aborted'
            ),
            array(
                'action_status' => 'abort_no_action'
            )
        );

        // phpcs:ignore
        $wpdb->update(
            $action_history_table,
            array(
                'action_status' => 'abort_no_action'
            ),
            array(
                'action_status' => 'abort_no_action_1'
            )
        );
    }

    public function upgrade_database_15()
    {
        $oasiswf_custom_workflow_terminology = array(
            'submitToWorkflowText' => esc_html__('Submit to Workflow', 'oasisworkflow'),
            'signOffText' => esc_html__('Sign Off', 'oasisworkflow'),
            'assignActorsText' => esc_html__('Assign Actor(s)', 'oasisworkflow'),
            'dueDateText' => esc_html__('Due Date', 'oasisworkflow'),
            'publishDateText' => esc_html__('Publish Date', 'oasisworkflow'),
            'abortWorkflowText' => esc_html__('Abort Workflow', 'oasisworkflow'),
            'workflowHistoryText' => esc_html__('Workflow History')
        );
        update_option('oasiswf_custom_workflow_terminology', $oasiswf_custom_workflow_terminology);
    }

    public function upgrade_database_16()
    {
        global $wpdb;

        // look through each of the blogs and upgrade the DB
        if (function_exists('is_multisite') && is_multisite()) {
            // Get all blog ids; foreach them and call the uninstall procedure on each of them
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");  // phpcs:ignore

            // Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                // phpcs:ignore
                if ($wpdb->query('SHOW TABLES FROM ' . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'")) {
                    $this->upgrade_helper_16();
                }
                restore_current_blog();
            }

            // delete the site_options since we have added the same to respective site
            if (get_site_option('oasiswf_activate_workflow')) {
                delete_site_option('oasiswf_activate_workflow');
            }

            if (get_site_option('oasiswf_default_due_days')) {
                delete_site_option('oasiswf_default_due_days');
            }

            if (get_site_option('oasiswf_reminder_days')) {
                delete_site_option('oasiswf_reminder_days');
            }

            if (get_site_option('oasiswf_skip_workflow_roles')) {
                delete_site_option('oasiswf_skip_workflow_roles');
            }

            if (get_site_option('oasiswf_reminder_days_after')) {
                delete_site_option('oasiswf_reminder_days_after');
            }

            if (get_site_option('oasiswf_show_wfsettings_on_post_types')) {
                delete_site_option('oasiswf_show_wfsettings_on_post_types');
            }

            if (get_site_option('oasiswf_email_settings')) {
                delete_site_option('oasiswf_email_settings');
            }

            if (get_site_option('oasiswf_hide_workflow_graphic')) {
                delete_site_option('oasiswf_hide_workflow_graphic');
            }

            if (get_site_option('oasiswf_custom_workflow_terminology')) {
                delete_site_option('oasiswf_custom_workflow_terminology');
            }
            return;
        }
    }

    public function upgrade_helper_16()
    {
        global $wpdb;

        // add the wp_options to respective sites
        if (get_site_option('oasiswf_activate_workflow')) {
            update_option('oasiswf_activate_workflow', get_site_option('oasiswf_activate_workflow'));
        }

        if (get_site_option('oasiswf_default_due_days')) {
            update_option('oasiswf_default_due_days', get_site_option('oasiswf_default_due_days'));
        }

        if (get_site_option('oasiswf_reminder_days')) {
            update_option('oasiswf_reminder_days', get_site_option('oasiswf_reminder_days'));
        }

        if (get_site_option('oasiswf_skip_workflow_roles')) {
            update_option('oasiswf_skip_workflow_roles', get_site_option('oasiswf_skip_workflow_roles'));
        }

        if (get_site_option('oasiswf_reminder_days_after')) {
            update_option('oasiswf_reminder_days_after', get_site_option('oasiswf_reminder_days_after'));
        }

        if (get_site_option('oasiswf_show_wfsettings_on_post_types')) {
            update_option('oasiswf_show_wfsettings_on_post_types', get_site_option('oasiswf_show_wfsettings_on_post_types'));
        }

        if (get_site_option('oasiswf_email_settings')) {
            update_option('oasiswf_email_settings', get_site_option('oasiswf_email_settings'));
        }

        if (get_site_option('oasiswf_hide_workflow_graphic')) {
            update_option('oasiswf_hide_workflow_graphic', get_site_option('oasiswf_hide_workflow_graphic'));
        }

        if (get_site_option('oasiswf_custom_workflow_terminology')) {
            update_option('oasiswf_custom_workflow_terminology', get_site_option('oasiswf_custom_workflow_terminology'));
        }

        // create tables and data only if the workflow tables do not exist
        // phpcs:ignore
        if ($wpdb->query('SHOW TABLES FROM ' . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_workflow%'")) {
            return;
        }

        // first create the tables and put the default data
        $this->install_admin_database();

        // delete the default data
        // fc_workflow_steps table
        $wpdb->query('DELETE FROM ' . OW_Utility::instance()->get_workflow_steps_table_name());  // phpcs:ignore

        // fc_workflows table
        $wpdb->get_results('DELETE FROM ' . OW_Utility::instance()->get_workflows_table_name());  // phpcs:ignore

        // now insert data from the original/main table into these new tables
        $sql = 'INSERT INTO ' . OW_Utility::instance()->get_workflows_table_name() . ' SELECT * FROM ' . $wpdb->base_prefix . 'fc_workflows';
        $wpdb->get_results($sql);  // phpcs:ignore

        $sql = 'INSERT INTO ' . OW_Utility::instance()->get_workflow_steps_table_name() . ' SELECT * FROM ' . $wpdb->base_prefix . 'fc_workflow_steps';
        $wpdb->get_results($sql);  // phpcs:ignore
    }

    public function upgrade_database_17()
    {
        // update the dismissed pointer/message for existing plugin users.
        $blog_users = get_users('role=administrator');
        foreach ($blog_users as $user) {
            $dismissed = (string) get_user_meta($user->ID, 'dismissed_wp_pointers', true);
            $dismissed = $dismissed . ',' . 'owf_install_free';
            update_user_meta($user->ID, 'dismissed_wp_pointers', $dismissed);
        }
    }

    /**
     * Upgrade function for upgrading to v1.9
     * Calls upgrade_helper_19()
     *
     * @since 3.5
     */
    public function upgrade_database_19()
    {
        global $wpdb;

        // look through each of the blogs and upgrade the DB
        if (function_exists('is_multisite') && is_multisite()) {
            // Get all blog ids; foreach them and call the uninstall procedure on each of them
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");  // phpcs:ignore

            // Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                // phpcs:ignore
                if ($wpdb->query('SHOW TABLES FROM ' . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'")) {
                    $this->upgrade_helper_19();
                }
                restore_current_blog();
            }
        }

        $this->upgrade_helper_19();
    }

    /**
     * Upgrade Helper for v1.9
     *
     * Add new capabilities to author role
     *
     * @since 1.9
     */
    public function upgrade_helper_19()
    {
        global $wp_roles;

        if (class_exists('WP_Roles')) {
            if (!isset($wp_roles)) {
                $wp_roles = new WP_Roles();  // phpcs:ignore
            }
        }

        $wp_roles->add_cap('author', 'edit_others_posts');
        $wp_roles->add_cap('author', 'edit_others_pages');
    }

    private function upgrade_database_20()
    {
        global $wpdb;

        // look through each of the blogs and upgrade the DB
        if (function_exists('is_multisite') && is_multisite()) {
            // Get all blog ids; foreach them and call the uninstall procedure on each of them
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");  // phpcs:ignore

            // Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                // phpcs:ignore
                if ($wpdb->query('SHOW TABLES FROM ' . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'")) {
                    $this->upgrade_helper_20();
                }
                restore_current_blog();
            }
        }

        $this->upgrade_helper_20();
    }

    private function upgrade_database_22()
    {
        global $wpdb;

        $oasiswf_placeholders = array(
            '%first_name%' => esc_html__('first name', 'oasisworkflow'),
            '%last_name%' => esc_html__('last name', 'oasisworkflow'),
            '%post_title%' => esc_html__('post title', 'oasisworkflow'),
            '%category%' => esc_html__('category', 'oasisworkflow'),
            '%last_modified_date%' => esc_html__('last modified date', 'oasisworkflow'),
            '%post_author%' => esc_html__('post author', 'oasisworkflow')
        );

        update_site_option('oasiswf_placeholders', $oasiswf_placeholders);

        // look through each of the blogs and upgrade the DB
        if (function_exists('is_multisite') && is_multisite()) {
            // Get all blog ids; foreach them and call the uninstall procedure on each of them
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");  // phpcs:ignore

            // Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                // phpcs:ignore
                if ($wpdb->query('SHOW TABLES FROM ' . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'")) {
                    $this->upgrade_helper_22_for_workflow_info();
                    $this->upgrade_helper_22_for_settings();
                    $this->upgrade_helper_22_for_task_assignee();
                    $this->upgrade_helper_22_for_step_info();
                }
                restore_current_blog();
            }
        }

        $this->upgrade_helper_22_for_workflow_info();
        $this->upgrade_helper_22_for_settings();
        $this->upgrade_helper_22_for_task_assignee();
        $this->upgrade_helper_22_for_step_info();
    }

    private function upgrade_helper_22_for_settings()
    {
        global $wpdb;
        $email_settings = get_option('oasiswf_email_settings');

        $new_email_settings = array(
            'from_name' => $email_settings['from_name'],
            'from_email_address' => $email_settings['from_email_address'],
            'assignment_emails' => $email_settings['assignment_emails'],
            'reminder_emails' => $email_settings['reminder_emails'],
            'post_publish_emails' => $email_settings['post_publish_emails'],
            'unauthorized_post_update_emails' => 'yes',
            'abort_email_to_author' => 'yes',
            'submit_to_workflow_email' => 'no'
        );

        update_option('oasiswf_email_settings', $new_email_settings);

        // Priority Setting - initial setting is enabled
        $priority_setting = 'enable_priority';

        if (!get_option('oasiswf_priority_setting')) {
            update_option('oasiswf_priority_setting', $priority_setting);
        }
    }

    /**
     * Upgrade helper for v2.2 upgrade function
     *
     * Replace current "assignee" with "task_assignee"
     *
     * @since 2.2
     */
    private function upgrade_helper_22_for_task_assignee()
    {
        global $wpdb;

        /**
         * 1. Get all the steps
         * 2. in loop, compare key and replace key "assignee" with "task_assignee"
         * 3. Since we were using only roles, all the current assignees will be roles.
         */
        $steps_table = OW_Utility::instance()->get_workflow_steps_table_name();
        // phpcs:ignore
        $step_infos = $wpdb->get_results("SELECT step_info, ID FROM $steps_table");
        if ($step_infos) {
            foreach ($step_infos as $step_info) {
                $step_id = $step_info->ID;
                $step_info = json_decode($step_info->step_info);
                foreach ($step_info as $key => $info) {
                    if ($key == 'assignee') {
                        $key = 'task_assignee';
                        $step_info->$key = new stdClass();
                        $roles = array();
                        foreach ($info as $role_slug => $role_name) {
                            $roles[] = $role_slug;
                        }
                        $step_info->$key->roles = $roles;
                        unset($step_info->assignee);
                        // phpcs:ignore
                        $wpdb->query($wpdb->prepare("UPDATE $steps_table SET step_info = %s WHERE ID = %d", json_encode($step_info), $step_id));
                    }
                }
            }
        }
    }

    private function upgrade_helper_22_for_step_info()
    {
        global $wpdb;

        /**
         * 1. Get all workflows
         * 2. update the first step's post status as draft
         * 3. migrate the post status from step info to connection info
         * 4. add review settings to step info column
         */
        $workflows_table = OW_Utility::instance()->get_workflows_table_name();
        $workflow_steps_table = OW_Utility::instance()->get_workflow_steps_table_name();
        $workflows = $wpdb->get_results("SELECT * FROM $workflows_table");  // phpcs:ignore
        if ($workflows) {
            foreach ($workflows as $workflow) {
                // get the workflow_id
                $workflow_id = $workflow->ID;

                // get wf_info from the workflow
                $wf_info = json_decode($workflow->wf_info);

                /**
                 * Set first step post status - we will set "Draft" as a first step post status
                 * @since 4.0
                 */
                $first_step = $wf_info->first_step[0];
                if ($first_step && count($wf_info->first_step) == 1) {
                    $first_step_updated_info = array(
                        'step' => $first_step,
                        'post_status' => 'draft'
                    );
                    $wf_info->first_step[0] = $first_step_updated_info;
                }

                /**
                 * loop through all the steps in wf_info and set it into stepInfo array for later user
                 *    $stepInfo[step0] = array(
                 *       'step_name' => 'review',
                 *       'step'      => 'step0'
                 *    );
                 */
                $steps = $wf_info->steps;
                $stepInfo = array();
                foreach ($steps as $step) {
                    $stepInfo[$step->fc_addid] = (object) array(
                        'step_name' => $step->fc_label,
                        'step' => $step->fc_addid
                    );
                }

                /**
                 * Migrate the post status from step info to connection info
                 * The "on success" of source step goes to the success connection
                 * The "on failure" of the source step goes to the failure connection
                 */
                $connections = $wf_info->conns;
                foreach ($connections as $connection) {
                    $source = $connection->sourceId;
                    $stroke_style = $connection->connset->paintStyle->strokeStyle;

                    $step_name = $wpdb->esc_like('"step_name":"' . $stepInfo[$source]->step_name . '"');
                    // Add wildcards, since we are searching within step_info
                    $step_name = '%' . $step_name . '%';

                    $sql = "SELECT * FROM $workflow_steps_table WHERE workflow_id = %d AND step_info LIKE %s LIMIT 0, 1";
                    $results = $wpdb->get_row($wpdb->prepare($sql, $workflow_id, $step_name));  // phpcs:ignore

                    if ($results) {
                        $step_info = json_decode($results->step_info);
                        $success = $step_info->status;
                        $failure = $step_info->failure_status;
                        switch ($stroke_style) {
                            case 'blue':
                                $post_status = $success;
                                break;
                            case 'red':
                                $post_status = $failure;
                                break;
                            default:
                                $post_status = 'draft';
                                break;
                        }
                        $connection->post_status = $post_status;
                    }
                }

                $wf_info = wp_unslash(wp_json_encode($wf_info));

                // Finally update the workflow info for given workflow id
                // phpcs:ignore
                $wpdb->update($workflows_table, array(
                    'wf_info' => $wf_info
                ), array(
                    'ID' => $workflow->ID
                ));
            }

            // now lets unset the status from step info
            $sql = "SELECT ID, step_info FROM $workflow_steps_table";
            $steps = $wpdb->get_results($sql);  // phpcs:ignore
            foreach ($steps as $step) {
                $step_info = json_decode($step->step_info);
                unset($step_info->status, $step_info->failure_status);
                $step->step_info = wp_json_encode($step_info);
                // phpcs:ignore
                $wpdb->update($workflow_steps_table, array(
                    'step_info' => $step->step_info
                ), array(
                    'ID' => $step->ID
                ));
            }
        }
    }

    private function upgrade_helper_22_for_workflow_info()
    {
        global $wpdb;

        $workflows_table = OW_Utility::instance()->get_workflows_table_name();
        $workflows = $wpdb->get_results("SELECT * FROM $workflows_table");  // phpcs:ignore
        $additional_info = stripcslashes('a:4:{s:16:"wf_for_new_posts";i:1;s:20:"wf_for_revised_posts";i:1;s:12:"wf_for_roles";a:0:{}s:17:"wf_for_post_types";a:0:{}}');

        if ($workflows) {
            foreach ($workflows as $workflow) {
                $wpdb->update($workflows_table, array(
                    'wf_additional_info' => $additional_info
                ), array(
                    'ID' => $workflow->ID
                ));
            }
        }
    }

    /**
     * Upgrade Helper for v2.0
     * Remove options and replace it with custom capabilities
     *
     * @since 2.0
     */
    private function upgrade_helper_20()
    {
        global $wp_roles;

        if (class_exists('WP_Roles')) {
            if (!isset($wp_roles)) {
                $wp_roles = new WP_Roles();  // phpcs:ignore
            }
        }

        // remove options and replace it with custom capabilities
        if (get_site_option('oasiswf_skip_workflow_roles')) {
            $this->switch_from_option_to_capability('oasiswf_skip_workflow_roles', 'ow_skip_workflow', $wp_roles);
            delete_option('oasiswf_skip_workflow_roles');
        }

        if (get_site_option('oasiswf_view_other_users_inbox_roles')) {
            $this->switch_from_option_to_capability('oasiswf_view_other_users_inbox_roles',
                'ow_view_others_inbox', $wp_roles);
            delete_option('oasiswf_view_other_users_inbox_roles');
        }

        if (get_site_option('oasiswf_abort_workflow_roles')) {
            $this->switch_from_option_to_capability('oasiswf_abort_workflow_roles',
                'ow_abort_workflow', $wp_roles);
            delete_option('oasiswf_abort_workflow_roles');
        }

        // other admin capabilities
        $wp_roles->add_cap('administrator', 'ow_edit_workflow');
        $wp_roles->add_cap('administrator', 'ow_delete_workflow');
        $wp_roles->add_cap('administrator', 'ow_view_reports');
        $wp_roles->add_cap('administrator', 'ow_view_workflow_history');
        $wp_roles->add_cap('administrator', 'ow_delete_workflow_history');
        $wp_roles->add_cap('administrator', 'ow_view_others_inbox');
        $wp_roles->add_cap('administrator', 'ow_abort_workflow');
        $wp_roles->add_cap('administrator', 'ow_reassign_task');

        // add ow_submit_to_workflow and ow_sign_off_step to all the existing roles.
        if (!function_exists('get_editable_roles')) {
            require_once (ABSPATH . '/wp-admin/includes/user.php');
        }
        $editable_roles = get_editable_roles();
        foreach ($editable_roles as $role => $details) {
            if ('subscriber' == esc_attr($role)) {  // no need to give this capabilities to subscriber
                continue;
            }
            $wp_roles->add_cap(esc_attr($role), 'ow_submit_to_workflow');
            $wp_roles->add_cap(esc_attr($role), 'ow_sign_off_step');
        }

        /*
         * Add out of the box custom statuses as part of upgrade
         */
        if (!class_exists('OW_Custom_Statuses')) {
            include (OASISWF_PATH . 'includes/class-ow-custom-statuses.php');
        }
        $ow_custom_statuses = new OW_Custom_Statuses();
        $ow_custom_statuses->register_custom_taxonomy();

        $custom_statuses = array(
            'Pitch' => 'New idea proposed.',
            'With Author' => 'An author has been assigned to the post.',
            'Ready to Publish' => 'The post is ready for publication.'
        );

        foreach ($custom_statuses as $custom_status => $desc) {
            // phpcs:ignore
            if (term_exists($custom_status)) {
                continue;
            }

            $args = array(
                'slug' => sanitize_title($custom_status),
                'description' => $desc
            );

            wp_insert_term($custom_status, 'post_status', $args);
        }
    }

    private function upgrade_database_23()
    {
        global $wpdb;

        // look through each of the blogs and upgrade the DB
        if (function_exists('is_multisite') && is_multisite()) {
            // Get all blog ids; foreach them and call the uninstall procedure on each of them
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");  // phpcs:ignore

            // Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                // phpcs:ignore
                if ($wpdb->query('SHOW TABLES FROM ' . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'")) {
                    $this->upgrade_helper_23();
                }
                restore_current_blog();
            }
        }

        $this->upgrade_helper_23();
    }

    private function upgrade_helper_23()
    {
        global $wpdb;

        // update custom postmeta values
        // adding underscore will hide the custom post meta from the UI
        $meta_keys = array(
            'oasis_original' => '_oasis_original',
            'oasis_is_in_workflow' => '_oasis_is_in_workflow',
            'ow_task_priority' => '_oasis_task_priority',
            'oasis_current_revision' => '_oasis_current_revision'
        );

        foreach ($meta_keys as $old_meta_key => $new_meta_key) {
            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s", $new_meta_key, $old_meta_key));  // phpcs:ignore
        }

        // get current review settings
        $review_setting = get_option('oasiswf_review_process_setting');

        // add review_approval settings on existing review processes
        $workflow_steps_table = OW_Utility::instance()->get_workflow_steps_table_name();
        $sql = "SELECT ID, step_info FROM $workflow_steps_table";
        $steps = $wpdb->get_results($sql);  // phpcs:ignore
        foreach ($steps as $step) {
            $step_info = json_decode($step->step_info);
            if ($step_info->process === 'review') {
                // add review approval and set it's value to
                $step_info->review_approval = $review_setting;
                $step->step_info = wp_json_encode($step_info);
                // phpcs:ignore
                $wpdb->update($workflow_steps_table, array(
                    'step_info' => $step->step_info
                ), array(
                    'ID' => $step->ID
                ));
            }
        }

        // now delete the option, since we do not need it anymore
        delete_option('oasiswf_review_process_setting');

        // add new option for review rating
        if (!get_option('oasiswf_review_notice')) {
            update_option('oasiswf_review_notice', 'no');
        }

        if (!get_option('oasiswf_review_rating_interval')) {
            update_option('oasiswf_review_rating_interval', '');
        }

        // Publish date Setting
        if (!get_option('oasiswf_publish_date_setting')) {
            update_option('oasiswf_publish_date_setting', '');
        }

        // Count the post/pages processed and published via workflow for review rating
        $table_name = OW_Utility::instance()->get_action_history_table_name();
        // phpcs:ignore
        $workflow_completed_post = $wpdb->get_var("SELECT COUNT(ID) FROM {$table_name} WHERE action_status = 'complete'");
        if (!get_option('oasiswf_workflow_completed_post_count')) {
            update_option('oasiswf_workflow_completed_post_count', $workflow_completed_post);
        }
    }

    private function upgrade_database_29()
    {
        global $wpdb;

        // look through each of the blogs and upgrade the DB
        if (function_exists('is_multisite') && is_multisite()) {
            // Get all blog ids; foreach them and call the uninstall procedure on each of them
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");  // phpcs:ignore

            // Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                // phpcs:ignore
                if ($wpdb->query('SHOW TABLES FROM ' . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'")) {
                    $this->upgrade_helper_29();
                }
                restore_current_blog();
            }
        }

        $this->upgrade_helper_29();
    }

    private function upgrade_helper_29()
    {
        global $wpdb;

        // fc_action_history table  - add history_meta field
        $table_name = OW_Utility::instance()->get_action_history_table_name();
        $wpdb->query("ALTER TABLE {$table_name} ADD history_meta longtext");  // phpcs:ignore

        // fc_action table  - add history_meta field
        $table_name = OW_Utility::instance()->get_action_table_name();
        $wpdb->query("ALTER TABLE {$table_name} ADD history_meta longtext");  // phpcs:ignore
    }

    /**
     * Upgrade helper for v5.7 upgrade function
     * @since 5.7
     */
    private function upgrade_database_33()
    {
        global $wpdb;

        // look through each of the blogs and upgrade the DB
        if (function_exists('is_multisite') && is_multisite()) {
            // Get all blog ids; foreach them and call the uninstall procedure on each of them
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");  // phpcs:ignore

            // Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                // phpcs:ignore
                if ($wpdb->query('SHOW TABLES FROM ' . $wpdb->dbname . " LIKE '" . $wpdb->prefix . "fc_%'")) {
                    $this->upgrade_helper_33();
                }
                restore_current_blog();
            }
        }

        $this->upgrade_helper_33();
    }

    private function upgrade_helper_33()
    {
        global $wpdb;
        $action_history_table_name = OW_Utility::instance()->get_action_history_table_name();
        // phpcs:ignore
        $wpdb->query("ALTER TABLE {$action_history_table_name} MODIFY ID bigint(20) NOT NULL AUTO_INCREMENT, MODIFY post_id bigint(20), MODIFY from_id bigint(20)");

        $action_table_name = OW_Utility::instance()->get_action_table_name();
        // phpcs:ignore
        $wpdb->query("ALTER TABLE {$action_table_name} MODIFY ID bigint(20) NOT NULL AUTO_INCREMENT, MODIFY action_history_id bigint(20)");

        $email_table_name = OW_Utility::instance()->get_emails_table_name();
        // phpcs:ignore
        $wpdb->query("ALTER TABLE {$email_table_name} MODIFY history_id bigint(20)");
    }

    public function load_css_and_js_files()
    {
        add_action('admin_print_styles', array($this, 'add_css_files'));
        add_action('admin_print_scripts', array($this, 'add_js_files'));
        add_action('admin_footer', array($this, 'load_js_files_footer'));
    }

    /**
     * Retrieves pointers for the current admin screen. Use the 'owf_admin_pointers' hook to add your own pointers.
     *
     * @return array Current screen pointers
     * @since 2.0
     */
    private function get_current_screen_pointers()
    {
        $pointers = '';

        $screen = get_current_screen();
        $screen_id = $screen->id;

        // Format : array( 'screen_id' => array( 'pointer_id' => array([options : target, content, position...]) ) );

        $welcome_title = esc_html__('Welcome to Oasis Workflow', 'oasisworkflow');
        $img_html = "<img src='" . OASISWF_URL . 'img/small-arrow.gif' . "' style='border:0px;' />";
        $welcome_message_1 = esc_html__('To get started with Oasis Workflow follow the steps listed below.', 'oasisworkflow');
        $welcome_message_1_multisite = esc_html__('To get started with Oasis Workflow go to the individual site and follow the steps listed below.', 'oasisworkflow');
        $welcome_message_2 = sprintf(esc_html__('1. Go to Workflows %s All Workflows.', 'oasisworkflow'), $img_html);
        $welcome_message_3 = esc_html__('2. Create a new workflow OR modify/use the sample workflows that come with the plugin.', 'oasisworkflow');
        $welcome_message_4 = sprintf(esc_html__('3. Activate the workflow process from Workflows %s Settings, Workflow tab.', 'oasisworkflow'), $img_html);
        if (function_exists('is_multisite') && is_multisite()) {
            $default_pointers = array(
                'toplevel_page_oasiswf-inbox' => array(
                    'owf_install_free' => array(
                        'target' => '#toplevel_page_oasiswf-inbox',
                        'content' => '<h3>' . $welcome_title . '</h3> <p>'
                            . $welcome_message_1_multisite . '</p><p>'
                            . $welcome_message_2 . '</p><p>'
                            . $welcome_message_3 . '</p><p>'
                            . $welcome_message_4 . '</p>',
                        'position' => array('edge' => 'left', 'align' => 'center'),
                    )
                )
            );
        } else {
            $default_pointers = array(
                'toplevel_page_oasiswf-inbox' => array(
                    'owf_install_free' => array(
                        'target' => '#toplevel_page_oasiswf-inbox',
                        'content' => '<h3>' . $welcome_title . '</h3> <p>'
                            . $welcome_message_1 . '</p><p>'
                            . $welcome_message_2 . '</p><p>'
                            . $welcome_message_3 . '</p><p>'
                            . $welcome_message_4 . '</p>',
                        'position' => array('edge' => 'left', 'align' => 'center'),
                    )
                )
            );
        }

        if (!empty($default_pointers[$screen_id]))
            $pointers = $default_pointers[$screen_id];

        return apply_filters('owf_admin_pointers', $pointers, $screen_id);
    }

    /**
     * Show the welcome message on plugin activation.
     *
     * @since 2.0
     */
    public function show_welcome_message_pointers()
    {
        // Don't run on WP < 3.3
        if (get_bloginfo('version') < '3.3') {
            return;
        }

        // only show this message to the users who can activate plugins
        if (!current_user_can('activate_plugins')) {
            return;
        }

        $pointers = $this->get_current_screen_pointers();

        // No pointers? Don't do anything
        if (empty($pointers) || !is_array($pointers))
            return;

        // Get dismissed pointers.
        // Note : dismissed pointers are stored by WP in the "dismissed_wp_pointers" user meta.

        $dismissed = explode(',', (string) get_user_meta(get_current_user_id(), 'dismissed_wp_pointers', true));
        $valid_pointers = array();

        // Check pointers and remove dismissed ones.
        foreach ($pointers as $pointer_id => $pointer) {
            // Sanity check
            if (in_array($pointer_id, $dismissed) || empty($pointer) || empty($pointer_id) || empty($pointer['target']) || empty($pointer['content']))
                continue;

            // Add the pointer to $valid_pointers array
            $valid_pointers[$pointer_id] = $pointer;
        }

        // No valid pointers? Stop here.
        if (empty($valid_pointers))
            return;

        // Set our class variable $current_screen_pointers
        $this->current_screen_pointers = $valid_pointers;

        // Add our javascript to handle pointers
        add_action('admin_print_footer_scripts', array($this, 'display_pointers'));

        // Add pointers style and javascript to queue.
        wp_enqueue_style('wp-pointer');
        wp_enqueue_script('wp-pointer');
    }

    /**
     * Finally prints the javascript that'll make our pointers alive.
     *
     * @since 2.0
     */
    public function display_pointers()
    {
        // phpcs:disable
        if (!empty($this->current_screen_pointers)):
?>
          <script type="text/javascript">// <![CDATA[
              jQuery(document).ready(function ($) {
                  if (typeof (jQuery().pointer) != 'undefined') {
                     <?php foreach ($this->current_screen_pointers as $pointer_id => $data): ?>
                      $('<?php echo $data['target'] ?>').pointer({
                          content: '<?php echo addslashes($data['content']) ?>',
                          position: {
                              edge: '<?php echo addslashes($data['position']['edge']) ?>',
                              align: '<?php echo addslashes($data['position']['align']) ?>'
                          },
                          close: function () {
                              $.post(ajaxurl, {
                                  pointer: '<?php echo addslashes($pointer_id) ?>',
                                  action: 'dismiss-wp-pointer'
                              });
                          }
                      }).pointer('open');
                     <?php endforeach ?>
                  }
              });
              // ]]></script>
      <?php
            // phpcs:enable
        endif;
    }

    /**
     * Workflows List Page action.
     * This method is called when the menu item "All Workflows" is clicked.
     *
     * @since 2.0
     */
    public function list_workflows_page_content()
    {
        // phpcs:ignore
        $workflow_id = isset($_GET['wf_id']) ? intval(sanitize_text_field($_GET['wf_id'])) : '';
        if (!empty($workflow_id)) {
            include (OASISWF_PATH . 'includes/pages/workflow-create.php');
        } else {
            include (OASISWF_PATH . 'includes/pages/workflow-list.php');
        }
    }

    /**
     * New Workflow create action.
     * This method is called when the menu item "Add New Workflow" is clicked.
     *
     * @since 4.5
     */
    public function create_workflow_page_content()
    {
        include (OASISWF_PATH . 'includes/pages/workflow-create.php');
    }

    /**
     * Inbox page action.
     * This method is called when the menu item "Inbox" is clicked.
     *
     * @since 2.0
     */
    public function workflow_inbox_page_content()
    {
        include (OASISWF_PATH . 'includes/pages/workflow-inbox.php');
    }

    /**
     * Workflow History page action.
     * This method is called when the menu item "History" is clicked
     *
     * @since 2.0
     */
    public function workflow_history_page_content()
    {
        include_once (OASISWF_PATH . 'includes/pages/workflow-history.php');
        include_once (OASISWF_PATH . 'includes/pages/subpages/delete-history.php');
    }

    /**
     * Reports page.
     * This method is called when "Reports" is clicked.
     */
    public function workflow_reports_page_content()
    {
        include_once (OASISWF_PATH . 'includes/pages/workflow-reports.php');
    }

    /**
     * Load all the classes - as part of init action hook
     *
     * @since 2.0
     */
    public function load_all_classes()
    {
        /** include model classes */
        include_once (OASISWF_PATH . 'includes/models/class-ow-workflow-step.php');
        include_once (OASISWF_PATH . 'includes/models/class-ow-workflow.php');
        include_once (OASISWF_PATH . 'includes/models/class-ow-action-history.php');
        include_once (OASISWF_PATH . 'includes/models/class-ow-review-history.php');

        /** include service classes */
        include_once (OASISWF_PATH . 'includes/class-ow-email.php');
        include_once (OASISWF_PATH . 'includes/class-ow-placeholders.php');
        include_once (OASISWF_PATH . 'includes/class-ow-workflow-validator.php');
        include_once (OASISWF_PATH . 'includes/class-ow-workflow-service.php');
        include_once (OASISWF_PATH . 'includes/class-ow-process-flow.php');
        include_once (OASISWF_PATH . 'includes/class-ow-history-service.php');
        include_once (OASISWF_PATH . 'includes/class-ow-inbox-service.php');
        include_once (OASISWF_PATH . 'includes/class-ow-review-rating.php');
        include_once (OASISWF_PATH . 'includes/class-ow-report-service.php');

        /** Settings classes */
        include_once (OASISWF_PATH . 'includes/class-ow-settings-base.php');
        include_once (OASISWF_PATH . 'includes/class-ow-email-settings.php');
        include_once (OASISWF_PATH . 'includes/class-ow-workflow-settings.php');
        include_once (OASISWF_PATH . 'includes/class-ow-workflow-terminology-settings.php');

        if (!class_exists('OW_Tools_Service')) {
            include (OASISWF_PATH . 'includes/class-ow-tools-service.php');
        }

        if (!class_exists('OW_Custom_Capabilities')) {
            include (OASISWF_PATH . 'includes/class-ow-custom-capabilities.php');
        }

        // Include feedback class
        if (!class_exists('OW_Feedback')) {
            include (OASISWF_PATH . 'includes/class-ow-deactivate-feedback.php');
        }

        /** Include API classes */
        include (OASISWF_PATH . 'includes/api/api-usercap.php');
        include (OASISWF_PATH . 'includes/api/api-settings.php');
        include (OASISWF_PATH . 'includes/api/api-workflow.php');
        include (OASISWF_PATH . 'includes/api/api-utility.php');
    }

    /**
     * register post meta, so that it's recognized on the front end/gutenberg
     */
    public function register_custom_post_meta()
    {
        register_meta('post', '_oasis_is_in_workflow', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'integer',
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            }
        ));

        register_meta('post', '_oasis_original', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'integer',
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            }
        ));
    }

    /**
     * Redirect user to oasis workflow subscribe page upon plugin activation.
     *
     * @since 2.6
     */
    public function newsletter_signup_redirect()
    {
        if (!get_transient('owf_activation_redirect')) {
            return;
        }

        delete_transient('owf_activation_redirect');

        // Bail if activating from network, or bulk.
        if (is_network_admin()) {
            return;
        }

        // Bail if upgrading
        if (!$this->oasiswf_new_install()) {
            return;
        }

        // Redirect to oasis workflow subscribe page
        wp_safe_redirect('admin.php?page=oasiswf-stay-informed');
        exit();
    }

    /**
     * Check is it a new install of the plugin
     * @since 2.6
     */
    public function oasiswf_new_install()
    {
        $new_or_not = true;
        $saved = get_option('oasiswf_info');

        if ('false' === $saved) {
            $new_or_not = false;
        }
        return (bool) $new_or_not;
    }

    /**
     * Put the "Workflows" main menu after the "Comments" menu
     *
     * @since 2.0
     */
    private function get_menu_position($decimal_loc)
    {
        global $menu;
        $sp = 0;
        $ep = 0;
        foreach ($menu as $k => $v) {
            if ($v[2] == 'themes.php')
                $ep = $k;
            if ($v[2] == 'edit-comments.php')
                $sp = $k;
            $menu_position[] = $k;
        }
        for ($i = $ep; $i > $sp; $i--) {
            if (!in_array($i, $menu_position)) {
                $y = $i . $decimal_loc;
                return $y;
            }
        }
    }

    /**
     * Run on deactivation
     *
     * Removes the custom capabilities added by the plugin
     *
     * @since 2.0
     */
    private function run_on_deactivation()
    {
        /*
         * Include the custom capability class
         */
        if (!class_exists('OW_Custom_Capabilities')) {
            include (OASISWF_PATH . 'includes/class-ow-custom-capabilities.php');
        }

        $ow_custom_capabilities = new OW_Custom_Capabilities();
        $ow_custom_capabilities->remove_capabilities();
    }

    /**
     * Called on activation.
     * Creates the site_options (required for all the sites in a multi-site setup)
     * If the current version doesn't match the new version, runs the upgrade
     *
     * @since 2.0
     */
    private function run_on_activation()
    {
        $plugin_options = get_site_option('oasiswf_info');
        if (false === $plugin_options) {
            $oasiswf_info = array(
                'version' => OASISWF_VERSION,
                'db_version' => OASISWF_DB_VERSION
            );

            $oasiswf_process_info = array(
                'assignment' => OASISWF_URL . 'img/assignment.gif',
                'review' => OASISWF_URL . 'img/review.gif',
                'publish' => OASISWF_URL . 'img/publish.gif'
            );

            $oasiswf_path_info = array(
                'success' => array(__('Success', 'oasisworkflow'), 'blue'),
                'failure' => array(__('Failure', 'oasisworkflow'), 'red')
            );

            $oasiswf_status = array(
                'assignment' => __('In Progress', 'oasisworkflow'),
                'review' => __('In Review', 'oasisworkflow'),
                'publish' => __('Ready to Publish', 'oasisworkflow')
            );

            $oasiswf_placeholders = array(
                '%first_name%' => __('first name', 'oasisworkflow'),
                '%last_name%' => __('last name', 'oasisworkflow'),
                '%post_title%' => __('post title', 'oasisworkflow'),
                '%category%' => __('category', 'oasisworkflow'),
                '%last_modified_date%' => __('last modified date', 'oasisworkflow')
            );

            update_site_option('oasiswf_info', $oasiswf_info);
            update_site_option('oasiswf_process', $oasiswf_process_info);
            update_site_option('oasiswf_path', $oasiswf_path_info);
            update_site_option('oasiswf_status', $oasiswf_status);
            update_site_option('oasiswf_placeholders', $oasiswf_placeholders);

            // Add the transient to redirect.
            set_transient('owf_activation_redirect', true, 30);
        } else if (OASISWF_VERSION != $plugin_options['version']) {
            $this->run_on_upgrade();
        }

        if (!wp_next_scheduled('oasiswf_email_schedule'))
            wp_schedule_event(time(), 'daily', 'oasiswf_email_schedule');
    }

    /**
     * Called on activation.
     * Creates the options and DB (required by per site)
     *
     * @since 2.0
     */
    private function run_for_site()
    {
        /*
         * Include the custom capability class
         */
        if (!class_exists('OW_Custom_Capabilities')) {
            include (OASISWF_PATH . 'includes/class-ow-custom-capabilities.php');
        }
        $ow_custom_capabilities = new OW_Custom_Capabilities();
        $ow_custom_capabilities->add_capabilities();

        /*
         * Add out of the box custom statuses
         */
        if (!class_exists('OW_Custom_Statuses')) {
            include (OASISWF_PATH . 'includes/class-ow-custom-statuses.php');
        }
        $ow_custom_statuses = new OW_Custom_Statuses();
        $ow_custom_statuses->register_custom_taxonomy();

        $custom_statuses = array(
            'Pitch' => 'New idea proposed.',
            'With Author' => 'An author has been assigned to the post.',
            'Ready to Publish' => 'The post is ready for publication.'
        );

        foreach ($custom_statuses as $custom_status => $desc) {
            // phpcs:ignore
            if (term_exists($custom_status)) {
                continue;
            }

            $args = array(
                'description' => $desc,
                'slug' => sanitize_title($custom_status)
            );

            wp_insert_term($custom_status, 'post_status', $args);
        }

        // Activate workflow process by default
        $workflow_process = 'active';

        if (!get_option('oasiswf_activate_workflow')) {
            update_option('oasiswf_activate_workflow', $workflow_process);
        }

        $show_wfsettings_on_post_types = array('post', 'page');

        // Review Setting - initial setting - everyone should approve
        $review_process_setting = 'everyone';

        // Priority Setting - initial setting is enabled
        $priority_setting = 'enable_priority';

        // default roles for bulk actions
        $bulk_approval_roles = array('administrator');

        if (!get_option('oasiswf_show_wfsettings_on_post_types')) {
            update_option('oasiswf_show_wfsettings_on_post_types', $show_wfsettings_on_post_types);
        }

        $email_settings = array(
            'from_name' => '',
            'from_email_address' => '',
            'assignment_emails' => 'yes',
            'reminder_emails' => 'no',
            'post_publish_emails' => 'yes',
            'unauthorized_post_update_emails' => 'yes',
            'abort_email_to_author' => 'yes',
            'submit_to_workflow_email' => 'no'
        );

        if (!get_option('oasiswf_email_settings')) {
            update_option('oasiswf_email_settings', $email_settings);
        }

        if (!get_option('oasiswf_priority_setting')) {
            update_option('oasiswf_priority_setting', $priority_setting);
        }

        // Due date setting
        if (!get_option('oasiswf_default_due_days')) {
            update_option('oasiswf_default_due_days', '');
        }

        // Publish date setting
        if (!get_option('oasiswf_publish_date_setting')) {
            update_option('oasiswf_publish_date_setting', '');
        }

        // Reminder days setting
        if (!get_option('oasiswf_reminder_days')) {
            update_option('oasiswf_reminder_days', '');
        }

        // Reminder days after setting
        if (!get_option('oasiswf_reminder_days_after')) {
            update_option('oasiswf_reminder_days_after', '');
        }

        // add new option for review rating
        if (!get_option('oasiswf_review_notice')) {
            update_option('oasiswf_review_notice', 'no');
        }

        if (!get_option('oasiswf_review_rating_interval')) {
            update_option('oasiswf_review_rating_interval', '');
        }

        if (!get_option('oasiswf_workflow_completed_post_count')) {
            update_option('oasiswf_workflow_completed_post_count', 0);
        }

        $this->install_admin_database();
        $this->install_site_database();
    }

    /**
     * Called on uninstall - deletes site_options
     *
     * @since 2.0
     */
    private static function run_on_uninstall()
    {
        if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
            exit();

        global $wpdb;  // required global declaration of WP variable
        delete_site_option('oasiswf_info');
        delete_site_option('oasiswf_process');
        delete_site_option('oasiswf_path');
        delete_site_option('oasiswf_status');
        delete_site_option('oasiswf_placeholders');

        // delete the dismissed_wp_pointers entry for this plugin
        $blog_users = get_users('role=administrator');
        foreach ($blog_users as $user) {
            $dismissed = explode(',', (string) get_user_meta($user->ID, 'dismissed_wp_pointers', true));
            if (($key = array_search('owf_install_free', $dismissed)) !== false) {
                unset($dismissed[$key]);
            }

            $updated_dismissed = implode(',', $dismissed);
            update_user_meta($user->ID, 'dismissed_wp_pointers', $updated_dismissed);
        }
    }

    /**
     * Called on uninstall - deletes site specific options
     *
     * @since 2.0
     */
    private static function delete_for_site()
    {
        global $wpdb;

        /*
         * Include the custom capability class
         */
        if (!class_exists('OW_Custom_Capabilities')) {
            include (OASISWF_PATH . 'includes/class-ow-custom-capabilities.php');
        }

        $ow_custom_capabilities = new OW_Custom_Capabilities();
        $ow_custom_capabilities->remove_capabilities();

        delete_option('oasiswf_activate_workflow');
        delete_option('oasiswf_default_due_days');
        delete_option('oasiswf_reminder_days');
        delete_option('oasiswf_show_wfsettings_on_post_types');
        delete_option('oasiswf_reminder_days_after');

        // delete all the post meta created by this plugin
        delete_post_meta_by_key('_oasis_is_in_workflow');
        delete_post_meta_by_key('_oasis_task_priority');

        delete_option('oasiswf_email_settings');
        delete_option('oasiswf_custom_workflow_terminology');
        delete_option('oasiswf_review_notice');
        delete_option('oasiswf_review_rating_interval');
        delete_option('oasiswf_publish_date_setting');
        delete_option('oasiswf_priority_setting');
        delete_option('oasiswf_roles_can_bulk_approval');
        delete_option('oasiswf_workflow_completed_post_count');

        $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name like 'workflow_%'");

        $wpdb->query('DROP TABLE IF EXISTS ' . OW_Utility::instance()->get_emails_table_name());
        $wpdb->query('DROP TABLE IF EXISTS ' . OW_Utility::instance()->get_action_history_table_name());
        $wpdb->query('DROP TABLE IF EXISTS ' . OW_Utility::instance()->get_action_table_name());
        $wpdb->query('DROP TABLE IF EXISTS ' . OW_Utility::instance()->get_workflow_steps_table_name());
        $wpdb->query('DROP TABLE IF EXISTS ' . OW_Utility::instance()->get_workflows_table_name());
    }

    /**
     * Invoked when a new blog is added in a multi-site setup
     *
     * @since 2.0
     */
    public function run_on_add_blog($blog_id, $user_id, $domain, $path, $site_id, $meta)
    {
        global $wpdb;
        if (is_plugin_active_for_network(basename(dirname(__FILE__)) . '/oasiswf.php')) {
            switch_to_blog($blog_id);
            $this->run_for_site();
            restore_current_blog();
        }
    }

    /**
     * Invoked with a blog is deleted in a multi-site setup
     *
     * @since 2.0
     */
    public function run_on_delete_blog($blog_id, $drop)
    {
        switch_to_blog($blog_id);
        $this->delete_for_site();
        restore_current_blog();
    }

    /**
     * Switch option name to a capability. Applicable for role-type options
     *
     * @param string $option_name
     * @param string $capability
     * @param WP_Roles wp_roles instance
     *
     * @since 2.0
     */
    private function switch_from_option_to_capability($option_name, $capability, $wp_roles)
    {
        $option_value = get_option($option_name);
        if (is_object($wp_roles) && is_array($option_value)) {
            foreach ($option_value as $role) {
                if ($role != 'owfpostauthor') {  // since this is not a real role
                    $wp_roles->add_cap($role, $capability);
                }
            }
        }
    }

    /**
     * Create workflow tables and create the default workflow
     *
     * @since 2.0
     */
    private function install_admin_database()
    {
        global $wpdb;
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE {$wpdb->collate}";
        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

        // fc_workflows table
        $table_name = OW_Utility::instance()->get_workflows_table_name();
        // phpcs:ignore
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
\t\t\tID int(11) NOT NULL AUTO_INCREMENT,
\t\t\tname varchar(200) NOT NULL,
\t\t\tdescription mediumtext,
\t\t\twf_info longtext,
\t\t\tversion int(3) NOT NULL default 1,
\t\t\tparent_id int(11) NOT NULL default 0,
\t\t\tstart_date date DEFAULT NULL,
\t\t\tend_date date DEFAULT NULL,
\t\t\tis_auto_submit int(2) NOT NULL default 0,
\t\t\tauto_submit_info mediumtext,
\t\t\tis_valid int(2) NOT NULL default 0,
\t\t\tcreate_datetime datetime DEFAULT NULL,
\t\t\tupdate_datetime datetime DEFAULT NULL,
\t\t\twf_additional_info mediumtext DEFAULT NULL,
\t\t\tPRIMARY KEY (ID)
\t\t\t){$charset_collate};";
            dbDelta($sql);
        }

        // fc_workflow_steps table
        $table_name = OW_Utility::instance()->get_workflow_steps_table_name();
        // phpcs:ignore
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
\t\t\tID int(11) NOT NULL AUTO_INCREMENT,
\t\t\tstep_info text NOT NULL,
\t\t\tprocess_info longtext NOT NULL,
\t\t\tworkflow_id int(11) NOT NULL,
\t\t\tcreate_datetime datetime DEFAULT NULL,
\t\t\tupdate_datetime datetime DEFAULT NULL,
\t\t\tPRIMARY KEY (ID),
\t\t\tKEY workflow_id (workflow_id)
\t\t\t){$charset_collate};";
            dbDelta($sql);
        }

        $this->populate_default_workflows();
    }

    /**
     * Create workflow action tables
     *
     * @since 2.0
     */
    private function install_site_database()
    {
        global $wpdb;
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE {$wpdb->collate}";
        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

        // fc_emails table
        $table_name = OW_Utility::instance()->get_emails_table_name();
        // phpcs:ignore
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            // action - 1 indicates not send, 0 indicates email sent
            $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
\t\t\tID int(11) NOT NULL AUTO_INCREMENT,
\t\t\tsubject mediumtext,
\t\t\tmessage mediumtext,
\t\t\tfrom_user int(11),
\t\t\tto_user int(11),
\t\t\taction int(2) DEFAULT 1,
\t\t\thistory_id bigint(20),
\t\t\tsend_date date DEFAULT NULL,
\t\t\tcreate_datetime datetime DEFAULT NULL,
\t\t\tPRIMARY KEY (ID)
\t\t\t){$charset_collate};";
            dbDelta($sql);
        }

        // fc_action_history table
        $table_name = OW_Utility::instance()->get_action_history_table_name();
        // phpcs:ignore
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
\t\t\tID bigint(20) NOT NULL AUTO_INCREMENT,
\t\t\taction_status varchar(20) NOT NULL,
\t\t\tcomment longtext NOT NULL,
\t\t\tstep_id int(11) NOT NULL,
\t\t\tassign_actor_id int(11) NOT NULL,
\t\t\tpost_id bigint(20) NOT NULL,
\t\t\tfrom_id bigint(20) NOT NULL,
\t\t\tdue_date date DEFAULT NULL,
         history_meta longtext DEFAULT NULL,
\t\t\treminder_date date DEFAULT NULL,
\t\t\treminder_date_after date DEFAULT NULL,
\t\t\tcreate_datetime datetime NOT NULL,
\t\t\tPRIMARY KEY (ID)
\t\t\t){$charset_collate};";
            dbDelta($sql);
        }

        // fc_action table
        $table_name = OW_Utility::instance()->get_action_table_name();
        // phpcs:ignore
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
\t\t\tID bigint(20) NOT NULL AUTO_INCREMENT,
\t\t\treview_status varchar(20) NOT NULL,
\t\t\tactor_id int(11) NOT NULL,
\t\t\tnext_assign_actors text NOT NULL,
\t\t\tstep_id int(11) NOT NULL,
\t\t\tcomments mediumtext,
\t\t\tdue_date date DEFAULT NULL,
\t\t\taction_history_id bigint(20) NOT NULL,
         history_meta longtext DEFAULT NULL,
\t\t\tupdate_datetime datetime NOT NULL,
\t\t\tPRIMARY KEY (ID)
\t\t\t){$charset_collate};";
            dbDelta($sql);
        }
    }

    /**
     * Add default data - Create a Multi Level Review Workflow
     */
    private function populate_default_workflows()
    {
        global $wpdb;

        // insert into workflow table
        $table_name = OW_Utility::instance()->get_workflows_table_name();
        // phpcs:ignore
        $row = $wpdb->get_row("SELECT max(ID) as maxid FROM $table_name");
        if (is_numeric($row->maxid)) {  // data already exists, do not insert another row.
            return;
        }
        // phpcs:ignore
        $workflow_info = stripcslashes('{"steps":{"step0":{"fc_addid":"step0","fc_label":"Author Assignment","fc_dbid":"2","fc_process":"assignment","fc_position":["326px","568px"]},"step1":{"fc_addid":"step1","fc_label":"First Level Review","fc_dbid":"1","fc_process":"review","fc_position":["250px","358px"]},"step2":{"fc_addid":"step2","fc_label":"Second Level Review and Publish","fc_dbid":"3","fc_process":"publish","fc_position":["119px","622px"]}},"conns":{"0":{"sourceId":"step2","targetId":"step0","post_status":"draft","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"red"}}},"1":{"sourceId":"step1","targetId":"step0","post_status":"draft","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"red"}}},"2":{"sourceId":"step0","targetId":"step1","post_status":"pending","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"blue"}}},"3":{"sourceId":"step2","targetId":"step1","post_status":"pending","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"red"}}},"4":{"sourceId":"step1","targetId":"step2","post_status":"ready-to-publish","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"blue"}}}},"first_step":[{"step":"step1","post_status":"draft"}]}');
        // phpcs:ignore
        $additional_info = stripcslashes('a:4:{s:16:"wf_for_new_posts";i:1;s:20:"wf_for_revised_posts";i:1;s:12:"wf_for_roles";a:0:{}s:17:"wf_for_post_types";a:0:{}}');
        $data = array(
            'name' => 'Multi Level Review Workflow',
            'description' => 'Multi Level Review Workflow',
            'wf_info' => $workflow_info,
            'start_date' => date('Y-m-d', current_time('timestamp')),  // phpcs:ignore
            'end_date' => date('Y-m-d', current_time('timestamp') + YEAR_IN_SECONDS),  // phpcs:ignore
            'is_valid' => 1,
            'create_datetime' => current_time('mysql'),
            'update_datetime' => current_time('mysql'),
            'wf_additional_info' => $additional_info
        );
        // phpcs:ignore
        $result = $wpdb->insert($table_name, $data);
        if ($result) {
            // phpcs:ignore
            $row = $wpdb->get_row("SELECT max(ID) as maxid FROM $table_name");
            if ($row)
                $workflow_id = $row->maxid;
            else
                return false;
        } else {
            return false;
        }

        // insert steps
        $workflow_step_table = OW_Utility::instance()->get_workflow_steps_table_name();

        // step 1 - review
        $review_step_info = '{"process":"review","step_name":"First Level Review","assign_to_all":0,"task_assignee":{"roles":["editor"],"users":[],"groups":[]},"assignee":{},"status":"","review_approval":"everyone"}';
        $review_process_info = '{"assign_subject":"","assign_content":"","reminder_subject":"","reminder_content":""}';
        // phpcs:ignore
        $wpdb->insert(
            $workflow_step_table, array(
                'step_info' => stripcslashes($review_step_info),
                'process_info' => stripcslashes($review_process_info),
                'create_datetime' => current_time('mysql'),
                'update_datetime' => current_time('mysql'),
                'workflow_id' => $workflow_id
            )
        );

        // step 2 - assignment
        $assignment_step_info = '{"process":"assignment","step_name":"Author Assignment","assign_to_all":0,"task_assignee":{"roles":["author"],"users":[],"groups":[]},"assignee":{},"status":""}';
        $assignment_process_info = '{"assign_subject":"","assign_content":"","reminder_subject":"","reminder_content":""}';
        // phpcs:ignore
        $wpdb->insert(
            $workflow_step_table, array(
                'step_info' => stripcslashes($assignment_step_info),
                'process_info' => stripcslashes($assignment_process_info),
                'create_datetime' => current_time('mysql'),
                'update_datetime' => current_time('mysql'),
                'workflow_id' => $workflow_id
            )
        );

        // step 3 - publish
        $publish_step_info = '{"process":"publish","step_name":"Second Level Review and Publish","assign_to_all":0,"task_assignee":{"roles":["administrator"],"users":[],"groups":[]},"assignee":{},"status":""}';
        $publish_process_info = '{"assign_subject":"","assign_content":"","reminder_subject":"","reminder_content":""}';
        // phpcs:ignore
        $wpdb->insert(
            $workflow_step_table, array(
                'step_info' => stripcslashes($publish_step_info),
                'process_info' => stripcslashes($publish_process_info),
                'create_datetime' => current_time('mysql'),
                'update_datetime' => current_time('mysql'),
                'workflow_id' => $workflow_id
            )
        );

        $this->install_single_level_review_workflow();
    }

    /**
     * Add default Single Level Review Workflow
     * @since 4.9
     */
    private function install_single_level_review_workflow()
    {
        global $wpdb;

        // insert into workflow table
        $table_name = OW_Utility::instance()->get_workflows_table_name();
        $row = $wpdb->get_row("SELECT max(ID) as maxid FROM $table_name");  // phpcs:ignore
        if (is_numeric($row->maxid) && ($row->maxid >= 2)) {  // data already exists, do not insert another row.
            return;
        }
        // phpcs:ignore
        $workflow_info = stripcslashes('{"steps":{"step0":{"fc_addid":"step0","fc_label":"Review and Publish","fc_dbid":"4","fc_process":"publish","fc_position":["169px","135px"]},"step1":{"fc_addid":"step1","fc_label":"Author Assignment","fc_dbid":"5","fc_process":"assignment","fc_position":["168px","506px"]}},"conns":{"0":{"sourceId":"step0","targetId":"step1","post_status":"with-author","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"red"}}},"1":{"sourceId":"step1","targetId":"step0","post_status":"ready-to-publish","connset":{"connector":"StateMachine","paintStyle":{"lineWidth":3,"strokeStyle":"blue"}}}},"first_step":[{"step":"step0","post_status":"pending"}]}');
        // phpcs:ignore
        $additional_info = stripcslashes('a:4:{s:16:"wf_for_new_posts";i:1;s:20:"wf_for_revised_posts";i:1;s:12:"wf_for_roles";a:0:{}s:17:"wf_for_post_types";a:0:{}}');
        $data = array(
            'name' => 'Single Level Review Workflow',
            'description' => 'Single Level Review Workflow',
            'wf_info' => $workflow_info,
            'start_date' => date('Y-m-d', current_time('timestamp')),  // phpcs:ignore
            'end_date' => date('Y-m-d', current_time('timestamp') + YEAR_IN_SECONDS),  // phpcs:ignore
            'is_valid' => 1,
            'create_datetime' => current_time('mysql'),
            'update_datetime' => current_time('mysql'),
            'wf_additional_info' => $additional_info
        );
        $result = $wpdb->insert($table_name, $data);  // phpcs:ignore
        if ($result) {
            // phpcs:ignore
            $row = $wpdb->get_row("SELECT max(ID) as maxid FROM $table_name");
            if ($row)
                $workflow_id = $row->maxid;
            else
                return false;
        } else {
            return false;
        }

        // insert steps
        $workflow_step_table = OW_Utility::instance()->get_workflow_steps_table_name();

        // step 1 - review and publish
        $publish_step_info = '{"process":"publish","step_name":"Review and Publish","assign_to_all":0,"task_assignee":{"roles":["administrator"],"users":[],"groups":[]},"assignee":{},"status":""}';
        $publish_process_info = '{"assign_subject":"","assign_content":"","reminder_subject":"","reminder_content":""}';

        // phpcs:ignore
        $wpdb->insert(
            $workflow_step_table, array(
                'step_info' => stripcslashes($publish_step_info),
                'process_info' => stripcslashes($publish_process_info),
                'create_datetime' => current_time('mysql'),
                'update_datetime' => current_time('mysql'),
                'workflow_id' => $workflow_id
            )
        );

        // step 2 - assignment
        $assignment_step_info = '{"process":"assignment","step_name":"Author Assignment","assign_to_all":0,"task_assignee":{"roles":["owfpostsubmitter"],"users":[],"groups":[]},"assignee":{},"status":""}';
        $assignment_process_info = '{"assign_subject":"","assign_content":"","reminder_subject":"","reminder_content":""}';
        // phpcs:ignore
        $wpdb->insert(
            $workflow_step_table, array(
                'step_info' => stripcslashes($assignment_step_info),
                'process_info' => stripcslashes($assignment_process_info),
                'create_datetime' => current_time('mysql'),
                'update_datetime' => current_time('mysql'),
                'workflow_id' => $workflow_id
            )
        );
    }

    /**
     * Called on uninstall OR blog delete to clear the scheduled hooks
     *
     * @since 2.0
     */
    private static function clear_scheduled_hooks()
    {
        /*
         * Mail schedule remove
         */
        wp_clear_scheduled_hook('oasiswf_email_schedule');
    }

    /**
     * enqueue CSS files
     *
     * @since 2.0
     */
    public function add_css_files($page)
    {
        // ONLY load OWF scripts on OWF plugin pages
        // phpcs:ignore
        if (is_admin() && preg_match_all('/page=ow-settings(.*)|page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $_SERVER['REQUEST_URI'], $matches)) {
            wp_enqueue_style('owf-css', OASISWF_URL . 'css/pages/context-menu.css', false, OASISWF_VERSION, 'all');
            wp_enqueue_style('owf-modal-css', OASISWF_URL . 'css/lib/modal/simple-modal.css', false, OASISWF_VERSION, 'all');
            wp_enqueue_style('owf-calendar-css', OASISWF_URL . 'css/lib/calendar/datepicker.css', false, OASISWF_VERSION, 'all');
            wp_enqueue_style('owf-oasis-workflow-css', OASISWF_URL . 'css/pages/oasis-workflow.css', false, OASISWF_VERSION, 'all');

            /**
             * enqueue status dropdown js
             * @since 2.1
             */
            wp_register_script('owf-post-statuses', OASISWF_URL . 'js/pages/ow-status-dropdown.js', array('jquery'), OASISWF_VERSION);
            wp_enqueue_script('owf-post-statuses');
        }
    }

    /**
     * enqueue javascripts
     *
     * @since 2.0
     */
    public function add_js_files()
    {
        // ONLY load OWF scripts on OWF plugin pages
        // phpcs:ignore
        if (is_admin() && preg_match_all('/page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $_SERVER['REQUEST_URI'], $matches)) {
            echo "<script type='text/javascript'>
   \t\t\t\t\tvar wf_structure_data = '' ;
   \t\t\t\t\tvar wfeditable = '' ;
   \t\t\t\t\tvar wfPluginUrl  = '" . esc_url(OASISWF_URL) . "' ;
   \t\t\t\t</script>";
        }

        // phpcs:ignore
        if (is_admin() && isset($_GET['page']) && ($_GET['page'] == 'oasiswf-inbox' || $_GET['page'] == 'oasiswf-history')) {
            OW_Plugin_Init::enqueue_and_localize_inbox_script();
        }
        if (is_admin()) {
            wp_enqueue_script('owf-review-rating', OASISWF_URL . 'js/pages/subpages/review-rating.js', array('jquery'), OASISWF_VERSION);
        }
    }

    public static function enqueue_and_localize_inbox_script()
    {
        $ow_process_flow = new OW_Process_Flow();
        wp_enqueue_script('owf-workflow-inbox', OASISWF_URL . 'js/pages/workflow-inbox.js', array('jquery'), OASISWF_VERSION);
        wp_enqueue_script('owf-workflow-history', OASISWF_URL . 'js/pages/workflow-history.js', array('jquery'), OASISWF_VERSION);

        wp_localize_script('owf-workflow-inbox', 'owf_workflow_inbox_vars', array(
            'dateFormat' => OW_Utility::instance()->owf_date_format_to_jquery_ui_format(get_option('date_format')),
            'editDateFormat' => OW_Utility::instance()->owf_date_format_to_jquery_ui_format(OASISWF_EDIT_DATE_FORMAT),
            'abortWorkflowConfirm' => __('Are you sure to abort the workflow?', 'oasisworkflow')
        ));
    }

    /**
     * load/enqueue javascripts as part of the footer
     */
    public function load_js_files_footer()
    {
        // ONLY load OWF scripts on OWF plugin pages
        // phpcs:ignore
        if (is_admin() && preg_match_all('/page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $_SERVER['REQUEST_URI'], $matches)) {
            // wp_enqueue_script( 'thickbox' );
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-widget');
            wp_enqueue_script('jquery-ui-mouse');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-json', OASISWF_URL . 'js/lib/jquery.json.js', '', '2.3', true);
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-droppable');
        }

        // phpcs:ignore
        if (is_admin() && (isset($_GET['page']) && ($_GET['page'] == 'oasiswf-admin' || $_GET['page'] == 'oasiswf-add'))) {
            wp_enqueue_style('select2-style', OASISWF_URL . 'css/lib/select2/select2.css', false, OASISWF_VERSION, 'all');
            wp_enqueue_script('jsPlumb', OASISWF_URL . 'js/lib/jquery.jsPlumb-all-min.js', array('jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable'), '1.4.1', true);
            wp_enqueue_script('drag-drop-jsplumb', OASISWF_URL . 'js/pages/drag-drop-jsplumb.js', array('jsPlumb'), OASISWF_VERSION, true);
            wp_enqueue_script('select2-js', OASISWF_URL . 'js/lib/select2/select2.min.js', array('jquery'), OASISWF_VERSION, true);
            wp_localize_script('drag-drop-jsplumb', 'drag_drop_jsplumb_vars', array(
                'clearAllSteps' => esc_html__('Do you really want to clear all the steps?', 'oasisworkflow'),
                'removeStep' => esc_html__('This step is already defined. Do you really want to remove this step?', 'oasisworkflow'),
                'postStatusRequired' => esc_html__('Please select Post Status.', 'oasisworkflow'),
                'pathBetween' => esc_html__('The path between', 'oasisworkflow'),
                'stepAnd' => esc_html__('step and', 'oasisworkflow'),
                'incorrect' => esc_html__('step is incorrect.', 'oasisworkflow'),
                'stepHelp' => esc_html__('To edit/delete the step, right click on the step to access the step menu.', 'oasisworkflow'),
                'connectionHelp' => esc_html__('To connect to another step drag a line from the "dot" to the next step.', 'oasisworkflow'),
                'postStatusLabel' => esc_html__('Post Status', 'oasisworkflow')
            ));
        }

        // phpcs:ignore
        if (is_admin() && preg_match_all('/page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $_SERVER['REQUEST_URI'], $matches)) {
            // Check if we're on the desired admin page
            if (
                (isset($_GET['page']) && $_GET['page'] === 'oasiswf-add') ||
                (isset($_GET['page']) && $_GET['page'] === 'oasiswf-admin' && isset($_GET['wf_id']) && is_numeric($_GET['wf_id']))
            ) {
                wp_enqueue_script('owf-workflow-create', OASISWF_URL . 'js/pages/workflow-create.js', '', OASISWF_VERSION, true);
                wp_enqueue_script('owf-workflow-delete', OASISWF_URL . 'js/pages/workflow-delete.js', '', OASISWF_VERSION, true);

                wp_localize_script('owf-workflow-create', 'owf_workflow_create_vars', array(
                    'alreadyExistWorkflow' => esc_html__('There is an existing workflow with the same name. Please choose another name.', 'oasisworkflow'),
                    'unsavedChanges' => esc_html__('You have unsaved changes.', 'oasisworkflow'),
                    'dateFormat' => OW_Utility::instance()->owf_date_format_to_jquery_ui_format(get_option('date_format')),
                    'editDateFormat' => OW_Utility::instance()->owf_date_format_to_jquery_ui_format(OASISWF_EDIT_DATE_FORMAT)
                ));

                wp_localize_script('owf-workflow-delete', 'owf_workflow_delete_vars', array(
                    'workflow_delete_nonce' => wp_create_nonce('workflow_delete_nonce')
                ));
            }

            wp_enqueue_script('jquery-simplemodal', OASISWF_URL . 'js/lib/modal/jquery.simplemodal.js', '', '1.4.6', true);
            wp_enqueue_script('owf-workflow-util', OASISWF_URL . 'js/pages/workflow-util.js', '', OASISWF_VERSION, true);
            wp_localize_script('owf-workflow-util', 'owf_workflow_util_vars', array(
                'dueDateInPast' => esc_html__('Due date cannot be in the past.', 'oasisworkflow')
            ));

            wp_enqueue_script('text-edit-whizzywig', OASISWF_URL . 'js/lib/textedit/whizzywig63.js', '', '63', true);
            wp_enqueue_script('owf-workflow-step-info', OASISWF_URL . 'js/pages/subpages/step-info.js', array('text-edit-whizzywig'), OASISWF_VERSION, true);

            wp_localize_script('owf-workflow-step-info', 'owf_workflow_step_info_vars', array(
                'stepNameRequired' => esc_html__('Step name is required.', 'oasisworkflow'),
                'stepNameAlreadyExists' => esc_html__('Step name already exists. Please use a different name.', 'oasisworkflow'),
                'selectAssignees' => esc_html__('Please select assignee(s).', 'oasisworkflow'),
                'selectPlaceholder' => esc_html__('Please select a placeholder.', 'oasisworkflow')
            ));
        }

        // phpcs:ignore
        if (is_admin() && preg_match_all('/edit\.(.*)/', $_SERVER['REQUEST_URI'], $matches)) {
            wp_enqueue_style('owf-oasis-workflow-css', OASISWF_URL . 'css/pages/oasis-workflow.css', false, OASISWF_VERSION, 'all');

            /**
             * enqueue status dropdown js
             * @since 2.1
             */
            wp_register_script('owf-post-statuses', OASISWF_URL . 'js/pages/ow-status-dropdown.js', array('jquery'), OASISWF_VERSION);
            wp_enqueue_script('owf-post-statuses');

            OW_Plugin_Init::enqueue_and_localize_simple_modal_script();
        }

        // phpcs:ignore
        if (is_admin() && preg_match_all('/page=ow-settings&tab=email_settings(.*)/', $_SERVER['REQUEST_URI'], $matches)) {
            wp_enqueue_script('owf-email-settings', OASISWF_URL . 'js/pages/email-settings.js', array('jquery'), OASISWF_VERSION);
        }
    }

    /**
     * Enqueue and Localize the simple modal script
     *
     * @since 2.0
     */
    public static function enqueue_and_localize_simple_modal_script()
    {
        wp_enqueue_script('jquery-simplemodal', OASISWF_URL . 'js/lib/modal/jquery.simplemodal.js', '', '1.4.6', true);
        wp_enqueue_style('owf-modal-css', OASISWF_URL . 'css/lib/modal/simple-modal.css', false, OASISWF_VERSION, 'all');
    }

    /**
     * Display "Workflow Tasks At a Glance" widget to Dashboard
     *
     * @since 2.1
     */
    public function add_workflow_tasks_summary_widget()
    {
        wp_add_dashboard_widget('task_dashboard',
            esc_html__('Workflow Tasks At a Glance', 'oasisworkflow'),
            array($this, 'tasks_summary_dashboard_content'));
    }

    /**
     * Dashboard widget callback
     */
    public function tasks_summary_dashboard_content()
    {
        include_once (OASISWF_PATH . 'includes/pages/workflow-dashboard-widget.php');
    }

    /**
     * Enqueue dashboard widget css
     * @param string $hook - current page name ie. index.php
     *
     * @since 2.1
     */
    public function enqueue_wp_dashboard_style($hook)
    {
        if ('index.php' != $hook) {
            return;
        }

        wp_enqueue_style('owf-dashboard-css', OASISWF_URL . 'css/pages/workflow-dashboard-widget.css', false, OASISWF_VERSION, 'all');
    }

    /**
     * Add custom link for the plugin beside activate/deactivate links
     * @param array $links Array of links to display below our plugin listing.
     * @return array Amended array of links.    *
     * @since 2.6
     */
    public function oasiswf_plugin_action_links($links)
    {
        // We shouldn't encourage editing our plugin directly.
        unset($links['edit']);

        // adding class to deactivate to open feedback modal on click
        if (array_key_exists('deactivate', $links)) {
            $links['deactivate'] = str_replace('<a', '<a class="owf-deactivate-link"', $links['deactivate']);
        }

        $stay_informed = '<a href="' . admin_url('admin.php?page=oasiswf-stay-informed') . '">' . __('Stay Informed', 'oasisworkflow') . '</a>';

        array_unshift($links, $stay_informed);

        $links['owf_go_pro'] = '<a href="https://www.oasisworkflow.com/pricing-purchase" class="owf-go-pro" target="_blank">' . __('Go Pro', 'oasisworkflow') . '</a>';

        return $links;
    }

    /**
     * Enqueue oasis workflow gutenberg JavaScript and CSS
     */
    public function ow_gutenberg_scripts()
    {
        if (is_admin()) {
            global $wp_version;

            // load `owf_acf_validator` script so it can be used as `ow-gutenberg-sidebar-js` dependency
            $ow_process_flow = new OW_Process_Flow();
            $ow_process_flow->enqueue_acf_validator_script();

            $dependencies = [
                'wp-i18n',
                'wp-edit-post',
                'wp-element',
                'wp-editor',
                'wp-components',
                'wp-data',
                'utils',
                'wp-plugins',
                'wp-compose',
                'wp-edit-post',
                'wp-api-fetch',
                'wp-api',
            ];

            // since v6.5 to load in footer
            if (defined('ACF_VERSION')) {
                $dependencies[] = 'owf_acf_validator';
            }

            $blockPath = '/dist/ow-gutenberg.js';
            $stylePath = '/dist/ow-gutenberg.css';

            // Enqueue the bundled block JS file
            wp_enqueue_script(
                'ow-gutenberg-sidebar-js',
                plugins_url($blockPath, __FILE__),
                $dependencies,
                filemtime(plugin_dir_path(__FILE__) . $blockPath),
                true  // since v6.5 to load in footer
            );

            wp_localize_script(
                'ow-gutenberg-sidebar-js',
                'OWBlockEditorVars',
                [
                    'wpVersion' => $wp_version,
                    'pluginVersion' => OASISWF_VERSION
                ]
            );

            // Enqueue frontend and editor block styles
            wp_enqueue_style(
                'ow-gutenberg-sidebar-css',
                plugins_url($stylePath, __FILE__),
                '',
                filemtime(plugin_dir_path(__FILE__) . $stylePath)
            );

            $value = wp_set_script_translations('ow-gutenberg-sidebar-js', 'oasisworkflow', OASISWF_PATH . 'languages');
        }
    }

    /**
     * Add REST API support to an already registered post type.
     * @param array $args
     * @param string $post_type
     * @return array
     */
    public function update_custom_post_type_args($args, $post_type)
    {
        // Get post type selected from workflow settings
        $workflow_post_types = get_option('oasiswf_show_wfsettings_on_post_types');
        if (in_array($post_type, $workflow_post_types)) {
            $args['show_in_rest'] = 1;
            // If support attribute is set then only set custom-fields and revision attributes else bypass it.
            if (isset($args['supports']) && (!in_array('custom-fields', $args['supports']))) {
                array_push($args['supports'], 'custom-fields');
            }
            if (isset($args['supports']) && (!in_array('revisions', $args['supports']))) {
                array_push($args['supports'], 'revisions');
            }
        }
        return $args;
    }
}

// initialize the plugin
$ow_plugin_init = new OW_Plugin_Init();
