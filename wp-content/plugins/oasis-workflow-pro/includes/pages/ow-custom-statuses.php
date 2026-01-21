<?php
/*
 * Custom Statuses page
 *
 * @copyright   Copyright (c) 2016, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

global $ow_custom_statuses;
$status_terms = $ow_custom_statuses->get_all_custom_statuses();
?>
<div class="wrap">
    <h2><?php esc_html_e( 'Custom Statuses', 'oasisworkflow' ); ?></h2>
    <div id="col-right">
        <div class="col-wrap">
            <table class="wp-list-table widefat fixed striped customstatuses">
                <thead>
				<?php $ow_custom_statuses->get_custom_status_header(); ?>
                </thead>
                <tbody id="the-list" class="ui-sortable">
                <?php if ( $status_terms ) : ?>
	                <?php foreach ( $status_terms as $status_term ) : ?>
                        <tr id="term-<?php echo esc_attr( $status_term->term_id ); ?>"
                            class="term-static ui-sortable-handle">
                            <td class="name column-name">
                                <strong><a href="#"><?php echo esc_html( $status_term->name ); ?></a></strong>
                                <div class="row-actions">
                              <span class="edit">
                                 <a href="<?php echo esc_url_raw( add_query_arg( array(
	                                 'term_id'  => esc_attr( $status_term->term_id ),
	                                 '_wpnonce' => esc_attr( wp_create_nonce( 'edit_custom_status' ) ),
	                                 'action'   => 'edit-status'
                                 ) ) ); ?>">
                                 		<?php esc_html_e( 'Edit', 'oasisworkflow' ); ?>
                                 </a>
                                 &nbsp;|&nbsp;
                              </span>
                                    <span class="delete delete-status">
                                 <a href="<?php echo esc_url_raw( add_query_arg( array(
	                                 'term_id'  => esc_attr( $status_term->term_id ),
	                                 '_wpnonce' => esc_attr( wp_create_nonce( 'ow-delete-custom-status' ) ),
	                                 'action'   => 'ow-delete-status'
                                 ) ) ); ?>"
                                    onclick="if (!confirm('<?php esc_attr_e( 'Are you sure you want to delete the post status?',
	                                    'oasisworkflow' ); ?>')) {
                                        return false;
                                        }">
                              		<?php esc_attr_e( 'Delete', 'oasisworkflow' ); ?>
                              	</a>
                              </span>
                                </div>
                            </td>
                            <td class="slug column-slug">
	                            <?php echo esc_html( $status_term->slug ); ?>
                            </td>
                            <td class="description column-description">
	                            <?php echo esc_html( $status_term->description ); ?>
                            </td>
                        </tr>
					<?php endforeach; ?>
				<?php endif; ?>
                </tbody>
                <tfoot>
				<?php $ow_custom_statuses->get_custom_status_header(); ?>
                </tfoot>
            </table>
        </div> <!-- .col-wrap -->
    </div> <!-- #col-right -->
	<?php
	$status_term = $term_id = false;
	$term_name   = $term_slug = $term_desc = '';
	$term_id     = isset( $_GET['term_id'] ) ? intval( $_GET['term_id'] ) : "";
	$user_action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : "";
	if ( ! empty( $user_action ) && $user_action == 'edit-status' ) {
		check_admin_referer( 'edit_custom_status', '_wpnonce' );
		$status_term = $ow_custom_statuses->get_single_term_by( 'id', $term_id );
	}
	?>
    <div id="col-left">
        <div id="col-wrap">
            <div class="form-wrap">
                <form method="post">

                    <div class="form-field form-required">
                        <label for="status_name"><?php esc_html_e( 'Name', 'oasisworkflow' ); ?></label>
                        <input type="text" aria-required="true" size="20" maxlength="20" id="status_name"
                               name="status_name"
                               value="<?php echo esc_attr( $status_term ? $status_term->name : '' ); ?>"/>
                        <p class="description"><?php esc_html_e( 'The name is used to identify the status. (Max: 20 characters)',
								'oasisworkflow' ); ?></p>
                    </div>

                    <div class="form-field form-required">
                        <label for="slug_name"><?php esc_html_e( 'Slug', 'oasisworkflow' ); ?></label>
                        <input type="text" aria-required="true" size="20" maxlength="20" id="slug_name" name="slug_name"
                               value="<?php echo esc_attr( $status_term ? $status_term->slug : '' ); ?>"/>
                        <p class="description"><?php esc_html_e( 'The slug is the unique ID for the status. It is usually all lowercase and contains only letters, numbers and hyphens.',
								'oasisworkflow' ); ?></p>
                    </div>

                    <div class="form-field">
                        <label for="status_description"><?php esc_html_e( 'Description', 'oasisworkflow' ); ?></label>
                        <textarea cols="40" rows="5" id="status_description"
                                  name="status_description"><?php echo esc_textarea( $status_term
		                        ? $status_term->description
								: '' ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'The description is primarily for administrative use, to give you some context on what the custom status is to be used for.',
								'oasisworkflow' ); ?></p>
                    </div>

					<?php
					$btn_val  = 'add-new';
					$btn_name = esc_html__( 'Add New Status', 'oasisworkflow' );
					if ( $status_term ) {
						$btn_val  = 'update-status';
						$btn_name = esc_html__('Update Status', 'oasisworkflow');
						wp_nonce_field( 'edit_custom_status' );
					} else {
						wp_nonce_field( 'custom-status-add-nonce' );
					}
					?>
					<?php echo '<input id="action" name="action" type="hidden" value="' . esc_attr( $btn_val ) .
					           '" />'; ?>
					<?php echo '<input id="term_id" name="term_id" type="hidden" value="' . esc_attr( $term_id ) .
					           '" />'; ?>
                    <p class="submit"><?php submit_button( $btn_name, 'primary', 'submit', false ); // phpcs:ignore ?></p>
                </form>
            </div> <!-- .form-wrap -->
        </div> <!-- #col-wrap -->
    </div> <!-- #col-left -->
</div> <!-- .wrap -->
<script>
    jQuery(document).ready(function () {
        jQuery(document).on('blur', '#status_name', function () {
            var status_name = jQuery(this).val();
            if (status_name === '') {
                return false;
            }

            var slug = status_name.toLowerCase()
                .replace(/[^\w ]+/g, '') // remove hyphens (but not spaces)
                .replace(/ +/g, '-'); // remove spaces into a single hyphen
            jQuery('#slug_name').val(slug);
        });
    });
</script>