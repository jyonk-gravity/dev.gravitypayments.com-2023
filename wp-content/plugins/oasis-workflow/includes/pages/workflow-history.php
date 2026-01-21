<?php
// phpcs:ignore
if ( isset( $_GET['post'] ) && sanitize_text_field( $_GET["post"] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'owf_view_history_nonce' ) ) {
	$selected_post_id = intval( sanitize_text_field( $_GET["post"] ) );
} else {
	$selected_post_id = null;
}
$pagenum = ( isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] ) ) ? intval( sanitize_text_field( $_GET["paged"] ) ) : 1;
$trashed = ( isset( $_GET['trashed'] ) && sanitize_text_field( $_GET["trashed"] ) ) ? sanitize_text_field( $_GET["trashed"] ) : "";

// phpcs:ignore
if ( $selected_post_id && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'owf_view_history_nonce' ) ) {
	$selected_post = $selected_post_id;
} else {
	$selected_post = null;
}

$ow_history_service = new OW_History_Service();
$workflow_service   = new OW_Workflow_Service();
$ow_process_flow    = new OW_Process_Flow();

$histories        = $ow_history_service->get_workflow_history_all( $selected_post );
$count_posts      = $ow_history_service->get_workflow_history_count( $selected_post );
$per_page         = OASIS_PER_PAGE; // phpcs:ignore
$workflow_history = OW_Utility::instance()->get_custom_workflow_terminology( 'workflowHistoryText' );
?>
<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
    <h2><?php echo $workflow_history; // phpcs:ignore ?></h2>
	<?php if ( ! empty( $trashed ) && $trashed === "success_history_deleted" ): ?>
        <div id="message" class="updated notice notice-success is-dismissible">
            <p>
				<?php esc_html_e( "Workflow history deleted successfully.", "oasisworkflow" ); ?>
            </p>
            <button type="button" class="notice-dismiss"><span
                        class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'oasisworkflow' ); ?></span>
            </button>
        </div>
	<?php endif; ?>
	<?php if ( ! empty( $trashed ) && $trashed === "success_no_history_deleted" ): ?>
        <div id="message" class="updated notice notice-success is-dismissible">
            <p>
				<?php esc_html_e( "No eligible workflow history found to delete.", "oasisworkflow" ); ?>
            </p>
            <button type="button" class="notice-dismiss"><span
                        class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'oasisworkflow' ); ?></span>
            </button>
        </div>
	<?php endif; ?>
	<?php if ( ! empty( $trashed ) && $trashed === "error" ): ?>
        <div id="message " class="error">
            <p>
				<?php esc_html_e( "There was an error while deleting the history. Try again OR contact your administrator.", "oasisworkflow" ); ?>
            </p>
        </div>
	<?php endif; ?>
    <div id="view-workflow" class="workflow-history">
        <div class="tablenav">
            <form method="post"
                  id="workflow-history"
                  action="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=oasiswf-history' ), 'owf-workflow-history', 'security' ) ); ?>">
                <div class="alignleft actions">
                    <select id="post_filter" name="post_filter">
                        <option selected="selected"
                                value="0"><?php esc_html_e( "View Post/Page Workflow History", "oasisworkflow" ) ?></option>
						<?php
						$wf_posts = $ow_process_flow->get_posts_in_all_workflow();
						if ( $wf_posts ) {
							$option = '';
							foreach ( $wf_posts as $wf_post ) {

								$selected = selected( $selected_post, $wf_post->post_id );
								$option   .= "<option value='$wf_post->post_id' $selected >$wf_post->title</option>";
							}
							echo $option; // phpcs:ignore
						}
						?>
                    </select>

					<?php
					$filter_url = add_query_arg(
						array(
							'post' => esc_attr( $selected_post_id ),
							'_wpnonce' => wp_create_nonce('owf_view_history_nonce'),
						),
						admin_url( 'admin.php?page=oasiswf-history' )
					);
					?>
                    <a href="<?php echo esc_url( $filter_url ); ?>"class="button"><?php esc_html_e( "Filter", "oasisworkflow" ); ?></a>&nbsp;

					<?php
					if ( current_user_can( 'ow_download_workflow_history' ) ) {
						?>
                        <!-- Download Report Button -->
                        <input type="submit" class="button-secondary action" name="download_history" id="download"
                               value="<?php esc_attr_e( "Download Workflow History Report", "oasisworkflow" ); ?>"/>
						<?php
					}
					?>
					<?php
					if ( current_user_can( 'ow_delete_workflow_history' ) ) {
						?>
                        <input type="button" class="button-secondary action" id="owf-delete-history"
                               value="<?php esc_attr_e( "Delete History", "oasisworkflow" ); ?>"/>
						<?php
					}
					?>

                </div>
            </form>
            <div class="tablenav-pages">
				<?php OW_Utility::instance()->get_page_link( $count_posts, $pagenum, $per_page ); ?>
            </div>
        </div>
        <table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
            <thead>
			<?php $ow_history_service->get_table_header(); ?>
            </thead>
            <tfoot>
			<?php $ow_history_service->get_table_header(); ?>
            </tfoot>
            <tbody id="coupon-list">
			<?php
			if ( $histories ):
				$count = 0;
				$start = ( $pagenum - 1 ) * $per_page;
				$end   = $start + $per_page;
				foreach ( $histories as $row ) {
					$workflow_name = "<a href='admin.php?page=oasiswf-admin&wf_id=" . $row->workflow_id . "'><strong>" . $row->wf_name;
					if ( ! empty( $row->version ) ) {
						$workflow_name .= " (" . $row->version . ")";
					}
					$workflow_name .= "</strong></a>";

					if ( $count >= $end ) {
						break;
					}
					if ( $count >= $start ) {
						if ( $row->assign_actor_id != - 1 ) { //assignment and/or publish steps
							echo "<tr>";
							echo "<th scope='row' class='check-column'><input type='checkbox' name='linkcheck[]' value='1'></th>";
							echo "<td>
										<a href='post.php?post=" . esc_attr( $row->post_id ) . "&action=edit'><strong>" . esc_html( $row->post_title ) . "</strong></a>
									</td>";
							if ( $row->userid == 0 ) {
								$actor = "System";
							} else {
								$actor = OW_Utility::instance()->get_user_name( $row->userid );
								if ( empty( $actor ) ) { // in case the actor is deleted or non existent
									$actor = "System";
								}
							}
							echo "<td>" . esc_html( $actor ) . "</td>";
							echo "<td>" . wp_kses_post( $workflow_name ) . " <br> [" . esc_html( $workflow_service->get_step_name( $row ) ) . "]</td>";
							echo "<td>" . OW_Utility::instance()->format_date_for_display( $row->create_datetime, "-", "datetime" ) . "</td>"; // phpcs:ignore
							echo "<td>" . OW_Utility::instance()->format_date_for_display( $ow_process_flow->get_sign_off_date( $row ), "-", "datetime" ) . "</td>"; // phpcs:ignore
							echo "<td>" . esc_html( $ow_process_flow->get_sign_off_status( $row ) ) . "</td>";
							echo "<td class='comments column-comments'>
										<div class='post-com-count-wrapper'>
											<strong>
												<a href='#' actionid='" . esc_attr( $row->ID ) . "' actionstatus='" . esc_attr( $row->action_status ) . "' class='post-com-count post-com-count-approved' real='history'>
													<span class='comment-count-approved'>" . esc_html( $ow_process_flow->get_sign_off_comment_count( $row ) ) . "</span>
												</a>
												<span class='loading' style='display:none'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
											</strong>
										</div>
									  </td>";
							echo "</tr>";
						}
						if ( $row->assign_actor_id == - 1 ) { //review step
							$review_rows = $ow_history_service->get_review_action_by_history_id( $row->ID, "update_datetime" );
							if ( $review_rows ) {
								foreach ( $review_rows as $review_row ) {
									echo "<tr>";
									echo "<th scope='row' class='check-column'><input type='checkbox' name='linkcheck[]' value='1'></th>";
									echo "<td><a href='post.php?post=" . esc_attr( $row->post_id ) . "&action=edit'><strong>" . esc_html( $row->post_title ) . "</strong></a></td>";
									if ( $review_row->actor_id == 0 ) {
										$actor = "System";
									} else {
										$actor = OW_Utility::instance()->get_user_name( $review_row->actor_id );
										if ( empty( $actor ) ) { // in case the actor is deleted or non existent
											$actor = "System";
										}
									}
									echo "<td>" . esc_html( $actor ) . "</td>";
									echo "<td>" . wp_kses_post( $workflow_name ) . " <br> [" . esc_html( $workflow_service->get_step_name( $row ) ) . "]</td>";
									echo "<td>" . OW_Utility::instance()->format_date_for_display( $row->create_datetime, "-", "datetime" ) . "</td>"; // phpcs:ignore
									$signoff_date = $review_row->update_datetime;
									echo "<td>" . OW_Utility::instance()->format_date_for_display( $signoff_date, "-", "datetime" ) . "</td>"; // phpcs:ignore
									// If editors' review status is "no_action" (Not acted upon) then set user status as "No action taken"
									if ( $review_row->review_status == "no_action" || $review_row->review_status == "abort_no_action" ) {
										$review_status = esc_html__( "No Action Taken", "oasisworkflow" );
									} else {
										if ( $ow_process_flow->get_next_step_sign_off_status( $row ) == "complete" ) {
											$review_status = esc_html__( "Workflow completed", "oasisworkflow" );
										} else {
											$review_status = $ow_process_flow->get_review_sign_off_status( $row, $review_row );
										}
									}
									echo "<td>" . esc_html( $review_status ) . "</td>";
									echo "<td class='comments column-comments'>
												<div class='post-com-count-wrapper'>
													<strong>
														<a href='#' actionid='" . esc_attr( $review_row->ID ) . "' actionstatus='" . esc_attr( $review_row->review_status ) . "' class='post-com-count post-com-count-approved' real='review'>
															<span class='comment-count-approved'>" . esc_html( $ow_process_flow->get_review_sign_off_comment_count( $review_row ) ) . "</span>
														</a>
														<span class='loading' style='display:none'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
													</strong>
												</div>
											  </td>";
									echo "</tr>";
									$count ++;
								}
							}
						}
					}
					$count ++;
				}
			else:
				echo "<tr>";
				echo "<td colspan='9' class='no-found-td'><lavel>";
				esc_html_e( "No workflow history data found.", "oasisworkflow" );
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
				<?php OW_Utility::instance()->get_page_link( $count_posts, $pagenum, $per_page ); ?>
            </div>
        </div>
    </div>
</div>

<div id="post_com_count_content"></div>
<div id="ajaxcc"></div>