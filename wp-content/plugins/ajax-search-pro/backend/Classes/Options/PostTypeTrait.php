<?php
namespace WPDRMS\Backend\Options;

trait PostTypeTrait {
	private static $NON_DISPLAYABLE_POST_TYPES = array(
		"revision", "nav_menu_item", "attachment", 'peepso-post', 'peepso-comment', "acf",
		"shop_order", "shop_order_refund", "elementor_library", "elementor_font", "elementor_icons",
		"oembed_cache", "user_request", "wp_block", "shop_coupon", "avada_page_options",
		"_pods_template", "_pods_pod", "_pods_field", "bp-email",
		"lbmn_archive", "lbmn_footer", "mc4wp-form",
		"elementor-front", "elementor-icon", "tablepress_table",
		"fusion_template", "fusion_element", "wc_product_tab", "customize_changeset",
		"wpcf7_contact_form", "dslc_templates", "acf-field", "acf-group", "acf-groups", "acf-field-group", "custom_css"
	);
}