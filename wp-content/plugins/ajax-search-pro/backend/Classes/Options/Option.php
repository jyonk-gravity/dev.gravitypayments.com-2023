<?php

namespace WPDRMS\Backend\Options;

if (!defined('ABSPATH')) die('-1');

class Option {
	public static function create($optionType, $args) {
		$class = __NAMESPACE__ . "\\" . $optionType;
		if ( !class_exists( $class ) ) {
			$class = __NAMESPACE__ . "\\" . 'YesNo';
		}
		$c = new $class($args);
		return $c->render();
	}

	public static function init() {
		add_action('admin_enqueue_scripts', array( static::class, 'registerAssets' ));

		CustomFieldSearch::registerAjax();
		UserSelect::registerAjax();
		PostSearch::registerAjax();
	}

	public static function getOptions() {
		$all_options = array();

		foreach ( OptionDefaults::getGlobalDefaults() as $option_group_key => $default_options ) {
			$stored = get_site_option($option_group_key, array());
			$options = array();
			foreach ( $default_options as $key => $default_value ) {
				if ( isset($stored[$key]) ) {
					$options[$key] = self::optionValue($default_value, $stored[$key]);
				} else {
					$options[$key] = self::optionValue($default_value);
				}
			}
			$all_options[$option_group_key] = $options;
		}

		foreach ( OptionDefaults::getLocalDefaults() as $option_group_key => $default_options ) {
			$stored = get_option($option_group_key, array());
			$options = array();
			foreach ( $default_options as $key => $default_value ) {
				if ( isset($stored[$key]) ) {
					$options[$key] = self::optionValue($default_value, $stored[$key]);
				} else {
					$options[$key] = self::optionValue($default_value);
				}
			}
			$all_options[$option_group_key] = $options;
		}

		return $all_options;
	}

	public static function optionValue($default_value, $stored_value = null) {
		if ( is_array($default_value) && isset( $default_value['option_type'], $default_value['value'] ) ) {
			if ( $stored_value === null ) {
				return $default_value['value'];
			} else {
				$class = __NAMESPACE__ . "\\" . $default_value['option_type'];
				return $class::value($stored_value, $default_value['value']);
			}
		} else {
			if ( $stored_value === null ) {
				return $default_value;
			} else {
				return $stored_value;
			}
		}
	}

	public static function saveOptions($group_key, $new_options) {
		if ( isset(OptionDefaults::$global_defaults[$group_key]) ) {
			return update_site_option($group_key, $new_options);
		} else if ( isset(OptionDefaults::$local_defaults[$group_key]) ) {
			return update_option($group_key, $new_options);
		} else {
			return false;
		}
	}

	public static function registerAssets() {
		$media_query = ASP_DEBUG == 1 ? asp_gen_rnd_str() : get_site_option("asp_media_query", "defn");

		wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core', false, array('jquery'), false, true);
        wp_enqueue_script('jquery-ui-slider', false, array('jquery-ui-core'), false, true);
        wp_enqueue_script('jquery-ui-tabs', false, array('jquery-ui-core'), false, true);
        wp_enqueue_script('jquery-ui-sortable', false, array('jquery-ui-core'), false, true);
        wp_enqueue_script('jquery-ui-draggable', false, array('jquery-ui-core'), false, true);
        wp_enqueue_script('jquery-ui-datepicker', false, array('jquery-ui-core'), false, true);

		wp_enqueue_script('wpd-options-jquery-select2', ASP_URL_NP . 'backend/Assets/Options/dist/select2.min.js', array(
			'jquery'
		), $media_query, false);
		wp_enqueue_style('wpd-options-jquery-select2', ASP_URL_NP . 'backend/Assets/Options/dist/select2.min.css', false, $media_query);

		/*wp_enqueue_script('wpd-options', ASP_URL_NP . 'backend/Assets/Options/dist/options.min.js',
			array('jquery', 'wpd-options-jquery-select2', 'jquery-ui-datepicker'), $media_query, true);*/
		wp_enqueue_script('wpd-options', ASP_URL_NP . 'backend/Assets/Options/dist/App.js',
			array('jquery', 'wpd-options-jquery-select2', 'jquery-ui-datepicker'), $media_query, true);
		wp_enqueue_style('wpd-options', ASP_URL_NP . 'backend/Assets/Options/dist/options.min.css', array(), $media_query);
	}
}