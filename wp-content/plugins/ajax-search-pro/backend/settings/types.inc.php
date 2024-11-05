<?php
/**
 * Includes resources for types
 *
 * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
 * @version 4.0
 * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
 * @copyright Copyright (c) 2012, Ernest Marcinko
 */

/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

// Include the types
require 'class/type.class.php';
require 'class/animations.class.php';
require 'class/blogselect.class.php';
require 'class/border.class.php';
require 'class/boxshadow.class.php';
require 'class/bp_xprofile.class.php';
require 'class/colorpicker.class.php';
require 'class/colorpickerdummy.class.php';
require 'class/wd_cpt_editable.class.php';
require 'class/customposttypes.class.php';
require 'class/customposttypes-all.class.php';
require 'class/customposttypeseditable.class.php';
require 'class/customselect.class.php';
require 'class/customarrayselect.class.php';
require 'class/customfields.class.php';
require 'class/draggable.class.php';
require 'class/dateinterval.class.php';
require 'class/datefilter.class.php';
require 'class/datefilter-post.class.php';
require 'class/font-complete.class.php';
require 'class/four.class.php';
require 'class/gradient.class.php';
require 'class/hidden.class.php';
require 'class/imageradio.class.php';
require 'class/languageselect.class.php';
require 'class/loader-select.class.php';
require 'class/numericunit.class.php';
require 'class/sortable.class.php';
require 'class/wd_sortable_editable.class.php';
require 'class/tagssearch.class.php';
require 'class/tagselect.class.php';
require 'class/wd_taxonomy_term_select.class.php';
require 'class/taxonomy_select.class.php';
require 'class/termmeta-select.class.php';
require 'class/text.class.php';
require 'class/textarea.class.php';
require 'class/textarea-expandable.class.php';
require 'class/wd_textarea_b64.php';
require 'class/textsmall.class.php';
require 'class/themechooser.class.php';
require 'class/upload.class.php';
require 'class/userrole-select.class.php';
require 'class/yesno.class.php';
require 'class/wd_an_inputs.class.php';
require 'class/wd_cpt_search_callback.class.php';
require 'class/wd_cf_search_callback.class.php';
require 'class/wd_cpt_select.class.php';
require 'class/wd_draggable_fields.class.php';
require 'class/wd_image_radio.class.php';
require 'class/wd_mime_select.class.php';
require 'class/wd_ms_license_activator.class.php';
require 'class/wd_post_type_sortable.class.php';
require 'class/wd_taxterm_search_callback.class.php';
require 'class/wd_user_select.class.php';
require 'class/wd_usermeta.class.php';

if ( wd_asp()->manager->getContext() === 'backend' ) {
	add_filter('admin_body_class', 'asp_admin_bclass');
}

if ( !function_exists('asp_admin_bclass') ) {
	function asp_admin_bclass( $classes ) {
		return $classes . ' asp-backend ';
	}
}

add_action('admin_enqueue_scripts', 'admin_stylesV05');
add_action('admin_enqueue_scripts', 'admin_scriptsV05');

if ( !function_exists('admin_scriptsV05') ) {
	function admin_scriptsV05() {
		$media_query = ASP_DEBUG == 1 ? asp_gen_rnd_str() : get_site_option('asp_media_query', 'defn');

		// Remove all nag notices from the back-end
		remove_all_actions( 'admin_notices');

		// ------------ Dequeue some scripts causing issues on the back-end --------------
		wp_dequeue_script( 'otw-admin-colorpicker' );
		wp_dequeue_script( 'otw-admin-select2' );
		wp_dequeue_script( 'otw-admin-otwpreview' );
		wp_dequeue_script( 'otw-admin-fonts');
		wp_dequeue_script( 'otw-admin-functions');
		wp_dequeue_script( 'otw-admin-variables');

		wp_enqueue_media(); // For image uploader.
		wp_enqueue_script('thickbox', false, array( 'jquery' ));

		// Helper script
		wp_register_script(
			'wd-helpers',
			ASP_URL_NP . 'backend/settings/assets/wd_core/js/helpers.js',
			array(
				'jquery',
			),
			$media_query,
			true
		);
		wp_enqueue_script('wd-helpers');

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core', false, array( 'jquery' ), false, true);
		wp_enqueue_script('jquery-ui-slider', false, array( 'jquery-ui-core' ), false, true);
		wp_enqueue_script('jquery-ui-tabs', false, array( 'jquery-ui-core' ), false, true);
		wp_enqueue_script('jquery-ui-sortable', false, array( 'jquery-ui-core' ), false, true);
		wp_enqueue_script('jquery-ui-draggable', false, array( 'jquery-ui-core' ), false, true);
		wp_enqueue_script('jquery-ui-resizable', false, array( 'jquery-ui-core' ), false, true);
		wp_enqueue_script('jquery-ui-datepicker', false, array( 'jquery-ui-core' ), false, true);

		wp_register_script('wd-conditionals', ASP_URL_NP . 'backend/settings/assets/wd_core/js/jquery.conditionals.js', array( 'jquery' ), $media_query, true);
		wp_enqueue_script('wd-conditionals');

		wp_enqueue_script(
			'asp-backend-jquery-select2',
			ASP_URL_NP . 'backend/settings/assets/select2/js/select2.min.js',
			array(
				'jquery',
			),
			$media_query,
			true
		);

		wp_register_script(
			'wpdreams-types',
			ASP_URL_NP . 'backend/settings/assets/types.js',
			array( 'jquery', 'jquery-ui-sortable', 'farbtastic', 'jquery-ui-datepicker', 'wd-conditionals' ),
			$media_query,
			true
		);
		wp_enqueue_script('wpdreams-types');

		wp_register_script(
			'wpdreams-tabs',
			ASP_URL_NP . 'backend/settings/assets/tabs.js',
			array(
				'jquery',
			),
			$media_query,
			true
		);
		wp_enqueue_script('wpdreams-tabs');

		wp_register_script(
			'wpd-textarea-autosize',
			ASP_URL_NP . 'backend/settings/assets/textarea-autosize/jquery.textarea-autosize.js',
			array(
				'jquery',
			),
			$media_query,
			true
		);
		wp_enqueue_script('wpd-textarea-autosize');

		wp_register_script(
			'wpdreams-spectrum',
			ASP_URL_NP . 'backend/settings/assets/js/spectrum/spectrum.js',
			array(
				'jquery',
			),
			$media_query,
			true
		);
		wp_enqueue_script('wpdreams-spectrum');

		wp_register_script('wpdreams-fonts-jsapi', '//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js', array( 'jquery' ), $media_query, true);
		wp_enqueue_script('wpdreams-fonts-jsapi');

		//wp_register_script('wpd-modal', ASP_URL_NP . 'backend/settings/assets/wpd-modal/wpd-modal.js', array( 'jquery' ), $media_query, true);
		//wp_enqueue_script('wpd-modal');

		$metadata = require_once ASP_PATH . 'build/js/modal.asset.php';
		wp_enqueue_script(
			'wpd-modal',
			ASP_URL_NP . 'build/js/modal.js',
			$metadata['dependencies'],
			$metadata['version'],
			array(
				'in_footer' => true,
			)
		);

		wp_register_script(
			'wpdreams-fonts',
			ASP_URL_NP . 'backend/settings/assets/fonts.js',
			array(
				'jquery',
				'media-upload',
				'thickbox',
			),
			$media_query,
			true
		);
		wp_enqueue_script('wpdreams-fonts');
	}
}

