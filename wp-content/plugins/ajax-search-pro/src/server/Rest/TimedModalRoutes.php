<?php

namespace WPDRMS\ASP\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPDRMS\ASP\Modal\Factories\ModalFactory;
use WPDRMS\ASP\Modal\Services\TimedModalService;

class TimedModalRoutes extends AbstractRest {
	public function registerRoutes(): void {
		register_rest_route(
			ASP_DIR,
			'timed_modal/get',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getTimedModal',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function getTimedModal( WP_REST_Request $request ): WP_REST_Response {
		try {
			return new WP_REST_Response(
				TimedModalService::instance(new ModalFactory())->get(),
				200
			);
		} catch ( \Exception $e ) {
			return new WP_REST_Response(
				new WP_Error('save_options', $e->getMessage()),
				400
			);
		}
	}
}
