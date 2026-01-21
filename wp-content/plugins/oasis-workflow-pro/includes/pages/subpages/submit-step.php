<?php
/*
 * Submit Step Popup
 *
 * @copyright   Copyright (c) 2016, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

global $chkResult;
// phpcs:ignore WordPress.Security.NonceVerification
$action_history_id = ( isset( $_GET["oasiswf"] ) && sanitize_text_field( $_GET["oasiswf"] ) ) ? sanitize_text_field( $_GET["oasiswf"] ) : $chkResult;

// if elementor then check $current_history_id exists rather then $_GET['oasiswf']
if( empty( $action_history_id ) && isset( $current_history_id ) && ! empty( $current_history_id ) ) {
    $action_history_id = $current_history_id;
}

// phpcs:ignore WordPress.Security.NonceVerification
$parent_page       = ( isset( $_GET["parent_page"] ) && sanitize_text_field( $_GET["parent_page"] ) ) ? sanitize_text_field( $_GET["parent_page"] ) : "post_edit"; //check to be called from which page

// phpcs:ignore WordPress.Security.NonceVerification
if ( isset( $_GET["task_user"] ) && sanitize_text_field( $_GET["task_user"] ) ) {
	$task_user = intval( sanitize_text_field( $_GET["task_user"] ) ); // phpcs:ignore WordPress.Security.NonceVerification
} else if ( isset( $_GET["user"] ) && sanitize_text_field( $_GET["user"] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	$task_user = intval( sanitize_text_field( $_GET["user"] ) ); // phpcs:ignore WordPress.Security.NonceVerification
} else {
	$task_user = "";
}
$editable            = current_user_can( 'edit_posts' );
$the_post_id             = null;
$ow_process_flow     = new OW_Process_Flow();
$ow_history_service  = new OW_History_Service();
$ow_workflow_service = new OW_Workflow_Service();

if ( $action_history_id ) {
	$current_action = $ow_history_service->get_action_history_by_id( $action_history_id );
	$current_step   = $ow_workflow_service->get_step_by_id( $current_action->step_id );

	$process = $ow_workflow_service->get_gpid_dbid( $current_step->workflow_id,
		$current_action->step_id, "process" );

	$step_info                = json_decode( $current_step->step_info );
	$process_type             = $step_info->process;
	$last_step_publish_status = isset( $step_info->last_step_post_status ) ? $step_info->last_step_post_status : "";
	$signoff_action_success   = isset( $step_info->signoff_success_action ) &&
	                            ( ! empty( $step_info->signoff_success_action ) )
		? $step_info->signoff_success_action
		: ( $process == "review" ? esc_html__( "Approve", "oasisworkflow" )
			: esc_html__( "Complete", "oasisworkflow" ) );

	$signoff_action_failure = isset( $step_info->signoff_failure_action ) &&
	                          ( ! empty( $step_info->signoff_failure_action ) )
		? $step_info->signoff_failure_action
		: ( $process == "review" ? esc_html__( "Reject", "oasisworkflow" )
			: esc_html__( "Unable to Complete", "oasisworkflow" ) );

	$the_post_id             = $current_action->post_id;
	$show_failure_option = $ow_process_flow->show_failure_decision_option( $action_history_id );

}
$default_due_days    = get_option( 'oasiswf_default_due_days' );
$reminder_days       = get_option( 'oasiswf_reminder_days' );
$reminder_days_after = get_option( 'oasiswf_reminder_days_after' );

$workflow_terminology_options = get_option( 'oasiswf_custom_workflow_terminology' );

$sign_off_label      = ! empty( $workflow_terminology_options['signOffText'] )
	? $workflow_terminology_options['signOffText'] : esc_html__( 'Sign Off', 'oasisworkflow' );
$assign_actors_label = ! empty( $workflow_terminology_options['assignActorsText'] )
	? $workflow_terminology_options['assignActorsText'] : esc_html__( 'Assign Actor(s)', 'oasisworkflow' );
$due_date_label      = ! empty( $workflow_terminology_options['dueDateText'] )
	? $workflow_terminology_options['dueDateText'] : esc_html__( 'Due Date', 'oasisworkflow' );
$priority_label      = ! empty( $workflow_terminology_options['taskPriorityText'] )
	? $workflow_terminology_options['taskPriorityText'] : esc_html__( 'Priority', 'oasisworkflow' );

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
                <option value="complete"><?php echo esc_html( $signoff_action_success ); ?></option>
				<?php if ( $show_failure_option ) : ?>
                    <option value="unable"><?php echo esc_html( $signoff_action_failure ); ?></option>
				<?php endif; ?>
            </select>
            <br class="clear">
        </div>

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
					$sel_priority = get_post_meta( $the_post_id, "_oasis_task_priority", true );
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
			if ( get_option( 'oasiswf_priority_setting' ) != 'enable_priority' &&
			     get_post_meta( $the_post_id, "_oasis_task_priority", true ) != '' ) : ?>
                <input type="hidden" id="priority-select" name="priority-select"
                       value="<?php echo esc_attr( get_post_meta( $the_post_id, "_oasis_task_priority", true ) ); ?>"/>
			<?php endif; ?>
			<?php
			// If team addon is active display team list
			do_action( 'owf_assignee_list' );
			?>
            <div id="multiple-actors-div" class="select-info owf-hidden" style="height:120px;">
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
                                src="<?php echo esc_url( OASISWF_URL . "img/role-set.png" ); ?>"
                                style="border:0px;"/></a><br><br>
                        <a href="#" id="assignee-unset-point"><img
                                src="<?php echo esc_url( OASISWF_URL . "img/role-unset.png" ); ?>"
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
                        <input class="date_input" id="due-date" value=""/>
                        <button class="date-clear"><?php echo esc_html__( "clear", "oasisworkflow" ); ?></button>
                    </div>
                    <br class="clear">
                </div>
			<?php endif; ?>
        </div>
    </div>

    <div id="ow-step-custom-data" class="owf-hidden owf-text-info left full-width"></div>

    <div id="immediately-div">
		<?php if ( $process_type == "publish" ):
			// If last step post status empty or not equal to publish, future or private than hide publish section
			$hide_publish_section = "owf-hidden";
			$publish_statuses = array( "publish", "future", "private" );
			if ( in_array( $last_step_publish_status, $publish_statuses ) || $last_step_publish_status == "" ) :
				$hide_publish_section = "";
			endif;
			?>

            <div class="owf-text-info left full-width <?php echo esc_attr( $hide_publish_section ); ?>">
                <div class="left">
                    <label><?php echo esc_html__( "Publish", "oasisworkflow" ); ?> : </label>
                </div>
                <div class="left">
					<?php
					$publish_date_settings = get_option( 'oasiswf_publish_date_setting' );

					$the_post = get_post( $the_post_id );
					if ( ( $the_post->post_date !== '0000-00-00 00:00:00' &&
					       $the_post->post_date_gmt === '0000-00-00 00:00:00' ) ||
					     $publish_date_settings === 'hide' ) {
						$is_immediately = true;
					} else {
						$is_immediately = false;
					}
					?>
                    <label for="immediately-chk">
                        <input type="checkbox"
                               id="immediately-chk" <?php echo esc_attr( $is_immediately ) ? 'checked="checked"'
	                        : ''; ?> />&nbsp&nbsp;
	                    <?php echo esc_html__( "Immediately", "oasisworkflow" ); ?>
                    </label>
                    <span id="immediately-span" style="display:none;">
							<?php $ow_process_flow->get_immediately_content( $the_post_id ); ?>
						</span>
                    <br class="clear">
                </div>
            </div>
            <form id="custom-fields">
				<?php
				$html = "";
				if ( has_filter( 'owf_display_publish_custom_fields' ) ) {
					$html = apply_filters( 'owf_display_publish_custom_fields', $task_user, $process_type, $html );
				}
				echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
            </form>
		<?php endif; ?>
    </div>

    <div class="select-info owf-text-info left full-width owf-comment-textarea" id="comments-div">
        <div class="full-width">
            <label><?php echo esc_html__( "Comments : ", "oasisworkflow" ); ?></label>
            <textarea id="workflowComments" class="workflow-comments"></textarea>
        </div>
        <br class="clear">
    </div>

    <div id="update_publish_msg" class="ow-info-message left owf-hidden">
		<?php echo esc_html__( "Signing off will copy over the contents of this revised article to the corresponding published/original article. This will happen either immediately or on the scheduled date/time.",
			"oasisworkflow" ); ?>
    </div>

    <div class="select-info left changed-data-set full-width btn-submit-step-group">
        <div class="dialog-title" style="padding-bottom:0.5em"></div>
        <br class="clear">

        <span>&nbsp;</span>
        <input type="button" id="submitSave" class="button-primary" value="<?php echo esc_attr( $sign_off_label ); ?>"/>
        <input type="button" id="cancelSave" class="button-primary" value="<?php echo esc_attr( $sign_off_label ); ?>"/>
        <input type="button" id="completeSave" class="button-primary"
               value="<?php echo esc_attr( $sign_off_label ); ?>"/>
        <a href="#" id="submitCancel"><?php echo esc_html__( "Cancel", "oasisworkflow" ); ?></a>
    </div>
    <br class="clear">
    <input type="hidden" id="hi_post_id" value="<?php echo esc_attr( $the_post_id ); ?>"/>
    <input type="hidden" id="hi_oasiswf_id" name="hi_oasiswf_id" value="<?php echo esc_attr( $action_history_id ); ?>"/>
    <input type="hidden" id="hi_editable" value="<?php echo esc_attr( $editable ); ?>"/>
    <input type="hidden" id="hi_parrent_page" value="<?php echo esc_attr( $parent_page ); ?>"/>
    <input type="hidden" id="hi_current_process" value="<?php echo esc_attr( $process ); ?>"/>
    <input type="hidden" id="hi_task_user" value="<?php echo esc_attr( $task_user ); ?>"/>
    <input type="hidden" id="hi_is_team" value=""/>
    <input type="hidden" id="hi_custom_condition" value=""/>
    <input type="hidden" class="owf-bypass-warning" value=""/>
    <input type="hidden" name="owf_signoff_ajax_nonce" id="owf_signoff_ajax_nonce"
           value="<?php echo esc_attr( wp_create_nonce( 'owf_signoff_ajax_nonce' ) ); ?>"/>
    <input type="hidden" name="owf_claim_process_ajax_nonce" id="owf_claim_process_ajax_nonce"
           value="<?php echo esc_attr( wp_create_nonce( 'owf_claim_process_ajax_nonce' ) ); ?>"/>
    <input type="hidden" name="owf_inbox_ajax_nonce" id="owf_inbox_ajax_nonce"
           value="<?php echo esc_attr( wp_create_nonce( 'owf_inbox_ajax_nonce' ) ); ?>"/>
</div>
<div id="reassign-div"></div>