jQuery( document ).ready( function () {
   var deactivateLink = "";
   
   jQuery('#the-list').on('click', 'a.owf-deactivate-link', function (e) {
      e.preventDefault();
      deactivateLink = jQuery(this).attr('href');
      jQuery("#owf-deactivate").attr('href', deactivateLink);
      jQuery( "#owf_deactivate_feedback" ).owfmodal( {
         onShow: function ( dlg ) {
            jQuery("#simplemodal-container").css({
               "width": "523px",
               "max-height": "90%",
               "top":"60px"
            });
            jQuery( dlg.wrap ).css( 'overflow', 'auto' );
         }
      } );
   });

   jQuery( '#owf-feedback-contents input' ).on('change', function(){
        jQuery('.owf-reason-required').removeClass('visible');
   });

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }
   
   jQuery( '.ow-feedback-email' ).on('paste keyup', function(){
        var val = jQuery(this).val();
        if( isEmail( val )) {
            jQuery(".ow-feedback-email").removeClass('error');
        }
   });

   jQuery( document ).on( "click", "#owf-feedback-save", function () {
            
      var selectedFeedback = jQuery(".selected-reason:checked").val();
      var feedbackThoughts = jQuery(".feedback-thoughts").val();
      var feedbackEmail = jQuery(".ow-feedback-email").val();

      if( typeof selectedFeedback == 'undefined' ) {
          jQuery('.owf-reason-required').addClass('visible');
          return false;
      }

      if( ! isEmail( feedbackEmail ) ) {
          jQuery(".ow-feedback-email").addClass('error');
          return false;
      }
      
      var submit_feedback_data = {
         action:     'submit_deactivation_feedback',
         feedback:   selectedFeedback,
         thoughts:   feedbackThoughts,
         email:      feedbackEmail,
         security:   jQuery("#owf_feedback_ajax_nonce").val()
      };

      jQuery( ".btn-submit-feedback-group span" ).addClass( "loading" );
      jQuery( "#owf-feedback-save" ).hide();
      
      jQuery.post(ajaxurl, submit_feedback_data, function (response) {
         if (response == -1) {
            return false; // Invalid nonce
         }

         window.location.href = deactivateLink;         
      });
      
   });
   
});