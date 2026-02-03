<?php

namespace WPMailSMTP\Pro\Emails\Logs\Webhooks\Providers\Mailjet\Events;

use WPMailSMTP\Pro\Emails\Logs\Webhooks\Events\Failed as FailedBase;

/**
 * Class Failed.
 *
 * @since 4.2.0
 */
class Failed extends FailedBase {

	/**
	 * Get error message from event data.
	 *
	 * @since 4.2.0
	 *
	 * @param array $data Event data.
	 *
	 * @return string
	 */
	protected function get_error_message( $data ) {

		if ( ! empty( $data['comment'] ) ) {
			return $data['comment'];
		}

		if ( ! empty( $data['error'] ) ) {
			return $data['error'];
		}

		return parent::get_error_message( $data );
	}
}
