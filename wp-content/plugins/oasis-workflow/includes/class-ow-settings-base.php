<?php
/*
 * Base class for Oasis Workflow settings
 *
 * @copyright   Copyright (c) 2016, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OW_Settings_Base Class
 *
 * @since 2.0
 */
class OW_Settings_Base {

	public $tabs = array();

	/**
	 * Set things up.
	 *
	 * @since 2.0
	 */
	public function __construct() {

		// add the license tab
		$this->tabs['license_settings'] = esc_html__( 'License', 'oasisworkflow' );

		// add the workflow settings tab
		$this->tabs['workflow_settings'] = esc_html__( 'Workflow', 'oasisworkflow' );

		// add the email settings tab
		$this->tabs['email_settings'] = esc_html__( 'Email', 'oasisworkflow' );

		// add the workflow terminology settings tab
		$this->tabs['workflow_terminology_settings'] = esc_html__( 'Workflow Terminology', 'oasisworkflow' );

		// to add tabs for add-ons
		do_action_ref_array( 'owf_add_settings_tab', array( &$this->tabs ) );

		add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );
	}

	/**
	 * Add a settings menu under the main Workflows menu
	 *
	 * @since 2.0
	 */
	public function add_settings_menu() {
		add_submenu_page( 'oasiswf-inbox',
			esc_html__( 'Settings', 'oasisworkflow' ),
			esc_html__( 'Settings', 'oasisworkflow' ),
			'edit_theme_options',
			'ow-settings',
			array( $this, 'add_base_settings_page' ) );

		// Go Pro
		if ( current_user_can( 'ow_edit_workflow' ) ) {

			$menu_title = '<span class="owf-go-pro">' . __( 'Go Pro', 'oasisworkflow' ) . '</span>';

			add_submenu_page( 'oasiswf-inbox',
				'',
				$menu_title,
				'manage_options',
				'https://www.oasisworkflow.com/pricing-purchase' );
		}
	}

	/**
	 * Page associated with the "Settings" menu
	 *
	 * @since 2.0
	 */
	public function add_base_settings_page() {
		?>
        <div class="wrap">
			<?php
			// phpcs:ignore
			$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'workflow_settings';

			// only display the license tab if there are any add-ons installed
			if ( ! has_action( 'owf_add_license_settings' ) ) {
				unset( $this->tabs['license_settings'] );
			}

			echo '<h2 class="nav-tab-wrapper">';
			foreach ( $this->tabs as $tab => $name ) {
				$css_class = ( $tab == $active_tab ) ? ' nav-tab-active' : '';
				echo "<a class='nav-tab " . esc_attr( $css_class ) . "' href='?page=ow-settings&tab=" . esc_attr( $tab ) . "'>" . esc_html( $name ) . "</a>";
			}
			echo '</h2>';
			?>
			<?php
			// display messages set via add_settings_error function
			settings_errors();

			// display the active tab
			switch ( $active_tab ) {
				case 'license_settings' :
					$ow_license_settings = new OW_License_Settings();
					$ow_license_settings->add_settings_page();
					break;
				case 'workflow_settings' :
					$ow_workflow_settings = new OW_Workflow_Settings();
					$ow_workflow_settings->add_settings_page();
					break;
				case 'email_settings' :
					$ow_email_settings = new OW_Email_Settings();
					$ow_email_settings->add_settings_page();
					break;
				case 'workflow_terminology_settings' :
					$ow_workflow_terminology_settings = new OW_Workflow_Terminology_Settings();
					$ow_workflow_terminology_settings->add_settings_page();
					break;
				default :
					// to display tabs for add-ons
					do_action( 'owf_display_settings_tab', $active_tab );
					break;
			}
			include( OASISWF_PATH . "includes/pages/about-us.php" );
			?>
        </div>
		<?php
	}

}

$ow_settings_base = new OW_Settings_Base();