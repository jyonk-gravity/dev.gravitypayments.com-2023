jQuery( document ).ready( function () {
   if ( exit_wfid ) {
      jQuery( "#publishing-action" ).append( "<div class='abort-workflow-section right'><a href='#' id='exit_link'>" + owf_abort_workflow_vars.abortWorkflow + "</a><span class='blank-space loading owf-hidden'></span></div>" );
      jQuery( '.error' ).hide();

   }
   jQuery( document ).on("click", "#exit_link", function( e ){
      e.preventDefault();
      
      modal_data = {
         action: 'workflow_abort_comments',
         security: jQuery('#owf_exit_post_from_workflow').val(),
         command: 'exit_from_workflow'
      }
      
      jQuery.post(ajaxurl, modal_data, function (response) {
         if (response == -1) {
            return false; // Invalid nonce
         }
         
         if ( response.success ) {
            var content = html_decode(response.data);
            jQuery( content ).owfmodal();
            jQuery("#simplemodal-container").css({ "top":"130px" });
            
            jQuery("#abortSave").click(function () {
               var comments = jQuery('#abortComments').val();
               data = {
                  action: 'workflow_abort' ,
                  history_id: exit_wfid,
                  comment: comments,
                  security: jQuery('#owf_exit_post_from_workflow').val(),
                  command: 'exit_from_workflow'
               };
               jQuery(this).hide();
               jQuery( this ).parent().children( ".loading" ).show();
               jQuery.post(ajaxurl, data, function( response ) {
                  if ( response == -1 ) { // incorrect nonce
                     return false;
                  }
                  
                  if ( response.success ) {
                     jQuery( this ).parent().children( ".loading" ).hide();
                     location.reload();
                  }
               });
            });
         }
      });
	})
    
   jQuery( document ).on( "click", "#abortCancel", function () {
      jQuery.modal.close();
   } );
   
   
} );