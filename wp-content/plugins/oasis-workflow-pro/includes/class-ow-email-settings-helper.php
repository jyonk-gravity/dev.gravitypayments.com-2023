<?php

/**
 * Helper class for Workflow Emails
 *
 * @copyright   Copyright (c) 2017, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.6
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OW_Email_Settings_Helper Class
 *
 * @since 4.6
 */
class OW_Email_Settings_Helper {

	// define the email placeholder constants
	const FIRST_NAME = "{first_name}";
	const LAST_NAME = "{last_name}";
	const POST_TITLE = "{post_title}";
	const POST_ID = "{post_id}";
	const POST_CATEGORY = "{category}";
	const POST_LAST_MODIFIED_DATE = "{last_modified_date}";
	const POST_PUBLISH_DATE = "{publish_date}";
	const POST_AUTHOR = "{post_author}";
	const BLOG_NAME = "{blog_name}";
	const CURRENT_USER = "{current_user}";

	// email types
	const POST_PUBLISH_EMAIL = "post-published";
	const REVISED_POST_PUBLISH_EMAIL = "revised-post-published";
	const UNAUTHORIZED_UPDATE_EMAIL = "unauthorized-update";
	const TASK_CLAIMED_EMAIL = "task-claimed";
	const POST_SUBMITTED_EMAIL = "post-submitted";
	const WORKFLOW_ABORT_EMAIL = "workflow-abort";


	/**
	 * Set things up.
	 *
	 * @since 4.6
	 */
	public function __construct() {

	}

	/**
	 * set email types
	 *
	 * @return array
	 * @since 4.6
	 */
	public function email_types() {
		return array(
			'post_publish'        => esc_html__( 'Post Publish Notification', 'oasisworkflow' ),
			'revised_post'        => esc_html__( 'Revised Post Published Notification', 'oasisworkflow' ),
			'unauthorized_update' => esc_html__( 'Unauthorized Update Notification', 'oasisworkflow' ),
			'task_claim'          => esc_html__( 'Task Claimed Notification', 'oasisworkflow' ),
			'post_submit'         => esc_html__( 'Post Submit Notification', 'oasisworkflow' ),
			'workflow_abort'      => esc_html__( 'Workflow Abort Notification', 'oasisworkflow' )
		);
	}

	/**
	 * Set email type option values for email templates
	 *
	 * @param string $sel_type default selected email type will be post_publish
	 *
	 * @return HTML of email type options tag
	 * @since 4.6
	 */
	public function get_email_type_dropdown( $sel_type = 'post_publish' ) {
		$email_type = $this->email_types();
		$option     = '';
		foreach ( $email_type as $key => $val ) {
			$option .= '<option value="' . esc_attr($key) . '" ' . selected( $sel_type, $key, false ) . '>' .
			           esc_html( $val ) . '</option>';
		}
		echo wp_kses($option, array(
			'option' => array(
				'value'  => array(),
				'selected' => array(),
			)
		));
	}

	/**
	 * Set user roles for emails
	 *
	 * @return array
	 * @since 4.6
	 */
	public function email_user_roles() {
		return array(
			'post_author'            => esc_html__( 'Post Author(s)', 'oasisworkflow' ),
			'administrator'          => esc_html__( 'Administrator(s)', 'oasisworkflow' ),
			'post_submitter'         => esc_html__( 'Post Submitter', 'oasisworkflow' ),
			'current_task_assignees' => esc_html__( 'Current Task Assignees', 'oasisworkflow' )
		);
	}

