<?php
/* Prevent direct access */

defined('ABSPATH') or die("You can't access this file directly.");

/**
 * A better method to store the init data.
 *
 * The JSON data is stored inside this invisible element, the it is parsed
 * and passed as an argument to the initialization method.
 */

$options_array = array(
	'animations'          => array(
		'pc'  => array(
			'settings' => array(
				'anim' => $style['sett_box_animation'],
				'dur'  => intval($style['sett_box_animation_duration']),
			),
			'results'  => array(
				'anim' => $style['res_box_animation'],
				'dur'  => intval($style['res_box_animation_duration']),
			),
			'items'    => $style['res_items_animation'],
		),
		'mob' => array(
			'settings' => array(
				'anim' => $style['sett_box_animation_m'],
				'dur'  => $style['sett_box_animation_duration_m'],
			),
			'results'  => array(
				'anim' => $style['res_box_animation_m'],
				'dur'  => $style['res_box_animation_duration_m'],
			),
			'items'    => $style['res_items_animation_m'],
		),
	),
	'autocomplete'        => array(
		'enabled'           => $style['autocomplete'] == 1 || $style['autocomplete'] == 2 ? 1 : 0,
		'trigger_charcount' => intval($style['autoc_trigger_charcount']),
		'googleOnly'        => $style['autocomplete_source'] == 'google' ? 1 : 0,
		'lang'              => $style['autocomplete_google_lang'],
		'mobile'            => $style['autocomplete'] == 1 || $style['autocomplete'] == 3 ? 1 : 0,
	),
	'autop'               => array(
		'state'  => $style['auto_populate'],
		'phrase' => stripslashes($style['auto_populate_phrase']),
		'count'  => intval($style['auto_populate_count']),
	),
	'charcount'           => intval($style['charcount']),
	'closeOnDocClick'     => intval($style['close_on_document_click']),
	'compact'             => array(
		'enabled'          => intval($style['box_compact_layout']),
		'focus'            => intval($style['box_compact_layout_focus_on_open']),
		'width'            => $style['box_compact_width'],
		'width_tablet'     => $style['box_compact_width_tablet'],
		'width_phone'      => $style['box_compact_width_phone'],
		'closeOnMagnifier' => intval($style['box_compact_close_on_magn']),
		'closeOnDocument'  => intval($style['box_compact_close_on_document']),
		'position'         => $style['box_compact_position'],
		'overlay'          => intval($style['box_compact_overlay']),
	),
	'cptArchive'          => array(
		'useAjax'  => ( \WPDRMS\ASP\Utils\Archive::isPostTypeArchive() && $style['cpt_archive_live_search'] ) ? 1 : 0,
		'selector' => $style['cpt_archive_live_selector'],
		'url'      => \WPDRMS\ASP\Utils\Archive::getCurrentArchiveURL(),
	),
	'detectVisibility'    => intval($style['visual_detect_visbility']),
	'divi'                => array(
		'bodycommerce' => defined('DE_DB_WOO_VERSION') ? 1 : 0,
	),
	'focusOnPageload'     => intval($style['focus_on_pageload']),
	'fss_layout'          => $style['fss_column_layout'],
	'highlight'           => intval($style['highlight']),
	'highlightWholewords' => intval($style['highlightwholewords']),
	'homeurl'             => is_admin()
		? home_url('/')
		: ( function_exists('pll_home_url')
			? PLL()->links->get_home_url('', true)
			: home_url('/') ),
	'is_results_page'     => isset($_GET['s']) ? 1 : 0,
	'isotopic'            => array(
		'itemWidth'        => is_numeric($style['i_item_width']) ? $style['i_item_width'] . 'px' : $style['i_item_width'],
		'itemWidthTablet'  => is_numeric($style['i_item_width_tablet']) ? $style['i_item_width_tablet'] . 'px' : $style['i_item_width_tablet'],
		'itemWidthPhone'   => is_numeric($style['i_item_width_phone']) ? $style['i_item_width_phone'] . 'px' : $style['i_item_width_phone'],
		'itemHeight'       => is_numeric($style['i_item_height']) ? $style['i_item_height'] . 'px' : $style['i_item_height'],
		'itemHeightTablet' => is_numeric($style['i_item_height_tablet']) ? $style['i_item_height_tablet'] . 'px' : $style['i_item_height_tablet'],
		'itemHeightPhone'  => is_numeric($style['i_item_height_phone']) ? $style['i_item_height_phone'] . 'px' : $style['i_item_height_phone'],
		'pagination'       => intval($style['i_pagination']),
		'rows'             => intval($style['i_rows']),
		'gutter'           => intval($style['i_item_margin']),
		'showOverlay'      => intval($style['i_overlay']),
		'blurOverlay'      => intval($style['i_overlay_blur']),
		'hideContent'      => intval($style['i_hide_content']),
	),
	'itemscount'          => intval($style['v_res_show_scrollbar'] == 1 ? $style['itemscount'] : '0'),
	'loaderLocation'      => $style['loader_display_location'],
	'mobile'              => array(
		'trigger_on_type'        => intval($style['mob_trigger_on_type']),
		'click_action'           => $style['mob_click_action'] == 'same' ? $style['click_action'] : $style['mob_click_action'],
		'return_action'          => apply_filters('asp_show_more_url', $style['mob_return_action'] == 'same' ? $style['return_action'] : $style['mob_return_action'], $real_id),
		'click_action_location'  => $style['mob_click_action'] == 'same' ? $style['click_action_location'] : $style['mob_click_action_location'],
		'return_action_location' => $style['mob_return_action'] == 'same' ? $style['return_action_location'] : $style['mob_return_action_location'],
		'redirect_url'           => $style['mob_click_action'] == 'custom_url' || $style['mob_return_action'] == 'custom_url' ? $style['mob_redirect_url'] : $style['redirect_url'],
		'elementor_url'          => $style['mob_click_action'] == 'same' ? $style['redirect_elementor'] : $style['mob_redirect_elementor'],
		'menu_selector'          => $style['mob_auto_focus_menu_selector'],
		'hide_keyboard'          => intval($style['mob_hide_keyboard']),
		'force_res_hover'        => intval($style['mob_force_res_hover']),
		'force_sett_hover'       => intval($style['mob_force_sett_hover']),
		'force_sett_state'       => $style['mob_force_sett_state'],
	),
	'override_method'     => $style['override_method'],
	'overridewpdefault'   => intval($style['override_default_results']),
	'prescontainerheight' => $style['prescontainerheight'],
	'preventBodyScroll'   => intval(wd_asp()->o['asp_compatibility']['js_prevent_body_scroll']),
	'preventEvents'       => intval($prevent_events),
	'rb'                  => array(
		'action' => $style['fe_rb_action'],
	),
	'resPage'             => array(
		'useAjax'           => is_search() && $style['res_live_search'] ? 1 : 0,
		'selector'          => $style['res_live_selector'],
		'trigger_type'      => intval($style['res_live_trigger_type']),
		'trigger_facet'     => intval($style['res_live_trigger_facet']),
		'trigger_magnifier' => intval($style['res_live_trigger_click']),
		'trigger_return'    => intval($style['res_live_trigger_return']),
	),
	'results'             => array(
		'width'        => $style['results_width'],
		'width_tablet' => $style['results_width_tablet'],
		'width_phone'  => $style['results_width_phone'],
	),
	'resultsSnapTo'       => $style['results_snap_to'],
	'resultsposition'     => $style['resultsposition'],
	'resultstype'         => $style['resultstype'],
	'sb'                  => array(
		'redirect_action'   => $style['fe_sb_action'],
		'redirect_location' => $style['fe_sb_action_location'],
		'redirect_url'      => $style['fe_sb_redirect_url'],
		'elementor_url'     => $style['fe_sb_redirect_elementor'],
	),
	'scrollBar'           => array(
		'horizontal' => array(
			'enabled' => intval($style['h_res_show_scrollbar']),
		),
	),
	'scrollToResults'     => array(
		'enabled' => intval($style['scroll_to_results']),
		'offset'  => intval($style['scroll_to_results_offset']),
	),
	'select2'             => array(
		'nores' => esc_html(asp_icl_t('Searchable select filter placeholder' . " ($real_id)", $style['jquery_select2_nores'])),
	),
	'settings'            => array(
		'unselectChildren' => intval($style['frontend_terms_parent_unselect_children']),
		'hideChildren'     => intval($style['frontend_terms_hide_children']),
	),
	'settingsHideOnRes'   => intval($style['fss_hide_on_results']),
	'settingsimagepos'    => $style['settingsimagepos'],
	'settingsVisible'     => intval($style['frontend_search_settings_visible']),
	'show_more'           => array(
		'enabled'       => intval($style['showmoreresults']),
		'url'           => apply_filters('asp_show_more_url', $style['more_redirect_url'], $real_id),
		'elementor_url' => $style['more_redirect_elementor'],
		'action'        => $style['more_results_action'],
		'location'      => $style['more_redirect_location'],
		'infinite'      => $style['more_results_infinite'] && $style['more_results_action'] == 'ajax' ? 1 : 0,
	),
	'singleHighlight'     => intval($style['single_highlight']),
	'statistics'          => get_option('asp_stat', 0) ? 0 : 1,
	'taxArchive'          => array(
		'useAjax'  => ( \WPDRMS\ASP\Utils\Archive::isTaxonomyArchive() && $style['taxonomy_archive_live_search'] ) ? 1 : 0,
		'selector' => $style['taxonomy_archive_live_selector'],
		'url'      => \WPDRMS\ASP\Utils\Archive::getCurrentArchiveURL(),
	),
	'trigger'             => array(
		'delay'              => $style['trigger_delay'],
		'autocomplete_delay' => $style['autocomplete_trigger_delay'],
		'update_href'        => intval($style['trigger_update_href']),
		'facet'              => intval($style['trigger_on_facet']),
		'type'               => $style['triggerontype'] == 1 ? 1 : 0,
		'click'              => $style['click_action'],
		'click_location'     => $style['click_action_location'],
		'return'             => $style['return_action'],
		'return_location'    => $style['return_action_location'],
		'redirect_url'       => apply_filters('asp_redirect_url', $style['redirect_url'], $real_id),
		'elementor_url'      => $style['redirect_elementor'],
	),
	'wooShop'             => array(
		'useAjax'  => ( \WPDRMS\ASP\Utils\WooCommerce::isShop() && $style['woo_shop_live_search'] ) ? 1 : 0,
		'selector' => $style['woo_shop_live_selector'],
		'url'      => ( \WPDRMS\ASP\Utils\WooCommerce::isShop() && $style['woo_shop_live_search'] ) ? get_permalink(wc_get_page_id('shop')) : '',
	),
);

$_asp_script_out = json_encode($options_array);
wd_asp()->instances->add_script_data($real_id, $_asp_script_out);
?>
<div class="asp_init_data"
	style="display:none !important;"
	id="asp_init_id_<?php echo $id; ?>"
	data-asp-id="<?php echo $real_id; ?>"
	data-asp-instance="<?php echo self::$perInstanceCount[ $real_id ]; ?>"
	data-aspdata="<?php echo base64_encode($_asp_script_out); ?>"></div>
