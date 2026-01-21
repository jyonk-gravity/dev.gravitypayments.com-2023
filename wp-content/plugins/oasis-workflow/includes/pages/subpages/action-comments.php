<?php
// phpcs:ignore
$comments           = array();
$ow_process_flow    = new OW_Process_Flow();
$ow_history_service = new OW_History_Service();

// in the inbox, we want to show all the comments and not just the latest comments
// phpcs:ignore
if ( isset( $_POST['comment'] ) && sanitize_text_field( $_POST['comment'] ) == 'inbox_comment' ) {
	global $wpdb;
	$post_id           = intval( sanitize_text_field( $_POST['post_id'] ) ); // phpcs:ignore
	$action_history_id = intval( sanitize_text_field( $_POST['actionid'] ) ); // phpcs:ignore

	// get previous assignment and publish comments from post id
	$action_history_ids = array();
	$results            = $ow_process_flow->get_assignment_comment_for_post( $post_id );
	if ( $results ) {
		foreach ( $results as $result ) {
			$action_history_ids[] = $result->ID;
			if ( ! empty( $result->comment ) ) {
				$comments[] = json_decode( $result->comment ); // phpcs:ignore
			}
		}
	}
	if ( ! empty( $action_history_ids ) ) {
		$results = $ow_process_flow->get_reassigned_comments_for_review_steps( $action_history_ids );
		if ( $results ) {
			foreach ( $results as $result ) {
				if ( ! empty( $result->comments ) ) {
					$comments[] = json_decode( $result->comments ); // phpcs:ignore
				}
			}
		}
	}

	// sort the comments via timestamp
	usort( $comments, function ( $a, $b ) {
		$a = $a[0];
		$b = $b[0];
		if ( isset( $a->comment_timestamp ) && isset( $b->comment_timestamp ) ) {
			return $a->comment_timestamp < $b->comment_timestamp ? 1 : - 1; // need to switch 1 and -1
		} else {
			return 1;
		}
	} );
}

// phpcs:ignore
if ( isset( $_POST["page_action"] ) && $_POST["page_action"] == "history" ) {
	// phpcs:ignore
	if ( $_POST["actionstatus"] == "aborted" || $_POST["actionstatus"] == "abort_no_action" ) {
		$action = $ow_history_service->get_action_history_by_id( $_POST["actionid"] ); // phpcs:ignore
	} else {
		$action = $ow_history_service->get_action_history_by_from_id( $_POST["actionid"] ); // phpcs:ignore
	}
	$post_id = $action->post_id; // phpcs:ignore
	if ( $action ) {
		$comments[] = json_decode( $action->comment ); // phpcs:ignore
	}
}

// phpcs:ignore
if ( isset( $_POST["page_action"] ) && $_POST["page_action"] == "review" ) {
	// phpcs:ignore
	if ( $_POST["actionstatus"] == "aborted" ) {
		$action = $ow_history_service->get_action_history_by_id( $_POST["actionid"] ); // phpcs:ignore
	} else {
		$action         = $ow_history_service->get_review_action_by_id( $_POST["actionid"] ); // phpcs:ignore
		$action_history = $ow_history_service->get_action_history_by_id( $action->action_history_id );
	}
	// phpcs:ignore
	$post_id = $action_history->post_id;
	if ( $action ) {
		$comments[] = json_decode( $action->comments ); // phpcs:ignore
	}
}
?>
<div id="ow-editorial-readonly-comment-popup">
    <div id="ow-comment-popup" class="ow-modal-dialog ow-top_15">
        <a class="ow-modal-close" onclick="ow_modal_close(event);"></a>
        <div class="ow-modal-header">
            <h3 class="ow-modal-title"
                id="poststuff"><?php echo esc_html__( 'Editorial Comments On: ' ) . esc_html( get_the_title( $post_id ) ); ?></h3>
        </div>
        <div class="ow-modal-body">
            <div class="ow-textarea">
                <div id="ow-scrollbar" class="ow-comment-popup-scrollbar">
					<?php
					if ( $comments ) {
						foreach ( $comments as $object ) {
							if ( $object ) {
								// phpcs:ignore
								foreach ( $object as $comment ) {
									$send_id   = $comment->send_id;
									$user      = OW_Utility::instance()->get_user_role_and_name( $send_id );
									$timestamp = "";
									if ( ! empty ( $comment->comment_timestamp ) ) {
										$timestamp = OW_Utility::instance()->format_date_for_display( $comment->comment_timestamp, '-', 'datetime' );
									}
									?>
                                    <ul id="readonly-comments">
                                        <li>
											<?php echo get_avatar( $send_id, 64 ); ?>
                                            <p class="author-name"><?php echo esc_html( $user->username ); ?></p>
                                            <p class="author-role"><?php echo esc_html( $user->role ); ?></p>
                                        </li>
                                        <li>
                                            <div class="ow-signed-off">
                                                <p class="ow-sign-off-text"><?php esc_html_e( 'Signed off on', 'oasisworkflow' ); ?>
                                                    <span class="timestamp"><?php echo esc_html( $timestamp ); ?></span>
                                                </p>
                                                <p class="ow-signed-off-comment"><?php echo $comment->comment !== '' ? wp_kses_post( $comment->comment ) : esc_html__( 'No Comments', 'oasisworkflow' ); ?></p>
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
            <a href="#" onclick="ow_modal_close(event);"
               class="modal-close"><?php esc_html_e( 'Close', 'oasisworkflow' ); ?></a>
        </div>
    </div>
    <div class="ow-overlay"></div>
</div>
<script>
    function ow_modal_close(event) {
        event.preventDefault()
        jQuery(document).find('#post_com_count_content').html('')
        jQuery(document).find('.post-com-count').show()
        jQuery('.loading').hide()
        jQuery('#ow-editorial-readonly-comment-popup').remove()
    }
</script>