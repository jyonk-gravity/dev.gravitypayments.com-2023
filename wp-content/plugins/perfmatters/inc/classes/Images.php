<?php
namespace Perfmatters;

class Images
{
    //initialize image functions
    public static function init() 
    {
        add_action('perfmatters_queue', array('Perfmatters\Images', 'queue'));
    }

    //queue functions
    public static function queue() 
    {
        //image dimensions
        if(!empty(Config::$options['lazyload']['image_dimensions']) && !empty(Config::$options['lazyload']['image_dimensions'])) {
            add_action('perfmatters_output_buffer_template_redirect', array('Perfmatters\Images', 'image_dimensions'));
        }
    }

    //fix images missing dimensions
    public static function image_dimensions($html) 
    {
        //match all img tags without width or height attributes
        preg_match_all('#<img((?:[^>](?!(height|width)=[\'\"](?:\S+)[\'\"]))*+)>#is', $html, $images, PREG_SET_ORDER);

        if(!empty($images)) {

            //remove any duplicate images
            $images = array_unique($images, SORT_REGULAR);

            //exclude specific images
            $image_exclusions = array(
                ';base64',
                'w3.org'
            );
            $image_exclusions = apply_filters('perfmatters_image_dimensions_exclusions', $image_exclusions);

            //loop through images
            foreach($images as $image) {

                //get image attributes array
                $image_atts = Utilities::get_atts_array($image[1]);

                $src = $image_atts['src'] ?? $image_atts['data-src'] ?? '';

                if(!empty($src)) {

                    foreach($image_exclusions as $exclude) {
                        if(strpos($image[1], $exclude) !== false) {
                            continue 2;
                        }
                    }

                    //get image dimensions
                    $dimensions = self::get_dimensions_from_url($src);

                    if(!empty($dimensions)) {

                        //remove any existing dimension attributes
                        $new_image = preg_replace('/(height|width)=[\'"](?:\S+)*[\'"]/i', '', $image[0]);

                        //add dimension attributes to img tag
                        $new_image = preg_replace('/<\s*img/i', '<img width="' . $dimensions['width'] . '" height="' . $dimensions['height'] . '"', $new_image);

                        //replace original img tag in html
                        if(!empty($new_image)) {
                            $html = str_replace($image[0], $new_image, $html);
                        }
                    }
                }
            }
        }

        return $html;
    }

    //return array of dimensions based on image url
    private static function get_dimensions_from_url($url)
    {
        //grab dimensions from file name if available
        if(preg_match('/(?:.+)-([0-9]+)x([0-9]+)\.(jpg|jpeg|png|gif|svg)$/', $url, $matches)) {
            return array('width' => $matches[1], 'height' => $matches[2]);
        }

        //get image path
        $parsed_url = parse_url($url);
        if(empty($parsed_url['path'])) {
            return false;
        }
        
        $image_path = Utilities::get_root_dir_path() . ltrim($parsed_url['path'], '/');

        if(file_exists($image_path)) {

            //get dimensions from file
            $sizes = getimagesize($image_path);

            if(!empty($sizes)) {
                return array('width' => $sizes[0], 'height' => $sizes[1]);
            }
        }

        return false;
    }
}