<?php

	namespace ShortPixel\AI;

	use ShortPixelAI;
	use ShortPixel\AI\Notice\Constants;
	use ShortPixel\AI\Options\Option;

	class Notice {
		/**
		 * @var \ShortPixel\AI\Notice Instance of class
		 */
		private static $instance;

		/**
		 * @var string $template Notice template
		 */
		private static $template = '<div class="{{ NOTICE CLASSES }}" data-icon="{{ NOTICE ICON }}" data-causer="{{ CAUSER }}" data-plugin="short-pixel-ai"><div class="body-wrap"><div class="message-wrap">{{ MESSAGE }}</div><div class="buttons-wrap">{{ BUTTONS }}</div></div></div>';

		/**
		 * @var array $allowed_types Valid notice classes
		 */
		private static $allowed_types = [ 'success', 'error', 'warning', 'info' ];

		/**
		 * @var array $allowed_icons Valid notice icons
		 */
		private static $allowed_icons = [ 'scared', 'happy', 'wink', 'cool', 'magnifier', 'notes' ];

		/**
		 * @var array $allowed_button_types Valid notice button types
		 */
		private static $allowed_button_types = [ 'link', 'button' ];

		/**
		 * @var \ShortPixelAI $ctrl ShortPixel AI main controller
		 */
		private $ctrl;

		/**
		 * Single ton implementation
		 *
		 * @param \ShortPixelAI|null $controller
		 *
		 * @return \ShortPixel\AI\Notice
		 */
		public static function _( $controller = null ) {
			return self::$instance instanceof self ? self::$instance : new self( $controller );
		}

		/**
		 * Method renders the admin notice using passed parameters
		 *
		 * @param null|string $causer
		 * @param null|array  $data
		 */
		public static function render( $causer = null, $data = null ) {
			echo self::get( $causer, $data );
		}

		/**
		 * Method creates and returns the admin notice using passed parameters
		 *
		 * @param null $causer
		 * @param null $data
		 *
		 * @return string
		 */
		public static function get( $causer = null, $data = null ) {
			$message = '';
			$buttons = '';

			$notice_classes = [ 'notice' ];

			if ( in_array( $data[ 'notice' ][ 'type' ], self::$allowed_types ) ) {
				$notice_classes[] = 'notice-' . strtolower( $data[ 'notice' ][ 'type' ] );
			}

			if ( isset($data[ 'notice' ][ 'dismissible' ]) && !!$data[ 'notice' ][ 'dismissible' ] ) {
				$notice_classes[] = 'is-dismissible';
			}

			if ( !empty( $data[ 'message' ][ 'title' ] ) ) {
				$message .= '<h3>' . $data[ 'message' ][ 'title' ] . '</h3>';
			}

			if ( !empty( $data[ 'message' ][ 'body' ] ) ) {
				foreach ( $data[ 'message' ][ 'body' ] as $paragraph ) {
					$message .= '<p>' . $paragraph . '</p>';
				}
			}

			if ( !empty( $data[ 'message' ][ 'buttons' ] ) ) {
				$buttons = self::renderButtons( $data[ 'message' ][ 'buttons' ] );
//				foreach ( $data[ 'message' ][ 'buttons' ] as $button ) {
//					$button_type    = isset( $button[ 'type' ] ) ? ( in_array( $button[ 'type' ], self::$allowed_button_types ) ? $button[ 'type' ] : 'button' ) : 'button';
//					$button_classes = [ 'button' ];
//
//					if ( isset( $button[ 'primary' ] ) && !!$button[ 'primary' ] ) {
//						$button_classes[] = 'button-primary';
//					}
//					else {
//						$button_classes[] = 'button-secondary';
//					}
//
//					$title      = empty( $button[ 'title' ] ) ? '' : $button[ 'title' ];
//					$action     = empty( $button[ 'action' ] ) ? '' : ' data-action="' . $button[ 'action' ] . '"';
//					$additional = empty( $button[ 'additional' ] ) ? '' : ' data-additional=' . json_encode( $button[ 'additional' ] ) . '';
//
//					if ( $button_type === 'link' ) {
//						$target  = empty( $button[ 'target' ] ) ? '' : ' target="' . $button[ 'target' ] . '"';
//						$url     = empty( $button[ 'url' ] ) ? '#' : $button[ 'url' ];
//						$buttons .= '<a href="' . $url . '" class="' . implode( ' ', $button_classes ) . '"' . $target . '>' . $title . '</a>';
//					}
//					else {
//						$buttons .= '<button type="button" class="' . implode( ' ', $button_classes ) . '"' . $action . $additional . '>' . $title . '</button>';
//					}
//				}
			}

			return str_replace(
				[ '{{ NOTICE CLASSES }}', '{{ NOTICE ICON }}', '{{ MESSAGE }}', '{{ BUTTONS }}', '{{ CAUSER }}' ],
				[
					implode( ' ', $notice_classes ),
					empty( $data[ 'notice' ][ 'icon' ] ) ? 'none' : ( in_array( $data[ 'notice' ][ 'icon' ], self::$allowed_icons ) ? strtolower( $data[ 'notice' ][ 'icon' ] ) : 'none' ),
					$message,
					$buttons,
					$causer,
				],
				self::$template );
		}

		public static function renderButtons($buttonsArray) {
			$buttons = '';
			foreach ( $buttonsArray as $button ) {
				$button_type    = isset( $button[ 'type' ] ) ? ( in_array( $button[ 'type' ], self::$allowed_button_types ) ? $button[ 'type' ] : 'button' ) : 'button';
				$button_classes = [ 'button' ];

				if ( isset( $button[ 'primary' ] ) && !!$button[ 'primary' ] ) {
					$button_classes[] = 'button-primary';
				}
				else {
					$button_classes[] = 'button-secondary';
				}

				$title      = empty( $button[ 'title' ] ) ? '' : $button[ 'title' ];
                $type       = empty( $button[ 'type' ] ) ? '' : ' data-type="' . $button[ 'type' ] . '"';
				$action     = empty( $button[ 'action' ] ) ? '' : ' data-action="' . $button[ 'action' ] . '"';
				$additional = empty( $button[ 'additional' ] ) ? '' : ' data-additional=' . json_encode( $button[ 'additional' ] ) . '';

				if ( $button_type === 'link' ) {
					$target  = empty( $button[ 'target' ] ) ? '' : ' target="' . $button[ 'target' ] . '"';
					$url     = empty( $button[ 'url' ] ) ? '#' : $button[ 'url' ];
					$buttons .= '<a href="' . $url . '" class="' . implode( ' ', $button_classes ) . '"' . $target . '>' . $title . '</a>';
				}
				else {
					$buttons .= '<button type="button" class="' . implode( ' ', $button_classes ) . '"' . $type . $action . $additional . '>' . $title . '</button>';
				}
			}
			return $buttons;
		}

		/**
		 * Method adds info about dismissed notice
		 *
		 * @param string $causer
		 * @param mixed  $value What to put into the dismissed (for example plugin version if need to dismiss only for that version), default is time();
		 *
		 * @return bool
		 */
		public static function dismiss( $causer, $value = null ) {
			$dismissed = Options::_()->get( 'dismissed', 'notices', Option::_() );
			// extra check to make sure that we get right object
			$dismissed = $dismissed instanceof Option ? $dismissed : Option::_();

			$dismissed->{$causer} = isset( $value ) ? $value : time();

			return !!Options::_()->set( $dismissed, 'dismissed', 'notices' );
		}

		/**
		 * Method return object with information about dismissed notices
		 *
		 * @return Option
		 */
        public static function getDismissed() {
            $dismissed = Options::_()->get( 'dismissed', 'notices', Option::_() );

            return $dismissed instanceof Option ? $dismissed->getData() : new stdClass();
        }

		/**
		 * Method deletes info about dismissed notification
		 *
		 * @param string $causer
		 */
		public static function deleteDismissing( $causer ) {
			$causer    = Converter::toSnakeCase( $causer );
			$dismissed = Options::_()->get( 'dismissed', 'notices', Option::_() );
			// extra check to make sure that we get right object
			$dismissed = $dismissed instanceof Option ? $dismissed : Option::_();

			unset( $dismissed->{$causer} );

			Options::_()->set( $dismissed, 'dismissed', 'notices' );
		}

		/**
		 * Method clears all dismissed notifications
		 */
		public static function clearDismissed() {
			Options::_()->delete( 'dismissed', 'notice' );
		}

		/**
		 * Method renders all admin notices
		 */
		public function renderNotices() {
			if ( !function_exists( 'current_user_can' ) || !current_user_can( 'manage_options' ) ) {
				return;
			}

			$tests        = $this->ctrl->options->tests;
			$conflict     = $this->ctrl->is_conflict();
			$dismissed    = self::getDismissed();
			$integrations = ActiveIntegrations::_(true);

			// Critical OR conflicting notifications
			if ( $conflict === 'ao' ) {
				self::render( 'ao',
					[
						'notice'  => [
							'type' => 'error',
							'icon' => 'scared',
						],
						'message' => Constants::_()->autoptimize,

					] );
			}
			else if ( $conflict === 'avadalazy' ) {
				self::render( 'avadalazy',
					[
						'notice'  => [
							'type' => 'error',
							'icon' => 'scared',
						],
						'message' => Constants::_()->avadalazy,

					] );
			}
			else if ( $conflict === 'ginger' ) {
				self::render( 'ginger',
					[
						'notice'  => [
							'type' => 'error',
							'icon' => 'scared',
						],
						'message' => Constants::_()->ginger,

					] );
			}
			else if ( $conflict === 'divitoolbox' ) {
				self::render( 'divitoolbox',
					[
						'notice'  => [
							'type' => 'error',
							'icon' => 'scared',
						],
						'message' => Constants::_()->divitoolbox,

					] );
			}
			/* Obsolete because of implemented hook for this
			else if ( $conflict === 'elementorexternal' && !isset( $dismissed->elementorexternal ) ) {
				self::render( 'elementorexternal',
					[
						'notice'  => [
							'type'        => 'error',
							'icon'        => 'scared',
							'dismissible' => true,
						],
						'message' => Constants::_()->elementorexternal,
						'buttons' => [
							[
								'type'    => 'link',
								'title'   => __( 'Change Elementor\'s option', 'shortpixel-adaptive-images' ),
								'url'     => 'themes.php?page=elementor#tab-advanced',
								'primary' => true,
							],
							[
								'type'    => 'link',
								'title'   => __( 'ShortPixel Adaptive Images options', 'shortpixel-adaptive-images' ),
								'url'     => 'options-general.php?page=' . Page::NAMES[ 'settings' ] . '#top#areas',
								'primary' => false,
							],
						],
					] );
			}
			*/

            // Information notifications
            if ( !function_exists('mb_convert_case') ) {
                self::render( 'mbstring',
                    [
                        'notice'  => [
                            'type'        => 'error',
                            'icon'        => 'scared',
                            'dismissible' => false,
                        ],
                        'message' => Constants::_()->mbstring,
                    ] );
            }

			// Information notifications
			if ( ShortPixelAI::is_beta() && ( !isset( $dismissed->beta ) || $dismissed->beta !== SHORTPIXEL_AI_VERSION ) ) {
				self::render( 'beta',
					[
						'notice'  => [
							'type'        => 'info',
							'icon'        => 'notes',
							'dismissible' => true,
						],
						'message' => Constants::_()->beta,

					] );
			}

            //currently deactivating this notice as we have passed to 3.0 a looong time ago.
			if ( false && !$this->ctrl->options->pages_onBoarding_displayAllowed && !$this->ctrl->options->flags_all_firstInstall && !isset( $dismissed->on_boarding ) ) {
				self::render( 'on boarding',
					[
						'notice'  => [
							'type'        => 'info',
							'icon'        => 'wink',
							'dismissible' => true,
						],
						'message' => Constants::_()->on_boarding,

					] );
			}

			// Warnings
			if ( !isset( $dismissed->lazy ) ) {
				$thrown = get_transient( "shortpixelai_thrown_notice" );

				if ( is_array( $thrown ) ) {
					if ( $thrown[ 'when' ] == 'lazy' ) {
						self::render( 'lazy',
							[
								'notice'  => [
									'type'        => 'warning',
									'icon'        => 'scared',
									'dismissible' => true,
								],
								'message' => Constants::_()->lazy,
							] );
					}
				}

				delete_transient( "shortpixelai_thrown_notice" );
			}

			if ( !isset( $dismissed->wp_rocket_defer_js ) && $integrations->has( 'wp-rocket', 'defer-all-js' ) ) {
				self::render( 'wp rocket defer js', [
					'notice'  => [
						'type'        => 'warning',
						'icon'        => 'scared',
						'dismissible' => true,
					],
					'message' => Constants::_()->wp_rocket_defer_js,

				] );
			}

			if ( !isset( $dismissed->wp_rocket_lazy ) && $integrations->has( 'wp-rocket', 'lazyload' ) ) {
				self::render( 'wp rocket lazy',
					[
						'notice'  => [
							'type'        => 'warning',
							'icon'        => 'scared',
							'dismissible' => true,
						],
						'message' => Constants::_()->wp_rocket_lazy,

					] );
			}

			if ( !isset( $dismissed->wprocketcss ) && $this->ctrl->settings->areas->parse_css_files > 0
                 && $integrations->has('wp-rocket', 'minify-css') && !$integrations->has('wp-rocket', 'css-filter') ) {
				self::render( 'wprocketcss',
					[
						'notice'  => [
							'type'        => 'warning',
							'icon'        => 'scared',
							'dismissible' => true,
						],
						'message' => Constants::_()->wprocketcss,

					] );
			}

			if ( !isset( $dismissed->key ) && !Page::isCurrent( 'on-boarding' ) && !$this->ctrl->options->flags_all_account ) {
				$account = \ShortPixelDomainTools::get_shortpixel_account();

                if(!isset($account->Status) || $account->Status == -3) {
                    $this->renderNetworkError($account);
                }
				elseif ( $account->key ) {
					self::render( 'key',
						[
							'notice'  => [
								'type'        => 'warning',
								'icon'        => 'happy',
								'dismissible' => true,
							],
							'message' => [
								'title' => Constants::_()->key['title'],
								'body'  => [
									sprintf( Constants::_()->key['body'][0], $account->email ),
								],
								'buttons' => Constants::_()->key['buttons'],
							],

						] );
				}
			}

			if ( !isset( $dismissed->credits ) ) {
				$message = self::getCreditsNoticeInfo($this->ctrl);
				if(!empty($message)) {
					self::render( 'credits',
						[
							'notice'  => [
								'type'        => 'warning',
								'icon'        => 'notes',
								'dismissible' => true,
							],
							'message' => $message,

						] );
				}
			}

			if (
				!isset( $dismissed->twicelossy ) && $this->ctrl->settings->compression->level === 'lossy' && !Page::isCurrent( 'on-boarding' )
				&& is_plugin_active( 'shortpixel-image-optimiser/wp-shortpixel.php' ) && get_option( 'wp-short-pixel-compression', false ) == '1'
			) {
				self::render( 'twicelossy',
					[
						'notice'  => [
							'type'        => 'warning',
							'icon'        => 'happy',
							'dismissible' => true,
						],
						'message' => Constants::_()->twicelossy,

					] );
			}

			if ( !!$tests->front_end->missing_jquery && !isset( $dismissed->missing_jquery ) ) {
				self::render( 'missing jquery',
					[
						'notice'  => [
							'type'        => 'warning',
							'icon'        => 'scared',
							'dismissible' => true,
						],
						'message' => Constants::_()->missing_jquery,

					]
				);
			}

            if ( !isset( $dismissed->temporary_redirect )) {
                $domainStatus = \ShortPixelDomainTools::get_domain_status(true);
                if($domainStatus && $domainStatus->Status == -3) {
                    $this->renderNetworkError($domainStatus);
                }
                elseif($domainStatus && ($domainStatus->TemporaryRedirectOrigin === true || $domainStatus->TemporaryRedirectOrigin === "true")) {
                    self::render( 'temporary redirect',
                        [
                            'notice'  => [
                                'type'        => 'warning',
                                'icon'        => 'scared',
                                'dismissible' => true,
                            ],
                            'message' => Constants::_()->temporary_redirect,

                        ]
                    );
                }
            }

            if ( $integrations->has('swift-performance') && !!$this->ctrl->settings->areas->parse_css_files > 0 && !isset( $dismissed->swift_performance ) ) {
				if ( $integrations->has('swift-performance', 'has_bug') && $integrations->has('swift-performance', 'has_conflict') ) {
					self::render( 'swift performance',
						[
							'notice'  => [
								'type'        => 'warning',
								'icon'        => 'scared',
								'dismissible' => true,
							],
							'message' => Constants::_()->swift_performance,

						] );
				}
			}

			if ( $integrations->has('imagify', 'has_conflict') && !isset( $dismissed->imagify ) ) {
				self::render( 'imagify',
					[
						'notice'  => [
							'type'        => 'warning',
							'icon'        => 'scared',
							'dismissible' => true,
						],
						'message' => Constants::_()->imagify,

					] );
			}

			if ( !isset( $dismissed->spio_webp ) && is_plugin_active( 'shortpixel-image-optimiser/wp-shortpixel.php' ) && !empty( get_option( 'wp-short-pixel-create-webp-markup', 0 ) ) ) {
				self::render( 'spio webp', [
					'notice'  => [
						'type'        => 'warning',
						'icon'        => 'scared',
						'dismissible' => true,
					],
					'message' => Constants::_()->spio_webp,

				] );
			}

			if ( !empty( get_option( 'litespeed.conf.optm-js_comb', '' ) ) && !isset( $dismissed->litespeed_js_combine ) ) {
				self::render( 'litespeed js combine', [
					'notice'  => [
						'type'        => 'warning',
						'icon'        => 'scared',
						'dismissible' => true,
					],
					'message' => Constants::_()->litespeed_js_combine,

				] );
			}

        if ( $integrations->has( 'wp-optimize', 'enable_merging_of_css' ) && !isset( $dismissed->wpo_merge_css ) ) {
				self::render( 'wpo merge css', [
					'notice'  => [
						'type'        => 'warning',
						'icon'        => 'scared',
						'dismissible' => true,
					],
					'message' => Constants::_()->wpo_merge_css,

				] );
			}
      
			if ( !!get_transient( 'spai_lqip_mkdir_failed' ) && !isset( $dismissed->lqip_mkdir_failed ) ) {
				// Disable the LQIP option
				Options::_()->settings_behaviour_lqip = false;

				self::render( 'lqip mkdir failed', [
					'notice' => [
						'type'        => 'warning',
						'icon'        => 'scared',
						'dismissible' => true,
					],
					'message' => Constants::_()->lqip_mkdir_failed,
				] );
			}
		}

		/**
		 * Returns content for rendering credits notification
		 * @param ShortPixelAI
		 * @return array
		 */
		public static function getCreditsNoticeInfo(ShortPixelAI $ctrl) {
			$ret = [];
			$domain_status = \ShortPixelDomainTools::get_domain_status();

            //in rare cases because of a network issue $domain_status is null
            //                                                     vv -- temporary network error, ignore
			if ( $domain_status && $domain_status->Status !== 2 && $domain_status->Status !== -3 ) {
                $buttons = $messages = [];
                if($ctrl->options->settings_general_apiKey) {
                    $buttons[] = [
                        'title'   => __( 'Show me the best available options', 'shortpixel-adaptive-images' ),
                        'type'    => 'js',
                        'action'  => 'spaiProposeUpgrade',
                        'primary' => true,
                    ];
                    $messages[] = ' <div id="spaiProposeUpgradeShade" data-ajaxnonce="' . wp_create_nonce( 'ajax_request' )
                        . '" data-ajaxurl="' . admin_url( 'admin-ajax.php' ) . '" class="spai-modal-shade" style="display:none;"></div>
                        <div id="spaiProposeUpgrade" class="spai-modal spai-hide" style="min-width:650px;margin-left:-305px;">
                            <div class="spai-modal-title">
                                <button type="button" class="spai-close-upgrade-button" onclick="jQuery.spaiCloseProposeUpgrade()">&times;</button>
                            </div>
                            <div class="spai-modal-body" style="height:auto;min-height:400px;padding:0;"></div>
                        </div>';
                }
				$buttons[] = [
                    'title'   => __( 'Check credits', 'shortpixel-adaptive-images' ),
                    'action'  => 'check',
                    'primary' => true,
                ];

				if ( $domain_status->Status === 1 ) {
					$messages[] = __( 'Please note that your ShortPixel Adaptive Images quota will be exhausted soon.', 'shortpixel-adaptive-images' );
				}
				else if ( $domain_status->Status === -1 || $domain_status->Status === -2 ) {
					$messages[] = __( 'Your ShortPixel Adaptive Images quota has been exceeded.', 'shortpixel-adaptive-images' );
					$messages[] = __( 'Your images are served from the origin server until you top-up your account.', 'shortpixel-adaptive-images' );
						//__( 'The already optimized images will still be served from the ShortPixel CDN for up to 30 days but the images that weren\'t already optimized and cached via CDN will be served directly from your website.', 'shortpixel-adaptive-images' ),
				}

				if ( !!$domain_status->HasAccount ) {
					if(is_null( $ctrl->options->settings_general_apiKey )) {
						$messages[] = __( 'Please input your API Key in settings in order to get more details.', 'shortpixel-adaptive-images' )  .
						              ' <a href="https://shortpixel.com/knowledge-base/article/94-how-to-associate-a-domain-to-my-account" target="_blank">' .
						              __( 'How do I do this?', 'shortpixel-adaptive-images' ) . '</a>';
					}

					$title = __( 'Log-in', 'shortpixel-adaptive-images' );
                    $redirect = '/dashboard';
					if ( $domain_status->Status === 1 ) {
						$title = __( 'Top-up', 'shortpixel-adaptive-images' );
                        $redirect = '/pricing-adaptive-cdn/http' . urlencode( ($_SERVER['HTTPS'] ? 's' : '') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
					}
                    $key = $ctrl->options->settings_general_apiKey ?: '_';
                    $url = ShortPixelAI::DEFAULT_MAIN_DOMAIN. '/login/'. $key . $redirect;

					$buttons[]  = [
						'type'    => 'link',
						'title'   => $title,
						'url'     => $key === '_' ? apply_filters('spai_affiliate_link', $url) : $url, //apply affiliate filters only if no API key
						'target'  => '_blank',
						'primary' => false,
					];
				}
				else {
					$messages[] = __( 'If you <span>sign-up now</span> with ShortPixel you will receive 5Gb more free traffic and also you\'ll get 50% bonus credits to any purchase that you\'ll choose to make. Image optimization traffic can be purchased with as little as $3.99 for 52.5Gb monthly (including the 50% bonus).',
						'shortpixel-adaptive-images' ) . ' DATA: ' . print_r($domain_status, true);
					$fsuUrl = apply_filters('spai_affiliate_link',ShortPixelAI::DEFAULT_MAIN_DOMAIN. '/fsu');
					if(count(explode('/', $fsuUrl)) == 4) {
						$fsuUrl .= '/af/MNCMIUS28044';
					}
					$buttons[]  = [
						'type'    => 'link',
						'title'   => __( 'Sign-up', 'shortpixel-adaptive-images' ),
						'url'     => $fsuUrl,
						'target'  => '_blank',
						'primary' => false,
					];
				}
				$ret = [
					'title' => __( 'ShortPixel Adaptive Images notice', 'shortpixel-adaptive-images' ),
					'body'  => $messages,
					'buttons' => $buttons,
				];

			}
			return $ret;
		}

		public function enqueueAdminScripts() {
			$scripts = [];
			$min     = ( !!SHORTPIXEL_AI_DEBUG ? '' : '.min' );

			$scripts[ 'notice' ][ 'file' ]    = 'assets/js/notice' . $min . '.js';
			$scripts[ 'notice' ][ 'version' ] = !!SHORTPIXEL_AI_DEBUG ? hash_file( 'crc32', $this->ctrl->plugin_dir . $scripts[ 'notice' ][ 'file' ] ) : SHORTPIXEL_AI_VERSION;

			// Registering scripts
			wp_register_script( 'spai-notice', $this->ctrl->plugin_url . $scripts[ 'notice' ][ 'file' ], [ 'jquery' ], $scripts[ 'notice' ][ 'version' ] );

			// Enqueueing scripts
			wp_enqueue_script( 'spai-notice' );
		}

		/**
		 * Notice constructor.
		 *
		 * @param \ShortPixelAI $controller ShortPixel AI main controller
		 */
		private function __construct( $controller ) {
			if ( !isset( self::$instance ) || !self::$instance instanceof self ) {
				self::$instance = $this;
			}

			$this->ctrl = $controller;

			add_action( 'admin_notices', [ $this, 'renderNotices' ] );
			add_action( 'admin_footer', [ $this, 'enqueueAdminScripts' ] );
			add_action( 'wp_ajax_shortpixel_ai_handle_notice_action', [ 'ShortPixel\AI\Notice\Actions', 'handle' ] );
		}

        /**
         * This usually is a "connection error: cURL error 28: Operation timed out after 10001 milliseconds with 0 out of 0 bytes received"
         * @param $account
         * @return void
         */
        public function renderNetworkError($account)
        {
        // There is an issue with the wp_safe_remote_get on this host so notify the user
            self::render('remote_get_error', [
                'notice' => [
                    'type' => 'warning',
                    'icon' => 'scared',
                    'dismissible' => true,
                ],
                'message' => [
                    'title' => Constants::_()->remote_get_error['title'],
                    'body' => [
                        sprintf(Constants::_()->remote_get_error['body'][0], isset($account->Message) ? $account->Message : 'unknown error'),
                    ],
                ],
            ]);
        }
    }
