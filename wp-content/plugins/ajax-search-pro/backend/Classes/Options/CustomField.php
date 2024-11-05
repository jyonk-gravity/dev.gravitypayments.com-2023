<?php

namespace WPDRMS\Backend\Options;

class CustomField extends AbstractOption {
	protected $default_args = array(
		"show_pods" => false
	);

	public static function value( $value, $default_value = null ) {
		if ( gettype($value) === 'string' ) {
			if ( $value != '' ) {
				$value = array_unique(array_filter(explode('|', $value)));
			} else {
				$value = array();
			}
		}

		return self::compatibility( $value );
	}

	protected static function outputValue( $value ) {
		// No need to decode
		return implode('|', $value);
	}

	function render() {
	?>
	<div class='wpdreamsCustomFields' data-id="<?php echo self::$num; ?>" id='wpdreamsCustomFields-<?php echo self::$num; ?>'>
		<fieldset>
			<legend><?php echo $this->label; ?></legend>
			<div class="draggablecontainer" id="draggablecontainer<?php echo self::$num; ?>">
				<div class="arrow-all arrow-all-left"></div>
				<div class="arrow-all arrow-all-right"></div>
				<div style="margin: -3px 0 5px -5px;">
					<?php
					Option::create('CustomFieldSearch', array(
						'name' => 'wdcfs_' . self::$num,
						'label' =>  '',
						'value' => '',
						'args' => array(
							'callback' => 'wd_cf_ajax_callback',
							'show_pods' => $this->args['show_pods'],
							'limit' => 40
						)
					));
					?>
				</div>
				<ul id="sortable<?php echo self::$num; ?>" class="connectedSortable connectedSortable<?php echo self::$num; ?>">
					<?php echo __('Use the search bar above to look for custom fields', 'ajax-search-pro'); ?>
				</ul>
			</div>
			<div class="sortablecontainer">
				<p><?php echo __('Drag here the custom fields you want to use!', 'ajax-search-pro'); ?></p>
				<ul id="sortable_conn<?php echo self::$num; ?>" class="connectedSortable connectedSortable<?php echo self::$num; ?>">
					<?php foreach($this->value as $v ): ?>
						<li class="ui-state-default" cf_name="<?php echo $v; ?>">
							<?php echo str_replace('__pods__', '[PODs] ', $v); ?>
							<a class="deleteIcon"></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<input isparam="1" type="hidden"
				   value="<?php echo self::outputValue($this->value); ?>"
				   name="<?php echo $this->name; ?>">
		</fieldset>
	</div>
	<?php
	}
}