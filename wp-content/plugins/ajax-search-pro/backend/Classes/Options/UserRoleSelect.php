<?php

namespace WPDRMS\Backend\Options;

class UserRoleSelect extends AbstractOption {
	protected
		$roles = array(),
		$selected = array(),
		$value = array();

	function __construct($args) {
		global $wp_roles;
		parent::__construct($args);

		// Check if role exists, if not, then remove it
		$this->value = array_filter($this->value, function($role){
			global $wp_roles;
			return isset($wp_roles->roles[$role]);
		});

		foreach ( $wp_roles->roles as $role => $vv ) {
			if ( !in_array($role, $this->value) ) {
				$this->roles[] = $role;
			}
			if ( in_array($role, $this->value) ) {
				$this->selected[] = $role;
			}
		}
	}

	public static function value( $value, $default_value = null ) {
		return array_filter(explode('|', $value));
	}

	protected static function outputValue( $value ) {
		// No need to decode
		return implode('|', $value);
	}

	function render() {
		global $wp_roles;
	?>
	<div class='wpdreamsUserRoleSelect' data-id="<?php echo self::$num; ?>" id='wpdreamsUserRoleSelect-<?php echo self::$num; ?>'>
		<fieldset>
			<legend><?php echo $this->label; ?></legend>
			<div class="sortablecontainer" id="sortablecontainer<?php echo self::$num; ?>">
				<div class="arrow-all arrow-all-left"></div>
				<div class="arrow-all arrow-all-right"></div>
				<p><?php echo __('Available user roles', 'ajax-search-pro'); ?></p>
				<ul id="sortable<?php echo self::$num; ?>" class="connectedSortable connectedSortable<?php echo self::$num; ?>">
					<?php foreach($this->roles as $role ): ?>
						<li class="ui-state-default" data-role="<?php echo esc_attr($role); ?>">
							<?php echo esc_html($wp_roles->roles[$role]['name']); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="sortablecontainer">
				<p><?php echo __('Drag here the user roles you want to exclude!', 'ajax-search-pro'); ?></p>
				<ul id="sortable_conn<?php echo self::$num; ?>" class="connectedSortable connectedSortable<?php echo self::$num; ?>">
					<?php foreach($this->selected as $role ): ?>
						<li class="ui-state-default" data-role="<?php echo esc_attr($role); ?>">
							<?php echo esc_html($wp_roles->roles[$role]['name']); ?>
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