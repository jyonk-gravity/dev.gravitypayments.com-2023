<?php

namespace WPDRMS\ASP\Utils\AdvancedField;

use stdClass;
use WPDRMS\ASP\Utils\AdvancedField\Types\AdvancedFieldTypeInterface;
use WPDRMS\ASP\Utils\AdvancedField\Types\LegacyResultsFieldTypes;
use WPDRMS\ASP\Utils\AdvancedField\Types\LegacyUserMetaFieldTypes;
use WPDRMS\ASP\Utils\AdvancedField\Types\ResultsFieldTypes;
use WPDRMS\ASP\Utils\AdvancedField\Types\UserMetaFieldTypes;

class UserFieldTypeFactory {

	/**
	 * Order obviously matter, but i am not going to explain why.
	 *
	 * @var array<class-string<AdvancedFieldTypeInterface>, string[]>
	 */
	private array $rules = array(
		LegacyResultsFieldTypes::class => array(
			'titlefield',
			'descriptionfield',
			'__id',
			'__title',
			'__content',
			'__link',
			'__url',
			'__image',
			'__date',
		),
		ResultsFieldTypes::class       => array( 'result_field' ),
		UserMetaFieldTypes::class      => array( 'custom_field' ),
	);

	/**
	 * @param string               $field
	 * @param array<string, mixed> $field_args
	 * @param stdClass             $result
	 * @return AdvancedFieldTypeInterface
	 */
	public function create( string $field, array $field_args, stdClass $result ): AdvancedFieldTypeInterface {
		foreach ( $this->rules as $class => $fields ) {
			if ( in_array($field, $fields, true) ) {
				return new $class($field, $field_args, $result);
			}
		}
		return new LegacyUserMetaFieldTypes($field, $field_args, $result);
	}
}
