<?php
$action  = ( isset( $_GET['action'] ) && sanitize_text_field( $_GET["action"] ) ) ? sanitize_text_field( $_GET["action"] ) : "all"; // phpcs:ignore
$pagenum = ( isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] ) ) ? intval( sanitize_text_field( $_GET["paged"] ) ) : 1; // phpcs:ignore

$workflow_service = new OW_Workflow_Service();
if ( $action == 'hideNotice' ) {
	update_site_option( "oasiswf_show_upgrade_notice", "no" );
	$workflows       = $workflow_service->get_workflow_list( "all" );
	$wf_class["all"] = 'class="current"';
} else {
	$workflows           = $workflow_service->get_workflow_list( $action );
	$wf_class[ $action ] = 'class="current"';
}

// Lets set new property post_count
if ( $workflows ) {
	$post_counts_array = $workflow_service->get_post_count_in_workflow();
	foreach ( $workflows as $workflow ) {
		$workflow_id = $workflow->ID;
		foreach ( $post_counts_array as $post_count_object ) {
			if ( $workflow_id == $post_count_object->workflow_id ) {
				$workflow->post_count = $post_count_object->post_count;
			}
		}
	}
}

// Lets sort out the workflow by post count
// phpcs:ignore
if ( isset( $_GET['orderby'] ) && 'post_count' === $_GET['orderby'] &&
     isset( $_GET['order'] ) && $workflows ) { // phpcs:ignore
	usort( $workflows, function ( $obj1, $obj2 ) {

		if ( isset( $obj1->post_count ) && isset( $obj2->post_count ) ) {
			// phpcs:ignore
			if ( 'desc' === $_GET['order'] ) {
				return $obj1->post_count < $obj2->post_count ? 1 : - 1; // need to switch 1 and -1
			} else {
				return $obj1->post_count > $obj2->post_count ? 1 : - 1;
			}
		} else {
			return 1;
		}
	} );
}

$wf_count_by_status = $workflow_service->get_workflow_count_by_status();
$workflow_count     = count( $workflows );

