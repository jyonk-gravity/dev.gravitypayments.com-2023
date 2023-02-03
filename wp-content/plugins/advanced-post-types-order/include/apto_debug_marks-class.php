<?php

    class APTO_debug_marks
        {
            
            var $debug_marks_indexes    =   array();
            
            function init()
                {
                        
                    add_filter('the_posts',             array ( $this, 'debug_marks__the_posts'), 99, 2 );
                    
                    add_action('wp_enqueue_scripts' ,   array($this, 'debug_marks_enqueue_scripts')); 
                    
                    add_action( 'wp_footer',            array($this, 'wp_footer'));              
                
                }   
            
            function debug_marks__the_posts( $posts, $query)
                {
                    $index  =   count ( $this->debug_marks_indexes ) +  1;
                    
                    ?>
                        <span class="apto_debug_block" data-id="<?php echo $index ?>"><?php echo $index ?></span>
                    <?php
                    
                    $_query  =   unserialize(serialize($query));
                    
                    $this->debug_marks_indexes[ $index ]    =   $this->filter_query_data ( $_query );
                
                    return $posts;   
                }
                
                
            function debug_marks_enqueue_scripts()
                {
                    wp_register_style('apto-debug-marks', APTO_URL . '/css/apto-debug-marks.css');
                    wp_enqueue_style( 'apto-debug-marks'); 
                    
                    wp_enqueue_script('jquery');
                    
                    wp_register_script('apto-debug-marks', APTO_URL . '/js/apto-debug-marks.js');
                    wp_enqueue_script( 'apto-debug-marks'); 
                
                }   

            
            function wp_footer()
                {
                    ?>
                    <div id="apto_debug_block_info">
                        <?php
                        
                            foreach ( $this->debug_marks_indexes    as  $key    =>  $data )
                                {
                                    ?>
                                    <div data-id="<?php echo $key ?>"><?php print_r($data) ?></div>
                                    <?php   
                                    
                                }
                        ?>                    
                    </div>
                    <?php   
                    
                    
                }
                
            function filter_query_data ( $query )
                {
                
                    //filter posts
                    foreach ($query->posts  as  $key  =>    $_post )
                        {
                            $newpost                =   new stdClass();
                            $newpost->ID            =   $_post->ID;
                            $newpost->post_title    =   $_post->post_title;
                            
                            $query->posts[ $key ]   =   $newpost;
                            
                        }
                
                    return $query;
                }    
            
        }
    
    $APTO_debug_marks   =   new APTO_debug_marks();
    $APTO_debug_marks->init();
        
?>