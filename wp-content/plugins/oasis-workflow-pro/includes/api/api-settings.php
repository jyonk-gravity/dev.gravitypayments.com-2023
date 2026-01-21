<?php

add_action(
	'rest_api_init', function () {

	$ow_tools_service = new OW_Tools_Service();

	// Register route to get all workflow settings
	register_rest_route(
		'oasis-workflow/v1', '/settings', array(
		'methods'             => 'GET',
		'callback'            => array( $ow_tools_service, 'api_get_plugin_settings' ),
		'permission_callback' => function () {
			return OW_Utility::instance()->can_use_workflows();
		}
	) );

	// Register route to check if editorial comment add-on is active
	register_rest_route(
		'oasis-workflow/v1', '/editorialComments', array(
		'methods'             => 'GET',
		'callback'            => array( $ow_tools_service, 'api_check_is_active_editorial_comments' ),
		'permission_callback' => function () {
			return OW_Utility::instance()->can_use_workflows();
		}
	) );

}
);