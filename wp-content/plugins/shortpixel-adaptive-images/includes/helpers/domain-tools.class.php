<?php
/**
 * User: simon
 * Date: 13.10.2020
 */

class ShortPixelDomainTools {

    private static $domainStatus;
    private static $cdnUsage;

    public static function is_our_cdn($cdn_domain) {
        $request = wp_safe_remote_get( 'https://' . $cdn_domain . '/assets/js/bundles/spai-lib.' . SHORTPIXEL_AI_VANILLAJS_VER . '.min.js' );

        if ( is_wp_error( $request ) ) {
                return false;
        }

        $headers = wp_remote_retrieve_headers( $request );

        return isset( $headers['x-sp-owner'] );

    }


    /**
     * Method fills the empty spaces with zero data in API response
     *
     * @param array|object $place
     * @param mixed $empty_data
     */
    private static function fill_cdn_usage( &$place, $empty_data ) {
        if ( empty( $place ) || ( !is_array( $place ) && !is_object( $place ) ) ) {
            return;
        }

        $is_object = false;

        if ( is_object( $place ) ) {
            $is_object = true;
            $place     = (array) $place;
        }

        // flag to double check the array has it more skipped dates?
        $has_skipped = false;

        foreach ( $place as $date => $value ) {
            $current_time       = time();
            $next_day_time      = strtotime( '+1 day', strtotime( $date ) );
            $next_day_formatted = date( 'Y-m-d', $next_day_time );

            // if next day is a real next day - stop it
            if ( $current_time < $next_day_time ) {
                break;
            }

            if ( empty( $place[ $next_day_formatted ] ) ) {
                $has_skipped                  = true;
                $place[ $next_day_formatted ] = $empty_data;
            }
        }

        if ( $has_skipped ) {
            self::fill_cdn_usage( $place, $empty_data );
        }

        // sorting by keys
        ksort( $place );

        $place = $is_object ? (object) $place : $place;
    }

