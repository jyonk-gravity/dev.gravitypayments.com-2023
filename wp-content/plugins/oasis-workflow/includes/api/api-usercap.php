<?php

add_action('rest_api_init', function () {

   $ow_custom_capabilities = new OW_Custom_Capabilities();

   // Register route to get all workflow settings
   register_rest_route( 'oasis-workflow/v1', '/usercap/', array(
         'methods' => 'GET',
         'callback' => array( $ow_custom_capabilities, 'api_check_user_capabilities' ),
         'permission_callback' => '__return_true'
      )
   );
});