	/**
	 * Create HTML checkbox section for email user roles
	 *
	 * @param $setting_name
	 * @param $action
	 * @param $selected_users
	 *
	 * @return HTML for roles checkbox
	 * @since 4.6
	 */
	public function ow_email_user_roles( $setting_name, $action, $selected_users ) {
		$selected_row     = '';
		$email_user_roles = $this->email_user_roles();
		foreach ( $email_user_roles as $role => $display_name ) {
			// for certain email settings, we do not want to show the current task assignees as an option
			if ( ( $action == OW_Email_Settings_Helper::POST_PUBLISH_EMAIL ||
			       $action == OW_Email_Settings_Helper::REVISED_POST_PUBLISH_EMAIL ||
			       $action == OW_Email_Settings_Helper::POST_SUBMITTED_EMAIL ) &&
			     $role == "current_task_assignees"
			) {
				continue;
			}

			if ( ! empty( $selected_users ) && is_array( $selected_users ) &&
			     in_array( esc_attr( $role ), $selected_users ) ) { // preselect specified role
				$checked = " ' checked='checked' ";
			} else {
				$checked = '';
			}

			$selected_row .= "<label class='owf-email-checkbox'><input type='checkbox' class='owf-checkbox'
					name='" . esc_attr($setting_name) . "' value='" . esc_attr( $role ) . "'" . $checked . "'/>";
			$selected_row .= esc_html( $display_name );
			$selected_row .= "</label>";
		}
		echo wp_kses($selected_row, array(
			'label' => array(
				'class'  => array(),
				'selected' => array(),
			),
			'input' => array(
				'type'  => array(),
				'class' => array(),
				'name' => array(),
				'value' => array(),
				'checked' => array(),
			),
		));
	}

	/**
	 * Default subject for post publish notification
	 *
	 * @return email subject text $default_subject
	 * @since 4.6
	 */
	public function get_post_publish_subject() {
		$default_subject = "[{blog_name}]" . esc_html__( " Your article has been published.", "oasisworkflow" );

		return $default_subject;
	}

	/**
	 * Default template text for post publish notification content
	 *
	 * @return email message text $default_email_body
	 * @since 4.6
	 */
	public function get_post_publish_content() {
		$default_email_body = esc_html__( 'Hello ', 'oasisworkflow' ) . '{first_name}' . ",\n\n";
		$default_email_body .= esc_html__( 'Your article ', 'oasisworkflow' ) . '{post_title}' . '';
		$default_email_body .= esc_html__( ' has been published on ', 'oasisworkflow' ) . '{blog_name}' . ".\n\n";
		$default_email_body .= esc_html__( 'Thanks.', 'oasisworkflow' );

		return $default_email_body;
	}

	/**
	 * Default subject for revised post publish notification
	 *
	 * @return email subject text $default_subject
	 * @since 4.6
	 */
	public function get_revised_post_publish_subject() {
		$default_subject = "[{blog_name}]" . esc_html__( " Your revised article has been published.", "oasisworkflow" );

		return $default_subject;
	}

	/**
	 * Default template text for revised post publish notification content
	 *
	 * @return email message text $default_email_body
	 * @since 4.6
	 */
	public function get_revised_post_publish_content() {
		$default_email_body = esc_html__( 'Hello ', 'oasisworkflow' ) . '{first_name}' . ",\n\n";
		$default_email_body .= esc_html__( 'Your revised article ', 'oasisworkflow' ) . '{post_title}' . '';
		$default_email_body .= esc_html__( ' has been published on ', 'oasisworkflow' ) . '{blog_name}' . ".\n\n";
		$default_email_body .= esc_html__( 'Thanks.', 'oasisworkflow' );

		return $default_email_body;
	}

	/**
	 * Default subject for unauthorized update notification
	 *
	 * @return email subject text $default_subject
	 * @since 4.6
	 */
	public function get_unauthorized_update_subject() {
		$default_subject = "[{blog_name}]" . esc_html__( " Article was updated outside the workflow.", "oasisworkflow" );

		return $default_subject;
	}

	/**
	 * Default template text for runauthorized update notification content
	 *
	 * @return email message text $default_email_body
	 * @since 4.6
	 */
	public function get_unauthorized_update_content() {
		$default_email_body = esc_html__( 'Hello ', 'oasisworkflow' ) . '{first_name}' . ",\n\n";
		$default_email_body .= '{current_user}, ' .
		                       esc_html__( 'who is not part of the assignee list has updated the article ', 'oasisworkflow' ) .
		                       '{post_title}' . '';
		$default_email_body .= esc_html__( ' outside the workflow on ', 'oasisworkflow' ) . '{blog_name}' . ".\n\n";
		$default_email_body .= esc_html__( 'Thanks.', 'oasisworkflow' );

		return $default_email_body;
	}

