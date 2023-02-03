<?php
/**
 * Template to report the results of a cloning operation (loaded by ajax).
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$reports = ns_cloner()->report->get_all_reports();
// If page gets reloaded while in progress, don't show and clear report.
if ( ! empty( $reports ) && ! ns_cloner()->process_manager->is_in_progress() ) {
	?>
		<div class="ns-cloner-report-content">
			<?php if ( isset( $reports['_error'] ) ) : ?>
				<span class="ns-cloner-error-message"><?php echo esc_html( $reports['_error'] ); ?></span>
			<?php elseif ( isset( $reports['_message'] ) ) : ?>
				<h5><?php echo esc_html( $reports['_message'] ); ?></h5>
			<?php endif; ?>
			<?php if ( isset( $reports['_notices'] ) ) : ?>
				<?php foreach ( $reports['_notices'] as $notice ) : ?>
				<span class="ns-cloner-warning-message"><?php echo esc_html( $notice ); ?></span>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php foreach ( $reports as $label => $value ) : ?>
				<?php
				// Skip special/hidden messages that start with underscore.
				if ( strpos( $label, '_' ) === 0 ) {
					continue;
				}
				// Format links - for logs just display the last.
				if ( preg_match( '/^http/', $value ) ) {
					$value = "<a href='$value' target='_blank'>" . str_replace( NS_CLONER_V4_PLUGIN_URL, '', $value ) . '</a>';
				}
				?>
				<div class="ns-cloner-report-item">
					<div class="ns-cloner-report-item-label"><?php echo esc_html( $label ); ?>:</div>
					<div class="ns-cloner-report-item-value"><?php echo wp_kses( $value, ns_wp_kses_allowed() ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php
	// Clear now that they've been displayed once.
	ns_cloner()->report->clear_all_reports();
}
?>
