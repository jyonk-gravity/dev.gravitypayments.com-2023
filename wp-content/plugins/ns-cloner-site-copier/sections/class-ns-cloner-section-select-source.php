<?php
/**
 * Select Source Section Class
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class NS_Cloner_Section_Select_Source
 *
 * Adds settings section for selecting source site to clone from.
 */
class NS_Cloner_Section_Select_Source extends NS_Cloner_Section {

	/**
	 * Mode ids that this section should be visible and active for.
	 *
	 * @var array
	 */
	public $modes_supported = array( 'core', 'clone_over', 'clone_teleport' );

	/**
	 * DOM id for section box.
	 *
	 * @var string
	 */
	public $id = 'select_source';

	/**
	 * Priority relative to other section boxes in UI.
	 *
	 * @var int
	 */
	public $ui_priority = 200;

	/**
	 * Output content for section settings box on admin page.
	 */
	public function render() {
		$this->open_section_box( __( 'Select Source', 'ns-cloner-site-copier' ), __( 'Select Source', 'ns-cloner-site-copier' ) );
		if ( ! is_multisite() ) {
			?>
			<h5><?php esc_html_e( 'The cloning source is set to the current site.', 'ns-cloner-site-copier' ); ?></h5>
			<p><?php esc_html_e( 'This is configurable for WordPress multisite, but here you only have one site installed to choose from, so it\'s been automatically selected for you.', 'ns-cloner-site-copier' ); ?></p>
			<?php
		} elseif ( ! is_network_admin() ) {
			?>
			<h5><?php esc_html_e( 'The cloning source is set to the current site.', 'ns-cloner-site-copier' ); ?></h5>
			<p><?php esc_html_e( 'You can use this plugin in Network mode  to choose from other source sites.', 'ns-cloner-site-copier' ); ?></p>
			<select name="source_id" class="ns-cloner-site-select no-chosen" style="display:none">
				<option value="<?php echo esc_attr( get_current_blog_id() ); ?>"></option>
			</select>
			<?php
		} else {
			?>
			<h5><?php esc_html_e( 'Select a site to clone', 'ns-cloner-site-copier' ); ?></h5>
			<select name="source_id" class="ns-cloner-site-select">
				<?php foreach ( ns_wp_get_sites_list() as $id => $label ) : ?>
					<?php
					// Pre-select saved 'default' template if applicable.
					$saved = get_site_option( 'ns_cloner_default_template' );
					// Enable GET source var to override default, used by quick clone links on network sites page.
					$default = ns_cloner_request()->get( 'source', $saved );
					?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php echo selected( esc_attr( $id ), $default ); ?>>
						<?php // phpcs:ignore WordPress.Security -- can't escape b/c non-latin chars can cause empty string. ?>
						<?php echo $label; ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description ns-cloner-clear">
				<?php esc_html_e( 'Choose an existing source site to clone.' ); ?>
				<?php esc_html_e( 'If you haven\'t already, now is a great time to set up a "template" site exactly the way you want the new clone site to start out (theme, plugins, settings, etc.).', 'ns-cloner-site-copier' ); ?>
			</p>
			<?php
		}
		$this->close_section_box();
	}

}
