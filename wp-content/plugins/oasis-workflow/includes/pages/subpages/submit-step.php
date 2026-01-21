<?php
global $chkResult;
// phpcs:ignore
$action_history_id = ( isset( $_GET["oasiswf"] ) && sanitize_text_field( $_GET["oasiswf"] ) ) ? sanitize_text_field( $_GET["oasiswf"] ) : $chkResult;
// phpcs:ignore
$parent_page = ( isset( $_GET["parent_page"] ) && $_GET["parent_page"] ) ? sanitize_text_field( $_GET["parent_page"] ) : "post_edit"; //check to be called from which page

// phpcs:ignore
if ( isset( $_GET["task_user"] ) && sanitize_text_field( $_GET["task_user"] ) ) {
	$task_user = intval( sanitize_text_field( $_GET["task_user"] ) ); // phpcs:ignore
} else if ( isset( $_GET["user"] ) && sanitize_text_field( $_GET["user"] ) ) { // phpcs:ignore
	$task_user = intval( sanitize_text_field( $_GET["user"] ) ); // phpcs:ignore
} else {
	$task_user = "";
}
$editable            = current_user_can( 'edit_posts' );
$post_id             = null; // phpcs:ignore
$ow_process_flow     = new OW_Process_Flow();
$ow_history_service  = new OW_History_Service();
$ow_workflow_service = new OW_Workflow_Service();

if ( $action_history_id ) {
	$current_action = $ow_history_service->get_action_history_by_id( $action_history_id );
	$current_step   = $ow_workflow_service->get_step_by_id( $current_action->step_id );
	$process        = $ow_workflow_service->get_gpid_dbid( $current_step->workflow_id, $current_action->step_id, "process" );
	$step_info      = json_decode( $current_step->step_info );
	$process_type   = $step_info->process;
	$post_id        = $current_action->post_id; // phpcs:ignore
}
$default_due_days = get_option( 'oasiswf_default_due_days' );
$default_date     = date_i18n( OASISWF_EDIT_DATE_FORMAT, current_time( 'timestamp' ) );
if ( ! empty( $default_due_days ) ) {
	$default_date = date( OASISWF_EDIT_DATE_FORMAT, current_time( 'timestamp' ) + DAY_IN_SECONDS * $default_due_days ); // phpcs:ignore
}
$reminder_days       = get_option( 'oasiswf_reminder_days' );
$reminder_days_after = get_option( 'oasiswf_reminder_days_after' );

$workflow_terminology_options = get_option( 'oasiswf_custom_workflow_terminology' );
$sign_off_label               = ! empty( $workflow_terminology_options['signOffText'] ) ? $workflow_terminology_options['signOffText'] : esc_html__( 'Sign Off', 'oasisworkflow' );
$assign_actors_label          = ! empty( $workflow_terminology_options['assignActorsText'] ) ? $workflow_terminology_options['assignActorsText'] : esc_html__( 'Assign Actor(s)', 'oasisworkflow' );
$due_date_label               = ! empty( $workflow_terminology_options['dueDateText'] ) ? $workflow_terminology_options['dueDateText'] : esc_html__( 'Due Date', 'oasisworkflow' );
$priority_label               = ! empty( $workflow_terminology_options['taskPriorityText'] ) ? $workflow_terminology_options['taskPriorityText'] : esc_html__( 'Priority', 'oasisworkflow' );

