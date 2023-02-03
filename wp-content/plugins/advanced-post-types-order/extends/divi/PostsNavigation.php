<?php


    function apto_divi_Posts_Navigation() 
        {
            if ( class_exists('ET_Builder_Module')) 
                {
                    include_once(APTO_PATH . '/extends/divi/PostsNavigation.class.php');
                }
        }
    add_action('et_builder_ready', 'apto_divi_Posts_Navigation', 9999);
    
    


?>