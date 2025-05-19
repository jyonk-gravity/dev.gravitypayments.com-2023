<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;

interface AdvancedFieldTypeInterface {
	/**
	 * @param string               $field
	 * @param array<string, mixed> $field_args
	 * @param stdClass|null        $result
	 * @return string
	 */
	public function __construct( string $field, array $field_args, ?stdClass $result );

	public function process(): string;
}
