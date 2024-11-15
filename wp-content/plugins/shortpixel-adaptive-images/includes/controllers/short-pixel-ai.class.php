<?php
	use ShortPixel\AI\Page;
	use ShortPixel\AI\LQIP;
	use ShortPixel\AI\Help;
	use ShortPixel\AI\Notice;
	use ShortPixel\AI\Options;
	use ShortPixel\AI\Request;
	use ShortPixel\AI\Feedback;
	use \ShortPixel\AI\ActiveIntegrations;

class ShortPixelAI {
    const DEFAULT_MAIN_DOMAIN = 'https://shortpixel.com';
    const DEFAULT_API_AI = 'https://cdn.shortpixel.ai';
    const DEFAULT_STATUS_AI = 'https://cdn.shortpixel.ai';
    const DEFAULT_API_AI_PATH = '/spai';
    const SP_API = 'https://api.shortpixel.com/';
    const AI_JS_VERSION = '2.0';
    const ONE_CREDIT_IN_TRAFFIC = 5242880;
    const SEP = '+'; //can be + or ,
    const LOG_NAME = 'shortpixel-ai.log';
	const ACCOUNT_CHECK_SCHEDULE = array( 'name' => 'spai_account_check_event', 'recurrence' => 'twicedaily', );
    public static $SHOW_STOPPERS = array('ao', 'avadalazy', 'ginger');
    public static $excludedAjaxActions = array(
        //Add Media popup     Image to editor              Woo product variations
        'query-attachments', 'send-attachment-to-editor', 'woocommerce_load_variations',
        //avia layout builder AJAX calls
        'avia_ajax_text_to_interface', 'avia_ajax_text_to_preview',
        //My Listing theme
        'mylisting_upload_file',
        //Oxygen stuff
        'ct_get_components_tree', 'ct_exec_code',
        //Zion builder
        'znpb_render_module'
    );

    const THUMBNAIL_REGEX = "/(-[0-9]+x[0-9]+)\.([a-zA-Z0-9]+)$/";
	const GRAVATAR_REGEX = "regex:/\/\/([^\/]*\.|)gravatar.com\//";

	public $options;
	public $settings;
    public $exclusions;

    public $cssCacheVer;

    public $lazyNoticeThrown = false;
    public $affectedTags;

    public $blankInlinePlaceholders = [];

    /**
     * @var $instance
     */
    private $file;
    public $basename;
    public $plugin_dir;
    public $plugin_url;

    private static $instance;
    private $doingAjax = false;

    private $conflict = false;
    private $spaiJSDequeued = false;

    private $logger = false;
    private $parser = false;
    private $cssParser = false;

    private $domainStatus = false;
    private $cdnUsage = false;

    public $varyCacheSupport = false;

    /**
     * @return ShortPixelRegexParser
     */
    public function getRegexParser() {
        return $this->parser;
    }

    /**
     * @return bool|ShortPixelCssParser
     */
    public function getCssParser()
    {
        return $this->cssParser;
    }

