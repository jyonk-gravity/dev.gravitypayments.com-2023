<?php

namespace WPDRMS\ASP\Options\Routes;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPDRMS\ASP\Rest\AbstractRest;

class UsersRoute extends AbstractRest {
	public function registerRoutes(): void {
		register_rest_route(
			ASP_DIR,
			'options/users/get',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getUsers',
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
	 * @return WP_REST_Response|WP_Error
	 */
	public function getUsers( WP_REST_Request $request ) {
		try {
			$user_ids = $request->get_param('user_ids');
			$search   = $request->get_param('search');
			$args     = array();
			if ( !is_null($user_ids) && is_array($user_ids) ) {
				$args['include'] = $user_ids;
			}
			if ( !is_null($search) ) {
				$args['search']         = "*$search*";
				$args['search_columns'] = array(
					'user_login',
					'display_name',
					'user_nicename',
					'user_email',
					'ID',
				);
				$args['number']         = $request->get_param('number') ?? 100;
			}
			$users = get_users($args);
			if ( $users instanceof WP_Error ) {
				return $users;
			}
			return new WP_REST_Response(
				$users,
				is_wp_error($users) ? 500 : 200
			);
		} catch ( \Exception $e ) {
			return new WP_Error('users_get', $e->getMessage());
		}
	}
}
