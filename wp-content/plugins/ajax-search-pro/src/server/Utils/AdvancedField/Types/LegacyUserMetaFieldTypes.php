<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;
use WPDRMS\ASP\Utils\Post;
use WPDRMS\ASP\Utils\User;

/**
 * Handles special and built in advanced field types related to results fields
 */
class LegacyUserMetaFieldTypes extends UserMetaFieldTypes implements AdvancedFieldTypeInterface {
	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		parent::__construct($field, $field_args, $result);
		$this->field = $field;
	}
}
