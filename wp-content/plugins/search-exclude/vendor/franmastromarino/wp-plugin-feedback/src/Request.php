<?php

namespace QuadLayers\PluginFeedback;

class Request
{
    /**
     * Sends the feedback data to the remote server.
     *
     * @param  array $data The collected data.
     * @return bool True if the data was sent successfully, False otherwise.
     */
    public function send(array $data): bool
    {

        $body = json_encode($data);

        // Create unique hash to prevent duplicate submissions
        $hash = md5($body);

        if (get_transient('ql_plugin_feedback_' . $hash)) {
            return true;
        }

        // Create a transient with the hash as the key and set the expiration time to 7 days
        set_transient('ql_plugin_feedback_' . $hash, true, 30 * DAY_IN_SECONDS);

        $response = wp_remote_post(
            'https://feedback.quadlayers.com/',
            array(
                'body'    => $body,
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen($body),
                    'User-Agent' => 'WordPress/QuadLayers', // Set a custom User-Agent
                ],
            )
        );

        if (is_wp_error($response)) {
            return false;
        }

        $statusCode = wp_remote_retrieve_response_code($response);

        // Return true if the status code is 200 (OK)
        return $statusCode === 200;
    }
}