if ( !function_exists('admin_stylesV05') ) {
	function admin_stylesV05() {
		$media_query = ASP_DEBUG == 1 ? asp_gen_rnd_str() : get_site_option('asp_media_query', 'defn');

		$metadata = require_once ASP_PATH . 'build/css/admin-shared.asset.php';
		wp_enqueue_style(
			'wpd-admin-shared',
			ASP_URL_NP . 'build/css/admin-shared.css',
			$metadata['dependencies'],
			$metadata['version'],
		);

		wp_register_style('asp-backend-jquery-select2', ASP_URL_NP . 'backend/settings/assets/select2/css/select2.min.css', false, $media_query);
		wp_enqueue_style('asp-backend-jquery-select2');
		wp_register_style('wpdreams-style', ASP_URL_NP . 'backend/settings/assets/style.css', array( 'wpdreams-tabs' ), $media_query);
		wp_enqueue_style('wpdreams-style');
		wp_register_style('wpdreams-style-hc', ASP_URL_NP . 'backend/settings/assets/style-hc.css', array( 'wpdreams-tabs' ), $media_query);
		wp_enqueue_style('wpdreams-style-hc');
		wp_enqueue_style('thickbox');
		wp_register_style('wpdreams-jqueryui', 'https://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css');
		wp_enqueue_style('wpdreams-jqueryui');
		wp_register_style('wpdreams-tabs', ASP_URL_NP . 'backend/settings/assets/tabs.css');
		wp_enqueue_style('wpdreams-tabs');
		wp_register_style('wpdreams-accordion', ASP_URL_NP . 'backend/settings/assets/accordion.css');
		wp_enqueue_style('wpdreams-accordion');
		wp_register_style('wpdreams-spectrum', ASP_URL_NP . 'backend/settings/assets/js/spectrum/spectrum.css');
		wp_enqueue_style('wpdreams-spectrum');
//		wp_register_style('wpd-modal', ASP_URL_NP . 'backend/settings/assets/wpd-modal/wpd-modal.css');
//		wp_enqueue_style('wpd-modal');

		$metadata = require_once ASP_PATH . 'build/css/modal.asset.php';
		wp_enqueue_style(
			'wpd-modal',
			ASP_URL_NP . 'build/css/modal.css',
			$metadata['dependencies'],
			$metadata['version'],
		);

		wp_enqueue_style('wpdreams_animations', ASP_URL_NP . 'css/animations.css', array(), $media_query);
	}
}

/* Extra Functions */
if ( !function_exists('wd_isEmpty') ) {
	function wd_isEmpty( $v ) {
		if ( trim($v) != '' ) {
			return false;
		} else {
			return true;
		}
	}
}

if ( !function_exists('wd_in_array_r') ) {
	function wd_in_array_r( $needle, $haystack, $strict = false ) {
		foreach ( $haystack as $item ) {
			if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array($item) && wd_in_array_r($needle, $item, $strict) ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( !function_exists('wpdreams_get_blog_list') ) {
	function wpdreams_get_blog_list( $start = 0, $num = 10, $deprecated = '' ) {

		global $wpdb;
		if ( !isset($wpdb->blogs) ) {
			return array();
		}
		$blogs = $wpdb->get_results($wpdb->prepare("SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = %d AND public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0' ORDER BY registered DESC", $wpdb->siteid), ARRAY_A);

		foreach ( (array) $blogs as $details ) {
			$blog_list[ $details['blog_id'] ]              = $details;
			$blog_list[ $details['blog_id'] ]['postcount'] = $wpdb->get_var('SELECT COUNT(ID) FROM ' . $wpdb->get_blog_prefix($details['blog_id']) . "posts WHERE post_status='publish' AND post_type='post'");
		}
		unset($blogs);
		$blogs = $blog_list;

		if ( false == is_array($blogs) ) {
			return array();
		}

		if ( $num == 'all' ) {
			return array_slice($blogs, $start, count($blogs));
		} else {
			return array_slice($blogs, $start, $num);
		}
	}
}
