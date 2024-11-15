<?php

	namespace ShortPixel\AI\LQIP;

	use ShortPixelAI;
	use ShortPixel\AI\LQIP;
	use ShortPixelUrlTools;
	use ShortPixel\AI\Converter;

	class Actions {
		/**
		 * Method handles the pages's actions
		 * Works via AJAX
		 */
		public static function handle() {
			if ( ShortPixelAI::isAjax() ) {
				$data   = isset( $_POST[ 'data' ] ) ? $_POST[ 'data' ] : null;
				$action = isset( $data[ 'action' ] ) ? $data[ 'action' ] : null;

				$response = [ 'success' => false ];

				if ( !empty( $action ) && is_string( $action ) ) {
					$action = Converter::toTitleCase( $action );

					unset( $data[ 'action' ] );

					$response = call_user_func( [ 'self', 'handle' . $action ], isset( $data ) ? $data : null );
				}

				wp_send_json( $response, 200 );
			}

			return null;
		}

		/**
		 * Handles collect action
		 *
		 * @param $data
		 *
		 * @return array
		 */
		private static function handleCollect( $data ) {
			$collection = isset( $data[ 'collection' ] ) ? $data[ 'collection' ] : null;
			$referer = $data['referer'];
			$collection = array_map(function($item) use ($referer) {$item['referer'] = $referer; return $item;}, $collection);
			$data  = LQIP::_()->process( $collection );
			$processed = $data['processed'];

			return [
				'success'    => true,
				'message'    => $processed ? __( 'Collection has been updated', 'shortpixel-adaptive-images' ) : __( 'Collection has not been updated', 'shortpixel-adaptive-images' ) . ' (' . $data['message'] . ')',
				'collection' => $processed,
			];
		}
	}