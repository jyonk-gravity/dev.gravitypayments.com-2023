<?php

namespace QuadLayers\PluginFeedback;

class AjaxHandler
{

    public static $instance;

    private function __construct()
    {
        add_action('wp_ajax_quadlayers_send_feedback', [self::class, 'send_feedback']);
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Sends feedback from the deactivation survey via AJAX.
     */
    public static function send_feedback() 
    {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'quadlayers_send_feedback_nonce')) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'wp-plugin-feedback')], 400);
            return;
        }

        // Validate required fields
        $pluginBasename = sanitize_text_field($_POST['plugin_basename'] ?? '');
        $feedbackReason = sanitize_text_field($_POST['feedback_reason'] ?? '');
        $feedbackDetails = sanitize_textarea_field($_POST['feedback_details'] ?? '');
        $isAnonymous = filter_var($_POST['is_anonymous'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $hasFeedback = filter_var($_POST['has_feedback'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (empty($pluginBasename)) {
            wp_send_json_error(['message' => __('Missing required fields.', 'wp-plugin-feedback')], 400);
            return;
        }

        // Create a transient with the hash as the key and set the expiration time to 7 days
        set_transient('ql_plugin_feedback_' . $pluginBasename, true, 7 * DAY_IN_SECONDS);
        
        // Send feedback (handling anonymous option)
        $result = (new Client($pluginBasename))->sendFeedback($feedbackReason, $feedbackDetails, $isAnonymous, $hasFeedback);

        if ($result) {
            wp_send_json_success(['message' => __('Feedback submitted successfully.', 'wp-plugin-feedback')]);
        } else {
            wp_send_json_error(['message' => __('Error sending feedback.', 'wp-plugin-feedback')], 500);
        }
    }
}