	/**
	 * Default subject for task claimed notification
	 *
	 * @return email subject text $default_subject
	 * @since 4.6
	 */
	public function get_task_claimed_subject() {
		$default_subject = "[{blog_name}]" . esc_html__( " Task claimed.", "oasisworkflow" );

		return $default_subject;
	}

	/**
	 * Default template text for task clamied notification content
	 *
	 * @return email message text $default_email_body
	 * @since 4.6
	 */
	public function get_task_claimed_content() {
		$default_email_body = esc_html__( 'Hello ', 'oasisworkflow' ) . '{first_name}' . ",\n\n";
		$default_email_body .= esc_html__( 'Another user has claimed the task for the article ', 'oasisworkflow' ) .
		                       '{post_title}' . '.';
		$default_email_body .= esc_html__( ' Please ignore the task. ', 'oasisworkflow' ) . "\n\n";
		$default_email_body .= esc_html__( 'Thanks.', 'oasisworkflow' );

		return $default_email_body;
	}

	/**
	 * Default subject for post submit notification
	 *
	 * @return email subject text $default_subject
	 * @since 4.6
	 */
	public function get_post_submit_subject() {
		$default_subject = "[{blog_name}]" . esc_html__( " Your article has been submitted.", "oasisworkflow" );

		return $default_subject;
	}

	/**
	 * Default template text for post submit notification content
	 *
	 * @return email message text $default_email_body
	 * @since 4.6
	 */
	public function get_post_submit_content() {
		$default_email_body = esc_html__( 'Hello ', 'oasisworkflow' ) . '{first_name}' . ",\n\n";
		$default_email_body .= esc_html__( 'Your article ', 'oasisworkflow' ) . '{post_title}' . '';
		$default_email_body .= esc_html__( ' has been successfully submitted to the workflow on ', 'oasisworkflow' ) .
		                       '{blog_name}' . ".\n\n";
		$default_email_body .= esc_html__( 'Thanks.', 'oasisworkflow' );

		return $default_email_body;
	}

	/**
	 * Default subject for workflow abort notification
	 *
	 * @return email subject text $default_subject
	 * @since 4.6
	 */
	public function get_workflow_abort_subject() {
		$default_subject = "[{blog_name}]" . esc_html__( " Workflow has been aborted.", "oasisworkflow" );

		return $default_subject;
	}

	/**
	 * Default template text for workflow abort notification content
	 *
	 * @return email message text $default_email_body
	 * @since 4.6
	 */
	public function get_workflow_abort_content() {
		$default_email_body = esc_html__( 'Hello ', 'oasisworkflow' ) . '{first_name}' . ",\n\n";
		$default_email_body .= esc_html__( 'Your article ', 'oasisworkflow' ) . '{post_title}' . '';
		$default_email_body .= esc_html__( ' has been aborted from the workflow on ', 'oasisworkflow' ) . '{blog_name}' .
		                       ".\n\n";
		$default_email_body .= esc_html__( 'If you have further questions regarding your article, please contact the administrator. ',
				'oasisworkflow' ) . "\n\n";
		$default_email_body .= esc_html__( 'Thanks.', 'oasisworkflow' );

		return $default_email_body;
	}

	/**
	 * Returns users list for emails according the participating roles for workflow.
	 *
	 * @return HTMl $options drop down box for mails additional users
	 * @since 4.6
	 */
	public function get_users_option_list( $selected_users ) {
		$participants   = get_option( 'oasiswf_participating_roles_setting' );
		$user_role_keys = array_keys( $participants );

		// get all registered users in the site
		$args    = array(
			'blog_id'  => $GLOBALS['blog_id'],
			'role__in' => $user_role_keys,
			'fields'   => array( 'ID', 'display_name' )
		);
		$users   = get_users( $args );
		$options = '';
		foreach ( $users as $user ) {
			if ( ! empty( $selected_users ) && is_array( $selected_users ) &&
			     in_array( esc_attr( $user->ID ), $selected_users ) ) { // preselect specified role
				$selected = " ' selected='selected' ";
			} else {
				$selected = '';
			}
			$options .= "<option value='{$user->ID}' $selected >".esc_html($user->display_name)."</option>";
		}
		echo wp_kses($options, array(
			'option' => array(
				'value'  => array(),
				'selected' => array(),
			)
		));
	}

