<?php

	namespace ShortPixel\AI;

	class Request {
		private static $arguments = [ 'timeout' => 15, 'httpversion' => '1.1' ];

		public static function get( $url, $json = true, $arguments = null ) {
			$arguments = is_array( $arguments ) && !empty( $arguments ) ? $arguments : self::$arguments;
			$arguments = array_merge( $arguments, [ 'method' => 'GET' ] );
			$json      = is_bool( $json ) ? $json : true;

			return self::request( $url, $json, $arguments );
		}

		public static function post( $url, $json = true, $arguments = null ) {
			$arguments = is_array( $arguments ) && !empty( $arguments ) ? $arguments : self::$arguments;
			$arguments = array_merge( $arguments, [ 'method' => 'POST' ] );
			$json      = is_bool( $json ) ? $json : true;

			return self::request( $url, $json, $arguments );
		}

		private static function request( $url, $json, $arguments ) {
			$arguments = is_array( $arguments ) && !empty( $arguments ) ? array_merge( self::$arguments, $arguments ) : self::$arguments;

			$response = wp_safe_remote_request( $url, $arguments );

			if ( is_wp_error( $response ) ) {
                \ShortPixelAILogger::instance()->log("REQUEST ERR", $response);
				return $response;
			}

			if ( !isset( $response[ 'response' ] ) || !is_array( $response[ 'response' ] ) ) {
                \ShortPixelAILogger::instance()->log("REQUEST UNKNOWN:", $response);
				return '';
			}

			if ( !isset( $response[ 'body' ] ) ) {
                \ShortPixelAILogger::instance()->log("REQUEST PAYLOAD EMPTY: ", $response);
				return '';
			}

			return !!$json ? json_decode( $response[ 'body' ] ) : $response[ 'body' ];
		}
	}