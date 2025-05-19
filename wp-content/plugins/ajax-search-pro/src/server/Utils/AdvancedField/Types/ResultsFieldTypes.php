<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;

class ResultsFieldTypes implements AdvancedFieldTypeInterface {
	private string $field;
	private ?stdClass $result;
	private ?string $date_format;
	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		$this->field       = $field_args['field_name'] ?? '';
		$this->result      = $result;
		$this->date_format = $field_args['date_format'] ?? null;
	}

	/**
	 * Results fields
	 *
	 * @return string
	 */
	public function process(): string {
		if ( is_null($this->result) ) {
			return '';
		}

		switch ( $this->field ) {
			case 'id':
				return $this->result->id ?? '';
			case 'title':
				return $this->result->title ?? '';
			case 'content':
				return $this->result->content ?? '';
			case 'post_type':
				if ( isset($this->result->post_type) ) {
					$post_type_obj = get_post_type_object( $this->result->post_type );
					return !is_null($post_type_obj) ? $post_type_obj->labels->singular_name : '';
				}
				break;
			case 'link':
			case 'url':
				return $this->result->link ?? '';
			case 'image':
				return $this->result->image ?? '';
			case 'date':
				if ( isset($this->result->date) ) {
					if ( !is_null($this->date_format) ) {
						return date_i18n($this->date_format, strtotime($this->result->date));
					} else {
						return $this->result->date;
					}
				}
				break;
			case 'author':
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
