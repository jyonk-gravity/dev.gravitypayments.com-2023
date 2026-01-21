<?php
/*
 * Workflow History Page
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

$nonce_val = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : "";
// phpcs:ignore WordPress.Security.NonceVerification
if ( isset( $_GET['post'] ) && sanitize_text_field( $_GET["post"] ) && ! empty( $nonce_val ) && wp_verify_nonce( $nonce_val, 'owf_view_history_nonce' ) ) {
	$selected_post_id = intval( sanitize_text_field( $_GET["post"] ) );
} else {
	$selected_post_id = 0;
}
$page_number = ( isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] ) )
	? intval( sanitize_text_field( $_GET["paged"] ) ) : 1;
$trashed     = ( isset( $_GET['trashed'] ) && sanitize_text_field( $_GET["trashed"] ) )
	? sanitize_text_field( $_GET["trashed"] ) : "";

if ( $selected_post_id && ! empty( $nonce_val ) &&
     wp_verify_nonce( $nonce_val, 'owf_view_history_nonce' ) ) {
	$selected_post = $selected_post_id;
} else {
	$selected_post = null;
}

// phpcs:ignore WordPress.WP.GlobalVariablesOverride
$action = ( isset( $_GET['show_unclaimed'] ) && intval( $_GET["show_unclaimed"] ) ) ? intval( sanitize_text_field( $_GET["show_unclaimed"] ) ) : 0;

if ( $action == 1 ) {
	$display_unclaimed = "show_unclaimed";
}

if ( $action == 0 ) {
	$display_unclaimed = "online";
}

$ow_history_service = new OW_History_Service();
$workflow_service   = new OW_Workflow_Service();
$ow_process_flow    = new OW_Process_Flow();

$show_unclaimed = false;

// Get transient user details
$user_id = get_current_user_id();
if ( $user_id ) {
	$show_unclaimed = get_transient( 'show_unclaimed_' . $user_id );
}

// Set css class to hide and show buttons
$unclaim_class  = "hide-unclaim-button";
$claim_class    = "show-claim-button";
$hide_no_action = null;

// If clicked show unclaimed activity
if ( $display_unclaimed === "online" ) {
	// If clicked hide unclaimed activity
	$claim_class    = "show-claim-button";
	$unclaim_class  = "hide-unclaim-button";
	$hide_no_action = "hide_no_action";
	$action         = 0; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	$histories      = $ow_history_service->get_workflow_history_all( $page_number, "online", $selected_post );
	$count_posts    = $ow_history_service->get_workflow_history_count( $selected_post, "online" );
} else if ( $display_unclaimed === "show_unclaimed" || $show_unclaimed ) {
	$unclaim_class = "show-unclaim-button";
	$claim_class   = "hide-claim-button";
	$action        = 1; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	$histories     = $ow_history_service->get_workflow_history_all( $page_number, "show_unclaimed", $selected_post );
	$count_posts   = $ow_history_service->get_workflow_history_count( $selected_post, "show_unclaimed" );
} else {
	$histories   = $ow_history_service->get_workflow_history_all( $page_number, "online", $selected_post );
	$count_posts = $ow_history_service->get_workflow_history_count( $selected_post, "online" );
}

$per_page         = OASIS_PER_PAGE; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
$option           = get_option( 'oasiswf_custom_workflow_terminology' );
$workflow_history = ! empty( $option['workflowHistoryText'] ) ? sanitize_text_field( $option['workflowHistoryText'] )
	: esc_html__( 'Workflow History', 'oasisworkflow' );

$action_url = add_query_arg(
	array(
		'security' => wp_create_nonce('owf-workflow-history'),
	),
	'admin.php?page=oasiswf-history'
);
?>
<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
    <h2><?php echo esc_html( $workflow_history ); ?></h2>
	<?php if ( ! empty( $trashed ) && $trashed === "success_history_deleted" ): ?>
        <div id="message" class="updated notice notice-success is-dismissible">
            <p>
				<?php echo esc_html__( "Workflow history deleted successfully.", "oasisworkflow" ); ?>
            </p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">
                    <?php esc_html__( "Dismiss this notice.", "oasisworkflow" ); ?>
                </span>
            </button>
        </div>
	<?php endif; ?>
	<?php if ( ! empty( $trashed ) && $trashed === "success_no_history_deleted" ): ?>
        <div id="message" class="updated notice notice-success is-dismissible">
            <p>
				<?php echo esc_html__( "No eligible workflow history found to delete.", "oasisworkflow" ); ?>
            </p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">
                    <?php esc_html__( "Dismiss this notice.", "oasisworkflow" ); ?>
                </span>
            </button>
        </div>
	<?php endif; ?>
	<?php if ( ! empty( $trashed ) && $trashed === "error" ): ?>
        <div id="message " class="error">
            <p>
				<?php echo esc_html__( "There was an error while deleting the history. Try again OR contact your administrator.",
					"oasisworkflow" ); ?>
            </p>
        </div>
	<?php endif; ?>
    <div id="view-workflow" class="workflow-history">
        <div class="tablenav">
            <form method="post"
                  id="workflow-history"
                  action="<?php echo esc_url( $action_url ); ?>">
                <div class="alignleft actions">
                    <select id="post_filter" name="post_filter">
                        <option selected="selected" value="0"><?php echo esc_html__( "View Post/Page Workflow History",
								"oasisworkflow" ) ?></option>
						<?php
						$wf_posts = $ow_process_flow->get_posts_in_all_workflow();
						if ( $wf_posts ) {
							$option = '';
							foreach ( $wf_posts as $wf_post ) {

								$selected = selected( $selected_post, $wf_post->post_id, false );
								$option   .= "<option value='" . esc_attr( $wf_post->post_id ) . "' " .
								             esc_attr( $selected ) . ">" .
								             esc_html( $wf_post->title ) . "</option>";
							}
							echo $option; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
                    </select>
					<?php
					$filter_url = add_query_arg(
						array(
							'post' => esc_attr( $selected_post_id ),
							'show_unclaimed' => esc_attr( $action ),
							'_wpnonce' => wp_create_nonce('owf_view_history_nonce'),
						),
						admin_url( 'admin.php?page=oasiswf-history' )
					);
					?>
                    <a id="owf_history_filter" class="button" href="<?php echo esc_url( $filter_url ); ?>"><?php echo esc_attr__( "Filter", "oasisworkflow" ); ?></a>&nbsp;&nbsp;

					<?php
					if ( current_user_can( 'ow_download_workflow_history' ) ) {
						?>
                        <!-- Download Report Button -->
                        <input type="submit" class="button-secondary action" name="download_history" id="download"
                               value="<?php echo esc_attr__( "Download Workflow History Report",
							       "oasisworkflow" ); ?>"/>
						<?php
					}
					?>
					<?php
					if ( current_user_can( 'ow_delete_workflow_history' ) ) {
						?>
                        <input type="button" class="button-secondary action" id="owf-delete-history"
                               value="<?php echo esc_attr__( "Delete History", "oasisworkflow" ); ?>"/>
						<?php
					}

					$show_activities_url = add_query_arg(
						array(
							'post' => esc_attr( $selected_post_id ),
							'show_unclaimed' => '1',
							'_wpnonce' => wp_create_nonce('owf_view_history_nonce'),
						),
						admin_url( 'admin.php?page=oasiswf-history' )
					);
					$hide_activities_url = add_query_arg(
						array(
							'post' => esc_attr( $selected_post_id ),
							'show_unclaimed' => '0',
							'_wpnonce' => wp_create_nonce('owf_view_history_nonce'),
						),
						admin_url( 'admin.php?page=oasiswf-history' )
					);
					?>
                    <a href="<?php echo esc_url( $show_activities_url ); ?>"
                       id="owf-show-unclaimed" class="<?php echo esc_attr( $claim_class ); ?>">
                        <input type="button" class="button-secondary action"
                               value="<?php echo esc_attr__( "Show No-Action Activities", "oasisworkflow" ); ?>"/>
                    </a>

                    <a href="<?php echo esc_url( $hide_activities_url ); ?>"
                       id="owf-hide-unclaimed" class="<?php echo esc_attr( $unclaim_class ); ?>">
                        <input type="button" class="button-secondary action"
                               value="<?php echo esc_attr__( "Hide No-Action Activities", "oasisworkflow" ); ?>"/>
                    </a>
                </div>
            </form>
            <div class="tablenav-pages">
				<?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page ); ?>
            </div>
        </div>
        <table class="wp-list-table widefat fixed posts" cellspacing="0" border="0">
            <thead>
			<?php $ow_history_service->get_table_header(); ?>
            </thead>
            <tfoot>
			<?php $ow_history_service->get_table_header(); ?>
            </tfoot>
            <tbody id="coupon-list">
			<?php
			// Hide/Show actor column using the filter
			$current_user_id   = get_current_user_id();
			$current_user_role = OW_Utility::instance()->get_user_role( $current_user_id );

			$roles = array();

			$user_roles = apply_filters( 'owf_hide_attributes_by_role', $roles );

			if ( $histories ):
				foreach ( $histories as $row ) {
					$wf_post = null;
					foreach ( $wf_posts as $data ) {
						if ( $row->post_id == $data->post_id ) {
							$wf_post = $data;
							break;
						}
					}

					$row_url = add_query_arg(
						array(
							'wf_id' => esc_attr( $row->workflow_id )
						),
						admin_url( 'admin.php?page=oasiswf-admin' )
					);

					$workflow_name = "<a href='" . esc_url( $row_url ) . "''><strong>" .
					                 esc_html( $row->wf_name );
					if ( ! empty( $row->version ) ) {
						$workflow_name .= " (" . esc_html( $row->version ) . ")";
					}
					$workflow_name .= "</strong></a>";

					if ( $row->assign_actor_id != - 1 ) { //assignment and/or publish steps
						echo "<tr>";
						echo "<th scope='row' class='check-column'><input type='checkbox' name='linkcheck[]' value='1'></th>";
						if ( $wf_post && $wf_post->status == 'publish' ) {
							echo "<td class='column-primary' data-colname='Title'>" . esc_html( $row->post_title ) .
							     "</td>";
						} else {
							echo "<td class='column-primary' data-colname='Title'>
										<a href='post.php?post=" . esc_attr( $row->post_id ) .
							     "&action=edit'><strong>" .
							     esc_html( $row->post_title ) . "</strong></a><button type='button' class='toggle-row'>
										<span class='screen-reader-text'>
										" . esc_html__( "Show more details", "oasisworkflow" ) . "
										</span>
										</button>
									</td>";
						}

						if ( ! in_array( $current_user_role, $user_roles ) ) {
							if ( $row->userid == 0 ) {
								$actor = "System";
							} else {
								$actor = OW_Utility::instance()->get_user_name( $row->userid );
								if ( empty( $actor ) ) { // in case the actor is deleted or non existent
									$actor = "System";
								}
							}
							echo "<td data-colname='Actor' >" . esc_html( $actor ) . "</td>";
						}

						echo "<td data-colname='Workflow [Step]'>" . $workflow_name . " <br> [" . esc_attr( $workflow_service->get_step_name( $row ) ) . "]</td>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo "<td data-colname='Assigned date'>" .
						     OW_Utility::instance()->format_date_for_display( $row->create_datetime, "-", "datetime" ) . "</td>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo "<td data-colname='Sign off date'>" .
						     OW_Utility::instance()->format_date_for_display( $ow_process_flow->get_sign_off_date( $row ), "-", "datetime" ) . "</td>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo "<td data-colname='Result'>" . esc_html( $ow_process_flow->get_sign_off_status( $row ) ) .
						     "</td>";
                        
						echo "<td class='comments column-comments' data-colname='Comments'>
										<div class='post-com-count-wrapper'>
											<strong>
												<a href='#' actionid='" . esc_attr( $row->ID ) . "' actionstatus='" .
						     esc_attr( $row->action_status ) . "' class='post-com-count post-com-count-approved' real='history'>
													<span class='comment-count-approved'>" .
						     esc_attr( $ow_process_flow->get_sign_off_comment_count( $row ) ) . "</span>
												</a>
												<span class='loading' style='display:none'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
											</strong>
										</div>
									  </td>";
						// add-ons to add rows to history table on history page
						apply_filters( 'display_history_column_content', $row->post_id, $row, 'history' );
						echo "</tr>";
					}
					if ( $row->assign_actor_id == - 1 ) { //review step
						$review_rows = $ow_history_service->get_review_action_by_history_id( $row->ID,
							"update_datetime", $hide_no_action );
						if ( $review_rows ) {
							foreach ( $review_rows as $review_row ) {
								echo "<tr>";
								echo "<th scope='row' class='check-column'><input type='checkbox' name='linkcheck[]' value='1'></th>";
								echo "<td class='column-primary' data-colname='Title'><a href='post.php?post=" .
								     esc_attr( $row->post_id ) . "&action=edit'><strong>" .
								     esc_html( $row->post_title ) .
								     "</strong></a><button type='button' class='toggle-row'><span class='screen-reader-text'>Show more details</span></button></td>";

								if ( ! in_array( $current_user_role, $user_roles ) ) {
									if ( $review_row->actor_id == 0 ) {
										$actor = "System";
									} else {
										$actor = OW_Utility::instance()->get_user_name( $review_row->actor_id );
										if ( empty( $actor ) ) { // in case the actor is deleted or non existent
											$actor = "System";
										}
									}
									echo "<td data-colname='Actor'>" . esc_html( $actor ) . "</td>";
								}

								echo "<td data-colname='Workflow [Step]'>" . $workflow_name . " <br> [" . esc_attr( $workflow_service->get_step_name( $row ) ) . "]</td>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo "<td data-colname='Assigned date'>" .
								     OW_Utility::instance()->format_date_for_display( $row->create_datetime, "-", "datetime" ) . "</td>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								$signoff_date = $review_row->update_datetime;
								echo "<td data-colname='Sign off date'>" .
								     OW_Utility::instance()->format_date_for_display( $signoff_date, "-", "datetime" ) . "</td>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								// If editors' review status is "no_action" (Not acted upon) then set user status as "No action taken"
								if ( $review_row->review_status == "no_action" ||
								     $review_row->review_status == "abort_no_action" ) {
									$review_status = esc_html__( "No Action Taken", "oasisworkflow" );
								} else {
									if ( $ow_process_flow->get_next_step_sign_off_status( $row ) == "complete" ) {
										$review_status = esc_html__( "Workflow completed", "oasisworkflow" );
									} else if ( $ow_process_flow->get_next_step_sign_off_status( $row ) ==
									            "cancelled" ) {
										$review_status = esc_html__( "Cancelled", "oasisworkflow" );
									} else {
										$review_status = $ow_process_flow->get_review_sign_off_status( $row,
											$review_row );
									}
								}
								echo "<td data-colname='Result'>" . esc_html( $review_status ) . "</td>";
								echo "<td class='comments column-comments' data-colname='Comments'>
												<div class='post-com-count-wrapper'>
													<strong>
														<a href='#' actionid='" . esc_attr( $review_row->ID ) .
								     "' actionstatus='" .
								     esc_attr( $review_row->review_status ) . "' class='post-com-count post-com-count-approved' real='review'>
															<span class='comment-count-approved'>" .
								     esc_attr( $ow_process_flow->get_review_sign_off_comment_count( $review_row,
									     $row->post_id, $review_row->actor_id ) ) .
								     "</span></a>
											<span class='loading' style='display:none'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
													</strong>
												</div>
											  </td>";
								// add-ons to add rows to history table on history page
								apply_filters( 'display_history_column_content', $row->post_id, $review_row,
									'review_history' );
								echo "</tr>";
							}
						}
					}
				}
			else:
				echo "<tr>";
				echo "<td colspan='9' class='no-found-td'><lavel>";
				echo esc_html__( "No workflow history data found.", "oasisworkflow" );
				echo "</label></td>";
				echo "</tr>";
			endif;
			?>
            </tbody>
        </table>
		<?php wp_nonce_field( 'owf-workflow-history', 'owf_workflow_history_nonce' ); ?>
        <input type="hidden" name="owf_inbox_ajax_nonce" id="owf_inbox_ajax_nonce"
               value="<?php echo esc_attr( wp_create_nonce( 'owf_inbox_ajax_nonce' ) ); ?>"/>
        <div class="tablenav">
            <div class="tablenav-pages">
				<?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page ); ?>
            </div>
        </div>
    </div>
</div>

<div id="post_com_count_content"></div>
<div id="ajaxcc"></div>