<?php

namespace WPDRMS\Backend\Options;

use WP_User_Query;
use WPDRMS\ASP\Utils\Ajax;

class UserSelect extends AbstractOption {
	protected $default_args = array(
		"show_type" => 0,
		"show_checkboxes" => 0,
		"show_all_users_option" => 1
	);

	public static function registerAjax() {
		if ( !has_action('wp_ajax_wd_search_users') ) {
			add_action('wp_ajax_wd_search_users', array(get_called_class(), 'search'));
		}
	}

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


	public function render() {
		?>
		<div class='wd_userselect' id='wd_userselect-<?php echo self::$num; ?>'>
			<fieldset>
				<div style='margin:15px 30px;text-align: left; line-height: 45px;'>
					<label>
						<?php echo __('Search users:', 'ajax-search-pro'); ?>
						<input type="text" class="wd_user_search" placeholder="<?php echo __('Type here..', 'ajax-search-pro'); ?>"/>
					</label>
					<label<?php echo ($this->args["show_type"] == 1) ? '' :  ' class="hiddend"'; ?>>
						<?php echo __('Operation:', 'ajax-search-pro'); ?>
						<select class="tts_operation">
							<option value="include"<?php echo $this->value['op_type'] == "include" ? ' selected="selected"' : ''; ?>><?php echo __('Include', 'ajax-search-pro'); ?></option>
							<option value="exclude"<?php echo $this->value['op_type'] == "exclude" ? ' selected="selected"' : ''; ?>><?php echo __('Exclude', 'ajax-search-pro'); ?></option>
						</select>
					</label>
				</div>
				<legend><?php echo $this->label; ?></legend>
				<div class="draggablecontainer" id="sortablecontainer<?php echo self::$num; ?>">
					<div class="dragLoader hiddend"></div>
					<p><?php echo __('User Results', 'ajax-search-pro'); ?></p>
					<ul id="sortable<?php echo self::$num; ?>" class="connectedSortable wd_csortable<?php echo self::$num; ?>">
						<?php if ($this->args['show_all_users_option'] == 1): ?>
						<li class="ui-state-default"  data-userid="-1"><?php echo __('All users', 'ajax-search-pro'); ?><a class="deleteIcon"></a></li>
						<?php endif; ?>
						<li class="ui-state-default"  data-userid="0"><?php echo __('Anonymous user (no user)', 'ajax-search-pro'); ?><a class="deleteIcon"></a></li>
						<li class="ui-state-default"  data-userid="-2"><?php echo __('Current logged in user', 'ajax-search-pro'); ?><a class="deleteIcon"></a></li>
						<?php echo __('Use the search to look for users :)', 'ajax-search-pro'); ?>
					</ul>
				</div>
				<div class="sortablecontainer"><p><?php echo __('Drag here the ones you want to', 'ajax-search-pro'); ?> <span style="font-weight: bold;" class="tts_type"><?php echo $this->value['op_type']; ?></span>!</p>
					<ul id="sortable_conn<?php echo self::$num; ?>" class="connectedSortable wd_csortable<?php echo self::$num; ?>">
						<?php $this->printSelectedUsers(); ?>
					</ul>
				</div>

				<input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
				<input isparam=1 type='hidden' value="<?php echo self::outputValue($this->value); ?>" name='<?php echo $this->name; ?>'>
			</fieldset>
		</div>
		<?php
	}

	private function printSelectedUsers() {
		foreach($this->value['users'] as $u) {
			switch ($u) {
				case -1:
					echo '<li class="ui-state-default termlevel-0"  data-userid="-1">' . __('All users', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>';
					break;
				case 0:
					echo '<li class="ui-state-default"  data-userid="0">' . __('Anonymous user (no user)', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>';
					break;
				case -2:
					echo '<li class="ui-state-default"  data-userid="-2">' . __('Current logged in user', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>';
					break;
				default:
					$user = get_user_by("ID", $u);
					if (empty($user) || is_wp_error($user))
						break;
					$checkbox = "";
					if ($this->args['show_checkboxes'] == 1)
						$checkbox = '<input style="float:left;" type="checkbox" value="' . $user->ID . '"
					' . (!in_array($user->ID, $this->value['un_checked']) ? ' checked="checked"' : '') . '/>';
					echo '
					<li class="ui-state-default" data-userid="' . $user->ID . '">' . $user->user_login . ' ('.$user->display_name.')
						' . $checkbox . '
					<a class="deleteIcon"></a></li>';
					break;
			}
		}
	}

	public static function search() {
		$phrase = trim($_POST['wd_phrase']);
		$data = json_decode(base64_decode($_POST['wd_args']), true);
		$user_query = new WP_User_Query( array( 'search' => "*" . $phrase . "*", "number" => 100 ) );

		Ajax::prepareHeaders();
		if ( $data['show_all_users_option'] == 1 )
			echo '<li class="ui-state-default termlevel-0"  data-userid="-1">' . __('All users', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>';
		echo '<li class="ui-state-default"  data-userid="0">' . __('Anonymous user (no user)', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>
			  <li class="ui-state-default"  data-userid="-2">' . __('Current logged in user', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>';

		// User Loop
		$user_results = $user_query->get_results();
		if ( ! empty( $user_results ) ) {
			echo "Or select users:";
			foreach ( $user_results as $user ) {
				$checkbox = "";
				if ($data['show_checkboxes'] == 1)
					$checkbox = '<input style="float:left;" type="checkbox" value="' . $user->ID . '" checked="checked"/>';
				echo '
				<li class="ui-state-default" data-userid="' . $user->ID . '">' . $user->user_login . ' ('.$user->display_name.')
					'.$checkbox.'
				<a class="deleteIcon"></a></li>
			';
			}
		} else {
			echo __('No users found for term:', 'ajax-search-pro') . ' <b>' . $phrase .'</b>';
		}
		die();
	}
}