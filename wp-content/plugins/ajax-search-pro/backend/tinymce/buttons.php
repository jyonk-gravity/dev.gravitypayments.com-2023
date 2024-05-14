<?php

if ( !defined('ABSPATH') ) {
	die("You can't access this file directly.");
}

/**
 * Adds the Classic Editor shortcode buttons
 */
class ASP_TinyMce_Buttons {
	public function __construct() {
		// check user permissions
		if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
			return;
		}

		// check if WYSIWYG is enabled
		if ( !user_can_richedit() ) {
			return;
		}

		add_action( 'admin_head', array( $this, 'load' ) );
	}

	public function load(): void {
		$post_types     = wd_asp()->o['asp_compatibility']['meta_box_post_types']; // Allow only for selected post types
		$post_types     = explode('|', $post_types);
		$current_screen = get_current_screen();

		if ( is_null($current_screen) || is_wp_error($current_screen) ) {
			return;
		}

		if ( in_array( $current_screen->post_type, $post_types, true ) ) {
			add_action( 'admin_head', array( $this, 'addMceVariables' ), 999 );
			add_filter( 'mce_external_plugins', array( $this, 'addMceScript' ) );
			add_filter( 'mce_buttons', array( $this, 'addMceButton' ) );
		}
	}

	public function addMceVariables(): void {
		$menu_items            = array();
		$menu_result_items     = array();
		$menu_setting_items    = array();
		$menu_two_column_items = array();

		foreach ( wd_asp()->instances->get() as $x => $instance ) {
			$id                      = $instance['id'];
			$menu_items[]            = "{text: 'Search $id (" . preg_replace('/[^\w\d ]/ui', '', esc_attr( $instance['name'] )) . ")',onclick: function() {editor.insertContent('[wpdreams_ajaxsearchpro id=$id]');}}";
			$menu_result_items[]     = "{text: 'Results $id (" . preg_replace('/[^\w\d ]/ui', '', esc_attr( $instance['name'] )) . ")',onclick: function() {editor.insertContent('[wpdreams_ajaxsearchpro_results id=$id element=div]');}}";
			$menu_setting_items[]    = "{text: 'Settings $id (" . preg_replace('/[^\w\d ]/ui', '', esc_attr( $instance['name'] )) . ")',onclick: function() {editor.insertContent('[wpdreams_asp_settings id=$id]');}}";
			$menu_two_column_items[] = "{text: 'Two column layout for $id (" . preg_replace('/[^\w\d ]/ui', '', esc_attr( $instance['name'] )) . ")',onclick: function() {editor.insertContent('[wpdreams_ajaxsearchpro_two_column id=$id]');}}";
		}
		?>

		<?php if ( count($menu_items) >0 ) : ?>
			<?php $menu_items = implode(', ', $menu_items); ?>
			<?php $menu_result_items = implode(', ', $menu_result_items); ?>
			<?php $menu_setting_items = implode(', ', $menu_setting_items); ?>
			<?php $menu_two_column_items = implode(', ', $menu_two_column_items); ?>
			<script type="text/javascript">
				wpdreams_asp_mce_button_menu = "<?php echo $menu_items; ?>";
				wpdreams_asp_res_mce_button_menu = "<?php echo $menu_result_items; ?>";
				wpdreams_asp_sett_mce_button_menu = "<?php echo $menu_setting_items; ?>";
				wpdreams_asp_two_column_mce_button_menu = "<?php echo $menu_two_column_items; ?>";
			</script>
			<?php
		endif;
	}

	public function addMceScript( array $plugin_array ): array {
		$plugin_array['wpdreams_asp_mce_button'] = plugins_url() . '/ajax-search-pro/backend/tinymce/buttons.js';
		return $plugin_array;
	}

	public function addMceButton( array $buttons ): array {
		array_push( $buttons, 'wpdreams_asp_mce_button' );
		return $buttons;
	}
}

new ASP_TinyMce_Buttons();