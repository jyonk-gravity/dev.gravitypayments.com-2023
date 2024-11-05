<?php
/*
 * Plugin Name: Multiple Columns for Gravity Forms
 * Plugin URI: https://wordpress.org/plugins/gf-form-multicolumn/
 * Description: Introduces new form elements into Gravity Forms which allow rows to be split into multiple columns.
 * Author: WebHolism
 * Author URI: http://www.webholism.com
 * Version: 4.0.6
 * Text Domain: gf-form-multicolumn
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'LOG_PATH', __DIR__ . '/log/' );
define( 'LOG_LEVEL', 'DEBUG' );

require __DIR__ . '/vendor/autoload.php';

use WH\GF\Multicolumn;
use WH\GF\Multicolumn\Classes\WH_GF_Multicolumn_Activator;
use WH\GF\Multicolumn\Classes\WH_GF_Multicolumn_Uninstaller;

add_action( 'gform_loaded', [
	'WH_GF_Multicolumn_Bootstrap',
	'load',
], 5 );

/**
 * Class GFMC_Bootstrap
 */
class WH_GF_Multicolumn_Bootstrap {
	public static function load() {
		if ( ! method_exists( '\GFForms', 'include_addon_framework' ) ) {
			return;
		} else {
			GFAddOn::register( 'WH\GF\Multicolumn\Classes\WH_GF_Multicolumn' );
			WH\GF\Multicolumn\Classes\WH_GF_Multicolumn::get_instance();
		}
	}
}

WH_GF_Multicolumn_Bootstrap::load();

/**
 * Activation is responsible for adding form options
 * gfmc_enable_css and gfmc_enable_js.
 * Uninstall will remove those settings and delete all multicolumn form
 * element entries.
 */
register_activation_hook( __FILE__, 'gfmc_plugin_activate' );
register_uninstall_hook( __FILE__, 'gfmc_plugin_uninstall' );

function gfmc_plugin_activate() {
	$activator = new WH_GF_Multicolumn_Activator();
	$activator->gfmc_activate();
}

function gfmc_plugin_uninstall() {
	// The Gravity Forms AddOns automatically deletes: Form settings, Plugin
	// settings, Entry meta & Version information
	$uninstaller = new WH_GF_Multicolumn_Uninstaller();
	$uninstaller->gfmc_uninstall();
}
