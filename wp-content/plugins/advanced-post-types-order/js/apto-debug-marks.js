


    jQuery(document).ready(function() {
        // Tooltip only Text
        jQuery('.apto_debug_block').hover(function(){
            
                if ( jQuery('.apto_debug_block_tooltip').hasClass('force-visibility'))
                    return;
                
                // Hover over code
                var el_index    =   jQuery(this).attr('data-id');
                var description =   jQuery('#apto_debug_block_info div[data-id="' + el_index  +'"]').html();
                jQuery(this).data('tipText', description).removeAttr('title');
                jQuery('<pre class="apto_debug_block_tooltip"></pre>')
                .appendTo('body')
                .fadeIn('slow');
                
                jQuery('.apto_debug_block_tooltip').html( description )
        }, function() {
                // Hover out code
                if ( jQuery('.apto_debug_block_tooltip').hasClass('force-visibility'))
                    return; 
                    
                jQuery(this).attr('title', jQuery(this).data('tipText'));
                jQuery('.apto_debug_block_tooltip').remove();
                
        }).mousemove(function(e) {
            
            if ( typeof jQuery('.apto_debug_block_tooltip').attr('data-positioning') !== "undefined" ) 
                return;
                 
            var mousex = e.pageX; //Get X coordinates
                var mousey = e.pageY; //Get Y coordinates
                jQuery('.apto_debug_block_tooltip')
                .css({ top: mousey, left: mousex })
                
            jQuery('.apto_debug_block_tooltip').attr('data-positioning', 'true')
        });
        
        jQuery('.apto_debug_block').on('click', function() {
            jQuery(this).toggleClass('force-visibility');
            jQuery('.apto_debug_block_tooltip').toggleClass('force-visibility');
               
        })    
    
    });