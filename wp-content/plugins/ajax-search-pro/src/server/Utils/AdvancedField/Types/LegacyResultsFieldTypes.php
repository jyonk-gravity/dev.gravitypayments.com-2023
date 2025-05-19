<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;

class LegacyResultsFieldTypes implements AdvancedFieldTypeInterface {
	private string $field;
	private ?stdClass $result;
	private ?string $date_format;
	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		$this->field       = $field;
		$this->result      = $result;
		$this->date_format = $field_args['date_format'] ?? null;
	}

	/**
	 * Results fields
	 *
	 * ```
	 * {__id}
	 * ```
	 *
	 * @return string
	 */
	public function process(): string {
		if ( is_null($this->result) ) {
			return '';
		}

		switch ( $this->field ) {
			case '__id':
				return $this->result->id ?? '';
			case 'titlefield': // legacy
			case '__title':
				return $this->result->title ?? '';
			case 'descriptionfield': // legacy
			case '__content':
				return $this->result->content ?? '';
			case '__post_type':
				if ( isset($this->result->post_type) ) {
					$post_type_obj = get_post_type_object( $this->result->post_type );
					return !is_null($post_type_obj) ? $post_type_obj->labels->singular_name : '';
				}
				break;
			case '__link':
			case '__url':
				return $this->result->link ?? '';
			case '__image':
				return $this->result->image ?? '';
			case '__date':
				if ( isset($this->result->date) ) {
					if ( !is_null($this->date_format) ) {
						return date_i18n($this->date_format, strtotime($this->result->date));
					} else {
						return $this->result->date;
					}
				}
				break;
			case '__author':
				return isset($this->result->author) && $this->result->author !== '' ?
					$this->result->author :
					get_the_author_meta(
						'display_name',
						intval(get_post_field('post_author', $this->result->id))
					);
		}

		return '';
	}
}
