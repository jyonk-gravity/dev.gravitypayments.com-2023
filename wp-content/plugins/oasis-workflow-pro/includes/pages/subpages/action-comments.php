<?php
/*
 * Sign off Comments Popup
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

$workflow_comments  = array();
$ow_process_flow    = new OW_Process_Flow();
$ow_history_service = new OW_History_Service();

// in the inbox, we want to show all the comments and not just the latest comments
if ( isset( $_POST['comment'] ) && sanitize_text_field( $_POST['comment'] ) == 'inbox_comment' ) { // phpcs:ignore
	$content_id        = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : ""; // phpcs:ignore
	$action_history_id = isset( $_POST['actionid'] ) ? intval( $_POST['actionid'] ) : ""; // phpcs:ignore

	// get previous assignment and publish comments from post id
	$action_history_ids = array();
	$results            = $ow_process_flow->get_assignment_comment_for_post( $content_id );
	if ( $results ) {
		foreach ( $results as $result ) {
			if ( $result->action_status !== 'processed' && $result->assign_actor_id == - 1 ) {
				$review_step_action_history_ids[] = $result->ID;
			}
			$action_history_ids[] = $result->ID;
			if ( ! empty( $result->comment ) ) {
				$workflow_comments[] = json_decode( $result->comment );
			}
		}
	}

	if ( ! empty( $review_step_action_history_ids ) ) {
		$results = $ow_process_flow->get_comments_for_review_steps( $review_step_action_history_ids );
		if ( $results ) {
			foreach ( $results as $result ) {
				if ( ! empty( $result->comments ) ) {
					$workflow_comments[] = json_decode( $result->comments );
				}
			}
		}
	}

	// sort the comments via timestamp
	usort( $workflow_comments, function ( $a, $b ) {
		$a = $a[0];
		$b = $b[0];
		if ( isset( $a->comment_timestamp ) && isset( $b->comment_timestamp ) ) {
			return $a->comment_timestamp < $b->comment_timestamp ? 1 : - 1; // need to switch 1 and -1
		} else {
			return 1;
		}
	} );
}
$page_action   = isset( $_POST["page_action"] ) ? sanitize_text_field( $_POST["page_action"] ) : ""; // phpcs:ignore
$action_status = isset( $_POST["actionstatus"] ) ? sanitize_text_field( $_POST["actionstatus"] ) : ""; // phpcs:ignore
$action_id     = isset( $_POST["actionid"] ) ? intval( $_POST["actionid"] ) : ""; // phpcs:ignore
if ( $page_action == "history" ) {
	if ( $action_status == "aborted" || $action_status == "abort_no_action" ) {
		$action_details = $ow_history_service->get_action_history_by_id( $action_id );
	} else {
		$action_details = $ow_history_service->get_action_history_by_from_id( $action_id );
	}

	$content_id = $action_details->post_id;
	if ( $action_details ) {
		$workflow_comments[] = json_decode( $action_details->comment );
	}
}

if ( $page_action == "review" ) {
	if ( $action_status == "aborted" ) {
		$action_details = $ow_history_service->get_action_history_by_id( $action_id );
		$content_id     = $action_details->post_id;
	} else {
		$action_details = $ow_history_service->get_review_action_by_id( $action_id );
		$action_history = $ow_history_service->get_action_history_by_id( $action_details->action_history_id );
		$content_id     = $action_history->post_id;
	}

	if ( $action_details ) {
		$workflow_comments[] = json_decode( $action_details->comments );
	}
}
?>
<div id="ow-editorial-readonly-comment-popup">
    <div id="ow-comment-popup" class="ow-modal-dialog ow-top_15">
        <a class="ow-modal-close" onclick="ow_modal_close(event);"></a>
        <div class="ow-modal-header">
            <h3 class="ow-modal-title" id="poststuff">
	            <?php echo esc_html__( 'Editorial Comments On: ' ) . esc_html( get_the_title( $content_id ) ); ?>
            </h3>
        </div>
        <div class="ow-modal-body">
            <div class="ow-textarea">
                <div id="ow-scrollbar" class="ow-comment-popup-scrollbar">
					<?php
					if ( $workflow_comments ) {
						foreach ( $workflow_comments as $object ) {
							if ( $object ) {
								foreach ( $object as $workflow_comment ) {
									$send_id   = $workflow_comment->send_id;
									$user      = OW_Utility::instance()->get_user_role_and_name( $send_id );
									$timestamp = "";
									if ( ! empty ( $workflow_comment->comment_timestamp ) ) {
										$timestamp = OW_Utility::instance()->format_date_for_display(
											$workflow_comment->comment_timestamp, '-', 'datetime' );
									}
									?>
                                    <ul id="readonly-comments">
                                        <li>
											<?php echo get_avatar( $send_id, 64 ); ?>
                                            <p class="author-name"><?php echo esc_html($user->username); ?></p>
                                            <p class="author-role"><?php echo esc_html($user->role); ?></p>
                                        </li>
                                        <li>
                                            <div class="ow-signed-off">
                                                <p class="ow-sign-off-text"><?php esc_html_e( 'Signed off on', 'oasisworkflow' ); ?> <span
                                                        class="timestamp"><?php echo esc_html( $timestamp ); ?></span>
                                                </p>
                                                <p class="ow-signed-off-comment"><?php echo $workflow_comment->comment !== '' ? wp_kses_post( $workflow_comment->comment ) : esc_html__( 'No Comments', 'oasisworkflow' ); ?></p>
                                            </div>

                                        </li>
                                    </ul>
									<?php
								}
							}
						}
					}
					?>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>

        <div class="ow-modal-footer">
            <a href="#" onclick="ow_modal_close(event);" class="modal-close"><?php esc_html_e( 'Close',
					'oasisworkflow' ); ?></a>
        </div>
    </div>
    <div class="ow-overlay"></div>
</div>

<script>
    function ow_modal_close (event) {
        event.preventDefault();
        jQuery(document).find('#post_com_count_content').html('');
        jQuery(document).find('.post-com-count').show();
        jQuery('.loading').hide();
        jQuery('#ow-editorial-readonly-comment-popup').remove();
    }
</script>