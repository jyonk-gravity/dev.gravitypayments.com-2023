<?php

namespace WPDRMS\ASP\NavMenu;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * Nav menu view
 */
class View {
	/**
	 * The menu box on the Navigation Menus screen
	 *
	 * @return void
	 */
	public function menuBox(): void {
		?>
		<div id="posttype-aspm" class="posttypediv">
			<?php $this->assets(); ?>
			<div id="tabs-panel-aspm" class="tabs-panel tabs-panel-active">
				<p>
					Click the <strong>Add to Menu</strong> to add Ajax Search Pro to the menu.
				</p>
				<ul id ="aspm-checklist" class="categorychecklist form-no-clear" style="display:none">
					<li>
						<label class="menu-item-title">
							<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="1" checked=checked> Ajax Search Pro
						</label>
						<input type="hidden" class="menu-item-name" name="menu-item[-1][menu-item-name]" value="asp">
						<input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="Ajax Search Pro">
						<input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="">
						<input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="aspm-container">
					</li>
				</ul>
			</div>
			<p class="button-controls">
				<span class="add-to-menu">
						<input type="submit" class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-posttype-aspm">
						<span class="spinner"></span>
					</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Inline scripts and styles to handle the custom menu elements
	 *
	 * This should be printed anywhere on the menu editor screen. Safest location is probably the
	 * in the menuBox() so no additional hook is needed for the wp_footer.
	 * Also in case something inherits or integrates the menu, it will be carried, whereas in the footer
	 * a screen check would be necessary, which may lead to issues.
	 *
	 * @return void
	 */
	private function assets(): void {
		?>
		<style>
			.aspm-menu-item .field-url,
			.aspm-menu-item .description:not(.aspm-description-show) {
				display: none;
			}
		</style>
		<script>
			jQuery(function($){
				let t;
				let o = new MutationObserver(function() {
					clearTimeout(t);
					t = setTimeout(function () {
						addClasses();
					}, 50);
				});
				o.observe(document.querySelector(".menu.ui-sortable"), {subtree: true, childList: true});

				function addClasses() {
					jQuery('.aspm-description-show').closest('.menu-item').addClass('aspm-menu-item');
				}

				addClasses();
			});
		</script>
		<?php
	}

	/**
	 * Additional custom fields to the created Menu element on the nav menu screen
	 *
	 * @param Model   $data
	 * @param integer $item_id
	 * @return void
	 */
	public function customFields( Model $data, int $item_id ): void {
		$value = $data->value();
		?>
		<p class="aspm-description-show description description-wide">
			<label for="aspm-search_id-<?php echo $item_id; //phpcs:ignore ?>" >
				<?php esc_html_e('Search', 'ajax-search-pro'); ?>
				<select
						id="aspm-search_id-<?php echo $item_id; //phpcs:ignore ?>"
						name="aspm-search_id[<?php echo $item_id; //phpcs:ignore ?>]"
				>
					<?php foreach ( wd_asp()->instances->get() as $instance ) : ?>
					<option
							value="<?php echo esc_attr($instance['id']); ?>"
						<?php selected($instance['id'], $value['search_id']); ?>
					>
						<?php echo esc_attr($instance['name']); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>
		<p class="aspm-description-show description description-wide">
			<label for="aspm-menu-item-button-<?php echo $item_id; //phpcs:ignore ?>" >
				<input type="checkbox"
						id="aspm-prevent_events-<?php echo $item_id; //phpcs:ignore ?>"
						name="aspm-prevent_events[<?php echo $item_id; //phpcs:ignore ?>]"
					<?php checked($value['prevent_events']); ?>
				/><?php esc_html_e('Prevent custom script events', 'ajax-search-pro'); ?>
			</label>
		</p>
		<?php
	}
}
