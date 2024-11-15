<?php

	namespace ShortPixel\AI\Help;

	use ShortPixel\AI\Page;
	use ShortPixel\AI\Options;
	use ShortPixel\AI\Converter;

	class Actions {
		/**
		 * Method handles the help's actions
		 * Works via AJAX
		 */
		public static function handle() {
			$data   = $_POST[ 'data' ];
			$action = $data[ 'action' ];

			$response = [ 'success' => false ];

			if ( !empty( $action ) && is_string( $action ) ) {
				$action = Converter::toTitleCase( $action );

				// unset action
				unset( $data[ 'action' ] );

				$response = call_user_func( [ 'self', 'handle' . $action ], isset( $data ) ? $data : null );
			}

			wp_send_json( $response );
		}

		/**
		 * "enable on boarding" action should be fired by button \ShortPixel\AI\Help:46
		 * @return array
		 */
		private static function handleEnableOnBoarding() {
			$return = [
				'success' => false,
			];

			if ( !!Options::_()->get( 'has_been_passed', [ 'pages', 'on_boarding' ] ) ) {
				$return[ 'success' ] = !!Options::_()->set( 0, 'step', [ 'pages', 'on_boarding' ] ) // set current step to first
				                       && !!Options::_()->set( false, 'has_been_passed', [ 'pages', 'on_boarding' ] ); // set flag has not been passed
			}
			else {
				// set this flag to true just to do a redirect without changes in DB
				$return[ 'success' ] = true;
			}

			if ( $return[ 'success' ] ) {
				$return[ 'redirect' ] = [
					'url' => admin_url( 'admin.php?page=' . Page::NAMES[ 'on-boarding' ] ),
				];
			}

			return $return;
		}
	}