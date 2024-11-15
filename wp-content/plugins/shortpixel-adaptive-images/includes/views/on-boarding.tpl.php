<?php
	/**
	 * @var \ShortPixelAI $controller
	 */
	$controller = $this->ctrl;

	$dismissed = \ShortPixel\AI\Notice::getDismissed();

	$has_conflict = in_array( $controller->is_conflict(), ShortPixelAI::$SHOW_STOPPERS )
	                || ( !!$controller->options->get( 'missing_jquery', [ 'tests', 'front_end' ] ) && !isset( $dismissed->missing_jquery ) )
	                || ( is_plugin_active( 'shortpixel-image-optimiser/wp-shortpixel.php' ) && !empty( get_option( 'wp-short-pixel-create-webp-markup', 0 ) ) && !isset( $dismissed->spio_webp ) );

	$steps = \ShortPixel\AI\Page\Constants::_( $controller )->onBoarding;

	$step      = (int) $controller->options->pages_onBoarding_step;
	$steps_qty = count( $steps[ 'messages' ] );

	$step = $step > $steps_qty - 1 ? $steps_qty - 1 : $step;

	$domain_status    = ShortPixelDomainTools::get_domain_status( true );
	$domain_cdn_usage = ShortPixelDomainTools::get_cdn_domain_usage(null, $controller->options->settings_general_apiKey);
?>
	<h1><?php echo $this->data[ 'Name' ]; ?></h1>
	<div class="shortpixel-on-boarding-wrap wrap">
		<div class="sp-obw__title-wrap">
			<img
				src="<?= /*$has_conflict || $domain_status->Status === -1 ? $controller->plugin_url . 'assets/img/robo-scared.png' :*/
					$controller->plugin_url . 'assets/img/robo-happy.png' ?>"
				srcset="<?= /*$has_conflict || $domain_status->Status === -1 ? $controller->plugin_url . 'assets/img/robo-scared@2x.png' :*/
					$controller->plugin_url . 'assets/img/robo-happy@2x.png' ?> 2x"
				alt="ShortPixel Robo"
			>
			<h3><?= /*$has_conflict || $domain_status->Status === -1 ? __( 'ShortPixel Adaptive Images has detected issues to be solved immediately!', 'shortpixel-adaptive-images' ) :*/
					__( 'Welcome and thank you for installing the ShortPixel Adaptive Images Plugin!',
						'shortpixel-adaptive-images' ); ?></h3>
		</div>
		<div class="sp-obw__content-wrap">
			<?php
				if ( $has_conflict ) {
					echo '<p><span class="sp-obw-alert">' . __( 'To fully benefit from the ShortPixel Adaptive Images plugin, please check the notification above.', 'shortpixel-adaptive-images' ) . '</span></p>';
					echo '<p><strong>' . __( 'Once you took the appropriate action the Setup Wizard will start.', 'shortpixel-adaptive-images' ) . '</strong></p>';
				}
				else if ( $domain_status->Status === -1 ) {
					echo '<p><strong>' . __( 'Your ShortPixel Adaptive Images quota has been exceeded.', 'shortpixel-adaptive-images' ) . '</strong></p>';
					echo '<p><span class="sp-obw-alert">' . __( 'Please top-up your account to continue using the ShortPixel Adaptive Images plugin.' ) . '</span></p>';
					echo '<p>' . __( 'The already optimized images will still be served from the ShortPixel CDN for up to 30 days but the images that weren\'t already optimized and cached via CDN will be served directly from your website.', 'shortpixel-adaptive-images' ) . '</p>';
				}
				else {
					?>
					<p><?= sprintf( __( 'The plugin is active and already serving optimized versions of your site’s images from the CDN; you have %s traffic available. %s',
							'shortpixel-adaptive-images' ),
							!$domain_status->HasAccount
								? ( $domain_status->FreeCredits - $domain_status->UsedFreeCredits <= 0
									? 'no'
									: ShortPixelDomainTools::credits2bytes($domain_status->FreeCredits - $domain_status->UsedFreeCredits) ) . ' ' . __( 'free', 'shortpixel-adaptive-images' )
								:
								( isset($domain_cdn_usage->quota)
									? ( $domain_cdn_usage->quota->monthly->available > 0
										? ShortPixelDomainTools::credits2bytes($domain_cdn_usage->quota->monthly->available) . ' ' . __( 'monthly', 'shortpixel-adaptive-images' )
										: '' ) .
									  ( $domain_cdn_usage->quota->monthly->available > 0 && $domain_cdn_usage->quota->oneTime->available > 0
										  ? ' ' . __( 'and', 'shortpixel-adaptive-images' )
										  : '' ) .
									  ( $domain_cdn_usage->quota->oneTime->available > 0
										  ? ' ' . ShortPixelDomainTools::credits2bytes($domain_cdn_usage->quota->oneTime->available) . ' ' . __( 'one-time', 'shortpixel-adaptive-images' )
										  : '' )
									: '' ) .
								( !!$domain_cdn_usage && ( $domain_cdn_usage->quota->monthly->available <= 0 && $domain_cdn_usage->quota->oneTime->available <= 0 )
									? 'no'
									: '' ),
							//'https://shortpixel.com/knowledge-base/article/96-how-are-the-credits-counted',
							$domain_status->HasAccount && !isset($domain_cdn_usage->quota)
								? '<br>' . sprintf( __( 'The domain %s is associated to the ShortPixel account %s.', 'shortpixel-adaptive-images' ), '<strong>' . ShortPixelDomainTools::get_site_domain() . '</strong>',
									'<span>' . $domain_status->Email . '</span>' ) . ''
								: '' );
						?>
					</p>
					<p><strong><?= __( 'Next steps', 'shortpixel-adaptive-images' ); ?>:</strong></p>

					<div class="shortpixel-steps" data-step="<?php echo $step; ?>" data-spainonce="<?= $this->getNonce(); ?>">
						<?php
							foreach ( $steps[ 'titles' ] as $index => $title ) {
								$step_classes = [ 'step' ];

								if ( $step === $index ) {
									$step_classes[] = 'active';
								}
								else if ( $index < $step ) {
									$step_classes[] = 'passed';
								}

								$step_classes = implode( ' ', $step_classes );
								?>
								<div class="<?php echo $step_classes; ?>">
									<div class="number">
                                        <span class="sp-obw-step"><?php echo $index + 1; ?></span>
										<div class="title"><?php echo $title; ?></div>
									</div>
								</div>
								<?php
							}
						?>
					</div>

					<div class="step-message-wrap">
						<?php echo $steps[ 'messages' ][ $step ]; ?>
					</div>
					<?php
				}
			?>
		</div>
	</div>
<?php
