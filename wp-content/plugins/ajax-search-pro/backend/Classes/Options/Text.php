<?php

namespace WPDRMS\Backend\Options;

class Text extends AbstractOption {
	protected $default_args = array(
		"small" => false,
		"regex" => '',
		'validationMsg' => ''
	);

	protected static function outputValue( $value ) {
		// No need to decode
		return stripslashes(esc_html($value));
	}

	public function render() {
		if ( !$this->args['small'] ) {
		?>
		<div class='wpdreamsText'>
			<label for="wpdreamstext_<?php echo self::$num; ?>"><?php echo $this->label; ?></label>
			<input isparam=1
				   <?php echo ' data-regex="'.$this->args['regex'].'"' . ' data-validationmsg="'.$this->args['validationMsg'].'"' ?>
				   value="<?php echo self::outputValue($this->value); ?>"
				   type='text'
				   name="<?php echo $this->name; ?>" id="wpdreamstext_<?php echo self::$num; ?>" />
		</div>
		<?php
		} else {
		?>
		<div class='wpdreamsText wpdreamsTextSmall'>
			<label for="wpdreamstext_<?php echo self::$num; ?>"><?php echo $this->label; ?></label>
			<input isparam=1
				   <?php echo ' data-regex="'.$this->args['regex'].'"' . ' data-validationmsg="'.$this->args['validationMsg'].'"' ?>
				   value="<?php echo self::outputValue($this->value); ?>"
				   type='text'
				   class="small"
				   name="<?php echo $this->name; ?>" id="wpdreamstext_<?php echo self::$num; ?>" />
		</div>
		<?php
		}
	}
}