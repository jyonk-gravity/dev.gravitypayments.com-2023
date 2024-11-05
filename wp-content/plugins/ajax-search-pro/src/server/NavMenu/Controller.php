<?php

namespace WPDRMS\ASP\NavMenu;

use stdClass;
use WPDRMS\ASP\Patterns\SingletonTrait;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * Nav menu data model
 *
 * @phpstan-import-type MenuData from Model
 * @phpstan-type WP_Menu_Item (stdClass&object{ID:int, title:string, url:string})
 */
class Controller {
	use SingletonTrait;

	/**
	 * @var View
	 */
	private View $view;

	public function __construct() {
		$this->view = new View();
		add_action('admin_init', array( $this, 'addMetaBoxes' ));
		add_action('wp_nav_menu_item_custom_fields', array( $this, 'customFields' ), 10, 2);
		add_action( 'wp_update_nav_menu_item', array( $this, 'saveCustomFields' ), 10, 2 );
		add_action( 'walker_nav_menu_start_el', array( $this, 'handleMenuOutput' ), 10, 2 );
	}

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
			array( $this, 'menuBox' ),
			'nav-menus',
			'side',
			'low'
		);
	}

	public function menuBox(): void {
		$this->view->menuBox();
	}

	/**
	 * @param string       $item_output
	 * @param WP_Menu_Item $menu_item
	 * @return string
	 */
	public function handleMenuOutput( string $item_output, $menu_item ): string {
		if ( $menu_item->title === 'Ajax Search Pro' ) {
			$fields = $this->getCustomFields($menu_item->ID);
			if ( $fields['search_id'] > 0 ) {
				$item_output = do_shortcode(
					"[wd_asp id={$fields['search_id']} prevent_events={$fields['prevent_events']}]"
				);
			}
		}

		return $item_output;
	}

	/**
	 * @param int          $item_id
	 * @param WP_Menu_Item $menu_item
	 * @return void
	 */
	public function customFields( int $item_id, $menu_item ): void {
		if ( $menu_item->title === 'Ajax Search Pro' ) {
			$stored = get_post_meta($item_id, '_ajax_search_pro_menu_data', true);
			$data = new Model(is_array($stored) ? $stored : array());
			$this->view->customFields($data, $item_id);
		}
	}

	/**
	 * @param int $item_id
	 * @return MenuData
	 */
	public function getCustomFields( int $item_id ): array {
		$stored = get_post_meta( $item_id, '_ajax_search_pro_menu_data', true );
		$data   = new Model( is_array($stored) ? $stored : array() );
		return $data->value();
	}

	/**
	 * @param int $menu_id
	 * @param int $menu_item_db_id
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function saveCustomFields( int $menu_id, int $menu_item_db_id ): void {
		if ( isset($_POST['aspm-search_id'], $_POST['aspm-search_id'][ $menu_item_db_id ]) ) {
			$model = new Model(
				array(
					'search_id'      => intval($_POST['aspm-search_id'][ $menu_item_db_id ] ), //phpcs:ignore
					'prevent_events' => intval(isset($_POST['aspm-prevent_events'][ $menu_item_db_id ]) && $_POST['aspm-prevent_events'][ $menu_item_db_id ] === 'on') // phpcs:ignore
				)
			);
			update_post_meta(
				$menu_item_db_id,
				'_ajax_search_pro_menu_data',
				$model->value()
			);
		}
	}
}
