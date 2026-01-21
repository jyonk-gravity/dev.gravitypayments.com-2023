<?php
/*
 * Workflow List Page
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

$default = apply_filters( 'ow_workflow_list_default', 'active' );
$action  = ( isset( $_GET['action'] ) && sanitize_text_field( $_GET["action"] ) ) ? sanitize_text_field( $_GET["action"] ) : $default; // phpcs:ignore
$pagenum = ( isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] ) ) ? intval( sanitize_text_field( $_GET["paged"] ) ) : 1; // phpcs:ignore

$workflow_service = new OW_Workflow_Service();
$workflows        = $workflow_service->get_workflow_list( $action );

$wf_class[ $action ] = 'class="current"';
$wf_count_by_status  = $workflow_service->get_workflow_count_by_status();
$workflow_count      = count( $workflows );

$per_page = OASIS_PER_PAGE; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
?>
<form id="wf-form" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=oasiswf-admin' ) ); ?>">
    <input type="hidden" id="hi_wf_id" name="wf_id"/>
    <input type="hidden" id="save_action" name="save_action" value="workflow_copy"/>
    <input type="hidden" id="define-workflow-title" name="define-workflow-title"/>
    <input type="hidden" id="define-workflow-description" name="define-workflow-description"/>
	<?php wp_nonce_field( 'owf_workflow_create_nonce', 'owf_workflow_create_nonce' ); ?>
</form>
<?php
// include the file for the workflow copy popup
include( OASISWF_PATH . 'includes/pages/subpages/workflow-copy-popup.php' );
?>
<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
    <h2><?php echo esc_html__( "Edit Workflows", "oasisworkflow" ) ?>
		<?php
		if ( current_user_can( 'ow_create_workflow' ) ) {
			?>
            <a href="admin.php?page=oasiswf-add" class="add-new-h2"><?php echo esc_html__( "Add New",
					"oasisworkflow" ); ?></a>
			<?php
		}
		?>
    </h2>

	<?php
	// Display license notices
	OW_Utility::instance()->display_license_notices( "Oasis Workflow Pro",
		"oasiswf_license_key", "oasiswf_license_expiry" );
	?>
    <div id="view-workflow">
        <div class="tablenav">
            <ul class="subsubsub">
				<?php
				$active_val   = isset( $wf_class["active"] ) ? $wf_class["active"] : "";
				$inactive_val = isset( $wf_class["inactive"] ) ? $wf_class["inactive"] : "";
				$all_val      = isset( $wf_class["all"] ) ? $wf_class["all"] : "";
				echo '<li class="all"><a href="admin.php?page=oasiswf-admin&action=all"' . wp_kses_data( $all_val ) . ' >' .
				     esc_html__( 'All', "oasisworkflow" ) .
				     '<span class="count"> (' . esc_html( $wf_count_by_status->wf_all ) . ')</span></a></li>';
				echo ' | <li class="all"><a href="admin.php?page=oasiswf-admin&action=active"' .
				wp_kses_data( $active_val ) . '>' .
				     esc_html__( 'Active', "oasisworkflow" ) .
				     '<span class="count"> (' . esc_html( $wf_count_by_status->wf_active ) . ')</span></a> </li>';
				echo ' | <li class="all"><a href="admin.php?page=oasiswf-admin&action=inactive"' .
				wp_kses_data( $inactive_val ) . '>' .
				     esc_html__( 'Inactive', "oasisworkflow" ) .
				     '<span class="count"> (' . esc_html( $wf_count_by_status->wf_inactive ) . ')</span></a> </li>';
				?>
            </ul>
            <div class="tablenav-pages">
				<?php OW_Utility::instance()->get_page_link( $workflow_count, $pagenum, $per_page ); ?>
            </div>

        </div>
        <form method="post">
            <table class="wp-list-table widefat fixed posts" cellspacing="0" border="0">
                <thead>
				<?php $workflow_service->get_table_header(); ?>
                </thead>
                <tfoot>
				<?php $workflow_service->get_table_header(); ?>
                </tfoot>
                <tbody id="coupon-list">
				<?php
				if ( $workflows ):
					$act   = array( "", "active" );
					$count = 0;
					$start = ( $pagenum - 1 ) * $per_page;
					$end   = $start + $per_page;
					foreach ( $workflows as $wf ) {
						if ( $count >= $end ) {
							break;
						}
						if ( $count >= $start ) {
							$postcount = $workflow_service->get_post_count_in_workflow( $wf->ID );
							$valid     = ( $wf->is_valid ) ? "Yes" : "No";
							echo "<tr class='alternate author-self status-publish format-default iedit'>";
							echo "<th scope='row' class='check-column'><input type='checkbox' name='workflows[]' value='" .
							     esc_attr( $wf->ID ) . "'></th>";
							echo "<td class='column-primary' data-colname='Workflow ID'>" . esc_html( $wf->ID ) .
							     "<button type='button' class='toggle-row'><span class='screen-reader-text'>" .
							     esc_html__( "Show more details", "oasisworkflow" ) .
							     "</span></button></td>";
							echo "<td data-colname='Title'>";
							$class    = "";
							$content  = "";
							$datadesc = "";
							if ( ! empty( $wf->description ) ) {
								// display description on mouse over
								$class    = "wf-desc";
								$content  = "hover-data";
								$datadesc = $wf->description;
							}
							echo "<a href='admin.php?page=oasiswf-admin&wf_id=" . esc_attr( $wf->ID ) .
							     "' class='" . esc_attr( $class ) . "'>";
							echo "<div class='bold-label' id=" . "workflow-name-" . esc_attr( $wf->ID ) .
							     ">" . esc_html( $wf->name ) . "</div>";
							echo "<span class='" . esc_attr( $content ) . "'>" . esc_html( $datadesc ) . "</span></a>";
							echo "<div class='row-actions'>";

							$workflow_row_actions = $workflow_service->display_workflow_row_actions( $wf->ID,
								$postcount );

							$action_count = count( $workflow_row_actions );
							$i            = 0;

							foreach ( $workflow_row_actions as $action ) { // phpcs:ignore
								++ $i;
								( $i == $action_count ) ? $sep = '' : $sep = ' | ';
								echo "<span>" . $action . esc_attr( $sep ) . "</span>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							echo "</div>
									</td>";
							echo "<td data-colname='Version'>" . esc_html( $wf->version ) . "</td>";
							echo "<td data-colname='Start Date'>" .
							     OW_Utility::instance()->format_date_for_display( $wf->start_date ) . "</td>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo "<td data-colname='End Date'>" .
							     OW_Utility::instance()->format_date_for_display( $wf->end_date ) . "</td>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo "<td data-colname='Post/Pages in workflow'>" . esc_html( $postcount ) . "</td>";
							echo "<td data-colname='Is Valid?'>" . esc_html( $valid ) . "</td>";
							echo "</tr>";
						}
						$count ++;
					}
				else:
					if ( $action == "all" && current_user_can( 'ow_create_workflow' ) ) {
						$msg = "<label>" . esc_html__( "You don't have any workflows. Let's go ", "oasisworkflow" ) . "</label>
								<a href='admin.php?page=oasiswf-add'>" . esc_html__( "create one", "oasisworkflow" ) .
						       "</a> !";
					} else {
						$msg = esc_html__( "You don't have ", "oasisworkflow" ) .
						       esc_attr( $action ) .
						       esc_html__( " workflows", "oasisworkflow" );
					}
					echo "<tr>";
					echo "<td colspan='8' class='no-found-lbl'>" . $msg . "</td>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo "</tr>";
				endif;
				?>
                </tbody>
            </table>
        </form>
        <div class="tablenav">
            <div class="tablenav-pages">
				<?php OW_Utility::instance()->get_page_link( $workflow_count, $pagenum, $per_page ); ?>
            </div>
        </div>
    </div>
</div>