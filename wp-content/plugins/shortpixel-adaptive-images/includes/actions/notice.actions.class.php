<?php

	namespace ShortPixel\AI\Notice;

	use ShortPixel\AI\Notice;
	use ShortPixel\AI\Converter;
	use ShortPixel\AI\Options;
	use ShortPixel\AI\Page;

	class Actions {
		/**
		 * Main method of class, all action handlers should start with "handle{Causer}"
		 * Method handles the notice's actions
		 * Works via AJAX
		 */
		public static function handle() {
			$ctrl   = \ShortPixelAI::_();
			$data   = $_POST[ 'data' ];
			$causer = $_POST[ 'causer' ];

			if ( !empty( $causer ) && !empty( $data ) ) {
                $response = call_user_func( [ Actions::class, 'handle' . Converter::toTitleCase( $causer ) ], $data, $ctrl );
			}

			if ( empty( $response ) ) {
				$response = [];

				$response[ 'success' ] = false;
				$response[ 'notice' ]  = Notice::get( null,
					[
						'notice'  => [
							'type'        => 'error',
							'dismissible' => true,
						],
						'message' => [
							'body' => [ __( 'Something went wrong... API key is invalid. ', 'shortpixel-adaptive-images' ) . __( 'Please check it on your account: <a href="https://shortpixel.com/login" target="_blank"><strong>Login</strong></a>.', 'shortpixel-adaptive-images' ) ],
						],
					] );
			}

			header( 'Content-Type: application/json' );
			echo json_encode( $response );
			die;
		}

		private static function handleAo( $data ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 'solve conflict' ) {
				$success            = false;
				$autoptimize_imgopt = get_option( 'autoptimize_imgopt_settings', false );

				if ( $autoptimize_imgopt ) {
					unset( $autoptimize_imgopt[ 'autoptimize_imgopt_checkbox_field_1' ] );
					$success = update_option( 'autoptimize_imgopt_settings', $autoptimize_imgopt );
				}
				else {
					$autoptimize_extra = get_option( 'autoptimize_extra_settings', false ); //this is set by Autoptimize version <= 2.4.4
					unset( $autoptimize_extra[ 'autoptimize_extra_checkbox_field_5' ] );
					$success = update_option( 'autoptimize_extra_settings', $autoptimize_extra );
				}

				return [
					'success' => $success,
					'reload'  => [
						'allowed' => $success,
					],
					'notice'  => Notice::get( null,
						[
							'notice'  => [
								'type'        => $success ? 'success' : 'error',
								'dismissible' => true,
							],
							'message' => [
								'body' => [
									$success
										? __( 'Yay! The conflicting options have been disabled.', 'shortpixel-adaptive-images' )
										: __( 'The conflict has not been resolved.', 'shortpixel-adaptive-images' ) . ' ' . __( 'Please check the WP Rocket settings.',
											'shortpixel-adaptive-images' ),
								],
							],
						] ),
				];
			}

		}

		/**
		 * Method handles actions for "WP Rocket Defer JS"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleWpRocketDeferJs( $data ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 'solve conflict' ) {
				$success = false;

				if ( function_exists( 'update_rocket_option' ) ) {
					update_rocket_option( 'defer_all_js_safe', 1 );

					$success = true;
				}

				return [
					'success' => $success,
					'notice'  => Notice::get( null,
						[
							'notice'  => [
								'type'        => $success ? 'success' : 'error',
								'dismissible' => true,
							],
							'message' => [
								'body' => [
									$success
										? __( 'Yay! The safe mode has been enabled.', 'shortpixel-adaptive-images' )
										: __( 'The conflict has not been resolved.', 'shortpixel-adaptive-images' ) . ' ' . __( 'Please check the WP Rocket settings.',
											'shortpixel-adaptive-images' ),
								],
							],
						] ),
				];
			}
			else if ( $action === 'dismiss' ) {
				return [
					'success' => Notice::dismiss( 'wp rocket defer js' ),
				];
			}

			return null;
		}

        /**
         * Method handles actions for "WP Rocket Lazy"
         *
         * @param array $data
         *
         * @return null|array
         */
        private static function handleLazy( $data ) {
            // action which should be handled
            return self::justDismiss($data, 'lazy', null, 'dismiss forever');
        }

        /**
		 * Method handles actions for "WP Rocket Lazy"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleWpRocketLazy( $data ) {
			// action which should be handled
            return self::justDismiss($data, 'wp rocket lazy');
		}

		/**
		 * Method handles actions for "WP Rocket CSS"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleWpRocketCss( $data ) {
			// action which should be handled
            return self::justDismiss($data, 'wp rocket css');
		}

		/**
		 * Method handles actions for "Key"
		 *
		 * @param array         $data
		 * @param \ShortPixelAI $controller
		 *
		 * @return null|array
		 */
		private static function handleKey( $data, $controller ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 'use account' ) {
				$success = \ShortPixelDomainTools::use_shortpixel_account($controller);

				if ( $success ) {
					Notice::dismiss( 'key' );

					return [
						'success' => $success,
						'notice'  => Notice::get( null,
							[
								'notice'  => [
									'type'        => 'success',
									'dismissible' => true,
								],
								'message' => [
									'body' => [
										__( 'Yay! <strong>ShortPixel Adaptive Images</strong> will use this account.', 'shortpixel-adaptive-images' ) . ' 😉',
									],
								],
							] ),
					];
				}
			}
			else if ( $action === 'dismiss' ) {
				return [
					'success' => Notice::dismiss( 'key' ),
				];
			}

			return null;
		}

		/**
		 * Method handles actions for "Credits"
		 *
		 * @param array         $data
		 * @param \ShortPixelAI $controller
		 *
		 * @return null|array
		 */
		private static function handleCredits( $data, $controller ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 'check' ) {
				$domain_status = \ShortPixelDomainTools::get_domain_status( true );
				$success       = ( $domain_status->Status === 2 );

				$type = $domain_status->Status === 2 ? 'success' : ( $domain_status->Status === 1 ? 'warning' : 'error' );

				$message = $domain_status->Status === 2
					? __( 'Yay! Your new credits are active.', 'shortpixel-adaptive-images' )
					: ( $domain_status->Status === 1
						? __( 'Your account is still about to run out of credits.', 'shortpixel-adaptive-images' )  . __( 'Please top-up.',
							'shortpixel-adaptive-images' )
						: __( 'Still no credits.', 'shortpixel-adaptive-images' ) . ' ' . __( 'Please check your account.',
							'shortpixel-adaptive-images' ) );

				return [
					'success' => $success,
					'reload'  => [
						'allowed' => true,
					],
					'notice'  => Notice::get( null,
						[
							'notice'  => [
								'type'        => $type,
								'dismissible' => true,
							],
							'message' => [
								'body' => [ $message ],
							],
						] ),
				];
			}
			else if ( $action === 'dismiss' ) {
				return [
					'success' => Notice::dismiss( 'credits' ),
				];
			}

			return null;
		}

		/**
		 * Method handles actions for "Elementor external"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 * @deprecated
		 *
		 * private static function handleElementorExternal( $data ) {
		 * // action which should be handled
		 * $action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;
		 *
		 * if ( $action === 'dismiss' ) {
		 * return [
		 * 'success' => Notice::dismiss( 'elementor external' ),
		 * ];
		 * }
		 *
		 * return null;
		 * }
		 */

		/**
		 * Method handles actions for "On-Boarding"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleOnBoarding( $data ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 'redirect' ) {
				Options::_()->set( true, 'display_allowed', [ 'pages', 'on_boarding' ] );

				return [
					'success'  => Notice::dismiss( 'on boarding' ),
					'redirect' => [
						'allowed' => true,
						'url'     => admin_url( 'admin.php?page=' . Page::NAMES[ 'on-boarding' ] ),
					],
				];
			}
			else if ( $action === 'dismiss' ) {
				return ['success' => Notice::dismiss( 'on boarding' )];
			}

			return null;
		}

		/**
		 * Method handles actions for "Beta"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleBeta( $data ) {
			// action which should be handled
            return self::justDismiss($data, 'beta', SHORTPIXEL_AI_VERSION);
		}

		/**
		 * Method handles actions for "Twice lossy"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleTwicelossy( $data ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 'dismiss' ) {
				return [
					'success' => Notice::dismiss( 'twicelossy' ),
				];
			}

			return null;
		}

		/**
		 * Method handles actions for "Missing jQuery"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleMissingJquery( $data ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 're-check' ) {
				return [
					'success'  => !!Options::_()->set( true, 'enqueued', [ 'tests', 'front_end' ] ) && !!Options::_()->set( false, 'missing_jquery', [ 'tests', 'front_end' ] ),
					'redirect' => [
						'allowed' => true,
						'url'     => home_url() . '?return_to=' . urlencode( $data[ 'additional' ][ 'return_url' ] ),
					],
				];
			}
			else if ( $action === 'dismiss' ) {
				return [
					'success' => Notice::dismiss( 'missing jquery' ),
				];
			}

			return null;
		}

        /**
         * Method handles actions for "Temporary Redirect"
         *
         * @param array $data
         *
         * @return null|array
         */
        private static function handleTemporaryRedirect( $data ) {
            // action which should be handled
            //TODO, we need an API point server side that removes the domain from the banned list. The point will be recheck-banned-domain
            // For now we will just redirect back.
            $action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

            if ( $action === 're-check' ) {
                return [
                    'success'  => true,
                    'redirect' => [
                        'allowed' => true,
                        'url'     => $data[ 'additional' ][ 'return_url' ],
                    ],
                ];
            }
            else if ( $action === 'dismiss' ) {
                return [
                    'success' => Notice::dismiss( 'missing jquery' ),
                ];
            }

            return null;
        }

        /**
		 * Method handles actions for "Swift Performance"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleSwiftPerformance( $data ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 'solve conflict' ) {
				$success = false;

				if ( class_exists( 'Swift_Performance_Lite' ) ) {
					\Swift_Performance_Lite::update_option( 'merge-styles', '' );
					\Swift_Performance_Lite::update_option( 'normalize-static-resources', '' );

					$success = !\Swift_Performance_Lite::get_option( 'merge-styles' )
					           && !\Swift_Performance_Lite::get_option( 'normalize-static-resources' );
				}

				if ( class_exists( 'Swift_Performance' ) ) {
					\Swift_Performance::update_option( 'merge-styles', '' );
					\Swift_Performance::update_option( 'normalize-static-resources', '' );

					$success = !\Swift_Performance::get_option( 'merge-styles' )
					           && !\Swift_Performance::get_option( 'normalize-static-resources' );
				}

				if ( $success && class_exists( 'Swift_Performance_Cache' ) ) {
					\Swift_Performance_Cache::clear_all_cache();
				}

				return [
					'success' => $success,
					'notice'  => Notice::get( null,
						[
							'notice'  => [
								'type'        => $success ? 'success' : 'error',
								'dismissible' => true,
							],
							'message' => [
								'body' => [
									$success
										? __( 'Yay! The conflicting options have been disabled.', 'shortpixel-adaptive-images' )
										: __( 'The conflict has not been resolved.', 'shortpixel-adaptive-images' ) . ' ' . __( 'Please check the Swift Performance settings.',
											'shortpixel-adaptive-images' ),
								],
							],
						] ),
				];
			}
			else if ( $action === 'dismiss' ) {
				return [
					'success' => Notice::dismiss( 'swift performance' ),
				];
			}

			return null;
		}

		/**
		 * Method handles actions for "Imagify"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleImagify( $data ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 'solve conflict' ) {
				$success = false;

				if ( function_exists( 'update_imagify_option' ) ) {
					update_imagify_option( 'display_webp', 0 );
					$success = true;
				}

				return [
					'success' => $success,
					'notice'  => Notice::get( null,
						[
							'notice'  => [
								'type'        => $success ? 'success' : 'error',
								'dismissible' => true,
							],
							'message' => [
								'body' => [
									$success
										? __( 'Yay! The conflicting options have been disabled.', 'shortpixel-adaptive-images' )
										: __( 'The conflict has not been resolved.', 'shortpixel-adaptive-images' ) . ' ' . __( 'Please check the Imagify settings.', 'shortpixel-adaptive-images' ),
								],
							],
						] ),
				];
			}
			else if ( $action === 'dismiss' ) {
				return [
					'success' => Notice::dismiss( 'imagify' ),
				];
			}

			return null;
		}

		/**
		 * Method handles actions for "SPIO WebP Delivering"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleSpioWebp( $data ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 'solve conflict' ) {
				$success = false;

				if ( function_exists( 'update_option' ) ) {
					update_option( 'wp-short-pixel-create-webp-markup', 0 );
					$success = true;
				}

				return [
					'success' => $success,
					'notice'  => Notice::get( null,
						[
							'notice'  => [
								'type'        => $success ? 'success' : 'error',
								'dismissible' => true,
							],
							'message' => [
								'body' => [
									$success
										? __( 'Yay! The <b>Deliver next generation images</b> option has been disabled.', 'shortpixel-adaptive-images' )
										: __( 'The <b>Deliver next generation images</b> option has not been disabled.', 'shortpixel-adaptive-images' ) . ' ' . sprintf( __( 'Please check <span>%s\'s</span> <a href="%s" target="_blank">settings</a>.', 'shortpixel-adaptive-images' ),
											'ShortPixel Image Optimizer',
											admin_url( 'options-general.php?page=wp-shortpixel-settings&part=adv-settings' ) ),
								],
							],
						] ),
				];
			}
			else if ( $action === 'dismiss' ) {
				return [
					'success' => Notice::dismiss( 'spio webp' ),
				];
			}

			return null;
		}

		/**
		 * Method handles actions for "LiteSpeed Cache's JS Combine"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleLitespeedJsCombine( $data ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 'solve conflict' ) {
				$success = false;

				if ( function_exists( 'update_option' ) ) {
					update_option( 'litespeed.conf.optm-js_comb', '' );
					$success = true;
				}

				return [
					'success' => $success,
					'notice'  => Notice::get( null,
						[
							'notice'  => [
								'type'        => $success ? 'success' : 'error',
								'dismissible' => true,
							],
							'message' => [
								'body' => [
									$success
										? __( 'Yay! The conflicting options have been disabled.', 'shortpixel-adaptive-images' )
										: __( 'The conflict has not been resolved.', 'shortpixel-adaptive-images' ) . ' ' . sprintf( __( 'Please check <span>%s\'s</span> <a href="%s" target="_blank">settings</a>.', 'shortpixel-adaptive-images' ),
											'LiteSpeed Cache',
											admin_url( 'admin.php?page=litespeed-page_optm#settings_js' ) ),
								],
							],
						] ),
				];
			}
			else if ( $action === 'dismiss' ) {
				return [
					'success' => Notice::dismiss( 'litespeed js combine' ),
				];
			}

			return null;
		}

		/**
		 * Method handles actions for "WP Optimize CSS options"
		 *
		 * @param array $data
		 *
		 * @return null|array
		 */
		private static function handleWpoMergeCss( $data ) {
			// action which should be handled
			$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

			if ( $action === 'solve conflict' ) {
				$success = false;

				if ( function_exists( 'wp_optimize_minify_config' ) ) {
					$config_instance = wp_optimize_minify_config();

					if ( method_exists( $config_instance, 'get' ) && method_exists( $config_instance, 'update' ) ) {
						$config = $config_instance->get();

						if ( is_array( $config ) && isset( $config[ 'enable_merging_of_css' ] ) && !!$config[ 'enable_merging_of_css' ] ) {
							$config[ 'enable_merging_of_css' ] = false;
						}

						$config_instance->update( $config );

						$success = true;
					}
				}

				return [
					'success' => $success,
					'notice'  => Notice::get( null,
						[
							'notice'  => [
								'type'        => $success ? 'success' : 'error',
								'dismissible' => true,
							],
							'message' => [
								'body' => [
									$success
										? __( 'Yay! Looks like conflicting options have been disabled.', 'shortpixel-adaptive-images' )
										: __( 'Conflict has not been resolved.', 'shortpixel-adaptive-images' ) . ' ' . sprintf( __( 'Please check <span>%s\'s</span> <a href="%s" target="_blank">settings</a>.', 'shortpixel-adaptive-images' ),
											'WP Optimize',
											admin_url( 'admin.php?page=wpo_minify&tab=wp_optimize_css' ) ),
								],
							],
						] ),
				];
			}
			else if ( $action === 'add exclusions' ) {
				$success = false;

				if ( function_exists( 'wp_optimize_minify_config' ) ) {
					$config_instance = wp_optimize_minify_config();

					if ( method_exists( $config_instance, 'get' ) && method_exists( $config_instance, 'update' ) ) {
						$config = $config_instance->get();

						if ( is_array( $config ) && isset( $config[ 'ignore_list' ] ) && is_string( $config[ 'ignore_list' ] ) ) {
							$plugin_folder = plugin_basename( SHORTPIXEL_AI_PLUGIN_DIR );

							$conflicting_files = [
								'/' . $plugin_folder . '/assets/css/admin.css',
								'/' . $plugin_folder . '/assets/css/admin.min.css',
								'/' . $plugin_folder . '/assets/css/style-bar.css',
								'/' . $plugin_folder . '/assets/css/style-bar.min.css',
							];

							foreach ( $conflicting_files as $file ) {
								if ( strpos( $config[ 'ignore_list' ], $file ) === false ) {
									$config[ 'ignore_list' ] .= PHP_EOL . $file;
								}
							}
						}

						$config_instance->update( $config );

						$success = true;
					}
				}

				return [
					'success' => $success,
					'notice'  => Notice::get( null,
						[
							'notice'  => [
								'type'        => $success ? 'success' : 'error',
								'dismissible' => true,
							],
							'message' => [
								'body' => [
									$success
										? __( 'Yay! The exclusion rules have been successfully added.', 'shortpixel-adaptive-images' )
										: __( 'Exclusion rules have not been added.', 'shortpixel-adaptive-images' ) . ' ' . sprintf( __( 'Please check <span>%s\'s</span> <a href="%s" target="_blank">settings</a>.', 'shortpixel-adaptive-images' ),
											'WP Optimize',
											admin_url( 'admin.php?page=wpo_minify&tab=wp_optimize_css' ) ),
								],
							],
						] ),
				];
			}
			else if ( $action === 'dismiss' ) {
				return [
					'success' => Notice::dismiss( 'wpo merge css' ),
				];
			}

			return null;
		}

        private static function justDismiss($data, $causer, $value = null, $dismissAction = 'dismiss') {
            $action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;
            if ( $action === $dismissAction ) {
                return [
                    'success' => Notice::dismiss( $causer, $value),
                ];
            }
            return null;
        }

    }
