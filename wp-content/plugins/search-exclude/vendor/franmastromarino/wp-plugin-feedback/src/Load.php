<?php

namespace QuadLayers\PluginFeedback;

class Load
{

    public static $instance;
    public static $plugins = array();
    public static $options = array();

    private function __construct()
    {
        add_action('admin_init', [self::class, 'ajax']);
        add_action('plugins_loaded', [self::class, 'scripts']);
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add(string $plugin_file, array $options = []): void
    {
        $pluginBasename = plugin_basename($plugin_file);

        // Check if the plugin is already in the list
        if (in_array($pluginBasename, self::$plugins)) {
            return;
        }

        // Check if the plugin transient exists
        if (get_transient('ql_plugin_feedback_' . $pluginBasename)) {
            return;
        }

        self::$plugins[] = $pluginBasename;
        self::$options[$pluginBasename] = $options;
    }

    public static function scripts(): void
    {
        // Enqueue the scripts for the deactivation survey
        Scripts::instance(self::$plugins, self::$options);
    }

    public static function ajax(): void
    {
        // Register AJAX actions
        AjaxHandler::instance();
    }
}
