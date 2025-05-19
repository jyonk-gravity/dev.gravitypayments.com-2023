<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use WPDRMS\ASP\Utils\User;

/**
 * Handles special and built in advanced field types related to results fields
 */
class UserMetaFieldTypes extends PostMetaFieldTypes implements AdvancedFieldTypeInterface {

	public function process(): string {
		if ( is_null($this->result) || empty($this->field) ) {
			return '';
		}

		$values = User::getMetaValueArray(
			$this->result->id,
			$this->field,
			// $this->separator,
			$this->source,
			$this->use_acf,
		);

		return $this->format( $values );
	}
}
