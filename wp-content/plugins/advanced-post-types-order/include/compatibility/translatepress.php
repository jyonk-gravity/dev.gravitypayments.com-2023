<?php
    
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class APTO_compatibility_translatepress
        {
            function __construct()
                {
                    add_action ( 'apto/blog_language',                               array ( $this, 'blog_language' ) );
                }
                
                
            /**
            * TranslatePress language adjust
            * 
            * @param mixed $language
            */
            function blog_language( $language )
                {
                    
                    $default_language   =   get_option( 'WPLANG' );
                    if ( ! empty ( $default_language ) )
                        $language   =   $default_language;
                    
                    if ( strpos ( $language, '_' )  !== FALSE )
                        {
                            $locale_data    =   explode("_", $language);
                            $language       =   $locale_data[0];
                        }
                    
                    return $language;   
                }
    
        }
        
        
    new APTO_compatibility_translatepress();
        
         