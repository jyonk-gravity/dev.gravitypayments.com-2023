<?php

namespace WPMailSMTP\Pro\Emails\Logs\Webhooks\Providers\Resend\Events;

use WPMailSMTP\Pro\Emails\Logs\Webhooks\Events\Failed as FailedBase;

/**
 * Class Failed.
 *
 * @since 4.7.0
 */
class Failed extends FailedBase {

	/**
	 * Get error message from event data.
	 *
	 * @since 4.7.0
	 *
	 * @param array $data Event data.
	 *
	 * @return string
	 */
	protected function get_error_message( $data ) {

		if ( ! empty( $data['failed'] ) && ! empty( $data['failed']['reason'] ) ) {
			return $data['failed']['reason'];
		} elseif ( ! empty( $data['bounce'] ) && ! empty( $data['bounce']['message'] ) ) {
			return $data['bounce']['message'];
		}

		return parent::get_error_message( $data );
	}
}