	public static function isAjax() {
		return function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * Method checks is user logged in and has capability
	 *
	 * @param string            $capability WP user capabilities
	 * @param \WP_User|int|null $user       if null would be used current user ID
	 *
	 * @return bool
	 */
	public static function userCan( $capability, $user = null ) {
		$user = $user instanceof WP_User ? $user : ( is_int( $user ) && $user > 0 ? get_user_by( 'id', $user ) : wp_get_current_user() );

		if ( !$user instanceof WP_User ) {
			return false;
		}

		return $user->exists() && user_can( $user, $capability );
	}

    /**
     * Make sure only one instance is running.
     */
	public static function _() {
		if ( !isset ( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

    private function __construct() {
        load_plugin_textdomain('shortpixel-adaptive-images', false, plugin_basename(dirname(SHORTPIXEL_AI_PLUGIN_FILE)) . '/lang');

        $this->logger = ShortPixelAILogger::instance();
        $this->options = Options::_();
        $this->settings = $this->options->settings;
        $this->exclusions = $this->getExclusionsMap();
        $this->logger->log("EXCLUSIONS MAP FROM SETTINGS:", $this->exclusions);

        //$parser = new ShortPixelRegexParser($this);
        //$parser = new ShortPixelDomParser($this);
        $this->cssParser = new ShortPixelCssParser($this);
        //$this->parser = new ShortPixelSimpleDomParser($this);
        $this->parser = new ShortPixelRegexParser($this);

        //The recorded affected tags are from pieces of content that are loaded after the page, for example AJAX content. The first time the image will be blank but at second load OK
        $this->affectedTags = new \ShortPixel\AI\AffectedTags();

        $this->doingAjax = self::isAjax();
		$this->setup_globals();
		$this->include_libs();
        $this->setup_hooks();
    }

    public function init_ob() {
        if ($this->isWelcome()) {
            SHORTPIXEL_AI_DEBUG && $this->logger->log('WILL PARSE ' . $_SERVER['REQUEST_URI'] . ' CALLED BY ' . @$_SERVER['HTTP_REFERER']
                . ($this->doingAjax ? ' - AJAX CALL PARAMS: ' . json_encode($_REQUEST) : ''));
            //remove srcset and sizes param
			add_filter( 'wp_calculate_image_srcset', array( $this, 'replace_image_srcset' ), 10, 5 );

            $integrations = ActiveIntegrations::_( true );

            // action to change urls in the Elementor's autogenerated css
	        if (   $integrations->has('elementor') && $this->settings->behaviour->nojquery <= 0
                // && !$this->settings->areas->parse_css_files //deactivate this condition as Elementor circumveits for example WP Rocket's CSS cache.
                && get_option( 'elementor_css_print_method', false ) === 'external' ) {
                $this->logger->log('SETUP: ELEMENTOR CSS');
				add_action( 'elementor/element/parse_css', array( $this, 'parse_elementor_css' ), 10, 2 );
			}

	        if ( $integrations->has('nextgen') ) {
                $this->logger->log('SETUP: NGG LIGHTBOX');
		        add_filter( 'ngg_pro_lightbox_images_queue', [ $this, 'parseNextGenEntities' ] );
	        }

	        $wpRocket = $integrations->get('wp-rocket');
	        if ( $this->settings->areas->parse_css_files > 0 && $wpRocket[ 'minify-css' ] && $wpRocket[ 'css-filter' ] ) {
		        $this->logger->log('SETUP: WP ROCKET CSS FILTER');
		        // if WP Rocket is active and the css option is on and the version is >=3.4 we can use its cache to store the changed CSS
		        add_filter( 'rocket_css_content', [ $this, 'parse_cached_css' ], 10, 3 );
	        }

	        if ( $this->settings->areas->parse_css_files > 0 && $integrations->has( 'wp-fastest-cache' ) ) {
		        $this->logger->log( 'SETUP: WP FASTEST CACHE CSS FILTER' );

		        add_filter( 'wpfc_css_content', [ $this, 'parse_cached_css' ], 10, 3 );
	        }

	        if ( $this->settings->areas->parse_css_files > 0 && $integrations->has( 'w3-total-cache' ) ) {
		        $this->logger->log( 'SETUP: W3 TOTAL CACHE CSS FILTER' );

		        add_filter( 'w3tc_minify_css_content', [ $this, 'parse_cached_css' ], 10, 3 );
	        }

            if ( $this->settings->areas->parse_css_files > 0 && $integrations->has( 'wp-optimize', 'enable_css' ) ) {
                $this->logger->log( 'SETUP: WP Optimize CSS FILTER' );
                //WP Optimize
                add_filter( 'wpo_minify_get_css', [ $this, 'parse_cached_css_wpo' ], 10, 3 );
            }

            if ( $this->settings->areas->parse_css_files > 0 && $integrations->has( 'litespeed-cache' ) ) {
		        $this->logger->log( 'SETUP: LITESPEED CACHE CSS FILTER' );

		        // TODO: test these hooks
		        add_filter( 'litespeed_css_serve', [ $this, 'parse_cached_css' ], 10, 4 );
		        add_filter( 'litespeed_optm_cssjs', [ $this, 'parse_cached_css' ], 10, 3 );
	        }

            if ( $integrations->has( 'slider-revolution' ) ) {
                $this->settings->exclusions->excluded_paths .= (strlen($this->settings->exclusions->excluded_paths) ? PHP_EOL : '')
                    . "path:/revslider/public/assets/assets/transparent.png";
            }
            if ( $integrations->has( 'custom-facebook-feed' ) ) {
                $this->settings->exclusions->excluded_paths .=  (strlen($this->settings->exclusions->excluded_paths) ? PHP_EOL : '')
                    . "path:/custom-facebook-feed-pro/img/placeholder.png";
            }
            if ( $integrations->has( 'smart-cookie-kit' ) ) {
                $this->settings->exclusions->excluded_paths .=  (strlen($this->settings->exclusions->excluded_paths) ? PHP_EOL : '')
                    . "path:/smart-cookie-kit/res/empty.gif";
            }
            if ( $wpRocket['video-placeholder'] ) {
                //This is a template for the youtube video images. If replacing lazily in JS blocks, it replaces it and this breaks the video placeholders of WP rocket.
                //It doesn't make sense to serve from CDN either as youtube does its own thing about this.
                $this->settings->exclusions->excluded_paths .=  (strlen($this->settings->exclusions->excluded_paths) ? PHP_EOL : '')
                    . "path://i.ytimg.com/vi/ID/hqdefault.jpg";
            }
            if( $integrations->has( 'instagram-feed' ) || $integrations->has( 'insta-gallery' ) || $integrations->has('essential-grid')) {
                $this->exclusions->excluded_paths[] = "domain:cdninstagram.com";
            }

            if($integrations->themeIs('Jupiter')) {
                $this->exclusions->eager_selectors[] = 'img[data-mk-image-src-set]';
            }

            if( $integrations->has( 'perfmatters' )) {
                $this->logger->log( 'PERFMATTERS PRELOAD IS EAGER.');
                $this->exclusions->eager_selectors[] = 'img[data-perfmatters-preload]';
            }

            $swiftPerf = $integrations->get('swift-performance');
	        if (
		        $this->settings->areas->parse_css_files > 0 && !empty( $swiftPerf ) && !empty( $swiftPerf[ 'merge_styles' ] )
		        && isset( $swiftPerf[ 'plugin' ] ) && $swiftPerf[ 'plugin' ] === 'pro'
	        ) {
		        add_filter( 'swift_performance_critical_css_content', function( $critical_css ) {
			        $this->logger->log( 'SWIFT PERFORMANCE (CRITICAL) CSS FILTER' );

			        // try to replace the background images with our CDN
			        $critical_css = $this->parse_cached_css( $critical_css, null, null );

			        return $critical_css;
		        }, 10, 1 );

		        add_filter( 'swift_performance_css_content', function( $css_content, $key ) {
			        $this->logger->log( 'SWIFT PERFORMANCE (REGULAR) CSS FILTER' );

			        // try to replace the background images with our CDN
			        $css_content = $this->parse_cached_css( $css_content, null, null );

			        return $css_content;
		        }, 10, 2 );
	        }

			// add a hook to the Rocket's init 'wp' filter
			add_filter( 'wp', array( $this, 'disableRocketLazy' ), 1 );

            (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_HTML) && $this->logger->log("STARTING OUTPUT BUFFERING.");
            ob_start(array($this, 'maybe_replace_images_src'));
        } elseif(defined('SHORTPIXEL_AI_CLEANUP')) {
            $this->logger->log("CLEANUP " . $_SERVER['REQUEST_URI']);
            ob_start(array($this, 'maybe_cleanup'));
        } else {
            $this->logger->log("WON'T PARSE " . $_SERVER['REQUEST_URI']);
        }
    }

	/**
	 * Method adds filter do_rocket_lazyload to disable the WP Rocket's lazy loading
	 * @since 1.8.1
	 */
	public function disableRocketLazy() {
		add_filter( 'do_rocket_lazyload', '__return_false', 1 );
	}

	/**
	 * Method parses NextGen Gallery Entities to replace image URLs with placeholders
	 *
	 * @param array $entities
	 *
	 * @return array
	 */
	public function parseNextGenEntities( $entities ) {
		$return        = [];

		if ( !empty( $entities ) && is_array( $entities ) ) {
			foreach ( $entities as $entity ) {
                $sizes = ShortPixelUrlTools::get_image_size($entity[ 'image' ]);
                $entity[ 'image' ]                      = ShortPixelUrlTools::generate_placeholder_svg(isset($sizes[0]) ? $sizes[0]: false, isset($sizes[1]) ? $sizes[1]: false,
                                                          $entity[ 'image' ]);

                $sizes = ShortPixelUrlTools::get_image_size($entity[ 'full_image' ]);
                $entity[ 'full_image' ]                 = ShortPixelUrlTools::generate_placeholder_svg(isset($sizes[0]) ? $sizes[0]: false, isset($sizes[1]) ? $sizes[1]: false,
                                                          $entity[ 'full_image' ]);

                $sizes = ShortPixelUrlTools::get_image_size($entity[ 'thumb' ]);
                $entity[ 'thumb' ]                      = ShortPixelUrlTools::generate_placeholder_svg(isset($sizes[0]) ? $sizes[0]: false, isset($sizes[1]) ? $sizes[1]: false,
                                                          $entity[ 'thumb' ]);

                $sizes = ShortPixelUrlTools::get_image_size($entity[ 'srcsets' ][ 'hdpi' ]);
                $entity[ 'srcsets' ][ 'hdpi' ]          = ShortPixelUrlTools::generate_placeholder_svg(isset($sizes[0]) ? $sizes[0]: false, isset($sizes[1]) ? $sizes[1]: false,
                                                          $entity[ 'srcsets' ][ 'hdpi' ]);

                $sizes = ShortPixelUrlTools::get_image_size($entity[ 'srcsets' ][ 'original' ]);
                $entity[ 'srcsets' ][ 'original' ]      = ShortPixelUrlTools::generate_placeholder_svg(isset($sizes[0]) ? $sizes[0]: false, isset($sizes[1]) ? $sizes[1]: false,
                                                          $entity[ 'srcsets' ][ 'original' ]);

                $sizes = ShortPixelUrlTools::get_image_size($entity[ 'full_srcsets' ][ 'hdpi' ]);
                $entity[ 'full_srcsets' ][ 'hdpi' ]     = ShortPixelUrlTools::generate_placeholder_svg(isset($sizes[0]) ? $sizes[0]: false, isset($sizes[1]) ? $sizes[1]: false,
                                                          $entity[ 'full_srcsets' ][ 'hdpi' ]);

                $sizes = ShortPixelUrlTools::get_image_size($entity[ 'full_srcsets' ][ 'original' ]);
                $entity[ 'full_srcsets' ][ 'original' ] = ShortPixelUrlTools::generate_placeholder_svg(isset($sizes[0]) ? $sizes[0]: false, isset($sizes[1]) ? $sizes[1]: false,
                                                          $entity[ 'full_srcsets' ][ 'original' ]);

				$return[] = $entity;
			}
		}

		$this->logger->log( 'NEXTGEN ENTITIES: ' . var_export( $return, true ) );

		return $return;
	}

	/**
	 * Method regenerates Elementor's CSS files for posts
	 */
	public function regenerateElementorsCSS() {
		if ( class_exists( 'Elementor\Core\Files\Manager' ) ) {
			$elementor_files_manager = new Elementor\Core\Files\Manager();

			if ( method_exists( $elementor_files_manager, 'clear_cache' ) ) {
				$elementor_files_manager->clear_cache();
			}
		}
	}

	/**
	 * Method integrates SPAI with Elementor's CSS Print method
	 *
	 * @param \Elementor\Core\DynamicTags\Dynamic_CSS $post_css
	 * @param \Elementor\Element_Base                 $element
	 */
	public function parse_elementor_css( $post_css, $element ) {
		try {
			$reflection = new \ReflectionClass( $element );
			$class_name = $reflection->getName();

		}
		catch ( \ReflectionException $exception ) {
			$class_name = get_class( $element );
		}

        (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_CSS) && $this->logger->log("HANDLING ELEMENTOR CSS CLASS " . $class_name);

        /**
		 * Temporary fix until Elementor Pro fixes
         */

        if(defined('SPAI_ELEMENTOR_WORKAROUND')) {
            $contains_bug = [
                'Elementor\Widget_Image',
                'Elementor\Widget_Heading',
                'ElementorPro\Modules\GlobalWidget\Widgets\Global_Widget',
                'Elementor\Widget_Spacer',
                'Elementor\Element_Column',
                'Elementor\Element_Section',
            ];

            if ( in_array( $class_name, $contains_bug ) ) {
                (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_CSS) && $this->logger->log("NOT PARSING ELEMENTOR CSS, BUGGY CLASS " . $class_name, $element->get_raw_data());
                return;
            }
        }

        /*  TBD  if(!method_exists($element, 'get_raw_data')) {
            (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_CSS) && $this->logger->log("ELEMENTOR CSS CLASS LACKS get_raw_data", $element);
            return;
        }*/
		// TBD $element_raw      = $element->get_raw_data();
        $element_selector = $element->get_unique_selector();
        (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_CSS) && $this->logger->log("PARSING ELEMENTOR CSS DATA (elm.selector: $element_selector): ");

        $post_stylesheet = $post_css->get_stylesheet();

		$api_url            = $this->get_api_url(false, false);
		$api_url_only_store = $this->get_api_url( false, false, false, 'svg' );

		if ( true /* !empty( $element_raw[ 'settings' ][ '_background_image' ] ) || !empty( $element_raw[ 'settings' ][ 'background_image' ] ) */ ) {
			// getting current rules
			$current_rules = $post_stylesheet->get_rules( null, $element->get_unique_selector() );

			// if rules weren't found it means nothing to change there
			if ( !empty( $current_rules ) ) {
				// passing through devices (Elementor supports responsive options)
				foreach ( $current_rules as $device => $rule ) {
					// exploding hash to prepare right query for Elementor
					$exploded_device = explode( '_', $device );

					// if device 'all' - null, otherwise generate the query
					// 0 - max or min (end point), 1 - targeted device width or string with name of device
					$query = $device === 'all' ? null : [ $exploded_device[ 0 ] => $exploded_device[ 1 ] ];

					// rule contains selector and styles
					foreach ( $rule as $selector => $styles ) {
						// does the general selector has a sought element's selector? & do styles have a background-image?
						if ( strpos( $selector, $element_selector ) && array_key_exists( 'background-image', $styles ) ) {
							// taking targeted device or width for $element
							// TBD $background_target = $device === 'all' ? '' : '_' . $exploded_device[ 1 ];
                            (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_CSS) && $this->logger->log("ELEMENTOR CSS background-image: ", $styles['background-image']);

							// determine should be the underscore before targeted background image
							//$underscore = empty( $element_raw[ 'settings' ][ '_background_image' . $background_target ] ) ? '' : '_';

							if (// TBD  isset( $element_raw[ 'settings' ][ $underscore . 'background_image' . $background_target ][ 'url' ] ) &&
                                 preg_match('/\s*url\s*\(/', $styles['background-image'])) {
							    $matches = false;
							    preg_match_all('/url\((?:\'|")?([^\'"\)]*)(\'|")?\s*\)/s', $styles['background-image'], $matches);
							    if(isset($matches[1])) foreach($matches[1] as $background_url) {

                                    (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_CSS) && $this->logger->log("PARSING ELEMENTOR FOUND BACKGROUND (selector: $selector, background: " . $styles['background-image'] . ") MATCH: ", $background_url);
                                    // preparing the url to right (full) format
                                    $background_url = ShortPixelUrlTools::absoluteUrl( $background_url );

                                    // does passed url contain ".svg" at the end of the string? if so it's a SVG
                                    $only_store = ShortPixelUrlTools::is( $background_url, ShortPixelUrlTools::$ONLY_STORE );

                                    // if current image is SVG and the "Serve SVGs through CDN" is disabled we'll let it lie as is
                                    //TODO remove completely after a while, SVG remains default on CDN
                                    //if ( $only_store && !$this->settings->areas->serve_svg ) {
                                    //    continue;
                                    //}

                                    // set the right API URL depending on the image's extension
                                    $current_api_url = ( $only_store ? $api_url_only_store : $api_url );

                                    // if so replacing the url with API url
                                    $styles[ 'background-image' ] = str_replace($background_url, $current_api_url . '/' . ShortPixelUrlTools::absoluteUrl($background_url), $styles[ 'background-image' ]);
                                    (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_CSS) && $this->logger->log("PARSING ELEMENTOR REPLACE WITH: " . $current_api_url . '/' . $background_url, $styles);
                                }

								// adding the rules to the post's stylesheet
								$post_stylesheet->add_rules( $selector, $styles, $query );
							}
						}
					}
				}
			}
		}
	}

	private function include_libs() {
		// libs to be included
	}

	private function setup_globals() {
		$this->file        = SHORTPIXEL_AI_PLUGIN_FILE;
		$this->basename    = plugin_basename( $this->file );
		$this->plugin_dir  = plugin_dir_path( $this->file );
		$this->plugin_url  = plugin_dir_url( $this->file );
		$gravatar          = self::GRAVATAR_REGEX;

		if ( is_null( $this->options->get( 'api_url', [ 'settings', 'behaviour' ], null ) ) ) {
			$this->options->settings_behaviour_apiUrl        = ShortPixelAI::DEFAULT_API_AI . self::DEFAULT_API_AI_PATH;
			$this->options->settings_behaviour_replaceMethod = 'src';
			$this->options->settings_behaviour_fadein        = true;
			// moved to self::migrate_options()
			// $this->options->settings_compression_level     = 'lossy';
			$this->options->settings_compression_webp         = true;
			$this->options->settings_compression_pngToWebp    = true;
			$this->options->settings_compression_jpgToWebp    = true;
			$this->options->settings_compression_gifToWebp    = true;
			$this->options->settings_areas_serveSvg           = true;
			$this->options->settings_exclusions_excludedPaths = $gravatar;
            $this->options->settings_behaviour_sizespostmeta  = false;
			$this->options->settings_exclusions_excludedPages = '';
			//set advanced to off by default for new installations
			$this->options->flags_all_advanced = false;
            $this->options->settings_behaviour_nojquery = 2; //default to 2 for new installations in order to differentiate from the situations where it was manually set.
		} else {
			//for existing installations set advanced to true by default
			if ( is_null( $this->options->get( 'advanced', [ 'flags', 'all' ], null ) ) ) {
				$this->options->flags_all_advanced = true;
			}
		}

        if ( is_null( $this->options->get( 'sizespostmeta', [ 'settings', 'behaviour' ], null ) ) ) {
            $this->options->settings_behaviour_sizespostmeta = true;
        }

	    if ( is_null( $this->options->get( 'backgrounds_lazy', [ 'settings', 'areas' ], null ) ) ) {
		    $this->options->settings_areas_backgroundsLazy  = false;
		    $this->options->settings_compression_removeExif = true;
	    }

        if ( is_null( $this->options->get( 'backgrounds_lazy_style', [ 'settings', 'areas' ], null ) ) ) {
            //copy the tag option (formerly ambiguously described as for STYLE blocks)
            $this->options->settings_areas_backgroundsLazyStyle  = $this->options->settings_areas_backgroundsLazy;
        }

	    if ( is_null( $this->options->get( 'excluded_paths', [ 'settings', 'exclusions' ], null ) ) ) {
		    $this->options->settings_exclusions_excludedPaths = $gravatar;
	    }

		if ( is_null( $this->options->get( 'excluded_pages', [ 'settings', 'exclusions' ], null ) ) ) {
			$this->options->settings_exclusions_excludedPages = '';
		}

		if ( is_null( $this->options->get( 'eager_selectors', [ 'settings', 'exclusions' ], null ) ) && !empty( $this->options->get( 'noresize_selectors', [ 'settings', 'exclusions' ], null ) ) ) {
		    // for backwards compatibility, the eager should take the values from noresize because noresize was also eager.
		    $this->options->settings_exclusions_eagerSelectors = $this->options->settings_exclusions_noresizeSelectors;
	    }

	    if ( !is_bool( $this->options->get( 'enqueued', [ 'tests', 'front_end' ], null ) ) ) {
		    $this->options->tests_frontEnd_enqueued = true;
	    }

	    if ( $this->options->get( 'parse_css_files', [ 'settings', 'areas' ], false ) > 0 ) {
		    $this->cssCacheVer = $this->options->get( 'css_ver', [ 'flags', 'all' ], 0 );
	    }

        if(is_null($this->options->settings_behaviour_alter2wh)) {
            $this->options->settings_behaviour_alter2wh = ($this->options->flags_all_firstInstall ? 0 : 1);
        }
		if(is_null($this->options->settings_behaviour_topbarmenu)) {
			$this->options->settings_behaviour_topbarmenu = true;
		}

//        if(SHORTPIXEL_AI_DEBUG) {
//            foreach($this->settings as $key => $value) {
//                if(isset($_GET[$key])) {
//                    $this->settings[$key] = $_GET[$key];
//                }
//            }
//        }
        $this->varyCacheSupport = !$this->options->settings->compression->webp_detect
                               || (strpos($this->options->settings->behaviour->api_url, self::DEFAULT_API_AI) !== false);
    }

    private function setup_hooks() {
	    // has event not already been scheduled?
		if ( !wp_next_scheduled( self::ACCOUNT_CHECK_SCHEDULE[ 'name' ] ) ) {
			wp_schedule_event( time(), self::ACCOUNT_CHECK_SCHEDULE[ 'recurrence' ], self::ACCOUNT_CHECK_SCHEDULE[ 'name' ] );
		}

		// account check event's handler
		add_action( self::ACCOUNT_CHECK_SCHEDULE[ 'name' ], array( $this, 'account_check_handler' ) );

		$this->setup_front_tests();

	    add_action( 'admin_bar_menu', [ $this, 'toolbar_sniper' ], 998 );
		add_action( 'admin_bar_menu', [$this, 'toolbar_top_menu'], 999);

	    /**
	     * Filter deactivates WordPress's images lazy-loading
		 * @since WP 5.5
	     */
	    add_filter( 'wp_lazy_loading_enabled', '__return_false', 1 );

        LQIP::_( $this );

        //if(!(is_admin() && !wp_doing_ajax() /* && function_exists("is_user_logged_in") && is_user_logged_in() */)) {
        if (!is_admin() || $this->doingAjax) {
            //FRONT-END
            if (!in_array($this->is_conflict(), self::$SHOW_STOPPERS)) {
                //setup to replace URLs only if not admin.
	            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ] );
	            add_action( 'init', [ $this, 'init_ob' ], 1 );
	            // USING ob_ instead of the filters below.
                //add_filter( 'the_content', array( $this, 'maybe_replace_images_src',));
                //add_filter( 'post_thumbnail_html', array( $this, 'maybe_replace_images_src',));
                //add_filter( 'woocommerce_single_product_image_thumbnail_html', array( $this, 'maybe_replace_images_src',));

                //Disable the Cloudflare Rocket Loader for ai.min.js
                add_filter( 'script_loader_tag', array(&$this, 'disable_rocket_loader'), 10, 3 );
            }

	        // Deactivating when jQuery is missing
	        add_action( 'wp_ajax_nopriv_shortpixel_deactivate_ai', [ $this, 'deactivate_ai_handler' ] );
	        add_action( 'wp_ajax_nopriv_shortpixel_activate_ai', [ $this, 'activate_ai_handler' ] );

	        //EXCEPT our AJAX actions which are front but also from admin :)
            if (is_admin()) {
	            add_action( 'wp_ajax_shortpixel_ai_add_selector_to_list', [ $this, 'add_selector_to_list' ] );
	            add_action( 'wp_ajax_shortpixel_ai_remove_selector_from_list', [ $this, 'remove_selector_from_list' ] );
	            add_action( 'wp_ajax_shortpixel_deactivate_ai', [ $this, 'deactivate_ai_handler' ] );
	            add_action( 'wp_ajax_shortpixel_activate_ai', [ $this, 'activate_ai_handler' ] );
                add_action( 'wp_ajax_spai_propose_upgrade', [ $this, 'propose_upgrade' ] );
            }
            if(   $this->doingAjax && isset($_POST[ 'data' ]) && isset($_POST[ 'action' ])
               && strpos($_POST[ 'action' ], 'shortpixel_ai') === 0)
            {
                //These are SP admin's ajax calls
                Page::_( $this );
                Notice::_( $this );
                Feedback::_( $this );
                Help::_();
            }
        } else {
            //BACK-END
            Page::_( $this );
            Notice::_( $this );
            Notice\Constants::_( $this );
            Feedback::_( $this );
            Help::_();

            if(@$this->settings->areas->parse_css_files > 0) {
                $this->setup_cache_hooks();
            }

	        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
	        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_script' ] );

	        add_action( 'in_plugin_update_message-' . $this->basename, [ 'ShortPixel\AI\PluginUpdateNotice', 'in_plugin_update_message' ], 10, 2 );

	        add_filter( 'plugin_action_links_' . $this->basename, [ $this, 'generate_plugin_links' ] ); //for plugin settings page
        }
    }

    private function setup_front_tests() {
        /**
         * When current theme has been changed/deactivated this hook fired
         * @since WP 1.5.2
         */
        if ( has_action( 'switch_theme' ) ) {
            add_action( 'switch_theme', [ $this, 'enqueue_front_tests' ] );
        }

        /**
         * When new theme has been activated this hook fired
         * @since WP 3.3
         */
        if ( has_action( 'after_switch_theme' ) ) {
            add_action( 'after_switch_theme', [ $this, 'enqueue_front_tests' ] );
        }
        /**
         * When theme has been initialized this hook fired
         * @since WP 3.0
         */
        else if ( has_action( 'after_setup_theme' ) ) {
            add_action( 'after_setup_theme', [ $this, 'enqueue_front_tests' ] );
        }
        else {
            $this->enqueue_front_tests();
        }

    }

    private function setup_cache_hooks() {
	    $integrations = ActiveIntegrations::_();
	    if($integrations->has('swift-performance')) {
	        add_action('swift_performance_before_clear_all_cache', [$this, 'clear_css_cache']);
        }
        if($integrations->has('w3-total-cache')) {
            add_action('w3tc_flush_all', [$this, 'clear_css_cache']);
        }
        if($integrations->has('wp-fastest-cache')) {
            add_action('wpfc_delete_cache', [$this, 'clear_css_cache']);
        }
        if($integrations->has('wp-super-cache')) {
            add_action( 'wp_cache_cleared', [$this, 'clear_css_cache']);
        }
        if($integrations->has('litespeed-cache')) {
            add_action( 'litespeed_purged_all_cssjs', [$this, 'clear_css_cache']);
        }
        if($integrations->has('wp-rocket')) {
            add_action( 'before_rocket_clean_minify', [$this, 'clear_css_cache']);
        }
    }

	public function account_check_handler() {
		$domain_response   = ShortPixelDomainTools::get_domain_status( true );
		$domain_status     = (int) $domain_response->Status === 2 || (int) $domain_response->Status === 0;
		$dismissed_notices = Notice::getDismissed();

		if ( $domain_status && isset( $dismissed_notices->credits ) ) {
			Notice::deleteDismissing( 'credits' );
		}
	}

	public function enqueue_front_tests() {
		return !!$this->options->set( true, 'enqueued', [ 'tests', 'front_end' ] );
	}

	public function deactivate_ai_handler() {
        return $this->change_ai_handler(true);
	}

	public function activate_ai_handler() {
        return $this->change_ai_handler(false);
	}

    protected function change_ai_handler($deactivate) {
        $httpCode = 403;
        if(!self::userCan( 'manage_options' )) {
            $response = [ 'success' => false, 'message' => 'You do not have the necessary permissions to perform this action.' ];
        }
        elseif(!isset($_REQUEST['spainonce']) || !wp_verify_nonce($_REQUEST['spainonce'], 'spai-ajax-nonce')) {
            $response = [ 'success' => false, 'message' => 'Nonce verification failed, please refresh the page and try again.' ];
        } else {
            $success = !!$this->options->set(false, 'enqueued', ['tests', 'front_end'])
                    && !!$this->options->set($deactivate, 'missing_jquery', ['tests', 'front_end']);

            $response = [ 'success' => $success,
                'front'   => [
                    'reload' => true,
                ],
            ];
            $httpCode = 200;
        }
        if ( self::isAjax() ) {
            wp_send_json( $response, $httpCode );
        } else {
            return $response['success'];
        }
    }

    public function propose_upgrade() {

        if (! wp_verify_nonce($_POST['nonce'], 'ajax_request')) {
            echo "Nonce verification failed, please refresh the page and try again.";
        } else {
            ShortPixelDomainTools::propose_upgrade($this->options->settings_general_apiKey);
        }
    }

	/**
	 * Method returns queried WP dependencies
	 *
	 * @param string $type
	 *
	 * @return \_WP_Dependency[]
	 */
	public function get_queried_dependencies( $type = 'scripts' ) {
		switch ( $type ) {
			case 'styles' :
				global $wp_styles;
				$dependencies = $wp_styles;
				break;

			case 'scripts' :
				global $wp_scripts;
				$dependencies = $wp_scripts;
				break;

			default :
				global $wp_scripts;
				$dependencies = $wp_scripts;
		}

		$return = [];

		foreach ( $dependencies->queue as $handle ) {
			$return[] = $dependencies->registered[ $handle ];
		}

		return $return;
	}

	/**
	 * Method returns user's logged in token
	 *
	 * @return string
	 */
	public function get_user_token() {
		if ( function_exists( 'wp_get_session_token' ) ) {
			return wp_get_session_token();
		}

		$cookie = wp_parse_auth_cookie( '', 'logged_in' );

		return !empty( $cookie[ 'token' ] ) ? $cookie[ 'token' ] : '';
	}

    public function toolbar_sniper_bar($wp_admin_bar) {
		//                                                TODO: missing_jquery check should be removed after sniper will migrate to VanillaJS
	    if ( !self::userCan( 'manage_options' ) || !!$this->options->tests_frontEnd_missingJquery && ($this->options->settings_behaviour_nojquery <= 0)
        //    || !!$this->options->tests_frontEnd_enqueued
        ) {
            $this->logger->log('TOOLBAR SNIPER CANCELLED: ' . (!!$this->options->tests_frontEnd_missingJquery ? ' Missing jQuery' : ''));
            return;
        }

	    //Temporarily add this exclusion for the toolbar SPAI icon while the user is logged in
	    $this->exclusions->excluded_selectors[] = 'img.spai-snip-loader-img';

        $args = array(
            'id'    => 'shortpixel_ai_sniper',
            'title' => '<div id="shortpixel_ai_sniper" onclick="spaiSniperClick();return false;" title="' . __('Click here and then use the mouse to select an image to check, clear the CDN cache for it, or exclude','shortpixel-adaptive-images') . '" data-spai-exclude="true">
                       <div id="spai-smps">
                            <div id="spai-smp-multiple" class="spai-smp" style="display:none;">
                                <button class="spai-smp-options-button-cancel spai-smp-options-button-cancel-top">Cancel</button>
                                <p id="spai-smp-multiple-title">' . __('Please choose an image from the following list.','shortpixel-adaptive-images') . '</p>
                                <div id="spai-smp-multiple-list"></div>
                            </div>
                            <div id="spai-smp-single-template" class="spai-smp" style="display:none;">
                                <div class="spai-smp-single-item-container">
                                    <div class="spai-smp-single-item-container-image-container">
                                        <img src="//:0" class="spai-smp-single-item-container-image" alt="">
                                    </div>
                                    <span class="spai-smp-single-item-container-basename"></span>
                                </div>
                                <div class="spai-smp-single-menu">
                                    <p class="spai-smp-single-title"></p>
                                    <div class="spai-smp-single-options"><p class="spai-smp-single-details"></p></div>
                                    <div class="spai-smp-buttons"></div>
                                </div>
                            </div>
                            <div id="spai-smp-message" class="spai-smp" style="display:none;">
                                <p class="spai-smp-single-title">' . __( 'Couldn\'t find an image there...', 'shortpixel-adaptive-images' ) . '</p>
                                <p class="spai-smp-message-body">
                                </p>
                                <div class="spai-smp-buttons" class="spai-smp">
                                    <button class="spai-smp-options-button-retry">' . __( 'Retry', 'shortpixel-adaptive-images' ) . '</button>
                                    <button class="spai-smp-options-button-cancel">' . __( 'Close', 'shortpixel-adaptive-images' ) . '</button>
                                </div>
                            </div>
                       </div>
					   <div id="spai-snip-loader" class="spai-snip-loader" style="display:none;">
							<img src="' . plugins_url( 'assets/img/Spinner-1s-200px.gif', SHORTPIXEL_AI_PLUGIN_FILE ) . '" alt="" class="spai-snip-loader-img" data-spai-excluded="true"/>
							<p class="spai-snip-loader-text">' . __('Requesting...','shortpixel-adaptive-images') . '</p>
                       </div>
					   <div id="spai-snip-response" class="spai-snip-loader" style="display:none;">
							<p class="spai-snip-loader-text"><span></span></p>
							<button id="spai-snip-refresh-page" onclick="window.location.reload(true)">' . __( 'Refresh', 'shortpixel-adaptive-images' ) . '</button>
                       </div>
                       '
                .'</div>',
            'href'  => '#',
            'meta'  => array('class' => 'shortpixel-ai-sniper')
        );
        $wp_admin_bar->add_node( $args );
        $this->logger->log('TOOLBAR SNIPER MARKUP ADDED.');
    }

    public function toolbar_sniper_scripts() {
        $this->enqueue_style('spai-bar-style', 'style-bar', false, true);

        wp_register_script( 'spai-sniper', 'https://' . (SHORTPIXEL_AI_DEBUG ? 'dev.shortpixel.ai' : parse_url($this->settings->behaviour->api_url, PHP_URL_HOST)) . '/assets/js/snip-3.1.min.js', [], SPAI_SNIP_VERSION, true );

        wp_localize_script( 'spai-sniper', 'sniperLocalization', [
            'sizes'    => (object) [
                'gb'    => __( 'GB', 'shortpixel-adaptive-images' ),
                'mb'    => __( 'MB', 'shortpixel-adaptive-images' ),
                'kb'    => __( 'KB', 'shortpixel-adaptive-images' ),
                'byte'  => __( 'byte', 'shortpixel-adaptive-images' ),
                'bytes' => __( 'bytes', 'shortpixel-adaptive-images' ),
            ],
            'messages' => (object) [
                'static'  => (object) [
                    'cdn'                  => __( 'CDN', 'shortpixel-adaptive-images' ),
                    'origin'               => __( 'ORIGIN', 'shortpixel-adaptive-images' ),
                    'yes'                  => __( 'Yes', 'shortpixel-adaptive-images' ),
                    'back'                 => __( 'Back', 'shortpixel-adaptive-images' ),
                    'show'                 => __( 'Show', 'shortpixel-adaptive-images' ),
                    'retry'                => __( 'Retry', 'shortpixel-adaptive-images' ),
                    'cancel'               => __( 'Cancel', 'shortpixel-adaptive-images' ),
                    'path'                 => __( 'Path', 'shortpixel-adaptive-images' ),
                    'selector'             => __( 'selector', 'shortpixel-adaptive-images' ),
                    'imageExcluded'        => __( 'Image is excluded.', 'shortpixel-adaptive-images' ),
                    'removeExcludingRule'  => __( 'Remove the excluding rule', 'shortpixel-adaptive-images' ),
                    'clickToInspect'       => sprintf( __( 'Please click on the image that you want to inspect. <a href="%s" target="_blank">More details</a>', 'shortpixel-adaptive-images' ), 'https://shortpixel.com/knowledge-base/article/338-how-to-use-the-image-checker-tool' ),
                    'whyImageNotIncluded'  => __( 'Why isn\'t this image included?', 'shortpixel-adaptive-images' ),
                    'hasBeenSelected'      => __( 'has been selected', 'shortpixel-adaptive-images' ),
                    'imageOptimized'       => __( 'Image optimized', 'shortpixel-adaptive-images' ),
                    'excludeLikeThis'      => __( 'Exclude images like this one from optimization.', 'shortpixel-adaptive-images' ),
                    'excludeUrl'           => __( 'Exclude this image URL.', 'shortpixel-adaptive-images' ),
                    'removeNoResizeRule'   => __( 'Remove the no resize rule.', 'shortpixel-adaptive-images' ),
                    'dontResizeLikeThis'   => __( 'Do not resize images like this one.', 'shortpixel-adaptive-images' ),
                    'removeLazyRule'       => __( 'Remove the lazy-load rule.', 'shortpixel-adaptive-images' ),
                    'dontLazyLikeThis'     => __( 'Do not lazy-load images like this one.', 'shortpixel-adaptive-images' ),
                    'refreshOnCdn'         => __( 'Refresh on CDN.', 'shortpixel-adaptive-images' ),
                    'wantToExcludeUrl'     => __( 'Are you sure you want to exclude this image URL from optimization?', 'shortpixel-adaptive-images' ),
                    'createNeededSelector' => sprintf( __( 'Use the controls below to create the CSS selector needed. Try to keep it as simple as possible. <a href="%s" target="_blank">How do I use this?</a>', 'shortpixel-adaptive-images' ),
                        'https://shortpixel.com/knowledge-base/article/338-how-to-use-the-image-checker-tool' ),
                    'errorOccurred'        => __( 'An error occurred, please contact support.', 'shortpixel-adaptive-images' ),
                    'resizing'             => __( 'resizing', 'shortpixel-adaptive-images' ),
                    'optimizing'           => __( 'optimizing', 'shortpixel-adaptive-images' ),
                    'lazyLoading'          => __( 'lazy-loading', 'shortpixel-adaptive-images' ),
                    'dontResize'           => __( 'Don\'t resize', 'shortpixel-adaptive-images' ),
                    'excluded'             => __( 'Excluded', 'shortpixel-adaptive-images' ),
                    'dontLazyLoad'         => __( 'Don\'t lazy-load', 'shortpixel-adaptive-images' ),
                    'oneImage'             => __( 'One image', 'shortpixel-adaptive-images' ),
                    'invalidParameters'    => __( 'Invalid parameters have been passed to the function. Please try again.', 'shortpixel-adaptive-images' ),
                    'refreshing'    => __( 'Refreshing...', 'shortpixel-adaptive-images' ),
                    'checking'    => __( 'Checking...', 'shortpixel-adaptive-images' ),
                    'cdnCacheCleared'    => __( 'The image CDN cache was cleared and the image was refreshed. If you do not see the expected change, please clear the browser cache and then refresh the page.', 'shortpixel-adaptive-images' ),
                ],
                'dynamic' => (object) [
                    'sizeReducedFromTo'         => __( 'Size reduced from %s to %s', 'shortpixel-adaptive-images' ),
                    'scaledFrom'                => __( 'and scaled from %spx to %spx.', 'shortpixel-adaptive-images' ),
                    'reallyWantToStop'          => __( 'Do you really want to stop %s these images?', 'shortpixel-adaptive-images' ),
                    'confirmClickedForSelector' => __( 'Confirm has been clicked for selector %s. Data action was %s', 'shortpixel-adaptive-images' ),
                    'scrollToSeeAllImages' => __( 'Scroll the page to see all images <b>(%s)</b> ', 'shortpixel-adaptive-images' ),
                    'ruleWillBeAddedToList' => sprintf( __( 'matched by this selector on this page. The rule will be added to the <b>%s selectors</b> list in <a href="%s" target="_blank">ShortPixel AI Settings</a> and applied to <b>all the pages of your website</b>.', 'shortpixel-adaptive-images' ), '%s', admin_url( 'options-general.php?page=shortpixel-ai-settings#top#exclusions' ) ),
                    'alreadyHaveSelectors' => __( 'You already have %s selectors active. Please keep the number of exclusion selectors low for site performance.', 'shortpixel-adaptive-images' ),
                ],
            ],
        ] );

        wp_enqueue_script( 'spai-sniper');
    }

    public function generate_plugin_links($links)
    {
	    $in = '<a href="options-general.php?page=' . Page::NAMES[ 'settings' ] . '">' . __( 'Settings' ) . '</a>';
        array_unshift($links, $in);
        return $links;
    }

    function disable_rocket_loader( $tag, $handle, $src ) {
        if ( strpos($handle, 'spai-scripts') !== false ) {
            //$tag = str_replace( 'src=', 'data-cfasync="false" src=', $tag );
            $tag = str_replace( '<script', '<script data-cfasync="false"', $tag );
        }
        return $tag;
    }

    function parse_cached_css($content, $source = false, $target = false) {
        $this->cssParser->cssFilePath = $target ? trailingslashit(dirname($target)) : false;
        $ret = $this->cssParser->replace_inline_style_backgrounds($content);
        $this->cssParser->cssFilePath = false;
        (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_CSS) && $this->logger->log("PARSE WP-ROCKET || W3TC || Swift || WPFC CSS return " . strlen($ret)
            . ((SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_CSS) && (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_INCLUDE_CONTENT)
                ? "\n\nSOURCE: $source\n\nTARGET: $target\n\nCONTENT: $content\n\nCONTENT PARSED: $ret" : ''));
        return $ret;
    }

    function parse_cached_css_wpo($content, $url, $minify) {
        $this->cssParser->cssFilePath = trailingslashit(dirname($url));
        $ret = $this->cssParser->replace_inline_style_backgrounds($content);
        $this->cssParser->cssFilePath = false;
        SHORTPIXEL_AI_DEBUG && $this->logger->log("PARSE WP Optimize returns " . strlen($ret)
            . ((SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_INCLUDE_CONTENT) ? "\n\nURL: $url\n\nCONTENT: $content\n\nCONTENT PARSED: $ret" : ''));
        return $ret;
    }

    public function enqueue_script() {
        if ( $this->isWelcome() )
        {
            if ( $this->settings->behaviour->fadein && !$this->settings->behaviour->lqip ) {
                \ShortPixel\AI\JsLoader::_($this)->fadeInCss();
            }

            \ShortPixel\AI\JsLoader::_($this)->enqueue();

            if (self::userCan( 'manage_options' )) {
                // Registering the styles
                $this->enqueue_style( 'spai-admin-styles', 'admin', false, true);
                $this->toolbar_sniper_scripts();
            }

        }
    }

    //TODO refactor
    public function splitSelectors($selectors, $delimiter) {
        if(!is_string($selectors)) {
            return [];
        }
	    if($delimiter !== "\n") {
	        $selectors = str_replace("\n", $delimiter, $selectors);
        }
        $selArray = strlen($selectors) ? explode($delimiter, $selectors) : array();
        return array_map('trim', $selArray);
    }

    public function toolbar_sniper($wp_admin_bar) {
        if (!is_admin() && $this->isWelcome() && self::userCan( 'manage_options' )) {
            $this->logger->log('TOOLBAR SNIPER ON');
            $this->toolbar_sniper_bar($wp_admin_bar);
        }
    }
	public function toolbar_top_menu($wp_admin_bar) {

		if (   self::userCan( 'manage_options' )
            && Options::_()->settings_behaviour_topbarmenu
            && (!!Options::_()->pages_onBoarding_hasBeenPassed || !is_admin()) // if we're in the onboarding, we still want to show it in the front-end so that the user can use it to check the images
		) {
			$this->register_js('spai-topbar-menu', 'topbar-menu');
			$wp_admin_bar->add_menu( array(
				'id'    => 'shortpixel_ai_topmenu',

			));

			if ( Options::_()->settings_areas_parseCssFiles > 0 && !!Options::_()->pages_onBoarding_hasBeenPassed) {
				$wp_admin_bar->add_menu( array(
					'id'     => 'spai_top_menu_clear_css_cache',
					'title'  => __( 'Clear CSS Cache', 'shortpixel-adaptive-images' ),
					'href'   => '#',
					'parent' => 'shortpixel_ai_topmenu',
					'meta'   => [
						'class'   => 'spai_clear_css_cache',
						'onclick' => 'spaiCssCacheClear(this, "' . Page::_($this)->getNonce(). '");return false;'
					]
				) );
			}
			if ( Options::_()->settings_behaviour_lqip && !!Options::_()->pages_onBoarding_hasBeenPassed) {
				$wp_admin_bar->add_menu( array(
					'id'     => 'spai_top_menu_clear_lqip_cache',
					'title'  => __( 'Clear LQIP cache', 'shortpixel-adaptive-images' ),
					'href'   => '#',
					'parent' => 'shortpixel_ai_topmenu',
					'meta'   => [
						'class'   => 'spai_clear_lqip_cache',
						'onclick' => 'spaiLqipCacheClear(this, "' . Page::_($this)->getNonce(). '");return false;'
					]
				) );
			}
			if (!is_admin() && $this->isWelcome()) {
				$this->logger->log( 'TOOLBAR SNIPER ON' );
				$wp_admin_bar->add_menu( array(
					'id'     => 'spai_top_menu_sniper',
					'title'  => __( 'Check images', 'shortpixel-adaptive-images' ),
					'href'   => '#',
					'parent' => 'shortpixel_ai_topmenu',
					'meta'   => [
						'class'   => 'shortpixel-ai-sniper spai-smp-trigger',
						'onclick' => 'spaiSniperClick();return false;',
					]
				) );
			}
            if(!!Options::_()->pages_onBoarding_hasBeenPassed) {
                $wp_admin_bar->add_menu( array(
                    'id'    => 'spai_top_menu_settings',
                    'title' => __( 'Settings', 'shortpixel-adaptive-images' ),
                    'href'  => admin_url( 'options-general.php?page=shortpixel-ai-settings' ),
                    //'href'  => '#',
                    'parent'=>'shortpixel_ai_topmenu',
                    'meta' => [
                        'class' => 'spai-settings'
                    ]
                ));
            }
		}
	}

	public function enqueue_admin_styles() {
		// Registering the styles
		//TODO only load CSS when needed
		$this->enqueue_style( 'spai-admin-styles', 'admin', false, true );

        //die(var_dump($screen->id));
        if(Page::isCurrent('settings') || Page::isCurrent('on-boarding')) {
            $this->enqueue_style( 'tippy-css', 'libs/tippy');
            $this->enqueue_style( 'tippy-animations-scale', 'libs/scale');
            $this->enqueue_style( 'tippy-animations-shift-away', 'libs/shift-away');
            $this->enqueue_style( 'tippy-backdrop', 'libs/backdrop');
            $this->enqueue_style( 'tippy-svg-arrow', 'libs/svg-arrow');
        }
	}

	public function enqueue_style($name, $file, $onlyMin = true, $addVersion = false, $deps = []) {
	    $this->_register('css', $name, $file, $onlyMin, $addVersion, $deps);
	    wp_enqueue_style($name);
    }

    public function register_js($name, $file, $enqueue = true, $onlyMin = false, $addVersion = true, $deps = [], $defer = false) {
        $this->_register('js', $name, $file, $onlyMin, $addVersion, $deps, $defer);
        if($enqueue) {
            wp_enqueue_script($name);
        }
    }

    protected function _register($type, $name, $file, $onlyMin = true, $addVersion = false, $deps = [], $defer = false) {
        global $wp_version;
        $ext = ( !!SHORTPIXEL_AI_DEBUG && !$onlyMin ? '' : '.min' ) . '.' . $type;
        $path = 'assets/' . $type . '/' . $file . $ext;
	    $url =  $this->plugin_url . $path;
	    $version = $addVersion
            ? (!!SHORTPIXEL_AI_DEBUG ? hash_file( 'crc32', $this->plugin_dir . $path )
                    : ($addVersion === true ? SHORTPIXEL_AI_VERSION : $addVersion))
            : null;
	    if($type === 'js') {
            $opts = $defer && (version_compare('6.3.0', $wp_version) <= 0) ? ['strategy'  => 'defer'] : true;
            wp_register_script( $name, $url, $deps, $version, $opts);
        } else {
            wp_register_style( $name, $url, $deps, $version);
        }
    }

	public function enqueue_admin_script() {
		$min     = ( !!SHORTPIXEL_AI_DEBUG ? '' : '.min' );
        if(Page::isCurrent('settings')) {
            wp_deregister_script( 'popper-js');
            $this->register_js('popper-js', 'libs/popper', true, false, '2.4.4');

            wp_deregister_script( 'tippy-js' );
            wp_register_script( 'tippy-js', $this->plugin_url . 'assets/js/libs/tippy-bundle.umd' . $min . '.js', [], '6.2.6', true );
            wp_enqueue_script( 'tippy-js' );
        }

        $this->register_js( 'spai-admin-scripts', 'admin');
	}

	/**
	 * Method increases current css cache version to refresh files on the cdn
	 */
	public static function clear_css_cache() {
		return !!Options::_()->set( Options::_()->get( 'css_ver', [ 'flags', 'all' ], 0 ) + 1, 'css_ver', [ 'flags', 'all' ] );
	}

    //TODO refactor
    public function add_selector_to_list() {
        $result = array('status' => 'error', 'message' => __( 'An error occurred, please contact support.', 'shortpixel-adaptive-images' ));
        $which = $_POST['which_list'];
        if(is_admin()) {
            if(empty($_POST['selector']) || !is_string($_POST['selector'])) {
                $result['message'] = __('Invalid selector has been provided.', 'shortpixel-adaptive-images' );
            }
            else if(empty($which) || !is_string($which) || !in_array($which, array('noresize_selectors', 'excluded_selectors', 'excluded_paths', 'eager_selectors'))) {
                $result['message'] = __('Invalid list has been provided.', 'shortpixel-adaptive-images' );
            }
            else {
                $selector =  preg_replace('/\s+/', ' ', trim($_POST['selector']));
                $wp_option_name = 'settings_exclusions_' . \ShortPixel\AI\Converter::snakeToCamelCase($which);
                $selectors_now = $this->options->$wp_option_name;
                $result['status'] = 'ok';
                if($which === 'excluded_paths') {
                    $name = 'URL';
                    $delimiter = "\n";

                    if(strpos($selector, $this->settings->behaviour->api_url) === 0) {
                        $selector = substr($selector, strlen($this->settings->behaviour->api_url));
                    }

                    $selectorArr = explode('/http', $selector);
                    if(count($selectorArr) > 1) {
                        array_shift($selectorArr);
                        $selector = 'http' . implode('/http', $selectorArr);
                    } else {
                        $selectorArr = explode('///', $selector);
                        if(count($selectorArr) > 1) {
                            array_shift($selectorArr);
                            $selector = 'path:' . implode('///', $selectorArr);
                        }
                        else {
                            $selectorArr = explode('/', $selector);
                            if(count($selectorArr) > 1) {
                                array_splice($selectorArr, 0, 2);
                                $selector = 'path:' . implode('/', $selectorArr);
                            }
                        }
                    }
                    //remove thumbnail part
                    if(!defined('SPAI_FILENAME_RESOLUTION_UNSAFE')) {
                        $selector = preg_replace_callback(self::THUMBNAIL_REGEX,
                            function($matches) {
                                return '.' . $matches[2];
                            }, $selector);
                    }
                }
                else {
                    $name = 'selector';
                    $delimiter = ',';
                }
                $list = $this->splitSelectors($selectors_now, $delimiter);
                if(in_array($selector, $list)) {
                    $result['message'] = __( 'The selector is already present in the list. Please refresh.', 'shortpixel-adaptive-images' );
                }
                else {
                    $list[] = $selector;
                    $this->options->$wp_option_name = implode($delimiter, $list);
                    if($this->options->$wp_option_name) {
                        $listName = ($which == 'eager_selectors' ? '"Don\'t lazy load" selectors' : ucwords(str_replace('_', ' ', $which)));
                        $result['message'] = sprintf( __( 'The %s has been added to the %s list.', 'shortpixel-adaptive-images' ), $name, $listName );
                        $result['message'] = \ShortPixel\AI\CacheCleaner::_()->clear($result['message']);
                    }
                    else {
                        $result['status'] = 'error';
                        $result['message'] = __( 'An error occurred, please contact support.', 'shortpixel-adaptive-images' );
                    }
                }
                $result['list'] = $this->splitSelectors($this->options->$wp_option_name, $delimiter);
            }
        }
        else {
            $result['message'] = __( 'Please log in as admin.', 'shortpixel-adaptive-images' );
        }
        echo json_encode($result);
        wp_die();
    }

    //TODO refactor
    public function remove_selector_from_list() {
        $result = array('status' => 'error', 'message' => __( 'An error occurred, please contact support.', 'shortpixel-adaptive-images' ));
        $which = $_POST['which_list'];
        if(is_admin()) {
            if(empty($_POST['selector']) || !is_string($_POST['selector'])) {
                $result['message'] = __('Invalid list has been provided.', 'shortpixel-adaptive-images' );
            }
            else if(empty($which) || !is_string($which) || !in_array($which, array('noresize_selectors', 'excluded_selectors', 'excluded_paths', 'eager_selectors'))) {
                $result['message'] = __('Invalid list has been provided.', 'shortpixel-adaptive-images' );
            }
            else {
                $selector = $_POST['selector'];
                $delimiter = $which == 'excluded_paths' ? "\n" : ',';
                $wp_option_name = 'settings_exclusions_' . \ShortPixel\AI\Converter::snakeToCamelCase($which);
                $selectors_now = $this->options->$wp_option_name;
                $list = $this->splitSelectors($selectors_now, $delimiter);
                $result['status'] = 'ok';
                if($which === 'excluded_paths' && in_array(str_replace('\\\\', '\\', $selector), $list)) {
                    $selector = str_replace('\\\\', '\\', $selector);
                }
                if(!in_array($selector, $list)) {
                    $result['message'] = __( 'The selector does not exist in the list.', 'shortpixel-adaptive-images' );
                }
                else {
	                $list_new    = [];
	                $has_removed = false;

	                foreach ( $list as $list_element ) {
		                if ( $list_element !== $selector ) {
			                $list_new[] = $list_element;
		                }
		                else {
			                $has_removed = true;
		                }
	                }
	                $this->options->$wp_option_name = implode( $delimiter, $list_new );

	                if ( $has_removed ) {
		                $result[ 'message' ] = __( 'The selector has been removed from the list.', 'shortpixel-adaptive-images' );
		                $result[ 'message' ] = \ShortPixel\AI\CacheCleaner::_()->clear( $result[ 'message' ] );
	                }
	                else {
		                $result[ 'status' ]  = 'error';
		                $result[ 'message' ] = __( 'An error occurred, please contact support.', 'shortpixel-adaptive-images' );
	                }
                }

                $result['list'] = $this->splitSelectors($this->options->$wp_option_name, $delimiter);
            }
        }
        else {
            $result['message'] = __( 'Please log in as admin.', 'shortpixel-adaptive-images' );
        }

        echo json_encode($result);
        wp_die();
    }

	/**
	 * Method returns the result of testing is the plugin is in the beta stage
	 *
	 * @return bool
	 */
	public static function is_beta() {
		return stripos( SHORTPIXEL_AI_VERSION, 'beta' ) !== false;
	}

	/**
	 * Method migrates to the new Options implementation used in 2.x
	 */
	public static function migrate_options() {
		if ( get_option( 'spai_settings_compress_level', false ) !== false ) {
			$options = Options::_();

            if(!empty($options->settings_areas_nativeLazy)) {
                $options->settings_behaviour_nativeLazy = 1;
            }

            // compression level of new "Options" needed to check has the 2.x.x been installed before
			$compression_level = $options->settings_compression_level;

			// if new compression method is empty means that it's a fresh installation of the 2.x.x version
			if ( empty( $compression_level ) ) {
				// Compression
				$compression_level = get_option( 'spai_settings_compress_level', false );
				$replace_method    = get_option( 'spai_settings_type' );

				$options->settings_compression_level      = $compression_level === false ? 'lossy' : ( $compression_level == 1 ? 'lossy' : ( $compression_level == 2 ? 'glossy' : 'lossless' ) );
				$options->settings_compression_webp       = !!get_option( 'spai_settings_webp' );
				$options->settings_compression_removeExif = !!get_option( 'spai_settings_remove_exif' );

				// Behaviour
				$options->settings_behaviour_fadein        = !!get_option( 'spai_settings_fadein' );
				$options->settings_behaviour_crop          = !!get_option( 'spai_settings_crop' );
				$options->settings_behaviour_replaceMethod = $replace_method == 1 ? 'src' : ( $replace_method == 3 ? 'both' : 'srcset' );
				$options->settings_behaviour_apiUrl        = get_option( 'spai_settings_api_url' );
				$options->settings_behaviour_hoverHandling = !!get_option( 'spai_settings_hover_handling' );
				$options->settings_behaviour_nativeLazy    = !!get_option( 'spai_settings_native_lazy' );

				// Areas
				$options->settings_areas_backgroundsLazy     = !!get_option( 'spai_settings_backgrounds_lazy' );
                $options->settings_areas_backgroundsLazyStyle= !!get_option( 'spai_settings_backgrounds_lazy' );
				$options->settings_areas_backgroundsMaxWidth = (int) get_option( 'spai_settings_backgrounds_max_width' );
				$options->settings_areas_parseCssFiles       = !!get_option( 'spai_settings_parse_css_files' );
				$options->settings_areas_cssDomains          = get_option( 'spai_settings_css_domains' );
				$options->settings_areas_parseJs             = !!get_option( 'spai_settings_parse_js' );
				$options->settings_areas_parseJsLazy         = !!get_option( 'spai_settings_parse_js_lazy' );
				$options->settings_areas_parseJson           = !!get_option( 'spai_settings_parse_json' );
				$options->settings_areas_parseJsonLazy       = !!get_option( 'spai_settings_parse_json_lazy' );

				// Exclusions
				$options->settings_exclusions_excludedPaths     = get_option( 'spai_settings_excluded_paths' );
				$options->settings_exclusions_excludedSelectors = get_option( 'spai_settings_excluded_selectors' );
				$options->settings_exclusions_noresizeSelectors = get_option( 'spai_settings_noresize_selectors' );
				$options->settings_exclusions_eagerSelectors    = get_option( 'spai_settings_eager_selectors' );

				// Flags
				$options->flags_all_account = get_option( 'spai_settings_account' );
				$options->flags_all_cssVer  = get_option( 'spai_settings_css_ver', 1 );

				// Notices
				$options->notices_dismissed = get_option( 'spai_settings_dismissed_notices', Options\Option::_() );
			}

			// not first install because of migrate
			$options->flags_all_firstInstall = false;

			// Deleting old options
			delete_option( 'spai_settings_compress_level' );
			delete_option( 'spai_settings_webp' );
			delete_option( 'spai_settings_remove_exif' );
			delete_option( 'spai_settings_fadein' );
			delete_option( 'spai_settings_crop' );
			delete_option( 'spai_settings_type' );
			delete_option( 'spai_settings_api_url' );
			delete_option( 'spai_settings_hover_handling' );
			delete_option( 'spai_settings_native_lazy' );
			delete_option( 'spai_settings_backgrounds_lazy' );
			delete_option( 'spai_settings_backgrounds_max_width' );
			delete_option( 'spai_settings_parse_css_files' );
			delete_option( 'spai_settings_css_domains' );
			delete_option( 'spai_settings_parse_js' );
			delete_option( 'spai_settings_parse_js_lazy' );
			delete_option( 'spai_settings_parse_json' );
			delete_option( 'spai_settings_parse_json_lazy' );
			delete_option( 'spai_settings_excluded_paths' );
			delete_option( 'spai_settings_excluded_selectors' );
			delete_option( 'spai_settings_noresize_selectors' );
			delete_option( 'spai_settings_eager_selectors' );
			delete_option( 'spai_settings_parse_css_files_changing_ward' );
			delete_option( 'spai_settings_missing_jquery' );
			delete_option( 'spai_settings_tab' );
			delete_option( 'spai_settings_account' );
			delete_option( 'spai_settings_css_ver' );
			delete_option( 'spai_settings_ext_meta' );
			delete_option( 'spai_settings_dismissed_notices' );
		}
		else {
			// Setting the flag that plugin has been installed for very first time
			if ( is_null( Options::_()->flags_all_firstInstall ) ) { // due to using magic get method we can't use isset() here because isset works only with varibales and properties
				Options::_()->flags_all_firstInstall = true;

				// Set the compression level to default
				Options::_()->settings_compression_level = 'lossy';
                Options::_()->settings_behaviour_alter2wh = 0;
                //Options::_()->settings_behaviour_sizespostmeta = false;
			}
		}
	}

	/**
	 * Method to be able to migrate back to the 1.x plugin version
	 */
	public static function revert_options() {
		$options = Options::_();

		$compression_level = $options->settings_compression_level;

		if ( isset( $compression_level ) ) {
			$replace_method = $options->settings_behaviour_replaceMethod;

			update_option( 'spai_settings_compress_level', $compression_level === 'lossy' ? '1' : ( $compression_level === 'glossy' ? '2' : '0' ) );
			update_option( 'spai_settings_webp', !!$options->settings_compression_webp );
			update_option( 'spai_settings_remove_exif', !!$options->settings_compression_removeExif );

			//Behaviour
			update_option( 'spai_settings_fadein', !!$options->settings_behaviour_fadein );
			update_option( 'spai_settings_crop', !!$options->settings_behaviour_crop );
			update_option( 'spai_settings_type', $replace_method === 'src' ? '1' : ( $replace_method === 'both' ? '3' : '0' ) );
			update_option( 'spai_settings_api_url', $options->settings_behaviour_apiUrl );
			update_option( 'spai_settings_hover_handling', !!$options->settings_behaviour_hoverHandling );
            update_option( 'spai_settings_native_lazy', !!$options->settings_behaviour_nativeLazy );
			update_option( 'spai_settings_topbarmenu', !!$options->settings_behaviour_topbarmenu );
			//Areas
			update_option( 'spai_settings_backgrounds_lazy', !!$options->settings_areas_backgroundsLazy );
			update_option( 'spai_settings_backgrounds_max_width', $options->settings_areas_backgroundsMaxWidth );
			update_option( 'spai_settings_parse_css_files', $options->settings_areas_parseCssFiles > 0 );
			update_option( 'spai_settings_css_domains', $options->settings_areas_cssDomains );
			update_option( 'spai_settings_parse_js', !!$options->settings_areas_parseJs );
			update_option( 'spai_settings_parse_js_lazy', !!$options->settings_areas_parseJsLazy );
			update_option( 'spai_settings_parse_json', !!$options->settings_areas_parseJson );
			update_option( 'spai_settings_parse_json_lazy', !!$options->settings_areas_parseJsonLazy );
			//Exclusions
			update_option( 'spai_settings_excluded_paths', $options->settings_exclusions_excludedPaths );
			update_option( 'spai_settings_excluded_selectors', $options->settings_exclusions_excludedSelectors );
			update_option( 'spai_settings_noresize_selectors', $options->settings_exclusions_noresizeSelectors );
			update_option( 'spai_settings_eager_selectors', $options->settings_exclusions_eagerSelectors );

			update_option( 'spai_settings_account', $options->flags_all_account );
			update_option( 'spai_settings_css_ver', $options->get( 'css_ver', [ 'flags', 'all' ], 1 ) );

			$dismissed = $options->get( 'dismissed', 'notices' );
			$dismissed = $dismissed instanceof Options\Option ? (array) $dismissed : [];

			update_option( 'spai_settings_dismissed_notices', (array) $dismissed );

			// Deleting the options
			$options->delete( 'settings' );
		}
	}

	public static function activate() {
		// deleting already scheduled event
		wp_clear_scheduled_hook( self::ACCOUNT_CHECK_SCHEDULE[ 'name' ] );

		// adding event again
		wp_schedule_event( time(), self::ACCOUNT_CHECK_SCHEDULE[ 'recurrence' ], self::ACCOUNT_CHECK_SCHEDULE[ 'name' ] );

		self::migrate_options();

		// adding or updating option to run Front-end SPAI tests
		Options::_()->tests_frontEnd_enqueued = true;

		// set a flag to do a meet redirect (then it will be checked if on boarding has been passed)
		Options::_()->pages_onBoarding_redirectAllowed = true;

        if(Options::_()->settings_areas_parseCssFiles > 0) {
            \ShortPixel\AI\AccessControlHeaders::addHeadersToHtaccess();
        }
	}

    public static function deactivate() {
		// deleting already scheduled events
		wp_clear_scheduled_hook( self::ACCOUNT_CHECK_SCHEDULE[ 'name' ] );
	  	wp_clear_scheduled_hook( LQIP::SCHEDULE[ 'name' ] );

        \ShortPixel\AI\AccessControlHeaders::removeHeadersFromHtaccess();
        ShortPixelAILogger::instance()->clearLog();

        // adding or updating option to run Front-end SPAI tests
		Options::_()->tests_frontEnd_enqueued = true;
    }

    public function is_conflict() {
	    if ( in_array( $this->conflict, self::$SHOW_STOPPERS ) ) { // the elementorexternal doesn't deactivate the plugin
		    return $this->conflict;
	    }

        $this->conflict = 'none';

	    if ( !function_exists( 'is_plugin_active' ) || is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
		    $autoptimizeImgopt = get_option( 'autoptimize_imgopt_settings', false ); //this is set by Autoptimize version >= 2.5.0

		    if ( $autoptimizeImgopt ) {
			    $this->conflict = ( isset( $autoptimizeImgopt[ 'autoptimize_imgopt_checkbox_field_1' ] ) && $autoptimizeImgopt[ 'autoptimize_imgopt_checkbox_field_1' ] == '1' ? 'ao' : 'none' );
		    }
		    else {
			    $autoptimizeExtra = get_option( 'autoptimize_extra_settings', false ); //this is set by Autoptimize version <= 2.4.4
			    $this->conflict   = ( isset( $autoptimizeExtra[ 'autoptimize_extra_checkbox_field_5' ] ) && $autoptimizeExtra[ 'autoptimize_extra_checkbox_field_5' ] ) ? 'ao' : 'none';
		    }
	    }

        if (function_exists('is_plugin_active') && is_plugin_active('divi-toolbox/divi-toolbox.php')) {
	        $path = SHORTPIXEL_AI_WP_PLUGINS_DIR . '/divi-toolbox/divi-toolbox.php';
            $pluginInfo = get_plugin_data($path);
            if(is_array($pluginInfo) && version_compare($pluginInfo['Version'], '1.4.2') < 0) {//older versions than 1.4.2 produce the conflict
                $diviToolboxOptions = unserialize(get_option('dtb_toolbox', 'a:0:{}'));
                if(is_array($diviToolboxOptions) && isset($diviToolboxOptions['dtb_post_meta'])) {
                    $this->conflict = 'divitoolbox';
                    return $this->conflict;
                }
            }
        }
        if (function_exists('is_plugin_active') && is_plugin_active('lazy-load-optimizer/lazy-load-optimizer.php')) {
            $this->conflict = 'llopt';
            return $this->conflict;
        }
        if (function_exists('is_plugin_active') && is_plugin_active('ginger/ginger-eu-cookie-law.php')) {
            $ginger = get_option('ginger_general', array());
            if(isset($ginger['ginger_opt']) && $ginger['ginger_opt'] === 'in') {
                $this->conflict = 'ginger';
                return $this->conflict;
            }
        }

        $theme = wp_get_theme();
        if (strpos($theme->Name, 'Avada') !== false) {
            $avadaOptions = get_option('fusion_options', array());
            if (isset($avadaOptions['lazy_load']) && $avadaOptions['lazy_load'] == '1') {
                $this->conflict = 'avadalazy';
            }
        }

	    if ( !function_exists( 'is_plugin_active' ) || is_plugin_active( 'elementor/elementor.php' ) || is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
		    $elementorCSS = get_option( 'elementor_css_print_method', false );

		    if ( $elementorCSS == 'external' ) {
			    if ( $this->settings->areas->parse_css_files == 0 ) {
				    $this->options->settings_areas_parseCssFiles = 1;
			    }
			    else if ( $this->settings->areas->parse_css_files == -1 ) { //the option is explicitely unset by user
				    $this->conflict = 'elementorexternal';

				    return $this->conflict;
			    }
		    }
	    }

        return $this->conflict;
    }

    public function get_extension( $url ) {
        $path = parse_url( $url, PHP_URL_PATH );
        if ( !is_string( $path ) || empty( $path ) ) {
            return null;
        }
        return pathinfo( $path, PATHINFO_EXTENSION );
    }

    public static function is_ssl($url) {
        return strpos($url, 'https://') === 0;
    }

    public static function rem_proto($url) {
        return preg_replace('/^https?:\/\//', '', $url);
    }

	public function get_api_url( $url = false, $width = '%WIDTH%', $height = '%HEIGHT%', $type = false, $compression = false, $retAuto = false, $cacheVer = false) {
        $args = array();
        $http = $url === false ? !is_ssl() : !self::is_ssl($url);

        if($compression == 'orig' && defined('SHORTPIXEL_AI_ORIG_NO_CDN')) {
            return '';
        }

		if ( !in_array($type, ShortPixelUrlTools::$ONLY_STORE) ) {
			//ATTENTION, w_ should ALWAYS be the first parameter if present! (see fancyboxUpdateWidth in JS)
			if ( $width !== false ) {
				$args[] = array( 'w' => $width );
			}
			$args[] = array( 'q' => ( $compression ?: $this->settings->compression->level ) );
			if ( !$this->settings->compression->remove_exif ) {
				$args[] = array( 'ex' => '1' );
			}
		}

		$args[] = array( 'ret' => ($retAuto ? 'auto' : 'img') );// img returns the original if not found, auto will ret_wait for JS, fonts and CSS and will ret_img for images

        if (   !in_array($type, ShortPixelUrlTools::$ONLY_STORE) && $type !== 'noauto'
            && ($this->settings->compression->webp || $this->settings->compression->avif) && $this->varyCacheSupport)
        {
            //only add the to_auto/to_webp/to_avif parameter if our cdn or the user CDN supports vary cache
            $typeToWebp = Options::_()->get( $type . '_to_webp', [ 'settings', 'compression' ] );
            if($typeToWebp !== null && $typeToWebp || $typeToWebp === null) {
                $args[] = ['to' => ($this->settings->compression->webp && $this->settings->compression->avif
                                ? 'auto'
                                : ($this->settings->compression->webp ? 'webp' : 'avif'))];
            }
        }
        if($http) {
            $args[] = [ 'p' => 'h' ];
        }
        if($cacheVer) {
            $args[] = [ 'v' => $cacheVer ];
        }

		$api_url = $this->settings->behaviour->api_url;

        if ( !$api_url ) {
			$api_url = self::DEFAULT_API_AI . self::DEFAULT_API_AI_PATH;
		}

        $api_url = trailingslashit($api_url);

        /*
        Make args to be in desired format
         */
        foreach ($args as $arg) {
            foreach ($arg as $k => $v) {
                $api_url .= $k . '_' . $v . self::SEP;
            }
        }
        $api_url = rtrim($api_url, self::SEP);
        //$api_url = trailingslashit( $api_url );
        return $api_url . ($url ?  '/' . self::rem_proto($url) : '');
    }

    public function maybe_replace_images_src($content)
    {
        (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_HTML) && $this->logger->log("maybe_replace_images_src - PROCESSING OUTPUT BUFFER."
            . (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_INCLUDE_CONTENT ? "\n\nCONTENT:" . strlen($content) . "bytes, type:" . gettype($content) . "\n" . $content : ''));
        if (!$this->doingAjax && !\ShortPixel\AI\JsLoader::_($this)->check($content)) {
            //the script was dequeued
            $this->logger->log("SPAI JS DEQUEUED ... and it's not AJAX");
            $this->spaiJSDequeued = true;
        }
        /*if(strpos($_SERVER['REQUEST_URI'],'action=alm_query_posts') > 0) {
            $this->logger->log("CONTENT: " . substr($content, 0, 200));
        //}*/
        if ((function_exists('is_amp_endpoint') && is_amp_endpoint())) {
            $this->logger->log("IS AMP ENDPOINT");
            return $content;
        }

        $contentObj = json_decode($content);
        $isJson = !($jsonErr = json_last_error() === JSON_ERROR_SYNTAX) && ($contentObj !== null);
        if(!$isJson && ActiveIntegrations::_()->has('wp-grid-builder') && strpos($content,'{"facets":{') ) {
            $this->logger->log('Not JSON but try again, maybe it is the WP Grid Builder malformed JSON');
            $contentObj = json_decode(substr($content, strpos($content,'{"facets":{')));
            $isJson = !($jsonErr = json_last_error() === JSON_ERROR_SYNTAX) && ($contentObj !== null);
        }

        if ($isJson) {
            (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_JSON) && $this->logger->log("JSON CONTENT: " . $content);
            if ($this->settings->areas->parse_json) {
                $jsonParser = new ShortPixelJsonParser($this);
                $content = json_encode($jsonParser->parse($contentObj));
                $this->affectedTags->record();
            }
            else {
                $changed = false;
                //if not parsing json, still replace inside first level html properties.
                if(is_object($contentObj) || is_array($contentObj)) { //primitive types as 'lala' or 10 can also be JSON, can't iterate over these.
                    foreach($contentObj as $key => $value) {
                        if(is_string($value) && preg_match('/^([\s↵]*(<!--[^>]+-->)*)*<\w*(\s[^>]*|)>/s', $value)) {
                            $contentObj->$key = $this->parser->parse($value);
                            $changed = true;
                        }
                    }
                }
                if($changed) {
                    //$this->logger->log(' AJAX - recording affected tags ', $this->affectedTags);
                    $this->affectedTags->record();
                    $content = json_encode($contentObj);
                } else {
                    $this->logger->log("MISSING HTML");
                }
            }
        }
        elseif($this->spaiJSDequeued) {
            //TODO in cazul asta vom inlocui direct cu URL-urile finale ca AO
            (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_HTML) && $this->logger->log("SPAI JS IS DEQUEUED. ABORTING.");
        }
        //found a content starting with a zero width non-breaking space \xFEFF (HS#76951) and this matches: (\u{FEFF})?
        elseif(preg_match("/^"
            . (version_compare(PHP_VERSION, '7.0.0') >= 0 ? "(\u{FEFF})?" : "")
            . "(\s*<!--.*-->)*(\s*<!--[^->!]+\-->)*\s*<\s*(!\s*doctype|\s*[a-z0-9]+)(\s+[^\>]+|)\/?\s?>/i", $content)) { //check if really HTML
            $content = $this->parser->parse($content);
            if($this->doingAjax) {
                $this->affectedTags->record();
            }
        }
        else {
            (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_AREA_HTML) && $this->logger->log("OOPS... WHAT KIND OF ANIMAL IS THIS?!", (SHORTPIXEL_AI_DEBUG & ShortPixelAILogger::DEBUG_INCLUDE_CONTENT ? $content : false));
        }

        if($this->options->settings_behaviour_lqip && count($this->blankInlinePlaceholders)) {
            if($this->options->settings_behaviour_processWay === LQIP::USE_CRON) {
                $this->logger->log("LQIP - BIPs sent to processing.");
                LQIP::_($this)->process($this->blankInlinePlaceholders);
            }
            $this->logger->log("LQIP - ASKING THE CACHE PLUGINS not to cache this page as there are blank placeholders on it.");
            \ShortPixel\AI\CacheCleaner::_()->excludeCurrentPage();
        }
        SHORTPIXEL_AI_DEBUG && $this->logger->log("OVER AND OUT.");
        return $content;
    }

    /*    public function replace_images_no_quotes ($matches) {
            if (strpos($matches[0], 'src=data:image/svg;u=') || count($matches) < 2){
                //avoid duplicated replaces due to filters interference
                return $matches[0];
            }
            return $this->_replace_images('src', $matches[0], $matches[1]);
        }*/

    public function maybe_cleanup($content)
    {
        $this->logger->log('CLEANUP: ' . preg_quote($this->settings->behaviour->api_url, '/'));
        return preg_replace_callback('/' . preg_quote($this->settings->behaviour->api_url, '/') . '.*?\/(https?):\/\//is',
            array($this, 'replace_api_url'), $content);
    }
    public function replace_api_url($matches) {
        return $matches[1] . '://';
    }

    public function replace_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id) {
        if((function_exists('is_amp_endpoint') && is_amp_endpoint())) {
            return $sources;
        }
        $aspect = false;
        $this->logger->log("******** REPLACE IMAGE SRCSET: ", $sources);
        //return $sources;
        if($this->urlIsExcluded($image_src) || !ShortPixelUrlTools::isValid($image_src)) return $sources;
        if($this->settings->behaviour->replace_method === 'src') return $sources; //not returning array() because the srcset is integrated and removed in full document parse;
        $pseudoSources = array();
        foreach ($sources as $key => $data) {
            // if(strpos($data['url'], 'data:image/svg+xml;u=') === false) { // old implementation
            if(ShortPixelUrlTools::url_from_placeholder_svg($data['url']) !== false) {
                if($this->urlIsExcluded($data['url'])) {
                    //if any of the items are excluded, don't replace
                    return $sources;
                }
                if($aspect === false) {
                    $sizes = ShortPixelUrlTools::get_image_size($image_src);
                    $aspect = $sizes[1] / $sizes[0];
                    $height = $sizes[1] > 1 ? $sizes[1] : 100;
                } else {
                    $height = round($key * $aspect);
                }
                $pseudoSources[$key] = array(
                    'url' => ShortPixelUrlTools::generate_placeholder_svg($key, $height, $data['url']),//$this->absoluteUrl($data['url'])),
                    'descriptor' => $data['descriptor'],
                    'value' => $data['value']);
            } else {
                $pseudoSources[$key] = $data;
            }
        }
        $this->logger->log("******** WITH: ", $pseudoSources);
        return $pseudoSources;
    }

