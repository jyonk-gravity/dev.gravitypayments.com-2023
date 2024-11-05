<?php

namespace WPDRMS\Backend\Options;

/**
 * Alternatives for both wpdreamsImageRadio, wd_imageRadio options
 */
class ImageRadio extends AbstractOption {
	public function render() {
		?>
		<div class='wd_imageRadio'>
			<label class='image_radio'><?php echo $this->label; ?></label>
			<?php
			foreach ($this->args['images'] as $k => $image) {
				$image = trim($image);
				$value = is_string($k) ? $k : $image;
				$selected = !(strpos($value, $this->value) === false);
				echo "
		  		<img data-value = '".$value."' src='" . plugins_url() . $image . "' 
		  			 class='image_radio" . (($selected) ? ' selected' : '') . "'/>";
			}
			?>
			<input isparam="1" type="hidden"
				   class='realvalue'
				   value="<?php echo self::outputValue($this->value); ?>"
				   name="<?php echo $this->name; ?>">
		</div>
		<?php
	}
}