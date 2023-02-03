<?php
/**
 * Template for displaying warnings about possible issues before cloning (on cloner admin page).
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
 * Handle different possible scenarios with Pro and single vs multisite usage; there are a few possibilities:
 *
 * 1. If they have an OLD PRO VERSION, they should be alerted, no matter whether on network or single admin.
 * 2. If they are ON NON-NETWORK ADMIN with NO PRO VERSION, they should be:
 *     a. If single site, alerted why it's disabled.
 *     b. If multisite and network activated, given link to network admin.
 *     c. if multisite and not network activated, directed to network activate.
 * 3. If they are ON NON-NETWORK ADMIN with NEW PRO VERSION, they should be:
 *     b. If multisite and network activated, given link to network admin.
 *     c. if multisite and not network activated, directed to network activate.
 */

// #1: Warn about incompatible pre-v4 pro plugin if it's present
if ( remove_action( 'ns_cloner_before_construct', 'ns_cloner_addon_content_users_init' ) ) {
	echo "<span class='ns-cloner-error-message'>";
	// If remove_action returned true, then pre-v4 pro was installed, so show notice to explain why pro features are missing.
	$url_function = is_multisite() ? 'network_admin_url' : 'admin_url';
	printf(
		wp_kses(
			/* translators: URLs to plugins page and plugin license page */
			__( 'NS Cloner Pro needs to be updated for compatibility with NS Cloner 4. Please <a href="%1$s" target="_blank">click here</a> to check for the latest version, and then click "Update now" on NS Cloner Pro when you are redirected to the plugins page (you\'ll need to have <a href="%2$s" target="_blank">entered an active license</a> for the new version to show up).', 'ns-cloner-site-copier' ),
			ns_wp_kses_allowed()
		),
		esc_url( $url_function( 'plugins.php?check_for_updates=yes' ) ),
		esc_url( $url_function( 'admin.php?page=ns_cloner_pro_dashboard' ) )
	);
	echo '</span>';
}

// #2: Warn if activating core for single site without pro.
if ( ! is_network_admin() && ! defined( 'NS_CLONER_PRO_VERSION' ) ) {
	if ( ! is_multisite() ) {
		$message = __( 'The free version of NS Cloner only works on WordPress Multisite.', 'ns-cloner-site-copier' );
	} elseif ( is_plugin_active_for_network( plugin_basename( NS_CLONER_V4_PLUGIN_DIR . '/ns-cloner.php' ) ) ) {
		$message = sprintf(
			/* translators: URL to network cloner page */
			__( 'The free version of NS Cloner only works as a Multisite Network plugin (<a href="%s">go here to access the Network Cloner page</a>).', 'ns-cloner-site-copier' ),
			network_admin_url( 'admin.php?page=' . ns_cloner()->menu_slug )
		);
	} elseif ( is_multisite() ) {
		$message = sprintf(
			/* translators: URL to network plugins page */
			__( 'The free version of NS Cloner only works as a Network Activated plugin. Go to <a href="%s">Network Admin > Plugins</a> to activate it.', 'ns-cloner-site-copier' ),
			network_admin_url( 'plugins.php' )
		);
	}
	/* translators: URL to plugin info page */
	$pro_text = __( 'For cloning to and from single sites, check out <a href="%s" target="_blank">NS Cloner Pro</a>.', 'ns-cloner-site-copier' );
	$pro_link = sprintf( $pro_text, esc_url( NS_CLONER_PRO_URL ) );
	echo "<span class='ns-cloner-warning-message'>" . wp_kses( $message . ' ' . $pro_link, ns_wp_kses_allowed() ) . '</span>';
	// No other warnings matter now.
	return;
}

// #3: Notice if the installation is multisite but they're using it from a single site admin.
if ( ! is_network_admin() && defined( 'NS_CLONER_PRO_VERSION' ) ) {
	if ( is_plugin_active_for_network( plugin_basename( NS_CLONER_V4_PLUGIN_DIR . '/ns-cloner.php' ) ) ) {
		$message = sprintf(
			/* translators: URL to network cloner page */
			__( 'Just a heads up: the NS Cloner is most powerful and has additional features available when used as a Multisite Network plugin. You can <a href="%s">go here to access the Network Cloner page</a>.', 'ns-cloner-site-copier' ),
			network_admin_url( 'admin.php?page=' . ns_cloner()->menu_slug )
		);
		echo "<span class='ns-cloner-info-message'>" . wp_kses( $message, ns_wp_kses_allowed() ) . '</span>';
	} elseif ( is_multisite() ) {
		$message = sprintf(
			/* translators: URL to network plugins page */
			__( 'Just a heads up: the NS Cloner is most powerful and has additional features available as a Network Activated plugin. You can go to <a href="%s">Network Admin > Plugins</a> to activate it.', 'ns-cloner-site-copier' ),
			network_admin_url( 'plugins.php' )
		);
		echo "<span class='ns-cloner-info-message'>" . wp_kses( $message, ns_wp_kses_allowed() ) . '</span>';
	}
}

