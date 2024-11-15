<?php
/**
 * User: simon
 * Date: 20.11.2020
 */

namespace ShortPixel\AI;

class CacheCleaner
{
    private static $instance = false;
    private $logger;
    private $LOGGER_ON;

    /**
     * @param bool $refresh
     * @return CacheCleaner
     */
    public static function _()
    {
        if (self::$instance === false) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    protected function __construct()
    {
        $this->logger = \ShortPixelAILogger::instance();
        $this->LOGGER_ON = (SHORTPIXEL_AI_DEBUG & \ShortPixelAILogger::DEBUG_AREA_CACHE);
    }

    public function clear($message, $urls = false, $wpFlush = true) {
        $result = $message;
        $cache_cleared = false;

        $LOGGER_ON = $this->LOGGER_ON;
        $LOGGER_ON && $this->logger->log('CLEARING CACHE - urls: ', $urls);

        if($wpFlush) {
            wp_cache_flush();
        }

        if($urls && !is_array($urls)) {
            $urls = [$urls];
        }

        // Comet Cache
        try {
            if(class_exists('\comet_cache')) {
                if($urls) {
                    array_map(function ($item) {
                        \comet_cache::clearUrl($item);
                    }, $urls);
                } else {
                    \comet_cache::clear();
                }
                $LOGGER_ON && $this->logger->log('Comet cache cleared ' . json_encode($urls));
                $cache_cleared = true;
            }
        } catch(Throwable $t) {} catch(Exception $e) {}

        //WPRocket
        if(function_exists('rocket_clean_domain')) {
            $urls ? rocket_clean_files($urls) : rocket_clean_domain();
            $LOGGER_ON && $this->logger->log('WP Rocket cache cleared ' . json_encode($urls));
            $cache_cleared = true;
        }

        //W3 Total Cache
        if(function_exists('w3tc_flush_all')) {
            if($urls) {
                array_map(function ($item) {
                    w3tc_flush_url($item);
                }, $urls);
            } else {
                w3tc_flush_all();
            }
            $LOGGER_ON && $this->logger->log('W3TC cache cleared ' . json_encode($urls));
            $cache_cleared = true;
        }

        //Swift Performance cache
        if(class_exists('\Swift_Performance_Cache')) {
            if($urls) {
                array_map(function ($item) {
                    \Swift_Performance_Cache::clear_permalink_cache($item);
                }, $urls);
            } else {
                \Swift_Performance_Cache::clear_all_cache();
            }
            $LOGGER_ON && $this->logger->log('Swift Perf. cache cleared ' . json_encode($urls));
            $cache_cleared = true;
        }

        //Cache Enabler
        if(class_exists('\Cache_Enabler')) {
            if($urls) {
                array_map(function ($item) {
                    \Cache_Enabler::clear_page_cache_by_url($item);
                }, $urls);
            } else {
                \Cache_Enabler::clear_complete_cache();
            }
            $LOGGER_ON && $this->logger->log('Cache Enabler cache cleared ' . json_encode($urls));
            $cache_cleared = true;
        }

        //WP Optimize
        if(class_exists('\WPO_Page_Cache')) {
            if($urls) {
                array_map(function ($item) {
                    \WPO_Page_Cache::delete_cache_by_url($item);
                }, $urls);
            } else {
                \WP_Optimize()->get_page_cache()->purge();
            }
            $LOGGER_ON && $this->logger->log('WPOptimize cache cleared ' . json_encode($urls));
            $cache_cleared = true;
        }

        //LiteSpeed cache
        if(class_exists('\LiteSpeed\Purge')) {
            if($urls) {
                array_map(function ($item) {
                    (new \LiteSpeed\Purge())->purge_url($item);
                }, $urls);
            } else {
                \LiteSpeed\Purge::purge_all();
            }
            $LOGGER_ON && $this->logger->log('LiteSpeed cache cleared ' . json_encode($urls));
            $cache_cleared = true;
        }

        //WP Super Cache
        if(function_exists('wpsc_delete_url_cache')) {
            if($urls) {
                array_map(function ($item) {
                    wpsc_delete_url_cache($item);
                }, $urls);
            } else {
                wp_cache_clear_cache();
            }
        }

        //Breeze cache
        if(class_exists('Breeze_PurgeCache')) {
            if($urls) {
                array_map(function ($item) use($cache_cleared) {
                    $postId = url_to_postid($item);
                    if($postId) {
                        \Breeze_PurgeCache::purge_post_on_update($postId);
                        $cache_cleared = true;
                    }
                }, $urls);
            } else {
                \Breeze_PurgeCache::breeze_cache_flush();
                $cache_cleared = true;
            }
        }

        //WP Fastest Cache

        //Generic cache, search for cache folders in wp-content/cache - only if we have a list of URLs otherwise we could delete too many things...
        if(!$cache_cleared && $urls) {
            $cache_parent = WP_CONTENT_DIR . '/cache/';
            $caches = @scandir($cache_parent);
            $LOGGER_ON && $this->logger->log('Generic caches: ' . json_encode($caches));
            if($caches) foreach($caches as $cache) {
                if ($cache == '.' || $cache == '..') continue;
                if(is_dir($cache_parent . $cache)) {
                    foreach($urls as $url) {
                        $parsed = parse_url($url);
                        $domain = isset($parsed['domain']) ? $parsed['domain'] : '';
                        $path = isset($parsed['path']) ? $parsed['path'] : '';
                        if(!($cache_cleared = $this->deleteHtmlFiles($cache_parent . $cache . DIRECTORY_SEPARATOR . $domain . $path))) {
                            $cache_cleared = $this->deleteHtmlFiles($cache_parent . $cache . DIRECTORY_SEPARATOR . $path);
                        }
                    }
                }
            }
        }

        if($cache_cleared) {
            $result .= ' ' . __( 'Please press OK to refresh the page.', 'shortpixel-adaptive-images' );
        }
        else {
            $result .= "\n" . __( 'Please clear all page cache levels from the WP admin and CDN (if you have one), then press REFRESH to reload the page.', 'shortpixel-adaptive-images' );
        }
        return $result;
    }

    protected function deleteHtmlFiles($cache_path) {
        $counter = 0;
        $cached_pages = @scandir($cache_path);
        $this->LOGGER_ON && $this->logger->log('PATH to clear cache: ' . $cache_path . ' contains: ' . json_encode($cached_pages));
        if($cached_pages) foreach ($cached_pages as $cp) {
            if ($cp == '.' || $cp == '..') continue;
            if(preg_match('/\.html?$/', $cp)) {
                $counter += @unlink(trailingslashit($cache_path) . $cp );
            }
        }
        return $counter;
    }

    public function excludeCurrentPage()
    {
        global $wp;
        $currentUrl = home_url( add_query_arg( array(), $wp->request ) );

        //WPRocket
        add_filter( 'rocket_cache_reject_uri', function() use ($currentUrl){
            return [$currentUrl];
        });

        //W3 Total Cache, WP Optimize and WP Super Cache
        if(!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }

        //Swift Performance cache
        add_filter('swift_performance_is_cacheable', function() {
            return false;
        });

        //Cache Enabler
        add_filter('bypass_cache', function() {
            return false;
        });

        //LiteSpeed cache
        define('LSCACHE_NO_CACHE', true);

        //WPFC
        if(function_exists('wpfc_exclude_current_page')) {
            wpfc_exclude_current_page();
        }
    }
}