	/**
	 * Placeholders for email content
	 *
	 * @return HTML
	 * @since 4.6
	 */
	public function get_placeholders() { ?>
        <div class="select-info email-placeholders">
			<?php
			$placeholders        = get_site_option( "oasiswf_email_placeholders" );
			$custom_placeholders = apply_filters( 'oasiswf_emails_placeholders', '' );
			$placeholders        = is_array( $custom_placeholders ) ? array_merge( $placeholders, $custom_placeholders )
				: $placeholders;
			?>
            <ul>
                <li>
                   <span class="description">
                      <?php echo esc_html__( "Available template placeholders for email subject and content (Applicable to email recipients in the 'To' list only):",
	                      "oasisworkflow" ); ?>
                   </span>
                </li>
				<?php
				if ( $placeholders ) {
					foreach ( $placeholders as $k => $v ) {
						echo "<li><span class='description'>" . esc_html( $k ) . " - " . esc_html( $v ) .
						     "</span></li>";
					}
				}
				?>
            </ul>
        </div>
		<?php
	}

	/**
	 * Get Post Author
	 *
	 * @param $post_id
	 *
	 * @return array
	 * @since 4.6
	 */
	private function get_post_author( $post_id ) {
		$post_id = intval( $post_id );

		$user_ids    = array();
		$post_author = get_post_field( 'post_author', $post_id );
		array_push( $user_ids, $post_author );

		return $user_ids;
	}

	/**
	 * Get Site Administrators
	 *
	 * @return array
	 * @since 4.6
	 */
	private function get_administrators() {
		$user_ids = array();
		$args     = array(
			'blog_id'  => $GLOBALS['blog_id'],
			'role__in' => 'administrator',
			'fields'   => array( 'ID' )
		);
		$users    = get_users( $args );
		foreach ( $users as $user ) {
			array_push( $user_ids, $user->ID );
		}

		return $user_ids;
	}

	/**
	 * Get the user who submitted post to workflow
	 *
	 * @param $post_id
	 *
	 * @return array
	 * @since 4.6
	 */
	private function get_workflow_submitter( $post_id ) {
		global $wpdb;
		$post_id = intval( $post_id );

		$user_ids = array();
		$row      = $wpdb->get_row( $wpdb->prepare( "SELECT assign_actor_id FROM " . $wpdb->fc_action_history .
		                                            " WHERE action_status = 'submitted' AND post_id = %d", $post_id ) );

		if ( $row->assign_actor_id != 0 ) { // not system submitted
			array_push( $user_ids, $row->assign_actor_id );
		}

		return $user_ids;
	}

	/**
	 * Get current task assignees
	 *
	 * @param $post_id
	 *
	 * @return array
	 * @since 4.6
	 */
	private function get_assigned_task_recipients( $post_id ) {

		// sanitize post_id
		$post_id = intval( $post_id );

		$user_ids           = array();
		$ow_history_service = new OW_History_Service();
		$action_histories   = $ow_history_service->get_action_history_by_status( "assignment", $post_id );
		foreach ( $action_histories as $action_history ) {
			// if it's a review step, then get the actors from the fc_action table
			if ( $action_history->assign_actor_id == - 1 ) {
				$review_action_history = $ow_history_service->get_review_action_by_status( "assignment",
					$action_history->ID );
				foreach ( $review_action_history as $review_action ) {
					array_push( $user_ids, $review_action->actor_id );
				}
			} else {
				array_push( $user_ids, $action_history->assign_actor_id );
			}
		}

		return $user_ids;
	}

