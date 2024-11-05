<?php

namespace WPDRMS\Backend\Options;

class Text extends AbstractOption {
	protected $default_args = array(
		"small" => false,
		/**
		 * Always double escape!!
		 * Positive integers: ^\\d*$
		 * Any integer: ^-?\\d*\\.?\\d+$
		 */
		"regex" => '',
		"allow_empty" => 0,
		"default" => '', // Reverts when loses focus
		'validation_msg' => ''
	);

	protected static function outputValue( $value ) {
		// No need to decode
		return stripslashes( esc_html($value)) ;
	}

	public function render() {
		?>
		<div class='wpdreamsText<?php echo $this->args['small'] ? " wpdreamsTextSmall":""; ?>'>
			<label for="wpdreamstext_<?php echo self::$num; ?>"><?php echo $this->label; ?></label>
			<input
				isparam=1
				<?php echo ' data-regex="'.esc_attr($this->args['regex']).'"'; ?>
				<?php echo ' data-validation_msg="'.esc_attr($this->args['validation_msg']).'"'; ?>
				<?php echo ' data-allow_empty="'.esc_attr($this->args['allow_empty']).'"'; ?>
				<?php echo ' data-default="'.esc_attr($this->args['default']).'"'; ?>
				value="<?php echo self::outputValue($this->value); ?>"
				type='text'
				name="<?php echo $this->name; ?>" id="wpdreamstext_<?php echo self::$num; ?>" />
		</div>
		<?php
	}
}