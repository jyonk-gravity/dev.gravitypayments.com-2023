<?php
namespace Perfmatters;

class Utilities
{
    //get given post meta option for current post
    public static function get_post_meta($option) {

        global $post;

        if(!is_object($post)) {
            return false;
        }

        if(is_home()) {
            $post_id = get_queried_object_id();
        }

        if(is_singular() && isset($post)) {
            $post_id = $post->ID;
        }

        return (isset($post_id)) ? get_post_meta($post_id, $option, true) : false;
    }

    //remove unecessary bits from html for search
    public static function clean_html($html) {

        //remove existing script tags
        $html = preg_replace('/<script\b(?:[^>]*)>.*?<\/script>/msi', '', $html);

        //remove existing noscript tags
        $html = preg_replace('#<noscript>(?:.+)</noscript>#Umsi', '', $html);

        return $html;
    }

    //get array of element attributes from attribute string
    public static function get_atts_array($atts_string) {
    
        if(!empty($atts_string)) {
            $atts_array = array_map(
                function(array $attribute) {
                    return $attribute['value'];
                },
                wp_kses_hair($atts_string, self::wp_allowed_protocols())
            );

            return $atts_array;
        }

        return false;
    }

    //get attribute string from array of element attributes
    public static function get_atts_string($atts_array) {

        if(!empty($atts_array)) {
            $assigned_atts_array = array_map(
            function($name, $value) {
                    if($value === '') {
                        return $name;
                    }
                    return sprintf('%s="%s"', $name, esc_attr($value));
                },
                array_keys($atts_array),
                $atts_array
            );
            $atts_string = implode(' ', $assigned_atts_array);

            return $atts_string;
        }

        return false;
    }

    //add protocols to allowed list for internal use
    public static function wp_allowed_protocols()
    {
        $protocols = wp_allowed_protocols();
        $protocols[] = 'data';
        return $protocols;
    }

    //check for string match inside array
    public static function match_in_array($string, $array) {
        
        if(!empty($array)) {
            foreach((array) $array as $item) {
                if(stripos($string, $item) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    //check for specific woocommerce pages
    public static function is_woocommerce() {
        return apply_filters('perfmatters_is_woocommerce', class_exists('WooCommerce') && (is_cart() || is_checkout() || is_account_page()));
    }

    //return root directory path
    public static function get_root_dir_path() {
        $wp_content_relative_path = str_replace(array(trailingslashit(home_url()), trailingslashit(site_url())), '', content_url(), $count);

        //try pathless home url if nothing matched so far
        if(empty($count)) {
            $parsed_url = trailingslashit(($path = wp_parse_url(home_url(), PHP_URL_PATH)) ? str_replace($path, '', home_url()) : home_url());
            $wp_content_relative_path = str_replace($parsed_url, '', content_url());
        }

        $pos = strrpos(WP_CONTENT_DIR, $wp_content_relative_path);
        if($pos !== false) {
            $root_dir_path = substr_replace(WP_CONTENT_DIR, '', $pos, strlen($wp_content_relative_path));
        }
        else {
            $root_dir_path = WP_CONTENT_DIR;
        }
        return trailingslashit($root_dir_path);
    }

    //get local file path from url
    public static function get_file_path($url) {
        
        //get image path
        $parsed_url = @parse_url($url);
        if(empty($parsed_url['path'])) {
            return false;
        }

        //parse base path to strip
        $base_path = parse_url(home_url('/'), PHP_URL_PATH);
    
        //make sure base_path is not empty and ends with a slash
        $base_path = ($base_path && $base_path !== '/') ? rtrim($base_path, '/') . '/' : '/';

        $file_path = Utilities::get_root_dir_path() . ltrim($parsed_url['path'], $base_path);

        return $file_path;
    }

    //gets path to uploads directory, can pass in directory or file to add if needed
    public static function get_uploads_dir($subdir = '') {

        $upload_dir = wp_get_upload_dir(); 
        //$upload_dir = wp_get_upload_dir(); 
        $url = $upload_dir['basedir'];

        if ( ! empty( $subdir ) ) {
            $url = trailingslashit( $url ) . ltrim( $subdir, '/' );
        }

        return $url;
    }

    //gets url to uploads directory, can pass in directory or file to add if needed
    public static function get_uploads_url($subdir = '') {

        $upload_dir = wp_get_upload_dir(); 
        //$upload_dir = wp_get_upload_dir(); 
        $url = $upload_dir['baseurl'];

        if ( ! empty( $subdir ) ) {
            $url = trailingslashit( $url ) . ltrim( $subdir, '/' );
        }

        return $url;
    }
}