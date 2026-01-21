<?php
/*
 * Workflow Inbox Page
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// sanitize the data
// phpcs:ignore WordPress.Security.NonceVerification
$selected_user = ( isset( $_GET['user'] ) && sanitize_text_field( $_GET["user"] ) ) ? intval( sanitize_text_field( $_GET["user"] ) ) : get_current_user_id();
// phpcs:ignore WordPress.Security.NonceVerification
$page_number   = ( isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] ) ) ? intval( sanitize_text_field( $_GET["paged"] ) ) : 1;
// phpcs:ignore WordPress.Security.NonceVerification
$due_date_type = ( isset( $_GET['due_date_type'] ) && sanitize_text_field( $_GET["due_date_type"] ) ) ? sanitize_text_field( $_GET["due_date_type"] ) : "none";
// phpcs:ignore WordPress.Security.NonceVerification
$priority      = ( isset( $_GET['priority'] ) && sanitize_text_field( $_GET["priority"] ) ) ? sanitize_text_field( $_GET["priority"] ) : "none";
$action = ( isset( $_REQUEST["action"] ) && sanitize_text_field( $_REQUEST["action"] ) ) ? sanitize_text_field( $_REQUEST["action"] ) : "inbox-all"; // phpcs:ignore

$request_params = array_diff_key($_GET, array('page' => 'oasiswf-inbox'));

// Verify nonce
$nonce = ( isset( $_GET['_wpnonce'] ) ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '';

// apply nonce verification only if action para
if ( ! empty( $request_params ) && ! empty( $nonce ) && ! check_admin_referer( 'ow_inbox_nonce', '_wpnonce' ) ) {
    // Nonce verification failed, handle accordingly (exit, redirect, etc.)
    exit( 'Nonce verification failed!' );
}

$parameters = array(
	"due_date_type" => $due_date_type,
	"priority"      => $priority
);

// add a filter for $parameters
$parameters = apply_filters( 'owf_inbox_parameters', $parameters );

$ow_inbox_service    = new OW_Inbox_Service();
$ow_process_flow     = new OW_Process_Flow();
$ow_workflow_service = new OW_Workflow_Service();

// get assigned posts for selected user
$inbox_items = $ow_process_flow->get_assigned_post( null, $selected_user, "rows", $parameters );
$count_posts = count( $inbox_items );
$ow_per_page    = OASIS_PER_PAGE;

// Get filtered items and count
$filtered_items = $ow_process_flow->filter_inbox_items( $inbox_items, $action );
if ( has_filter( 'owf_filter_inbox_items' ) ) {
	$filtered_items = apply_filters( 'owf_filter_inbox_items', $filtered_items, $action, $selected_user, "rows",
		$parameters );
}

$all_task_count       = $filtered_items["allTaskCount"];
$mine_task_count      = $filtered_items["mineTaskCount"];
$unclaimed_task_count = $filtered_items["unclaimedTaskCount"];

$inbox_items = $filtered_items["inboxItems"]; // reassign the $inbox_items with the new value from filtered_items
$count_posts = $filtered_items["display_count"]; // reassign the new count from filtered_items

// get workflow settings
$hide_compare_button = get_option( "oasiswf_hide_compare_button" );
$due_date_settings   = get_option( 'oasiswf_default_due_days' );

?>
<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
    <h2><?php echo esc_html__( "Inbox", "oasisworkflow" ); ?></h2>
    <div id="owf-inbox-error" class="owf-hidden"></div>
    <div id="workflow-inbox">
        <div class="tablenav top">

            <!-- Bulk Actions Start -->
			<?php do_action( 'owf_bulk_actions_section' ); ?>
            <!-- Bulk Actions End -->

            <input type="hidden" id="hidden_task_user" value="<?php echo esc_attr( $selected_user ); ?>"/>
            <form id="inbox_filter_form" method="post"
                  action="<?php echo esc_url( admin_url( 'admin.php?page=oasiswf-inbox' ) ); ?>">
                <input type="hidden" id="action" name="action" value="<?php echo esc_attr( $action ); ?>"/>
                <div class="tablenav">
                    <ul class="subsubsub">
						<?php
						$all       = ( $action == "inbox-all" ) ? "class='current'" : "";
						$mine      = ( $action == "inbox-mine" ) ? "class='current'" : "";
						$unclaimed = ( $action == "inbox-unclaimed" ) ? "class='current'" : "";
						$due_date  = $due_date_filter = "";
						if ( $due_date_settings !== "" ) {
							$due_date        = "+ '&due_date_type=' +jQuery('#inbox_due_date_filter').val()";
							$due_date_filter = "&due_date_type=' + jQuery('#inbox_due_date_filter').val() + '";
						}

                        $all_url = add_query_arg(
                            array(
                                'action' => 'inbox-all',
                                'user' => esc_attr( $selected_user ),
                                'due_date_type' => esc_attr( $due_date_type ),
                                'priority' => esc_attr( $priority ),
                                '_wpnonce' => wp_create_nonce('ow_inbox_nonce'),
                            ),
                            admin_url( 'admin.php?page=oasiswf-inbox' )
                        );
                        
                        $mine_url = add_query_arg(
                            array(
                                'action' => 'inbox-mine',
                                'user' => esc_attr( $selected_user ),
                                'due_date_type' => esc_attr( $due_date_type ),
                                'priority' => esc_attr( $priority ),
                                '_wpnonce' => wp_create_nonce('ow_inbox_nonce'),
                            ),
                            admin_url( 'admin.php?page=oasiswf-inbox' )
                        );

                        $unclaimed_url = add_query_arg(
                            array(
                                'action' => 'inbox-unclaimed',
                                'user' => esc_attr( $selected_user ),
                                'due_date_type' => esc_attr( $due_date_type ),
                                'priority' => esc_attr( $priority ),
                                '_wpnonce' => wp_create_nonce('ow_inbox_nonce'),
                            ),
                            admin_url( 'admin.php?page=oasiswf-inbox' )
                        );

						?>
                        <li id="owf_inbox_all"><a <?php echo $all; // phpcs:ignore ?>
                                href="<?php echo esc_url( $all_url ); ?>" <?php echo esc_attr( $all ); ?>
                                aria-current="page"><?php echo esc_html__( "All", "oasisworkflow" ); ?> <span
                                    class="count">(<?php echo esc_html( $all_task_count ); ?>)</span></a>
                            |
                        </li>
                        <li id="owf_inbox_mine"><a <?php echo $mine; // phpcs:ignore ?>
                                href="<?php echo esc_url( $mine_url ); ?>" <?php echo esc_attr( $mine ); ?>
                                aria-current="page"><?php echo esc_html__( "Mine", "oasisworkflow" ); ?> <span
                                    class="count">(<?php echo esc_html( $mine_task_count ); ?>)</span></a>
                            |
                        </li>
                        <li id="owf_inbox_unclaimed"><a <?php echo $unclaimed; // phpcs:ignore ?>
                                href="<?php echo esc_url( $unclaimed_url ); ?>" <?php echo esc_attr( $unclaimed ); ?>
                                aria-current="page"><?php echo esc_html__( "Unclaimed", "oasisworkflow" ); ?> <span
                                    class="count">(<?php echo esc_html( $unclaimed_task_count ); ?>)</span></a>
                        </li>
                    </ul>
                </div>
            </form>
			<?php if ( current_user_can( 'ow_view_others_inbox' ) ) { ?>
                <form action="<?php echo esc_url( admin_url( 'admin.php?page=oasiswf-inbox' ) ); ?>">
                    <input type="hidden" name="page" value="oasiswf-inbox" />
                    <input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>"/>
                    <input type="hidden" name="user" value="<?php echo esc_attr( $selected_user ); ?>"/>
                    <input type="hidden" name="due_date_type" value="<?php echo esc_attr( $due_date_type ); ?>"/>
                    <input type="hidden" name="priority" value="<?php echo esc_attr( $priority ); ?>"/>
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
            <form action="<?php echo esc_url( admin_url( 'admin.php?page=oasiswf-inbox' ) ); ?>">
                <input type="hidden" name="page" value="oasiswf-inbox" />
                <input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>" />
                <input type="hidden" name="user" value="<?php echo esc_attr( $selected_user ); ?>" />
                <?php wp_nonce_field( 'ow_inbox_nonce', '_wpnonce', false ); ?>
                <div class="tablenav alignleft actions">
                    <?php
                    do_action( 'owf_inbox_tablenav_actions_before' );

                    if ( $due_date_settings !== "" ) {
                        ?>
                        <select id="inbox_due_date_filter" name="due_date_type">
                            <option value=""><?php esc_html_e( "All Due Dates", "oasisworkflow" ); ?></option>
                            <?php OW_Utility::instance()->get_due_date_dropdown( $due_date_type ); ?>
                        </select>
                    <?php } ?>
                    <select id="inbox_priority_filter" name="priority">
                        <option value=""><?php esc_html_e( "All Priority", "oasisworkflow" ); ?></option>
                        <?php OW_Utility::instance()->get_priority_dropdown( $priority ); ?>
                    </select>
                    <?php do_action( 'owf_inbox_filter_lists' ); ?>
                    <input type="submit" class="button-secondary action"
                            value="<?php esc_attr_e( "Filter", "oasisworkflow" ); ?>"/>

                    <?php do_action( 'owf_inbox_tablenav_actions_after' ); ?>
                </div>
            </form>
            <ul class="subsubsub"></ul>
            <div class="tablenav-pages">
				<?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $ow_per_page ); ?>
            </div>
        </div>
        <table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
			<?php $inbox_column_headers = $ow_inbox_service->get_table_header(); ?>
            <thead>
            <tr>
				<?php
				echo implode( '', $inbox_column_headers ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
            </tr>
            </thead>
            <tfoot>
            <tr>
				<?php
				echo implode( '', $inbox_column_headers ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
            </tr>
            </tfoot>
            <tbody id="coupon-list">
			<?php
			$inbox_data = array(
				"page_number"    => $page_number,
				"per_page"       => $ow_per_page,
				"selected_users" => $selected_user
			);
			$ow_inbox_service->get_table_rows( $inbox_data, $inbox_items, $inbox_column_headers,
				$unclaimed_task_count );
			?>
            </tbody>
        </table>
        <div class="tablenav">
            <div class="tablenav-pages">
				<?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $ow_per_page ); ?>
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
