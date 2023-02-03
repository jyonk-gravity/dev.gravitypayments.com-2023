<?php
/**
 * Displays the main "Settings" tab.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.1
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/templates
 */

// Prevent direct/unauthorized access.
if ( ! defined( 'BSR_PATH' ) ) exit;

// Get information about the current license.
$license = get_option( 'bsr_license_key' );
$status  = get_option( 'bsr_license_status' );

// Other settings.
$page_size 		= get_option( 'bsr_page_size' ) ? absint( get_option( 'bsr_page_size' ) ) : 20000;
$max_results 	= get_option( 'bsr_max_results' ) ? absint( get_option( 'bsr_max_results' ) ) : 60;

if ( '' === get_option( 'bsr_enable_gzip' ) ) {
	$bsr_enable_gzip = false;
} else {
	$bsr_enable_gzip = true;
}
 ?>

<?php settings_fields( 'bsr_settings_fields' ); ?>

<div class="inside">

	<?php if ( 'invalid' === $status ) : ?>
        <div class="notice notice-warning bsr-updated">
            <p>
				<?php _e( 'The license key you entered appears to be invalid or expired. Please check your license key and try activating it again.', 'better-search-replace' ); ?>
            </p>
        </div>
	<?php endif; ?>

	<!--Settings Panel-->
	<div class="panel">

		<div class="panel-header">
			 <h3><?php _e( 'Settings', 'better-search-replace' ); ?></h3>
		</div>

		<div class="panel-content settings">

			<!--License Key-->
			<div class="row">
				<div class="input-text">
					<label><strong><?php _e( 'License Key', 'better-search-replace' ); ?></strong></label>
					<div class="license-field">
                        <?php
						    $readonly = '';
                            $type     = 'text';
                            if ( 'valid' === $status ) {
                                $readonly = 'readonly="readonly"';
                                $type = 'password';
                            }
                        ?>
						<input id="bsr_license_key" name="bsr_license_key" type="<?php echo $type; ?>" class="regular-text" value="<?php esc_attr_e( $license ); ?>" <?php echo $readonly; ?> />
							<?php if( 'valid' === $status ) { ?>
								<?php wp_nonce_field( 'bsr_license_nonce', 'bsr_license_nonce' ); ?>
								<input type="submit" class="button button-secondary button-sm" name="bsr_license_deactivate" value="<?php _e( 'Remove', 'better-search-replace' ); ?>"/>
							<?php } else { ?>
								<?php wp_nonce_field( 'bsr_license_nonce', 'bsr_license_nonce' ); ?>
								<input type="submit" class="button button-secondary button-sm" name="bsr_license_activate" value="<?php _e( 'Activate License', 'better-search-replace' ); ?>"/>
							<?php } ?>
					</div>
                    <?php if ( 'valid' !== $status ) : ?>
					    <p class="description" for="bsr_license_key"><?php _e( 'Enter your license key for support and updates.', 'better-search-replace' ); ?></p>
                    <?php endif; ?>
				</div>
			</div>

			<!--Max Page Size-->
			<div class="row">
				<div class="input-text">
					<div class="settings-header">
						<label><strong><?php _e( 'Max Page Size', 'better-search-replace' ); ?></strong></label>
						<span id="bsr-page-size-value"><?php echo absint( $page_size ); ?></span>
					</div>
					<input id="bsr_page_size" type="hidden" name="bsr_page_size" value="<?php echo $page_size; ?>" />
					<p class="description"><?php _e( 'If you notice timeouts or are unable to backup/import the database, try decreasing this value.', 'better-search-replace' ); ?></p>
					<div class="slider-wrapper">
						<div id="bsr-page-size-slider" class="bsr-slider"></div>
					</div>
				</div>
			</div>

			<!--Max Results-->
			<div class="row">
				<div class="input-text">
					<div class="settings-header">
						<label><strong><?php _e( 'Max Results', 'better-search-replace' ); ?></strong></label>
						<span id="bsr-max-results-value"><?php echo absint( $max_results ); ?></span>
					</div>
					<input id="bsr_max_results" type="hidden" name="bsr_max_results" value="<?php echo $max_results; ?>" />
					<p class="description"><?php _e( 'The maximum amount of results to store when running a search/replace.', 'better-search-replace' ); ?></p>
					<div class="slider-wrapper">
						<div id="bsr-max-results-slider" class="bsr-slider"></div>
					</div>
				</div>
			</div>

			<!--Enable Gzip-->
			<label class="row last-row">
					<div class="col">
						<input id="bsr-enable-gzip" type="checkbox" name="bsr_enable_gzip" <?php checked( $bsr_enable_gzip, true ); ?> />
					</div>
					<div class="col">
						<label for="bsr-enable-gzip"><strong><?php _e( 'Enable Gzip?', 'better-search-replace' ); ?></strong></label>
						<label for="bsr-enable-gzip"><span class="description"><?php _e( 'If enabled, backups will be compressed to reduce file size.', 'better-search-replace' ); ?></span></label>
					</div>
			</label>

			<!--Submit Button-->
			<div class="row panel-footer">
					<?php submit_button(); ?>
			</div>

			</div>
 	</div>
</div>
