<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;

class LegacyTaxonomyFieldTypes extends TaxonomyFieldTypes implements AdvancedFieldTypeInterface {
	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		parent::__construct($field, $field_args, $result);
		$this->taxonomy = str_replace(array( '_taxonomy_', '__tax_' ), '', $field);
	}
}
