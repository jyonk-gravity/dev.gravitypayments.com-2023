<?php


    /**
    * WooCommerce support class
    */
    class APTO_woocommerce
        {
            var $APTO;
            
            function __construct()
                {
                    
                    global $APTO, $woocommerce;
                    $this->APTO             =   $APTO; 
                    
                    $site_settings          =   $APTO->functions->get_settings();
                    
                    /**
                    * WooCommerce Up-sells Sort  
                    */
                    if( $site_settings['woocommerce_upsells_sort']   ==  '1'    )
                        {
                            if( is_admin()  )
                                {
                                    //admin
                                    add_filter('current_screen', array($this, 'woocommerce_upsells_sort'));   
                                }
                                else
                                {
                                    //front side
                                    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
                                    
                                    /**
                                    * Require at least WooCommerce 3.0
                                    */
                                    if ( version_compare( $woocommerce->version, '3.0', ">=" ) )
                                        {
                                            add_action( 'woocommerce_after_single_product_summary', array($this, 'custom_wc_upsell_display'), 15 );
                                        }
                                        
                                    add_filter ( 'woocommerce_upsells_orderby', array ( $this, 'woocommerce_upsells_orderby' ) );
                                    add_filter ( 'woocommerce_upsells_order',   array ( $this, 'woocommerce_upsells_order' ) );

                                }
                        }
                        
                      
                    
                }
            
            
            /**
            * WooCommerce Up-sells sort
            *       
            */
            function woocommerce_upsells_sort()
                {
                    
                    $screen = get_current_screen();
                    if($screen->post_type  !=  'product'   ||  $screen->base    !=  'post')
                        return;
                    
                    add_action('admin_footer', array($this, 'upsells_sort_admin_footer'));
                    
                }
            
            /**
            * load the dependencies
            * 
            */
            function upsells_sort_admin_footer()
                {
          
                    ?>
                        <style>
                            .select2-container.parent_upsell_ids .select2-selection__choice {cursor: move}
                            .select2-container.parent_upsell_ids .ui-state-highlight {float: left;min-width: 100px; padding: 2px 6px;margin-right: 5px; margin-top: 5px;}
                        </style>
                        <script type="text/javascript">
                        (function($){
                            $.fn.extend({
                                select2_sortable: function(){
                                    var select = $(this);
                               
                                    var ul = $(select).next('.select2-container').addClass('parent_upsell_ids').first('ul.select2-selection__rendered');
                                    ul.sortable({
                                        placeholder : 'ui-state-highlight', 
                                        forcePlaceholderSize: true,
                                        items       : 'li.select2-selection__choice',
                                        tolerance   : 'pointer',
                                        stop: function() {
                                            $($(ul).find('.select2-selection__choice').get().reverse()).each(function() {
                                                var id = $(this).data('data').id;
                                                var option = select.find('option[value="' + id + '"]')[0];
                                                $(select).prepend(option);
                                            });
                                        }
                                    });
                                    jQuery('select#upsell_ids').closest('p.form-field').find('.select2-selection__rendered').on('click', 'span.select2-selection__choice__remove', function() {
                                         el_itme_remove_click( jQuery(this) )
                                    })
                                }
                            });
                        }(jQuery));
                        
                        jQuery(document).ready(function(){
                            jQuery('#upsell_ids').each(function(){
                                jQuery(this).select2_sortable();
                                jQuery(this).off( "change");

                            })
                        });
                        
                        function el_itme_remove_click( element )
                            {
                                var parent_el  =   jQuery( element ).closest('li');
                                var parent_el_index =  jQuery( parent_el ).closest('ul.select2-selection__rendered').find(' > li').index( jQuery( parent_el ) );
                                jQuery( parent_el ).remove();
                                jQuery('#upsell_ids option').eq(parent_el_index ).remove();    
                                
                            }
                        
                        </script>
                    <?php   
                    
                }
                
                
            function custom_wc_upsell_display() 
                {
                    global $product;
                
                    $upsells    =   array_map( 'wc_get_product', $product->get_upsell_ids() );
                    
                    $upsells    =   array_filter( $upsells );
                    
                    wc_get_template( 'single-product/up-sells.php', array(
                                                                                    'upsells'               =>  $upsells,
                                                                                    'posts_per_page'        => -1,
                                                                                    'orderby'               => 'post__in',
                                                                                    'columns'               => 3
                                                                                    ));
                    
                }
                
            
            function woocommerce_upsells_orderby( $orderby )
                {
                    $orderby    =   'none';
                        
                    return $orderby;   
                }
            
            function woocommerce_upsells_order( $order )
                {
                    $order    =   'none';
                        
                    return $order;   
                }
                       
        }
        
        
    new APTO_woocommerce();

?>