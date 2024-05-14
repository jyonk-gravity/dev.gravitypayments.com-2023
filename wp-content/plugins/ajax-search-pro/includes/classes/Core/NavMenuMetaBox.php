<?php
namespace WPDRMS\ASP\Core;

use WPDRMS\ASP\Patterns\SingletonTrait;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * Allows Ajax Search Pro as a menu element
 */
class NavMenuMetaBox {
	use SingletonTrait;

	/**
	 * Default menu custom metadata
	 *
	 * @var array<string, mixed>
	 */
	public static array $defaults = array(
		'prevent_events' => false,
	);

	/**
	 * Registers the meta boxes
	 *
	 * @hook admin_init
	 * @return void
	 */
	public function addMetaBoxes(): void {
		add_meta_box(
			'asp_nav_menu_link',
			__('Ajax Search Pro', 'ajax-search-pro'),
			array( $this, 'navMenuLink' ),
			'nav-menus',
			'side',
			'low'
		);
	}

	public function navMenuLink(): void {
		?>
		<div id="posttype-wl-login" class="posttypediv">
			<div id="tabs-panel-wishlist-login" class="tabs-panel tabs-panel-active">
				<ul id ="wishlist-login-checklist" class="categorychecklist form-no-clear">
					<li>
						<label class="menu-item-title">
							<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="1"> Search 1
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="Search 1">
						<input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="<?php bloginfo('wpurl'); ?>/wp-login.php">
						<input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="wl-login-pop">
					</li>
					<li>
						<label class="menu-item-title">
							<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="2"> Search 2
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="Search 2">
						<input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="<?php bloginfo('wpurl'); ?>/wp-login.php">
						<input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="aspm_container">
					</li>
				</ul>
			</div>
			<p class="button-controls">
					<span class="list-controls">
						<a href="/wordpress/wp-admin/nav-menus.php?page-tab=all&amp;selectall=1#posttype-page" class="select-all">Select All</a>
					</span>
				<span class="add-to-menu">
						<input type="submit" class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-posttype-wl-login">
						<span class="spinner"></span>
					</span>
			</p>
		</div>
		<?php
	}

	public function printNavMenuCustomFields( $item_id ): void {
		$data = get_post_meta($item_id, '_ajax_search_pro_menu_data', true);
		$data = wp_parse_args($data, self::$defaults);
		?>
		<p class="aspm-show-as-button description description-wide">
			<label for="aspm-menu-item-button-<?php echo esc_attr($item_id); ?>" >
				<input type="checkbox"
						id="aspm-prevent_events-<?php echo esc_attr($item_id); ?>"
						name="aspm-prevent_events[<?php echo esc_attr($item_id); ?>]"
					<?php checked($data['prevent_events']); ?>
				/><?php esc_html_e('Prevent custom script events', 'ajax-search-pro'); ?>
			</label>
		</p>
		<?php
	}

	public function updateNavMenuCustomFields( $menu_id, $menu_item_db_id ) {
		$prevent_events = isset($_POST['aspm-prevent_events'][ $menu_item_db_id ]) && $_POST['aspm-prevent_events'][ $menu_item_db_id ] === 'on'; // phpcs:ignore
		update_post_meta(
			$menu_item_db_id,
			'_ajax_search_pro_menu_data',
			array(
				'prevent_events' => $prevent_events,
			)
		);
	}
}