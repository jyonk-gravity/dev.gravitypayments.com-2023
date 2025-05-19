<?php

namespace QuadLayers\PluginFeedback;

class Validator
{
    /**
     * Validates the feedback data.
     *
     * @param  array $data        The collected data.
     * @param  bool  $isAnonymous Determines if the feedback is anonymous.
     * @return bool True if the data is valid, False otherwise.
     */
    public function validate(array $data, bool $isAnonymous = false): bool
    {
        // Validate required fields
        if (empty($data['plugin_slug'])) {
            return false;
        }

        return true;
    }
}
