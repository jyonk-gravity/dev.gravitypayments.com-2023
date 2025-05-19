<?php

namespace QuadLayers\PluginFeedback;

class Collector
{

    private string $pluginBasename = '';

    public function __construct(
        string $pluginBasename = null
    ) {
        $this->pluginBasename = $pluginBasename;
    }

    /**
     * Collects the necessary feedback data.
     *
     * @param  string $pluginBasename The plugin slug.
     * @param  bool   $isAnonymous    If true, no personal data is collected.
     * @return array The collected data.
     */
    public function collectData(string $feedbackReason, string $feedbackDetails, $isAnonymous = false, $hasFeedback = false): array
    {
        $data = [
            'plugin_slug'        => $this->getPluginSlug(),
            'plugin_version'     => $this->getPluginVersion(),
            'server_software'    => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_php_version' => phpversion(),
            'server_mysql_version' => $this->getMySQLVersion(),
            'server_wp_version'  => get_bloginfo('version'),
            'site_url'           => get_site_url(),
            'site_theme'         => wp_get_theme()->get('Name'),
            'site_theme_version' => wp_get_theme()->get('Version'),
            'site_language'      => get_bloginfo('language'),
            'site_plugins'       => $this->getActivePlugins(),
            'feedback_reason'    => $feedbackReason,
            'feedback_details'   => $feedbackDetails,
            'is_anonymous'       => $isAnonymous,
            'has_feedback'       => $hasFeedback,
        ];

        // If feedback is not anonymous, collect user email
        if (true !== $isAnonymous) {
            $data['user_email'] = wp_get_current_user()->user_email;
        }

        return $data;
    }

    private function getMySQLVersion(): string
    {
        global $wpdb;
        return $wpdb->get_var("SELECT VERSION()") ?? 'Unknown';
    }

    private function getActivePlugins(): string
    {
        $plugins = get_option('active_plugins', []);
        $pluginNames = [];

        foreach ($plugins as $plugin) {
            $pluginData = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
            $pluginNames[] = $pluginData['Name'] . ' ' . $pluginData['Version'];
        }

        return implode(', ', $pluginNames);
    }

    private function getPluginSlug()
    {
        return dirname($this->pluginBasename);
    }

    private function getPluginVersion()
    {
        $pluginData = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->pluginBasename);

        return $pluginData['Version'];

    }
}