?>
<div class="info-setting owf-hidden" id="new-step-submit-div">
    <div class="dialog-title"><strong><?php echo esc_html( $sign_off_label ); ?></strong></div>
    <div id="ow-step-messages" class="owf-hidden"></div>
    <div id="message_div"></div>
    <div id="step-info-contents">
        <div class="select-part">
            <label><?php echo esc_html__( "Action : ", "oasisworkflow" ); ?></label>
            <select id="decision-select" style="width:200px;">
                <option></option>
                <option
                        value="complete"><?php echo ( $process == "review" ) ? esc_html__( "Approved", "oasisworkflow" ) : esc_html__( "Complete", "oasisworkflow" ); // phpcs:ignore ?></option>
                <option
                        value="unable"><?php echo ( $process == "review" ) ? esc_html__( "Reject", "oasisworkflow" ) : esc_html__( "Unable to Complete", "oasisworkflow" ); // phpcs:ignore ?></option>
            </select>
            <br class="clear">
        </div>
        <div id="immediately-div">
			<?php if ( $process_type == "publish" ): ?>
                <label><?php echo esc_html__( "Publish", "oasisworkflow" ); ?> : </label>
				<?php
				$publish_date_settings = get_option( 'oasiswf_publish_date_setting' );
				$post                  = get_post( $post_id ); // phpcs:ignore
				if ( ( $post->post_date !== '0000-00-00 00:00:00' && $post->post_date_gmt === '0000-00-00 00:00:00' ) ||
				     $publish_date_settings === 'hide' ) {
					$is_immediately = true;
				} else {
					$is_immediately = false;
				}
				?>
                <label for="immediately-chk">
                    <input type="checkbox"
                           id="immediately-chk" <?php echo $is_immediately ? 'checked="checked"' : ''; ?> />&nbsp;&nbsp;<?php echo esc_html__( "Immediately", "oasisworkflow" ); ?>
                </label>&nbsp;&nbsp;
                <span id="immediately-span" style="display:none;">
                   <?php $ow_process_flow->get_immediately_content( $post_id ); ?>
               </span>
                <br class="clear">
			<?php endif; ?>
        </div>

		<?php apply_filters( 'owf_signoff_last_step_publish_message', $post_id, $action_history_id ); ?>

        <div id="sum_step_info">
            <div class="select-info">
                <label><?php echo esc_html__( "Step : ", "oasisworkflow" ); ?></label>
                <select id="step-select" name="step-select" style="width:150px;">
                    <option></option>
                </select><span id="step-loading-span"></span>
                <br class="clear">
            </div>
			<?php if ( get_option( 'oasiswf_priority_setting' ) == 'enable_priority' ) { ?>
                <div class="select-info">
                    <label><?php echo esc_html( $priority_label ) . " :"; ?></label>
					<?php
					$sel_priority = get_post_meta( $post_id, "_oasis_task_priority", true );
					if ( empty( $sel_priority ) ) {
						$sel_priority = '2normal';
					}
					?>
                    <select id="priority-select" name="priority-select">
						<?php OW_Utility::instance()->get_priority_dropdown( $sel_priority ); ?>
                    </select>
                    <span id="step-loading-span"></span>
                    <br class="clear">
                </div>
			<?php } ?>

			<?php
			// Create hidden priority field if site admin has disable the priority in mid step
			if ( get_option( 'oasiswf_priority_setting' ) != 'enable_priority' && get_post_meta( $post_id, "_oasis_task_priority", true ) != '' ) :
				?>
                <input type="hidden" id="priority-select" name="priority-select"
                       value="<?php esc_attr_e( get_post_meta( $post_id, "_oasis_task_priority", true ) ); ?>"/>
			<?php endif; ?>
            <div id="one-actors-div" class="select-info">
                <label><?php echo esc_html__( "Assign actor : ", "oasisworkflow" ); ?></label>
                <select id="actor-one-select" name="actor-one-select" style="width:150px;"
                        real="assign-loading-span"></select>
                <span class="assign-loading-span">&nbsp;</span>
                <br class="clear">
            </div>
            <div id="multi-actors-div" class="select-info" style="height:120px;">
                <label><?php echo esc_html( $assign_actors_label ) . " :"; ?></label>
                <div class="select-actors-div">
                    <div class="select-actors-list">
                        <label><?php echo esc_html__( "Available", "oasisworkflow" ); ?></label>
                        <span class="assign-loading-span" style="float:right;margin-top:-18px;">&nbsp;</span>
                        <br class="clear">
                        <p>
                            <select id="actors-list-select" name="actors-list-select" size=10
                                    multiple="multiple"></select>
                        </p>
                    </div>
                    <div class="select-actors-div-point">
                        <a href="#" id="assignee-set-point"><img
                                    src="<?php echo esc_url( OASISWF_URL ) . "img/role-set.png"; ?>"
                                    style="border:0px;"/></a><br><br>
                        <a href="#" id="assignee-unset-point"><img
                                    src="<?php echo esc_url( OASISWF_URL ) . "img/role-unset.png"; ?>"
                                    style="border:0px;"/></a>
                    </div>
                    <div class="select-actors-list">
                        <label><?php echo esc_html__( "Assigned", "oasisworkflow" ); ?></label><br class="clear">
                        <p>
                            <select id="actors-set-select" name="actors-set-select" size=10
                                    multiple="multiple"></select>
                        </p>
                    </div>
                </div>
                <br class="clear">
            </div>
			<?php if ( $default_due_days != '' || $reminder_days != '' || $reminder_days_after != '' ): ?>
                <div class="select-info owf-text-info owf-clearfix">
                    <div class="full-width">
                        <label><?php echo esc_html( $due_date_label ) . " : "; ?></label>
                        <input class="date_input" id="due-date" value="<?php echo esc_attr( $default_date ); ?>"/>
                        <button class="date-clear"><?php echo esc_html__( "clear", "oasisworkflow" ); ?></button>
                    </div>
                    <br class="clear">
                </div>
			<?php endif; ?>
        </div>
        <div class="select-info owf-text-info full-width owf-comment-textarea" id="comments-div">
            <div class="full-width">
                <label><?php echo esc_html__( "Comments : ", "oasisworkflow" ); ?></label>
                <textarea id="workflowComments" class="workflow-comments"></textarea>
            </div>
            <br class="clear">
        </div>
    </div>
    <div class="dialog-title" style="padding-bottom:0.5em"></div>
    <br class="clear">
    <div class="select-info left changed-data-set full-width btn-submit-step-group">
        <span>&nbsp;</span>
        <input type="button" id="submitSave" class="button-primary" value="<?php echo esc_attr( $sign_off_label ); ?>"/>
        <input type="button" id="cancelSave" class="button-primary" value="<?php echo esc_attr( $sign_off_label ); ?>"/>
        <input type="button" id="completeSave" class="button-primary"
               value="<?php echo esc_attr( $sign_off_label ); ?>"/>
        <a href="#" id="submitCancel"><?php echo esc_html__( "Cancel", "oasisworkflow" ); ?></a>
    </div>
    <br class="clear">
    <input type="hidden" id="hi_post_id" value="<?php echo esc_attr( $post_id ); ?>"/>
    <input type="hidden" id="hi_oasiswf_id" name="hi_oasiswf_id" value="<?php echo esc_attr( $action_history_id ); ?>"/>
    <input type="hidden" id="hi_editable" value="<?php echo esc_attr( $editable ); ?>"/>
    <input type="hidden" id="hi_parrent_page" value="<?php echo esc_attr( $parent_page ); ?>"/>
    <input type="hidden" id="hi_current_process" value="<?php echo esc_attr( $process ); ?>"/>
    <input type="hidden" id="hi_task_user" value="<?php echo esc_attr( $task_user ); ?>"/>
    <input type="hidden" name="owf_signoff_ajax_nonce" id="owf_signoff_ajax_nonce"
           value="<?php echo esc_attr( wp_create_nonce( 'owf_signoff_ajax_nonce' ) ); ?>"/>
</div>
