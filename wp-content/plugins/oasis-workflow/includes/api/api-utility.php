<?php

add_action( 'rest_api_init', function () {
	$ow_utility = OW_Utility::instance();

	// Register Route to fetch priorities
	register_rest_route( 'oasis-workflow/v1', '/priorities/', array(
		'methods'             => 'GET',
		'callback'            => array( $ow_utility, 'api_get_priorities' ),
		'permission_callback' => function () {
			return OW_Utility::instance()->can_use_workflows();
		}
	) );

} );
