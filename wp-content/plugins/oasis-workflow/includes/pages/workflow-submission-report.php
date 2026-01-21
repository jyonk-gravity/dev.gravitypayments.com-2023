<?php
// phpcs:ignore
$action = ( isset( $_REQUEST["action"] ) && sanitize_text_field( $_REQUEST["action"] ) ) ? sanitize_text_field( $_REQUEST["action"] ) : "in-workflow";
// phpcs:ignore
$post_type = ( isset( $_REQUEST["type"] ) && sanitize_text_field( $_REQUEST["type"] ) ) ? sanitize_text_field( $_REQUEST["type"] ) : "all";
// phpcs:ignore
$page_number = ( isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] ) ) ? intval( sanitize_text_field( $_GET["paged"] ) ) : 1;

$ow_report_service = new OW_Report_Service();
$ow_process_flow   = new OW_Process_Flow();

$submitted_posts    = $ow_process_flow->get_submitted_articles( $post_type );
$un_submitted_posts = $ow_process_flow->get_unsubmitted_articles( $post_type );

if ( $action == "in-workflow" ) {
	$posts = $submitted_posts; // phpcs:ignore
} else {
	$posts = $un_submitted_posts; // phpcs:ignore
}

$count_posts = count( $posts );
$per_page    = OASIS_PER_PAGE; // phpcs:ignore

$url = add_query_arg(
    array(
        'tab' => 'workflowSubmissions',
        '_wpnonce' => wp_create_nonce('ow_report_nonce'),
    ),
    '?page=oasiswf-reports'
);

?>
<div class="wrap">
    <div id="view-workflow">
        <form id="submission_report_form" method="post"
              action="<?php echo esc_url( $url ); ?>">
            <div class="tablenav top">
                <input type="hidden" name="page" value="oasiswf-submission"/>
                <input type="hidden" id="action" name="action" value="<?php echo esc_attr( $action ); ?>"/>
                <div class="alignleft actions">
                    <select name="type">
                        <option value="all" <?php echo ( $post_type == "all" ) ? "selected" : ""; ?> >All Types</option>
						<?php OW_Utility::instance()->owf_dropdown_post_types_multi( $post_type ); ?>
                    </select>
                    <input type="submit" class="button action" value="Filter"/>
                </div>
                <div>
                    <ul class="subsubsub">
						<?php
						$all       = ( $action == "all" ) ? "class='current'" : "";
						$not_in_wf = ( $action == "not-workflow" ) ? "class='current'" : "";
						$in_wf     = ( $action == "in-workflow" ) ? "class='current'" : "";
						// phpcs:ignore
						echo '<li class="all"><a id="notInWorkflow" href="#" ' . $not_in_wf . '>' . esc_html__( 'Not in Workflow', 'oasisworkflow' ) .
						     '<span class="count"> (' . count( $un_submitted_posts ) . ')</span></a> </li>';
						// phpcs:ignore
						echo ' | <li class="all"><a id="inWorkflow" href="#" ' . $in_wf . '>' . esc_html__( 'In Workflow', 'oasisworkflow' ) .
						     '<span class="count"> (' . count( $submitted_posts ) . ')</span></a> </li>';
						?>
                    </ul>
                </div>
                <div class="tablenav-pages">
					<?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page, $action ); ?>
                </div>
            </div>
        </form>
        <table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
            <thead>
			<?php $ow_report_service->get_submission_report_table_header( $action ); ?>
            </thead>
            <tfoot>
			<?php $ow_report_service->get_submission_report_table_header( $action ); ?>
            </tfoot>
            <tbody id="coupon-list">
			<?php
			if ( $posts ):
				$count = 0;
				$start = ( $page_number - 1 ) * $per_page;
				$end   = $start + $per_page;
				// phpcs:ignore
				foreach ( $posts as $post ) {
					if ( $count >= $end ) {
						break;
					}
					if ( $count >= $start ) {
						$user = get_userdata( $post->post_author );
						echo "<tr>";
						if ( $action == 'in-workflow' ) {
							echo "<td><input type='checkbox' id='abort-" . esc_attr( $post->ID ) . "' value='" . esc_attr( $post->ID ) . "' name='abort' /></td>";
						}
						echo "<td><a href='post.php?post=" . esc_attr( $post->ID ) . "&action=edit'>" . esc_html( $post->post_title ) . "</a></td>";
						echo "<td>" . esc_html( $post->post_type ) . "</td>";
						echo "<td>" . esc_html( $user->data->display_name ) . "</td>";
						echo "<td>" . esc_html( OW_Utility::instance()->format_date_for_display( $post->post_date, "-", "datetime" ) ) . "</td>";
						echo "</tr>";
					}
					$count ++;
				}
			else:
				echo "<tr>";
				echo "<td class='hurry-td' colspan='4'>
							<label class='hurray-lbl'>";
				echo esc_html__( "No Posts/Pages found in any workflows.", "oasisworkflow" );
				echo "</label></td>";
				echo "</tr>";
			endif;
			?>
            </tbody>
        </table>

		<?php if ( $action == 'in-workflow' && current_user_can( 'ow_abort_workflow' ) ) : ?>

            <div class="tablenav bottom">
                <!-- Bulk Actions Start -->
                <div class="alignleft actions">
                    <select name="action_type" id="action_type">
                        <option value="none"><?php echo esc_html__( "-- Select Action --", "oasisworkflow" ); ?></option>
                        <option value="abort"><?php echo esc_html__( "Abort", "oasisworkflow" ); ?></option>
                    </select>
                    <input type="button" class="button action" id="apply_action" value="Apply"><span
                            class='loading owf-hidden' class='inline-loading'></span>
                </div>
                <!-- Bulk Actions End -->
                <!-- Display pages Start -->
                <div class="tablenav-pages">
					<?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page, $action ); ?>
                </div>
                <!-- Display pages End -->
            </div>

		<?php endif; ?>
    </div>
    <div id="out"></div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#notInWorkflow').click(function (event) {
            jQuery('#action').val('not-workflow')
            jQuery('#submission_report_form').submit()
        })

        jQuery('#inWorkflow').click(function (event) {
            jQuery('#action').val('in-workflow')
            jQuery('#submission_report_form').submit()
        })

        jQuery('input[name=abort-all]').click(function (event) {
            jQuery('input[type=checkbox]').prop('checked', jQuery(this).prop('checked'))
        })

        jQuery('#apply_action').click(function () {
            if (jQuery('#action_type').val() == 'none')
                return

            var arr = jQuery('input[name=abort]:checked')
            var post_ids = new Array()
            jQuery.each(arr, function (k, v) {
                post_ids.push(jQuery(this).val())
            })
            if (post_ids.length === 0)
                return

            data = {
                action: 'multi_workflow_abort',
                postids: post_ids,
            }

            jQuery('.loading').show()
            jQuery.post(ajaxurl, data, function (response) {
                if (response.success) {
                    jQuery('.loading').hide()
                    jQuery('#inWorkflow').click()
                }
            })
        })

    })
</script>