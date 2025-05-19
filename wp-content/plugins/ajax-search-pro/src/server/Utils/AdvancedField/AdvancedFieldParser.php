<?php

namespace WPDRMS\ASP\Utils\AdvancedField;

use InvalidArgumentException;
use stdClass;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Utils\Str;

class AdvancedFieldParser {
	use SingletonTrait;

	/**
	 * @var array{
	 *      callback: callable(null|array<string, mixed>, null|stdClass):void,
	 *      field_name: string,
	 *      field_title: string,
	 *      attributes?: Array<string, array{
	 *          type: 'text'|'textarea'|'number'|'color'|'select'|'multiselect'|'checkbox'
	 *      }>
	 *  } | array{}
	 */
	private array $handlers = array();

	private PostFieldTypeFactory $post_type_field_factory;
	private UserFieldTypeFactory $user_type_field_factory;

	private function __construct() {
		$this->post_type_field_factory = new PostFieldTypeFactory();
		$this->user_type_field_factory = new UserFieldTypeFactory();

		// In case of additional external types, this hooks should be used
		do_action('asp/utils/advanced-field', $this);
	}

	/**
	 * Adds a field with
	 *
	 * @param array{
	 *     field_name: string,
	 *     callback: callable,
	 *     type?: 'post_type'|'user'
	 * } $args
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function add( array $args ) {
		$field_args = $args;
		if ( !isset($field_args['field_name']) ) {
			throw new InvalidArgumentException('field_name is missing');
		}
		if ( !isset($field_args['callback']) || !is_callable($field_args['callback']) ) {
			throw new InvalidArgumentException('callback is missing or invalid');
		}
		$field_args['type'] = $field_args['type'] ?? 'pagepost';
		if ( $field_args['type'] === 'post_type' ) {
			$field_args['type'] = 'pagepost';
		}
		$this->handlers[ $args['field_name'] ] = $field_args;
	}

	/**
	 * Parses the advanced field based on the registered handlers
	 *
	 * Advanced fields can use the following syntax:
	 *
	 * {} curly braces including variable name. The registered handlers will look for these and replace
	 * with the processed text. Example: `{_price}` will become `$100`
	 *
	 * [] brackets to encapsulate field variables. When either of the variables are empty within the curly
	 * brackets, it is resolved to an empty string.
	 * Example: `[The price is {_price}]` will become `The price is 100$` or "" if the _price field resolves to ""
	 *
	 * [[]] double brackets to indicate shortcodes. Example `[[my_shortcode]]`
	 *
	 * @param string   $text
	 * @param stdClass $result
	 * @return string
	 */
	public function parse( string $text, stdClass $result ): string {
		return $this->process( stripslashes($text), $result );
	}

	/**
	 * A recursive function to process the advanced title/content field values.
	 *
	 * @param string   $text
	 * @param stdClass $result
	 * @param bool     $return_on_empty_result
	 * @param int      $depth
	 * @return string
	 */
	private function process( string $text, stdClass $result, bool $return_on_empty_result = false, int $depth = 0 ): string {
		$field_pattern = $text; // Let's not make changes to arguments, shall we.

		// Handle shortcode patterns
		if ( $depth === 0 && strpos($field_pattern, '[[') !== false ) {
			$do_shortcodes = true;
			$field_pattern = str_replace(
				array( '[[', ']]' ),
				array( '____shortcode_start____', '____shortcode_end____' ),
				$field_pattern
			);
		} else {
			$do_shortcodes = false;
		}

		// Find conditional patterns, like [prefix {field} suffix}
		if ( preg_match_all( '/(\[.*?\])/', $text, $matches ) ) {
			foreach ( $matches[1] as $fieldset ) {
				$processed_fieldset = $this->process(
					str_replace(array( '[', ']' ), '', $fieldset),
					$result,
					true,
					$depth + 1
				);
				$field_pattern      = Str::replaceFirst($fieldset, $processed_fieldset, $field_pattern);
			}
		}

		if ( preg_match_all( '/{(.*?)}/', $field_pattern, $matches ) ) {
			foreach ( $matches[1] as $complete_field ) {
				$val        = '';
				$field_args = shortcode_parse_atts($complete_field);
				if ( is_array($field_args) && isset($field_args[0]) ) {
					$field = array_shift($field_args);
				} else {
					continue;
				}

				foreach ( $this->handlers as $field_name => $handler_args ) {
					if ( $field_name === $field && $handler_args['type'] === $result->content_type ) {
						$val = Str::anyToString( $handler_args['callback']($field_args, $result) );
					}
				}

				if ( $val === '' ) {
					if ( $result->content_type === 'pagepost' || $result->content_type === 'attachment' ) {
						$val = $this->post_type_field_factory->create($field, $field_args, $result)->process();
					} elseif ( $result->content_type === 'user' ) {
						$val = $this->user_type_field_factory->create($field, $field_args, $result)->process();
					}
					$val = apply_filters('asp/utils/advanced-field/raw_value', $val, $field, $result, $field_args);
				}

				// For the recursive call to break, if any of the fields is empty
				if ( $return_on_empty_result && trim($val) === '' ) {
					return '';
				}
				$val = Str::fixSSLURLs($val);

				if ( isset($field_args['maxlength']) ) {
					$val = wd_substr_at_word($val, $field_args['maxlength']);
				}

				if ( $result->content_type === 'pagepost' || $result->content_type === 'attachment' ) {
					$val = apply_filters('asp_cpt_advanced_field_value', $val, $field, $result, $field_args);
				} elseif ( $result->content_type === 'user' ) {
					$val = apply_filters('asp_user_advanced_field_value', $val, $field, $result, $field_args);
				}
				$val           = apply_filters('asp/utils/advanced-field/value', $val, $field, $result, $field_args);
				$field_pattern = str_replace( '{' . $complete_field . '}', $val, $field_pattern );
			}
		}

		// On depth=0 and if tags were found $do_shortcodes is true
		if ( $do_shortcodes ) {
			$field_pattern = str_replace(
				array( '____shortcode_start____', '____shortcode_end____' ),
				array( '[', ']' ),
				$field_pattern
			);
			$field_pattern = do_shortcode($field_pattern);
		}

		return $field_pattern;
	}
}
