<?php
/*
 * Workflow History Graphic popup
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

global $wpdb, $chkResult;

$ow_workflow_service = new OW_Workflow_Service();

$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : "";

if ( is_admin() &&
     preg_match_all( '/page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $request_uri, $matches ) ) {
	wp_enqueue_script( 'owf-workflow-history',
		OASISWF_URL . 'js/pages/subpages/history-graphic.js',
		'',
		OASISWF_VERSION,
		true );
}

$workflow = null;
$post_id  = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : ''; // phpcs:ignore

if ( is_numeric( $chkResult ) ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$workflow = $wpdb->get_row( $wpdb->prepare( "SELECT C.ID, C.wf_info FROM (
   				(SELECT * FROM " . $wpdb->fc_action_history . " WHERE ID = %d) AS A
   				LEFT JOIN " . $wpdb->fc_workflow_steps . " AS B
   				ON A.step_id = B.ID
   				LEFT JOIN " . $wpdb->fc_workflows . " AS C
   				ON B.workflow_id = C.ID
   			)", $chkResult ) );
}
if ( $workflow ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$processes = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM " . $wpdb->fc_action_history .
		                " WHERE ID <= %d AND (action_status = 'processed' OR action_status = 'assignment') " .
		                " AND post_id = %d ORDER BY ID",
			$chkResult, $post_id ) );

	if ( $processes ) {

		$startid = "";
		foreach ( $processes as $process ) {
			if ( $startid ) {
				$newconns[] = $ow_workflow_service->get_connection( $workflow, $startid, $process->step_id );
			}
			$startid = $process->step_id;
		}

		$current_step_id = $ow_workflow_service->get_gpid_dbid( $workflow->wf_info, $startid );

		$wf_info = $workflow->wf_info;
	}

    echo '<script type="text/javascript">
         var wfPluginUrl = "' . esc_url( OASISWF_URL ) . '";
         var stepinfo = "' . esc_js( $wf_info ) . '";
         var currentStepGpId = "' . esc_js( $current_step_id ) . '";
      </script>';
}
?>
<div id="workflow-area" style="position:relative;width:100%;"></div>
<br class="clear">