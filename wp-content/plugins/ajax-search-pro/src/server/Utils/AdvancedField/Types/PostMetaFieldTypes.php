<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;
use WPDRMS\ASP\Utils\Post;
use WPDRMS\ASP\Utils\User;

/**
 * Handles special and built in advanced field types related to results fields
 */
class PostMetaFieldTypes implements AdvancedFieldTypeInterface {
	protected bool $use_acf;
	protected string $field;

	/**
	 * @var 'text' | 'number' | 'date'
	 */
	protected string $type;

	/**
	 * @var 'post_meta' | 'user_meta' | 'pods' | 'relationship'
	 */
	protected string $source;

	/**
	 * @var string
	 */
	protected string $separator;

	/**
	 * @var string
	 */
	protected string $separation;

	/**
	 * @var string
	 */
	protected string $thousand_separator;

	/**
	 * @var int
	 */
	protected int $decimals;

	/**
	 * @var string
	 */
	protected string $decimal_separator;

	/**
	 * @var string
	 */
	protected string $date_format;

	protected ?stdClass $result;

	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		$this->use_acf            = wd_asp()->o['asp_compatibility']['use_acf_getfield'];
		$this->field              = $field_args['field'] ?? '';
		$this->result             = $result;
		$this->type               = $field_args['type'] ?? 'text';
		$this->source             = $field_args['source'] ?? 'post_meta';
		$this->separation         = $field_args['separation'] ?? 'text';
		$this->separator          = $this->separation === 'text' ? ( $field_args['separator'] ?? ', ' ) : '</li><li>';
		$this->thousand_separator = $field_args['thousand_separator'] ?? ',';
		$this->decimals           = intval($field_args['decimals'] ?? 0);
		$this->decimal_separator  = $field_args['decimal_separator'] ?? '.';
		$this->date_format        = $field_args['date_format'] ?? get_option( 'date_format' );
	}

	public function process(): string {
		if ( is_null($this->result) ) {
			return '';
		}

		if ( $this->source === 'pods' ) {
			// PODs field
			$values = Post::getPODsValue($this->field, $this->result);
		} elseif ( $this->source === 'user_meta' ) {
			// User Meta Field
			$author = get_post_field( 'post_author', $this->result->id );
			$values = User::getMetaValueArray( intval($author), $this->field, 'user_meta', $this->use_acf);
		} else {
			// Custom field or Relationship
			$values = Post::getMetaValueArray(
				$this->result->id,
				$this->field,
				// $this->separator,
				$this->source
			);
		}

		return $this->format( $values );
	}

	/**
	 * @param string[] $values
	 * @return string
	 */
	protected function format( array $values ): string {
		$values = wd_flatten_array($values);

		foreach ( $values as &$value ) {
			if ( $this->type === 'date' ) {
				$value = date_i18n(
					$this->date_format,
					is_numeric($value) ? intval($value) : strtotime( $value )
				);
			} elseif ( $this->type === 'number' ) {
				$value = number_format(
					floatval($value),
					$this->decimals,
					$this->decimal_separator,
					$this->thousand_separator
				);

			}
		}

		if ( empty($values) ) {
			return '';
		}

		$partial_html = implode($this->separator, $values);
		return $this->separation === 'text' ? $partial_html : '<ul><li>' . $partial_html . '</li></ul>';
	}
}