	/**
	 * Get current task assignees for workflow abort action
	 *
	 * @param $post_id
	 *
	 * @return array
	 * @since 4.6
	 */
	private function get_abort_workflow_recipients( $post_id ) {

		// sanitize post_id
		$post_id = intval( $post_id );

		$user_ids = array();

		// get current assignees for this aborted task
		$ow_history_service = new OW_History_Service();
		$action_histories   = $ow_history_service->get_action_history_by_status( "aborted", $post_id );

		// get the latest abort action only
		array_push( $user_ids, $action_histories[0]->assign_actor_id );

		$action_histories = $ow_history_service->get_action_history_by_status( "abort_no_action", $post_id );
		foreach ( $action_histories as $action_history ) {
			if ( $action_history->assign_actor_id == - 1 ) { // review process, then get the actors from fc_action
				$review_action_history = $ow_history_service->get_review_action_by_status( "abort_no_action",
					$action_history->ID );
				foreach ( $review_action_history as $review_action ) {
					array_push( $user_ids, $review_action->actor_id );
				}
			} else {
				array_push( $user_ids, $action_history->assign_actor_id );
			}
		}

		return $user_ids;
	}

	/**
	 * Get unclaimed users
	 *
	 * @param $post_id
	 *
	 * @return array
	 * @since 4.6
	 */
	private function get_task_unclaimed_recipients( $post_id ) {

		// sanitize post_id
		$post_id = intval( $post_id );

		$user_ids           = array();
		$ow_history_service = new OW_History_Service();
		$action_histories   = $ow_history_service->get_action_history_by_status( "claim_cancel", $post_id );
		foreach ( $action_histories as $action_history ) {
			array_push( $user_ids, $action_history->assign_actor_id );
		}

		return $user_ids;
	}

	/**
	 * Get additional users specified in the email type
	 *
	 * @param $additional_users
	 *
	 * @return array
	 * @since 4.6
	 */
	private function get_additional_users( $additional_users ) {
		$user_ids = array();

		if ( ! empty( $additional_users ) ) {
			$user_ids = array_merge( $user_ids, $additional_users );
		}

		return $user_ids;
	}

	/**
	 * Get email recipients for the email type
	 *
	 * @param $email_recipient_params
	 *
	 * @return array
	 * @since 4.6
	 * modified in version 7.2
	 */
	public function get_email_recipients( $email_recipient_params ) {
		$post_id = $email_recipient_params['post_id'];
		$post_id = intval( $post_id );

		$action = sanitize_text_field( $email_recipient_params["action"] );

		if ( ! empty ( $email_recipient_params['email_assignees'] ) ) {
			$email_assignees = $email_recipient_params['email_assignees'];
		}

		if ( ! empty ( $email_recipient_params['email_cc'] ) ) {
			$email_cc = $email_recipient_params['email_cc'];
		}

		if ( ! empty ( $email_recipient_params['email_bcc'] ) ) {
			$email_bcc = $email_recipient_params['email_bcc'];
		}

		if ( ! empty ( $email_assignees ) ) {
			$email_assignees_id      = $this->get_email_recipients_users_ids( $post_id, $action, $email_assignees );
			$unique_email_recipients = array_unique( $email_assignees_id );
		}

		if ( ! empty ( $email_cc ) ) {
			$email_cc_user_ids          = $this->get_email_recipients_users_ids( $post_id, $action, $email_cc );
			$unique_cc_email_recipients = array_unique( $email_cc_user_ids );
		}

		if ( ! empty ( $email_bcc ) ) {
			$email_bcc_user_ids          = $this->get_email_recipients_users_ids( $post_id, $action, $email_bcc );
			$unique_bcc_email_recipients = array_unique( $email_bcc_user_ids );
		}

		// remove duplicate user ids from Cc and Bcc list
		if ( $unique_cc_email_recipients ) {
			foreach ( $unique_cc_email_recipients as $key => $cc_recipients ) {
				if ( in_array( $cc_recipients, $unique_email_recipients ) ) :
					unset( $unique_cc_email_recipients[ $key ] );
				endif;
			}
		}

		if ( $unique_bcc_email_recipients ) {
			foreach ( $unique_bcc_email_recipients as $key => $bcc_recipients ) {
				if ( in_array( $bcc_recipients, $unique_email_recipients ) ) :
					unset( $unique_bcc_email_recipients[ $key ] );
				endif;
			}
		}

		$email_recipients = array(
			"email_assignees" => $unique_email_recipients,
			"email_cc"        => $unique_cc_email_recipients,
			"email_bcc"       => $unique_bcc_email_recipients
		);

		return $email_recipients;
	}
	
