<?php

namespace WPMailSMTP\Pro\Emails\Logs\Webhooks\Providers\Mandrill\Events;

use WPMailSMTP\Pro\Emails\Logs\Webhooks\Events\Failed as FailedBase;

/**
 * Class Failed.
 *
 * @since 4.6.0
 */
class Failed extends FailedBase {

	/**
	 * Get error message from event data.
	 *
	 * @since 4.6.0
	 *
	 * @param array $data Event data.
	 *
	 * @return string
	 */
	protected function get_error_message( $data ) {

		$reason = '';

		if ( ! empty( $data['msg']['reject']['reason'] ) ) {
			$reason = $data['msg']['reject']['reason'];
		} elseif ( ! empty( $data['msg']['bounce_description'] ) ) {
			$reason = $data['msg']['bounce_description'];

			if ( ! empty( $data['msg']['diag'] ) ) {
				$reason .= ' - ' . $data['msg']['diag'];
			}
		}

		if ( ! empty( $reason ) ) {
			/* translators: %s - The reason the email was rejected. */
			return sprintf( esc_html__( 'The email failed to be delivered. Reason: %s', 'wp-mail-smtp-pro' ), $reason );
		}

		return parent::get_error_message( $data );
	}
}
