<?php

/****************************************************
 * PRIME MOVE GLOBAL FUNCTIONS
 * Globally accessible by Prime Mover clases/scripts
 * **************************************************
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !function_exists( 'pm_fs' ) ) {
    // Create a helper function for easy SDK access.
    function pm_fs()
    {
        global  $pm_fs ;
        
        if ( !isset( $pm_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3826_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3826_MULTISITE', true );
            }
            // Include Freemius SDK.
            require_once PRIME_MOVER_MAINDIR . '/freemius/start.php';
            $pm_fs = fs_dynamic_init( array(
                'id'             => '3826',
                'slug'           => 'prime-mover',
                'premium_slug'   => 'prime-mover-pro',
                'type'           => 'plugin',
                'public_key'     => 'pk_a69fd5401be20bf46608b1c38165b',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 14,
                'is_require_payment' => true,
            ),
                'menu'           => array(
                'slug'    => 'migration-panel-settings',
                'network' => true,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $pm_fs;
    }
    
    // Init Freemius.
    pm_fs();
    // Signal that SDK was initiated.
    do_action( 'pm_fs_loaded' );
}

if ( !function_exists( 'primeMoverGetConfigurationPath' ) ) {
    function primeMoverGetConfigurationPath()
    {
        
        if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
            return ABSPATH . 'wp-config.php';
        } elseif ( @file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && !@file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
            return dirname( ABSPATH ) . '/wp-config.php';
        } else {
            return '';
        }
    
    }

}
if ( !function_exists( 'primeMoverGetUploadsDirectoryInfo' ) ) {
    function primeMoverGetUploadsDirectoryInfo()
    {
        $main_site_blog_id = 0;
        $multisite = false;
        if ( is_multisite() ) {
            $multisite = true;
        }
        if ( $multisite ) {
            $main_site_blog_id = get_main_site_id();
        }
        if ( $multisite ) {
            switch_to_blog( $main_site_blog_id );
        }
        $upload_dir = wp_upload_dir();
        if ( $multisite ) {
            restore_current_blog();
        }
        return $upload_dir;
    }

}
if ( !function_exists( 'primeMoverIsShaString' ) ) {
    function primeMoverIsShaString( $string = '', $mode = 256 )
    {
        if ( !$string ) {
            return false;
        }
        $lengths = [
            256 => 64,
            512 => 128,
        ];
        $length = $lengths[$mode];
        return (bool) preg_match( '/^[0-9a-f]{' . $length . '}$/i', $string );
    }

}
if ( !function_exists( 'is_php_version_compatible' ) ) {
    function is_php_version_compatible( $required )
    {
        return empty($required) || version_compare( phpversion(), $required, '>=' );
    }

}
if ( !function_exists( 'primeMoverIsEncKeyValid' ) ) {
    function primeMoverIsEncKeyValid()
    {
        return defined( 'PRIME_MOVER_DB_ENCRYPTION_KEY' ) && PRIME_MOVER_DB_ENCRYPTION_KEY;
    }

}
if ( !function_exists( 'primeMoverIsWpSiteUrlValid' ) ) {
    function primeMoverIsWpSiteUrlValid()
    {
        if ( is_multisite() ) {
            return true;
        }
        if ( defined( 'WP_SITEURL' ) && WP_SITEURL === get_site_url() ) {
            return true;
        }
        return false;
    }

}
if ( !function_exists( 'primeMoverIsWpHomeUrlValid' ) ) {
    function primeMoverIsWpHomeUrlValid()
    {
        if ( is_multisite() ) {
            return true;
        }
        if ( defined( 'WP_HOME' ) && WP_HOME === get_home_url() ) {
            return true;
        }
        return false;
    }

}
if ( !function_exists( 'primeMoverOutputSuggestedEncConstant' ) ) {
    function primeMoverOutputSuggestedEncConstant()
    {
        $key = wp_generate_password( 64, false, false );
        return "define( 'PRIME_MOVER_DB_ENCRYPTION_KEY', '" . $key . "' );";
    }

}
if ( !function_exists( 'primeMoverOutputSiteUrlParameter' ) ) {
    function primeMoverOutputSiteUrlParameter()
    {
        $site_url = get_site_url();
        return "define( 'WP_SITEURL', '" . $site_url . "' );";
    }

}
if ( !function_exists( 'primeMoverOutputHomeUrlParameter' ) ) {
    function primeMoverOutputHomeUrlParameter()
    {
        $home_url = get_home_url();
        return "define( 'WP_HOME', '" . $home_url . "' );";
    }

}
if ( !function_exists( 'primeMoverAutoDeactivatePlugin' ) ) {
    function primeMoverAutoDeactivatePlugin()
    {
        
        if ( defined( 'PRIME_MOVER_MAINPLUGIN_FILE' ) ) {
            $input_get = filter_input_array( INPUT_GET, array(
                'activate' => FILTER_VALIDATE_BOOLEAN,
            ) );
            
            if ( isset( $input_get['activate'] ) ) {
                unset( $_GET['activate'] );
                $_GET['deactivate'] = true;
            }
            
            $plugin_basename = plugin_basename( PRIME_MOVER_MAINPLUGIN_FILE );
            deactivate_plugins( $plugin_basename );
        }
    
    }

}