<?php
/*
 * Oasis Workflow mail class
 *
 * @copyright   Copyright (c) 2015, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class OW_Email
 */
class OW_Email {
	
	/**
	 * Set things up.
	 *
	 * @since 2.0
	 */
	public function __construct() {
		// post published notification hook
		add_action( 'transition_post_status', array( $this, 'post_published_notification' ), 10, 3 );

		// scheduled - send reminder email
		add_action( 'oasiswf_email_schedule', array( $this, 'send_reminder_email' ) );

		// Schedule the bluck assignment emails for a given user
		add_action( 'oasiswf_email_digest_schedule', array( $this, 'send_assignment_digest_email' ) );
	}


	/**
	 * Hook - Send post published notification to the author, if configured "yes"
	 *
	 * @param string $new_status current status of the post
	 * @param string $old_status old status of the post
	 * @param mixed  $post       the post object
	 *
	 * @since 2.0
	 */
	public function post_published_notification( $new_status, $old_status, $post ) {

		// Send email when post is published via workflow, do not send email when post is directly published OR published post is updated
		$ow_email_settings_helper    = new OW_Email_Settings_Helper();
		$post_publish_email_settings = get_option( 'oasiswf_post_publish_email_settings' );
		$oasis_is_in_workflow        = get_post_meta( $post->ID, '_oasis_is_in_workflow', true );

		if ( $new_status == 'publish' &&
		     $old_status != 'publish' &&
		     $post_publish_email_settings && $post_publish_email_settings['is_active'] == "yes" &&
		     $oasis_is_in_workflow == 1 ) {

			// Fetch all mail parameters
			$email_assignees = isset( $post_publish_email_settings['email_assignees'] )
				? $post_publish_email_settings['email_assignees'] : '';
			$email_cc        = isset( $post_publish_email_settings['email_cc'] )
				? $post_publish_email_settings['email_cc'] : '';
			$email_bcc       = isset( $post_publish_email_settings['email_bcc'] )
				? $post_publish_email_settings['email_bcc'] : '';
			$subject         = isset( $post_publish_email_settings['subject'] )
				? $post_publish_email_settings['subject'] : $ow_email_settings_helper->get_post_publish_subject();
			$content         = isset( $post_publish_email_settings['content'] )
				? $post_publish_email_settings['content'] : $ow_email_settings_helper->get_post_publish_content();

			$email_recipient_params = array(
				"post_id"         => $post->ID,
				"action"          => OW_Email_Settings_Helper::POST_PUBLISH_EMAIL,
				"email_assignees" => $email_assignees,
				"email_cc"        => $email_cc,
				"email_bcc"       => $email_bcc
			);

			// Get email recipients - all for to, cc and bcc
			$email_recipients = $ow_email_settings_helper->get_email_recipients( $email_recipient_params );

			$to_email_recipients  = $email_recipients["email_assignees"];
			$cc_email_recipients  = $email_recipients["email_cc"];
			$bcc_email_recipients = $email_recipients["email_bcc"];

			foreach ( $to_email_recipients as $key => $user_id ) {
				$email_params = array(
					"post_id"       => $post->ID,
					"email_to"      => $user_id,
					"email_subject" => $subject,
					"email_content" => $content
				);

				// Replace placeholders in mail subject and mail content
				$mail          = $ow_email_settings_helper->get_email_content( $email_params );
				$final_subject = $mail['subject'];
				$final_message = $mail['message'];

				if ( $key == 0 ) {
					// send Cc and Bcc recipients with first "To" email recipients
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message, "",
						$cc_email_recipients, $bcc_email_recipients );
				} else {
					// now send email
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message );
				}
			}
		}

		// since the post is now published, lets call clean up.
		if ( $new_status == 'publish' &&
		     $old_status != 'publish' &&
		     $oasis_is_in_workflow == 1 ) {

			$ow_process_flow = new OW_Process_Flow();
			$ow_process_flow->cleanup_after_workflow_complete( $post->ID );
		}
	}

	/**
	 * Send email using Oasis Workflow email settings
	 *
	 * @param int|string $to_user user_id or email address of the user to whom the email is addressed
	 * @param string     $subject subject of the email
	 * @param string     $message message of the email
	 *
	 * @since 2.0
	 */
	public function send_mail(
		$to_user, $subject, $message, $attachments = '', $cc_users = array(), $bcc_users = array()
	) {
		// Sanitize incoming data
		$to_user = sanitize_text_field( $to_user );
		$subject = sanitize_text_field( $subject );

		if ( ! empty( $cc_users ) ) {
			$cc_users = array_map( 'esc_attr', $cc_users );
		}

		if ( ! empty( $bcc_users ) ) {
			$bcc_users = array_map( 'esc_attr', $bcc_users );
		}

		//Get user email address
		$user_email = $this->get_user_email_by_id( $to_user );

		// Allow users to disable email sending based on the subject
		$should_send = apply_filters('oasiswf_allow_email_send', true, $subject, $to_user, $message);

		if ( ! $should_send ) {
			OW_Utility::instance()->logger("Email sending disabled for subject: $subject");
			return false; // Skip email sending
		}

		// get the email settings
		$email_settings = get_option( 'oasiswf_email_settings' );
		$from_name      = $email_settings['from_name'];
		if ( empty( $email_settings['from_name'] ) ) {
			$decoded_blog_name = html_entity_decode( get_option( 'blogname' ), ENT_QUOTES, 'UTF-8' );
			$from_name         = $decoded_blog_name;
		}

		$from_email = $email_settings['from_email_address'];
		if ( empty( $email_settings['from_email_address'] ) ) {
			$from_email = get_option( 'admin_email' );
		}
		$headers = array(
			"From: " . $from_name . " <" . $from_email . ">",
			"Reply-to: " . $from_email . "",
			"Content-Type: text/html; charset=UTF-8"
		);

		if ( ! empty( $cc_users ) || ( ! empty( $bcc_users ) ) ) {
			// Generate Cc and Bcc headers
			$header_cc_bcc = $this->generate_cc_bcc_headers( $cc_users, $bcc_users );
			// Merge headers
			$headers = array_merge( $headers, $header_cc_bcc );
		}

        $headers = apply_filters( "oasiswf_email_headers", $headers, $subject );

		$imploded_headers = implode( "\r\n", $headers ) . "\r\n";

		$decoded_title   = html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' );
		$decoded_message = html_entity_decode( $message, ENT_QUOTES, 'UTF-8' );
		$decoded_message = stripslashes( $decoded_message );

		// send mail using wp_email function
		$result = wp_mail( $user_email, $decoded_title, $decoded_message, $imploded_headers, $attachments );

		// wp mail debugging
		if ( ! $result ) {
			global $ts_mail_errors;
			global $phpmailer;

			if ( ! isset( $ts_mail_errors ) ) {
				$ts_mail_errors = array();
			}

			if ( isset( $phpmailer ) ) {
				$ts_mail_errors[] = $phpmailer->ErrorInfo;
			}

			OW_Utility::instance()->logger( $ts_mail_errors );

			return false;
		}

		return true;
	}

	private function get_user_email_by_id( $user_id ) {
		// to_user could be an id of the user or email address
		if ( is_numeric( $user_id ) ) {
			/* External user ID are saved as 5 digit numbers say 12345
			 * preg_match checks if user id is of 5 digit number then get
			 * the details of the external users.
			 */
			if ( 1 === preg_match( "/^\d{5}$/", $user_id ) ) {
				$get_external_user_details = get_option( "oasiswf_external_user_settings" );
				$user_detail               = isset( $get_external_user_details[ $user_id ] )
					? $get_external_user_details[ $user_id ] : array();
				$user_email                = ( ! empty( $user_detail ) ) ? $user_detail["email"] : "";

				//check default user table if email is empty
				if ( empty( $user_email ) ) {
					$user       = get_userdata( $user_id );
					$user_email = $user->data->user_email;
				}
			} else {
				$user       = get_userdata( $user_id );
				$user_email = $user->data->user_email;
			}
		} else {
			$user = get_user_by( 'email', $user_id );
			if ( $user ):
				$user_email = $user->data->user_email;
			endif;
		}

		return $user_email;
	}

	/**
	 * Generate headers for Cc and Bcc email recipients
	 *
	 * @param array $cc_users
	 * @param array $bcc_users
	 *
	 * @return array $header
	 * @since 7.2
	 */
	private function generate_cc_bcc_headers( $cc_users, $bcc_users ) {
		$header = array();

		if ( $cc_users ) {
			foreach ( $cc_users as $cc_user ) {
				$user_email = is_email( $cc_user ) ? sanitize_text_field( $cc_user ) : $this->get_user_email_by_id( $cc_user );
				$header[]   = "Cc: " . $user_email;
			}
		}

		if ( $bcc_users ) {
			foreach ( $bcc_users as $bcc_user ) {
				$user_email = is_email( $bcc_user ) ? sanitize_text_field( $bcc_user ) : $this->get_user_email_by_id( $bcc_user );
				$header[]   = "Bcc: " . $user_email;
			}
		}

		return $header;
	}

	/**
	 * Hook - Send post publish notification for revised post , if configured "yes"
	 *
	 * @param int   $original_post_id
	 * @param mixed $revised_post the post object
	 *
	 * @since 2.0
	 */
	public function revised_post_published_notification( $original_post_id, $revised_post ) {
		/* sanitize incoming data */
		$original_post_id = intval( $original_post_id );

		$ow_email_settings_helper            = new OW_Email_Settings_Helper();
		$revised_post_publish_email_settings = get_option( 'oasiswf_revised_post_email_settings' );
		if ( $revised_post_publish_email_settings && $revised_post_publish_email_settings['is_active'] == "yes" ) {
			// Fetch all mail parameters
			$email_assignees = isset( $revised_post_publish_email_settings['email_assignees'] )
				? $revised_post_publish_email_settings['email_assignees'] : '';
			$email_cc        = isset( $revised_post_publish_email_settings['email_cc'] )
				? $revised_post_publish_email_settings['email_cc'] : '';
			$email_bcc       = isset( $revised_post_publish_email_settings['email_bcc'] )
				? $revised_post_publish_email_settings['email_bcc'] : '';
			$subject         = isset( $revised_post_publish_email_settings['subject'] )
				? $revised_post_publish_email_settings['subject']
				: $ow_email_settings_helper->get_revised_post_publish_subject();
			$content         = isset( $revised_post_publish_email_settings['content'] )
				? $revised_post_publish_email_settings['content']
				: $ow_email_settings_helper->get_revised_post_publish_content();

			$email_recipient_params = array(
				"post_id"         => $revised_post->ID,
				"action"          => OW_Email_Settings_Helper::REVISED_POST_PUBLISH_EMAIL,
				"email_assignees" => $email_assignees,
				"email_cc"        => $email_cc,
				"email_bcc"       => $email_bcc
			);

			// Get email recipients - all for to, cc and bcc
			$email_recipients = $ow_email_settings_helper->get_email_recipients( $email_recipient_params );

			$to_email_recipients  = $email_recipients["email_assignees"];
			$cc_email_recipients  = $email_recipients["email_cc"];
			$bcc_email_recipients = $email_recipients["email_bcc"];

			foreach ( $to_email_recipients as $key => $user_id ) {
				$email_params = array(
					"post_id"       => $original_post_id,
					"email_to"      => $user_id,
					"email_subject" => $subject,
					"email_content" => $content
				);

				// Replace placeholders in mail subject and mail content
				$mail          = $ow_email_settings_helper->get_email_content( $email_params );
				$final_subject = $mail['subject'];
				$final_message = $mail['message'];

				if ( $key == 0 ) {
					// send Cc and Bcc recipients with first "To" email recipients
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message, "",
						$cc_email_recipients, $bcc_email_recipients );
				} else {
					// now send email
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message );
				}
			}
		}
	}

	/**
	 * Hook - Send reminder email
	 *
	 * @since 2.0
	 */

	public function send_reminder_email() {
		global $wpdb;

		$email_settings = get_option( 'oasiswf_email_settings' );
		if ( $email_settings['reminder_emails'] == "yes" ) {
			$ddate = gmdate( 'Y-m-d' );
			// If email digest is set than call send_digest_email()
			if ( $email_settings['digest_emails'] == "yes" ) {
				$email_type = 1; // for emails which are not sent yet
				$this->send_digest_email( $email_type );
			} else {
				$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->fc_emails .
				                                            " WHERE action = 1 AND send_date = %s", $ddate ) );

				foreach ( $rows as $row ) {
					$cc_users = isset( $row->cc_users ) ? maybe_unserialize( $row->cc_users ) : [];
					$bcc_users = isset( $row->bcc_users ) ? maybe_unserialize( $row->bcc_users ) : [];
					$this->send_mail( $row->to_user, $row->subject, $row->message, '', $cc_users, $bcc_users );
					$wpdb->update( $wpdb->fc_emails, array( "action" => 0 ), array( "ID" => $row->ID ) );
				}
			}
		}
	}

	/**
	 * Sends digest emails of assignment and reminder according to the cron schedule event
	 *
	 * Loop through all the emails which are not sent yet,
	 * Combine them into one email per user (digest email)
	 * Send mail
	 * Update action to 0 after mail is send.
	 *
	 * @param $email_type
	 *
	 * @since 4.6
	 */
	public function send_digest_email( $email_type ) {
		global $wpdb;

		// Sanitize incoming data
		$email_type = intval( $email_type );

		// get the current date
		$send_date = gmdate( 'Y-m-d' );

		$emails_table = OW_Utility::instance()->get_emails_table_name();

		// fetch all unsend assignments according to email type
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT to_user, subject, message FROM " . $wpdb->fc_emails .
		                                            " WHERE action = %d AND send_date = %s ORDER BY to_user",
			$email_type, $send_date ) );

		// set parameters for looping
		$previous_user   = '';
		$numItems        = count( $rows );
		$i               = 0;
		$combine_message = array();
		foreach ( $rows as $row ) {
			$current_user = $row->to_user;

			$cc_users = isset( $row->cc_users ) ? maybe_unserialize( $row->cc_users ) : [];
			$bcc_users = isset( $row->bcc_users ) ? maybe_unserialize( $row->bcc_users ) : [];

			/*
			 * 1. On start of loop $previous_user would be empty
			 * 2. if current and previous user are same
			 * for both condition combine messages into one array for particular user
			 */
			if ( $current_user == $previous_user || $previous_user == '' ) {
				$combine_message[] = $row->message;
			}

			// increment the count to check end of loop
			$i ++;

			/*
			 * 1. If current and previous user are not same
			 * 2. End of loop but current and previous users are same
			 */
			if ( ( $previous_user !== '' && $current_user !== $previous_user ) ||
			     ( $i === $numItems && $current_user == $previous_user ) ) {

				// put email subject for assignment/reminder mails
				if ( count( $combine_message ) > 1 ) {
					// if more than one assignment
					$subject = "You have multiple assignments";
					$message = implode( '<br> ', $combine_message );
				} else {
					// if only one assignment use the email subject of that particular assignment
					$subject = $row->subject;
					$message = implode( '<br> ', $combine_message );
				}

				// send mail
				$this->send_mail( $previous_user, $subject, $message, '', $cc_users, $bcc_users );
				// update the action
				$wpdb->update( $emails_table, array( "action" => 0 ), array(
					"to_user"   => $previous_user,
					"action"    => $email_type,
					"send_date" => $send_date
				) );
				unset( $combine_message );
				/*
				 *  If current and previous user is not same than for current user set the
				 *  loops first message into combine message array.
				 */
				$combine_message[] = $row->message;
			}

			/*
			 * If end of loop, current and preivious user are not same
			 * and only one message to be send for current user in a loop
			 */
			if ( $i === $numItems && count( $combine_message ) == 1 && $current_user !== $previous_user ) {
				$subject = $row->subject;
				$message = implode( '<br> ', $combine_message );
				// send mail
				$this->send_mail( $current_user, $subject, $message, '', $cc_users, $bcc_users );
				// update the action
				$wpdb->update( $emails_table, array( "action" => 0 ), array(
					"to_user"   => $current_user,
					"action"    => $email_type,
					"send_date" => $send_date
				) );
				unset( $combine_message );
			}

			$previous_user = $current_user;
		}
		unset( $previous_user );
	}

	/**
	 * Hook - send bulk assignment emails for a given user
	 *
	 * @since 4.6
	 */
	public function send_assignment_digest_email() {
		$email_settings = get_option( 'oasiswf_email_settings' );
		// If email digest is set than call send_digest_email()
		if ( $email_settings['digest_emails'] == "yes" ) {
			$email_type = 2; // for assignment emails which are not send yet, and are set as digest emails
			$this->send_digest_email( $email_type );
		}
	}

	/**
	 *
	 * Send assignment emails
	 * Setup reminder emails for before and after due date
	 *
	 * @param int $action_id  action_history_id for the given workflow step
	 * @param int $to_user_id user_id in the given step
	 *
	 * @since 2.0
	 */
	public function send_step_email( $action_id, $to_user_id = null ) {
		global $wpdb;

		// sanitize the input
		$action_id  = intval( sanitize_text_field( $action_id ) );
		$to_user_id = intval( sanitize_text_field( $to_user_id ) );

		$current_user_id = get_current_user_id();

		$ow_history_service = new OW_History_Service();
		$ow_email_settings_helper = new OW_Email_Settings_Helper();
		$action_step        = $ow_history_service->get_action_history_by_id( $action_id );
		$to_user_id         = ( $to_user_id ) ? $to_user_id : $action_step->assign_actor_id;
		$mails              = $this->get_step_mail_content( $action_id, $action_step->step_id, $to_user_id,
			$action_step->post_id );
		$comment            = $this->get_step_comment_content( $action_id );

		$post_id   = $action_step->post_id;
		$post_type = get_post_type( $post_id );

		do_action( "owf_generate_additional_attachments", $post_id, $action_id );

		// Get attachments
		// as of now, it's only the PDF files generated using the PDF generator add-on
		$attachments = $this->get_attachments( $post_id );

		$data = array(
			'to_user'         => $to_user_id,
			'history_id'      => $action_id,
			'create_datetime' => current_time( 'mysql' )
		);

		// send email if setting is true
		$email_settings = get_option( 'oasiswf_email_settings' );
		if ( isset( $mails ) &&
		     isset( $mails->assign_subject ) && $mails->assign_subject &&
		     isset( $mails->assign_content ) && $mails->assign_content &&
		     $email_settings['assignment_emails'] == "yes" && ( $to_user_id !== $current_user_id ) ) {

			$mail_content = $mails->assign_content . $comment;
			$mail_content = apply_filters( "oasiswf_custom_email_content", $mail_content, $post_id, $action_id );

			$email_cc = isset( $mails->assign_cc ) ? json_decode(json_encode($mails->assign_cc), true) : [];
			$email_bcc = isset( $mails->assign_bcc ) ? json_decode(json_encode($mails->assign_bcc), true) : [];

			$email_recipient_params = array(
				"post_id"   => $post_id,
				"email_cc" 	=> $email_cc,
				"email_bcc" => $email_bcc
			);

			// Get email recipients - all for to, cc and bcc
			$email_recipients = $ow_email_settings_helper->get_cc_recipients( $email_recipient_params );

			$cc_users  = $email_recipients["email_cc"];
			$bcc_users = $email_recipients["email_bcc"];

            $cc_users = apply_filters( 'ow_assignment_cc_users', $cc_users );
            $bcc_users = apply_filters( 'ow_assignment_bcc_users', $bcc_users );

			// check is enable digest email option is set or not
			if ( $email_settings['digest_emails'] !== "yes" ) {
				// if digest email is not set send the assignment mail and set action to 0
				$result = $this->send_mail( $to_user_id, $mails->assign_subject, $mail_content, $attachments, $cc_users, $bcc_users );

				if( $result ) {
					OW_Utility::instance()->logger( "Send step email: " . $action_id . "-" . $to_user_id );
				} else {
					OW_Utility::instance()->logger( "Failed to send step email: " . $action_id . "-" . $to_user_id );
				}
				$data["action"] = 0;
			} else {
				// add a horizontal line at the end of each message.
				$mail_content .= '<hr/>';
				/* if digest email is enable set action to 2 so that the cron scheduler will
				 * combines all assignment emails generated in the last one hour
				 * into one single message for which the action is set to 2.
				 */
				$data["action"] = 2;
			}

			$data["subject"]   = $mails->assign_subject;
			$data["message"]   = $mail_content;
			$data["cc_users"]   = maybe_serialize( $cc_users );
			$data["bcc_users"]   = maybe_serialize( $bcc_users );
			$data["send_date"] = current_time( 'mysql' );
			
			OW_Utility::instance()->insert_to_table( $wpdb->fc_emails, $data );
		}

		// set reminder email for future delivery
		if ( isset( $mails ) &&
		     isset( $mails->reminder_subject ) && $mails->reminder_subject &&
		     isset( $mails->reminder_content ) && $mails->reminder_content ) {
			$mail_content = $mails->reminder_content . $comment;
			$mail_content = apply_filters( "oasiswf_custom_email_content", $mail_content, $post_id, $action_id );

			$email_cc = isset( $mails->reminder_cc ) ? json_decode(json_encode($mails->reminder_cc), true) : [];
			$email_bcc = isset( $mails->reminder_bcc ) ? json_decode(json_encode($mails->reminder_bcc), true) : [];

			$email_recipient_params = array(
				"post_id"   => $post_id,
				"email_cc" 	=> $email_cc,
				"email_bcc" => $email_bcc
			);

			// Get email recipients - all for to, cc and bcc
			$email_recipients = $ow_email_settings_helper->get_cc_recipients( $email_recipient_params );

			$cc_users  = $email_recipients["email_cc"];
			$bcc_users = $email_recipients["email_bcc"];

            $cc_users = apply_filters( 'ow_reminder_cc_users', $cc_users );
            $bcc_users = apply_filters( 'ow_reminder_bcc_users', $bcc_users );

			$data["subject"] = $mails->reminder_subject;
			$data["message"] = $mail_content;
			$data["cc_users"]   = maybe_serialize( $cc_users );
			$data["bcc_users"]   = maybe_serialize( $bcc_users );
			$data["action"]  = 1;

			// for reminder before date
			if ( $action_step->reminder_date ) {
				$data["send_date"] = $action_step->reminder_date;
				OW_Utility::instance()->insert_to_table( $wpdb->fc_emails, $data );
			}

			// for reminder after date
			if ( $action_step->reminder_date_after ) {
				$data["send_date"] = $action_step->reminder_date_after;
				OW_Utility::instance()->insert_to_table( $wpdb->fc_emails, $data );
			}
		}
	}

	/**
	 * Get mail content from the step configuration
	 * Merge placeholders with actual value
	 *
	 * @param int $action_id  action_history_id for the given workflow step
	 * @param int $step_id    step_id for the given workflow
	 * @param int $to_user_id user_id in the given step
	 * @param int $post_id    post currently in the workflow
	 *
	 * @return mixed step message if step and post exists OR false
	 *
	 * @since 2.0
	 */
	public function get_step_mail_content( $action_id, $step_id, $to_user_id, $post_id ) {
		/* sanitize the input */
		$action_id       = intval( sanitize_text_field( $action_id ) );
		$step_id         = intval( sanitize_text_field( $step_id ) );
		$to_user_id      = intval( sanitize_text_field( $to_user_id ) );
		$post_id         = intval( sanitize_text_field( $post_id ) );
		$current_user_id = get_current_user_id();

		// get step information
		$workflow_service = new OW_Workflow_Service();
		$step             = $workflow_service->get_step_by_id( $step_id );
		/*
		 * Replace the placeholders with actual value
		 */
		$ow_placeholders = new OW_Place_Holders();

		// get post details
		$post      = get_post( $post_id );
		$blog_name = '[' . addslashes( get_bloginfo( 'name' ) ) . '] ';
		if ( $step && $post ) {
			$messages = json_decode( trim( $step->process_info ) );
			if ( ! $messages ) {
				return false;
			}

			$post_link       = '';
			$message_content = trim( $messages->assign_content );

			// replace all the non visible characters with space
			$subject_line = str_replace( array(
				"\\r\\n",
				"\\r",
				"\\n",
				"\\t",
				"<br />",
				' '
			), '', trim( $messages->assign_subject ) );
			$content_line = str_replace( array(
				"\\r\\n",
				"\\r",
				"\\n",
				"\\t",
				"<br />",
				' '
			), '', trim( $message_content ) );

			// if the user didn't provide any comments, use default comments
			if ( empty( $content_line ) ) {
				$post_link = $ow_placeholders->get_post_title( $post_id, $action_id, true );
			}
			
			$messages->assign_subject = ( ! empty( $subject_line ) ) ? $messages->assign_subject
				: $blog_name . esc_html__( "You have an assignment", "oasisworkflow" );
			$messages->assign_content = ( ! empty( $content_line ) ) ? $messages->assign_content
				: esc_html__( "You have an assignment related to post - ", "oasisworkflow" ) . $post_link;

			// replace the placeholders
			$callback_custom_placeholders = array();
			if ( has_filter( 'oasiswf_custom_placeholders_handler' ) ) {
				$callback_custom_placeholders = apply_filters( 'oasiswf_custom_placeholders_handler', $post );
			}

			// placeholder callback with extra parameters
			$callback_custom_placeholders = apply_filters( 'oasiswf_custom_placeholders_list', $callback_custom_placeholders, $action_id, $step_id, $to_user_id, $post_id);

			$excludes = array(
				'assign_cc',
				'assign_bcc',
				'reminder_cc',
				'reminder_bcc'
			);

			foreach ( $messages as $k => $v ) {

				if( in_array( $k, $excludes ) ) {
					continue;
				}

				$v = str_replace( OW_Place_Holders::FIRST_NAME,
					$ow_placeholders->get_first_name( $to_user_id ), $v );
				$v = str_replace( OW_Place_Holders::LAST_NAME,
					$ow_placeholders->get_last_name( $to_user_id ), $v );
				$v = str_replace( OW_Place_Holders::POST_CATEGORY,
					$ow_placeholders->get_post_categories( $post_id ), $v );
				$v = str_replace( OW_Place_Holders::POST_LAST_MODIFIED_DATE,
					$ow_placeholders->get_post_last_modified_date( $post_id ), $v );
				$v = str_replace( OW_Place_Holders::POST_PUBLISH_DATE,
					$ow_placeholders->get_post_publish_date( $post_id ), $v );
				$v = str_replace( OW_Place_Holders::POST_AUTHOR,
					$ow_placeholders->get_author_display_name( $post_id ), $v );
				$v = str_replace( OW_Place_Holders::BLOG_NAME,
					addslashes( get_bloginfo( 'name' ) ), $v );
				$v = str_replace( OW_Place_Holders::POST_ID,
					intval( sanitize_text_field( $post_id ) ), $v );
				$v = str_replace( OW_Place_Holders::POST_SUBMITTER,
					$ow_placeholders->get_post_submitter( $current_user_id ), $v );

				if ( $k === "assign_content" ||
				     $k === "reminder_content" ) { //replace %post_title% with a link to the post
					$v = str_replace( OW_Place_Holders::POST_TITLE,
						$ow_placeholders->get_post_title( $post_id, $action_id, true ), $v );
				}
				if ( $k === "assign_subject" || $k === "reminder_subject" ) {
					// since its a email subject, we don't need to have a link to the post
					$v = str_replace( OW_Place_Holders::POST_TITLE,
						$ow_placeholders->get_post_title( $post_id, $action_id, false ), $v );
				}

				if ( ! empty( $callback_custom_placeholders ) ) {
					foreach ( $callback_custom_placeholders as $ki => $vi ) {
						if ( strpos( $v, $ki ) !== false ) {
							$v = str_replace( $ki, $vi, $v );
						}
					}
				}

				$messages->$k = $v;
			}

			return $messages;
		}

		// looks like we either didn't find the post or the step
		return false;
	}

	/**
	 * Get comments from step sign off
	 *
	 * @param int $action_id action_history_id for the given workflow step
	 *
	 * @return mixed comments added by the user during sign off OR false if comments were empty
	 *
	 * @since 2.0
	 */
	public function get_step_comment_content( $action_id, $review_data = [] ) {
		// sanitize the input
		$action_id = intval( sanitize_text_field( $action_id ) );

		$workflow_terminology_options = get_option( 'oasiswf_custom_workflow_terminology' );
		$priority_label               = ! empty( $workflow_terminology_options['taskPriorityText'] )
			? sanitize_text_field( $workflow_terminology_options['taskPriorityText'] ) : esc_html__( 'Priority', 'oasisworkflow' );

		$ow_history_service = new OW_History_Service();
		$action_step        = $ow_history_service->get_action_history_by_id( $action_id );
		// if no comment found, then return;
		if ( ! $action_step->comment ) {
			return false;
		}
		$comments     = json_decode( $action_step->comment );
		$comments_str = "";
		foreach ( $comments as $comment ) {
			$sign_off_date = OW_Utility::instance()
			                           ->format_date_for_display( $action_step->create_datetime, "-", "datetime" );

			$due_date = '';
			if ( ! empty( $action_step->due_date ) ) {
				$due_date = OW_Utility::instance()->format_date_for_display( $action_step->due_date );
			}

			$comments_str .= $this->get_user_sign_off_comments( $action_id, $comment, $action_step->post_id );

			if( empty( $comments_str ) && ! empty( $review_data ) && isset( $review_data['comment'] ) ) {
				$get_comments_str = $review_data['comment'];
				$final_comments = '';
				// get the full name of the user
				$full_name = OW_Utility::instance()->get_user_name( $review_data['task_actor_id'] );
				if ( $get_comments_str != "" ) {
					$final_comments .= "<p><strong>" . esc_html__( 'Additionally,', "oasisworkflow" ) . "</strong> {$full_name} " .
									esc_html__( 'added the following comments', "oasisworkflow" ) . ":</p>";
					$final_comments .= "<p>" . nl2br( $get_comments_str ) . "</p>";
				}

				if( ! empty( $final_comments ) ) {
					$comments_str .= $final_comments;
				}
			}

			// get task priority
			if ( get_option( 'oasiswf_priority_setting' ) == 'enable_priority' ) {
				$priority_value = get_post_meta( $action_step->post_id, '_oasis_task_priority', true );
				if ( ! empty ( $priority_value ) ) {
					$priority_array = OW_Utility::instance()->get_priorities();
					$priority       = $priority_array[ $priority_value ];
					$comments_str   .= "<p>" . $priority_label . " : {$priority}</p>";
				}
			}

			$display_sign_off_date = apply_filters( 'owf_display_sign_off_date', true );

			if ( ! empty( $display_sign_off_date ) ) {
				$comments_str .= "<p>" . esc_html__( 'Sign off date', "oasisworkflow" ) . " : {$sign_off_date}</p>";
			}

			if ( ! empty( $due_date ) ) {
				// get due date terminology from the settings
				$option        = get_option( 'oasiswf_custom_workflow_terminology' );
				$due_date_text = ! empty( $option['dueDateText'] ) ? sanitize_text_field( $option['dueDateText'] )
					: esc_html__( 'Due Date', 'oasisworkflow' );
				$comments_str  .= "<p>" . $due_date_text . " : {$due_date} </p>";
			}

		}

		return $comments_str;
	}

	public function get_user_sign_off_comments( $action_history_id, $comment_object, $post_id ) {
		$comments_str   = '';
		$final_comments = '';
		// get the full name of the user
		$full_name = OW_Utility::instance()->get_user_name( $comment_object->send_id );
		if ( $comment_object->comment != "" ) {
			$comments_str .= "<p><strong>" . esc_html__( 'Additionally,', "oasisworkflow" ) . "</strong> {$full_name} " .
			                 esc_html__( 'added the following comments', "oasisworkflow" ) . ":</p>";
			$comments_str .= "<p>" . nl2br( $comment_object->comment ) . "</p>";
		}

		$args = array( $comments_str, $action_history_id, $comment_object );
		do_action_ref_array( 'owf_get_user_comments', array( &$args ) );
		$final_comments = $args[0];

		return $final_comments;

	}

	/**
	 * get the files to be attached to the emails
	 *
	 * @param int $post_id
	 *
	 * @return array $attachment
	 */
	private function get_attachments( $post_id ) {

		// Sanitize incoming data
		$post_id = intval( $post_id );

		// Get absolute path
		$upload = wp_upload_dir();
		$path   = $upload['basedir'];

		$pdf_paths            = array();
		$publish_revision_pdf = array();

		do_action( "owf_modify_attachments", $post_id );

		// Get post current version pdf
		$current_pdf = get_post_meta( $post_id, '_oasis_current_post_pdf', true );

		// If revision of post than attach the published version pdf of the original post
		$original_post_id = get_post_meta( $post_id, '_oasis_original', true );

		if ( ! empty( $original_post_id ) ) {
			$publish_revision_pdf = get_post_meta( $original_post_id, '_oasis_published_revisions_pdf', true );
		}


		if ( ! empty( $publish_revision_pdf ) ) {
			// Get the latest published/revised pdf
			$pdf_paths[] = $path . end( $publish_revision_pdf );
		}


		if ( ! empty( $pdf_paths ) && ( ! empty( $current_pdf ) ) ) {
			/*
			 * If revision of the post and have published version pdf of original post
			 * than attach current version of revised post and
			 * published version of original post
			 */
			$pdf_paths[] = $path . $current_pdf;
			$attachment  = $pdf_paths;
		} else {
			// If not revision than only attach the current version of the post
			if ( ! empty( $current_pdf ) ) {
				$attachment = $path . $current_pdf;
			} else {
				$attachment = "";
			}
		}

		return $attachment;
	}

	/**
	 * Setup reminder emails for before and after due date
	 *
	 * @param int $action_id  action_history_id for the given workflow step
	 * @param int $to_user_id user_id in the given step
	 *
	 * @since 5.3
	 */
	public function generate_reminder_emails( $action_id, $to_user_id = null ) {
		// sanitize the input
		$action_id  = intval( sanitize_text_field( $action_id ) );
		$to_user_id = intval( sanitize_text_field( $to_user_id ) );

		$ow_history_service = new OW_History_Service();
		$ow_email_settings_helper = new OW_Email_Settings_Helper();
		$action_step        = $ow_history_service->get_action_history_by_id( $action_id );
		$to_user_id         = ( $to_user_id ) ? $to_user_id : $action_step->assign_actor_id;

		OW_Utility::instance()->logger( "Generate reminder emails for : " . $action_id . "-" . $to_user_id );

		$mails   = $this->get_step_mail_content( $action_id, $action_step->step_id, $to_user_id,
			$action_step->post_id );
		$comment = $this->get_step_comment_content( $action_id );

		$post_id = $action_step->post_id;

		$data = array(
			'to_user'         => $to_user_id,
			'history_id'      => $action_id,
			'create_datetime' => current_time( 'mysql' )
		);

		// send email if setting is true
		$emails_table = OW_Utility::instance()->get_emails_table_name();

		// set reminder email for future delivery
		if ( isset( $mails ) &&
		     isset( $mails->reminder_subject ) && $mails->reminder_subject &&
		     isset( $mails->reminder_content ) && $mails->reminder_content ) {
			$mail_content = $mails->reminder_content . $comment;
			$mail_content = apply_filters( "oasiswf_custom_email_content", $mail_content, $post_id, $action_id );

			$email_cc = isset( $mails->reminder_cc ) ? json_decode(json_encode($mails->reminder_cc), true) : [];
			$email_bcc = isset( $mails->reminder_bcc ) ? json_decode(json_encode($mails->reminder_bcc), true) : [];

			$email_recipient_params = array(
				"post_id"   => $post_id,
				"email_cc" 	=> $email_cc,
				"email_bcc" => $email_bcc
			);

			// Get email recipients - all for to, cc and bcc
			$email_recipients = $ow_email_settings_helper->get_cc_recipients( $email_recipient_params );

			$cc_users  = $email_recipients["email_cc"];
			$bcc_users = $email_recipients["email_bcc"];

			$data["subject"] = $mails->reminder_subject;
			$data["message"] = $mail_content;
			$data["cc_users"] = $cc_users;
			$data["bcc_users"] = $bcc_users;
			$data["action"]  = 1;

			// for reminder before date
			if ( $action_step->reminder_date ) {
				$data["send_date"] = $action_step->reminder_date;
				OW_Utility::instance()->insert_to_table( $emails_table, $data );
			}

			// for reminder after date
			if ( $action_step->reminder_date_after ) {
				$data["send_date"] = $action_step->reminder_date_after;
				OW_Utility::instance()->insert_to_table( $emails_table, $data );
			}
		}
	}

	/**
	 * Delete step reminder email, if assignment was completed on time
	 *
	 * @param int $action_history_id action_history_id for the given workflow step
	 * @param int $user_id           user_id in the given step    *
	 *
	 * @since 2.0
	 */
	public function delete_step_email( $action_history_id, $user_id = null ) {
		// if the user completes the assignment on time, then no need to send reminder emails
		global $wpdb;

		// sanitize the input
		$action_history_id = intval( sanitize_text_field( $action_history_id ) );
		$user_id           = intval( sanitize_text_field( $user_id ) );

		if ( $user_id ) {
			$wpdb->get_results( $wpdb->prepare( "DELETE FROM " . $wpdb->fc_emails .
			                                    " WHERE ( action = 1 OR action = 2 ) AND history_id = %d AND to_user = %d",
				$action_history_id, $user_id ) );
		} else {
			$wpdb->get_results( $wpdb->prepare( "DELETE FROM " . $wpdb->fc_emails .
			                                    " WHERE ( action = 1 OR action = 2) and history_id = %d",
				$action_history_id ) );
		}
	}

	/**
	 * Log email error in the debug.log
	 *
	 * @param $wp_error WP_Error
	 */
	public function log_email_send_error( $wp_error ) {
		OW_Utility::instance()->logger( $wp_error );
	}

	/**
	 * Send email to assignees, if post was updated by a non-assignee
	 *
	 * @param int $post_id the ID of the post
	 *
	 * @since 2.5
	 */

	public function notify_users_on_unauthorized_update( $post_id ) {
		// sanitize the input
		$post_id = intval( sanitize_text_field( $post_id ) );

		// Send email when post is published, also do not send email when post has auto-draft or inherit statuses.
		$ow_email_settings_helper           = new OW_Email_Settings_Helper();
		$unauthorized_update_email_settings = get_option( 'oasiswf_unauthorized_update_email_settings' );
		if ( isset( $unauthorized_update_email_settings['is_active'] ) && $unauthorized_update_email_settings['is_active'] == "yes" ) {
			// Fetch all mail parameters
			$email_assignees = isset( $unauthorized_update_email_settings['email_assignees'] )
				? $unauthorized_update_email_settings['email_assignees'] : '';
			$email_cc        = isset( $unauthorized_update_email_settings['email_cc'] )
				? $unauthorized_update_email_settings['email_cc'] : '';
			$email_bcc       = isset( $unauthorized_update_email_settings['email_bcc'] )
				? $unauthorized_update_email_settings['email_bcc'] : '';
			$subject         = isset( $unauthorized_update_email_settings['subject'] )
				? $unauthorized_update_email_settings['subject']
				: $ow_email_settings_helper->get_unauthorized_update_subject();
			$content         = isset( $unauthorized_update_email_settings['content'] )
				? $unauthorized_update_email_settings['content']
				: $ow_email_settings_helper->get_unauthorized_update_content();

			$email_recipient_params = array(
				"post_id"         => $post_id,
				"action"          => OW_Email_Settings_Helper::UNAUTHORIZED_UPDATE_EMAIL,
				"email_assignees" => $email_assignees,
				"email_cc"        => $email_cc,
				"email_bcc"       => $email_bcc
			);

			// Get email recipients - all for to, cc and bcc
			$email_recipients = $ow_email_settings_helper->get_email_recipients( $email_recipient_params );

			$to_email_recipients  = $email_recipients["email_assignees"];
			$cc_email_recipients  = $email_recipients["email_cc"];
			$bcc_email_recipients = $email_recipients["email_bcc"];

			foreach ( $to_email_recipients as $key => $user_id ) {
				$email_params = array(
					"post_id"       => $post_id,
					"email_to"      => $user_id,
					"email_subject" => $subject,
					"email_content" => $content
				);

				// Replace placeholders in mail subject and mail content
				$mail          = $ow_email_settings_helper->get_email_content( $email_params );
				$final_subject = $mail['subject'];
				$final_message = $mail['message'];

				if ( $key == 0 ) {
					// send Cc and Bcc recipients with first "To" email recipients
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message, "",
						$cc_email_recipients, $bcc_email_recipients );
				} else {
					// now send email
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message );
				}
			}
		}
	}

	/**
	 * send email to other users, saying that the article has been removed from their inbox,
	 *  since it was claimed by another user
	 *
	 * @param int $post_id the ID of the post
	 *
	 * @since 3.7
	 */
	public function notify_users_on_task_claimed( $post_id ) {
		// sanitize the input
		$post_id = intval( sanitize_text_field( $post_id ) );

		$ow_email_settings_helper    = new OW_Email_Settings_Helper();
		$task_claimed_email_settings = get_option( 'oasiswf_task_claim_email_settings' );
		if ( isset( $task_claimed_email_settings['is_active'] ) && $task_claimed_email_settings['is_active'] == "yes" ) {
			// Fetch all mail parameters
			$email_assignees = isset( $task_claimed_email_settings['email_assignees'] )
				? $task_claimed_email_settings['email_assignees'] : '';
			$email_cc        = isset( $task_claimed_email_settings['email_cc'] )
				? $task_claimed_email_settings['email_cc'] : '';
			$email_bcc       = isset( $task_claimed_email_settings['email_bcc'] )
				? $task_claimed_email_settings['email_bcc'] : '';
			$subject         = isset( $task_claimed_email_settings['subject'] )
				? $task_claimed_email_settings['subject'] : $ow_email_settings_helper->get_task_claimed_subject();
			$content         = isset( $task_claimed_email_settings['content'] )
				? $task_claimed_email_settings['content'] : $ow_email_settings_helper->get_task_claimed_content();

			$email_recipient_params = array(
				"post_id"         => $post_id,
				"action"          => OW_Email_Settings_Helper::TASK_CLAIMED_EMAIL,
				"email_assignees" => $email_assignees,
				"email_cc"        => $email_cc,
				"email_bcc"       => $email_bcc
			);

			// Get email recipients - all for to, cc and bcc
			$email_recipients = $ow_email_settings_helper->get_email_recipients( $email_recipient_params );

			$to_email_recipients  = $email_recipients["email_assignees"];
			$cc_email_recipients  = $email_recipients["email_cc"];
			$bcc_email_recipients = $email_recipients["email_bcc"];

			foreach ( $to_email_recipients as $key => $user_id ) {
				$email_params = array(
					"post_id"       => $post_id,
					"email_to"      => $user_id,
					"email_subject" => $subject,
					"email_content" => $content
				);

				// Replace placeholders in mail subject and mail content
				$mail          = $ow_email_settings_helper->get_email_content( $email_params );
				$final_subject = $mail['subject'];
				$final_message = $mail['message'];

				if ( $key == 0 ) {
					// send Cc and Bcc recipients with first "To" email recipients
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message, "",
						$cc_email_recipients, $bcc_email_recipients );
				} else {
					// now send email
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message );
				}
			}
		}
	}

	/**
	 * Send notification to the user who submit post to workflow
	 *
	 * @param int $post_id the ID of the post
	 * @param int $new_action_history_id
	 *
	 * @since 3.8
	 */
	public function post_submit_notification( $post_id, $new_action_history_id ) {
		/* sanitize incoming data */
		$post_id        = intval( sanitize_text_field( $post_id ) );
		$new_history_id = intval( sanitize_text_field( $new_action_history_id ) );

		$ow_email_settings_helper   = new OW_Email_Settings_Helper();
		$post_submit_email_settings = get_option( 'oasiswf_post_submit_email_settings' );
		if ( isset( $post_submit_email_settings['is_active'] ) && $post_submit_email_settings['is_active'] == "yes" ) {
			// Fetch all mail parameters
			$email_assignees = isset( $post_submit_email_settings['email_assignees'] )
				? $post_submit_email_settings['email_assignees'] : '';
			$email_cc        = isset( $post_submit_email_settings['email_cc'] )
				? $post_submit_email_settings['email_cc'] : '';
			$email_bcc       = isset( $post_submit_email_settings['email_bcc'] )
				? $post_submit_email_settings['email_bcc'] : '';
			$subject         = isset( $post_submit_email_settings['subject'] ) ? $post_submit_email_settings['subject']
				: $ow_email_settings_helper->get_post_submit_subject();
			$content         = isset( $post_submit_email_settings['content'] ) ? $post_submit_email_settings['content']
				: $ow_email_settings_helper->get_post_submit_content();

			$email_recipient_params = array(
				"post_id"         => $post_id,
				"action"          => OW_Email_Settings_Helper::POST_SUBMITTED_EMAIL,
				"email_assignees" => $email_assignees,
				"email_cc"        => $email_cc,
				"email_bcc"       => $email_bcc
			);

			// Get email recipients - all for to, cc and bcc
			$email_recipients = $ow_email_settings_helper->get_email_recipients( $email_recipient_params );

			$to_email_recipients  = $email_recipients["email_assignees"];
			$cc_email_recipients  = $email_recipients["email_cc"];
			$bcc_email_recipients = $email_recipients["email_bcc"];

			foreach ( $to_email_recipients as $key => $user_id ) {
				$email_params = array(
					"post_id"       => $post_id,
					"email_to"      => $user_id,
					"email_subject" => $subject,
					"email_content" => $content
				);

				// Replace placeholders in mail subject and mail content
				$mail          = $ow_email_settings_helper->get_email_content( $email_params );
				$final_subject = $mail['subject'];
				$final_message = $mail['message'];

				if ( $key == 0 ) {
					// send Cc and Bcc recipients with first "To" email recipients
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message, "",
						$cc_email_recipients, $bcc_email_recipients );
				} else {
					// now send email
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message );
				}
			}
		}
	}

	/**
	 * Send email if workflow was aborted
	 *
	 * @param int $post_id the ID of the post for which the workflow was aborted
	 * @param int $action_id the new action history ID
	 *
	 * @since 2.6
	 */
	public function send_abort_email( $post_id, $action_id ) {
		// sanitize the input
		$post_id = intval( $post_id );

		$ow_email_settings_helper      = new OW_Email_Settings_Helper();
		$workflow_abort_email_settings = get_option( 'oasiswf_workflow_abort_email_settings' );
		if ( isset( $workflow_abort_email_settings['is_active'] ) && $workflow_abort_email_settings['is_active'] == 'yes' ) {
			// Fetch all mail parameters
			$email_assignees = isset( $workflow_abort_email_settings['email_assignees'] )
				? $workflow_abort_email_settings['email_assignees'] : '';
			$email_cc        = isset( $workflow_abort_email_settings['email_cc'] )
				? $workflow_abort_email_settings['email_cc'] : '';
			$email_bcc       = isset( $workflow_abort_email_settings['email_bcc'] )
				? $workflow_abort_email_settings['email_bcc'] : '';
			$subject         = isset( $workflow_abort_email_settings['subject'] ) && ! empty( $workflow_abort_email_settings['subject'] )
				? $workflow_abort_email_settings['subject'] : $ow_email_settings_helper->get_workflow_abort_subject();
			$content         = isset( $workflow_abort_email_settings['content'] ) && ! empty( $workflow_abort_email_settings['content'] )
				? $workflow_abort_email_settings['content'] : $ow_email_settings_helper->get_workflow_abort_content();

			$content .= $this->get_step_comment_content( $action_id );

			$email_recipient_params = array(
				"post_id"         => $post_id,
				"action"          => OW_Email_Settings_Helper::WORKFLOW_ABORT_EMAIL,
				"email_assignees" => $email_assignees,
				"email_cc"        => $email_cc,
				"email_bcc"       => $email_bcc
			);

			// Get email recipients - all for to, cc and bcc
			$email_recipients = $ow_email_settings_helper->get_email_recipients( $email_recipient_params );

			$to_email_recipients  = $email_recipients["email_assignees"];
			$cc_email_recipients  = $email_recipients["email_cc"];
			$bcc_email_recipients = $email_recipients["email_bcc"];

			foreach ( $to_email_recipients as $key => $user_id ) {
				$email_params = array(
					"post_id"       => $post_id,
					"email_to"      => $user_id,
					"email_subject" => $subject,
					"email_content" => $content
				);

				// Replace placeholders in mail subject and mail content
				$mail          = $ow_email_settings_helper->get_email_content( $email_params );
				$final_subject = $mail['subject'];
				$final_message = $mail['message'];

				if ( $key == 0 ) {
					// send Cc and Bcc recipients with first "To" email recipients
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message, "",
						$cc_email_recipients, $bcc_email_recipients );
				} else {
					// now send email
					$this->send_mail( $email_params['email_to'], $final_subject, $final_message );
				}
			}
		}
	}


}

$ow_email = new OW_Email();

// send email of post submitted to workflow succesfully.
add_action( 'owf_submit_to_workflow', array( $ow_email, 'post_submit_notification' ), 10, 2 );

//send email to post author about workflow abort
add_action( 'owf_workflow_abort', array( $ow_email, 'send_abort_email' ), 10, 2 );

// revised post published notification
add_action( 'owf_update_published_page', array( $ow_email, 'revised_post_published_notification' ), 10, 2 );
add_action( 'owf_update_published_post', array( $ow_email, 'revised_post_published_notification' ), 10, 2 );

//add_action( 'wp_mail_failed', array( $ow_email, 'log_email_send_error' ) );

