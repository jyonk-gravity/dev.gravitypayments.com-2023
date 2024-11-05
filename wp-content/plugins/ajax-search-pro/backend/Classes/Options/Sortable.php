<?php

namespace WPDRMS\Backend\Options;

class Sortable extends AbstractOption {

	public static function value( $value, $default_value = null ) {
		$value = array_filter(explode('|', $value));

		if ( $default_value != null ) {
			$missing_from_value = array_diff($default_value, $value);
			$not_needed = array_diff($value, $default_value);
			$value = array_diff($value, $not_needed);
			$value = array_merge($value, $missing_from_value);
		}
		return $value;
	}

	protected static function outputValue( $value ) {
		return implode('|', $value);
	}

	function render() {
		?>
		<div class='wpdreamsSortable' data-id="<?php echo self::$num; ?>" id='wpdreamsSortable-<?php echo self::$num; ?>'>
			<div class="sortablecontainer" style="float:right;">
				<p><?php echo $this->label; ?></p>
				<ul id="sortable<?php echo self::$num; ?>" class="connectedSortable">
					<?php foreach($this->value as $value ): ?>
						<li class="ui-state-default" data-value="<?php echo esc_attr($value); ?>">
							<?php echo esc_html($value); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<input isparam=1 type='hidden' value="<?php echo self::outputValue($this->value); ?>" name='<?php echo $this->name; ?>'>
			<div class="clear"></div>
		</div>
		<?php
	}
}