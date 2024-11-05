<?php

namespace WPDRMS\Backend\Options;

class TextArea extends AbstractOption {
	protected $default_args = array(
		"wide" => false
	);

	protected static function outputValue( $value ) {
		// No need to decode
		return stripslashes(esc_html($value));
	}

	public function render() {
		$style = $this->args['wide'] ? 'style="min-width:85%;"' : '';
		?>
		<label class="wd_textarea_expandable" for="wd_textareae_<?php echo self::$num; ?>"><?php echo $this->label; ?></label>
		<textarea rows='1' data-min-rows='2' id="wd_textareae_<?php echo self::$num; ?>"
				  class='wd_textarea_expandable'
				  name="<?php echo $this->name; ?>"
				  <?php echo $style; ?>
				  id="wd_textareae_<?php echo self::$num; ?>"><?php echo self::outputValue($this->value); ?></textarea>
		<?php
	}
}