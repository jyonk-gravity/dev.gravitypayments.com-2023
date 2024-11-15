<?php
namespace ShortPixel\AI;

class PluginUpdateNotice {

    public static function in_plugin_update_message( $data, $response ) {
        $version_parts = explode( '.', $response->new_version );

        $notice = '';

        if ( version_compare( PHP_VERSION, $response->requires_php, '<' ) ) {
            $notice .= '<span>' . sprintf( __( '<strong>Heads up! We do not recommend to update!</strong> ShortPixel Adaptive Images version <strong>%s</strong> is not compatible with your PHP version.', 'shortpixel-adaptive-images' ), $response->new_version ) . '</span>';
            $notice .= '<span>' . sprintf( __( 'The new ShortPixel Adaptive Images version requires at least PHP <strong>%s</strong> and your PHP version is <strong>%s</strong>', 'shortpixel-adaptive-images' ), $response->requires_php, PHP_VERSION ) . '</span>';

            echo wp_kses_post( $notice );

            return;
        }

        // Major
        if ( $version_parts[ 1 ] == '0' && ( isset( $version_parts[ 2 ] ) ? $version_parts[ 2 ] == '0' : true ) && version_compare( $version_parts[ 0 ] . '.' . $version_parts[ 1 ], SHORTPIXEL_AI_VERSION, '>' ) ) {
            $notice .= '<span>' . sprintf( __( '<strong>Heads up!</strong> %s is a %s update.', 'shortpixel-adaptive-images' ), $response->new_version, __( 'major', 'shortpixel-adaptive-images' ) ) . '</span>';
        }
        // Minor update message
        else if ( $version_parts[ 1 ] != '0' && ( isset( $version_parts[ 2 ] ) ? $version_parts[ 2 ] == '0' : true ) && version_compare( $version_parts[ 0 ] . '.' . $version_parts[ 1 ], SHORTPIXEL_AI_VERSION, '>' ) ) {
            $notice .= '<span>' . sprintf( __( '<strong>Heads up!</strong> %s is a %s update.', 'shortpixel-adaptive-images' ), $response->new_version, __( 'minor', 'shortpixel-adaptive-images' ) ) . '</span>';
        }

        $notice .= self::get_update_notice( $response );

        echo wp_kses_post( $notice );
    }


    /**
     * Get the upgrade notice from WordPress.org.
     *
     * @param object $response WordPress response
     *
     * @return string
     */
    private static function get_update_notice( $response ) {
        \ShortPixelAILogger::instance()->log("UPDATE NOTICE?", $response);
        $transient_name = 'spai_update_notice_' . $response->new_version;
        $update_notice  = get_transient( $transient_name );
        \ShortPixelAILogger::instance()->log("UPDATE NOTICE TRANSIENT?", $update_notice);

        if ( $update_notice === false ) {
            $readme_response = Request::get( 'https://plugins.svn.wordpress.org/shortpixel-adaptive-images/trunk/readme.txt', false );
            //$readme_response = Request::get( 'http://spaitest.shortpixel.com/wp-content/plugins/shortpixel-adaptive-images/readme.txt', false );
            \ShortPixelAILogger::instance()->log("GETTING FROM WP README:", substr($readme_response, 0, 500));

            if ( !empty( $readme_response ) ) {

                $update_notice = self::parse_update_notice( $readme_response, $response );
                \ShortPixelAILogger::instance()->log("UPDATE NOTICE SET", $update_notice);
                set_transient( $transient_name, $update_notice, DAY_IN_SECONDS );
            }
        }

        return $update_notice;
    }

    /**
     * Parse update notice from readme file.
     *
     * @param string $content  ShortPixel AI readme file content
     * @param object $response WordPress response
     *
     * @return string
     */
    private static function parse_update_notice( $content, $response ) {
        $version_parts     = explode( '.', $response->new_version );
        $maj = $version_parts[ 0 ];
        $min = $version_parts[ 1 ];
        $micro = isset($version_parts[ 2 ]) ? $version_parts[ 2 ] : '';
        $build = isset($version_parts[ 3 ]) ? $version_parts[ 3 ] : '';
        $check_for_notices = [
            $maj . '.' . $min . '.' . $micro . '.' . $build, // build
            $maj . '.' . $min . '.' . $micro, // patch (micro)
            $maj . '.' . $min . '.0', // minor
            $maj . '.' . $min, // minor
            $maj . '.0.0', // major
            $maj . '.0', // major
        ];

        $update_notice = '';

        foreach ( $check_for_notices as $id => $check_version ) {
            if ( version_compare( SHORTPIXEL_AI_VERSION, $check_version, '>' ) ) {
                continue;
            }

            $result = self::parse_readme_content( $content, $check_version, $response );

            if ( !empty( $result ) ) {
                $update_notice .= $result;
                break;
            }
        }

        return wp_kses_post( $update_notice );
    }

    /**
     * Parses readme file's content to find notice related to passed version
     *
     * @param string $content Readme file content
     * @param string $version Checked version
     * @param object $response WordPress response
     *
     * @return string
     */
    private static function parse_readme_content( $content, $version, $response ) {
        $notice_regexp = '/==\s*Upgrade Notice\s*==.*=\s*(' . preg_quote( $version ) . ')\s*=(.*)(=\s*' . preg_quote( $version . ':END' ) . '\s*=|$)/Uis';

        $notice = '';
        $matches = null;
        if ( preg_match( $notice_regexp, $content, $matches ) ) {
            $notices = (array) preg_split( '/[\r\n]+/', trim( $matches[ 2 ] ) );

            if ( version_compare( trim( $matches[ 1 ] ), $version, '=' ) ) {
                foreach ( $notices as $index => $line ) {
                    $notice .= '<span>';
                    $notice .= self::replace_readme_constants( self::markdown2html( $line ), $response );
                    $notice .= '</span>';
                }
            }
        }

        return $notice;
    }

    private static function replace_readme_constants( $content, $response ) {
        $constants    = [ '{{ NEW VERSION }}', '{{ CURRENT VERSION }}', '{{ PHP VERSION }}', '{{ REQUIRED PHP VERSION }}' ];
        $replacements = [ $response->new_version, SHORTPIXEL_AI_VERSION, PHP_VERSION, $response->requires_php ];

        return str_replace( $constants, $replacements, $content );
    }

    public static function markdown2html( $content ) {
        $patterns = [
            '/\*\*(.+)\*\*/U', // bold
            '/__(.+)__/U', // italic
            '/\[([^\]]*)\]\(([^\)]*)\)/U', // link
        ];

        $replacements = [
            '<strong>${1}</strong>',
            '<em>${1}</em>',
            '<a href="${2}" target="_blank">${1}</a>',
        ];

        $prepared_content = preg_replace( $patterns, $replacements, $content );

        return isset( $prepared_content ) ? $prepared_content : $content;
    }
}

