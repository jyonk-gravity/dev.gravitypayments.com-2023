<?php
/**
 * Create Target Section Class
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class NS_Cloner_Section_Create_Target
 *
 * Adds and validates options for url and title of the new subsite to be created.
 */
class NS_Cloner_Section_Create_Target extends NS_Cloner_Section {

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
	public $id = 'create_target';

	/**
	 * Priority relative to other section boxes in UI.
	 *
	 * @var int
	 */
	public $ui_priority = 300;

	/**
	 * Output content for section settings box on admin page.
	 */
	public function render() {
		if ( ! is_multisite() ) {
			return;
		}
		$this->open_section_box( __( 'Create New Site', 'ns-cloner-site-copier' ), __( 'Create Site', 'ns-cloner-site-copier' ) );
		?>
		<h5><label for="target_title"><?php esc_html_e( 'Give the target site a title', 'ns-cloner-site-copier' ); ?></label></h5>
		<div class="ns-cloner-input-group">
			<input type="text" name="target_title"
				placeholder="<?php esc_attr_e( 'New Site Title', 'ns-cloner-site-copier' ); ?>"
				data-label="<?php esc_attr_e( 'Site title', 'ns-cloner-site-copier' ); ?>"
				data-required="1" />
		</div>
		<h5><label for="target_name"><?php esc_html_e( 'Give the target site a URL', 'ns-cloner-site-copier' ); ?></label></h5>
		<div class="ns-cloner-input-group">
		<?php if ( is_subdomain_install() ) : ?>
			<label><?php echo is_ssl() ? 'https://' : 'http://'; ?></label>
			<input type="text" name="target_name" class="ns-cloner-quick-validate" data-label="<?php esc_attr_e( 'Site URL', 'ns-cloner-site-copier' ); ?>" />
			<label>.<?php echo esc_html( preg_replace( '|^www\.|', '', get_current_site()->domain ) ); ?></label>
		<?php else : ?>
			<label><?php echo esc_url( trailingslashit( site_url() ) ); ?></label>
			<input type="text" name="target_name" class="ns-cloner-quick-validate" data-label="<?php esc_attr_e( 'Site URL', 'ns-cloner-site-copier' ); ?>" />
		<?php endif; ?>
		</div>
		<?php
		$this->close_section_box();
	}

	/**
	 * Check ns_cloner_request() and any validation error messages to $this->errors.
	 */
	public function validate() {
		$site_errors = ns_wp_validate_site(
			ns_cloner_request()->get( 'target_name' ),
			ns_cloner_request()->get( 'target_title' )
		);
		foreach ( $site_errors as $error ) {
			$this->errors[] = $error;
		}
	}

}