	public function getExclusionsMap() {
        $ex = @$this->settings->exclusions;
		return (object) [
			'excluded_selectors' => $this->splitSelectors( @$ex->excluded_selectors, ',' ),
			'eager_selectors'    => $this->splitSelectors( @$ex->eager_selectors, ',' ),
			'noresize_selectors' => $this->splitSelectors( @$ex->noresize_selectors, ',' ),
			'excluded_paths'     => $this->splitSelectors( @$ex->excluded_paths, "\n" ),
			'excluded_pages'     => $this->splitSelectors( @$ex->excluded_pages, "\n" ),
		];
	}

	public function tagIs( $type, $text ) {
        //First check if marked with data-spai attribute
        if(preg_match('/\bdata-spai-' . $type . '\b/', $text)) {
            return true;
        }
		//Second it could be by excluded_selectors or noresize_selectors
        if(strpos($text, 'data-perfmatters-preload')) $this->logger->log( 'TAG IS ' . $type . '? ' . $text );

        foreach ( $this->exclusions->{$type . '_selectors'} as $selector ) {
            $selector = trim( $selector );
            if(strpos($text, 'data-perfmatters-preload')) $this->logger->log( 'TAG IS SELECTOR: ' . $selector );
            $parts    = explode( '.', $selector );
            if ( count( $parts ) == 2 && ( $parts[ 0 ] == '' || strpos( $text, $parts[ 0 ] ) === 1 ) ) {
                $partEscaped = str_replace('*', '[a-zA-Z0-9_-]*', $parts[ 1 ]);
                if ( preg_match( '/\sclass=[\'"]([-_a-zA-Z0-9\s]*[\s]+' . $partEscaped . '|' . $partEscaped . ')[\'"\s]/i', $text ) ) {
                    return true;
                }
                else if ( preg_match( '/\sclass=' . $partEscaped . '[>\s]/i', $text ) ) {
                    return true;
                }
            }
            else {
                $parts = explode( '#', $selector );
                if ( count( $parts ) == 2 && ( $parts[ 0 ] == '' || strpos( $text, $parts[ 0 ] ) === 1 ) ) {
                    if ( preg_match( '/\sid=[\'"]' . $parts[ 1 ] . '[\'"\s]/i', $text ) ) {
                        return true;
                    }
                }
                else {
                    preg_match('/^([^\s>\(]*)\[([^\t\n\f\s\/>"\'=]+?)(?:=(?:["\']?([^\]]*?)["\']?)|)\]$/', $selector, $matches);

                    if($matches && (!strlen($matches[1]) || strpos( $text, $matches[ 1 ] ) === 1)) {
                        return isset($matches[3]) && preg_match( '/\b'. $matches[2] . '=[\'"]?' . $matches[ 3 ] . '[\'"\s]?/i', $text ) //attribute with value
                           || !isset($matches[3]) && preg_match( '/\b'. $matches[2] . '\b/i', $text ); //only existing attribute
                    }
                    //TODO test this
                    elseif ($selector === substr($text, 1, strlen($selector))) {
                        //it's only the tag name
                        return true;
                    }
                }
            }
        }

		return false;
	}

