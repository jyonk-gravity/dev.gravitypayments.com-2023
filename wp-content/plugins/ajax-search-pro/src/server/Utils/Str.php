<?php
/*
@noinspection PhpComposerExtensionStubsInspection */
/* @noinspection RegExpRedundantEscape */
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");


class Str {
	/**
	 * Performs a safe sanitation and escape for strings and numeric values in LIKE type queries.
	 * This is not to be used on whole queries, only values.
	 *
	 * @param mixed  $input
	 * @param bool   $remove_quotes
	 * @param string $remove
	 * @return array|mixed
	 * @uses wd_mysql_escape_mimic()
	 */
	public static function escape( $input, bool $remove_quotes = false, string $remove = '' ) {

		// recursively go through if it is an array
		if ( is_array($input) ) {
			foreach ( $input as $k => $v ) {
				$input[ $k ] = self::escape($v, $remove_quotes, $remove);
			}
			return $input;
		}

		if ( is_float( $input ) || is_int( $input ) ) {
			return $input;
		}

		if ( $remove_quotes ) {
			$input = str_replace(
				array(
					chr(145),
					chr(146),
					chr(147),
					chr(148),
					chr(150),
					chr(151),
					chr(133),
					"'",
					'"',
				),
				'',
				$input
			);
		}
		if ( !empty($remove) ) {
			$input = str_replace(str_split($remove), '', $input);
		}

		if ( function_exists( 'esc_sql' ) ) {
			return esc_sql( $input );
		}

		// Okay, what? Not one function is present, use the one we have
		return wd_mysql_escape_mimic($input);
	}