$per_page = OASIS_PER_PAGE; // phpcs:ignore
OW_Utility::instance()->owf_pro_features();
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
    <h2>
		<?php esc_html_e( "Edit Workflows", "oasisworkflow" ) ?>
		<?php
		if ( current_user_can( 'ow_create_workflow' ) ) {
			?>
            <a href="admin.php?page=oasiswf-add"
               class="add-new-h2"><?php esc_html_e( "Add New", "oasisworkflow" ); ?></a>
			<?php
		}
		?>
    </h2>
    <div id="view-workflow">
        <div class="tablenav">
            <ul class="subsubsub">
				<?php
				$workflow_statuses = array(
					'wf_all'      => 'All',
					'wf_active'   => 'Active',
					'wf_inactive' => 'Inactive'
				);
				$statuses          = array();
				// phpcs:ignore
				foreach ( $workflow_statuses as $key => $status ) {
					$count      = $wf_count_by_status->$key;
					$slug       = sanitize_key( $status );
					$class      = $slug === $action ? 'class=current' : '';
					$url        = add_query_arg( 'action', $slug, admin_url( 'admin.php?page=oasiswf-admin' ) );
					$statuses[] = "<li class='" . $slug . "'>
                                    <a href='$url' $class>" . __( $status, 'oasisworkflow' ) .
					              "<span class='count'> ($count) </span>
                                    </a>
                                 </li>";
				}
				$statuses = apply_filters( 'owf_workflow_status', $statuses );

				echo implode( ' | ', $statuses ); // phpcs:ignore
				?>
            </ul>
            <div class="tablenav-pages">
				<?php OW_Utility::instance()->get_page_link( $workflow_count, $pagenum, $per_page ); ?>
            </div>

        </div>
        <form method="post">

			<?php
			// display bulk actions on the workflow list page
			do_action( 'owf_display_workflow_bulk_actions' );
			?>

            <table class="wp-list-table widefat fixed posts" cellspacing="0" border="0">
                <thead>
				<?php $workflow_service->get_table_header(); ?>
                </thead>
                <tfoot>
				<?php $workflow_service->get_table_header(); ?>
                </tfoot>
                <tbody id="coupon-list">
				<?php
				ob_start();
				if ( $workflows ) {
					$count = 0;
					$start = ( $pagenum - 1 ) * $per_page;
					$end   = $start + $per_page;
					foreach ( $workflows as $wf ) {
						if ( $count >= $end ) {
							break;
						}
						if ( $count >= $start ) {
							$workflow_id = (int) $wf->ID;

							$class = $content = $workflow_desc = '';
							if ( ! empty( $wf->description ) ) {
								// display description on mouse over
								$class         = 'wf-desc';
								$content       = 'hover-data';
								$workflow_desc = $wf->description;
							}

							$edit_link = add_query_arg( array(
								'wf_id' => $workflow_id
							), admin_url( 'admin.php?page=oasiswf-admin' ) );
							?>
                            <tr class="alternate author-self status-publish format-default iedit">
                                <th scope="row" class="check-column"><input type="checkbox" name="workflows[]"
                                                                            value="<?php echo esc_attr( $workflow_id ); ?>">
                                </th>
                                <td>
                                    <a href="<?php echo esc_url( $edit_link ); ?>"
                                       class="<?php echo esc_attr( $class ); ?>">
                                        <div class="bold-label"
                                             id="workflow-name-<?php echo esc_attr( $workflow_id ); ?>"><?php echo esc_html( $wf->name ); ?></div>
                                        <span
                                                class="<?php echo esc_attr( $content ); ?>"><?php echo esc_html( wp_unslash( $workflow_desc ) ); ?></span>
                                    </a>
                                    <div class="row-actions">
										<?php
										$workflow_row_actions = $workflow_service->display_workflow_row_actions( $wf->ID, $wf->post_count );
										$action_count         = count( $workflow_row_actions );
										$i                    = 0;

										// phpcs:ignore
										foreach ( $workflow_row_actions as $action ) {
											++ $i;
											( $i == $action_count ) ? $sep = '' : $sep = ' | ';
											echo "<span>$action$sep</span>"; // phpcs:ignore
										}
										?>
                                    </div>
                                </td>
                                <td><?php echo esc_html( $wf->version ); ?></td>
                                <td><?php echo OW_Utility::instance()->format_date_for_display( $wf->start_date ); // phpcs:ignore ?></td>
                                <td><?php echo OW_Utility::instance()->format_date_for_display( $wf->end_date ); // phpcs:ignore ?></td>
                                <td><?php echo esc_html( $wf->post_count ); ?></td>
                                <td><?php echo $wf->is_valid ? 'Yes' : 'No'; ?></td>


								<?php
								// to display columns from add-ons like auto submit
								apply_filters( 'owf_workflow_additional_column_contents', $wf );
								?>
                            </tr>
							<?php
						}
						$count ++;
					}
				} else {

					if ( $action == "all" && current_user_can( 'ow_create_workflow' ) ) {
						$msg = "<label>" . esc_html__( "You don't have any workflows. Let's go ", "oasisworkflow" ) . "</label>
								<a href='admin.php?page=oasiswf-add'>" . esc_html__( "create one", "oasisworkflow" ) . "</a> !";
					} else {
						$msg = sprintf( "%s %s %s", esc_html__( "You don't have", "oasisworkflow" ), $action, esc_html__( 'workflows', 'oasisworkflow' ) );
					}
					?>
                    <tr>
                        <td colspan="6" class="no-found-lbl"><?php echo wp_kses_post( $msg ); ?></td>
                    </tr>
					<?php
				}
				echo ob_get_clean(); // phpcs:ignore
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