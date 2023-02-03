<?php
/**
 * Template for displaying prompt to opt in to analytics gathering
 *
 * @package NS_Cloner
 */

$user_modes = ns_cloner_analytics()->get_user_modes();
?>
<div class="ns-cloner-extra-modal load" id="analytics-settings">
	<div class="ns-cloner-extra-modal-content">
		<h3><?php esc_html_e( 'NS Cloner Statistics', 'ns-cloner-site-copier' ); ?></h3>
		<p>
			<?php
			esc_html_e(
				'We\'d be grateful if you are willing to allow the NS Cloner to share cloning statistics with us to help improve the plugin and offer better support. 
            It won\'t include any actual data from your site except the raw domain name at most. 
            But you have full control over this and can update it at any time from the Cloner Logs/Status page.',
				'ns-cloner-site-copier'
			);
			?>
		</p>
		<p>
			<?php esc_html_e( 'By default NO stats are shared - are you willing to Opt-In?', 'ns-cloner-site-copier' ); ?>
		</p>
		<?php foreach ( $user_modes as $value => $mode_details ) : ?>
			<button class="button ns-cloner-form-button save-analytics-mode <?php echo ! empty( $mode_details['tooltip'] ) ? 'tooltip' : ''; ?>" data-mode="<?php echo esc_attr( $value ); ?>"
					data-tooltip="<?php echo ! empty( $mode_details['tooltip'] ) ? esc_attr( $mode_details['tooltip'] ) : ''; ?>">
				<span class="tooltip-toggle"><?php echo esc_html( $mode_details['text'] ); ?></span>
				<?php if ( ! empty( $mode_details['tooltip'] ) ) : ?>
					<span class="tooltip-text"><?php echo esc_html( $mode_details['tooltip'] ); ?></span>
				<?php endif; ?>
			</button>
		<?php endforeach; ?>
	</div>
</div>
