<?php
/*
 * Workflow Reports Main Page
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

$selected_tab = ( isset( $_GET['tab'] ) && sanitize_text_field( $_GET['tab'] ) ) ? sanitize_text_field( $_GET['tab'] )
	: 'userAssignments';

// Verify nonce
$nonce = ( isset( $_GET['_wpnonce'] ) ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '';

if ( empty( $nonce ) && "userAssignments" !== $selected_tab && ! check_admin_referer( 'ow_report_nonce', '_wpnonce' ) ) {
    // Nonce verification failed, handle accordingly (exit, redirect, etc.)
    exit( 'Nonce verification failed!' );
}

?>
<div class="wrap">
	<?php
	$report_tabs = array(
		'userAssignments'     => esc_html__( 'Current Assignments', "oasisworkflow" ),
		'workflowSubmissions' => esc_html__( 'Workflow Submissions', "oasisworkflow" ),
		'taskByDueDate'       => esc_html__( 'Assignments By Due Date', "oasisworkflow" )
	);
	$nonce = wp_create_nonce( 'ow_report_nonce' );
	echo '<div id="icon-themes" class="icon32"><br></div>';
	echo '<h2 class="nav-tab-wrapper">';
	foreach ( $report_tabs as $report_tab => $name ) {
		$class = ( $report_tab == $selected_tab ) ? ' nav-tab-active' : '';

		$url = add_query_arg(
			array(
				'tab' => esc_attr( $report_tab ),
				'_wpnonce' => wp_create_nonce('ow_report_nonce'),
			),
			'?page=oasiswf-reports'
		);

		echo "<a class='nav-tab" . esc_attr( $class ) . "' href='" . esc_url( $url ) . "'>" .
		     esc_html( $name ) .
		     "</a>";

	}
	echo '</h2>';
	switch ( $selected_tab ) {
		case 'userAssignments' :
			include( OASISWF_PATH . "includes/pages/workflow-assignment-report.php" );
			break;
		case 'workflowSubmissions' :
			include( OASISWF_PATH . "includes/pages/workflow-submission-report.php" );
			break;
		case 'taskByDueDate' :
			include( OASISWF_PATH . "includes/pages/workflow-by-due-date-report.php" );
			break;
	}
	?>
</div>