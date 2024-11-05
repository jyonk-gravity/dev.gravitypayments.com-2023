<?php

namespace WPDRMS\Backend\Options;

class CustomSelect extends AbstractOption {
	private static $iconMsg;
	protected $default_args = array(
		'selects' => array(
			array(
				'label' => 'Label',
				'value'  => 'value',
				'icon'   => 'phone'
			)
		),
		'icon'	  => 'none'
	);

	function __construct($args) {
		parent::__construct($args);
		if ( !isset(static::$iconMsg) ) {
			static::$iconMsg = array(
				'phone' => __('Phone devices, on 0px to 640px widths', 'ajax-search-pro'),
				'tablet' => __('Tablet devices, on 641px to 1024px widths', 'ajax-search-pro'),
				'desktop' => __('Desktop devices, 1025px width  and higher', 'ajax-search-pro')
			);
		}
	}

	public function render() {
		?>
		<div class='wpdreamsCustomSelect'>
			<label for="wpdreamscustomselect_<?php echo self::$num; ?>"><?php echo $this->label; ?></label>
			<?php if ( $this->args['icon'] != 'none' ): ?>
			<span
				title="<?php echo $this->iconMsg[$this->args['icon']] ?? ''; ?>"
				class="wpd-txt-small-icon wpd-txt-small-icon-<?php echo $this->args['icon'] ?>">
			</span>
			<?php endif; ?>
			<select isparam=1 class='wpdreamscustomselect' id='wpdreamscustomselect_<?php echo self::$num; ?>' name="<?php echo $this->name; ?>">
				<?php foreach($this->args['selects'] as $sel): ?>
					<?php
					$disabled =  is_array($sel) && isset($sel['disabled']) ? ' disabled' : '';
					$label = is_array($sel) ? $sel['label'] : $sel;
					$value = is_array($sel) ? $sel['value'] : $sel;
					$selected = $value == $this->value ? " selected='selected'" : '';
					?>
					<option value="<?php echo $value; ?>"<?php echo $selected . $disabled ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}
}