	public function urlIsApi( $url ) {
		$parsed    = parse_url( $url );
		$parsedApi = parse_url( $this->settings->behaviour->api_url );

		return isset( $parsed[ 'host' ] ) && $parsed[ 'host' ] === $parsedApi[ 'host' ];
	}

    public function urlIsExcluded($url) {

	    //exclude generated images like JetPack's admin bar hours stats
	    if(strpos($url, '?page=')) {
		    $admin = parse_url(admin_url());
		    if(strpos($url, $admin['path'])) {
			    return true;
		    }
	    }

        if( strlen($this->settings->exclusions->excluded_paths)) {
			return $this->isExcluded($url, $this->settings->exclusions->excluded_paths);
		} else {
			return false;
	    }

    }


	/**
	 * Return if a page is Excluded
	 * @param $page
	 *
	 * @return mixed
	 */
	public function pageIsExcluded($page = null) {
		static $pagesCache = [];

		if(is_null($page)) {
			$page = home_url($_SERVER['REQUEST_URI']);
		}
		$this->logger->log(home_url($_SERVER['REQUEST_URI']));

		if(isset($pagesCache[$page])) {
			$ret = $pagesCache[$page];
		} else {
			$ret = $this->isExcluded($page, $this->settings->exclusions->excluded_pages);
			$pagesCache[$page] = $ret;
		}
		return $ret;

	}

