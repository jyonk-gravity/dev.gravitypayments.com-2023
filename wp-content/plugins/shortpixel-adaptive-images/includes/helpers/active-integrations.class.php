<?php
/**
 * User: shortpixel
 * Date: 2020-08-01
 */

namespace ShortPixel\AI;

class ActiveIntegrations {
    private static $instance = false;
    private $integrations = [];

    /**
     * @param bool $refresh
     * @return ActiveIntegrations
     */
    public static function _($refresh = false) {
        if(self::$instance === false || $refresh) {
            \ShortPixelAILogger::instance()->log("INTEGRATIONS - constructing ");
            self::$instance = new ActiveIntegrations();
        }
        return self::$instance;
    }

    protected function __construct() {
        $activePlugins = (array) apply_filters('active_plugins', get_option( 'active_plugins', array()));
        if ( is_multisite() ) {
            $activePlugins = array_merge($activePlugins, array_keys(get_site_option( 'active_sitewide_plugins')));
        }
        //test WPRocket
        if (in_array('wp-rocket/wp-rocket.php', $activePlugins)) {
            $path = SHORTPIXEL_AI_WP_PLUGINS_DIR . '/wp-rocket/wp-rocket.php';
            $pluginVersion = self::readPluginVersion($path);
            $wpRocketSettings = get_option('wp_rocket_settings', array());

            $hasCssFilter = (version_compare($pluginVersion, '3.4.3') >= 0);
            $hasLazyFilter = version_compare( $pluginVersion, '2.5', '>=' ); // does WP Rocket have the hook for disabling lazy loading?
            $hasSafeMode = version_compare( $pluginVersion, '3.8', '<' ); // does WP Rocket have the option to enable safe mode for jQuery? Removed in 3.8

            //if the lazy-load videos is active, do not optimize this placeholder: https://i.ytimg.com/vi/ID/hqdefault.jpg
            if(isset($wpRocketSettings['lazyload_youtube']) && $wpRocketSettings['lazyload_youtube'] == 1) {

            }

            $rocket = array(
                'lazyload'   => !$hasLazyFilter && ( isset( $wpRocketSettings[ 'lazyload' ] ) && $wpRocketSettings[ 'lazyload' ] == 1 ),
                'css-filter' => $hasCssFilter,
                'minify-css' => isset( $wpRocketSettings[ 'minify_css' ] ) && $wpRocketSettings[ 'minify_css' ] == 1,
                'defer-all-js' => $hasSafeMode && !!$wpRocketSettings[ 'defer_all_js' ] && !$wpRocketSettings[ 'defer_all_js_safe' ],
                'video-placeholder' => (isset($wpRocketSettings['lazyload_youtube']) && $wpRocketSettings['lazyload_youtube'] == 1),
            );

        } else {
            $rocket = array('lazyload' => false, 'css-filter' => false, 'minify-css' => false, 'defer-all-js' => false, 'video-placeholder' => false);
        }

        // Swift Performance
        $swift_performance = array(
            'lite' => array(
                'active' => in_array( 'swift-performance-lite/performance.php', $activePlugins ),
                'path'   => WP_CONTENT_DIR . '/plugins/swift-performance-lite/performance.php',
            ),
            'pro'  => array(
                'active' => in_array( 'swift-performance/performance.php', $activePlugins ),
                'path'   => WP_CONTENT_DIR . '/plugins/swift-performance/performance.php',
            ),
        );

        foreach ( $swift_performance as $plugin => $info ) {
            if ( !$info[ 'active' ] ) {
                continue;
            }

            $swift_performance[ $plugin ][ 'version' ] = defined( 'SWIFT_PERFORMANCE_VER' ) ? SWIFT_PERFORMANCE_VER : self::readPluginVersion( $info[ 'path' ] );
            $swift_performance[ $plugin ][ 'has_bug' ] = version_compare( $swift_performance[ $plugin ][ 'version' ], $plugin === 'lite' ? '2.1.2' : '2.1.6.2', $plugin === 'lite' ? '<=' : '<' );

            switch ( $plugin ) {
                case 'lite' :
                    if ( class_exists( 'Swift_Performance_Lite' ) ) {
                        $swift_performance[ $plugin ][ 'has_conflict' ] = !!\Swift_Performance_Lite::get_option( 'normalize-static-resources' )
                            && !!\Swift_Performance_Lite::get_option( 'merge-styles' );

                        $swift_performance[ $plugin ][ 'merge_styles' ] = !!\Swift_Performance_Lite::get_option( 'merge-styles' );
                    }
                    break;

                case 'pro' :
                    if ( class_exists( 'Swift_Performance' ) ) {
                        $swift_performance[ $plugin ][ 'has_conflict' ] = !!\Swift_Performance::get_option( 'normalize-static-resources' )
                            && !!\Swift_Performance::get_option( 'merge-styles' );

                        $swift_performance[ $plugin ][ 'merge_styles' ] = !!\Swift_Performance::get_option( 'merge-styles' );
                    }
                    break;
            }

            $swift_performance = $swift_performance[ $plugin ];

            $swift_performance[ 'plugin' ] = $plugin;
        }

        // Imagify
        $imagify = [
            'active' => in_array( 'imagify/imagify.php', $activePlugins ),
        ];

        if ( function_exists( 'get_imagify_option' ) ) {
            $imagify[ 'has_conflict' ] = !!get_imagify_option( 'display_webp' );
        }

        // WP Fastest Cache
        $wp_fastest_cache = false;

        if ( in_array( 'wp-fastest-cache/wpFastestCache.php', $activePlugins ) ) {
            $path    = WP_PLUGIN_DIR . '/wp-fastest-cache/wpFastestCache.php';
            $version = self::readPluginVersion( $path );

            if ( version_compare( $version, '0.9.0.7', '>=' ) ) {
                if ( class_exists( '\WpFastestCacheCreateCache' ) ) {
                    $wpfc = new \WpFastestCacheCreateCache;

                    if ( property_exists( $wpfc, 'options' ) && is_object( $wpfc->options ) ) {
                        if (
                            ( property_exists( $wpfc->options, 'wpFastestCacheStatus' ) && !!$wpfc->options->wpFastestCacheStatus )
                            && ( ( property_exists( $wpfc->options, 'wpFastestCacheMinifyCss' ) && !!$wpfc->options->wpFastestCacheMinifyCss )
                                || ( property_exists( $wpfc->options, 'wpFastestCacheCombineCss' ) && !!$wpfc->options->wpFastestCacheCombineCss ) )
                        ) {
                            $wp_fastest_cache = true;
                        }
                        else {
                            $wp_fastest_cache = false;
                        }
                    }
                }
                // condition for pre-init or admin pages because class WpFastestCacheCreateCache exists after "admin_init" and "init" hooks
                else {
                    $wp_fastest_cache = true;
                }
            }
        }

        // W3 Total Cache
        $w3_total_cache = false;

        if ( in_array( 'w3-total-cache/w3-total-cache.php', $activePlugins ) ) {
            $path    = WP_PLUGIN_DIR . '/w3-total-cache/w3-total-cache.php';
            $version = self::readPluginVersion( $path );

            if ( version_compare( $version, '0.14.0', '>=' ) ) {
                $w3_total_cache = true;
            }
        }

        // WP Optimize
        $wp_optimize = ['active' => false, 'enable_css' => false, 'enable_merging_of_css' => false];

        if ( in_array( 'wp-optimize/wp-optimize.php', $activePlugins ) ) {
            $wp_optimize['active'] = true;
            if ( function_exists( 'wp_optimize_minify_config' ) ) {
                $config = wp_optimize_minify_config();

                if ( method_exists( $config, 'get' ) ) {
                    $config = $config->get();

                    if ( is_array( $config ) ) {
                        $has_ignore_list = false;
                        $has_exclusion   = true;

                        if ( isset( $config[ 'ignore_list' ] ) && is_string( $config[ 'ignore_list' ] ) ) {
                            $has_ignore_list = true;

                            $plugin_folder = plugin_basename( SHORTPIXEL_AI_PLUGIN_DIR );

                            $conflicting_files = [
                                '/' . $plugin_folder . '/assets/css/admin.css',
                                '/' . $plugin_folder . '/assets/css/admin.min.css',
                                '/' . $plugin_folder . '/assets/css/style-bar.css',
                                '/' . $plugin_folder . '/assets/css/style-bar.min.css',
                            ];

                            foreach ( $conflicting_files as $file ) {
                                if ( strpos( $config[ 'ignore_list' ], $file ) === false ) {
                                    $has_exclusion = false;
                                    break;
                                }
                            }
                        }
                        if ( isset( $config[ 'enable_css' ] ) && !!$config[ 'enable_css' ] ){
                            $wp_optimize['enable_css'] = true;
                        }
                        if (
                            isset( $config[ 'enable_css' ] ) && isset( $config[ 'enable_merging_of_css' ] )
                            && ( !!$config[ 'enable_css' ] && !!$config[ 'enable_merging_of_css' ] )
                            && ( $has_ignore_list && !$has_exclusion )
                        ) {
                            $wp_optimize[ 'enable_merging_of_css' ] = true;
                        }
                    }
                }
            }
        }

        $this->integrations = array(
            'nextgen' => in_array('nextgen-gallery/nggallery.php', $activePlugins),
            'modula' => in_array('modula-best-grid-gallery/Modula.php', $activePlugins),
            'elementor' => in_array('elementor/elementor.php', $activePlugins),
            'elementor-addons' => in_array('essential-addons-for-elementor/essential_adons_elementor.php', $activePlugins)
                               || in_array('essential-addons-for-elementor-lite/essential_adons_elementor.php', $activePlugins),
            'viba-portfolio' => in_array('viba-portfolio/viba-portfolio.php', $activePlugins),
            'envira' => in_array('envira-gallery/envira-gallery.php', $activePlugins) || in_array('envira-gallery-lite/envira-gallery-lite.php', $activePlugins),
            'everest' => in_array('everest-gallery/everest-gallery.php', $activePlugins) || in_array('everest-gallery-lite/everest-gallery-lite.php', $activePlugins),
            'wp-bakery' => in_array('js_composer/js_composer.php', $activePlugins), //WP Bakery (testimonials)
            'woocommerce' => in_array('woocommerce/woocommerce.php' , $activePlugins),
            'wpc-variations' => in_array('wpc-variations-radio-buttons/wpc-variations-radio-buttons.php', $activePlugins),
            'foo' => in_array('foogallery/foogallery.php', $activePlugins),
            'global-gallery' => in_array('global-gallery/global-gallery.php', $activePlugins),
            'essential-grid' => in_array('essential-gridv1-9/essential-grid.php', $activePlugins),
            'oxygen' => in_array( 'oxygen/functions.php', $activePlugins),
            'slider-revolution' => in_array('revslider/revslider.php', $activePlugins),
            'custom-facebook-feed' => in_array('custom-facebook-feed-pro/custom-facebook-feed.php', $activePlugins),
            'smart-slider' => in_array('smart-slider-3/smart-slider-3.php', $activePlugins) || in_array('nextend-smart-slider3-pro/nextend-smart-slider3-pro.php', $activePlugins),
            'real3d-flipbook' => in_array('real3d-flipbook/real3d-flipbook.php', $activePlugins),
            'wp-grid-builder' => in_array('wp-grid-builder/wp-grid-builder.php', $activePlugins),
            'beaver-builder' => in_array('bb-plugin/fl-builder.php', $activePlugins) || in_array('beaver-builder-lite-version/fl-builder.php', $activePlugins),
            'the-grid' => in_array('the-grid/the-grid.php', $activePlugins),
            'social-pug' => in_array('social-pug/index.php', $activePlugins), //Mediavine Grow
            'instagram-feed' => in_array('instagram-feed/instagram-feed.php', $activePlugins),
            'insta-gallery' => in_array('insta-gallery/insta-gallery.php', $activePlugins), //Social Feed Gallery
            'content-views' => in_array( 'content-views-query-and-display-post-page/content-views.php', $activePlugins),
            'featherlight' => in_array('wp-featherlight/wp-featherlight.php', $activePlugins), //Featherlight lightbox
            'lightbox-photoswipe' => in_array('lightbox-photoswipe/lightbox-photoswipe.php', $activePlugins),
            'acf' => in_array('advanced-custom-fields-pro/acf.php', $activePlugins) || in_array('advanced-custom-fields/acf.php', $activePlugins),
            'soliloquy' => in_array('soliloquy/soliloquy.php', $activePlugins),
            'jetpack' => in_array('jetpack/jetpack.php', $activePlugins),
            'wp-rocket' => $rocket,
            'perfmatters' => in_array('perfmatters/perfmatters.php', $activePlugins),
            'swift-performance' => isset( $swift_performance[ 'active' ] ) ? $swift_performance : false,
            'imagify' => isset( $imagify[ 'active' ] ) ? $imagify : false,
            'wp-fastest-cache' => $wp_fastest_cache,
            'litespeed-cache' => in_array('litespeed-cache/litespeed-cache.php', $activePlugins) && (get_option('litespeed.conf.optm-css_min', false) || get_option('litespeed.conf.optm-css_comb', false)),
            'w3-total-cache' => $w3_total_cache,
            'wp-super-cache' => in_array('wp-super-cache/wp-cache.php', $activePlugins),
            'wp-optimize' => $wp_optimize,
            'breeze' => in_array( 'breeze/breeze.php', $activePlugins),
            'smart-cookie-kit' => in_array( 'smart-cookie-kit/plugin.php', $activePlugins),
            'wpzoom-theme' => class_exists('WPZOOM'),
            //modules that are included in themes for example, not standalone plugins
            'avia-gallery-module' => class_exists( 'avia_sc_gallery' ),
            //Deactivated, because the product is no longer added to chart excludedif the URL is changed. Keeping it here in case some other customer appears...
            //'visual-product-configurator' => in_array( 'visual-product-configurator/vpc.php', $activePlugins), //HS#42286
        );
        //test theme. 'Jupiter' 'CROWD 2'
        $theme = wp_get_theme();
        $this->integrations['theme'] = $theme->Name;
        if(SHORTPIXEL_AI_DEBUG) {
            //integration forced from the request parameters
            foreach($this->integrations as $key => $val) {
                if(isset($_REQUEST['spai_force_' . $key])) {
                    $this->integrations[$key] = true;
                }
            }
        }
    }