	public function get_cc_recipients( $email_recipient_params ) {
		$post_id = $email_recipient_params['post_id'];
		$post_id = intval( $post_id );
		$unique_cc_email_recipients = $unique_bcc_email_recipients = array();

		if ( ! empty ( $email_recipient_params['email_cc'] ) ) {
			$email_cc = $email_recipient_params['email_cc'];
		}

		if ( ! empty ( $email_recipient_params['email_bcc'] ) ) {
			$email_bcc = $email_recipient_params['email_bcc'];
		}

		if ( ! empty ( $email_cc ) ) {
			$email_cc_user_ids          = $this->get_email_recipients_users_ids( $post_id, '', $email_cc );
			$unique_cc_email_recipients = array_unique( $email_cc_user_ids );
		}

		if ( ! empty ( $email_bcc ) ) {
			$email_bcc_user_ids          = $this->get_email_recipients_users_ids( $post_id, '', $email_bcc );
			$unique_bcc_email_recipients = array_unique( $email_bcc_user_ids );
		}

		$email_recipients = array(
			"email_cc"        => $unique_cc_email_recipients,
			"email_bcc"       => $unique_bcc_email_recipients
		);

		return $email_recipients;
	}

	/**
	 * Get email recipients user ids
	 *
	 * @param int    $post_id
	 * @param string $action
	 * @param array  $email_assignees
	 *
	 * @return array $email_recipients
	 * @since 7.2
	 */
	public function get_email_recipients_users_ids( $post_id, $action, $email_assignees ) {
		$email_recipients = array();

		// Sanitize the data
		$roles          = array_map( 'esc_attr', $email_assignees["roles"] );
		$users          = array_map( 'esc_attr', $email_assignees["users"] );
		$external_users = array_map( 'esc_attr', $email_assignees["external_users"] );

		// Include file to avoid php errors.
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}
		$participating_roles = OW_Utility::instance()->get_participating_roles();

		foreach ( $roles as $role ) {
			// Post Author(s) role
			if ( $role == 'post_author' ) {
				$user_ids         = $this::get_post_author( $post_id );
				$email_recipients = array_merge( $email_recipients, $user_ids );
			}

			// Post Submitter
			if ( $role == 'post_submitter' ) {
				$user_ids         = $this::get_workflow_submitter( $post_id );
				$email_recipients = array_merge( $email_recipients, $user_ids );
			}

			if ( $role == 'current_task_assignees' ) {
				switch ( $action ) {
					case "workflow-abort" :
						$user_ids         = $this->get_abort_workflow_recipients( $post_id );
						$email_recipients = array_merge( $email_recipients, $user_ids );
						break;
					case "task-claimed" :
						$user_ids         = $this->get_task_unclaimed_recipients( $post_id );
						$email_recipients = array_merge( $email_recipients, $user_ids );
						break;
					case "unauthorized-update" :
						$user_ids         = $this->get_assigned_task_recipients( $post_id );
						$email_recipients = array_merge( $email_recipients, $user_ids );
						break;
				}
			}

			// For System roles and custom roles
			if ( array_key_exists( $role, $participating_roles ) ) {
				$user_ids         = OW_Utility::instance()->get_roles_user_id( $role );
				$email_recipients = array_merge( $email_recipients, $user_ids );
			}
		}