    /**
     * Method retrieves information about domain from CDN's API
     * request only once per HTTP call
     *
     * @param null|string $domain Targeted domain (current by default)
     * @param null|string $key    API key which domain connected
     * @param bool $refresh
     *
     * @return false|object
     */
    public static function get_cdn_domain_usage( $domain = null, $key = null , $refresh = false) {
        if(self::$cdnUsage && !$refresh) {
            return self::$cdnUsage;
        }

        if ( empty( $key ) ) {
            $key = get_option( 'wp-short-pixel-apiKey', false );
        }

        if ( !is_string( $key ) || empty( $key ) ) {
            return false;
        }

        if ( !is_string( $domain ) || empty( $domain ) ) {
            $domain = self::get_site_domain();
        }

        $api_url  = ShortPixelAI::DEFAULT_STATUS_AI . '/read-domain-cdn-usage/' . $domain . '/' . $key;
        //echo($api_url);
        $response = \ShortPixel\AI\Request::get( $api_url );
        //var_dump($response);

        if ( $response === '' || !is_object($response)) {
            return false;
        }
        if($response instanceof WP_Error) {
            return (object)['error' => $response->get_error_message()];
        }

        foreach ( $response as $key => $value ) {
            if ( $key === date( 'Y-m-d' ) || ( isset( $value->Traf ) && $value->Traf == 0 ) ) {
                return false;
            }
        }

        $return = (object) [];

        if ( !empty( $response->Email ) ) {
            $return->email = self::truncate_email( $response->Email );
            $return->isSubaccount = $response->IsSubaccount;
        }

        $return->quota   = (object) [];
        $return->cdn     = (object) [];
        $return->credits = (object) [];

        $return->quota->monthly = (object) [];
        $return->quota->oneTime = (object) [];

        $return->cdn->total     = $response->CDNQuota;
        $return->cdn->used      = 0;
        $return->cdn->available = null;
        // it'll be calculated later
        $return->cdn->usedPercent      = null;
        $return->cdn->availablePercent = null;
        $return->cdn->detailed         = [];
        $return->cdn->chart            = (object) [
            'labels' => [],
            'data'   => (object) [
                'b'  => [],
                'kb' => [],
                'mb' => [],
                'gb' => [],
            ],
        ];

        // Monthly quota
        $return->quota->monthly->total            = (int) $response->APIQuota;
        $return->quota->monthly->used             = (int) $response->PaidAPICalls;
        $return->quota->monthly->used             = $return->quota->monthly->used > $return->quota->monthly->total ? $return->quota->monthly->total : $return->quota->monthly->used;
        $return->quota->monthly->available        = $return->quota->monthly->total - $return->quota->monthly->used;
        $return->quota->monthly->usedPercent      = $return->quota->monthly->total > 0 ? round( $return->quota->monthly->used / ( $return->quota->monthly->total / 100 ), 2 ) : 0.0;
        $return->quota->monthly->availablePercent = 100 - $return->quota->monthly->usedPercent;

        $return->quota->monthly->totalCDN         = (int) $response->CDNQuota;

        $return->quota->monthly->nextBillingDate = new DateTime();
        $return->quota->monthly->nextBillingDate->add(new DateInterval('P' . $response->DaysToReset . 'D'));
        $return->quota->monthly->lastBillingDate = clone $return->quota->monthly->nextBillingDate;
        $return->quota->monthly->lastBillingDate->sub(new DateInterval('P30D'));

        // One-time quota
        $return->quota->oneTime->total            = (int) $response->APIQuotaOneTime;
        $return->quota->oneTime->used             = (int) $response->PaidAPICallsOneTime;
        $return->quota->oneTime->used             = $return->quota->oneTime->used > $return->quota->oneTime->total ? $return->quota->oneTime->total : $return->quota->oneTime->used;
        $return->quota->oneTime->available        = $return->quota->oneTime->total - $return->quota->oneTime->used;
        $return->quota->oneTime->usedPercent      = $return->quota->oneTime->total > 0 ? round( $return->quota->oneTime->used / ( $return->quota->oneTime->total / 100 ), 2 ) : 0.0;
        $return->quota->oneTime->availablePercent = 100 - $return->quota->oneTime->usedPercent;

        $return->credits->detailed  = [];
        $return->credits->chart     = (object) [
            'labels' => [],
            'data'   => (object) [
                'paid'      => [],
                'free'      => [],
                'total'     => [],
                'original'  => (object) [
                    'b'  => [],
                    'kb' => [],
                    'mb' => [],
                    'gb' => [],
                ],
                'optimized' => (object) [
                    'b'  => [],
                    'kb' => [],
                    'mb' => [],
                    'gb' => [],
                ],
            ],
        ];
        $return->credits->original  = 0;
        $return->credits->optimized = 0;

        /* CDN Usage */
        if ( !empty( $response->UsedCDN ) ) {
            // filling empty spaces with zero data
            self::fill_cdn_usage( $response->UsedCDN, (object) [
                'Traf' => 0,
            ] );

            // creating our own object
            foreach ( $response->UsedCDN as $date => $value ) {
                $traffic = isset( $value->Traf ) ? $value->Traf : 0;

                $return->cdn->chart->labels[]   = date( 'M d', strtotime( $date ) );
                $return->cdn->chart->data->b[]  = $traffic;
                $return->cdn->chart->data->kb[] = round( $traffic / 1024, 2 );
                $return->cdn->chart->data->mb[] = round( $traffic / 1024 / 1024, 2 );
                $return->cdn->chart->data->gb[] = round( $traffic / 1024 / 1024 / 1024, 2 );

                $return->cdn->detailed[] = (object) [
                    'date'    => $date,
                    'traffic' => $traffic,
                ];

                if(new DateTime($date) >= $return->quota->monthly->lastBillingDate) {
                    $return->cdn->used += $traffic;
                }
            }
        }

        $return->cdn->available        = $return->cdn->total - $return->cdn->used;
        $return->cdn->usedPercent      = $return->cdn->total ? round( 100 * $return->cdn->used / $return->cdn->total, 2 ) : 0;
        $return->cdn->availablePercent = 100 - $return->cdn->usedPercent;

        // Credits Usage
        if ( !empty( $response->UsedCredits ) ) {
            // filling empty spaces with zero data
            self::fill_cdn_usage( $response->UsedCredits, (object) [
                'Orig' => 0,
                'Opt'  => 0,
                'Paid' => 0,
                'Free' => 0,
            ] );

            // creating our own object
            foreach ( $response->UsedCredits as $date => $value ) {
                $original  = isset( $value->Orig ) ? $value->Orig : 0;
                $optimized = isset( $value->Opt ) ? $value->Opt : 0;
                $free      = isset( $value->Free ) ? $value->Free : 0;
                $paid      = isset( $value->Paid ) ? $value->Paid : 0;

                $return->credits->chart->labels[]      = $date;
                $return->credits->chart->data->paid[]  = $paid;
                $return->credits->chart->data->free[]  = $free;
                $return->credits->chart->data->total[] = $paid + $free;

                // Original size
                $return->credits->chart->data->original->b[]  = $original;
                $return->credits->chart->data->original->kb[] = round( $original / 1024, 2 );
                $return->credits->chart->data->original->mb[] = round( $original / 1024 / 1024, 2 );
                $return->credits->chart->data->original->gb[] = round( $original / 1024 / 1024 / 1024, 2 );

                // Optimized size
                $return->credits->chart->data->optimized->b[]  = $optimized;
                $return->credits->chart->data->optimized->kb[] = round( $optimized / 1024, 2 );
                $return->credits->chart->data->optimized->mb[] = round( $optimized / 1024 / 1024, 2 );
                $return->credits->chart->data->optimized->gb[] = round( $optimized / 1024 / 1024 / 1024, 2 );

                $return->credits->detailed[] = (object) [
                    'date'      => $date,
                    'paid'      => $paid,
                    'free'      => $free,
                    'total'     => $paid + $free,
                    'original'  => $original,
                    'optimized' => $optimized,
                ];

                $return->credits->original  += $original;
                $return->credits->optimized += $optimized;

                $original_percent = $original / 100;

                $return->credits->optimizedPercent = $original_percent === 0 ? 0 : round( ( $original - $optimized ) / $original_percent, 2 );
            }
        }

        self::$cdnUsage = $return;
        return $return;
    }

