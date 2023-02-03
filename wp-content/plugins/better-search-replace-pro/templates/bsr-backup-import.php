<?php
/**
 * Displays the "Backup" tab.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.1
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/templates
 */

// Prevent direct/unauthorized access.
if ( ! defined( 'BSR_PATH' ) ) exit;

$profiles = BSR_Admin::get_profiles();

?>


<div class="metabox-holder">
	<div class="inside">

        <div id="bsr-error-wrap"></div>

		<div id="bsr-backup-form" class="panel">

			<div class="panel-header">
				 <h3><?php _e( 'Backup Database', 'better-search-replace' ); ?></h3>
			</div>

			<div class="panel-content">

				<div class="row">
					<p><?php _e( 'Click the button below to take a backup of your database, which can then be imported into another instance of Better Search Replace.', 'better-search-replace' ); ?></p>
				</div>

				<!--Saved Profiles Dropdown-->
				<div class="row">
					<div class="input-text">
						<label for="bsr_profile"><strong><?php _e( 'Run Search/Replace profile on backup ', 'better-search-replace' ); ?></strong></label>
						<?php
							if ( 0 !== count( $profiles ) ) {

								echo '<select id="bsr_backup_profile" name="bsr_backup_profile" class="select">
										<option>' . __( 'Please select a profile...', 'better-search-replace' ) . '</option>';

								foreach ( $profiles as $k => $v ) {
									echo '<option value="' . $k . '">' . $k . '</option>';
								}

								echo '</select>';

							} else {
								printf( '<span class="bsr-no-profiles">%s <a href="%s">%s</a></span>', __( 'No profiles found.', 'better-search-replace' ), get_admin_url() . 'tools.php?page=better-search-replace', __( 'Create your first profile now.','better-search-replace' ) );
							}
						?>
					</div>
				</div>
			</div>

            <!--Backup Database Button-->
            <div class="row panel-footer">
                <?php wp_nonce_field( 'bsr_process_backup', 'bsr_nonce' ); ?>
                <input type="hidden" name="action" value="bsr_process_backup" />
                <button id="bsr-backup-submit" type="submit" class="button button-primary button-md"><?php _e( 'Backup Database', 'better-search-replace' ); ?>
                    <img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/icon-arrow.svg'; ?>">
                </button>
            </div>
        </div>

        <div id="bsr-import-form" class="panel">

            <div class="panel-header">
                 <h3><?php _e( 'Import Database', 'better-search-replace' ); ?></h3>
            </div>

            <div class="panel-content">

                <div class="row">
                    <p><?php _e( 'Use the form below to import a database backup and run a saved profile on the resulting database.', 'better-search-replace' ); ?></br>
                    <?php _e( 'Alternatively, you can upload the backup file to the wp-content/uploads/ directory manually and click "Import Database".', 'better-search-replace' ); ?></p>
                </div>

                <!--Import File Button-->
                <div class="row import-file">
                    <input id="bsr-file-import" type="file" name="bsr_import_file">
                </div>

                <!--Saved Profiles Dropdown-->
                <div class="row">
                    <div class="input-text">
                        <label for="bsr_profile"><strong><?php _e( 'Run Search/Replace profile after import', 'better-search-replace' ); ?></strong></label>
                        <?php
                            if ( 0 !== count( $profiles ) ) {

                                echo '<select id="bsr_import_profile" name="bsr_profile" class="select">
                                        <option>' . __( 'Please select a profile...', 'better-search-replace' ) . '</option>';

                                foreach ( $profiles as $k => $v ) {
                                    echo '<option value="' . $k . '">' . $k . '</option>';
                                }

                                echo '</select>';

                            } else {
                                printf( '<span class="bsr-no-profiles">%s <a href="%s">%s</a></span>', __( 'No profiles found.', 'better-search-replace' ), get_admin_url() . 'tools.php?page=better-search-replace', __( 'Create your first profile now.','better-search-replace' ) );
                            }
                        ?>
                    </div>
                </div>
            </div>

            <!--Import Database Button-->
            <div class="row panel-footer">
                <?php wp_nonce_field( 'bsr_process_import', 'bsr_nonce' ); ?>
                <input type="hidden" name="action" value="bsr_process_import" />
                <button id="bsr-import-submit" type="submit" class="button button-primary button-md"><?php _e( 'Import Database', 'better-search-replace' ); ?>
                <img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/icon-arrow.svg'; ?>">
              </button>

            </div>
    </div>
</div>
