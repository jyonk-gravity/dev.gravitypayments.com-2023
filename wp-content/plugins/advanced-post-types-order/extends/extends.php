<?php
    
    class APTO_extends
        {
               
            function __construct()
                {
                    
                    //include extended DIVI modules
                    include_once(APTO_PATH . '/extends/divi/PostsNavigation.php');
                       
                }
      
        }
    
    new APTO_extends();
      
?>