<?php
/*
 * Workflow Tools Main Page
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.3
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

$selected_tab = ( isset( $_GET['tab'], $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'ow_nonce' ) ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'import_export';

?>

<div class="wrap">
	<?php
	$tools_tabs = array(
		'import_export' => esc_html__( 'Import/Export', "oasisworkflow" ),
		'system_info'   => esc_html__( 'System Info', "oasisworkflow" )
	);
    $nonce = wp_create_nonce( 'ow_nonce' );
	echo '<div id="icon-themes" class="icon32"><br></div>';
	echo '<h2 class="nav-tab-wrapper">';
	foreach ( $tools_tabs as $tools_tab => $name ) {
		$class = ( $tools_tab == $selected_tab ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab" . esc_attr( $class ) .
		     "' href='?page=oasiswf-tools&tab=" . esc_attr( $tools_tab ) . "&_wpnonce=" . esc_attr( $nonce ) . "'>" .
		     esc_html( $name ) . "</a>";

	}
	echo '</h2>';
	switch ( $selected_tab ) {
		case 'import_export' :
			include( OASISWF_PATH . "includes/pages/ow-import-export.php" );
			break;
		case 'system_info' :
			include( OASISWF_PATH . "includes/pages/ow-system-info.php" );
			break;
	}
	?>
</div>

