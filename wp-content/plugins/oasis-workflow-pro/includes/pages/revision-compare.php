<?php
/*
 * Revision Compare
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

$nonce_val = isset( $_GET['_nonce'] ) ? sanitize_text_field( $_GET['_nonce'] ) : "";
if ( ! wp_verify_nonce( $nonce_val, 'owf_compare_revision_nonce' ) ) {
	return;
}
$revision_post_id   = isset( $_GET['revision'] ) ? intval( $_GET['revision'] ) : "";
$original_post_id   = get_post_meta( $revision_post_id, '_oasis_original', true );
$post               = get_post( $original_post_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
$revision_post      = get_post( $revision_post_id );
$revision_edit_link = get_edit_post_link( $revision_post->ID );
$original_edit_link = get_edit_post_link( $post->ID );
$h2                 = esc_html__( 'Comparison', 'oasisworkflow' );
$return_to_editor   = '<a href="' . esc_url( $revision_edit_link ) . '">' . "&larr;" .
                      esc_html__( ' Return to Post editor' ) . '</a>';

$ow_revision_service = new OW_Revision_Service();

$revision_notice    = esc_html__( 'Note : If you made any changes to the post, the updates are being saved while preparing the compare window. At times, if the "save" takes more time, you may not see the recent changes made to the post.
In those cases, simply close this window and click the "Compare With Original"  button again.', 'oasisworkflow' );
$return_to_revision = '<a href="' . esc_url( $revision_edit_link ) . '">' . esc_html( $revision_post->post_title ) .
                      '</a>';
$return_to_original = '<a href="' . esc_url( $original_edit_link ) . '">' . esc_html( $post->post_title ) . '</a>';

$compare_by = 'content';
if ( isset( $_POST['compare_by'] ) && ! empty( $_POST['compare_by'] ) ) {
	$compare_by = sanitize_text_field( $_POST['compare_by'] );
}

switch ( $compare_by ) {
	case 'raw':
		$original_content = $post->post_content;
		$revision_content = $revision_post->post_content;
		break;
	case 'content':
	default :
		$original_content = wp_strip_all_tags( $post->post_content );
		$revision_content = wp_strip_all_tags( $revision_post->post_content );
		break;
}
?>

<div class="wrap">
    <h2 class="long-header"><?php echo esc_html($h2); ?></h2>
    <span class="revision-message"><?php echo esc_html($revision_notice); ?></span>
    <!--
   <div class="revision-middle-box"><?php echo esc_html__( 'This is the test revision.', 'oasisworkflow' ); ?></div>
   -->
    <form method="post" onchange="">
        <p>
            <input type="radio" name="compare_by" value="raw" <?php checked( $compare_by, 'raw' ); ?>
                   onclick="javascript: submit()"/> <?php esc_html_e( 'HTML(raw) Compare', 'oasisworkflow' ); ?>
            &nbsp;
            <input type="radio" name="compare_by" value="content" <?php checked( $compare_by, 'content' ); ?>
                   onclick="javascript: submit()"/> <?php esc_html_e( 'Text Compare', 'oasisworkflow' ); ?>
        </p>
    </form>

    <div class="revisions">
        <div class="revisions-diff-frame">
            <div class="revisions-diff">
                <div class="loading-indicator"><span class="spinner"></span></div>
                <div class="diff-error"><?php esc_html_e('Sorry, something went wrong. The requested comparison could not be loaded.', 'oasisworkflow'); ?></div>
                <div class="diff">
                    <table class="diff">
                        <colgroup>
                            <col class="content">
                        </colgroup>
                        <tbody>
                        <tr>
                            <td align="center"><?php echo esc_html__( 'Original: ',
									'oasisworkflow' ); ?><?php echo $return_to_original; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                            <td>&nbsp;</td>
                            <td align="center"><?php echo esc_html__( 'Revision: ',
									'oasisworkflow' ); ?><?php echo $return_to_revision; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                        </tr>
                        <tr>
                            <td colspan=3>
                                <hr/>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <!-- Content Difference -->
                    <h3><?php echo esc_html__( "Contents", "oasisworkflow" ); ?></h3>
					<?php
					$content_diff = wp_text_diff( $original_content, $revision_content );

					if ( ! $content_diff ) {
						// It's a better user experience to still show the Content, even if it didn't change.
						$content_diff = $ow_revision_service->get_comparison_table( $original_content,
							$revision_content );

					}
					echo $content_diff; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>

                    <!-- Tag Difference -->
                    <h3><?php echo esc_html__( "Tags", "oasisworkflow" ); ?></h3>
					<?php
					$tags_diff = $ow_revision_service->compare_tags( $original_post_id, $revision_post_id );

					$tag_diff = wp_text_diff( $tags_diff['original_tag'], $tags_diff['revision_tag'] );

					if ( ! $tag_diff ) {
						// It's a better user experience to still show the Content, even if it didn't change.
						$tag_diff = $ow_revision_service->get_comparison_table( $tags_diff['original_tag'],
							$tags_diff['revision_tag'] );

					}
					echo $tag_diff; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>

                    <!-- Categories Difference -->
                    <h3><?php echo esc_html__( "Category", "oasisworkflow" ); ?></h3>
					<?php
					$cat_diff = $ow_revision_service->compare_categories( $original_post_id, $revision_post_id );

					$category_diff = wp_text_diff( $cat_diff['original_category'], $cat_diff['revision_category'] );

					if ( ! $tag_diff ) {
						// It's a better user experience to still show the Content, even if it didn't change.
						$category_diff = $ow_revision_service->get_comparison_table( $cat_diff['original_category'],
							$cat_diff['revision_category'] );

					}
					echo $category_diff; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>

                    <!-- Feature Image Difference -->
                    <h3><?php echo esc_html__( "Featured Image", "oasisworkflow" ); ?></h3>
					<?php
					$img_diff = $ow_revision_service->compare_featured_image( $original_post_id, $revision_post_id );

					$image_diff = wp_text_diff( $img_diff['original_image'], $img_diff['revision_image'] );

					if ( ! $image_diff ) {
						// It's a better user experience to still show the Content, even if it didn't change.
						$image_diff = $ow_revision_service->get_comparison_table( $img_diff['original_image'],
							$img_diff['revision_image'] );
					}
					echo $image_diff; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>

					<?php
					// to display custom data for add-ons
					do_action( 'owf_display_revision_compare_tab', $original_post_id, $revision_post_id );
					?>

					<?php
					apply_filters( 'owf_display_custom_fields', $original_post_id, $revision_post_id );
					?>
                </div>
            </div>
        </div>