    /**
     * Method returns current blog's domain
     * @return false|string
     */
    public static function get_site_domain() {
        return function_exists( 'parse_url' ) ? parse_url( get_home_url(), PHP_URL_HOST ) : false;
    }

    /**
     * Method truncates the email to hide customer's full email
     *
     * @param string $email
     *
     * @return false|string
     */
    public static function truncate_email( $email ) {
        $email = explode( '@', $email );

        if ( count( $email ) === 2 ) {
            $email[ 0 ] = substr( $email[ 0 ], 0, max( 3, round(strlen( $email[ 0 ] ) / 2 ) ) ) . "...";

            if ( strlen( $email[ 1 ] ) > 3 ) {
                return implode( '@', $email );
            }
        }

        return false;
    }

    public static function credits2bytes($credits, $precision = 2) {
        return self::formatBytes($credits * ShortPixelAI::ONE_CREDIT_IN_TRAFFIC, $precision);
    }
    /**
     * Transform a number of bytes in a human readable format.
     * @param $bytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . $units[$pow];
    }


    /**
     * Method gets the ShortPixel account using SPIO's API key
     *
     * @return array|mixed|object
     */
    public static function get_shortpixel_account()
    {
        $email = array();
        $resp = (object)['Status' => 0];
        if (($spKey = get_option('wp-short-pixel-apiKey', false))) {
            $resp = self::get_domain_status();
            if ($resp->Status == -3) {
                return $resp;
            }
            if ($resp->HasAccount) {
                return (object)array('Status' => 3, 'Message' => 'already associated', 'key' => '', 'email' => $resp->Email);
            }
            //the domain is not associated, check with SP API the user info for the key found locally
            $responseSP = wp_safe_remote_get(ShortPixelAI::SP_API . '/v2/user-info.php?key=' . $spKey, array('timeout' => 120, 'httpversion' => '1.1'));
            if (is_object($responseSP) && get_class($responseSP) == 'WP_Error') {
                return (object)array('Status' => -3, 'Message' => 'connection error', 'key' => '', 'email' => '');
            }
            if (isset($responseSP['response'])) {
                $respSP = json_decode($responseSP['body']);
                $email = explode('@', $respSP->email);
                if (/* $resp->HasAccount && */
                    count($email) == 2) {
                    $email[0] = substr($email[0], 0, max(3, intval(strlen($email[0]) / 2))) . "...";
                }
            }
        }
        return (object)array_merge((array)$resp, array('key' => $spKey, 'email' => implode('@', $email)));
    }

