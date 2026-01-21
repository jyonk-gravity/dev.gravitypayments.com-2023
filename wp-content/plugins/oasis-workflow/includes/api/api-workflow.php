<?php

add_action('rest_api_init', function () {
   $ow_workflow_service = new OW_Workflow_Service();
   $ow_process_flow = new OW_Process_Flow();
   
   // Register route to get worklfow list that are valid
   register_rest_route('oasis-workflow/v1', '/workflows/postId=(?P<post_id>\d+)', array(
      'methods' => 'GET',
      'callback' => array( $ow_workflow_service, 'api_get_valid_workflows' ),
      'permission_callback' => function () {
	      return OW_Utility::instance()->can_use_workflows();
      }
   ) );
   
   // Register Route to fetch step details
   register_rest_route('oasis-workflow/v1', '/workflows/submit/firstStep/workflowId=(?P<wf_id>\d+)/postId=(?P<post_id>\d+)', array(
      'methods' => 'GET',
      'callback' => array( $ow_process_flow, 'api_get_first_step_details' ),
      'permission_callback' => function () {
         return OW_Utility::instance()->can_use_workflows();
      }
   ));

   // Register route to check is role is applicable to submit to workflow
   register_rest_route('oasis-workflow/v1', '/workflows/submit/checkRoleCapability/postId=(?P<post_id>\d+)/postType=(?P<post_type>[a-zA-Z0-9-_]+)', array(
      'methods' => 'GET',
      'callback' => array( $ow_process_flow, 'api_check_is_role_applicable' ),
      'permission_callback' => function () {
         return OW_Utility::instance()->can_use_workflows();
      }
   ) );
        
   // Register Route to save workflow submit data
   register_rest_route('oasis-workflow/v1', '/workflows/submit/', array(
      'methods' => 'POST',
      'callback' => array( $ow_process_flow, 'api_submit_to_workflow' ),
      'permission_callback' => function () {
         return current_user_can( 'ow_submit_to_workflow' );
      }
   ));

   // Register Route to abort a workflow
   register_rest_route('oasis-workflow/v1', '/workflows/abort/', array(
      'methods' => 'POST',
      'callback' => array( $ow_process_flow, 'api_workflow_abort' ),
      'permission_callback' => function () {
         return current_user_can( 'ow_abort_workflow' );
      }
   ));
   
   // Register Route to fetch step actions
   register_rest_route('oasis-workflow/v1', '/workflows/signoff/stepActions/actionHistoryId=(?P<action_history_id>\d+)', array(
      'methods' => 'GET',
      'callback' => array( $ow_workflow_service, 'api_get_step_action_details' ),
      'permission_callback' => function () {
         return current_user_can( 'ow_sign_off_step' );
      }
   ));
   
   // Register Route to fetch signoff next steps
   register_rest_route('oasis-workflow/v1', '/workflows/signoff/nextSteps/actionHistoryId=(?P<action_history_id>\d+)/decision=(?P<decision>[a-zA-Z0-9-]+)/postId=(?P<post_id>\d+)', array(
      'methods' => 'GET',
      'callback' => array( $ow_process_flow, 'api_get_signoff_next_steps' ),
      'permission_callback' => function () {
         return OW_Utility::instance()->can_use_workflows();
      }
   ));
   
   // Register Route to fetch step details
   register_rest_route('oasis-workflow/v1', '/workflows/signoff/stepDetails/actionHistoryId=(?P<action_history_id>\d+)/stepId=(?P<step_id>\d+)/postId=(?P<post_id>\d+)', array(
      'methods' => 'GET',
      'callback' => array( $ow_process_flow, 'api_get_step_details' ),
      'permission_callback' => function () {
         return OW_Utility::instance()->can_use_workflows();
      }
   ));
   
   // Register Route to save workflow signoff submit data
   register_rest_route('oasis-workflow/v1', '/workflows/signoff/', array(
      'methods' => 'POST',
      'callback' => array( $ow_process_flow, 'api_submit_to_step' ),
      'permission_callback' => function () {
         return current_user_can( 'ow_sign_off_step' );
      }
   ));

   // Register Route for workflow complete
   register_rest_route('oasis-workflow/v1', '/workflows/signoff/workflowComplete/', array(
      'methods' => 'POST',
      'callback' => array( $ow_process_flow, 'api_workflow_complete' ),
      'permission_callback' => function () {
         return current_user_can( 'ow_sign_off_step' );
      }
   ));

   // Register Route for workflow cancel
   register_rest_route('oasis-workflow/v1', '/workflows/signoff/workflowCancel/', array(
      'methods' => 'POST',
      'callback' => array( $ow_process_flow, 'api_workflow_cancel' ),
      'permission_callback' => function () {
         return current_user_can( 'ow_abort_workflow' );
      }
   ));
   
   // Register route to check for claim
   register_rest_route('oasis-workflow/v1', '/workflows/claim/actionHistoryId=(?P<action_history_id>\d+)', array(
      'methods' => 'GET',
      'callback' => array( $ow_process_flow, 'api_check_for_claim' ),
      'permission_callback' => function () {
         return current_user_can( 'ow_sign_off_step' );
      }
   ) );
   
   // Register Route to claim the task
   register_rest_route('oasis-workflow/v1', '/workflows/claim/', array(
      'methods' => 'POST',
      'callback' => array( $ow_process_flow, 'api_claim_process' ),
      'permission_callback' => function () {
         return current_user_can( 'ow_sign_off_step' );
      }
   ));
});