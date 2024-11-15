<?php

	namespace ShortPixel\AI;

	use ShortPixel\AI\Notice\Constants;
	use ShortPixel\AI\Options\Option;
	use ShortPixel\AI\Options\Category;
	use ShortPixel\AI\Options\Collection;

	class Page {
		/**
		 * View folder
		 * @var string
		 */
		const VIEWS_DIR = SHORTPIXEL_AI_PLUGIN_DIR . '/includes/views';

		/**
		 * Available page names
		 * @var array
		 */
		const NAMES = [
			'settings'    => 'shortpixel-ai-settings',
            'export-settings' => 'shortpixel-ai-export-settings',
            'import-settings' => 'shortpixel-ai-import-settings',
			'on-boarding' => 'shortpixel-ai-on-boarding',
		];

		/**
		 * @var \ShortPixel\AI\Page $instance
		 */
		private static $instance;

		/**
		 * @var \ShortPixelAI $ctrl AI Controller
		 */
		protected $ctrl;

		/**
		 * @var array $data AI plugin data
		 */
		protected $data;

        /**
         * @var string $nonce SPAI plugin nonce
         */
        protected $nonce = false;

		/**
		 * @var Collection|Category|Option
		 */
		protected $options;

		/**
		 * @var \ShortPixel\AI\Notice\Constants
		 */
		protected $noticeConstants;

		protected $domain_status = false;
		protected $domain_usage;

		/**
		 * Single ton implementation
		 *
		 * @param \ShortPixelAI $controller
		 *
		 * @return \ShortPixel\AI\Page
		 */
		public static function _( $controller ) {
			return self::$instance instanceof self ? self::$instance : new self( $controller );
		}

		/**
		 * Method verifies does specified page is user's current page
		 *
		 * @param string $page
		 *
		 * @return bool
		 */
		public static function isCurrent( $page ) {
			if ( !in_array( $page, array_keys( self::NAMES ) ) || !function_exists( 'get_current_screen' ) ) {
				return false;
			}

			$screen = get_current_screen();

			return $screen && strpos( $screen->id, self::NAMES[ $page ] ) !== false;
		}

        public function getNonce() {
            if($this->nonce === false) {
                $this->nonce = wp_create_nonce('shortpixel-ai-settings');
            }
            return $this->nonce;
        }

        public static function checkSpaiNonce() {
            if(!isset($_REQUEST['spainonce']) || !wp_verify_nonce($_REQUEST['spainonce'], 'shortpixel-ai-settings')) {
                wp_send_json( [ 'success' => false, 'message' => __( 'Invalid or expired nonce. Please retry the action.', 'shortpixel-adaptive-images' ) ] );
            }
            return true;
        }

		/**
		 * Global init
		 */
		public function globalInit() {
			// add things here :)
		}

		/**
		 * Admin init
		 */
		public function adminInit() {
			$this->data = get_plugin_data( SHORTPIXEL_AI_PLUGIN_FILE );

			$this->checkForWizardRedirect();
		}

		/**
		 * Admin footer
		 */
		public function adminFooter() {
			if ( self::isCurrent( 'settings' ) ) {
				// Commented because of WordPress Plugins Reviewing team
				// echo '<div class="shortpixel-ai-beacon"></div>';
			}
		}

		/**
		 * Front-end styles & scripts for pages
		 */
		public function enqueueScripts() {
			$min    = ( !!SHORTPIXEL_AI_DEBUG ? '' : '.min' );
			$styles = [];

			$styles[ 'admin' ][ 'file' ]    = 'assets/css/admin' . $min . '.css';
			$styles[ 'admin' ][ 'url' ]     = $this->ctrl->plugin_url . $styles[ 'admin' ][ 'file' ];
			$styles[ 'admin' ][ 'version' ] = !!SHORTPIXEL_AI_DEBUG ? hash_file( 'crc32', $this->ctrl->plugin_dir . $styles[ 'admin' ][ 'file' ] ) : SHORTPIXEL_AI_VERSION;

			if ( \ShortPixelAI::userCan( 'manage_options' ) ) {
				wp_enqueue_style( 'spai-admin-styles', $styles[ 'admin' ][ 'url' ], [], $styles[ 'admin' ][ 'version' ] );
			}
		}

		/**
		 * Admin styles & scripts for pages
		 */
		public function enqueueAdminScripts() {
			$min     = ( !!SHORTPIXEL_AI_DEBUG ? '' : '.min' );
			$scripts = [];

			if ( self::isCurrent( 'settings' ) ) {
				$spai_key      = Options::_()->settings_general_apiKey;
                $this->domain_usage  = empty( $spai_key ) ? false : \ShortPixelDomainTools::get_cdn_domain_usage( null, $spai_key );
				$this->domain_status = \ShortPixelDomainTools::get_domain_status( true );

				// Registering scripts
                $this->ctrl->register_js('chart.js', 'libs/chart');

                $this->ctrl->register_js('spai-settings', 'pages/settings', false);

				wp_localize_script( 'spai-settings', 'exclusionsL10n', [
					'add'      => __( 'Add', 'shortpixel-adaptive-images' ),
					'save'     => __( 'Save', 'shortpixel-adaptive-images' ),
					'messages' => [
						'selectors' => [
							'alreadyExists' => __( 'Selector(s) already present.', 'shortpixel-adaptive-images' ),
							'invalid'       => __( 'This doesn\'t look like a valid selector. <a href="https://vegibit.com/css-selectors-tutorial/" target="_blank">How to write a selector?</a>', 'shortpixel-adaptive-images' ),
						],
					],
				] );
                wp_enqueue_script( 'spai-settings' );

				if ( @$this->domain_status->HasAccount && isset($this->domain_usage->quota) ) {
					wp_localize_script( 'spai-settings', 'statusBox', [
						'chart' => [
							'titles'      => [
								'cdn'     => __( 'CDN (Mb)', 'shortpixel-adaptive-images' ),
								'credits' => __( 'Credits (pcs)', 'shortpixel-adaptive-images' ),
							],
							'colors'      => [ 'cdn' => 'rgb(238, 44, 36)', 'credits' => 'rgb(75, 192, 192)' ],
							'backgrounds' => [ 'cdn' => 'rgba(238, 44, 36, 0.2)', 'credits' => 'rgba(75, 192, 192, 0.2)' ],
							'cdn'         => $this->domain_usage->cdn->chart,
							'credits'     => $this->domain_usage->credits->chart,
						],
					] );
				}

				$current_user = wp_get_current_user();
				$name_pieces  = [];

				if ( $current_user->first_name ) {
					$name_pieces[] = $current_user->first_name;
				}

				if ( $current_user->last_name ) {
					$name_pieces[] = $current_user->last_name;
				}
				if($spaiModeSwitchNotification = get_transient('spaiModeSwitchNotification')) {
                    wp_add_inline_script('spai-settings' , 'window.spaiModeSwitchNotification = "' . str_replace('"', '\"', $spaiModeSwitchNotification) . '"');
					delete_transient('spaiModeSwitchNotification');
                }
			}

			if ( self::isCurrent( 'on-boarding' ) ) {
				// Registering & enqueuing scripts
                $this->ctrl->register_js('spai-js-cookie', 'libs/js.cookie', true, true, '3.0.0-rc.0');
                $this->ctrl->register_js('spai-on-boarding', 'pages/on-boarding');
			}
		}

		/**
		 * Admin menu pages hook
		 */
		public function initAdminPages() {
			add_submenu_page(
				'admin.php',
				'ShortPixel AI On-Boarding',
				'ShortPixel AI On-Boarding',
				'manage_options',
				self::NAMES[ 'on-boarding' ],
				function() {
					$this->render( 'on-boarding.tpl.php' );
				} );

			add_submenu_page(
				'options-general.php',
				'ShortPixel AI Settings',
				'ShortPixel AI',
				'manage_options',
				self::NAMES[ 'settings' ],
				function() {
					$this->render( 'settings.tpl.php' );
				} );

            add_submenu_page(
                'admin.php',
                'ShortPixel AI Export Settings',
                null,
                'manage_options',
                self::NAMES[ 'export-settings' ],
                function() {
                    Page::checkSpaiNonce();
                    header('Content-Type: application/json');
                    header('Content-Disposition: attachment; filename=SPAI-settings.json');
                    echo(json_encode($this->ctrl->options->get()->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    die();
                } );

            add_submenu_page(
                'admin.php',
                'ShortPixel AI Import Settings',
                null,
                'manage_options',
                self::NAMES[ 'import-settings' ],
                function() {
                    echo('<h1>' . esc_html( get_admin_page_title() ) . '</h1>');
                    if(self::checkSpaiNonce() && isset($_FILES['import_settings_file']) && file_exists($_FILES['import_settings_file'] ["tmp_name"]))
                    {
                        $settings = json_decode(file_get_contents($_FILES['import_settings_file'] ["tmp_name"]));
                        if(null !== $settings) {
                            $settings = (array)$settings;
                            foreach ($settings as $areaName => $areaValues) {
                                $areaValues = (array)$areaValues;
                                foreach($areaValues as $settingName => $value) {
                                    $this->ctrl->options->set($value, $settingName, ['settings', $areaName]);
                                }
                            }
                            ?>
                            <div class="notice notice-success is-dismissible" data-icon="none" data-causer="" data-plugin="short-pixel-ai">
                                <div class="body-wrap">
                                    <div class="message-wrap">
                                        <p><?= __("The settings were successfully imported. Redirecting you back to the settings page in a moment...", 'shortpixel-adaptive-images' ) ?></p>
                                    </div>
                                </div>
                                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                            </div>
                            <script>
                                setTimeout(function(){
                                    window.location.href = 'options-general.php?page=shortpixel-ai-settings';
                                }, 5000);
                            </script>
                            <?php
                        }

                    }
                    wp_die();
                } );
        }

		/**
		 * @param \WP_Admin_Bar $admin_bar
		 */
		public function initAdminBarItems( $admin_bar ) {
			if ( !!$this->ctrl->options->pages_onBoarding_displayAllowed && !$this->ctrl->options->pages_onBoarding_hasBeenPassed ) {
				if ( !is_admin() || !\ShortPixelAI::userCan( 'manage_options' ) ) {
					return;
				}

				$admin_bar->add_node( [
					'id'     => self::NAMES[ 'on-boarding' ],
					'parent' => null,
					'group'  => null,
					'title'  => '<span class="ab-icon"></span><span class="ab-label">' . __( 'ShortPixel AI Setup', 'shortpixel-adaptive-images' ) . '</span>',
					'href'   => admin_url( 'admin.php?page=' . self::NAMES[ 'on-boarding' ] ),
				] );
			}
		}

		public function render( $view ) {
			if ( file_exists( self::VIEWS_DIR . DIRECTORY_SEPARATOR . $view ) ) {
				echo '<div class="wrap">';
                    require_once( self::VIEWS_DIR . DIRECTORY_SEPARATOR . $view );
				echo '</div>';
			}
		}

		/**
		 * Page constructor.
		 *
		 * @param \ShortPixelAI $controller
		 */
		private function __construct( $controller ) {
			if ( !isset( self::$instance ) || !self::$instance instanceof self ) {
				self::$instance = $this;
			}

			$this->ctrl = $controller;

			$this->noticeConstants = Constants::_( $controller );

			$this->hooks();
		}

		/**
		 * Method adds hooks
		 */
		private function hooks() {
			add_action( 'init', [ $this, 'globalInit' ] );
			add_action( 'admin_footer', [ $this, 'adminFooter' ] );
			add_action( 'admin_init', [ $this, 'adminInit' ] );
			add_action( 'admin_menu', [ $this, 'initAdminPages' ], 10 );
			add_action( 'admin_bar_menu', [ $this, 'initAdminBarItems' ], 500 );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdminScripts' ] );
			add_action( 'wp_ajax_shortpixel_ai_handle_page_action', [ 'ShortPixel\AI\Page\Actions', 'handle' ] );

			if ( !is_admin() ) {
				add_action( 'wp_enqueue_scripts', [ $this, 'enqueueScripts' ] );
			}
		}

		private function checkForWizardRedirect() {
			if ( $this->ctrl->options->pages_onBoarding_redirectAllowed !== false) {
				$this->ctrl->options->set( false, 'redirect_allowed', [ 'pages', 'on_boarding' ] );
				if ( !!$this->ctrl->options->flags_all_firstInstall && !$this->ctrl->options->pages_onBoarding_displayAllowed
                    && empty( $this->ctrl->options->pages_onBoarding_step ) && !$this->ctrl->options->pages_onBoarding_hasBeenPassed ) {
					// Setting the flag that plugin has been installed
					$this->ctrl->options->flags_all_firstInstall = false;
					// Setting the flag that display of On-Boarding Wizard is allowed
					$this->ctrl->options->pages_onBoarding_displayAllowed = true;

					wp_redirect( admin_url( 'admin.php?page=' . self::NAMES[ 'on-boarding' ] ) );
					die;
				}
			}
		}
	}