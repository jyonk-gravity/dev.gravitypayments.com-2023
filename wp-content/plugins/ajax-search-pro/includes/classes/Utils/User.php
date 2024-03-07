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
	 * @param string                      $field      Custom field label
	 * @param object|int                  $r          Result object||Result ID
	 * @param bool                        $use_acf    If true, will use the get_field() function from ACF
	 * @param array<string, float|string> $field_args Additional field arguments
	 * @return string
	 * @see get_field() ACF post meta parsing.
	 */
	public static function getCFValue( string $field, $r, bool $use_acf = false, array $field_args = array() ): string {
		$ret = '';
		if ( is_object($r) ) {
			$id = $r->id ?? 0;
		} else {
			$id = intval($r);
		}
		$separator = $field_args['separator'] ?? ', ';
		$separator = strval($separator);

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
}
