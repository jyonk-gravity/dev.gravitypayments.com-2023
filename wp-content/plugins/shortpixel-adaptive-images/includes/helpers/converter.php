<?php

	namespace ShortPixel\AI;

	class Converter {
		/**
		 * Method returns transformed string into snake_case
		 *
		 * @param string $string String to be transformed to snake_case
		 *
		 * @return string|false
		 */
		public static function toSnakeCase( $string ) {
			// 1st Reg Ex is an all non-alphabetic chars, 2nd Reg Ex is an all whitespace chars
			return is_string( $string ) && !empty( $string ) ? strtolower( preg_replace( [ '/(?>[^_A-Za-z\d\s]+)/su', '/[\s]+/su' ], [ '', '_' ], trim( $string ) ) ) : null;
		}

        public static function snakeToCamelCase($string, $capitalizeFirstCharacter = false)
        {

            $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

            if (!$capitalizeFirstCharacter) {
                $str[0] = strtolower($str[0]);
            }

            return $str;
        }

        /**
		 * Method returns transformed string from camelCase to snake_case
		 *
		 * @param $string String to be transformed from camelCase to snake_case
		 *
		 * @return string
		 */
		public static function fromCamelCase( $string ) {
			preg_match_all( '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches );

			$return = $matches[ 0 ];

			foreach ( $return as &$match ) {
				$match = $match == strtoupper( $match ) ? strtolower( $match ) : lcfirst( $match );
			}

			return implode( '_', $return );
		}

		public static function toTitleCase(
			$string,
			$delimiters = [ " ", "-", ".", "'", "O'", "Mc" ],
			$exceptions = [ "út",
							"u",
							"s",
							"és",
							"utca",
							"tér",
							"krt",
							"körút",
							"sétány",
							"I",
							"II",
							"III",
							"IV",
							"V",
							"VI",
							"VII",
							"VIII",
							"IX",
							"X",
							"XI",
							"XII",
							"XIII",
							"XIV",
							"XV",
							"XVI",
							"XVII",
							"XVIII",
							"XIX",
							"XX",
							"XXI",
							"XXII",
							"XXIII",
							"XXIV",
							"XXV",
							"XXVI",
							"XXVII",
							"XXVIII",
							"XXIX",
							"XXX",
			]
		) {
			/*
			 * Exceptions in lower case are words you don't want converted
			 * Exceptions all in upper case are any words you don't want converted to title case
			 *   but should be converted to upper case, e.g.:
			 *   king henry viii or king henry Viii should be King Henry VIII
			 */
			$string = mb_convert_case( $string, MB_CASE_TITLE, "UTF-8" );

			foreach ( $delimiters as $dlnr => $delimiter ) {
				$words    = explode( $delimiter, $string );
				$newwords = [];
				foreach ( $words as $wordnr => $word ) {

					if ( in_array( mb_strtoupper( $word, "UTF-8" ), $exceptions ) ) {
						// check exceptions list for any words that should be in upper case
						$word = mb_strtoupper( $word, "UTF-8" );
					}
					else if ( in_array( mb_strtolower( $word, "UTF-8" ), $exceptions ) ) {
						// check exceptions list for any words that should be in upper case
						$word = mb_strtolower( $word, "UTF-8" );
					}

					else if ( !in_array( $word, $exceptions ) ) {
						// convert to uppercase (non-utf8 only)

						$word = ucfirst( $word );
					}
					array_push( $newwords, $word );
				}
				$string = join( $delimiter, $newwords );
			}

			return preg_replace( '/(?:\.|-|_|\s)+/su', '', $string );
		}
	}