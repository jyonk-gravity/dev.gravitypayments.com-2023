<?php

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    /**
    * v2.1.6
    */
    class APTO_CodeAutoUpdate
         {

             public     $api_url;
             
             private    $slug;
             public     $plugin;
             
             private    $API_VERSION;
             
             var        $licence;

             public function __construct( $api_url, $slug, $plugin )
                 {
                     $this->api_url = $api_url;
                     
                     $this->slug    = $slug;
                     $this->plugin  = $plugin;
                     
                     //use laets available API 
                     $this->API_VERSION =   1.1;
                 
                     global $APTO;
                     $this->licence     =   $APTO->licence->get_licence_data();
                 }
             
             
             public function check_for_plugin_update( $checked_data )
                 {
                     if ( !is_object( $checked_data ) ||  ! isset ( $checked_data->response ) )
                        return $checked_data;
                     
                     $request_string = $this->prepare_request('plugin_update');
                     if($request_string === FALSE)
                        return $checked_data;
                     
                     global $wp_version;
                     
                     //check if server was busy
                     $busy  =   get_site_transient ( 'apto-check_for_plugin_update__server_busy' );
                     if ( ! empty ( $busy ) )
                        return $checked_data;
                     
                     // Start checking for an update
                     $request_uri = $this->api_url . '?' . http_build_query( $request_string , '', '&');
                     
                     //check if cached
                     $data  =   get_site_transient( 'apto-check_for_plugin_update_' . md5( $request_uri ) );
                     
                     if ( isset ( $_GET['force-check'] ) && $_GET['force-check']    ==  '1' )
                        {
                            global $APTO_UpdateData;
                            $data   =   FALSE;
                            
                            if ( is_array ( $APTO_UpdateData ) &&    isset ( $APTO_UpdateData['apto-check_for_plugin_update_' . md5( $request_uri )] ))
                                $data   =   $APTO_UpdateData['apto-check_for_plugin_update_' . md5( $request_uri )];   
                        }
                     
                     $refresh   =   FALSE;
                     if  ( $data    === FALSE )
                         {
                             $refresh   =   TRUE;
                             
                             $data = wp_remote_get( $request_uri, array(
                                                                                'timeout'     => 10,
                                                                                'user-agent'  => 'WordPress/' . $wp_version . '; APTO/' . APTO_VERSION .'; ' . get_bloginfo( 'url' ),
                                                                                ) );
                             
                             if(is_wp_error( $data ) || $data['response']['code'] != 200)
                                {
                                    //try again in few hours
                                    set_site_transient( 'apto-check_for_plugin_update__server_busy' , '1', ( 60 * 60 * 4 ) +  rand ( 100, 36000 ) );
                                    
                                    return $checked_data;
                                }
                                
                             set_site_transient( 'apto-check_for_plugin_update_' . md5( $request_uri ), $data, ( 60 * 60 * 48 ) +  rand ( 100, 36000 ) );
                             
                             if ( isset ( $_GET['force-check'] ) && $_GET['force-check']    ==  '1' )
                                $APTO_UpdateData['apto-check_for_plugin_update_' . md5( $request_uri )]    =   $data;                             
                         }
                            
                     $response_block = json_decode( wp_remote_retrieve_body( $data ) );
                      
                     if(!is_array($response_block) || count($response_block) < 1)
                        return $checked_data;
                     
                     //retrieve the last message within the $response_block
                     $response_block = $response_block[count($response_block) - 1];
                     
                     $response  =   $this->postprocess_response( $response_block );
                     if ( $response ) 
                        {
                            $checked_data->response[$this->plugin] = $response;
                            
                            //update any licensing data
                            if ( $refresh   &&  isset ( $response->licence_status ) )
                                {
                                    $this->licence['licence_status']    =   $response->licence_status;
                                    $this->licence['licence_expire']    =   $response->licence_expire;
                                    $this->wph->licence->update_licence_data( $this->licence );
                                }   
                        }
                        
                     return $checked_data;
                 }
             
             
             public function plugins_api_call($def, $action, $args)
                 {
                     if (!is_object($args) || !isset($args->slug) || $args->slug != $this->slug)
                        return $def;
    
                     $request_string = $this->prepare_request($action, $args);
                     if($request_string === FALSE)
                        return new WP_Error('plugins_api_failed', __('An error occour when try to identify the pluguin.' , 'woo-global-cart') . '&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>'. __( 'Try again', 'woo-global-cart' ) .'&lt;/a>');;
                     
                     global $wp_version;
                     
                     $request_uri = $this->api_url . '?' . http_build_query( $request_string , '', '&');
                     
                     //check if cached
                     $data  =   get_site_transient( 'apto-check_for_plugin_update_' . md5( $request_uri ) );
                     
                     if ( isset ( $_GET['force-check'] ) && $_GET['force-check']    ==  '1' )
                        $data   =   FALSE;
                     
                     if  ( $data    === FALSE )
                         {
                             $data = wp_remote_get( $request_uri, array(
                                                                                'timeout'     => 10,
                                                                                'user-agent'  => 'WordPress/' . $wp_version . '; APTO/' . APTO_VERSION .'; ' . get_bloginfo( 'url' ),
                                                                                ) );
                             
                             if(is_wp_error( $data ) || $data['response']['code'] != 200)
                                return new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.' , 'woo-global-cart') . '&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>'. __( 'Try again', 'woo-global-cart' ) .'&lt;/a>', $data->get_error_message());
                                
                             set_site_transient( 'apto-check_for_plugin_update_' . md5( $request_uri ), $data, ( 60 * 60 * 48 ) +  rand ( 100, 36000 ) );
                         }
        
                     $response_block = json_decode($data['body']);
                     //retrieve the last message within the $response_block
                     $response_block = $response_block[count($response_block) - 1];
                     
                     $response  =   $this->postprocess_response( $response_block );
                     if ( $response ) 
                        return $response;
                        
                 }
             
             private function prepare_request($action, $args = array())
                 {                    
                     global $wp_version;
                                          
                     $request_data  =   array(
                                                 'woo_sl_action'        =>  $action,
                                                 'version'              =>  APTO_VERSION,
                                                 'product_unique_id'    =>  APTO_PRODUCT_ID,
                                                 'licence_key'          =>  $this->licence['kye'],
                                                 'domain'               =>  APTO_INSTANCE,
                                                 
                                                 'wp-version'           =>  $wp_version,
                                                 'api_version'          =>  $this->API_VERSION
                                                 );
                      
                     return $request_data;
                 }
                 
                 
             private function postprocess_response( $response_block )
                 {
                     $response = isset($response_block->message) ? $response_block->message : '';
                     
                     if ( is_object( $response ) && ! empty ( $response ) )
                         {
                             //include slug and plugin data
                             $response->slug    =   $this->slug;
                             $response->plugin  =   $this->plugin;
                             
                             //if sections are being set
                             if ( isset ( $response->sections ) )
                                $response->sections = (array)$response->sections;
                             
                             //if banners are being set
                             if ( isset ( $response->banners ) )
                                $response->banners = (array)$response->banners;
                               
                             //if icons being set, convert to array
                             if ( isset ( $response->icons ) )
                                $response->icons    =   (array)$response->icons;
                             
                             return $response;
                         }
                     
                     return FALSE;
                     
                 }
                 
             
             function in_plugin_update_message( $plugin_data, $response  )
                {
                    
                    if  ( empty ( $response->upgrade_notice ))
                        return;
                        
                    echo ' ' .  $response->upgrade_notice;
                    
                }
                 
         }
         
         
         function APTO_run_updater()
             {
                 
                $wp_plugin_auto_update = new APTO_CodeAutoUpdate( APTO_APP_API_URL, 'advanced-post-types-order', 'advanced-post-types-order/advanced-post-types-order.php');
                 
                // Take over the update check
                add_filter('site_transient_update_plugins', array($wp_plugin_auto_update, 'check_for_plugin_update'));
                 
                // Take over the Plugin info screen
                add_filter('plugins_api', array($wp_plugin_auto_update, 'plugins_api_call'), 10, 3);
                
                add_action('in_plugin_update_message-advanced-post-types-order/advanced-post-types-order.php',  array( $wp_plugin_auto_update, 'in_plugin_update_message'), 10, 2);
             
             }
         add_action( 'after_setup_theme', 'APTO_run_updater' );



?>