    /**
     * Method tries to associate current domain using SPIO's API key
     *
     * @return bool
     */
    public static function use_shortpixel_account($ctrl) {
        $spio_api_key = get_option( 'wp-short-pixel-apiKey', false );

        if ( $spio_api_key ) {
            $response = self::associate_domain( null, $spio_api_key );

            if ( $response->success ) {
                $ctrl->options->flags_all_account       = true;
                $ctrl->options->settings_general_apiKey = $spio_api_key;

                return true;
            }
        }

        return false;
    }

    /**
     * Even if refresh is on, make sure we do only one call per HTTP request
     * @param bool $refresh
     * @return bool|mixed|object
     */
    public static function get_domain_status($refresh = false, $domain = null) {
        if(!self::$domainStatus) {
            if ( !is_string( $domain ) || empty( $domain ) ) {
                $domain = self::get_site_domain();
            }

            if ( !$refresh && ( $domain_status = get_transient( 'spai_domain_status' ) ) ) {
                $domain_status->cached = 'yes';

                return self::$domainStatus = $domain_status;
            }

            //possible statuses: 2 OK (credits available, this is also for not associated domains) 0 - credits near limit,
            // -1 credits depleted, CDN active, -2 credits depleted, CDN inactive, -3 connection error
            $api_url  = ShortPixelAI::DEFAULT_STATUS_AI . '/read-domain/' . $domain;
            $response = wp_safe_remote_get( $api_url, [ 'timeout' => 120, 'httpversion' => '1.1' ] );

            if ( $response instanceof WP_Error || !isset( $response[ 'response' ] ) ) {
                return (object) [ 'Status' => -3, 'Message' => 'connection error: ' . $response->get_error_message(), 'HasAccount' => false, 'FreeCredits' => 0];
            }

            $domain_status = json_decode( wp_remote_retrieve_body( $response ) );
            if($domain_status) {
                set_transient('spai_domain_status', $domain_status, 600);
            }

            self::$domainStatus = $domain_status;
        }

        //deactivate SPAI until the status gets back to 2
        ShortPixelAI::_()->options->flags_all_credits = !( self::$domainStatus->Status == -1 );

        return self::$domainStatus;
    }

    public static function associate_domain( $domain = null, $key = null ) {
        if ( empty( $key ) ) {
            $key = get_option( 'wp-short-pixel-apiKey', false );
        }

        if ( !is_string( $key ) || empty( $key ) ) {
            return false;
        }

        if ( !is_string( $domain ) || empty( $domain ) ) {
            $domain = self::get_site_domain();
        }

        $api_url = ShortPixelAI::DEFAULT_STATUS_AI . '/add-domain/' . $domain . '/' . $key;
        $request = wp_safe_remote_get( $api_url, [ 'timeout' => 120, 'httpversion' => '1.1' ] );

        if ( $request instanceof WP_Error ) {
            return false;
        }

        if ( empty( $request[ 'response' ] ) || $request[ 'response' ][ 'code' ] !== 200 ) {
            return false;
        }

        $response = json_decode( $request[ 'body' ] );

        return (object) [
            'success' => $response->Status === 1 || $response->Status === 2,
            'status'  => $response->Status,
            'message' => $response->Message,
        ];
    }

    public static function propose_upgrade($key) {
        $args = [
            'method' => 'POST',
            'timeout' => 10,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array("params" => json_encode(array(
                'product' => 'spai',
                'plugin_version' => SHORTPIXEL_AI_VERSION,
                'key' => $key,
                'domain' => self::get_site_domain(),
                /* */
                'iconsUrl' => base64_encode(ShortPixelAI::DEFAULT_MAIN_DOMAIN . '/old/img')
            ))),
            'cookies' => array()
        ];

        $proposal = wp_remote_post(ShortPixelAI::DEFAULT_MAIN_DOMAIN . "/propose-upgrade-frag", $args);

        if(is_wp_error( $proposal )) {
            $proposal = array('body' => __('Error. Could not contact ShortPixel server for proposal', 'shortpixel-image-optimiser'));
        }
        die( $proposal['body'] );

    }
}
