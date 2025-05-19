<?php

namespace QuadLayers\PluginFeedback;

class Scripts
{

    public static $instance;
    public static $plugins;
    public static $options;

    private function __construct(array $plugins = [], array $options = [])
    {
        self::$plugins = $plugins;
        self::$options = $options;
        add_action('admin_enqueue_scripts', [self::class, 'load']);
    }

    public static function instance(array $plugins, array $options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($plugins, $options);
        }
        return self::$instance;
    }

    public static function load(): void
    {
        global $pagenow;
    
        // Only load on the plugins page
        if ($pagenow !== 'plugins.php') {
            return;
        }

        $feedback = include plugin_dir_path(__FILE__) . '../build/js/index.asset.php';
    
        wp_enqueue_style('wp-components');
        wp_enqueue_script('quadlayers-plugin-feedback', plugins_url('../build/js/index.js', __FILE__), $feedback['dependencies'], '1.0.0', true);
        
        // Prepare plugin options for frontend
        $plugin_data = [];
        foreach (self::$plugins as $plugin) {
            $plugin_data[$plugin] = [
                'plugin' => $plugin,
                'options' => isset(self::$options[$plugin]) ? self::$options[$plugin] : []
            ];
        }
    
        wp_localize_script(
            'quadlayers-plugin-feedback', 
            'quadlayersPluginFeedback', 
            [
                'nonce'     => wp_create_nonce('quadlayers_send_feedback_nonce'),
                'plugins'   => $plugin_data
            ]
        );
    }
}
