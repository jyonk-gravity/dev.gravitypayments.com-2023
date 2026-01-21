jQuery(document).ready(function () {

   jQuery('.hide-rating').click( function () {
      var data = { 
           action: 'hide_rating',
           security: jQuery( '#owf_rating_ajax_nonce' ).val()
      };
      
      jQuery.post( ajaxurl, data, function ( response ) {
         if ( response.success ) {
            jQuery( '.owf-rating' ).slideUp( 'slow' );
         }
      });
      
   });
   
   jQuery('.set-rating-interval').click( function () {
      var data = { 
          action: 'set_rating_interval',
          security: jQuery( '#owf_rating_ajax_nonce' ).val()
      };
      
      jQuery.post( ajaxurl, data, function ( response ) {
         if ( response.success ) {
            jQuery( '.owf-rating' ).slideUp( 'slow' );
         }
      });
      
   });

});