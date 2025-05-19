<?php
namespace WPDRMS\ASP\Utils;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * User utility functions
 */
class User {
	/**
	 * Gets the custom user meta field value, supporting ACF get_field()
	 *
	 * @param string     $field      Custom field label
	 * @param object|int $r          Result object||Result ID
	 * @param bool       $use_acf    If true, will use the get_field() function from ACF
	 * @param string     $separator
	 * @return string
	 * @see get_field() ACF post meta parsing.
	 */
	public static function getCFValue( string $field, $r, bool $use_acf = false, string $separator = ', ' ): string {
		$ret = '';
		if ( is_object($r) ) {
			$id = $r->id ?? 0;
		} else {
			$id = intval($r);
		}

		if ( $use_acf && function_exists('get_field') ) {
			$field_values = get_field($field, 'user_' . $id, true);
			if ( !is_null($field_values) && $field_values !== '' && $field_values !== false ) {
				if ( is_array($field_values) ) {
					if ( !is_object($field_values[0]) ) {
						$ret = Str::anyToString($field_values, $separator);
					}
				} else {
					$ret = Str::anyToString($field_values, $separator);
				}
			}
		} else {
			$field_values = get_user_meta($id, $field);
			if ( isset($field_values[0]) ) {
				$ret = Str::anyToString($field_values[0], $separator);
			}
		}

		return $ret;
	}

	public static function getMetaValueArray(
		int $user_id,
		string $field,
		// string $separator = ', ',
		string $source = 'user_meta',
		bool $use_acf = true
	): array {
		$ret = array();
		if ( $use_acf && function_exists('get_field') ) {
			$field_values = get_field($field, 'user_' . $user_id, true);
		} else {
			$field_values = get_user_meta($user_id, $field);
		}

		if ( !is_null($field_values) && $field_values !== '' && $field_values !== false ) {
			$ret = Post::processCFValue($field_values, $source);
			// $ret = Str::anyToString( $ret, $separator );
		}
		return $ret;
	}
}
