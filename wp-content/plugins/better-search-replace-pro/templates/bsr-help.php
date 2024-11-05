<?php
/**
 * Displays the "System Info" tab.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.1
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/templates
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

$bsr_docs_url    = 'https://bettersearchreplace.com/docs/';
$bsr_support_url = 'https://bettersearchreplace.com/plugin-support/';
$bsr_license_key = get_option( 'bsr_license_key' );

if ( false !== $bsr_license_key ) {
	$bsr_support_url .= '?key=' . esc_attr( $bsr_license_key );
}

?>

<div class="inside">

	<div class="panel">

		<div class="panel-header">
			<h3><?php _e( 'Help & Troubleshooting', 'better-search-replace' ); ?></h3>
		</div>

		<div class="panel-content">

			<div class="row">
				<p><?php _e( 'Need some help, found a bug, or just have some feedback? ', 'better-search-replace' ); ?>
				<?php
					printf( wp_kses( __( 'Check out the <a href="%s" target="_blank">documentation</a> or <a href="%s" target="_blank">open a support ticket</a>.', 'better-search-replace' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ),
						esc_url( $bsr_docs_url ),
						esc_url( $bsr_support_url )
					);
				?>
				</p>
			</div>

			<!--System Info-->
			<div class="row">
				<div class="input-text full-width">
					<label><strong><?php _e( 'System Info', 'better-search-replace' ); ?></strong></label>
					<textarea readonly="readonly" onclick="this.focus(); this.select()" name='bsr-sysinfo'><?php echo BSR_Compatibility::get_sysinfo(); ?></textarea>
				</div>
			</div>

			<!--Submit Button-->
			<div class="row">
				<p class="submit">
					<input type="hidden" name="action" value="bsr_download_sysinfo" />
					<?php wp_nonce_field( 'bsr_download_sysinfo', 'bsr_sysinfo_nonce' ); ?>
					<input type="submit" name="bsr-download-sysinfo" id="bsr-download-sysinfo" class="button button-secondary button-sm" value="Download System Info">
				</p>
			</div>

	   </div>
	</div>
</div>
