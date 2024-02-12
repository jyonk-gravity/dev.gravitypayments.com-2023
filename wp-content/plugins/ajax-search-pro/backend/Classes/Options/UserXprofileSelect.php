<?php

namespace WPDRMS\Backend\Options;

class UserXprofileSelect extends AbstractOption {
	public static $all_fields = false;
	private $fields;

	function __construct($args) {
		global $wpdb;
		parent::__construct($args);

		if ( self::$all_fields === false ) {
			self::$all_fields = array();
			$table_name = $wpdb->base_prefix . "bp_xprofile_fields";
			if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name ) {
				$all_fields = $wpdb->get_results(
					"SELECT * FROM $table_name LIMIT 400"
				);
				$all_fields = is_wp_error($all_fields) ? array() : $all_fields;
				foreach ($all_fields as $pf) {
					self::$all_fields[$pf->id] = $pf;
				}
			}
		}
		$this->fields = array_filter(self::$all_fields, function ($field) {
			return !in_array($field->id, $this->value);
		});
	}

	public static function value( $value, $default_value = null ) {
		return array_filter(explode('|', $value));
	}

	protected static function outputValue( $value ) {
		// No need to decode
		return implode('|', $value);
	}

	function render() {
		?>
		<div class='wpdreamsBP_XProfileFields' data-id="<?php echo self::$num; ?>" id='wpdreamsBP_XProfileFields-<?php echo self::$num; ?>'>
			<fieldset>
				<legend><?php echo $this->label; ?></legend>
				<div class="sortablecontainer" id="sortablecontainer<?php echo self::$num; ?>">
					<div class="arrow-all arrow-all-left"></div>
					<div class="arrow-all arrow-all-right"></div>
					<p><?php echo __('Available profile fields', 'ajax-search-pro'); ?></p>
					<ul id="sortable<?php echo self::$num; ?>" class="connectedSortable connectedSortable<?php echo self::$num; ?>">
						<?php foreach($this->fields as $field ): ?>
							<li class="ui-state-default" data-bid="<?php echo $field->id; ?>">
								<?php echo $field->name; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="sortablecontainer">
					<p><?php echo __('Drag here the fields you want to search!', 'ajax-search-pro'); ?></p>
					<ul id="sortable_conn<?php echo self::$num; ?>" class="connectedSortable connectedSortable<?php echo self::$num; ?>">
						<?php foreach($this->value as $id ): ?>
							<?php if ( isset(self::$all_fields[$id]) ): ?>
							<li class="ui-state-default" data-bid="<?php echo $id; ?>">
								<?php echo self::$all_fields[$id]->name; ?>
							</li>
							<?php endif; ?>
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