<?php


    class APTO_conditionals
        {
            var $rules  = array();
            
            function __construct()
                {   
                    $this->add_rule(array(
                                            'id'                    =>  'is_home',
                                            'title'                 =>  'Home',
                                            'admin_html'            =>  array($this, 'conditional_rule_is_home_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_is_home_query_check'),
                                            'comparison'            =>  array('IS', 'IS NOT')
                                            ));
                                            
                    $this->add_rule(array(
                                            'id'                    =>  'is_page',
                                            'title'                 =>  'Page',
                                            'admin_html'            =>  array($this, 'conditional_rule_is_page_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_is_page_query_check'),
                                            'comparison'            =>  array('IS', 'IS NOT')
                                            ));
                                            
                    $this->add_rule(array(
                                            'id'                    =>  'is_feed',
                                            'title'                 =>  'Feed',
                                            'admin_html'            =>  array($this, 'conditional_rule_is_feed_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_is_feed_query_check'),
                                            'comparison'            =>  array('IS', 'IS NOT')
                                            ));
                                            
                    $this->add_rule(array(
                                            'id'                    =>  'is_ajax',
                                            'title'                 =>  'AJAX',
                                            'admin_html'            =>  array($this, 'conditional_rule_ajax_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_ajax_query_check'),
                                            'comparison'            =>  array('IS', 'IS NOT')
                                            ));
                                            
                    $this->add_rule(array(
                                            'id'                    =>  'is_url',
                                            'title'                 =>  'URL',
                                            'admin_html'            =>  array($this, 'conditional_rule_is_url_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_is_url_query_check'),
                                            'comparison'            =>  array('IS', 'IS NOT', 'CONTAIN', 'NOT CONTAIN')
                                            ));
                                            
                    $this->add_rule(array(
                                            'id'                    =>  'query_orderby',
                                            'title'                 =>  'WP_Query > OrderBy',
                                            'admin_html'            =>  array($this, 'conditional_rule_query_orderby_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_query_orderby_query_check'),
                                            'comparison'            =>  array(
                                                                                'IS NOT SET',
                                                                                'IS'    =>  array(
                                                                                                    'ID',
                                                                                                    'author',
                                                                                                    'title',
                                                                                                    'name',
                                                                                                    'type',
                                                                                                    'date',
                                                                                                    'modified',
                                                                                                    'parent',
                                                                                                    'rand',
                                                                                                    'comment_count',
                                                                                                    'meta_value',
                                                                                                    'meta_value_num',
                                                                                                    'post__in'
                                                                                                    )
                                                                                )
                                            ));
                                            
                    $this->add_rule(array(
                                            'id'                    =>  'query_query_vars_hash',
                                            'title'                 =>  'WP_Query > query_vars_hash',
                                            'admin_html'            =>  array($this, 'conditional_rule_query_query_vars_hash_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_query_query_vars_hash_query_check'),
                                            'comparison'            =>  array('IS', 'IS NOT')
                                            ));
                                            
                    $this->add_rule(array(
                                            'id'                    =>  'query_is_main_query',
                                            'title'                 =>  'WP_Query > is_main_query()',
                                            'admin_html'            =>  array($this, 'conditional_rule_query_is_main_query_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_query_is_main_query_query_check'),
                                            'comparison'            =>  array('TRUE', 'FALSE')
                                            ));
                    
                    $this->add_rule(array(
                                            'id'                    =>  'get',
                                            'title'                 =>  '$_GET',
                                            'admin_html'            =>  array($this, 'conditional_rule_get_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_get_query_check'),
                                            'comparison'            =>  array('Contains', 'Not Contains')
                                            ));
                                            
                    $this->add_rule(array(
                                            'id'                    =>  'post',
                                            'title'                 =>  '$_POST',
                                            'admin_html'            =>  array($this, 'conditional_rule_post_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_post_query_check'),
                                            'comparison'            =>  array('Contains', 'Not Contains')
                                            ));
                    
                    $this->add_rule(array(
                                            'id'                    =>  'request',
                                            'title'                 =>  '$_REQUEST',
                                            'admin_html'            =>  array($this, 'conditional_rule_request_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_request_query_check'),
                                            'comparison'            =>  array('Contains', 'Not Contains')
                                            ));
                                            
                    
                    
                    $files  =   $this->query_caller_is_template_file_get_template_files();
                    $this->add_rule(array(
                                            'id'                    =>  'query_caller_is_template_file',
                                            'title'                 =>  'Template - Caller',
                                            'admin_html'            =>  array($this, 'conditional_rule_query_caller_is_template_file_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_query_caller_is_template_file_query_check'),
                                            'comparison'            =>  array(
                                                                                'IS'   =>  $files,
                                                                                'IS NOT'   =>  $files,
                                                                                )
                                            ));
                                            
                    $this->add_rule(array(
                                            'id'                    =>  'is_user_id',
                                            'title'                 =>  'User',
                                            'admin_html'            =>  array($this, 'conditional_rule_is_user_id_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_is_user_id_query_check'),
                                            'comparison'            =>  array('IS', 'IS NOT')
                                            ));
                    
                    $this->add_rule(array(
                                            'id'                    =>  'user_role',
                                            'title'                 =>  'User Role',
                                            'admin_html'            =>  array($this, 'conditional_rule_user_role_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_user_role_query_check'),
                                            'comparison'            =>  array('IS', 'IS NOT')
                                            ));
                                            
                    $this->add_rule(array(
                                            'id'                    =>  'user_capability',
                                            'title'                 =>  'User Capability',
                                            'admin_html'            =>  array($this, 'conditional_rule_user_capability_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_user_capability_query_check'),
                                            'comparison'            =>  array('IS', 'IS NOT')
                                            ));
                                            
                    $this->add_rule(array(
                                            'id'                    =>  'mobile',
                                            'title'                 =>  'Mobile',
                                            'admin_html'            =>  array($this, 'conditional_rule_mobile_admin_html'),
                                            'query_check_callback'  =>  array($this, 'conditional_rule_mobile_query_check'),
                                            'comparison'            =>  array('IS', 'IS NOT')
                                            ));
                                            
                    do_action('apto_conditionals_add', $this);
                    
                }
                
            function add_rule($options)
                {
                    //check if id already exists
                    if(isset($conditional_rules[$options['id']]))
                        return FALSE;
                        
                    $this->rules[$options['id']] =  array(
                                                                    'title'                 =>  $options['title'],
                                                                    'admin_html'            =>  $options['admin_html'],
                                                                    'query_check_callback'  =>  $options['query_check_callback'],
                                                                    'comparison'            =>  $options['comparison']
                                                                    );
                                                                    
                    return TRUE;
                }
            
            /**
            * Return rule comparison available values
            *     
            * @param mixed $rule_id
            */
            function get_rule_comparison($rule_id)
                {
                    
                    return ($this->rules[$rule_id]['comparison']);
                }
                
                
            function conditional_rule_is_home_admin_html($options)
                {
                    //no output is required
                    
                }

            function conditional_rule_is_home_query_check($comparison, $value, $query)
                {
                    //check against the main query
                    global $wp_the_query;
                    
                    $condition_status = FALSE;
                    
                    if(!isset($wp_the_query->query)  || is_null($wp_the_query->query))
                        $ref_query  =   $query;
                        else
                        $ref_query  =   $wp_the_query;
                        
                    if($ref_query->is_home)
                        $condition_status   =   TRUE;
                        
                    if($comparison == 'IS NOT')
                        $condition_status   =   ($condition_status) ?  FALSE : TRUE;
                           
                    return $condition_status;    
                }
                
                
            function conditional_rule_is_page_admin_html($options)
                {
                    $args = array(
                                        'name'          =>  'conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]',
                                        'depth'         => 0,
                                        'title_li'      => '',
                                        'echo'          => 0,
                                        'sort_column'   => 'menu_order, post_title',
                                        'post_type'     => 'page',
                                        'post_status'   => 'publish' ,
                                        'selected'      => $options['selected_value'] 
                                    );   
                    $html = wp_dropdown_pages($args);
                    
                    return $html;   
                }

            function conditional_rule_is_page_query_check($comparison, $value, $query)
                {
                    //check against the main query
                    global $wp_the_query;
                    
                    $condition_status = false;
                    
                    if(!isset($wp_the_query->query)  || is_null($wp_the_query->query))
                        $ref_query  =   $query;
                        else
                        $ref_query  =   $wp_the_query;
                        
                    if($ref_query->is_page($value))
                        $condition_status   =   TRUE;
                        
                    if($comparison == 'IS NOT')
                        $condition_status   =   ($condition_status) ?  FALSE : TRUE;
                           
                    return $condition_status;   
                }
                
                
            function conditional_rule_is_feed_admin_html($options)
                {
                    //no output is required   
                }

            function conditional_rule_is_feed_query_check($comparison, $value, $query)
                {
                    //check against the main query
                    global $wp_the_query;
                    
                    $condition_status = false;
                    
                    if(!isset($wp_the_query->query)  || is_null($wp_the_query->query))
                        $ref_query  =   $query;
                        else
                        $ref_query  =   $wp_the_query;
                        
                    if($ref_query->is_feed())
                        $condition_status   =   TRUE;
                        
                    if($comparison == 'IS NOT')
                        $condition_status   =   ($condition_status) ?  FALSE : TRUE;
                           
                    return $condition_status;   
                }
                
                
            function conditional_rule_ajax_admin_html($options)
                {
                    //no output is required   
                }

            function conditional_rule_ajax_query_check( $comparison, $value, $query )
                {
                    $condition_status = FALSE;
                    
                    if ( (defined('DOING_AJAX') && DOING_AJAX) )
                        $condition_status   =   TRUE;
                        
                    if ( $comparison == 'IS NOT' )
                        $condition_status   =   ( $condition_status ) ?  FALSE : TRUE;
                           
                    return $condition_status;   
                }
                
                
            function conditional_rule_is_url_admin_html($options)
                {
                    $html = '<input type="text" name="conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]" class="text" value="'. htmlspecialchars($options['selected_value']) .'">';
                    
                    return $html;   
                }
                
            function conditional_rule_is_url_query_check( $comparison, $value, $query )
                {
                    $condition_status = FALSE;
                    
                    $protocol   =   strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https';
                    $host       =   $_SERVER['HTTP_HOST'];
                    $script     =   $_SERVER['REQUEST_URI'];
                    $params     =   $_SERVER['QUERY_STRING'];
                    $currentUrl =   $host . $script;
                    if(!empty($params))
                        $currentUrl .= '?' . $params;
                        
                    //stripp off the value protocol
                    $value  =   str_replace( array("http://", "https://"), "" , $value);
                    
                    switch ($comparison)
                        {
                            case 'IS':
                                            if($currentUrl  ==  $value)
                                                $condition_status   =   TRUE;
                                            break;

                            case 'IS NOT':
                                            $condition_status   =   ($currentUrl  ==  $value) ?  FALSE : TRUE;
                                            break;
                            
                            case 'CONTAIN':
                                            if(strpos($currentUrl, $value) !== FALSE)
                                                $condition_status   =   TRUE;
                                            break;
                                            
                            case 'NOT CONTAIN':
                                            if(strpos($currentUrl, $value) === FALSE)
                                                $condition_status   =   TRUE;
                                            break;
                        }
                           
                    return $condition_status;   
                }
            
            
            
            function conditional_rule_query_orderby_admin_html($options)
                {
                    $html   =   '';
                    
                    $comparison_value   =   $options['comparison_value'];
                    
                    if($comparison_value ==  '')
                        {
                            reset($options['comparison']);
                            $comparison_value   =   key($options['comparison']);
                        }
                    
                    
                    if(isset($options['comparison'][$comparison_value]) && is_array($options['comparison'][$comparison_value]))
                        {
                            $html = '<select id="conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]" name="conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]">';
                            
                            foreach($options['comparison'][$comparison_value]    as  $key    =>  $value)
                                {
                                    $html .= '<option '. selected( $options['selected_value'], $value, FALSE )  .'value="'.$value.'">'.$value.'</option>';
                                }
                            
                            $html .= '</select>';
                       
                        }
                        
                    return $html;
                }
                
            function conditional_rule_query_orderby_query_check($comparison, $value, $query)
                {
                    $condition_status = FALSE;
                    
                    
                    switch ($comparison)
                        {
                            case 'IS NOT SET':  
                                                if(!isset($query->query['orderby']))
                                                    $condition_status   =   TRUE;
                                                               
                                                break;
             
                            case 'IS':  
                                                if(isset($query->query['orderby'])  && strtolower($query->query['orderby']) ==  strtolower($value))
                                                    $condition_status   =   TRUE;
                                                               
                                                break;
                        }
                           
                    return $condition_status;   
                }
                
             function conditional_rule_query_query_vars_hash_admin_html($options)
                {
                    $html = '<input type="text" name="conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]" class="text" value="'. htmlspecialchars($options['selected_value']) .'">';
                    
                    return $html;   
                }
                
            function conditional_rule_query_query_vars_hash_query_check($comparison, $value, $query)
                {
                    $condition_status = false;
                        
                    switch ($comparison)
                        {
                            case 'IS':
                                            if(isset($query->query_vars_hash) && $query->query_vars_hash  ==  $value)
                                                $condition_status   =   TRUE;
                                            break;

                            case 'IS NOT':
                                            if(isset($query->query_vars_hash) && $query->query_vars_hash  !=  $value)
                                                $condition_status   =   TRUE;
                                            break;
      
                        }
                           
                    return $condition_status;   
                }
                
                
            function conditional_rule_query_is_main_query_admin_html($options)
                {
                    //no output is required
                    
                }

            function conditional_rule_query_is_main_query_query_check($comparison, $value, $query)
                {
                    
                    $condition_status = FALSE;
                    
                    switch ($comparison)
                        {
                            case 'TRUE':
                                            if($query->is_main_query())
                                                $condition_status   =   TRUE;
                                                else
                                                $condition_status   =   FALSE;
                                            break;

                            case 'FALSE':
                                            if($query->is_main_query())
                                                $condition_status   =   FALSE;
                                                else
                                                $condition_status   =   TRUE;
                                            break;
      
                        }
                                  
                    return $condition_status;    
                }
            
            
            /**
            * The superglobal $_GET
            *     
            * @param mixed $options
            */
            function conditional_rule_get_admin_html($options)
                {
                    $html = '<input type="text" name="conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]" class="text" value="'. htmlspecialchars($options['selected_value']) .'">';
                                        
                    return $html;   
                }

            /**
            * The superglobal $_REQUEST
            *     
            * @param mixed $comparison
            * @param mixed $value
            * @param mixed $query
            * @return {FALSE|TRUE}
            */
            function conditional_rule_get_query_check ( $comparison, $value, $query )
                {
                    
                    $condition_status = FALSE;
                    
                    parse_str ( $value , $data );
                    
                    if ( is_array ( $data ) )
                        {                                            
                            if ( $comparison == 'Contains' )
                                {
                                    $condition_status = FALSE;
                                    
                                    // Determine if all keys and values in $data are found in $_REQUEST
                                    if ( $this->conditional_rule_request_all_keys_found_recursive($data, $_GET))
                                        $condition_status = TRUE;   
                                }
                                
                            if ( $comparison == 'Not Contains' )
                                {
               
                                    $condition_status = TRUE;

                                    // Determine if all keys and values in $data are found in $_REQUEST
                                    if ( $this->conditional_rule_request_all_keys_found_recursive($data, $_GET))
                                        $condition_status = FALSE;
   
                                }                            
                        }
                           
                    return $condition_status;   
                }
                
                
            /**
            * The superglobal $_POST
            *     
            * @param mixed $options
            */
            function conditional_rule_post_admin_html($options)
                {
                    $html = '<input type="text" name="conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]" class="text" value="'. htmlspecialchars($options['selected_value']) .'">';
                                        
                    return $html;   
                }

            /**
            * The superglobal $_REQUEST
            *     
            * @param mixed $comparison
            * @param mixed $value
            * @param mixed $query
            * @return {FALSE|TRUE}
            */
            function conditional_rule_post_query_check ( $comparison, $value, $query )
                {
                    
                    $condition_status = FALSE;
                    
                    parse_str ( $value , $data );
                    
                    if ( is_array ( $data ) )
                        {                                            
                            if ( $comparison == 'Contains' )
                                {
                                    $condition_status = FALSE;
                                    
                                    // Determine if all keys and values in $data are found in $_REQUEST
                                    if ( $this->conditional_rule_request_all_keys_found_recursive($data, $_POST ))
                                        $condition_status = TRUE;   
                                }
                                
                            if ( $comparison == 'Not Contains' )
                                {
               
                                    $condition_status = TRUE;

                                    // Determine if all keys and values in $data are found in $_REQUEST
                                    if ( $this->conditional_rule_request_all_keys_found_recursive($data, $_POST ))
                                        $condition_status = FALSE;
   
                                }                            
                        }
                           
                    return $condition_status;   
                }
            
                
                
            /**
            * The superglobal $_REQUEST
            *     
            * @param mixed $options
            */
            function conditional_rule_request_admin_html($options)
                {
                    $html = '<input type="text" name="conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]" class="text" value="'. htmlspecialchars($options['selected_value']) .'">';
                                        
                    return $html;   
                }

            /**
            * The superglobal $_REQUEST
            *     
            * @param mixed $comparison
            * @param mixed $value
            * @param mixed $query
            * @return {FALSE|TRUE}
            */
            function conditional_rule_request_query_check ( $comparison, $value, $query )
                {
                    
                    $condition_status = FALSE;
                    
                    parse_str ( $value , $data );
                    
                    if ( is_array ( $data ) )
                        {                                            
                            if ( $comparison == 'Contains' )
                                {
                                    $condition_status = FALSE;
                                    
                                    // Determine if all keys and values in $data are found in $_REQUEST
                                    if ( $this->conditional_rule_request_all_keys_found_recursive($data, $_REQUEST))
                                        $condition_status = TRUE;   
                                }
                                
                            if ( $comparison == 'Not Contains' )
                                {
               
                                    $condition_status = TRUE;

                                    // Determine if all keys and values in $data are found in $_REQUEST
                                    if ( $this->conditional_rule_request_all_keys_found_recursive($data, $_REQUEST))
                                        $condition_status = FALSE;
   
                                }                            
                        }
                           
                    return $condition_status;   
                }
                
            
            /**
             * Recursively checks if all keys and values from $data are found in $request.
             * 
             * @param array $data The data structure to check.
             * @param array $request The request data (e.g., $_REQUEST).
             * @return bool Returns true if all keys and values match, false otherwise.
             */
            function conditional_rule_request_all_keys_found_recursive($data, $request) 
                {
                    foreach ($data as $key => $key_value) 
                        {
                            // If the key does not exist in the request, return false
                            if (!array_key_exists($key, $request))
                                return false;

                            // If the value is an array, recurse
                            if (is_array($key_value)) 
                                {
                                    if (!is_array($request[$key]))
                                        return false;

                                    if ( ! $this->conditional_rule_request_all_keys_found_recursive($key_value, $request[$key]))
                                        return false;
                                }
                            else 
                                {
                                    if ($request[$key] !== $key_value) {
                                        return false;
                                    }
                                }
                        }
                    return true;
                }    
                
                
                
                
            
            function query_caller_is_template_file_get_template_files()
                {
                    $template_files_list    =   array();   
                    
                    $files  =   @scandir(get_template_directory());
                    
                    if(!is_array($files)    ||  count($files) < 1)
                        return $template_files_list;
                        
                    foreach($files  as  $file)
                        {
                            if(strpos($file, '.php')    === FALSE)   
                                continue;
                                
                            $template_files_list[]  =   $file;
                        }
                        
                    return $template_files_list;
                }
                
            function conditional_rule_query_caller_is_template_file_admin_html($options)
                {
                    $html   =   '';
                    
                    $comparison_value   =   $options['comparison_value'];
                    
                    if($comparison_value ==  '')
                        {
                            reset($options['comparison']);
                            $comparison_value   =   key($options['comparison']);
                        }
                    
                    
                    if(isset($options['comparison'][$comparison_value]) && is_array($options['comparison'][$comparison_value]))
                        {
                            $html = '<select id="conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]" name="conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]">';
                            
                            foreach($options['comparison'][$comparison_value]    as  $key    =>  $value)
                                {
                                    $html .= '<option '. selected( $options['selected_value'], $value, FALSE )  .' value="'.$value.'">'.$value.'</option>';
                                }
                            
                            $html .= '</select>';
                       
                        }
                                    
                    return $html;
                    
                }

            function conditional_rule_query_caller_is_template_file_query_check($comparison, $value, $query)
                {

                    $condition_status = FALSE;
                                   
                    $backtrace  =   debug_backtrace();
                    
                    $found = FALSE;
                    foreach($backtrace  as  $backtrace_item)
                        {
                            if(!isset($backtrace_item['file']))
                                continue;

                            if(strpos($backtrace_item['file'],$value)    !== FALSE)
                                {
                                    $found  =   TRUE;
                                    break;
                                }
                            
                        }
                                            
                    switch ($comparison)
                        {
                            case 'IS':  
                                                if($found === TRUE)
                                                    $condition_status   =   TRUE;
                                                               
                                                break;
             
                            case 'IS NOT':  
                                                if($found === FALSE)
                                                    $condition_status   =   TRUE;
                                                               
                                                break;
                        }
                           
                    return $condition_status;    
                }
                
                
                
            function conditional_rule_is_user_id_admin_html($options)
                {
                    $args = array(
                                        'name'          =>  'conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]',
                                        'title_li'      =>  '',
                                        'echo'          =>  0,
                                        'multi'         =>  false,
                                        'selected'      =>  $options['selected_value'] 
                                    );   
                    $html = wp_dropdown_users($args);
                    
                    return $html;   
                }

            function conditional_rule_is_user_id_query_check($comparison, $value, $query)
                {
                    //check against the main query
                    global $wp_the_query;
                    
                    $condition_status = false;
                    
                    if ( ! is_user_logged_in() )
                        return FALSE;
                        
                    $user = wp_get_current_user();
                    if  ( $value == $user->ID)
                        $condition_status   =   TRUE;
                        
                    if($comparison == 'IS NOT')
                        $condition_status   =   ($condition_status) ?  FALSE : TRUE;
                           
                    return $condition_status;   
                } 
            
            function conditional_rule_user_role_admin_html($options)
                {
                    $html = '';
                    
                    $html = '<select name="conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]">';
                    
                    $editable_roles = array_reverse( get_editable_roles() );

                    foreach ( $editable_roles as $role => $details ) 
                        {
                            $name = translate_user_role($details['name'] );
                            // preselect specified role
                            if ( $options['selected_value'] == $role ) {
                                $html .= "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
                            } else {
                                $html .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
                            }
                        }
                    
                    $html .= '</select>';
                    
                    return $html;   
                }

            function conditional_rule_user_role_query_check($comparison, $value, $query)
                {
                    //check against the main query
                    global $wp_the_query;
                    
                    $condition_status = false;
                    
                    if ( ! is_user_logged_in() )
                        return FALSE;

                    $user = wp_get_current_user();
                    if ( in_array( $value, (array) $user->roles ) ) 
                        {
                            $condition_status   =   TRUE;
                        }                           
                           
                    if($comparison == 'IS NOT')
                        $condition_status   =   ($condition_status) ?  FALSE : TRUE;
                           
                    return $condition_status;   
                }
            
                
            function conditional_rule_user_capability_admin_html($options)
                {
                    $html = '';
                    
                    $html = '<select name="conditional_rules['.$options['group_id'].']['.$options['row_id'].'][conditional_value]">';
                    
                    $editable_roles = array_reverse( get_editable_roles() );
                    $all_capabilities   =   array();

                    foreach ( $editable_roles as $role => $details ) 
                        {
                            foreach ( $details['capabilities']  as $capability  =>  $is_set )
                                {
                                    if( ! in_array( $capability, $all_capabilities ) )
                                        {
                                            $all_capabilities[] =   $capability;
                                        }
                                }
                        }
                    
                    sort($all_capabilities, SORT_NATURAL);
                    
                    foreach ($all_capabilities  as $capability)
                        {
                            if ( $options['selected_value'] == $capability ) {
                                    $html .= "\n\t<option selected='selected' value='" . esc_attr( $capability ) . "'>$capability</option>";
                                } else {
                                    $html .= "\n\t<option value='" . esc_attr( $capability ) . "'>$capability</option>";
                                }   
                        }
                    
                    $html .= '</select>';
                    
                    return $html;   
                }

            function conditional_rule_user_capability_query_check($comparison, $value, $query)
                {
                    //check against the main query
                    global $wp_the_query;
                    
                    $condition_status = false;
                    
                    if ( ! is_user_logged_in() )
                        return FALSE;

                    if  ( current_user_can ( $value ) )
                        $condition_status   =   TRUE;
                        
                    if($comparison == 'IS NOT')
                        $condition_status   =   ($condition_status) ?  FALSE : TRUE;
                           
                    return $condition_status;   
                }
                
                
            function conditional_rule_mobile_admin_html( $options )
                {
                    //no output is required  
                }

            function conditional_rule_mobile_query_check( $comparison, $value, $query )
                {
                    
                    $condition_status = wp_is_mobile();

                    if($comparison == 'IS NOT')
                        $condition_status   =   ( $condition_status ) ?  FALSE : TRUE;
                           
                    return $condition_status;   
                } 
                
                  
            
        }
                
?>