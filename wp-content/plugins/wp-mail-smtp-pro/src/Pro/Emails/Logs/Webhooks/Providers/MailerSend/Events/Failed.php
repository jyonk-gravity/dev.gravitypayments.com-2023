<?php

namespace WPMailSMTP\Pro\Emails\Logs\Webhooks\Providers\MailerSend\Events;

use WPMailSMTP\Pro\Emails\Logs\Webhooks\Events\Failed as FailedBase;

/**
 * Class Failed.
 *
 * @since 4.5.0
 */
class Failed extends FailedBase {

	/**
	 * Get error message from event data.
	 *
	 * @since 4.5.0
	 *
	 * @param array $data Event data.
	 *
	 * @return string
	 */
	protected function get_error_message( $data ) {

		$text = '';

		if ( ! empty( $data['morph'] ) && ! empty( $data['morph']['reason'] ) ) {
			$text = $data['morph']['reason'];
		}

		return ! empty( $text ) ? $text : parent::get_error_message( $data );
	}
}
