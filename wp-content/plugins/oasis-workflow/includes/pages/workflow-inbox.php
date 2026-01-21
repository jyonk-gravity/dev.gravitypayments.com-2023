<?php
global $ow_custom_statuses;

// sanitize the data
// phpcs:ignore
$selected_user = ( isset( $_GET['user'] ) && sanitize_text_field( $_GET["user"] ) ) ? intval( sanitize_text_field( $_GET["user"] ) ) : get_current_user_id();
// phpcs:ignore
$page_number = ( isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] ) ) ? intval( sanitize_text_field( $_GET["paged"] ) ) : 1;

$request_params = array_diff_key($_GET, array('page' => 'oasiswf-inbox'));

// Verify nonce
$nonce = ( isset( $_GET['_wpnonce'] ) ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '';

// apply nonce verification only if action para
if ( ! empty( $request_params ) && ! check_admin_referer( 'ow_inbox_nonce', '_wpnonce' ) ) {
    // Nonce verification failed, handle accordingly (exit, redirect, etc.)
    exit( 'Nonce verification failed!' );
}

$ow_inbox_service    = new OW_Inbox_Service();
$ow_process_flow     = new OW_Process_Flow();
$ow_workflow_service = new OW_Workflow_Service();

// get assigned posts for selected user
$inbox_items = $ow_process_flow->get_assigned_post( null, $selected_user );
$count_posts = count( $inbox_items );
$per_page    = OASIS_PER_PAGE; // phpcs:ignore

// get custom terminology
$workflow_terminology_options = OW_Utility::instance()->get_custom_workflow_terminology();
$sign_off_label               = $workflow_terminology_options['signOffText'];
$abort_workflow_label         = $workflow_terminology_options['abortWorkflowText'];
?>
<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
    <h1><?php esc_html_e( 'Inbox', 'oasisworkflow' ); ?></h1>
    <div id="workflow-inbox">
        <div class="tablenav top">

            <!-- Bulk Actions Start -->
			<?php do_action( 'owf_bulk_actions_section' ); ?>
            <!-- Bulk Actions End -->

            <input type="hidden" id="hidden_task_user" value="<?php echo esc_attr( $selected_user ); ?>"/>
			<?php if ( current_user_can( 'ow_view_others_inbox' ) ) { ?>
				<form action="<?php echo esc_url( admin_url( 'admin.php?page=oasiswf-inbox' ) ); ?>">
                    <input type="hidden" name="page" value="oasiswf-inbox" />
                    <input type="hidden" name="user" value="<?php echo esc_attr( $selected_user ); ?>"/>
                    <?php wp_nonce_field( 'ow_inbox_nonce', '_wpnonce', false ); ?>
                    <div class="tablenav alignleft actions">
                        <select id="inbox_filter" name="user">
                            <option
                                value=<?php echo esc_attr( get_current_user_id() ); ?> selected="selected">
                                <?php echo esc_html__( "View inbox of ", "oasisworkflow" ) ?>
                            </option>
                            <?php
                            $assigned_users = $ow_process_flow->get_assigned_users();
                            if ( has_filter( 'owf_get_assigned_users' ) ) {
                                $assigned_users = apply_filters( 'owf_get_assigned_users', $assigned_users );
                            }
                            if ( $assigned_users ) {
                                foreach ( $assigned_users as $assigned_user ) {
                                    if ( ( isset( $_GET['user'] ) && $_GET["user"] == $assigned_user->ID ) ) { // phpcs:ignore
                                        echo "<option value=" . esc_attr( $assigned_user->ID ) .
                                            " selected> " . esc_html( $assigned_user->display_name ) . "</option>";
                                    } else {
                                        echo "<option value=" . esc_attr( $assigned_user->ID ) .
                                            ">" . esc_html( $assigned_user->display_name ) . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>

                        <input type="submit" class="button-secondary action" value="<?php esc_attr_e( "Show", "oasisworkflow" ); ?>"/>
                    </div>
                </form>
			<?php } ?>
            <ul class="subsubsub"></ul>
            <div class="tablenav-pages">
				<?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page ); ?>
            </div>
        </div>
        <table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
            <thead>
			<?php $ow_inbox_service->get_table_header(); ?>
            </thead>
            <tfoot>
			<?php $ow_inbox_service->get_table_header(); ?>
            </tfoot>
            <tbody id="coupon-list">
			<?php
			$wf_process_status = get_site_option( "oasiswf_status" );
			$space             = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if ( $inbox_items ):
				$count    = 0;
				$cat_name = '----';
				$start    = ( $page_number - 1 ) * $per_page;
				$end      = $start + $per_page;
				foreach ( $inbox_items as $inbox_item ) {
					if ( $count >= $end ) {
						break;
					}
					if ( $count >= $start ) {
						$post = get_post( $inbox_item->post_id ); // phpcs:ignore

						$cat_name = OW_Utility::instance()->get_post_categories( $inbox_item->post_id );
						$user     = get_userdata( $post->post_author );
						$stepId   = $inbox_item->step_id;
						if ( $stepId <= 0 || $stepId == "" ) {
							$stepId = $inbox_item->review_step_id;
						}
						$step     = $ow_workflow_service->get_step_by_id( $stepId );
						$workflow = $ow_workflow_service->get_workflow_by_id( $step->workflow_id );

						$needs_to_be_claimed = $ow_process_flow->check_for_claim( $inbox_item->ID );

						$original_post_id = get_post_meta( $inbox_item->post_id, '_oasis_original', true );
						/*Check due date and make post item background color in red to notify the admin*/
						$ow_email = new OW_Email();

						$current_date              = Date( " F j, Y " ); // phpcs:ignore
						$due_date                  = OW_Utility::instance()->format_date_for_display( $inbox_item->due_date );
						$past_due_date_row_class   = '';
						$past_due_date_field_class = '';
						if ( $due_date != "" && strtotime( $due_date ) < strtotime( $current_date ) ) {
							$past_due_date_row_class   = 'past-due-date-row';
							$past_due_date_field_class = 'past-due-date-field';

						}
						echo "<tr id='post-" . esc_attr( $inbox_item->post_id ) . "'
                        	class='post-" . esc_attr( $inbox_item->post_id ) . " post type-post " . esc_attr( $past_due_date_row_class ) . "
                        	status-pending format-standard hentry category-uncategorized alternate iedit author-other'> ";
						$workflow_post_id = esc_attr( $inbox_item->post_id );
						echo "<th scope='row' class='check-column'>
									<input type='checkbox' name='post[]' value='" . esc_attr( $workflow_post_id ) . "' wfid='" . esc_attr( $inbox_item->ID ) . "'></th>";

						echo "<td><strong>" . esc_html( $post->post_title );
						// TODO : see if we can find a better solution instead of using _post_states
						_post_states( $post );
						echo "</strong>";
						// create the action list
						if ( $needs_to_be_claimed ) { // if the item needs to be claimed, only "Claim" action is visible
							echo "<div class='row-actions'>
											<span>
												<a href='#' class='claim' actionid='" . esc_attr( $inbox_item->ID ) . "'>" . esc_html__( "Claim", "oasisworkflow" ) . "</a>
												<span class='loading'>" . esc_html( $space ) . "</span>
											</span>
										</div>";
						} else {
							echo "<div class='row-actions'>
										</div>";

							echo "<div class='row-actions'>";
							if ( OW_Utility::instance()->is_post_editable( $inbox_item->post_id ) ) {
								echo "<span><a href='post.php?post=" . esc_attr( $inbox_item->post_id ) . "&action=edit&oasiswf=" . esc_attr( $inbox_item->ID ) . "&user=" . esc_attr( $selected_user ) . "' class='edit' real='" . esc_attr( $inbox_item->post_id ) . "'>" . esc_html__( "Edit", "oasisworkflow" ) . "</a></span>&nbsp;|&nbsp;";
							}

							echo "<span><a target='_blank' href='" . esc_url( get_preview_post_link( $inbox_item->post_id ) ) . "'>" . esc_html__( "View", "oasisworkflow" ) . "</a></span>&nbsp;|&nbsp;";

							if ( current_user_can( 'ow_sign_off_step' ) && OW_Utility::instance()->is_post_editable( $inbox_item->post_id ) ) {
								echo "<span>
												<a href='#' wfid='" . esc_attr( $inbox_item->ID ) . "' postid='" . esc_attr( $inbox_item->post_id ) . "' class='quick_sign_off'>" . esc_html( $sign_off_label ) . "</a>
												<span class='loading'>" . esc_html( $space ) . "</span>
											</span>&nbsp;|&nbsp;";
							}
							if ( current_user_can( 'ow_reassign_task' ) ) {
								echo "<span>
												<a href='#' wfid='" . esc_attr( $inbox_item->ID ) . "' class='reassign'>" . esc_html__( "Reassign", "oasisworkflow" ) . "</a>
												<span class='loading'>" . esc_html( $space ) . "</span>
											</span>&nbsp;|&nbsp;";
							}
							if ( current_user_can( 'ow_abort_workflow' ) ) {
								echo "<span>
										<a href='#' wfid='" . esc_attr( $inbox_item->ID ) . "' postid='" . esc_attr( $inbox_item->post_id ) . "' class='abort_workflow'>" . esc_html( $abort_workflow_label ) . "</a>
										<span class='loading'>" . esc_html( $space ) . "</span>
										</span>&nbsp;|&nbsp;";
							}
							if ( current_user_can( 'ow_view_workflow_history' ) ) {
								$nonce_url = wp_nonce_url( "admin.php?page=oasiswf-history&post=$inbox_item->post_id", 'owf_view_history_nonce' );
								echo "<span><a href='" . esc_url( $nonce_url ) . "'> " . esc_html__( "View History", "oasisworkflow" ) . "</a></span>";
							}

							echo "</div>";
							get_inline_data( $post );
						}
						echo "</td>";

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
							echo "<td ><p class='post-priority " . esc_attr( $css_class ) . "-priority'>" . esc_html( $priority_value ) . "</p></td>";
						}

						$post_type_obj = get_post_type_object( get_post_type( $inbox_item->post_id ) );
						echo "<td>" . esc_html( $post_type_obj->labels->singular_name ) . "</td>";
						echo "<td>" . esc_html( OW_Utility::instance()->get_user_name( $user->ID ) ) . "</td>";
						$workflow_name = $workflow->name;
						if ( ! empty( $workflow->version ) ) {
							$workflow_name .= " (" . $workflow->version . ")";
						}

						echo "<td>" . wp_kses_post( $workflow_name ) . " [" . esc_html( $ow_workflow_service->get_gpid_dbid( $workflow->ID, $stepId, 'lbl' ) ) . "]</td>";

						echo "<td>" . esc_html( $cat_name ) . "</td>";

						$post_status = $ow_custom_statuses->get_single_term_by( 'slug', get_post_status( $post->ID ) );
						$post_status = is_object( $post_status ) && isset( $post_status->name ) ? $post_status->name : $wf_process_status[ $ow_workflow_service->get_gpid_dbid( $workflow->ID, $stepId, 'process' ) ];
						// if the due date is passed the current date show the field in a different color
						echo "<td><span class='" . esc_attr( $past_due_date_field_class ) . "'>" . esc_html( OW_Utility::instance()->format_date_for_display( $inbox_item->due_date ) ) . "</span></td>";

						echo "<td class='comments column-comments'>
										<div class='post-com-count-wrapper'>
											<strong>
												<a href='#' actionid='" . esc_attr( $inbox_item->ID ) . "' class='post-com-count post-com-count-approved' data-comment='inbox_comment' post_id=" . esc_attr( $inbox_item->post_id ) . ">
													<span class='comment-count-approved'>" . esc_html( $ow_process_flow->get_comment_count( $inbox_item->ID, true, $inbox_item->post_id ) ) . "</span>
												</a>
												<span class='loading'>" . esc_html( $space ) . "</span>
											</strong>
										</div>
									  </td>";
						echo "</tr>";
					}
					$count ++;
				}
			else:
				echo "<tr>";
				echo "<td class='hurry-td' colspan='8'>
								<label class='hurray-lbl'>";
				esc_html_e( "Hurray! No assignments.", "oasisworkflow" );
				echo "</label></td>";
				echo "</tr>";
			endif;
			?>
            </tbody>
        </table>
        <div class="tablenav">
            <div class="tablenav-pages">
				<?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page ); ?>
            </div>
        </div>
    </div>
</div>
<span id="wf_edit_inline_content"></span>
<div id="step_submit_content"></div>
<div id="reassign-div"></div>
<div id="post_com_count_content"></div>
<input type="hidden" name="owf_claim_process_ajax_nonce" id="owf_claim_process_ajax_nonce"
       value="<?php echo esc_attr( wp_create_nonce( 'owf_claim_process_ajax_nonce' ) ); ?>"/>
<input type="hidden" name="owf_inbox_ajax_nonce" id="owf_inbox_ajax_nonce"
       value="<?php echo esc_attr( wp_create_nonce( 'owf_inbox_ajax_nonce' ) ); ?>"/>
