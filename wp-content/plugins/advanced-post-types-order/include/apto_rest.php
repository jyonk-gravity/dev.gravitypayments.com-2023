<?php

    class APTO_rest
        {

                    
            /**
            * Init the instance
            * 
            */
            function __construct()
                {
                    add_filter('rest_endpoints', array( $this , 'rest_endpoints'), 999);
                }
            
            
            function rest_post_collection_params( $query_params )
                {
                    $query_params['orderby']['enum'][]  =   'menu_order';    
                    
                    return $query_params;    
                }
                
            
            /**
            * append the required paramethers for orderby to endpoints
            * 
            */
            function rest_endpoints($endpoints)
                {
                    global $APTO;
                    
                    if(isset($endpoints['/wp/v2/posts']))
                        {
                            foreach($endpoints['/wp/v2/posts'] as  $key    =>  $data)
                                {
                                    if( !isset($data['methods']))
                                        continue;
                                    
                                    if(!isset($data['args'])    ||  !isset($data['args']['orderby'])    ||  !isset($data['args']['orderby']['enum']))
                                        continue;
                                        
                                    $data['args']['orderby']['enum'][]  =   'menu_order';
                                    
                                    $endpoints['/wp/v2/posts'][$key]   =   $data;
                                }
                        }
                        
                    if(isset($endpoints['/wp/v2/pages']))
                        {
                            foreach($endpoints['/wp/v2/pages'] as  $key    =>  $data)
                                {
                                    if( !isset($data['methods']))
                                        continue;
                                    
                                    if(!isset($data['args'])    ||  !isset($data['args']['orderby'])    ||  !isset($data['args']['orderby']['enum']))
                                        continue;
                                        
                                    $data['args']['orderby']['enum'][]  =   'menu_order';
                                    
                                    $endpoints['/wp/v2/pages'][$key]   =   $data;
                                }
                        }
                    
                    $post_types =   $APTO->functions->get_post_types();
                    if  ( isset ( $post_type['post'] ) )
                        unset ( $post_type['post'] );
                    if  ( isset ( $post_type['page'] ) )
                        unset ( $post_type['page'] );
                    
                    foreach  ( $post_types  as  $post_type )
                        {
                            if(isset($endpoints['/wp/v2/' . $post_type]))
                                {
                                    foreach($endpoints['/wp/v2/' . $post_type] as  $key    =>  $data)
                                        {
                                            if( !isset($data['methods']))
                                                continue;
                                            
                                            if(!isset($data['args'])    ||  !isset($data['args']['orderby'])    ||  !isset($data['args']['orderby']['enum']))
                                                continue;
                                                
                                            $data['args']['orderby']['enum'][]  =   'menu_order';
                                            
                                            $endpoints['/wp/v2/' . $post_type][$key]   =   $data;
                                        }
                                }
                        }
                
                       
                    return $endpoints;   
                }
                
        }


?>