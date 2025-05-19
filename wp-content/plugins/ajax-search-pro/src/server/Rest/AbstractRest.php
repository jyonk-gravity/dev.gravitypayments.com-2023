<?php

namespace WPDRMS\ASP\Rest;

use WP_Error;
use WPDRMS\ASP\Patterns\SingletonTrait;

/**
 * Options Rest service
 *
 * NONCE verification is NOT needed, as authentication is done via the X-WP-Nonce header automatically.
 * It is sufficient to check the user status to properly authenticate in the permission callback.
 */
abstract class AbstractRest implements RestInterface {
	use SingletonTrait;

	/**
	 * A permission callback to restrict rest request to logged in users only
	 *
	 * @return true|WP_Error
	 */
	public function allowOnlyLoggedIn() {
		if ( !is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'Only logged in users can access this resource.' ), array( 'status' => 401 ) );
		}
		return true;
	}

	/**
	 * A permission callback to restrict rest request to administrator users only
	 *
	 * @return true|WP_Error
	 */
	public function allowOnlyAdmins() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'Only administrators can access this resource.' ), array( 'status' => 401 ) );
		}
		return true;
	}
}
