<?php
namespace WPDRMS\Backend\Options;

if (!defined('ABSPATH')) die('-1');

class YesNo extends AbstractOption {
	public static function value( $value, $default_value = null  ): int {
		return $value == 1 ? 1 : 0;
	}
	/**
	 * HTML Output for the option
	 */
	public function render() {
		?>
		<div class="wpdreamsYesNo<?php echo $this->value == 1 ? ' active' : ''; ?>">
			<label for="wpdreamstext_<?php echo self::$num; ?>"><?php echo $this->label; ?></label>
			<input isparam="1" type="hidden"
				   value="<?php echo self::outputValue($this->value); ?>"
				   name="<?php echo $this->name; ?>">
			<div class="wpdreamsYesNoInner"></div>
		</div>
		<?php
	}
}