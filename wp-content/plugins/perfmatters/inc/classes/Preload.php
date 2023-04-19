<?php
namespace Perfmatters;

class Preload
{
    private static $preloads;
    private static $critical_images;
    private static $preloads_ready = array();
    private static $used_srcs = array();

    //initialize preload functions
    public static function init() 
    {
       add_action('wp', array('Perfmatters\Preload', 'queue'));
    }

    //queue functions
    public static function queue() 
    {
        self::$preloads = apply_filters('perfmatters_preloads', Config::$options['preload']['preload'] ?? array());
        self::$critical_images = apply_filters('perfmatters_preload_critical_images', Config::$options['preload']['critical_images'] ?? 0);

        if(!empty(self::$preloads) || !empty(self::$critical_images)) {
            add_action('perfmatters_output_buffer_template_redirect', array('Perfmatters\Preload', 'add_preloads'));
        }
    }

    //add preloads to html
    public static function add_preloads($html) {

        if(!empty(self::$critical_images)) {
            self::add_critical_image_preloads($html, Utilities::clean_html($html));
        }

        if(!empty(self::$preloads)) {

            $mime_types = array(
                'svg'   => 'image/svg+xml',
                'ttf'   => 'font/ttf',
                'otf'   => 'font/otf',
                'woff'  => 'font/woff',
                'woff2' => 'font/woff2',
                'eot'   => 'application/vnd.ms-fontobject',
                'sfnt'  => 'font/sfnt'
            );

            foreach(self::$preloads as $line) {

                //device type check
                if(!empty($line['device'])) {
                    $device_type = wp_is_mobile() ? 'mobile' : 'desktop';
                    if($line['device'] != $device_type) {
                        continue;
                    }
                }

                //location check
                if(!empty($line['locations'])) {

                    $location_match = false;

                    $exploded_locations = explode(',', $line['locations']);
                    $trimmed_locations = array_map('trim', $exploded_locations);

                    //single post exclusion
                    if(is_singular()) {
                        global $post;
                        if(in_array($post->ID, $trimmed_locations)) {
                            $location_match = true;
                        }
                    }
                    //posts page exclusion
                    elseif(is_home() && in_array('blog', $trimmed_locations)) {
                        $location_match = true;
                    }
                    elseif(is_archive()) {
                        //woocommerce shop check
                        if(function_exists('is_shop') && is_shop()) {
                            if(in_array(wc_get_page_id('shop'), $trimmed_locations)) {
                                $location_match = true;
                            }
                        }
                    }

                    if(!$location_match) {
                        continue;
                    }
                }

                $mime_type = "";

                if(!empty($line['as']) && $line['as'] == 'font') {
                    $path_info = pathinfo($line['url']);
                    $mime_type = !empty($path_info['extension']) && isset($mime_types[$path_info['extension']]) ? $mime_types[$path_info['extension']] : "";
                }

                //print script/style handle as preload
                if(!empty($line['as']) && in_array($line['as'], array('script', 'style'))) {
                    if(strpos($line['url'], '.') === false) {

                        global $wp_scripts;
                        global $wp_styles;

                        $scripts_arr = $line['as'] == 'script' ? $wp_scripts : $wp_styles;

                        if(!empty($scripts_arr)) {
                            $scripts_arr = $scripts_arr->registered;

                            if(array_key_exists($line['url'], $scripts_arr)) {

                                $url = $scripts_arr[$line['url']]->src;

                                $parsed_url = parse_url($scripts_arr[$line['url']]->src);
                                if(empty($parsed_url['host'])) {
                                    $url = site_url($url);
                                }

                                if(strpos($url, '?') === false) {

                                    $ver = $scripts_arr[$line['url']]->ver;

                                    if(empty($ver)) {
                                        $ver = get_bloginfo('version');
                                    }
                                }

                                $line['url'] = $url . (!empty($ver) ? '?ver=' . $ver : '');
                            }
                            else {
                                continue;
                            }
                        }
                        else {
                            continue;
                        }
                    }
                }

                $preload = "<link rel='preload' href='" . $line['url'] . "'" . (!empty($line['as']) ? " as='" . $line['as'] . "'" : "") . (!empty($mime_type) ? " type='" . $mime_type . "'" : "") . (!empty($line['crossorigin']) ? " crossorigin" : "") . (!empty($line['as']) && $line['as'] == 'style' ? " onload=\"this.rel='stylesheet';this.removeAttribute('onload');\"" : "") . ">";

                if($line['as'] == 'image') {
                    array_unshift(self::$preloads_ready, $preload);
                }
                else {
                    self::$preloads_ready[] = $preload;
                }
            }
        }

        if(!empty(self::$preloads_ready)) {
            $preloads_string = "";
            foreach(apply_filters('perfmatters_preloads_ready', self::$preloads_ready) as $preload) {
                $preloads_string.= $preload;
            }
            $pos = strpos($html, '</title>');
            if($pos !== false) {
                $html = substr_replace($html, '</title>' . $preloads_string, $pos, 8);
            }
        }
        
        return $html;
    }

    //add critical image preloads
    public static function add_critical_image_preloads(&$html, $clean_html) {

        //match all image formats
        preg_match_all('#(<picture.*?)?<img([^>]+?)\/?>(?><\/picture>)?#is', $clean_html, $matches, PREG_SET_ORDER);

        if(!empty($matches)) {

            $count = 0;
            
            foreach($matches as $match) {

                if($count >= self::$critical_images) {
                    break;
                }

                if(strpos($match[0], 'secure.gravatar.com') !== false) {
                    continue;
                }

                $exclusions = apply_filters('perfmatters_critical_image_exclusions', array());
                if(!empty($exclusions) && is_array($exclusions)) {
                    foreach($exclusions as $exclusion) {
                        if(strpos($match[0], $exclusion) !== false) {
                            continue 2;
                        }
                    }
                }

                //picture tag
                if(!empty($match[1])) {
                    preg_match('#<source([^>]+?image\/(webp|avif)[^>]+?)\/?>#is', $match[0], $source);

                    if(!empty($source)) {
                        if(self::generate_critical_image_preload($source[1])) {
                            $new_picture = str_replace('<picture', '<picture data-perfmatters-preload', $match[0]);
                            $new_picture = str_replace('<img', '<img data-perfmatters-preload', $new_picture);
                            $html = str_replace($match[0], $new_picture, $html);
                            $count++;
                        }
                        continue;
                    }
                }

                //img tag
                if(!empty($match[2])) {
                    if(self::generate_critical_image_preload($match[2])) {
                        $new_image = str_replace('<img', '<img data-perfmatters-preload', $match[0]);
                        $html = str_replace($match[0], $new_image, $html);
                        $count++;
                    }
                }
            }
        }

        if(!empty(self::$preloads_ready)) {
            ksort(self::$preloads_ready);
        }
    }

    //generate preload link from att string
    private static function generate_critical_image_preload($att_string) {
        if(!empty($att_string)) {
            $atts = Utilities::get_atts_array($att_string);
            $src = $atts['data-src'] ?? $atts['src'] ?? '';

            //dont add if src was already used
            if(in_array($src, self::$used_srcs)) {
                return false;
            }

            //generate preload
            self::$preloads_ready[] = '<link rel="preload" href="' . $src . '" as="image"' . (!empty($atts['srcset']) ? ' imagesrcset="' . $atts['srcset'] . '"' : '') . (!empty($atts['sizes']) ? ' imagesizes="' . $atts['sizes'] . '"' : '') . ' />';

            //mark src used and return
            self::$used_srcs[] = $src;
            return true;
        }
    }
}