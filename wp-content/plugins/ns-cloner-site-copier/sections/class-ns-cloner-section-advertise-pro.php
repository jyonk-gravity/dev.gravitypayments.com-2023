<?php
/**
 * Advertise Pro Section Class
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class NS_Cloner_Section_Advertise_Pro
 *
 * Add several dummy sections to advertise Pro features.
 */
class NS_Cloner_Section_Advertise_Pro extends NS_Cloner_Section {

	/**
	 * Mode ids that this section should be visible and active for.
	 *
	 * @var array
	 */
	public $modes_supported = array( 'core' );

	/**
	 * DOM id for section box.
	 *
	 * @var string
	 */
	public $id = 'advertise_pro';

	/**
	 * Priority relative to other section boxes in UI.
	 *
	 * @var int
	 */
	public $ui_priority = 750;

	/**
	 * Output content for section settings box on admin page.
	 */
	public function render() {

		// Tables.
		$this->open_section_box( __( 'Copy Tables', 'ns-cloner-site-copier' ) );
		echo '<p>';
		esc_html_e( 'All database tables will be copied by default. ', 'ns-cloner-site-copier' );
		printf(
			wp_kses(
				/* translators: URL to plugin info page */
				__( 'For more efficiency and precise control over which tables are cloned, check out <a href="%s" target="_blank">NS Cloner Pro</a>.', 'ns-cloner-site-copier' ),
				ns_wp_kses_allowed()
			),
			esc_url( NS_CLONER_PRO_URL )
		);
		echo '</p>';
		$this->close_section_box();

		// Post types.
		$this->open_section_box( __( 'Post Types', 'ns-cloner-site-copier' ) );
		echo '<p>';
		esc_html_e( 'All post types will be copied by default. ', 'ns-cloner-site-copier' );
		printf(
			wp_kses(
				/* translators: URL to plugin info page */
				__( 'With <a href="%s" target="_blank">NS Cloner Pro</a> you can customize which post types are cloned, giving you even more powerful flexibility.', 'ns-cloner-site-copier' ),
				ns_wp_kses_allowed()
			),
			esc_url( NS_CLONER_PRO_URL )
		);
		echo '</p>';
		$this->close_section_box();

		// Search and replace.
		$this->open_section_box( __( 'Search and Replace', 'ns-cloner-site-copier' ) );
		echo '<p>';
		esc_html_e( 'By default, the site name and URL will be replaced in all cloned content and settings. ', 'ns-cloner-site-copier' );
		printf(
			wp_kses(
				/* translators: URL to plugin info page */
				__( ' <a href="%s" target="_blank">NS Cloner Pro</a> offers the ability to perform an unlimited number of additional custom search and replace operations.', 'ns-cloner-site-copier' ),
				ns_wp_kses_allowed()
			),
			esc_url( NS_CLONER_PRO_URL )
		);
		echo '</p>';
		$this->close_section_box();

		// Users.
		$this->open_section_box( __( 'Copy Users', 'ns-cloner-site-copier' ) );
		echo '<p>';
		esc_html_e( 'By default, just the current admin user (you) will be automatically added as a user on the new site. ', 'ns-cloner-site-copier' );
		printf(
			wp_kses(
				/* translators: URL to plugin info page */
				__( 'With <a href="%s" target="_blank">NS Cloner Pro</a> you can auto-generate new admin users, as well as have the option to clone all the existing users.', 'ns-cloner-site-copier' ),
				ns_wp_kses_allowed()
			),
			esc_url( NS_CLONER_PRO_URL )
		);
		echo '</p>';
		$this->close_section_box();

		// Media files.
		$this->open_section_box( __( 'Copy Media Files', 'ns-cloner-site-copier' ) );
		echo '<p>';
		esc_html_e( 'By default, all media library files will be copied by default to the new site, and links referring to them updated. ', 'ns-cloner-site-copier' );
		printf(
			wp_kses(
				/* translators: URL to plugin info page */
				__( '<a href="%s" target="_blank">NS Cloner Pro</a> enables you to control uploads cloning, so your cloned site could optionally continue to refer to the source site\'s media files.', 'ns-cloner-site-copier' ),
				ns_wp_kses_allowed()
			),
			esc_url( NS_CLONER_PRO_URL )
		);
		echo '</p>';
		$this->close_section_box();

	}

}
