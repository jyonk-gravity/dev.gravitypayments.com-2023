<?php

namespace WPMailSMTP\Pro\Emails\Logs\Webhooks\Providers\SMTP2GO\Events;

use WPMailSMTP\Pro\Emails\Logs\Webhooks\Events\Failed as FailedBase;

/**
 * Class Failed.
 *
 * @since 4.1.0
 */
class Failed extends FailedBase {

	/**
	 * Get error message from event data.
	 *
	 * @since 4.1.0
	 *
	 * @param array $data Event data.
	 *
	 * @return string
	 */
	protected function get_error_message( $data ) {

		return ! empty( $data ) ? $data : parent::get_error_message( $data );
	}
}