	protected function isExcluded($url, $excludedList) {

		//$this->logger->log("IS EXCLUDED? $url");
		$urlParsed = parse_url($url);
		foreach (explode("\n", $excludedList) as $rule) {

			$rule = explode(':', $rule);
			if(count($rule) >= 2) {
				$type = array_shift($rule);
				$value = implode(':', $rule);
				$value = trim($value); //remove whitespaces and especially the \r which gets added on Windows (most probably)

				switch($type) {
					case 'regex':
						if(@preg_match($value, $url)) {
							$this->logger->log("EXCLUDED by $type : $value");
							return true;
						}
						break;
					case 'path':
					case 'http': //being so kind to accept urls as they are. :)
					case 'https':
						if(!isset($urlParsed['host'])) {
							$valueParsed = parse_url($value);
							if(isset($valueParsed['host'])) {
								$url = ShortPixelUrlTools::absoluteUrl($url);
							}
						}
						if(strpos($url, $value) !== false) {
							$this->logger->log("EXCLUDED by $type $value RULE:", $rule);
							return true;
						}
						if(isset($urlParsed['path'])) {
							preg_match(self::THUMBNAIL_REGEX, $urlParsed['path'], $matches);
							//$this->logger->log("MATCHED THUMBNAIL for $url: ", $matches);
							if(isset($matches[1]) && isset($matches[2])) {
								//try again without the resolution part, in order to exclude all thumbnails if main image is excluded
								$urlMain = str_replace($matches[1] . '.' . $matches[2], '.' . $matches[2], $url);
								//$this->logger->log("WILL REPLACE : {$matches[1]}.{$matches[2]} with .{$matches[2]} results: ", $urlMain);
								if($urlMain !== $url) {
									return $this->urlIsExcluded($urlMain);
								}
							}
						}
						break;
					case 'domain':
						if(isset($urlParsed['host']) && stripos($urlParsed['host'], $value) !== false) {
							$this->logger->log("EXCLUDED by $type : $value");
							return true;
						}
				}
			}
		}
		return false;
	}


