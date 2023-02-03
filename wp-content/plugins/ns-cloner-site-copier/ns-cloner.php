<?php
/**
 * Plugin Name: NS Cloner - Site Copier
 * Plugin URI: https://neversettle.it
 * Description: The amazing NS Cloner creates a new site as an exact clone / duplicate / copy of an existing site with theme and all plugins and settings intact in just a few steps. Check out NS Cloner Pro for additional powerful add-ons and features!
 * Version: 4.2.0
 * Author: Never Settle
 * Author URI: https://neversettle.it
 * Requires at least: 4.6.0
 * Tested up to: 6.0
 * License: GPLv2 or later
 *
 * Text Domain: ns-cloner-site-copier
 * Domain Path: /languages
 *
 * @package   NeverSettle\NS-Cloner
 * @author    Never Settle
 * @copyright Copyright (c) 2012-2018, Never Settle (dev@neversettle.it)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'NS_CLONER_PRO_PLUGIN', 'ns-cloner-pro-v4/ns-cloner-pro.php' );
define( 'NS_CLONER_PRO_URL', 'https://neversettle.it/buy/wordpress-plugins/ns-cloner-pro/?utm_campaign=in+plugin+referral&utm_source=ns-cloner&utm_medium=plugin&utm_content=pro+features' );
define( 'NS_CLONER_V4_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NS_CLONER_V4_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NS_CLONER_LOG_DIR', NS_CLONER_V4_PLUGIN_DIR . 'logs/' );

// Load external libraries.
require_once NS_CLONER_V4_PLUGIN_DIR . 'vendor/autoload.php';

// Load function files.
require_once NS_CLONER_V4_PLUGIN_DIR . 'ns-utils.php';
require_once NS_CLONER_V4_PLUGIN_DIR . 'ns-compatibility.php';

// Load cloner core classes.
require_once NS_CLONER_V4_PLUGIN_DIR . 'class-ns-cloner-process-manager.php';
require_once NS_CLONER_V4_PLUGIN_DIR . 'class-ns-cloner-schedule.php';
require_once NS_CLONER_V4_PLUGIN_DIR . 'class-ns-cloner-ajax.php';
require_once NS_CLONER_V4_PLUGIN_DIR . 'class-ns-cloner-report.php';
require_once NS_CLONER_V4_PLUGIN_DIR . 'class-ns-cloner-log.php';
require_once NS_CLONER_V4_PLUGIN_DIR . 'class-ns-cloner-request.php';
require_once NS_CLONER_V4_PLUGIN_DIR . 'class-ns-cloner.php';

// Load extendable base classes.
require_once NS_CLONER_V4_PLUGIN_DIR . 'abstracts/class-ns-cloner-addon.php';
require_once NS_CLONER_V4_PLUGIN_DIR . 'abstracts/class-ns-cloner-section.php';
require_once NS_CLONER_V4_PLUGIN_DIR . 'abstracts/class-ns-cloner-process.php';

// Load cloner features classes.
require_once NS_CLONER_V4_PLUGIN_DIR . 'features/class-ns-cloner-analytics.php';


ns_cloner();
