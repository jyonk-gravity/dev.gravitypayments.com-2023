<?php
    
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class APTO_compatibility_admin_columns_pro
        {
            function __construct()
                {
                    add_action ( 'apto/default_interface_sort/allow',                               array ( $this, 'acp_apto_default_interface_sort_allow' ), 999, 2 );
                    add_filter ( 'apto/default_interface_sort/taxonomy',                            array ( $this, 'acp_apto_default_interface_sort_taxonomy' ) );
                    add_filter ( 'apto/default_interface_sort/term_id',                             array ( $this, 'acp_apto_default_interface_sort_term_id' ) );
                }
                
                
            function acp_apto_default_interface_sort_allow( $allow, $sort_id )
                {
                    if ( ! isset ( $_GET['ac-rules'] )  ||   empty ( $_GET['ac-rules'] ) )
                        return $allow;
                    
                    if ( ! isset ( $_GET['layout'] ) ||  empty ( $_GET['layout']  ) )
                        return $allow;
                        
                    $_acp_layout    =   sanitize_text_field ( $_GET['layout'] );
                    $_acp_rules     =   @json_decode ( @stripslashes ( $_GET['ac-rules'] ) );
                    
                    if ( ! is_object( $_acp_rules ) )
                        return $allow;
                    
                    if ( count ( $_acp_rules->rules ) !== 1 )
                        return $allow;
                        
                    reset ( $_acp_rules->rules );
                    $_acp_rule =    current ( $_acp_rules->rules );
                    
                    if ( $_acp_rule->operator   !== 'equal' )
                        return false;
                    
                    $_acp_rule_id   =   $_acp_rule->id;
                    
                    global $wpdb;
      
                    // Use prepared statement
                    $mysql_query = $wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}admin_columns
                        WHERE list_id = %s",
                        $acp_layout
                    );
                    $_acp_layout_data = $wpdb->get_row($mysql_query);
                    
                    
                    
                    if ( ! is_object ( $_acp_layout_data ) )
                        return $allow;
                        
                    $_acp_columns   =   @unserialize ( $_acp_layout_data->columns );
                    
                    if ( ! isset ( $_acp_columns[ $_acp_rule_id ] ) )
                        return $allow;
                    
                    $_acp_column_setting    =   $_acp_columns[ $_acp_rule_id ];
                    if ( strpos( $_acp_column_setting['type'], 'field_' )  !== 0 )
                        return $allow;
                        
                    $_acp_filed_id  =   $_acp_column_setting['type'];
    
                    // Use prepared statement
                    $mysql_query = $wpdb->prepare(
                                                    "SELECT * FROM {$wpdb->prefix}posts
                                                    WHERE post_name = %s 
                                                    AND post_type = %s",
                                                    $acp_field_id,
                                                    'acf-field'
                                                );
                    $_acp_acf_field_data = $wpdb->get_row($mysql_query);
                    
                    $_acp_acf_field_settings    =   @unserialize ( $_acp_acf_field_data->post_content );
                    if ( $_acp_acf_field_settings['type'] !==   'taxonomy' )
                        return $allow;
                    
                    global $_apto_acp_compatibility;
                        
                    $taxnomy_name   =   $_acp_acf_field_settings['taxonomy'];
                    $term_id        =   $_acp_rule->value;
                    
                    $_apto_acp_compatibility    =   array();
                    $_apto_acp_compatibility['taxonomy']    =   $taxnomy_name;
                    $_apto_acp_compatibility['term_id']     =   $term_id;
                        
                    return $allow;
                }
                
            function acp_apto_default_interface_sort_taxonomy ( $taxonomy )
                {
                    global $_apto_acp_compatibility;
                    
                    if ( is_array ( $_apto_acp_compatibility )  &&  isset ( $_apto_acp_compatibility['taxonomy'] ) )
                    
                    return $_apto_acp_compatibility['taxonomy'];   
                }
                
                
            function acp_apto_default_interface_sort_term_id ( $term_id )
                {
                    global $_apto_acp_compatibility;
                    
                    if ( is_array ( $_apto_acp_compatibility )  &&  isset ( $_apto_acp_compatibility['term_id'] ) )
                    
                    return $_apto_acp_compatibility['term_id'];   
                }
                
        }
        
        
    new APTO_compatibility_admin_columns_pro();
        
        