    /**
     * @return bool true if SPAI is welcome ( not welcome for example if it's an AMP page, CLI, is admin page or PageSpeed is off )
     */
	public function isWelcome() {
        if(defined('DONOTCDN')) {
            $this->logger->log('NOT WELCOME. DONOTCDN.');
            return false;
        }

        if(!$this->options->get( 'credits', [ 'flags', 'all' ], 1 )) {
            $this->logger->log('NOT WELCOME. No credits.');
            return false;
        }

		if($this->pageIsExcluded()) {
			$this->logger->log('NOT WELCOME. Page is excluded.');
			return false;
		}

	    $referrerPath = '';
		if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) ) {
			$admin    = parse_url( admin_url() );
			$referrer = parse_url( $_SERVER[ 'HTTP_REFERER' ] );
            $referrerPath = ( isset( $referrer[ 'path' ] ) ? $referrer[ 'path' ] : '' );

            //don't act on pages being customized (wp-admin/customize.php) or if referred by post.php unless it'a preview
			if (   $referrerPath === $admin[ 'path' ] . 'customize.php'
                || $referrerPath === $admin[ 'path' ] . 'post.php' && (!isset($_REQUEST['preview']) || $_REQUEST['preview'] !== 'true')
            ) {
                $this->logger->log('NOT WELCOME. customize/post '. $referrerPath);
				return false;
			}
			else if ( $this->doingAjax && $admin[ 'host' ] == $referrer[ 'host' ] && strpos( $referrer[ 'path' ], $admin[ 'path' ] ) === 0 ) {
                $this->logger->log('NOT WELCOME. admin');
				return false;
			}
		}

		SHORTPIXEL_AI_DEBUG && $this->logger->log("IS WELCOME? "
             . (is_feed() ? ' - IS FEED. ' : '')
             . ( defined( ' - DOING_AUTOSAVE' ) && DOING_AUTOSAVE ? ' DOING AUTOSAVE ' : '' )
             . ( defined( ' - DOING_CRON' ) && DOING_CRON ? ' DOING CRON ' : '' )
             . ( defined( ' - WP_CLI' ) && WP_CLI ? ' WP CLI ' : '')
            //missing jQuery AND using legacy jQuery JS (ai-2.0.js)
             . (   !!$this->options->get( 'missing_jquery', [ 'tests', 'front_end' ], false )
                && ($this->options->get( 'nojquery', [ 'settings', 'behaviour' ], 2 ) <= 0) ? ' - MISING jQuery ' : '')
             . (( is_admin() && function_exists( "is_user_logged_in" ) && is_user_logged_in()
                 && !$this->doingAjax ) ? ' - USER DOING AJAX ' : '')
             . ( function_exists( "is_user_logged_in" )
                 && !$this->options->get( 'replace_logged_in', [ 'settings', 'behaviour' ], true ) && is_user_logged_in() ? ' - USER LOGGED IN & SETTINGS ' : '')
             . ($this->doingAjax && count($_FILES) ? ' - UPLOADING FILES ' : ''));


		$welcome = !(
		             is_feed()
		          || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		          || ( defined( 'DOING_CRON' ) && DOING_CRON )
		          || ( defined( 'WP_CLI' ) && WP_CLI )
		          || ( isset( $_GET[ 'PageSpeed' ] ) && $_GET[ 'PageSpeed' ] == 'off' ) || strpos( $referrerPath, 'PageSpeed=off' )
                  || ( isset( $_GET[ 'SPAIOpt' ] ) && $_GET[ 'SPAIOpt' ] == 'off' ) || strpos( $referrerPath, 'SPAIOpt=off' )
                 //missing jQuery AND using legacy jQuery JS (ai-2.0.js)
		          || !!$this->options->get( 'missing_jquery', [ 'tests', 'front_end' ], false )
                     && ($this->options->get( 'nojquery', [ 'settings', 'behaviour' ], 2 ) <= 0)
		          || isset( $_GET[ 'fl_builder' ] ) || strpos( $referrerPath, '/?fl_builder' ) // shh.... Beaver Builder is editing :)
		          || ( isset( $_GET[ 'tve' ] ) && $_GET[ 'tve' ] == 'true' ) // Thrive Architect editor (thrive-visual-editor/thrive-visual-editor.php)
		          || ( isset( $_GET[ 'ct_builder' ] ) && $_GET[ 'ct_builder' ] == 'true' ) // Oxygen Builder
                  || isset( $_GET[ 'mailpoet_router' ] ) // MailPoet email view in browser link
		          || ( isset( $_GET[ 'oxygen_iframe' ] ) && $_GET[ 'oxygen_iframe' ] == 'true' ) // Oxygen Builder
                  || ( isset( $_GET[ 'zn_pb_edit' ] ) && $_GET[ 'zn_pb_edit' ] == '1' ) // Zion Page Builder
		          || ( isset( $_REQUEST[ 'action' ] ) && in_array( $_REQUEST[ 'action' ], self::$excludedAjaxActions ) )
		          || ( is_admin() && function_exists( "is_user_logged_in" ) && is_user_logged_in()
		               && !$this->doingAjax )
                  || ( function_exists( "is_user_logged_in" )
                       && !$this->options->get( 'replace_logged_in', [ 'settings', 'behaviour' ], true ) && is_user_logged_in() )
                  || $this->doingAjax && count($_FILES) //don't parse ajax responses to uploads

        );
        SHORTPIXEL_AI_DEBUG && $this->logger->log($welcome ? "YES!" : "NO.");
		return $welcome;
	}

	/**
	 * Sets all settings to simple mode defaults
	 * @return void
	 */
	public static function setSimpleDefaultOptions()
	{
		$options = Options::_();

		$simpleValues = [
			'compression' => [
				'avif' => 0,
				'remove_exif' => 1,
				//webp is set in interface, defaults for suboptions
				'png_to_webp' => 1,
				'jpg_to_webp' => 1,
				'gif_to_webp' => 1,
			],
			'behaviour' => [
				'fadein' => 0,
				'crop' => 0,
				'replace_method' => 'src',
				'generate_noscript' => 0,
				'api_url' => self::DEFAULT_API_AI . self::DEFAULT_API_AI_PATH,
				'lazy_threshold' => 500,
				'hover_handling' => 0,
				'replace_logged_in' => 1,
				'lqip' => 0,
				'process_way' => 'cron',
				'native_lazy' => 0,
				'alter2wh' => 0,
				'sizespostmeta' => 0,
				'size_breakpoints' => 0,
				'nojquery' => 2,

			],
			'areas' => [
				'lity' => 0,
				'parse_js_lazy' => 0,
				'parse_json_lazy' => 0,
				'backgrounds_max_width' => 1920,
				'backgrounds_lazy_style' => $options->settings_areas_parseCssFiles > 0,
				'backgrounds_lazy' => $options->settings_areas_parseCssFiles > 0,
			],
			'exclusions' => [
				'excluded_paths' => self::GRAVATAR_REGEX,
				'eager_selectors' => '',
				'noresize_selectors' => '',
				'excluded_selectors' => '',
				'excluded_pages' => '',
			]
		];

		foreach( $simpleValues as $category => $items ) {
			foreach( $items as $name => $value ) {
				$options->set( $value, $name, [ 'settings', $category ] );
			}
		}

	}

	/**
	 * @param object $options
	 *
	 * @return object
	 */
	public static function translateSimpleOptions( $options )
	{
		//Simple options are mostly meta options. Translate them to real one
        if($options->compression == null && (isset($options->simple->simple_level) || isset($options->simple->simple_webp))) {
            $options->compression = new \stdClass();
        }
		if( isset($options->simple->simple_level) ) {
			$options->compression->level = $options->simple->simple_level;
		}

		if( isset($options->simple->simple_webp) ) {
			$options->compression->webp = $options->simple->simple_webp;
		}

        if( $options->areas == null && (isset($options->simple->simple_optimize_backgrounds) || isset($options->simple->simple_optimize_js_images)) ) {
            $options->areas = new \stdClass();
        }
		if( isset($options->simple->simple_optimize_backgrounds) ) {
			$options->areas->backgrounds_lazy_style = $options->simple->simple_optimize_backgrounds;
			$options->areas->backgrounds_lazy = $options->simple->simple_optimize_backgrounds;
			$options->areas->parse_css_files = $options->simple->simple_optimize_backgrounds;
		}

		if( isset($options->simple->simple_optimize_js_images) ) {
			$options->areas->js2cdn = $options->simple->simple_optimize_js_images;
			$options->areas->parse_js = $options->simple->simple_optimize_js_images;
			$options->areas->parse_json = $options->simple->simple_optimize_js_images;
		}

		unset( $options->simple );

		return $options;
	}

    /**
     * Simple options are mostly meta options. Infer them from the real options.
     * @param $settings
     * @return boolean true if simple options were inferred, false if not
     */
    public static function verifySimpleOptions($settings )
    {
        //this is partial, used only for on-boarding, when pressing done, to decide if we activate the advanced mode.
        // TODO might be useful to when switching from advanced to simple mode, to notify the user only if settings can be lost
        // TODO but then it needs to check more items.
        return $settings->areas->backgrounds_lazy_style === $settings->areas->backgrounds_lazy
            && $settings->areas->backgrounds_lazy_style === ($settings->areas->parse_css_files > 0)

            && $settings->areas->js2cdn === $settings->areas->parse_js
            && $settings->areas->js2cdn === $settings->areas->parse_json
            && !$settings->areas->parse_js_lazy
            && !$settings->areas->parse_json_lazy

            && $settings->compression->webp == $settings->compression->png_to_webp
            && $settings->compression->png_to_webp === $settings->compression->jpg_to_webp
            && $settings->compression->png_to_webp === $settings->compression->gif_to_webp
            && $settings->compression->avif === 0
            && $settings->compression->remove_exif === 1;
    }
}
