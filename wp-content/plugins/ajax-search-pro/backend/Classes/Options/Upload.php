<?php

namespace WPDRMS\Backend\Options;

class Upload extends AbstractOption {
	function render() {
		?>
		<div class='wpdreamsUpload' id='wpdreamsUpload<?php echo self::$num; ?>'>
			<label for='wpdreamsUpload_input<?php echo self::$num; ?>'>
				<?php echo $this->label; ?>
			</label>
			<input id="wpdreamsUpload_input<?php echo self::$num; ?>" type="text"
				   class="wdUploadText"
				   size="36" name="<?php echo $this->name; ?>"
				   value="<?php esc_attr_e(self::outputValue($this->value)); ?>"/>
			<input id="wpdreamsUpload_button<?php echo self::$num; ?>"
				   class="wdUploadButton button" type="button"
				   value="<?php esc_attr_e('Upload', 'ajax-search-pro'); ?>"/>
		</div>
		<?php
	}
}