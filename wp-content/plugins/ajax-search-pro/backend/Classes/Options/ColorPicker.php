<?php
namespace WPDRMS\Backend\Options;

class ColorPicker extends AbstractOption {
	protected static function outputValue( $value ) {
		return self::hex2rgba($value);
	}

	function render() {
		?>
		<div class='wpdreamsColorPicker'>
			<label for="wpdreamscolorpicker_<?php echo self::$num; ?>"><?php echo $this->label; ?></label>
			<input isparam=1
				   type='text'
				   class='color'
				   name="<?php echo $this->name; ?>"
				   value="<?php echo self::outputValue($this->value); ?>"
				   id="wpdreamscolorpicker_<?php echo self::$num; ?>">
		</div>
		<?php
	}

	protected static function hex2rgba($color) {
		if (strlen($color)>7) return $color;
		if (strlen($color)<3) return "rgba(0, 0, 0, 1)";
		if ($color[0] == '#')
			$color = substr($color, 1);
		if (strlen($color) == 6)
			list($r, $g, $b) = array($color[0].$color[1],
				$color[2].$color[3],
				$color[4].$color[5]);
		elseif (strlen($color) == 3)
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		else
			return false;
		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		return "rgba(".$r.", ".$g.", ".$b.", 1)";
	}
}