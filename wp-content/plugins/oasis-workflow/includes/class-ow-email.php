<?php
/*
 * Oasis Workflow mail class
 *
 * @copyright   Copyright (c) 2016, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/*
 * OW_Email class
 *
 * @since 2.0
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

	}

	/**
	 * Send post published notification to the author, if configured "yes"
	 *
	 * @param string $new_status current status of the post
	 * @param string $old_status old status of the post
	 * @param mixed $post the post object
	 *
	 * @since 2.0
	 */
	public function post_published_notification( $new_status, $old_status, $post ) {
		// sanitize the input
		$new_status = sanitize_text_field( $new_status );
		$old_status = sanitize_text_field( $old_status );

		$email_settings = get_option( 'oasiswf_email_settings' );

		// Send email when post is published, also do not send email when post has auto-draft or inherit statuses.
		$oasis_is_in_workflow = get_post_meta( $post->ID, '_oasis_is_in_workflow', true );

		if ( $new_status == 'publish' &&
		     $old_status != 'publish' &&
		     $email_settings['post_publish_emails'] == "yes" &&
		     $oasis_is_in_workflow == 1 ) {

			$blog_name = '[' . addslashes( get_bloginfo( 'name' ) ) . '] ';
			$subject   = $blog_name . esc_html__( "Your article has been published.", "oasisworkflow" );
			$user      = get_userdata( $post->post_author );
			$to        = $user->user_email;

			$msg     = sprintf( '<div>%1$s %2$s,<p>%3$s <a href="%4$s" title="%5$s">%6$s</a> %7$s <a href="%8$s" title="%9$s">%10$s</a></p><p>%11$s</p></div>',
				esc_html__( 'Hello', 'oasisworkflow' ),
				esc_html( $user->display_name ),
				esc_html__( 'Your article', 'oasisworkflow' ),
				esc_url( get_permalink( $post->ID ) ),
				esc_attr( $post->post_title ),
				esc_html( $post->post_title ),
				esc_html__( 'has been published on', 'oasisworkflow' ),
				esc_url( get_bloginfo( 'url' ) ),
				esc_attr( get_bloginfo( 'name' ) ),
				esc_html( get_bloginfo( 'name' ) ),
				esc_html__( 'Thanks', 'oasisworkflow' )
			);
			$message = '<html><head></head><body><div class="email_notification_body">' . $msg . '</div></body></html>';

			$this->send_mail( $to, $subject, $message );
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
	 * @param string $subject subject of the email
	 * @param string $message message of the email
	 *
	 * @since 2.0
	 */
	public function send_mail( $to_user, $subject, $message ) {
		$to_user = sanitize_text_field( $to_user );
		$subject = sanitize_text_field( $subject );

		// to_user could be an id of the user or email address
		if ( is_numeric( $to_user ) ) {
			$user = get_userdata( $to_user );
		} else {
			$user = get_user_by( 'email', $to_user );
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

		$imploded_headers = implode( "\r\n", $headers ) . "\r\n";
		$decoded_title    = html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' );
		$decoded_message  = html_entity_decode( $message, ENT_QUOTES, 'UTF-8' );

		// send mail using wp_email function
		wp_mail( $user->data->user_email, $decoded_title, $decoded_message, $imploded_headers ); // phpcs:ignore

	}

	/**
	 * Send reminder email to user about the tasks
	 * @global object $wpdb
	 */
	public function send_reminder_email() {
		global $wpdb;
		$emails_table = OW_Utility::instance()->get_emails_table_name();

		$email_settings = get_option( 'oasiswf_email_settings' );
		if ( $email_settings['reminder_emails'] == "yes" ) {
			$ddate = gmdate( 'Y-m-d' );
			// phpcs:ignore
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . OW_Utility::instance()->get_emails_table_name() . " WHERE action = 1 AND send_date = %s", $ddate ) );
			foreach ( $rows as $row ) {
				$this->send_mail( $row->to_user, $row->subject, $row->message );
				$wpdb->update( $emails_table, array( "action" => 0 ), array( "ID" => $row->ID ) ); // phpcs:ignore
			}
		}
	}

	/**
	 * Send assignment emails
	 * Setup reminder emails for before and after due date
	 *
	 * @param int $action_id action_history_id for the given workflow step
	 * @param int $to_user_id user_id in the given step
	 *
	 * @since 2.0
	 */
	public function send_step_email( $action_id, $to_user_id = null ) {
		// sanitize the input
		$action_id  = intval( $action_id );
		$to_user_id = intval( $to_user_id );

		$ow_history_service = new OW_History_Service();
		$action_step        = $ow_history_service->get_action_history_by_id( $action_id );
		$to_user_id         = ( $to_user_id ) ? $to_user_id : $action_step->assign_actor_id;
		$mails              = $this->get_step_mail_content( $action_id, $action_step->step_id, $to_user_id, $action_step->post_id );
		$comment            = $this->get_step_comment_content( $action_id );

		$data = array(
			'to_user'         => $to_user_id,
			'history_id'      => $action_id,
			'create_datetime' => current_time( 'mysql' )
		);

		// send email if setting is true
		$emails_table   = OW_Utility::instance()->get_emails_table_name();
		$email_settings = get_option( 'oasiswf_email_settings' );
		if ( isset( $mails ) &&
		     isset( $mails->assign_subject ) && $mails->assign_subject &&
		     isset( $mails->assign_content ) && $mails->assign_content &&
		     $email_settings['assignment_emails'] == "yes" ) {

			$mail_content = $mails->assign_content . $comment;

			$this->send_mail( $to_user_id, $mails->assign_subject, $mail_content );

			$data["subject"]   = $mails->assign_subject;
			$data["message"]   = $mail_content;
			$data["send_date"] = current_time( 'mysql' );
			$data["action"]    = 0;
			OW_Utility::instance()->insert_to_table( $emails_table, $data );
		}

		// set reminder email for future delivery
		if ( isset( $mails ) &&
		     isset( $mails->reminder_subject ) && $mails->reminder_subject &&
		     isset( $mails->reminder_content ) && $mails->reminder_content ) {
			$mail_content = $mails->reminder_content . $comment;

			$data["subject"] = $mails->reminder_subject;
			$data["message"] = $mail_content;
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
	 * Get mail content from the step configuration
	 * Merge placeholders with actual value
	 *
	 * @param int $action_id action_history_id for the given workflow step
	 * @param int $step_id step_id for the given workflow
	 * @param int $to_user_id user_id in the given step
	 * @param int $post_id post currently in the workflow
	 *
	 * @return mixed step message if step and post exists OR false
	 *
	 * @since 2.0
	 */
	public function get_step_mail_content( $action_id, $step_id, $to_user_id, $post_id ) {
		$action_id  = intval( $action_id );
		$step_id    = intval( $step_id );
		$to_user_id = intval( $to_user_id );
		$post_id    = intval( $post_id );

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

			$messages->assign_subject = ( ! empty( $subject_line ) ) ? $blog_name . $messages->assign_subject : $blog_name . esc_html__( "You have an assignment", "oasisworkflow" );
			$messages->assign_content = ( ! empty( $content_line ) ) ? $messages->assign_content : sprintf( '%1$s - %2$s', esc_html__( 'You have an assignment related to post', 'oasisworkflow' ), $post_link );;

			// replace the placeholders
			//TODO: to implement custom placeholders

			$callback_custom_placeholders = apply_filters( 'oasiswf_custom_placeholders_handler', $post );


			foreach ( $messages as $k => $v ) {
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

				if ( $k === "assign_content" || $k === "reminder_content" ) { //replace %post_title% with a link to the post
					$v = str_replace( OW_Place_Holders::POST_TITLE,
						$ow_placeholders->get_post_title( $post_id, $action_id, true ), $v );
				}
				if ( $k === "assign_subject" || $k === "reminder_subject" ) { // since its a email subject, we don't need to have a link to the post
					$v = str_replace( OW_Place_Holders::POST_TITLE,
						$ow_placeholders->get_post_title( $post_id, $action_id, false ), $v );
				}

				foreach ( $callback_custom_placeholders as $ki => $vi ) {
					if ( strpos( $v, $ki ) !== false ) {
						$v = str_replace( $ki, $vi, $v );
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
	public function get_step_comment_content( $action_id ) {
		// sanitize the input
		$action_id = intval( $action_id );

		$workflow_terminology_options = get_option( 'oasiswf_custom_workflow_terminology' );
		$priority_label               = ! empty( $workflow_terminology_options['taskPriorityText'] ) ? $workflow_terminology_options['taskPriorityText'] : __( 'Priority', 'oasisworkflow' );

		$ow_history_service = new OW_History_Service();
		$action_step        = $ow_history_service->get_action_history_by_id( $action_id );
		// if no comment found, then return
		if ( ! $action_step->comment ) {
			return false;
		}
		$comments     = json_decode( $action_step->comment );
		$comments_str = "";
		foreach ( $comments as $comment ) {
			$sign_off_date = OW_Utility::instance()->format_date_for_display( $action_step->create_datetime, "-", "datetime" );

			$due_date = '';
			if ( ! empty( $action_step->due_date ) ) {
				$due_date = OW_Utility::instance()->format_date_for_display( $action_step->due_date );
			}

			$comments_str .= $this->get_user_sign_off_comments( $action_id, $comment, $action_step->post_id );

			// get task priority
			if ( get_option( 'oasiswf_priority_setting' ) == 'enable_priority' ) {
				$priority_value = get_post_meta( $action_step->post_id, '_oasis_task_priority', true );
				if ( ! empty ( $priority_value ) ) {
					$priority_array = OW_Utility::instance()->get_priorities();
					$priority       = $priority_array[ $priority_value ];
					$comments_str   .= "<p>" . $priority_label . " : {$priority}</p>";
				}
			}

			$comments_str .= "<p>" . __( 'Sign off date', "oasisworkflow" ) . " : {$sign_off_date}</p>";
			if ( ! empty( $due_date ) ) {
				// get due date terminology from the settings
				$due_date_text = ! empty( $workflow_terminology_options['dueDateText'] ) ? $workflow_terminology_options['dueDateText'] : esc_html__( 'Due Date', 'oasisworkflow' );
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
			$comments_str .= "<p><strong>" . esc_html__( 'Additionally,', "oasisworkflow" ) . "</strong> {$full_name} " . esc_html__( 'added the following comments', "oasisworkflow" ) . ":</p>";
			$comments_str .= "<p>" . nl2br( $comment_object->comment ) . "</p>";
		}

		$args = array( $comments_str, $action_history_id, $comment_object );
		do_action_ref_array( 'owf_get_user_comments', array( &$args ) );
		$final_comments = $args[0];

		return $final_comments;
	}

	/**
	 * Delete step reminder email, if assignment was completed on time
	 *
	 * @param int $action_history_id action_history_id for the given workflow step
	 * @param int $user_id user_id in the given step     *
	 *
	 * @since 2.0
	 */
	public function delete_step_email( $action_history_id, $user_id = null ) {
		// if the user completes the assignment on time, then no need to send reminder emails
		global $wpdb;

		// sanitize the input
		$action_history_id = intval( $action_history_id );
		$user_id           = intval( $user_id );

		if ( $user_id ) {
			$sql = "DELETE FROM " . OW_Utility::instance()->get_emails_table_name() . " WHERE action = 1 AND history_id = %d AND to_user = %d";
			// phpcs:ignore
			$wpdb->get_results( $wpdb->prepare( $sql, array( $action_history_id, $user_id ) ) );
		} else {
			$sql = "DELETE FROM " . OW_Utility::instance()->get_emails_table_name() . " WHERE action = 1 and history_id = %d";
			// phpcs:ignore
			$wpdb->get_results( $wpdb->prepare( $sql, $action_history_id ) );
		}
	}

	/**
	 * Send email to assignees, if post was updated by a non-assignee
	 *
	 * @param mixed $assignees list of assignees/users for the given step
	 * @param int $current_user_id user id of the logged in user
	 * @param int $post_id the ID of the post
	 *
	 * @since 2.0
	 */
	public function notify_users_on_unauthorized_update( $assignees, $current_user_id, $post_id ) {
		// sanitize the input
		$post_id         = intval( $post_id );
		$current_user_id = intval( $current_user_id );

		$email_settings = get_option( 'oasiswf_email_settings' );

		// Send email when post is published, also do not send email when post has auto-draft or inherit statuses.
		if ( $email_settings['unauthorized_post_update_emails'] == "yes" ) {
			$blog_name = '[' . addslashes( get_bloginfo( 'name' ) ) . '] ';
			$subject   = $blog_name . esc_html__( "Article was updated outside the workflow.", "oasisworkflow" );
			foreach ( $assignees as $assignee ) {
				$user      = get_userdata( $assignee );
				$post_info = get_post( $post_id );
				$to        = $user->user_email;
				$msg       = "<div>" . esc_html__( "Hello ", "oasisworkflow" );
				$msg       = $msg . OW_Utility::instance()->get_user_name( $assignee ) . ",";
				$msg       = $msg . "</div>";
				$msg       = $msg . "<p>";
				$msg       = $msg . OW_Utility::instance()->get_user_name( $current_user_id );
				$msg       = $msg . esc_html__( ", who is not part of the current assignee list has updated the article ", "oasisworkflow" );
				$msg       = $msg . "<a href=" . esc_url( get_permalink( $post_id ) ) . " title=" . $post_info->post_title . ">" . $post_info->post_title . "</a>";
				$msg       = $msg . esc_html__( " outside the workflow.", "oasisworkflow" );
				$msg       = $msg . "</p>";

				$message = '<html><head></head><body><div class="email_notification_body">' . $msg . '</div></body></html>';

				$this->send_mail( $to, $subject, $message );
			}
		}
	}

	/**
	 * send notification email when user claimed the task
	 *
	 * @param int $actor_id
	 * @param int $post_id
	 *
	 * @since 2.0
	 */
	public function notify_users_on_task_claimed( $actor_id, $post_id ) {
		// sanitize the input
		$actor_id = intval( $actor_id );
		$post_id  = intval( $post_id );

		$post_title = stripcslashes( get_post( $post_id )->post_title );

		// send email to other users, saying that the article has been removed from their inbox, since it was claimed by another user
		$email_settings = get_option( 'oasiswf_email_settings' );
		if ( $email_settings['assignment_emails'] == "yes" ) {
			$blog_name = '[' . addslashes( get_bloginfo( 'name' ) ) . '] ';
			$title     = $blog_name . esc_html__( "Task claimed", "oasisworkflow" );

			$message = sprintf( '%1$s %2$s , %3$s.',
				esc_html__( 'Another user has claimed the task for the article', 'oasisworkflow' ),
				$post_title,
				esc_html__( 'so please ignore it', 'oasisworkflow' )
			);
			$this->send_mail( $actor_id, $title, $message );
		}
	}

	/**
	 * Send notification to the user who submit post to workflow
	 *
	 * @param int $post_id the ID of the post
	 * @param mixed $post the post object
	 *
	 * @since 2.0
	 */
	public function post_submit_notification( $post_id, $new_action_history_id ) {
		$post_id = intval( $post_id );
		$post    = get_post( $post_id );

		$email_settings = get_option( 'oasiswf_email_settings' );
		if ( isset( $email_settings['submit_to_workflow_email'] ) && $email_settings['submit_to_workflow_email'] == "yes" ) {

			$blog_name = '[' . addslashes( get_bloginfo( 'name' ) ) . '] ';
			$subject   = $blog_name . esc_html__( "Your article has been submitted.", "oasisworkflow" );
			$user      = get_userdata( $post->post_author );
			$to        = $user->user_email;

			$msg     = sprintf( '<div>%1$s %2$s,<p>%3$s <a href="%4$s" title="%5$s">%6$s</a> %7$s.</p><p>%8$s</p></div>',
				esc_html__( 'Hello', 'oasisworkflow' ),
				esc_html( $user->display_name ),
				esc_html__( 'Your article', 'oasisworkflow' ),
				esc_url( get_permalink( $post_id ) ),
				esc_attr( $post->post_title ),
				esc_html( $post->post_title ),
				esc_html__( 'has been successfully submitted to the workflow', 'oasisworkflow' ),
				esc_html__( 'Thanks', 'oasisworkflow' )
			);
			$message = '<html><head></head><body><div class="email_notification_body">' . $msg . '</div></body></html>';

			$this->send_mail( $to, $subject, $message );
		}
	}

	/**
	 * Send email to author if workflow was aborted
	 *
	 * @param int $post_id the ID of the post for which the workflow was aborted
	 * @param int $action_id the new action history id.
	 *
	 * @since 2.0
	 */

	public function send_abort_email_to_author( $post_id, $action_id ) {
		// sanitize the input
		$post_id = intval( $post_id );

		$email_settings = get_option( 'oasiswf_email_settings' );
		if ( isset( $email_settings['abort_email_to_author'] ) && $email_settings['abort_email_to_author'] == 'yes' ) {
			$post         = get_post( $post_id );
			$post_title   = stripcslashes( $post->post_title );
			$author       = get_userdata( $post->post_author );
			$author_email = $author->user_email;
			$comment      = $this->get_step_comment_content( $action_id );

			$blog_name = '[' . addslashes( get_bloginfo( 'name' ) ) . '] ';
			$subject   = $blog_name . esc_html__( 'Workflow has been aborted.', "oasisworkflow" );
			$message   = "<p>";
			$message   .= esc_html__( "Hello ", "oasisworkflow" );
			$message   .= $author->display_name . ",";
			$message   .= "</p>";
			$message   .= "<p>" . esc_html__( "Your article ", "oasisworkflow" );
			$message   .= "<a href=" . esc_url( get_permalink( $post_id ) ) . " title=" . esc_attr( $post->post_title ) . ">" . esc_html( $post->post_title ) . "</a>";
			$message   .= esc_html__( " has been aborted from the workflow.", "oasisworkflow" );
			$message   .= "</p>";
			$message   .= "<p>";
			$message   .= $comment;
			$message   .= esc_html__( "If you have further questions regarding your article, please contact the administrator.", "oasisworkflow" );
			$message   .= "</p>";
			$this->send_mail( $author_email, $subject, $message );
		}
	}

	/*
	public function notify_admin_on_pass_duedate( $workflow_action_id ) {

	   // sanitize the input
	   $workflow_action_id = sanitize_text_field( $workflow_action_id );
	   $email_settings = get_option('oasiswf_email_settings') ;
	   $post = get_post( $workflow_action_id );

	   // Send email when post review due date is overdue.

		$blog_name = '[' . addslashes( get_bloginfo( 'name' )) . '] ';
		$subject =  $blog_name . __( "Article Review overdue.", "oasisworkflow" );
	   $user = get_userdata( '1' );
	   $to = $user->user_email;

	   $msg = sprintf( __( '<div>Hello, <strong>%1$s</strong></div><p>An article <a href="%2$s" title="%3$s">%3$s</a> has been outdated for review.</p><p>Thanks</p>', 'oasisworkflow' ), $user->user_login,	esc_url( $post->guid ),	$post->post_title );
	   $message = '<html><head></head><body><div class="email_notification_body">'.$msg.'</div></body></html>';
	   $this->send_mail( $to, $subject, $message );
	}
	*/

}

$ow_email = new OW_Email();

// send email of post submitted to workflow succesfully.
add_action( 'owf_submit_to_workflow', array( $ow_email, 'post_submit_notification' ), 10, 2 );

//send email to post author about workflow abort
add_action( 'owf_workflow_abort', array( $ow_email, 'send_abort_email_to_author' ), 10, 2 );