		// Users
		if ( ! empty( $users ) ) {
			$user_ids         = $this::get_additional_users( $users );
			$email_recipients = array_merge( $email_recipients, $user_ids );
		}

		// External Users
		if ( ! empty( $external_users ) ) {
			$user_ids         = $this::get_additional_users( $external_users );
			$email_recipients = array_merge( $email_recipients, $user_ids );
		}

		return $email_recipients;
	}

	/**
	 * Get mail content from the email template configuration
	 * Replace placeholders with actual value
	 *
	 * @param $email_params
	 *
	 * @return array
	 */
	public function get_email_content( $email_params ) {
		/* sanitize the input */
		$email_to = $email_params['email_to'];
		$email_to = intval( $email_to );

		$post_id = $email_params['post_id'];
		$post_id = intval( $post_id );

		$email_subject = $email_params['email_subject'];
		$email_subject = sanitize_text_field( trim( $email_subject ) );

		$email_content = esc_html( $email_params['email_content'] );
		$email_content = wpautop( $email_content, false );

		/*
		   * Replace the placeholders with actual value
		   */
		$ow_placeholders = new OW_Place_Holders();

		$mail_content = array( 'subject' => $email_subject, 'message' => $email_content );

		// replace the placeholders
		$callback_custom_placeholders = array();
		if ( has_filter( 'oasiswf_emails_placeholders_handler' ) ) {
			$callback_custom_placeholders = apply_filters( 'oasiswf_emails_placeholders_handler', $post_id );
		}

		foreach ( $mail_content as $k => $v ) {
			$v = str_replace( OW_Email_Settings_Helper::FIRST_NAME,
				$ow_placeholders->get_first_name( $email_to ), $v );
			$v = str_replace( OW_Email_Settings_Helper::LAST_NAME,
				$ow_placeholders->get_last_name( $email_to ), $v );
			$v = str_replace( OW_Email_Settings_Helper::POST_CATEGORY,
				$ow_placeholders->get_post_categories( $post_id ), $v );
			$v = str_replace( OW_Email_Settings_Helper::POST_LAST_MODIFIED_DATE,
				$ow_placeholders->get_post_last_modified_date( $post_id ), $v );
			$v = str_replace( OW_Email_Settings_Helper::POST_PUBLISH_DATE,
				$ow_placeholders->get_post_publish_date( $post_id ), $v );
			$v = str_replace( OW_Email_Settings_Helper::POST_AUTHOR,
				$ow_placeholders->get_author_display_name( $post_id ), $v );
			$v = str_replace( OW_Email_Settings_Helper::POST_TITLE,
				$this->get_post_title( $post_id, true ), $v );
			$v = str_replace( OW_Email_Settings_Helper::POST_ID,
				intval( sanitize_text_field( $post_id ) ), $v );
			$v = str_replace( OW_Email_Settings_Helper::BLOG_NAME,
				addslashes( get_bloginfo( 'name' ) ), $v );
			$v = str_replace( OW_Email_Settings_Helper::CURRENT_USER,
				$ow_placeholders->get_first_name( get_current_user_id() ), $v );

			if ( ! empty( $callback_custom_placeholders ) ) {
				foreach ( $callback_custom_placeholders as $ki => $vi ) {
					if ( strpos( $v, $ki ) !== false ) {
						$v = str_replace( $ki, $vi, $v );
					}
				}
			}

			$mail_content[ $k ] = $v;
		}

		return $mail_content;
	}

	/**
	 * get post title
	 *
	 * @param int     $post_id
	 * @param boolean $link , if true returns title as link.
	 *
	 * @return string post title as a link, if true
	 * @since 4.6
	 */
	public function get_post_title( $post_id, $link = true ) {
		// sanitize the input
		$post_id = intval( sanitize_text_field( $post_id ) );

		// get post details
		$post       = get_post( $post_id );
		$post_title = stripcslashes( $post->post_title );
		$post_url   = esc_url( get_permalink( $post_id ) );

		if ( $link ) {
			$post_link = '<a href="' . $post_url . '" target="_blank">' . esc_html( $post_title ) . '</a>';
		} else {
			$post_link = '"' . esc_html( $post_title ) . '"';
		}

		return $post_link;
	}

	/**
	 * Get email roles list
	 *
	 * @param string $action
	 * @param array  $selected_roles
	 *
	 * @return string $options
	 * @since 7.2
	 */
	public function get_email_roles_option_list( $action, $selected_roles ) {
		$participating_roles = get_option( 'oasiswf_participating_roles_setting' );

		// add our custom role "Post Author" and "Post Submitter" to this list
		$participating_roles['post_author']    = esc_html__( 'Post Author', 'oasisworkflow' );
		$participating_roles['post_submitter'] = esc_html__( 'Post Submitter', 'oasisworkflow' );

		if ( ( $action == OW_Email_Settings_Helper::UNAUTHORIZED_UPDATE_EMAIL ||
		       $action == OW_Email_Settings_Helper::TASK_CLAIMED_EMAIL ||
		       $action == OW_Email_Settings_Helper::WORKFLOW_ABORT_EMAIL ) ) {
			$participating_roles['current_task_assignees'] = esc_html__( 'Current Task Assignees', 'oasisworkflow' );
		}

		asort( $participating_roles );

		$options = '<optgroup label="' . esc_attr__( 'Roles', 'oasisworkflow' ) . '">';
		foreach ( $participating_roles as $role => $name ) {
			if ( ! empty( $selected_roles ) && is_array( $selected_roles ) &&
			     in_array( esc_attr( $role ), $selected_roles ) ) { // preselect specified role
				$selected = " selected='selected' ";
			} else {
				$selected = '';
			}
			$options .= "<option value='r@{$role}' $selected>$name</option>";
		}
		$options .= '</optgroup>';

		return $options;
	}

	/**
	 * Get email users list
     *
     * @param $selected_users
	 *
	 * @return string
	 */
	public function get_email_users_option_list( $selected_users ) {
		$participants   = get_option( 'oasiswf_participating_roles_setting' );
		$user_role_keys = array_keys( $participants );

		// get all registered users in the site
		$args  = array(
			'blog_id'  => $GLOBALS['blog_id'],
			'role__in' => $user_role_keys,
			'fields'   => array( 'ID', 'display_name' )
		);
		$users = get_users( $args );

		$options = '<optgroup label="' . esc_attr__( 'Users', 'oasisworkflow' ) . '">';
		foreach ( $users as $user ) {
			if ( ! empty( $selected_users ) && is_array( $selected_users ) &&
			     in_array( $user->ID, $selected_users ) ) { // preselect specified role
				$selected = " selected='selected' ";
			} else {
				$selected = '';
			}
			$options .= "<option value='u@{$user->ID}' $selected>".esc_html($user->display_name)."</option>";
		}

		$options .= '</optgroup>';

		return $options;
	}

	/**
	 * Get email external users list
     *
     * @param $selected_external_users
	 *
	 * @return string
	 */
	public function get_email_external_users_option_list( $selected_external_users ) {
		$external_users = get_option( "oasiswf_external_user_settings" );

		$options = '<optgroup label="' . esc_attr__( 'External Users', 'oasisworkflow' ) . '">';

		if ( $external_users ) {
			foreach ( $external_users as $key => $values ) {
//            invalid entry, because either email address or first/last name is null, so ignore and continue
				if ( ! $values['email'] || ( ! $values['fname'] && ! $values['lname'] ) ) {
					continue;
				}
				if ( ! empty( $selected_external_users ) && is_array( $selected_external_users ) &&
				     in_array( $key, $selected_external_users ) ) { // preselect specified role
					$selected = " selected='selected' ";
				} else {
					$selected = '';
				}
				$options .= "<option value='e@{$key}' $selected>" . $values['fname'] . " " . $values['lname'] .
				            "</option>";
			}
		}
		$options .= '</optgroup>';

		return $options;
	}


}

// construct an instance so that the actions get loaded
$ow_email_settings_helper = new OW_Email_Settings_Helper();
?>