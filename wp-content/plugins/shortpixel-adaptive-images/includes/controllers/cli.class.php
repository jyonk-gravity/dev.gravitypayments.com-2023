<?php
/**
 * User: simon
 * Date: 17.11.2020
 */

/**
 * Just a few sample commands to learn how WP-CLI works
 */
class ShortPixelCLI extends WP_CLI_Command {
    /**
     * Clear the CSS cache.
     */
    function clear_css() {
        if(!!ShortPixelAI::clear_css_cache()) {
            WP_CLI::line( 'CSS cache cleared successfully.' );
        } else {
            WP_CLI::line( 'Could not clear CSS cache.' );
        }
    }

    function cleanup_postmeta_pseudourls() {
        global $wpdb;
        //SELECT * FROM `wp_postmeta WHERE meta_value like '%data:image/svg+xml;base64%'
        WP_CLI::line( 'CLEANUP POSTMETA PSEUDO-URLS');
        //SELECT * FROM `wp_postmeta WHERE meta_value like '%data:image/svg+xml;base64%'
        $sql = "SELECT * FROM " . $wpdb->get_blog_prefix() . "postmeta WHERE meta_value like '%data:image_svg+xml;base64%'";
        $rows = $wpdb->get_results( $sql );
        WP_CLI::line( 'Postmeta query returned ' . count($rows) . ' rows.' );
        foreach($rows as $row) {
            //WP_CLI::line( 'FIRST ROW:' . print_r($row->meta_value, true));return;
            $data = @unserialize($row->meta_value);
            //WP_CLI::line( 'FIRST ROW UNSERIALIZED:' . print_r($data, true));return;
            if ($data !== false) {
                WP_CLI::line( 'Checking serialized ' . $row->meta_key . '...' );
                $replacements = $this->clean_item($data);
                $value = serialize($data);
            }
            else {
                WP_CLI::line( 'Checking string ' . $row->meta_key . '...' );
                $value = $row->meta_value;
                $replacements = $this->clean_item($value);
            }
            if($replacements) {
                WP_CLI::line( 'HAVE ' . $replacements . ' REPLACEMENTS, UPDATE: ' . print_r(
                        $wpdb->update( $wpdb->prefix . 'postmeta', ['meta_value' => $value], ['meta_id' => $row->meta_id]), true));
                //$rows = $wpdb->query( $wpdb->prepare($sql, $value, $ro->meta_id));
            }
        }

    }

    private function clean_item(&$item) {
        $count = 0;
        if(is_array($item) || is_object($item)) {
            foreach($item as $key => $value) {
                $count += $this->clean_item($value);
                $item[$key] = $value;
            }
        } elseif(is_string($item)) {
            preg_match_all('/data:image\/svg\+xml;base64,[a-zA-Z0-9=\+]+/', $item, $matches);
            if(isset($matches[0])) foreach($matches[0] as $match) {
                $decoded = ShortPixelUrlTools::url_from_placeholder_svg($match);

                WP_CLI::line( 'MATCH ' . $match);
                if(strlen($decoded)) {
                    $count++;
                    WP_CLI::line( 'REPLACE WITH: ' . $decoded);
                    $item = str_replace($match, $decoded, $item);
                }

            }
        }
        return $count;
    }


    /**
     * Clear the LQIPs
     */
    function clear_lqips() {
        \ShortPixel\AI\LQIP::clearCache();
        WP_CLI::line( 'LQIP images cleared.' );
    }
}

WP_CLI::add_command( 'shortpixel', 'ShortPixelCLI' );