	/**
	 * Checks if the given date format matches the pattern YYYY-MM-DD
	 *
	 * It does not validate the date, only the format.
	 *
	 * @param string $date
	 * @return bool
	 */
	public static function checkDate( string $date ): bool {
		if ( MB::strlen( $date ) !== 10 ) {
			return false;
		}

		return (bool) preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}\z/', $date);
	}

	/**
	 * Converts a string to number, array of strings to array of numbers
	 *
	 * Since esc_like() does not escape numeric values, casting them is the easiest way to go
	 *
	 * @param mixed $number
	 * @return int[]|float[]|int|float
	 */
	public static function forceNumeric( $number ) {
		if ( is_array($number) ) {
			foreach ( $number as $k => $v ) {
				$number[ $k ] = self::forceNumeric($v);
			}
			return $number;
		} else {
			if ( is_int($number) || is_float($number) ) {
				return $number;
			}
			if ( $number === null ) {
				return 0;
			}
			// Replace any non-numeric and decimal point character
			$number = preg_replace('/[^0-9\.\-]+/', '', $number);
			// PHP 7.x+ 'feature' - empty string, or non-numeric value throws an error
			// ...in this case it is either a number or an empty string at this point, so check for empty string
			$number = empty($number) ? 0 : $number; // Check explanation above
			// Still something fishy?
			$number = !is_numeric($number) ?
				strpos($number, '.') !== false ? floatval($number) : intval($number) :
				$number;
			$number = $number + 0;
		}

		return $number;
	}

	/**
	 * Generates a string reverse, support multibite strings, plus fallback if mbstring not avail
	 *
	 * @param string $s
	 * @return string
	 */
	public static function reverse( string $s ): string {
		/*
		 * Not sure if using extension_loaded(...) is enough.
		 */
		if (
			function_exists('mb_detect_encoding') &&
			function_exists('mb_strlen') &&
			function_exists('mb_substr')
		) {
			// Using mbstring
			$encoding = mb_detect_encoding($s);
			$encoding = $encoding === false ? null : $encoding;
			$length   = mb_strlen($s, $encoding); // @phpstan-ignore-line
			$reversed = '';
			while ( $length-- > 0 ) {
				$reversed .= mb_substr($s, $length, 1, $encoding); // @phpstan-ignore-line
			}

			return $reversed;

		} else {
			// Good old regex method, still supporting fully UFT8
			preg_match_all('/./us', $s, $ar);
			return implode(array_reverse($ar[0]));
		}
	}

	/**
	 * Converts anything to string
	 *
	 * @param mixed  $any
	 * @param string $separator
	 * @param int    $level
	 * @return string
	 */
	public static function anyToString( $any, string $separator = ' ', int $level = 0 ): string {
		$str = '';
		if ( is_string($any) && $level === 0 ) {
			$any = maybe_unserialize($any);

			/**
			 * String check is required again, as only string is accepted for json_validate
			 * and at this point it can be an array or object.
			 */
			if ( is_string($any) && function_exists('json_validate') && json_validate($any) ) {
				$any = json_decode($any, true);
			}
		}
		if ( is_array( $any ) ) {
			$str_arr = array();
			foreach ( $any as $sub_arr ) {
				$str_arr[] = self::anyToString( $sub_arr, $separator, $level + 1 );
			}
			$str_arr = array_filter( $str_arr, fn( $value ) => $value !== '' );
			$str     = implode( $separator, $str_arr );
		} else {
			// Invalid values
			if ( is_bool($any) || is_null($any) || ( is_float($any) && is_nan($any) ) ) {
				return '';
			}

			// Check for objects, as those yield a fatal error when converted to strings
			if ( !is_object($any) ) {
				$str = (string) $any;
			}
		}

		return $str;
	}

	/**
	 * Replaces the first occurrence of the $find string with $replace within the $subject.
	 *
	 * @param string $find
	 * @param string $replace
	 * @param string $subject
	 * @return string
	 * @since 4.11
	 */
	public static function replaceFirst( string $find, string $replace, string $subject ): string {
		if ( $find === '' ) {
			return $subject;
		}
		// From the comments at PHP.net/str_replace
		// Splits $subject into an array of 2 items by $find,
		// and then joins the array with $replace
		return implode($replace, explode($find, $subject, 2));
	}


	/**
	 * @param string $s
	 * @return string
	 */
	public static function fixSSLURLs( string $s ): string {
		if ( ASP_SITE_IS_PROBABLY_SSL ) {
			/** @noinspection HttpUrlsUsage */
			return str_replace('http://', 'https://', $s);
		}
		return $s;
	}

	/**
	 * Clears and trims a search phrase from extra slashes and extra space characters
	 *
	 * @param string $s
	 * @return string
	 */
	public static function clear( string $s ): string {
		// Step 2: Remove all control (non-printable) characters except for whitespace characters
		// Control characters range from \x00 to \x1F and \x7F
		// We'll preserve whitespace characters like space, tab, newline, etc.
		$cleaned = preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/u', '', $s);
		if ( $cleaned === null ) {
			return '';
		}

		// Step 1: Remove all backslashes
		$stripped = str_replace('\\', ' ', $cleaned);

		// Step 3: Trim leading and trailing whitespace
		$trimmed = trim($stripped);

		// Step 4: Replace multiple whitespace characters with a single space
		$res = preg_replace('/\s+/', ' ', $trimmed);

		// Step 5: Ensure the result is a string
		return is_string($res) ? $res : '';
	}

	/**
	 * PHP 7.4 way of json_validate()
	 *
	 * @param string $str
	 * @return bool
	 */
	public static function isJson( string $str ): bool {
		$json = json_decode($str);
		return $json && $str !== $json;
	}

	/**
	 * Strips given HTML tags and all of their contents from a text
	 *
	 * Similar to strip_tags() but the contents are also removed
	 *
	 * @param string|string[] $text String or array of strings
	 * @param string[]        $tags
	 * @return string|string[] Concatenated text
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function stripTagsWithContent( $text, $tags = array() ) {
		foreach ( $tags as $tag ) {
			$text = preg_replace('/<' . $tag . '>(.*?)<\/' . $tag . '>/s', '', $text);
			if ( $text === null ) {
				return '';
			}
			$text = preg_replace('/<\\/?' . $tag . '(.|\\s)*?>/', '', $text);
			if ( $text === null ) {
				return '';
			}
			// New line is required to split non-blank lines
			$text = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
			if ( $text === null ) {
				return '';
			}
		}
		return $text;
	}

	/**
	 * Removes WordPress Gutenberg editor blocks with their contents from the given string by block names array
	 *
	 * @param string   $content
	 * @param string[] $block_names
	 * @return string
	 */
	public static function removeGutenbergBlocks( string $content, array $block_names = array( 'core-embed/*' ) ): string {
		foreach ( $block_names as &$block_name ) {
			$block_name = str_replace('*', 'AAAAAAAAAAAAAAA', $block_name);
			$block_name = preg_quote($block_name, '~'); // Escaping with delimiter
			$block_name = str_replace('AAAAAAAAAAAAAAA', '.*?', $block_name);
		}
		$block_names = implode('|', $block_names);

		// Use '~' as the regex delimiter to avoid escaping '/'
		$ret = preg_replace(
			"~<!--\s+wp:($block_names)(\s+\{.*?\})?\s+-->(.*?)<!--\s+\/wp:($block_names)\s+-->~s",
			'',
			$content
		);
		return $ret === null ? '' : $ret;
	}

	/**
	 * Removes accents via transliterator and the wordpress core remove_accents function
	 *
	 * @param string $str
	 * @return string
	 */
	public static function removeAccents( string $str ): string {
		// Hebrew transliteration does not work correctly, no vowels.
		if ( preg_match("/\p{Hebrew}/u", $str) ) {
			return $str;
		}

		$s = $str;
		if (
			class_exists('\\Transliterator') &&
			method_exists('\\Transliterator', 'transliterate')
		) {
			$transliterator = \Transliterator::createFromRules(
				':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;',
				\Transliterator::FORWARD
			);
			if ( $transliterator !== null ) {
				$s = $transliterator->transliterate($s);
				if ( $s === false ) {
					$s = $str;
				}
			}
		}
		return remove_accents($s);
	}

	/**
	 * Resolves content with bracket syntax (such as no results fields etc...)
	 *
	 * @param $content
	 * @param array   $fields fieldN=>contentN keypairs for replacements
	 * @param bool    $empty_on_missing if any of the field contents is empty, leave the whole expression empty
	 * @return string
	 */
	public static function resolveBracketSyntax( $content, array $fields = array(), bool $empty_on_missing = false ): string {

		if ( empty($fields) ) {
			return $content;
		}

		// Find conditional patterns, like [prefix {field} suffix]
		preg_match_all( '/(\[.*?\])/', $content, $matches );
		if ( isset( $matches[0] ) && isset( $matches[1] ) && is_array( $matches[1] ) ) {
			foreach ( $matches[1] as $fieldset ) {
				// Pass on each section to this function again, the code will never get here
				$stripped_fieldset = str_replace(array( '[', ']' ), '', $fieldset);
				$processed_content = self::resolveBracketSyntax($stripped_fieldset, $fields, true);

				// Replace the original with the processed version, first occurrence, in case of duplicates
				$content = self::replaceFirst($fieldset, $processed_content, $content);
			}
		}

		preg_match_all( '/{(.*?)}/', $content, $matches );
		if ( isset( $matches[0] ) && isset( $matches[1] ) && is_array( $matches[1] ) ) {
			foreach ( $matches[1] as $field ) {
				$val = $fields[ $field ] ?? '';
				// For the recursive call to break, if any of the fields is empty
				if ( $empty_on_missing && $val == '' ) {
					return '';
				}
				$content = str_replace( '{' . $field . '}', $val, $content );
			}
		}

		return $content;
	}


	/**
	 * Checks if a string is Base64 encoded
	 *
	 * @param string $s
	 * @return bool
	 */
	public static function isBase64Encoded( string $s ): bool {
		if ( (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s) === false ) {
			return false;
		}
		$decoded = base64_decode($s, true);
		if ( $decoded === false ) {
			return false;
		}
		if ( function_exists('mb_detect_encoding') ) {
			$encoding = mb_detect_encoding($decoded);
			if ( !in_array($encoding, array( 'UTF-8', 'ASCII' ), true) ) {
				return false;
			}
		}
		return $decoded !== false && base64_encode($decoded) === $s;
	}


	public static function getContext( $content, $length, $depth, $phrase, $phrase_arr ): string {
		// Try for an exact match
		if ( count( $phrase_arr ) > 0 && $phrase != '' ) {
			$_ex_content = self::contextFind(
				$content,
				$phrase,
				floor($length / 6),
				$length,
				$depth,
				true
			);
			if ( $_ex_content === false ) {
				// No exact match, go with the first keyword
				$content = self::contextFind(
					$content,
					$phrase_arr[0],
					floor($length / 6),
					$length,
					$depth
				);
			} else {
				$content = $_ex_content;
			}
		}

		return $content;
	}


	/**
	 * Returns the context of a phrase within a text.
	 * Uses preg_split method to iterate through strings.
	 *
	 * @param $str - string context
	 * @param $needle - string context
	 * @param $context - int length of the context
	 * @param $maxlength - int maximum length of the string in characters
	 * @param $str_length_limit - source string maximum length
	 * @return string
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function contextFind( $str, $needle, $context, $maxlength, $str_length_limit = 10000, $false_on_no_match = false ) {
		$haystack = remove_accents(' ' . trim($str) . ' ');

		// To prevent memory overflow, we need to limit the hay to relatively low count
		$haystack = wd_substr_at_word(MB::strtolower($haystack), $str_length_limit, '');
		$needle   = remove_accents(MB::strtolower($needle));

		if ( $needle == '' ) {
			if ( MB::strlen($str) > $maxlength ) {
				return wd_substr_at_word($str, $maxlength);
			} else {
				return $str;
			}
		}

		/**
		 * This is an interesting issue. Turns out mb_substr($hay, $start, 1) is very ineffective.
		 * the preg_split(...) method is far more efficient in terms of speed, however it needs much more
		 * memory. In our case speed is the top priority. However, to prevent memory overflow, the haystack
		 * is reduced to 10000 characters (roughly 1500 words) first.
		 *
		 * Reference ticket: https://wp-dreams.com/forums/topic/search-speed/
		 * Speed tests: http://stackoverflow.com/questions/3666306/how-to-iterate-utf-8-string-in-php
		 */
		$chrArray   = preg_split('//u', $haystack, -1, PREG_SPLIT_NO_EMPTY);
		$hay_length = count($chrArray) - 1;

		if ( $i = MB::strpos($haystack, $needle) ) {
			$start  =$i;
			$end    =$i;
			$spaces =0;

			while ( $spaces < ( (int) $context /2 ) && $start > 0 ) {
				--$start;
				if ( $chrArray[ $start ] == ' ' ) {
					++$spaces;
				}
			}

			while ( $spaces < ( $context +1 ) && $end < $hay_length ) {
				++$end;
				if ( $chrArray[ $end ] == ' ' ) {
					++$spaces;
				}
			}

			while ( $spaces < ( $context +1 ) && $start > 0 ) {
				--$start;
				if ( $chrArray[ $start ] == ' ' ) {
					++$spaces;
				}
			}

			$str_start = ( $start - 1 ) < 0 ? 0 : ( $start -1 );
			$str_end   = ( $end - 1 ) < 0 ? 0 : ( $end -1 );

			$result = trim( MB::substr($str, $str_start, ( $str_end - $str_start )) );

			// Somewhere inbetween..
			if ( $start != 0 && $end < $hay_length ) {
				return '... ' . $result . ' ...';
			}

			// Beginning
			if ( $start == 0 && $end < $hay_length ) {
				return $result . ' ...';
			}

			// End
			if ( $start != 0 && $end == $hay_length ) {
				return '... ' . $result;
			}

			// If it is too long, strip it
			if ( MB::strlen($result) > $maxlength ) {
				return wd_substr_at_word( $result, $maxlength );
			}

			// Else, it is the whole
			return $result;

		} else {
			if ( $false_on_no_match ) {
				return false;
			}

			// If it is too long, strip it
			if ( MB::strlen($str) > $maxlength ) {
				return wd_substr_at_word( $str, $maxlength );
			}

			return $str;
		}
	}
}
