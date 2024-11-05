<?php

namespace WPDRMS\Backend\Options;

use wd_CFSearchCallBack;

class UserMeta extends AbstractOption {
		public static function value( $value, $default_value = null ) {
			if (gettype($value) === 'string' && substr($value, 0, strlen('_decode_')) === '_decode_') {
				$value = substr($value, strlen('_decode_'));
				$value = json_decode(base64_decode($value), true);
			}

			return self::compatibility( $value );
		}

		protected static function outputValue( $value ) {
			// No need to decode
			if (gettype($value) === 'string' && substr($value, 0, strlen('_decode_')) === '_decode_') {
				return $value;
			} else {
				return '_decode_' . base64_encode(json_encode($value));
			}
		}

        function render() {
            ?>
            <div class='wd_UserMeta' id="wd_UserMeta-<?php echo self::$num; ?>">
                <fieldset>
                <legend><?php echo $this->label; ?></legend>
                <div class="draggablecontainer" id="draggablecontainer<?php echo self::$num; ?>">
                <div class="arrow-all-left"></div>
                <div class="arrow-all-right"></div><div style="margin: -3px 0 5px -5px;">
            <?php
			Option::create('CustomFieldSearch', array(
				'name' => 'wdcfs_' . self::$num,
				'label' =>  '',
				'value' => '',
				'args' => array(
					'callback' => 'wd_um_ajax_callback',
					'limit' => 250,
					'usermeta' => 1
				)
			));
            ?>
            </div><ul id="sortable<?php echo self::$num; ?>" class="connectedSortable">
                <?php echo __('Use the search bar above to look for user meta fields', 'ajax-search-pro'); ?> :)
                </ul></div>
                <div class="sortablecontainer">
                    <p><?php echo __('Drag here the user meta fields you want to use!', 'ajax-search-pro'); ?></p>
                    <ul id="sortable_conn<?php echo self::$num; ?>" class="connectedSortable">
					<?php
					foreach ($this->value as $k => $v) {
						echo '<li class="ui-state-default" cf_name="' . $v . '">' . $v . '<a class="deleteIcon"></a></li>';
					}
					?>
                    </ul></div>
                    <input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
                    <input isparam=1 type='hidden' value="<?php echo self::outputValue($this->value); ?>" name="<?php echo $this->name; ?>">
                </fieldset>
              </div>
            <?php
        }
}