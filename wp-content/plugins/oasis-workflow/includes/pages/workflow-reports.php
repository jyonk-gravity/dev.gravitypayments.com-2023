<?php
// phpcs:ignore
$selected_tab = ( isset ( $_GET['tab'] ) && sanitize_text_field( $_GET["tab"] ) ) ? sanitize_text_field( $_GET['tab'] ) : 'userAssignments';

// Verify nonce
$nonce = ( isset( $_GET['_wpnonce'] ) ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '';

if ( empty( $nonce ) && "userAssignments" !== $selected_tab && ! check_admin_referer( 'ow_report_nonce', '_wpnonce' ) ) {
    // Nonce verification failed, handle accordingly (exit, redirect, etc.)
    exit( 'Nonce verification failed!' );
}


?>
<div class="wrap">
	<?php
	// phpcs:ignore
	$tabs = array(
		'userAssignments'     => esc_html__( 'Current Assignments', "oasisworkflow" ),
		'workflowSubmissions' => esc_html__( 'Workflow Submissions', "oasisworkflow" )
	);
	echo '<div id="icon-themes" class="icon32"><br></div>';
	echo '<h2 class="nav-tab-wrapper">';
	// phpcs:ignore
	foreach ( $tabs as $tab => $name ) {
		$class = ( $tab == $selected_tab ) ? ' nav-tab-active' : '';
		$url = add_query_arg(
			array(
				'tab' => esc_attr( $tab ),
				'_wpnonce' => wp_create_nonce('ow_report_nonce'),
			),
			'?page=oasiswf-reports'
		);
		echo "<a class='nav-tab" . esc_attr( $class ) . "' href='" . esc_url( $url ) . "'>" . esc_html( $name ) . "</a>";

	}
	echo '</h2>';
	switch ( $selected_tab ) {
		case 'userAssignments' :
			include( OASISWF_PATH . "includes/pages/workflow-assignment-report.php" );
			break;
		case 'workflowSubmissions' :
			include( OASISWF_PATH . "includes/pages/workflow-submission-report.php" );
			break;
	}
	?>
</div>