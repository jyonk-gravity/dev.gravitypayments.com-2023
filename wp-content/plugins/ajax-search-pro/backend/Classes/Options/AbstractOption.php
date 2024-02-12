<?php
namespace WPDRMS\Backend\Options;

if (!defined('ABSPATH')) die('-1');

abstract class AbstractOption {
	protected $name, $label, $value, $args, $default_args = array();
	protected static $num = 0;

	function __construct($args) {
		$args = array_merge(array(
			'name' => 'option_name',
			'label' => 'Option Label',
			'value' => '',
			'args' => array()
		), $args);
		$this->name = $args['name'];
		$this->label = $args['label'];
		$this->value = $args['value'];
		$this->args = wp_parse_args($args['args'], $this->default_args);
		++self::$num;
	}
	/**
	 * HTML Output for the option
	 */
	abstract public function render();

	/**
	 * Get the option value based on the stored value from the database
	 */
	public static function value( $value, $default_value = null ) {
		// Do the conversion here

		return self::compatibility( $value );
	}

	/**
	 * Make the value output friendly for rendering
	 */
	protected static function outputValue($value ) {
		return $value;
	}

	/**
	 * Check and convert the passed value through a backwards compatibility check
	 */
	protected static function compatibility( $value ) {
		return $value;
	}
}