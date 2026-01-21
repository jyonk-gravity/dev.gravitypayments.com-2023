<?php
/*
 * Service class for Inbox
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

/*
 * OW_Inbox_Service Class
 *
 * @since 2.0
 */

class OW_Inbox_Service {

	/*
	 * Set things up.
	 *
	 * @since 2.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_get_edit_inline_html', array( $this, 'get_edit_inline_html' ) );
		add_action( 'wp_ajax_get_step_signoff_page', array( $this, 'get_step_signoff_page' ) );
		add_action( 'wp_ajax_get_reassign_page', array( $this, 'get_reassign_page' ) );
		add_action( 'wp_ajax_get_step_comment_page', array( $this, 'get_step_comment_page' ) );
	}

	/**
	 * AJAX function - Get the inline edit data
	 * TODO: see if we can find an alternative for this.
	 */
	public function get_edit_inline_html() {
		global $current_screen;

		// nonce check
		check_ajax_referer( 'owf_inbox_ajax_nonce', 'security' );

		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );

		$current_screen->post_type = isset( $_POST["post_type"] ) ? sanitize_text_field( $_POST["post_type"] ) : "";
		$wp_list_table->inline_edit();
		wp_send_json_success();
	}

	/**
	 * AJAX function - Get step sign off page
	 *
	 */
	public function get_step_signoff_page() {
		// nonce check
		check_ajax_referer( 'owf_inbox_ajax_nonce', 'security' );

		ob_start();
		include( OASISWF_PATH . "includes/pages/subpages/submit-step.php" );
		$result = ob_get_contents();
		ob_end_clean();

		wp_send_json_success( htmlentities( $result ) );
	}

	/**
	 * AJAX function - Get reassign page
	 *
	 */
	public function get_reassign_page() {
		// nonce check
		// If its post edit page
		if ( isset( $_POST["screen"] ) && sanitize_text_field( $_POST["screen"] ) == "edit" ) {  // phpcs:ignore
			check_ajax_referer( 'owf_signoff_ajax_nonce', 'security' );
		} else {
			check_ajax_referer( 'owf_inbox_ajax_nonce', 'security' );
		}

		$history_id = isset( $_POST["oasiswf"] ) ? trim( sanitize_text_field( $_POST["oasiswf"] ) ) : "";
		// sanitize data
		$history_id = intval( $history_id );

		$task_user = isset( $_POST["task_user"] ) ? trim( sanitize_text_field( $_POST["task_user"] ) )
			: get_current_user_id();

		$users = $this->get_reassign_users( $history_id );

		$assignees = array();

		// no self-reassign
		foreach ( $users as $key => $user ) {
			if ( $user->ID != $task_user ) {
				array_push( $assignees, $user );
			}
		}

		$user_count = count( $assignees );

		if ( 0 === $user_count ) {
			wp_send_json_success( array( "reassign_users" => 0 ) );
		} else {
			ob_start();
			include( OASISWF_PATH . "includes/pages/subpages/reassign.php" );
			$result = ob_get_contents();
			ob_end_clean();

			wp_send_json_success( array( "reassign_users" => htmlentities( $result ) ) );
		}
	}

	/**
	 * Get reassign users
	 *
	 * @param int $history_id
	 * @param int $task_user
	 *
	 * @return array $users
	 * @since 6.8
	 */
	public function get_reassign_users( $history_id ) {

		// sanitize data
		$history_id = intval( $history_id );

		$ow_process_flow    = new OW_Process_Flow();
		$ow_history_service = new OW_History_Service();
		$workflow_service   = new OW_Workflow_Service();

		$history_details = $ow_history_service->get_action_history_by_id( $history_id );
		$team_id         = get_post_meta( $history_details->post_id, '_oasis_is_in_team', true );
		$users           = array();
		if ( $team_id != null && method_exists( 'OW_Teams_Service', 'get_team_members' ) ) {
			$step             = $workflow_service->get_step_by_id( $history_details->step_id );
			$step_info        = json_decode( $step->step_info );
			$assignee_roles   = isset( $step_info->task_assignee->roles )
				? array_flip( $step_info->task_assignee->roles ) : null;
			$ow_teams_service = new OW_Teams_Service();
			$users_ids        = $ow_teams_service->get_team_members( $team_id, $assignee_roles,
				$history_details->post_id );
			foreach ( $users_ids as $user_id ) {
				$user = get_userdata( $user_id );
				array_push( $users, $user );
			}
		} else {
			$user_info = $ow_process_flow->get_users_in_step( $history_details->step_id );
			$users     = $user_info["users"];
		}

		return $users;
	}

	/**
	 * Display workflow comments popup
	 */
	public function get_step_comment_page() {
		// nonce check
		check_ajax_referer( 'owf_inbox_ajax_nonce', 'security' );

		ob_start();
		include( OASISWF_PATH . "includes/pages/subpages/action-comments.php" );
		$result = ob_get_contents();
		ob_end_clean();

		wp_send_json_success( htmlentities( $result ) );
	}

	/**
	 * generate the table header for the inbox page
	 *
	 * @return mixed
	 */
	public function get_table_header() {

		// phpcs:ignore
		$sortby = ( isset( $_GET['order'] ) && sanitize_text_field( $_GET["order"] ) == "desc" ) ? "asc" : "desc";

		// sorting the inbox page via Author, Due Date, Post title and Post Type
		$author_class = $workflow_class = $due_date_class = $post_order_class = $post_type_class = $priority_class = '';
		// phpcs:ignore
		if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
			$orderby = sanitize_text_field( $_GET['orderby'] );
			switch ( $orderby ) {
				case 'author':
					$author_class = $sortby;
					break;
				case 'due_date':
					$due_date_class = $sortby;
					break;
				case 'post_title':
					$post_order_class = $sortby;
					break;
				case 'post_type':
					$post_type_class = $sortby;
					break;
				case 'priority':
					$priority_class = $sortby;
					break;
			}
		}
		$workflow_terminology_options = get_option( 'oasiswf_custom_workflow_terminology' );
		$due_date                     = ! empty( $workflow_terminology_options['dueDateText'] )
			? $workflow_terminology_options['dueDateText'] : esc_html__( 'Due Date', 'oasisworkflow' );
		$priority                     = ! empty( $workflow_terminology_options['taskPriorityText'] )
			? $workflow_terminology_options['taskPriorityText'] : esc_html__( 'Priority', 'oasisworkflow' );

		$inbox_column_headers['checkbox']
			                           = "<td scope='col' class='manage-column column-cb check-column'><input type='checkbox'></td>";
		$sorting_args                  = add_query_arg( array( 'orderby' => 'post_title', 'order' => $sortby ) );
		$inbox_column_headers['title'] = "<th width='300px' scope='col' class='column-primary sorted $post_order_class'>
		<a href='" . esc_url_raw( $sorting_args ) . "'>
		<span>" . esc_html__( "Post/Page", "oasisworkflow" ) . "</span>
					<span class='sorting-indicator'></span>
				</a>
				</th>";

		if ( get_option( 'oasiswf_priority_setting' ) == 'enable_priority' ) {
			$sorting_args                     = add_query_arg( array( 'orderby' => 'priority', 'order' => $sortby ) );
			$inbox_column_headers['priority'] = "<th scope='col' class='manage-column sorted column-priority $priority_class'>
		          <a href='" . esc_url_raw( $sorting_args ) . "'>
		             <span>" . $priority . "</span>
		             <span class='sorting-indicator'></span>
		          </a>
              </th>";
		}

		$sorting_args                   = add_query_arg( array( 'orderby' => 'post_type', 'order' => $sortby ) );
		$inbox_column_headers['type']   = "<th scope='col' class='sorted column-type $post_type_class'>
		<a href='" . esc_url_raw( $sorting_args ) . "'>
		<span>" . esc_html__( "Type", "oasisworkflow" ) . "</span>
					<span class='sorting-indicator'></span>
			</a>
			</th>";
		$sorting_args                   = add_query_arg( array( 'orderby' => 'post_author', 'order' => $sortby ) );
		$inbox_column_headers['author'] = "<th scope='col' class='sorted column-author $author_class'>
		<a href='" . esc_url_raw( $sorting_args ) . "'>
		<span>" . esc_html__( "Author", "oasisworkflow" ) . "</span>
					<span class='sorting-indicator'></span>
			</a>
			</th>";

		$inbox_column_headers['last_signoff_by'] = "<th class='column-last-signoff-by'>" .
		                                           esc_html__( "Last Signed off By", "oasisworkflow" ) . "</th>";

		$inbox_column_headers['workflow_name'] = "<th class='column-workflow-name'>" .
		                                         esc_html__( "Workflow [Step]", "oasisworkflow" ) . "</th>";

		$inbox_column_headers['category'] = "<th class='column-category'>" . esc_html__( "Category", "oasisworkflow" ) .
		                                    "</th>";

		$sorting_args                     = add_query_arg( array( 'orderby' => 'due_date', 'order' => $sortby ) );
		$inbox_column_headers['due_date'] = "<th scope='col' class='sorted column-due-date $due_date_class'>
		<a href='" . esc_url_raw( $sorting_args ) . "'>
					<span>" . esc_html( $due_date ) . "</span>
					<span class='sorting-indicator'></span>
			</a>
			</th>";

		// allow for add/remove of inbox column header via a filter
		if ( has_filter( 'owf_manage_inbox_column_headers' ) ) {
			$inbox_column_headers = apply_filters( 'owf_manage_inbox_column_headers', $inbox_column_headers );
		}
		$inbox_column_headers['comments'] = "<th class='column-comments'>" . esc_html__( "Comments", "oasisworkflow" ) .
		                                    "</th>";


		return $inbox_column_headers;
	}

	/**
	 * generate the table rows for the inbox page
	 *
	 * @param $inbox_data
	 * @param $inbox_items
	 * @param $inbox_column_headers
	 * @param int $unclaimed_task_count
	 */
	public function get_table_rows( $inbox_data, $inbox_items, $inbox_column_headers, $unclaimed_task_count = 0 ) {

		global $ow_custom_statuses;

		$page_number   = intval( $inbox_data["page_number"] );
		$per_page      = intval( $inbox_data["per_page"] );
		$selected_user = intval( $inbox_data["selected_users"] );

		$ow_process_flow     = new OW_Process_Flow();
		$ow_workflow_service = new OW_Workflow_Service();
		$ow_history_service  = new OW_History_Service();

		if ( $inbox_items ):
			$count = 0;
			$start = ( $page_number - 1 ) * $per_page;
			$end   = $start + $per_page;
			foreach ( $inbox_items as $inbox_item ) {
				if ( $count >= $end ) {
					break;
				}
				if ( $count >= $start ) {
					if ( has_action( 'owf_manage_inbox_items_row_start' ) ) {
						do_action( 'owf_manage_inbox_items_row_start', $inbox_item );
					}
					$post = get_post( $inbox_item->post_id );

					$cat_name = OW_Utility::instance()->get_post_categories( $inbox_item->post_id );
					$user     = get_userdata( $post->post_author );
					$stepId   = $inbox_item->step_id;
					if ( $stepId <= 0 || $stepId == "" ) {
						$stepId = $inbox_item->review_step_id;
					}
					$step     = $ow_workflow_service->get_step_by_id( $stepId );
					$workflow = $ow_workflow_service->get_workflow_by_id( $step->workflow_id );

					$needs_to_be_claimed = $ow_process_flow->check_for_claim( $inbox_item->ID );

					// Get last sign-off by
					if ( $inbox_item->assign_actor_id == - 1 ) {
						$review_rows = $ow_history_service->get_review_action_by_status( "complete", $inbox_item->ID );
						// If only one user is assigned the task
						if ( $review_rows ) {
							$comments = json_decode( $review_rows[0]->comments );
						} else {
							$comments = json_decode( $inbox_item->comment );
						}
						$send_by     = $comments[0]->send_id;
						$sign_off_by = OW_Utility::instance()->get_user_name( $send_by );
					} else {
						$comments    = json_decode( $inbox_item->comment );
						$send_by     = $comments[0]->send_id;
						$sign_off_by = OW_Utility::instance()->get_user_name( $send_by );
					}

					$original_post_id = get_post_meta( $inbox_item->post_id, '_oasis_original', true );
					/* Check due date and make post item background color in red to notify the admin */
					$ow_email = new OW_Email();

					$current_date = gmdate( " F j, Y " );

					$due_date = OW_Utility::instance()->format_date_for_display( $inbox_item->due_date );

					$past_due_date_row_class   = '';
					$past_due_date_field_class = '';
					if ( $due_date != "" && strtotime( $due_date ) < strtotime( $current_date ) ) {
						$past_due_date_row_class   = 'past-due-date-row';
						$past_due_date_field_class = 'past-due-date-field';
					}
					echo "<tr id='post-" . esc_attr( $inbox_item->post_id ) . "'
                        	class='post-" . esc_attr( $inbox_item->post_id ) . " post type-post " .
					     esc_attr( $past_due_date_row_class ) . "
                        	status-pending format-standard hentry category-uncategorized alternate iedit author-other'> ";
					$workflow_post_id = esc_attr( $inbox_item->post_id );
					if ( array_key_exists( 'checkbox', $inbox_column_headers ) ) {
						echo "<th scope='row' class='check-column'>
                              <input type='checkbox' name='post[]' value=" . esc_attr( $workflow_post_id ) .
						     " wfid='" . esc_attr( $inbox_item->ID ) . "'><div class='locked-indicator'></div></th>";
					}

					if ( array_key_exists( 'title', $inbox_column_headers ) ) {
                        $_post_states = _post_states( $post, false );
                        $_post_states = apply_filters( 'owf_inbox_title_states', $_post_states, $post );
						echo '<td class="post-title page-title column-title column-primary" data-colname="Post/Page"><strong>' .
						     esc_html( $post->post_title );
						// TODO : see if we can find a better solution instead of using _post_states
						echo $_post_states; // phpcs:ignore
						echo "</strong>";
						// create the action list
						if ( $needs_to_be_claimed ) { // if the item needs to be claimed, only "Claim" action is visible
							$claim_row_actions = "<div class='row-actions'>";
                            $claim_row_actions_items = "<span>
                                <a href='#' class='claim' id='claim' userid=" . esc_attr( $selected_user ) .
                                        " actionid=" . esc_attr( $inbox_item->ID ) . ">" .
                                        esc_html__( "Claim", "oasisworkflow" ) . "</a>
                                <span class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                            </span>
                            |
                            <span>
                                <a href='#' id='claim-and-edit' class='claim-and-edit' userid=" .
                                        esc_attr( $selected_user ) . " actionid=" .
                                        esc_attr( $inbox_item->ID ) . ">" .
                                        esc_html__( "Claim and Edit", "oasisworkflow" ) . "</a>
                                <span class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                            </span>";

                            if( current_user_can( 'ow_view_others_inbox' ) && get_current_user_id() !== $selected_user ) {
                                $claim_row_actions_items .= "|
                                <span>
                                    <a href='#' title='". esc_attr__( 'Send a reminder email', 'oasisworkflow' ) ."' class='nudge_poker' userid=" .
                                            esc_attr( $selected_user ) . " wfid=" .
                                            esc_attr( $inbox_item->ID ) . ">" .
                                            esc_html__( "Nudge", "oasisworkflow" ) . "</a>
                                    <span class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                </span>";
                            }

                            if ( has_filter( 'owf_inbox_row_claim_actions' ) ) {
								$claim_row_actions = apply_filters( 'owf_inbox_row_claim_actions', $claim_row_actions, $inbox_item );
							}

                            $claim_row_actions .= $claim_row_actions_items;
                            $claim_row_actions .= "</div>";
							
							/**
							 * Get allowed HTML for the \WP_Post object
							 *
							 * @see https://developer.wordpress.org/reference/functions/wp_kses_allowed_html/
							 */
							$allowed_html = wp_kses_allowed_html( 'post' );

							// Add additional attributes to anchor element
							$allowed_html['a']['userid']   = true;
							$allowed_html['a']['actionid'] = true;
							$allowed_html['a']['postid'] = true;
							$allowed_html['a']['wfid'] = true;

							/**
							 * Sanitize the HTML while echo-ing
							 * Pass the collected HTML array as parameter
							 *
							 * @see https://developer.wordpress.org/reference/functions/wp_kses/
							 */
							echo wp_kses( $claim_row_actions, $allowed_html );
						} else {
							echo "<div class='row-actions'>";

							$inbox_row_actions_data = array(
								"post_id"             => $inbox_item->post_id,
								"user_id"             => $selected_user,
								"workflow_history_id" => $inbox_item->ID,
								"original_post_id"    => $original_post_id
							);

							$inbox_row_actions = $this->display_row_actions( $inbox_row_actions_data );
							// allow for add/remove of inbox actions via a filter
							if ( has_filter( 'owf_inbox_row_actions' ) ) {
								$inbox_row_actions = apply_filters( 'owf_inbox_row_actions', $inbox_row_actions_data,
									$inbox_row_actions, $inbox_item );
							}
                            $inbox_row_actions = apply_filters( 'owf_inbox_row_actions_list', $inbox_row_actions,
									$inbox_row_actions_data, $inbox_item );
							$action_count = count( $inbox_row_actions );
							$i            = 0;

							foreach ( $inbox_row_actions as $action ) {
								++ $i;
								( $i == $action_count ) ? $sep = '' : $sep = ' | ';
								// $action is already sanitized via display_row_actions
								echo "<span>" . $action . esc_attr( $sep ) . "</span>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							echo "</div>";
							get_inline_data( $post );
						}
						echo '<button type="button" class="toggle-row"><span class="screen-reader-text">' . esc_html__('Show more details', 'oasisworkflow') . '</span></button></td>';
					}

					if ( array_key_exists( 'priority', $inbox_column_headers ) ) {
						if ( get_option( 'oasiswf_priority_setting' ) == 'enable_priority' ) {
							//priority settings
							$priority = get_post_meta( $post->ID, '_oasis_task_priority', true );
							if ( empty( $priority ) ) {
								$priority = '2normal';
							}

							$priority_array = OW_Utility::instance()->get_priorities();
							$priority_value = $priority_array[ $priority ];
							// the CSS is defined without the number part
							$css_class = substr( $priority, 1 );
							echo "<td data-colname='Priority' class='column-priority' ><p class='post-priority " .
							     esc_attr( $css_class ) . "-priority'>" .
							     esc_html( $priority_value ) . "</p></td>";
						}
					}

					if ( array_key_exists( 'type', $inbox_column_headers ) ) {
						$post_type_obj = get_post_type_object( get_post_type( $inbox_item->post_id ) );
						echo "<td data-colname='Type' class='column-type'>" .
						     esc_html( $post_type_obj->labels->singular_name ) .
						     "</td>";
					}

					if ( array_key_exists( 'author', $inbox_column_headers ) ) {
						echo "<td data-colname='Author' class='column-author'>" .
						     esc_html( OW_Utility::instance()->get_user_name( $user->ID ) ) . "</td>";
					}

					if ( array_key_exists( 'last_signoff_by', $inbox_column_headers ) ) {
						echo "<td data-colname='Last Signed off By' class='column-last-signoff-by'>" .
						     esc_html( $sign_off_by ) .
						     "</td>";
					}

					if ( array_key_exists( 'workflow_name', $inbox_column_headers ) ) {
						$workflow_name = $workflow->name;
						if ( ! empty( $workflow->version ) ) {
							$workflow_name .= " (" . $workflow->version . ")";
						}

						echo "<td data-colname='Workflow [Step]' class='column-workflow-name'>" .
						     esc_html( $workflow_name ) .
						     " [" . esc_html( $ow_workflow_service->get_gpid_dbid( $workflow->ID, $stepId, 'lbl' ) ) .
						     "]</td>";
					}

					if ( array_key_exists( 'category', $inbox_column_headers ) ) {
						echo "<td data-colname='Category' class='column-category'>" . esc_html( $cat_name ) . "</td>";
					}

					if ( array_key_exists( 'due_date', $inbox_column_headers ) ) {
						// if the due date is passed the current date show the field in a different color
						echo "<td data-colname='Due Date' class='column-due-date'><span class='" .
						     esc_attr( $past_due_date_field_class ) . "'>" .
						     esc_html( OW_Utility::instance()->format_date_for_display( $inbox_item->due_date ) ) . "</span></td>";
					}

					if ( has_filter( 'owf_manage_inbox_column_content' ) ) {
						apply_filters( 'owf_manage_inbox_column_content', $inbox_column_headers, $inbox_item );
					}

					if ( array_key_exists( 'comments', $inbox_column_headers ) ) {
                        $total_comments = $ow_process_flow->get_sign_off_comments_count_by_post_id( $inbox_item->post_id );
                        $total_comments = apply_filters( 'ow_inbox_comments_counter', $total_comments, $inbox_item->post_id );
						$comment_row_actions = "<td class='comments column-comments' data-colname='Comments'>
                                 <div class='post-com-count-wrapper'>
                                    <strong>
                                       <a href='#' actionid= " . esc_attr( $inbox_item->ID ) . " class='post-com-count post-com-count-approved' data-comment='inbox_comment' post_id=" . esc_attr( $inbox_item->post_id ) . ">
                                          <span class='comment-count-approved'>" .
						                       esc_html( $total_comments ) . "</span>
                                       </a>
                                       <span class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                    </strong>
                                 </div>
                                </td>";
						if ( has_filter( 'owf_inbox_row_comment_actions' ) ) {
							$comment_row_actions = apply_filters( 'owf_inbox_row_comment_actions', $comment_row_actions,
								$inbox_item );
						}
						/**
						 * Get allowed HTML for the \WP_Post object
						 *
						 * @see https://developer.wordpress.org/reference/functions/wp_kses_allowed_html/
						 */
						$allowed_html = wp_kses_allowed_html( 'post' );

						// Add additional attributes to anchor element
						$allowed_html['a']['actionid']     = true;
						$allowed_html['a']['data-comment'] = true;
						$allowed_html['a']['post_id']      = true;

						/**
						 * Sanitize the HTML while echo-ing
						 * Pass the collected HTML array as parameter
						 *
						 * @see https://developer.wordpress.org/reference/functions/wp_kses/
						 */
						echo wp_kses( $comment_row_actions, $allowed_html );
					}

					echo "</tr>";
					if ( has_action( 'owf_manage_inbox_items_row_end' ) ) {
						do_action( 'owf_manage_inbox_items_row_end', $inbox_item );
					}
				}
				$count ++;
			}
		elseif ( $unclaimed_task_count > 0 ):
			$else_unclaimed_task_message = esc_html__( ' But there are currently ', 'oasisworkflow' )
			                               .
			                               sprintf( '<a href="admin.php?page=oasiswf-inbox&action=inbox-unclaimed&user=%s">%s',
				                               esc_html( $selected_user ), esc_html( $unclaimed_task_count ) )
			                               . esc_html__( ' unclaimed task(s).', 'oasisworkflow' )
			                               . '</a>';
			echo "<tr>";
			echo "<td class='hurry-td' colspan='8'>
								<label class='hurray-lbl'>";
			// attribute is already sanitized above
			echo esc_html__( "Hurray! No assignments.", "oasisworkflow" ) . $else_unclaimed_task_message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "</label></td>";
			echo "</tr>";
		else:
			echo "<tr>";
			echo "<td class='hurry-td' colspan='8'>
								<label class='hurray-lbl'>";
			echo esc_html__( "Hurray! No assignments.", "oasisworkflow" );
			echo "</label></td>";
			echo "</tr>";
		endif;
	}

	/**
	 *
	 * Display row actions on inbox page
	 *
	 * @param $inbox_row_actions_data
	 *
	 * @return mixed
	 */
	public function display_row_actions( $inbox_row_actions_data ) {

		$post_id             = intval( $inbox_row_actions_data["post_id"] );
		$user_id             = intval( $inbox_row_actions_data["user_id"] );
		$workflow_history_id = intval( $inbox_row_actions_data["workflow_history_id"] );
		$original_post_id    = intval( $inbox_row_actions_data["original_post_id"] );

		$space = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		// get workflow settings for compare button
		$hide_compare_button = get_option( "oasiswf_hide_compare_button" );

		// get custom terminology
		$workflow_terminology_options = get_option( 'oasiswf_custom_workflow_terminology' );
		$sign_off_label               = ! empty( $workflow_terminology_options['signOffText'] )
			? $workflow_terminology_options['signOffText'] : esc_html__( 'Sign Off', 'oasisworkflow' );
		$abort_workflow_label         = ! empty( $workflow_terminology_options['abortWorkflowText'] )
			? $workflow_terminology_options['abortWorkflowText'] : esc_html__( 'Abort Workflow', 'oasisworkflow' );

		// Set the row action
		$inbox_row_actions = array();
		if ( OW_Utility::instance()->is_post_editable_others( $post_id ) ) {
			$inbox_row_actions['edit'] = "<a href='" . get_admin_url() .
			                             "post.php?post={$post_id}&action=edit&oasiswf={$workflow_history_id}&user={$user_id}' class='edit' real={$post_id}>" .
			                             esc_html__( "Edit", "oasisworkflow" ) . "</a>";
		}

		if ( did_action( 'elementor/loaded' ) &&
		     \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor() &&
		     OW_Utility::instance()->is_post_editable_others( $post_id ) ) {
			$inbox_row_actions['edit_with_elementor']
				= "<a href='post.php?post={$post_id}&action=elementor&oasiswf={$workflow_history_id}&user={$user_id}' class='edit' real={$post_id}>" .
				  esc_html__( "Edit with Elementor", "oasisworkflow" ) . "</a>";
		}

		if ( OW_Utility::instance()->is_post_editable_others( $post_id ) ) {
			$inbox_row_actions['view'] = "<a target='_blank' href='" . get_preview_post_link( $post_id ) . "'>" .
			                             esc_html__( "View", "oasisworkflow" ) . "</a>";
		}

		if ( $hide_compare_button == "" && $original_post_id && OW_Utility::instance()->is_post_editable( $post_id ) ) {
			$inbox_row_actions['compare'] = "<a href='post.php?page=oasiswf-revision&revision={$post_id}&_nonce=" .
			                                wp_create_nonce( 'owf_compare_revision_nonce' ) .
			                                "' wfid='$workflow_history_id' class='compare-post-revision' postid='$post_id'>" .
			                                esc_html__( "Compare", "oasisworkflow" ) .
			                                "</a><span class='loading'>$space</span>";
		}

		if ( current_user_can( 'ow_sign_off_step' ) && OW_Utility::instance()->is_post_editable( $post_id ) ) {
			$inbox_row_actions['sign_off']
				= "<a href='#' wfid='$workflow_history_id' postid='$post_id' class='quick_sign_off'>" .
				  $sign_off_label . "</a><span class='loading'>$space</span>";
		}

		if ( current_user_can( 'ow_reassign_task' ) ) {
			$inbox_row_actions['reassign'] = "<a href='#' wfid='$workflow_history_id' class='reassign'>" .
			                                 esc_html__( "Reassign", "oasisworkflow" ) .
			                                 "</a><span class='loading'>$space</span>";
		}

		if ( current_user_can( 'ow_abort_workflow' ) ) {
			$inbox_row_actions['abort_workflow']
				= "<a href='#' wfid='$workflow_history_id' postid='$post_id' class='abort_workflow'>" .
				  $abort_workflow_label . "</a><span class='loading'>" . esc_html( $space ) . "</span>";
		}

		if ( current_user_can( 'ow_view_workflow_history' ) ) {
			$nonce_url                         = wp_nonce_url( "admin.php?page=oasiswf-history&post=$post_id",
				'owf_view_history_nonce' );
			$inbox_row_actions['view_history'] = "<a href='$nonce_url'> " .
			                                     esc_html__( "View History", "oasisworkflow" ) .
			                                     "</a>";
		}

        if( current_user_can( 'ow_view_others_inbox' ) && get_current_user_id() !== $user_id ) {
            $inbox_row_actions['nudge_poker']
				= "<a href='#' wfid='$workflow_history_id' title='". esc_attr__( 'Send a reminder email', 'oasisworkflow' ) ."' user='$user_id' class='nudge_poker'>" .
				  esc_html__( 'Nudge' , 'oasisworkflow' ) . "</a><span class='loading'>" . esc_html( $space ) . "</span>";
        }

		return $inbox_row_actions;
	}

	/**
	 * enqueue and localize
	 */
	public function enqueue_and_localize_script() {
		wp_enqueue_script( 'owf_reassign_task',
			OASISWF_URL . 'js/pages/subpages/reassign.js',
			array( 'jquery' ),
			OASISWF_VERSION,
			true );

		wp_localize_script( 'owf_reassign_task', 'owf_reassign_task_vars', array(
			'selectUser'          => esc_html__( 'Select a user to reassign the task.', 'oasisworkflow' ),
			'isCommentsMandotory' => get_option( "oasiswf_comments_setting" ),
			'emptyComments'       => esc_html__( 'Please add comments.', 'oasisworkflow' )
		) );
	}

}

// construct an instance so that the actions get loaded
$inbox_service = new OW_Inbox_Service();

