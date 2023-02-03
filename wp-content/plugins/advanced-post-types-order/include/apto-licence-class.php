<?php   
        
        if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
        /**
        * V2.2       
        */       
        class APTO_licence
        {
                
            /**
            * Retrieve licence details
            * 
            */
            public function get_licence_data()
                {
                    $licence_data = get_site_option('apto_license');
                    
                    $default =   array(
                                            'kye'               =>  '',
                                            'last_check'        =>  '',
                                            'licence_status'    =>  '',
                                            'licence_expire'    =>  ''
                                            );    
                    $licence_data           =   wp_parse_args( $licence_data, $default );
                    
                    return $licence_data;
                }
            
                
            /**
            * Reset license data
            *     
            * @param mixed $licence_data
            */
            public function reset_licence_data( $licence_data )
                {
                    if  ( ! is_array( $licence_data ) ) 
                        $licence_data   =   array();
                        
                    $licence_data['kye']                =   '';
                    $licence_data['last_check']         =   time();
                    $licence_data['licence_status']     =   '';
                    $licence_data['licence_expire']     =   '';
                    
                    return $licence_data;
                }
            
            /**
            * Set licence data
            *     
            * @param mixed $licence_data
            */
            public function update_licence_data( $licence_data )
                {
                    update_site_option('apto_license', $licence_data);   
                }
                
            function licence_key_verify()
                {
                    $license_data = $this->get_licence_data();
                    if(!is_array($license_data))
                        $license_data   =   array();
                    
                    if($this->is_local_instance())
                        return TRUE;
                             
                    if(!isset($license_data['kye']) || $license_data['kye'] == '')
                        return FALSE;
                        
                    return TRUE;
                }
                
            function is_local_instance()
                {
                        
                    if( defined('APTO_REQUIRE_KEY') &&  APTO_REQUIRE_KEY    === TRUE    )
                        return FALSE;
                    
                    $instance   =   trailingslashit(APTO_INSTANCE);
                    if(
                            stripos($instance, base64_decode('bG9jYWxob3N0Lw==')) !== FALSE
                        ||  stripos($instance, base64_decode('MTI3LjAuMC4xLw==')) !== FALSE
                        ||  stripos($instance, base64_decode('LmRldg==')) !== FALSE
                        ||  stripos($instance, base64_decode('c3RhZ2luZy53cGVuZ2luZS5jb20=')) !== FALSE
                        

                        
                        )
                        {
                            return TRUE;   
                        }
                        
                    return FALSE;
                    
                }
           
            
            
        }
            

        
    
?>