// Warn if logs are not writeable.
if ( ! is_writeable( NS_CLONER_LOG_DIR ) ) {
	echo "<span class='ns-cloner-warning-message'>";
	echo wp_kses(
		sprintf(
			// translators: %s: path to logs directory.
			__( 'The logs directory for NS Cloner (<code>%s</code>) is not writable by the server. The Cloner will still work, but logs won\'t be available in case anything needs debugging.', 'ns-cloner-site-copier' ),
			NS_CLONER_LOG_DIR
		),
		ns_wp_kses_allowed()
	);
	echo '</span>';
}

// Warn if max execution time is less than one minute.
$max_execution_time = intval( ini_get( 'max_execution_time' ) );
// 0 means unlimited, so make sure it's greater than that but less than a reasonable limit
if ( $max_execution_time > 0 && $max_execution_time < 60 ) {
	echo "<span class='ns-cloner-warning-message'>";
	// translators: %d: max execution time in seconds.
	echo esc_html( sprintf( __( 'This host\'s max_execution_time is set to %d seconds - we generally recommend at least 60 seconds for running the Cloner.', 'ns-cloner-site-copier' ), $max_execution_time ) );
	esc_html_e( 'You may want to increase the max_execution_time in php.ini (or wherever your host supports PHP configuration updates) to avoid any timeout errors.', 'ns-cloner-site-copier' );
	echo '</span>';
}

// Warn if memory limit is less than 128M.
if ( function_exists( 'ini_get' ) ) {
	$memory_limit = (int) ini_get( 'memory_limit' );
	if ( $memory_limit && -1 !== $memory_limit && wp_convert_hr_to_bytes( $memory_limit ) < 128 * MB_IN_BYTES ) {
		echo "<span class='ns-cloner-warning-message'>";
		// translators: %d: memory limit in megabytes.
		echo esc_html( sprintf( __( 'This host\'s memory_limit is set to %dMB - we generally recommend at least 128MB for running the Cloner.', 'ns-cloner-site-copier' ), $memory_limit ) );
		esc_html_e( 'You may want to increase the memory_limit in php.ini (or wherever your host supports PHP configuration updates) to avoid any out-of-memory errors.', 'ns-cloner-site-copier' );
		echo '</span>';
	}
}

// Warn if .htaccess does not contain multisite file rewrite (but only if not on iis7).
if ( is_multisite() && ! iis7_supports_permalinks() ) {
	// Foolproof htaccess path detection stolen from wp-admin/includes/network.php.
	$slashed_home      = trailingslashit( get_option( 'home' ) );
	$base              = wp_parse_url( $slashed_home, PHP_URL_PATH );
	$document_root_fix = str_replace( '\\', '/', realpath( $_SERVER['DOCUMENT_ROOT'] ) ); // phpcs:ignore
	$abspath_fix       = str_replace( '\\', '/', ABSPATH );
	$home_path         = 0 === strpos( $abspath_fix, $document_root_fix ) ? $document_root_fix . $base : get_home_path();
	if ( file_exists( $home_path . '.htaccess' ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions -- file_get_contents is intentional.
		$htaccess = file_get_contents( $home_path . '.htaccess' );
		// Set patterns which tell us that multisite file rewrite is there.
		$pre_3_5_rewrite  = 'wp-includes/ms-files.php';
		$post_3_5_rewrite = '(wp-(content|admin|includes)';
		// Show error if neither pattern occurs.
		if ( false === strpos( $htaccess, $pre_3_5_rewrite ) && false === strpos( $htaccess, $post_3_5_rewrite ) ) {
			echo "<span class='ns-cloner-warning-message'>";
			esc_html_e( 'It appears that you have a non-standard (possibly incorrect) .htaccess file for a multisite install. Cloned sites will not work if rewrites are not configured correctly. ' );
			printf(
				wp_kses(
					// translators: %s: url to network setup page.
					__( 'Please check the recommended htaccess settings <a href="%s" target="_blank">here</a> and make sure your .htaccess file matches.', 'ns-cloner-site-copier' ),
					ns_wp_kses_allowed()
				),
				esc_url( network_admin_url( 'setup.php' ) )
			);
			echo '</span>';
		}
	}
}

// Warn if AJAX requests return a 401 (probably because of HTTP basic auth), b/c process dispatching won't work if so.
$test_response = wp_remote_get( admin_url( 'admin-ajax.php' ) );
if ( is_wp_error( $test_response ) || 401 === wp_remote_retrieve_response_code( $test_response ) ) {
	echo "<span class='ns-cloner-warning-message'>";
	esc_html_e( 'It appears you have HTTP basic auth or something else blocking remote requests to your site, which means background cloning won\'t work. ', 'ns-cloner-site-copier' );
	esc_html_e( 'The Cloner will default to AJAX processing, so you should be able to still clone sites successfully, but progress will stop if you leave this page. ', 'ns-cloner-site-copier' );
	printf(
		wp_kses(
			// translators: %s: url to network setup page.
			__( 'If you have basic auth enabled, you can <a href="%s" target="_blank">add a workaround</a>.', 'ns-cloner-site-copier' ),
			ns_wp_kses_allowed()
		),
		'https://neversettle.it/documentation/ns-cloner/cloning-on-a-password-protected-site/'
	);
	if ( is_wp_error( $test_response ) ) {
		echo esc_html( ' Error: ' . $test_response->get_error_message() . '.' );
	}
	echo '</span>';
}
