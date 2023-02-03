<?php
/**
 * Template for sidebar on main cloning admin page.
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="ns-cloner-sidebar">

	<div class="ns-side-widget ns-support-widget">
		<h5><?php esc_html_e( 'Support', 'ns-cloner-site-copier' ); ?></h5>
		<div class="ns-side-widget-content">
			<p>
				<?php esc_html_e( 'Have any issues with the Cloner, or ideas on how to make it better? We\'d love to hear from you.', 'ns-cloner-site-copier' ); ?>
			</p>
			<p>
				<a href="http://support.neversettle.it" class="button" data-cloner-modal="copy-logs" target="_blank">
					<?php esc_html_e( 'Support & Feature Requests', 'ns-cloner-site-copier' ); ?>
				</a>
			</p>
		</div>
	</div>

	<div class="ns-side-widget ns-rate-widget">
		<h5><?php esc_html_e( 'Do you like NS Cloner?', 'ns-cloner-site-copier' ); ?></h5>
		<div class="ns-side-widget-content">
			<p>
				<?php esc_html_e( 'If the Cloner has saved you lots of time, tell everyone with a 5-star rating!', 'ns-cloner-site-copier' ); ?>
			</p>
			<p>
				<a href="http://wordpress.org/support/view/plugin-reviews/ns-cloner-site-copier?rate=5#postform" target="_blank" class="button">
					<?php esc_html_e( 'Rate it 5 Stars', 'ns-cloner-site-copier' ); ?>
				</a>
			</p>
		</div>
	</div>

	<div class="ns-side-widget ns-subscribe-widget">
		<h5><?php esc_html_e( 'Don\'t Miss Anything!', 'ns-cloner-site-copier' ); ?></h5>
		<div class="ns-side-widget-content">
			<p><?php esc_html_e( 'Receive updates, beta invites, articles and more!', 'ns-cloner-site-copier' ); ?></p>
			<!-- Begin Active Campaign Signup Form -->
			<div class="_form_28" id="ns-subscribe-form"></div>
			<?php wp_enqueue_script( 'ns-subscribe', 'https://neversettle.activehosted.com/f/embed.php?id=28', array(), '28', true ); ?>
			<!-- End Active Campaign Signup Form -->
		</div>
	</div>

	<div class="ns-side-widget ns-links-widget">
		<h5>
			<?php esc_html_e( 'Built by', 'ns-cloner-site-copier' ); ?>
			<a href="http://neversettle.it/home/?utm_campaign=in+plugin+referral&utm_source=ns-cloner&utm_medium=plugin&utm_content=social+button+to+ns">Never Settle</a>
		</h5>
		<div class="ns-side-widget-content">
			<a href="http://neversettle.it/home/?utm_campaign=in+plugin+referral&utm_source=ns-cloner&utm_medium=plugin&utm_content=social+button+to+ns" target="_blank">
				<img src="<?php echo esc_url( NS_CLONER_V4_PLUGIN_URL . 'images/ns-visit.png' ); ?>" alt="Visit NS" />
			</a>
			<a href="http://facebook.com/neversettle.it" target="_blank">
				<img src="<?php echo esc_url( NS_CLONER_V4_PLUGIN_URL . 'images/ns-like.png' ); ?>" alt="Like NS" />
			</a>
			<a href="https://twitter.com/neversettleit" target="_blank">
				<img src="<?php echo esc_url( NS_CLONER_V4_PLUGIN_URL . 'images/ns-follow.png' ); ?>" alt="Follow NS" />
			</a>
		</div>
	</div>

	<?php if ( ! empty( ns_cloner()->log->get_recent_logs() ) ) : ?>
	<div class="ns-cloner-extra-modal" id="copy-logs">
		<div class="ns-cloner-extra-modal-content">
			<h3><?php esc_html_e( 'Before you go...', 'ns-cloner-site-copier' ); ?></h3>
			<p>
				<?php esc_html_e( 'If you\'re going to open a support request, could you please copy the log urls listed below and paste them at the bottom of your support request so we can give you better and faster help? Thank you!', 'ns-cloner-site-copier' ); ?>
			</p>
			<p class="description">
				<?php esc_html_e( '(Please send privately, not on a forum - some sensitive info from your database could be included in the logs.)', 'ns-cloner-site-copier' ); ?>
			</p>
			<textarea onclick="this.select();return false;"><?php echo esc_textarea( join( "\n", ns_cloner()->log->get_recent_logs() ) ); ?></textarea>
			<p>
				<a href="http://support.neversettle.it" class="button" target="_blank"><?php esc_html_e( 'Continue to Support', 'ns-cloner-site-copier' ); ?></a>
			</p>
		</div>
	</div>
	<?php endif; ?>

</div>