    public function themeIs($name) {
        return strpos($this->integrations['theme'], $name) === 0;
    }

    public function has($plugin, $attr = false) {
        return isset( $this->integrations[ $plugin ] )
            && ($attr ? isset($this->integrations[$plugin][$attr]) && !! $this->integrations[$plugin][$attr]
                      : !!$this->integrations[ $plugin ]);
    }

    public function get($plugin, $attr = false) {
        return
            isset( $this->integrations[ $plugin ] )
            ? ($attr && isset($this->integrations[ $plugin ][$attr])
                ? $this->integrations[ $plugin ][$attr]
                : $this->integrations[ $plugin ])
            : false;
    }

    public function getAll() {
        return $this->integrations;
    }

    public function getUseFirstSizes() {
        //if woocommerce is active, the following elements have to be forced to the size of the first case (because the first is displayed as larger image and the others get to that same size when selected)
        return $this->integrations['woocommerce'] ? ['div.woocommerce-product-gallery__image > a > img' => ['width' => 0, 'height' => 0]] : [];
    }

    public static function readPluginVersion( $path ) {
        $ver = '0.0';

        if ( file_exists( $path ) ) {
            $fp = fopen( $path, 'r' );
            for ( $i = 0; $i < 100 && !feof( $fp ); $i++ ) {
                $line = trim( fgets( $fp ) );
                if ( strpos( $line, 'Version:' ) !== false ) {
                    $version = explode( 'Version:', $line );
                    $ver     = trim( end( $version ) );
                    break;
                }
            }
            fclose( $fp );
        }

        return $ver;
    }
}
