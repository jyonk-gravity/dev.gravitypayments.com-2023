<?php

namespace WPDRMS\Backend\Options;

use WPDRMS\ASP\Utils\Str;

class MimeTypeSelect extends AbstractOption {
	protected static function outputValue( $value ) {
		// No need to decode
		return stripslashes(esc_html($value));
	}

	function render() {
		?>
		<div class="wd_MimeTypeSelect">
			<div class="file_mime_types_input hiddend">
				<label class='wd_textarea_expandable'
					   for='wd_textareae_<?php echo self::$num; ?>'><?php echo __($this->label, 'ajax-search-pro'); ?>
				<textarea rows='1' data-min-rows='1'
						  class='wd_textarea_expandable'
						  id='wd_textareae_<?php echo self::$num; ?>'
						  name='<?php echo $this->name; ?>'><?php echo self::outputValue($this->value); ?></textarea>
				</label>
				<span class="mime_input_hide"><?php echo __('>> Simplified view <<', 'ajax-search-pro'); ?></span>
			</div>
			<div class="file_mime_types_list">
				<label>
					<?php echo __($this->label, 'ajax-search-pro'); ?>
					<select multiple attr="multi_attachment_mime_types_<?php echo self::$num; ?>"
							id="multi_attachment_mime_types_<?php echo self::$num; ?>">
						<option value="pdf">PDF</option>
						<option value="text">Text</option>
						<option value="richtext">Rich Text (rtf etc..)</option>
						<option value="mso_word">Office Word</option>
						<option value="mso_excel">Office Excel</option>
						<option value="mso_powerpoint">Office PowerPoint</option>
						<option value="image">Image</option>
						<option value="video">Video</option>
						<option value="audio">Audio</option>
					</select>
				</label>
				<span class="mime_list_hide"><?php echo __('>> Enter manually <<', 'ajax-search-pro'); ?></span>
			</div>
		</div>
		<?php
	}

	protected static function compatibility( $value ) {
		// Older versions had base64 encoded inputs
		if ( Str::isBase64Encoded($value) ) {
			$value = base64_decode($value);
		}
		return $value;
	}
}