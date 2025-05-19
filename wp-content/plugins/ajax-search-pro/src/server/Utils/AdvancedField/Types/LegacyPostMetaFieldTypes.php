<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;
use WPDRMS\ASP\Utils\Post;
use WPDRMS\ASP\Utils\Str;
use WPDRMS\ASP\Utils\User;

/**
 * Handles special and built in advanced field types related to results fields
 */
class LegacyPostMetaFieldTypes implements AdvancedFieldTypeInterface {
	protected bool $use_acf;
	protected string $field;

	/**
	 * @var array<string, string>
	 */
	protected array $field_args;

	protected ?stdClass $result;

	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		$this->use_acf    = wd_asp()->o['asp_compatibility']['use_acf_getfield'];
		$this->field      = $field;
		$this->result     = $result;
		$this->field_args = $field_args;
	}

	public function process(): string {
		if ( is_null($this->result) ) {
			return '';
		}

		if ( strpos($this->field, '_pods_') === 0 || strpos($this->field, '_pod_') === 0 ) {
			// PODs field
			return Str::anyToString( Post::getPODsValue($this->field, $this->result) );
		} elseif ( strpos($this->field, '__um_') === 0 ) {
			// User Meta Field
			$um_field = str_replace('__um_', '', $this->field);
			$author   = get_post_field( 'post_author', $this->result->id );
			return User::getCFValue($um_field, intval($author), $this->use_acf);
		} else {
			// Probably a custom field?
			return Post::getCFValue($this->field, $this->result, $this->use_acf, $this->field_args);
		}
	}
}
