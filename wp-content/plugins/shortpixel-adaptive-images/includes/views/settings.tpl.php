<?php
	/**
	 * @var \ShortPixelAI $controller
	 */

use ShortPixel\AI\AccessControlHeaders;
use ShortPixel\AI\Notice;

$controller = $this->ctrl;
	$options    = $controller->options;

	$integrations = ShortPixel\AI\ActiveIntegrations::_( true );

	$status_box_integrations = [];

	foreach ( $integrations->getAll() as $integration => $value ) {
		if ( $integration === 'theme' ) {
			continue;
		}

		if ( is_bool( $value ) && !$value ) {
			continue;
		}
		else if ( is_array( $value ) && !!$value ) {
			$is_active = false;

			foreach ( $value as $item ) {
				if ( !!$item ) {
					$is_active = true;
					break;
				}
			}

			if ( $is_active ) {
				$status_box_integrations[] = ucwords( str_replace( [ '_', '-' ], ' ', $integration ) );
			}
		}
		else {
			$status_box_integrations[] = ucwords( str_replace( [ '_', '-' ], ' ', $integration ) );
		}
	}

	// SPAI API key
	$spai_key = $options->settings_general_apiKey;

	$domain_usage  = empty( $spai_key ) ? false : ShortPixelDomainTools::get_cdn_domain_usage( null, $spai_key, true );
	$domain_status = ShortPixelDomainTools::get_domain_status( true );
    $domain        = ShortPixelDomainTools::get_site_domain();
    $cdn_domain    = parse_url($options->settings->behaviour->api_url, PHP_URL_HOST);

    $is_our_cdn = ShortPixelDomainTools::is_our_cdn($cdn_domain);

    $submit_button_attributes = [
		'data-saving-text' => __( 'Saving...', 'shortpixel-adaptive-images' ),
	];

	$css_status = $domain_status->HasAccount && $domain_status->Status === 2
		? 'enough'
		: ( $domain_status->Status === 1
			? 'few'
			: ( $domain_status->Status <= 0 ? 'insufficiently' : '' ) );

	$status_text = $domain_status->Status === 1
		? ( $domain_status->HasAccount ? __( 'Your account usage is close to the CDN traffic limit.', 'shortpixel-adaptive-images' ) : __( 'Your free CDN traffic is close to the limit.', 'shortpixel-adaptive-images' ) )
		: ( $domain_status->Status < 0
			? '<span style="color: #ed3833;">'
              . ( $domain_status->HasAccount ? __( 'You have used all your account\'s CDN traffic.', 'shortpixel-adaptive-images' ) : __( 'You have used all your free CDN traffic.', 'shortpixel-adaptive-images' ) )
              . '</span>'
			: '' );

	$tooltip_text = $domain_status->Status === 1
		? ( $domain_status->HasAccount ? __( 'You have less than 10% of your CDN traffic available.', 'shortpixel-adaptive-images' ) . '<br>' . __( 'Please top up to ensure that you don\'t run out of CDN traffic.',
				'shortpixel-adaptive-images' ) : __( 'You have less than 10% of your CDN traffic available.', 'shortpixel-adaptive-images' ) . '<br>' . __( 'Please create an account to get 5Gb more free CDN traffic.',
				'shortpixel-adaptive-images' ) )
		: ( $domain_status->Status === -1
			? ( $domain_status->HasAccount ? __( 'Some of the image sizes and new images are no longer optimized.', 'shortpixel-adaptive-images' ) . '<br>' . __( 'Please top up to ensure that you don\'t run out of CDN traffic.',
					'shortpixel-adaptive-images' ) : __( 'Some of the image sizes and new images are no longer optimized.', 'shortpixel-adaptive-images' ) . '<br>' . __( 'Please create an account to get 5Gb more free CDN traffic.',
					'shortpixel-adaptive-images' ) )
			: ( $domain_status->Status === -2
				? ( $domain_status->HasAccount ? __( 'Your images are no longer optimized.', 'shortpixel-adaptive-images' ) . '<br>' . __( 'Please top up to ensure that you don\'t run out of CDN traffic.',
						'shortpixel-adaptive-images' ) : __( 'Your images are no longer optimized.', 'shortpixel-adaptive-images' ) . '<br>' . __( 'Please create an account to get 5Gb more free CDN traffic.',
						'shortpixel-adaptive-images' ) )
				: '' ) );
?>
	<div class="wpf-settings">
		<div class="shortpixel-settings-wrap">
			<h1><?= esc_html( get_admin_page_title() ); ?></h1>
			<div class="support-and-preferences">
                <a href="<?= apply_filters('spai_affiliate_link',ShortPixelAI::DEFAULT_MAIN_DOMAIN. '/pricing') ?>" target="_blank"><?= __( 'Buy Unlimited', 'shortpixel-adaptive-images' ); ?></a> |&nbsp;
				<a href="https://shortpixel.com/knowledge-base/category/307-shortpixel-adaptive-images" target="_blank"><?= __( 'Knowledge Base', 'shortpixel-adaptive-images' ); ?></a> |&nbsp;
				<a href="<?= apply_filters('spai_affiliate_link',ShortPixelAI::DEFAULT_MAIN_DOMAIN. '/contact') ?>" target="_blank"><?= __( 'Support', 'shortpixel-adaptive-images' ); ?></a>
			</div>
			<div class="spai_statusbox_wrap">
				<div class="title_wrap" data-status="<?= $css_status; ?>">
					<strong>
                        <?= $domain; ?>
                    </strong> <?=__('is', 'shortpixel-adaptive-images' ); ?>
                    <span class="<?= $domain_status->HasAccount ? 'success' : 'error'; ?>">
                        <?= $domain_status->HasAccount ? __( 'associated', 'shortpixel-adaptive-images' ) : __( 'not associated', 'shortpixel-adaptive-images' ); ?>
                    </span>
					<?= empty( $status_text ) ? ''
                        : '<div class="usage_msg">' . $status_text
                        . ' <span class="dashicons dashicons-editor-help" data-tippy-content="' . $tooltip_text . '" data-tippy-animation="shift-away" data-tippy-arrow="true"></span></div>'; ?>


                    <?php
                    if ($domain_status->Status <= 1 && (!isset($domain_usage->quota) || !$domain_usage->isSubaccount)) {
                        $login_link = ShortPixelAI::DEFAULT_MAIN_DOMAIN. '/login/' . ( $spai_key ?: '_' ) . '/pricing-adaptive-cdn/http'
                            . urlencode( ($_SERVER['HTTPS'] ? 's' : '') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
                        $login_link = $spai_key ? $login_link : apply_filters('spai_affiliate_link', $login_link); //only apply the affiliate filter if no API key
                        ?>

                                    <a href="<?= $login_link; ?>" target="_blank" class="bordered_link btn_topup" data-tippy-content="<?= __( 'Top-up account', 'shortpixel-adaptive-images' ); ?>"
                                       data-tippy-animation="shift-away" data-tippy-placement="left" data-tippy-arrow="true"><?= __( 'Top-up now', 'shortpixel-adaptive-images' ); ?></a><br>

                    <?php } ?>

				</div>
				<div class="box_content">
					<?php
                        $dFormat = get_option('date_format');
						if ( $domain_status->HasAccount && $domain_usage ) {

                            $total_monthly_label = $is_our_cdn ? __( 'Total monthly traffic', 'shortpixel-adaptive-images' ) : __( 'Total monthly credits', 'shortpixel-adaptive-images' );
                            $unlimited = ($domain_status->Unlimited == 'true');
                            if($unlimited) {
                                $total_monthly = __('Unlimited', 'shortpixel-adaptive-images');
                                $total_monthly_available = '';
                            } else {
                                $total_monthly = $is_our_cdn ? ShortPixelDomainTools::credits2bytes(min($domain_usage->quota->monthly->totalCDN, $domain_usage->quota->monthly->total)) : number_format($domain_usage->quota->monthly->available);
                                $total_monthly_available = $is_our_cdn ? ShortPixelDomainTools::credits2bytes(min($domain_usage->quota->monthly->totalCDN, $domain_usage->quota->monthly->available)) : number_format($domain_usage->quota->monthly->available);
                                $total_onetime = $is_our_cdn ? ShortPixelDomainTools::credits2bytes($domain_usage->quota->oneTime->total) : number_format($domain_usage->quota->oneTime->available);
                                $total_onetime_available = $is_our_cdn ? ShortPixelDomainTools::credits2bytes($domain_usage->quota->oneTime->available) : number_format($domain_usage->quota->oneTime->available);
                                $total_onetime_used = $is_our_cdn ? ShortPixelDomainTools::credits2bytes($domain_usage->quota->oneTime->used) : number_format($domain_usage->quota->oneTime->used);
                                $total_onetime_label = $is_our_cdn ? __( 'Total one-time traffic', 'shortpixel-adaptive-images' ) : __( 'Total one-time credits', 'shortpixel-adaptive-images' );
                            }
                            $total_monthly_used = $is_our_cdn ? ShortPixelDomainTools::credits2bytes(min($domain_usage->quota->monthly->totalCDN, $domain_usage->quota->monthly->used)) : number_format($domain_usage->quota->monthly->used);

						    if(!preg_match('/_CHILD_[0-9]+$/s', $domain_usage->email)) {
                                $login_link = ShortPixelAI::DEFAULT_MAIN_DOMAIN. "/login/" . ( $spai_key ?: '_' ) . '/dashboard';
                                $login_link = $spai_key ? $login_link : apply_filters('spai_affiliate_link', $login_link);
                                ?>
                                <p class="clearfix">
                                    <a href="<?= $login_link; ?>" target="_blank" class="bordered_link login_btn" data-tippy-content="<?= __( 'Login into your ShortPixel account.', 'shortpixel-adaptive-images' ); ?>"
                                       data-tippy-animation="shift-away" data-tippy-placement="left" data-tippy-arrow="true"><?= __( 'Login', 'shortpixel-adaptive-images' ); ?></a>
                                    <?= __( 'Account', 'shortpixel-adaptive-images' ); ?>: <strong><a href="<?= $login_link; ?>" target="_blank"><?= $domain_usage->email; ?></a></strong>
                                </p>
                            <?php } ?>

							<button class="bordered_link login_btn" data-action="remove key"
                                    data-tippy-content="<?= __('Forget the API key for this domain', 'shortpixel-adaptive-images' ); ?>. <?= __('The plugin will continue work, but you won\'t see the stats below.', 'shortpixel-adaptive-images' ); ?>"
                                    data-tippy-animation="shift-away" data-tippy-placement="left"
                                    data-tippy-arrow="true"><?= __( 'Forget', 'shortpixel-adaptive-images' ); ?></button>

							<p><?= $total_monthly_label . ': <strong>' . $total_monthly . '</strong>'; ?></p>
							<div class="progress_wrap">
								<div class="available"><?= $total_monthly_available ? $total_monthly_available . ' ' . __( 'available', 'shortpixel-adaptive-images' ) : ''; ?></div>
								<div class="used"><?= $total_monthly_used . ' ' . __( 'used', 'shortpixel-adaptive-images' ); ?></div>
								<div class="progress">
									<div class="used" style="width: <?= $domain_usage->quota->monthly->usedPercent; ?>%"></div>
								</div>
                                <?php if(!$unlimited) { ?>
                                    <div class="available">until <?= $domain_usage->quota->monthly->nextBillingDate->format($dFormat); ?></div>
                                <?php } ?>
							</div>
                            <?php if(!$unlimited) { ?>
                                <p><?= $total_onetime_label . ': <strong>' . $total_onetime . '</strong>'; ?></p>
                                <div class="progress_wrap">
                                    <div class="available"><?= $total_onetime_available . ' ' . __( 'available', 'shortpixel-adaptive-images' ); ?></div>
                                    <div class="used"><?= $total_onetime_used . ' ' . __( 'used', 'shortpixel-adaptive-images' ); ?></div>
                                    <div class="progress">
                                        <div class="used" style="width: <?= $domain_usage->quota->oneTime->usedPercent; ?>%"></div>
                                    </div>
                                </div>
                            <?php } ?>
							<div class="chart-wrap">
								<div class="toggle"></div>
								<p>Daily:</p>
								<canvas id="chart"></canvas>
							</div>
                            <?php if($is_our_cdn) {?>
							<p><?= __( 'CDN traffic used', 'shortpixel-adaptive-images' ) . ': <strong>'
                                . ShortPixelDomainTools::formatBytes($domain_usage->cdn->used) . '</strong> '
                                . __( 'since', 'shortpixel-adaptive-images' ) . ' '
                                . $domain_usage->quota->monthly->lastBillingDate->format($dFormat); ?>.</p>
                                <?php if(!$unlimited) { ?>
                                    <div class="progress_wrap">
                                        <div class="available"><?= ShortPixelDomainTools::formatBytes($domain_usage->cdn->available) . ' ' . __( 'available', 'shortpixel-adaptive-images' ); ?></div>
                                        <div class="used"><?=  ShortPixelDomainTools::formatBytes($domain_usage->cdn->used) . ' ' . __( 'used', 'shortpixel-adaptive-images' ); ?></div>
                                        <div class="progress">
                                            <div class="used" style="width: <?= $domain_usage->cdn->usedPercent; ?>%"></div>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                            <p><span><?= __( 'Your monthly consumption will reset on', 'shortpixel-adaptive-images' ) . ': <strong>'
                                    . $domain_usage->quota->monthly->nextBillingDate->format($dFormat); ?></strong>.</span></p>
							<?php
						}
						else if ( !$domain_status->HasAccount && $domain_status->FreeCredits) {

                            $domain_status->UsedFreeCredits = $domain_status->UsedFreeCredits > $domain_status->FreeCredits ? $domain_status->FreeCredits : $domain_status->UsedFreeCredits;
                            $used_percent      = round( 100 * $domain_status->UsedFreeCredits / $domain_status->FreeCredits, 0 );
                            $total_label = $is_our_cdn ? __( 'Total traffic', 'shortpixel-adaptive-images' ) : __( 'Total credits', 'shortpixel-adaptive-images' );
                            $total_free = $is_our_cdn ? ShortPixelDomainTools::credits2bytes($domain_status->FreeCredits) : number_format($domain_status->FreeCredits);
                            $total_free_available = $is_our_cdn ? ShortPixelDomainTools::credits2bytes($domain_status->FreeCredits - $domain_status->UsedFreeCredits) : number_format($domain_status->FreeCredits - $domain_status->UsedFreeCredits);
                            $total_free_used = $is_our_cdn ? ShortPixelDomainTools::credits2bytes($domain_status->UsedFreeCredits) : number_format($domain_status->UsedFreeCredits);
                            ?>
							<div class="buttons-wrap">
								<a href="<?= apply_filters('spai_affiliate_link',ShortPixelAI::DEFAULT_MAIN_DOMAIN. '/free-sign-up') ?>" target="_blank" class="dark_blue_link"><?= __( 'Create account', 'shortpixel-adaptive-images' ); ?></a>
								<a href="<?= apply_filters('spai_affiliate_link',ShortPixelAI::DEFAULT_MAIN_DOMAIN. '/login/_/associated-domains') ?>" target="_blank" class="bordered_link"><?= __( 'Associate existing', 'shortpixel-adaptive-images' ); ?></a>
							</div>
							<p><?= $total_label . ': <strong>' . $total_free . '</strong>'; ?></p>
							<div class="progress_wrap">
								<div class="available">
                                    <?= $total_free_available . ' ' . __( 'available', 'shortpixel-adaptive-images' ); ?></div>
								<div class="used"><?= $total_free_used . ' ' . __( 'used', 'shortpixel-adaptive-images' ); ?></div>
								<div class="progress">
									<div class="used" style="width: <?= $used_percent; ?>%"></div>
								</div>
							</div>
							<p><?= __( 'CDN traffic', 'shortpixel-adaptive-images' ) . ': <strong>'
                                . ShortPixelDomainTools::formatBytes( $domain_status->CDNQuota ) . '</strong>'; ?></p>
							<div class="progress_wrap">
								<?php
									$used_percent      = round( $domain_status->UsedCDN / ( $domain_status->CDNQuota / 100 ), 2 );
								?>
								<div class="available"><?= ShortPixelDomainTools::formatBytes(  $domain_status->CDNQuota - $domain_status->UsedCDN  ) . ' ' . __( 'available', 'shortpixel-adaptive-images' ); ?></div>
								<div class="used"><?= ShortPixelDomainTools::formatBytes( $domain_status->UsedCDN ) . ' ' . __( 'used', 'shortpixel-adaptive-images' ); ?></div>
								<div class="progress">
									<div class="used" style="width: <?= $used_percent; ?>%"></div>
								</div>
							</div>
							<a href="<?php
                                $login_link = ShortPixelAI::DEFAULT_MAIN_DOMAIN. '/login/' . ( $spai_key ?: '_' ) . '/pricing-adaptive-cdn/http'
                                    . urlencode( ($_SERVER['HTTPS'] ? 's' : '') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
                                echo( $spai_key ? $login_link : apply_filters('spai_affiliate_link', $login_link));
                                ?>"
                               target="_blank" class="dark_blue_link full_width">Top-up now</a>
							<?php
						}
						else {
							$api_key = $options->settings_general_apiKey;
							?>
							<div class="buttons-wrap">
								<form id="api-key-form" method="post" action="<?= admin_url( 'admin-ajax.php' ); ?>">
                                    <input type="hidden" name="spainonce" value="<?= $this->getNonce() ?>"/>
									<input type="hidden" name="action" value="shortpixel_ai_handle_page_action" />
									<input type="hidden" name="page" value="settings" />
									<input type="hidden" name="data[action]" value="save key" />

									<label for="api_key"><?= __( 'Enter your account\'s API key to get detailed info', 'shortpixel-adaptive-images' ) . ':'; ?></label>
									<input id="api_key" class="full_width" type="text" name="data[api_key]" size="25" value="<?= $api_key; ?>" autocomplete="off"/>
									<button type="submit" class="dark_blue_link full_width" data-saving-text="<?= __( 'Saving...', 'shortpixel-adaptive-images' ); ?>"><?= __( 'Save', 'shortpixel-adaptive-images' ); ?></button>
								</form>
							</div>
							<?php
						}
					?>
                    <div class="box_dropdown spai_news first" data-readmore="<?= __( 'Read more', 'shortpixel-adaptive-images' ); ?>">
                        <div class="title"><?= __( 'ShortPixel News', 'shortpixel-adaptive-images' ); ?></div>
                        <div class="dropdown_content loading" id="spaiNewsFeed">
                        </div>
                    </div>
					<div class="box_dropdown">
						<div class="title"><?= __( 'Active integrations', 'shortpixel-adaptive-images' ); ?></div>
						<div class="dropdown_content">
							<?php
								echo '<p><strong>' . __( 'Theme:', 'shortpixel-adaptive-images' ) . '</strong> ' . $integrations->get( 'theme' ) . '</p>';

								foreach ( $status_box_integrations as $index => $integration ) {
									echo '<p><strong>' . ( $index + 1 ) . '.</strong> ' . $integration . '</p>';
								}
							?>
						</div>
					</div>
                    <div class="box_dropdown">
						<div class="title"><?= __( 'Dismissed notifications', 'shortpixel-adaptive-images' ); ?></div>
						<div class="dropdown_content">
							<?php
								$dismissed = Notice::getDismissed();

								if ( !empty( (array) $dismissed ) ) {
									foreach ( $dismissed as $key => $item ) {
										$message = $this->noticeConstants->{$key};

                                        if($key == 'credits') {
	                                        $message = Notice::getCreditsNoticeInfo($controller);
                                            if(empty($message)) continue;
                                        }
                                        if(empty($message['causer'])) {
                                            $message['causer'] = $key;
                                        }
                                        if( $key == 'key' && !empty( $message['body'][0] ) ) {
	                                        $account = \ShortPixelDomainTools::get_shortpixel_account();
	                                        $message['body'][0] = sprintf( $message['body'][0], $account->email );
                                        }
										?>
										<div class="dismissed-notice-wrap" data-key="<?= ucwords( str_replace( [ '_', '-' ], ' ', $key ) ); ?>">
                                            <div class="dismissed-notice" data-causer="<?= $message['causer'] ?>" data-plugin="short-pixel-ai">
                                            <h4><?= $message[ 'title' ]; ?></h4>
                                                <?php
                                                    foreach ( $message[ 'body' ] as $paragraph ) {
                                                        echo '<p>' . $paragraph . '</p>';
                                                    }
                                                    if(isset($message['buttons'])) {
                                                        ?><div class="buttons-wrap"><?php
                                                        echo Notice::renderButtons($message['buttons']);
                                                        ?></div><?php
                                                    }
                                                ?>
                                            </div>
										</div>
									<?php }
								}
								else {
									echo '<p>' . __( 'No dismissed notifications.', 'shortpixel-adaptive-images' ) . '</p>';
								} ?>
						</div>
					</div>
				</div>
            </div>

            <div id="spaiHelpShade" class="spai-modal-shade" style="display:none;"></div>
            <div id="spaiHelp" class="spai-modal spai-hide">
                <div class="spai-modal-title">
                    <button type="button" class="spai-close-help-button" onclick="jQuery.spaiHelpClose()">&times;</button>
                </div>
                <div class="spai-modal-body">
                    <div class="local-content hidden"></div>
                    <iframe src="about:blank" width="100%" height="400" style="border:none"></iframe>
                </div>
            </div>
            <script  type="text/javascript" id="spai_help_js">
                jQuery(document).ready(function(){jQuery.spaiHelpInit();});
            </script>

			<div class="shortpixel-settings-tabs">
				<h2 class="nav-tab-wrapper" id="wpspai-tabs">
                    <?php  if($options->get( 'advanced' , [ 'flags', 'all' ] )) { ?>
                        <a class="nav-tab nav-tab-active" id="compression-tab" href="#top#compression"><?= __( 'Compression', 'shortpixel-adaptive-images' ); ?></a>
                        <a class="nav-tab" id="behaviour-tab" href="#top#behaviour"><?= __( 'Behavior', 'shortpixel-adaptive-images' ); ?></a>
                        <a class="nav-tab" id="areas-tab" href="#top#areas"><?= __( 'Areas', 'shortpixel-adaptive-images' ); ?></a>
                        <a class="nav-tab" id="exclusions-tab" href="#top#exclusions"><?= __( 'Exclusions', 'shortpixel-adaptive-images' ); ?></a>
					<?php } ?>
                    <span class="spai_top_actions" style=" height: 24px;">
                        <span class="spai_settings_advanced_popup hidden">
                            <div>
                                <p style="margin-bottom: 30px; font-size:140%"><?= __( 'Are you sure you want to disable the Advanced mode? Some of the settings will no longer be visible and will be reverted to their defaults.', 'shortpixel-adaptive-images' ); ?></p>
                                <p style="text-align: center; margin-bottom: 30px;">
                                    <input type="button" class="button-primary dark_blue_link button-yes" value="<?= __( 'Yes', 'shortpixel-adaptive-images' ); ?>" />
                                    <input type="button" class="button blue_link button-no" value="<?= __( 'No', 'shortpixel-adaptive-images' ); ?>" />
                                    <input type="button" class="button blue_link export-settings" title="<?= __( 'Export the current settings', 'shortpixel-adaptive-images' ); ?>" value="<?= __( 'Export settings', 'shortpixel-adaptive-images' ); ?>" />
                                </p>
                            </div>
                        </span>

                        <input
                                id="spai_settings_advanced"
                                type="checkbox"
                                name="advanced"
                                class="tgl"
                                data-type="bool"
                                value="1"
										<?php checked( 1, $options->flags_all_advanced, true ); ?>/>
                        <label for="spai_settings_advanced" class="tgl-btn">
										<span style="margin-top:8px"></span>
                        </label>
                        <?= __( 'Advanced', 'shortpixel-adaptive-images' ); ?>

                    </span>
                    <?php if( $options->settings_areas_parseCssFiles > 0 ): ?>
                    <button id="clear_css_cache" class="spai_top_actions" title="<?= __( 'Clear CSS Cache', 'shortpixel-adaptive-images' ) ?>">
                        <span class="clearcss-icon"></span>
                    </button>
                    <?php endif; ?>
                    <button id="export_settings" class="spai_top_actions" data-pressed-text="..." title="<?= __( 'Export settings', 'shortpixel-adaptive-images' ); ?>">
                        <span class="dashicons dashicons-download" style="height: 20px;padding-top: 4px;"></span>
                    </button>
                    <form id="import_settings_form" action="admin.php?page=shortpixel-ai-import-settings" method="post" enctype="multipart/form-data">
                        <label for="import_settings_file" id="import_settings" class="spai_top_actions" data-pressed-text="..." title="<?= __( 'Import settings...', 'shortpixel-adaptive-images' ); ?>">
                            <span class="dashicons dashicons-upload" style="height: 20px;padding-top: 4px;"></span>
                        </label>
                        <input type="file" id="import_settings_file" name="import_settings_file" style="display:none;">
                        <input type="hidden" id="import_spainonce" name="spainonce" value="<?= $this->getNonce() ?>">
                    </form>
				</h2>
				<form id="settings-form" method="post" action="<?= admin_url( 'admin-ajax.php' ); ?>">
                    <input type="hidden" name="spainonce" value="<?= $this->getNonce() ?>">
					<?php  if($options->get( 'advanced' , [ 'flags', 'all' ] )) { ?>
                        <div id="compression" class="spai_settings_tab active">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?= __( 'Compression Level', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help"><span class="dashicons dashicons-editor-help" title="Inline help" data-link="https://shortpixel.com/knowledge-base/article/11-lossy-glossy-or-lossless-which-one-is-the-best-for-me"></span></div>
                                        <?php
                                            $compression_level = $options->get( 'level', [ 'settings', 'compression' ], 'lossy' );
                                        ?>
                                        <div class="shortpixel_radio_btns" style="margin-right:10px">
                                            <input id="lossy" type="radio" name="level" data-type="string" value="lossy" <?php checked( 'lossy', $compression_level, true ); ?>>
                                            <label for="lossy" title="<?= __( 'This is the recommended option in most cases, producing results that look the same as the original to the human eye.', 'shortpixel-adaptive-images' ); ?>">
                                                <span>Lossy</span>
                                            </label>
                                            <input id="glossy" type="radio" name="level" data-type="string" value="glossy" <?php checked( 'glossy', $compression_level, true ); ?>>
                                            <label for="glossy" title="<?= __( 'Best option for photographers and other professionals that use very high quality images on their sites and want best compression while keeping the quality untouched.', 'shortpixel-adaptive-images' ); ?>">
                                                <span>Glossy</span>
                                            </label>
                                            <input id="lossless" type="radio" name="level" data-type="string" value="lossless" <?php checked( 'lossless', $compression_level, true ); ?>>
                                            <label
                                                for="lossless"
                                                title="<?= __( 'Make sure not a single pixel looks different in the optimized image compared to the original. In some rare cases you will need to use this type of compression. Some technical drawings or images from vector graphics are possible situations.' ); ?>"
                                            >
                                                <span>Lossless</span>
                                            </label>
                                        </div>
                                        <?= sprintf(__( '<a href="%s" target="_blank">Run a few tests</a> to help you decide.', 'shortpixel-adaptive-images' ), apply_filters('spai_affiliate_link','https://shortpixel.com/online-image-compression')); ?>
                                        <p class="<?= $compression_level === 'lossy' ? '' : 'hidden'; ?>" data-explanation="lossy"><?=
                                                __( '<b>Lossy compression (recommended): </b>offers the best compression rate.</br> This is the recommended option for most users, producing results that look the same as the original to the human eye.',
                                                    'shortpixel-adaptive-images' ); ?></p>
                                        <p class="<?= $compression_level === 'glossy' ? '' : 'hidden'; ?>" data-explanation="glossy"><?=
                                                __( '<b>Glossy compression: </b>creates images that are almost pixel-perfect identical to the originals.</br> Best option for photographers and other professionals that use very high quality images on their sites and want the best compression while keeping the quality untouched.',
                                                    'shortpixel-adaptive-images' ); ?></p>
                                        <p class="<?= $compression_level === 'lossless' ? '' : 'hidden'; ?>" data-explanation="lossless"><?=
                                                __( '<b>Lossless compression: </b> the resulting image is pixel-identical to the original image.</br>Make sure not a single pixel looks different in the optimized image compared to the original.
                                            In some rare cases you will need to use this type of compression. Some technical drawings or images from vector graphics are possible situations.', 'shortpixel-adaptive-images' ); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <?php
                                    $ng_type_support = ( $options->settings_compression_pngToWebp || $options->settings_compression_jpgToWebp || $options->settings_compression_gifToWebp );
                                    $webp_support = $options->settings_compression_webp && $ng_type_support;
                                    $avif_support = $options->settings_compression_avif && $ng_type_support;
                                    $ng_support = ($webp_support || $avif_support);


                                    if ( !$webp_support ) {
                                        $options->settings_compression_webp = false;
                                    }
                                    if ( !$avif_support ) {
                                        $options->settings_compression_avif = false;
                                    }
                                    $include_label = __( 'Enable for <strong>%s</strong> images', 'shortpixel-adaptive-images' );
                                    ?>

                                    <th scope="row"><?= __( 'Next-gen Images Support', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help"><span class="dashicons dashicons-editor-help" title="Inline help" data-link="https://shortpixel.com/knowledge-base/article/482-spai-webp-support"></span></div>
                                        <?=
                                        sprintf(__( 'Serve the images in the %s WebP and %s AVIF next-gen image formats to all the browsers that support <a href="https://caniuse.com/#search=webp" target="_blank">WebP</a> or <a href="https://caniuse.com/#search=avif" target="_blank">AVIF</a>.', 'shortpixel-adaptive-images' ),
                                            '<input
                                                id="webp"
                                                type="checkbox"
                                                name="webp"
                                                class="tgl"
                                                data-type="bool"
                                                data-depender="true"
                                                value="1"
                                            ' . checked( 1, $webp_support, false ) . '/>
                                        <label for="webp" class="tgl-btn">
                                            <span style="display:inline-block !important; float: none !important;margin: 1px 5px 1px 10px !important;"></span>
                                        </label>',
                                                '<input
                                                id="avif"
                                                type="checkbox"
                                                name="avif"
                                                class="tgl"
                                                data-depender="true"
                                                data-type="bool"
                                                value="1"
                                            ' . checked( 1, $avif_support, false ) . '/>
                                        <label for="avif" class="tgl-btn">
                                            <span style="display:inline-block !important; float: none !important;margin: 1px 5px 1px 10px !important;"></span>
                                        </label>' ); ?>

                                        <div class="children-wrap<?= $ng_support ? '' : ' hidden' ?>" data-depended="webp|avif">
                                            <input
                                                    id="png_to_webp"
                                                    type="checkbox"
                                                    name="png_to_webp"
                                                    class="tgl"
                                                    data-type="bool"
                                                    value="1"
                                                <?php checked( true, $options->settings_compression_pngToWebp, true ); ?>
                                            />
                                            <label for="png_to_webp" class="tgl-btn">
                                                <span></span>
                                                <?= sprintf( $include_label, 'PNG' ); ?>
                                            </label>
                                        </div>
                                        <div class="children-wrap<?= $ng_support ? '' : ' hidden' ?>" data-depended="webp|avif">
                                            <input
                                                    id="jpg_to_webp"
                                                    type="checkbox"
                                                    name="jpg_to_webp"
                                                    class="tgl"
                                                    data-type="bool"
                                                    value="1"
                                                <?php checked( true, $options->settings_compression_jpgToWebp, true ); ?>
                                            />
                                            <label for="jpg_to_webp" class="tgl-btn">
                                                <span></span>
                                                <?= sprintf( $include_label, 'JPG' ); ?>
                                            </label>
                                        </div>
                                        <div class="children-wrap<?= $ng_support ? '' : ' hidden' ?>" data-depended="webp|avif">
                                            <input
                                                    id="gif_to_webp"
                                                    type="checkbox"
                                                    name="gif_to_webp"
                                                    class="tgl"
                                                    data-type="bool"
                                                    value="1"
                                                <?php checked( true, $options->settings_compression_gifToWebp, true ); ?>
                                            />
                                            <label for="gif_to_webp" class="tgl-btn">
                                                <span></span>
                                                <?= sprintf( $include_label, 'GIF' ); ?>
                                            </label>
                                        </div>
                                        <p class="description">
                                            <?= __( 'The AVIF and WebP formats benefit from newer powerful algorithms that are able to make the image smaller than older formats such as PNG or JPEG. The conversion and optimization from the original image format to AVIF or WebP is done on-the-fly by ShortPixel. Recommended for SEO.', 'shortpixel-adaptive-images' ); ?>
                                        </p>

                                        <?php
                                        if(strpos($options->settings->behaviour->api_url, \ShortPixelAI::DEFAULT_API_AI) === false)
                                        {?>
                                            <div class="children-wrap<?= $ng_support ? '' : ' hidden' ?>">
                                                <input
                                                        id="webp_detect"
                                                        type="checkbox"
                                                        name="webp_detect"
                                                        class="tgl"
                                                        data-type="bool"
                                                        value="1"
                                                    <?php checked( true, $options->settings_compression_webpDetect); ?>
                                                />
                                                <label for="webp_detect" class="tgl-btn">
                                                    <span></span>
                                                    <?= __('Detect WebP/AVIF support in browser.', 'shortpixel-adaptive-images' ); ?>
                                                </label>
                                            </div>
                                            <p class="description children-wrap<?= $ng_support ? '' : ' hidden' ?>">
                                                <?= __( ' Check this if you\'re using your own CDN and it doesn\'t support serving different resources based on the Accept header.',
                                                    'shortpixel-adaptive-images' ); ?>
                                            </p>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'Remove EXIF', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/483-spai-remove-exif"></span></div>
                                        <input
                                            id="remove_exif"
                                            type="checkbox"
                                            name="remove_exif"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
                                            <?php checked( 1, $options->settings_compression_removeExif, true ); ?>/>
                                        <label for="remove_exif" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Remove the EXIF info from the images.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'The images are smaller and no information about author/location is present in the image. <a href="https://shortpixel.com/blog/how-much-smaller-can-be-images-without-exif-icc/" target="_blank">Read more</a>',
                                                'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div id="behaviour" class="spai_settings_tab">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?= __( 'Fade-in effect', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/484-spai-fade-in-effect"></span></div>
                                        <input
                                            id="fadein"
                                            type="checkbox"
                                            name="fadein"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
                                            <?php checked( 1, $options->settings_behaviour_fadein, true ); ?>/>
                                        <label for="fadein" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Fade-in the lazy-loaded images.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'If you experience problems with images that zoom on hover or have other special effects, try deactivating this option.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'Smart crop', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/182-what-is-smart-cropping"></span></div>
                                        <input
                                            id="crop"
                                            type="checkbox"
                                            name="crop"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
                                            <?php checked( 1, $options->settings_behaviour_crop, true ); ?>/>
                                        <label for="crop" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Smartly crop the images when it\'s possible and safe.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'The plugin identifies the images that are not completely displayed, and crops them accordingly. This might not work for some backgrounds (won\'t harm them though).', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                        <div class="notification_popup hidden">
                                            <p class="text"><?= __( 'In some rare cases this option can shift the backgrounds relative to the uncropped ones. Please check your site after activating this option. If you notice any issue, you can always revert by deactivating it.',
                                                    'shortpixel-adaptive-images' ); ?></p>
                                            <input type="button" class="button blue_link" value="<?= __( 'Activate Option', 'shortpixel-adaptive-images' ); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'Replace method', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/485-spai-replace-method"></span></div>
                                        <?php
                                            $replace_method = $options->get( 'replace_method', [ 'settings', 'behaviour' ], 'src' );
                                        ?>
                                        <div class="shortpixel_radio_btns">
                                            <input id="src" type="radio" name="replace_method" data-type="string" value="src" <?php checked( 'src', $replace_method, true ); ?>>
                                            <label for="src" title="<?= __( 'SRC makes sure as many images as possible are replaced with a best-fit resized and optimized image.', 'shortpixel-adaptive-images' ); ?>">
                                                <span>SRC</span>
                                            </label>
                                            <input id="both" type="radio" name="replace_method" data-type="string" value="both" <?php checked( 'both', $replace_method, true ); ?>>
                                            <label for="both" title="<?= __( 'EXPERIMENTAL: Use BOTH if you have images that dynamically change size (enlarge on hover, etc.)', 'shortpixel-adaptive-images' ); ?>">
                                                <span>BOTH</span>
                                            </label>
                                            <input id="srcset" type="radio" name="replace_method" data-type="string" value="srcset" <?php checked( 'srcset', $replace_method, true ); ?>>
                                            <label for="srcset" title="<?= __( 'EXPERIMENTAL: Use SRCSET if you still encounter problems with specific content.', 'shortpixel-adaptive-images' ); ?>">
                                                <span>SRCSET</span>
                                            </label>
                                        </div>
                                        <p class="description <?= $replace_method === 'src' ? '' : 'hidden'; ?>" data-explanation="src"><?= __( 'SRC makes sure as many images as possible are replaced with a best-fit resized and optimized image.', 'shortpixel-adaptive-images' ); ?></p>
                                        <p class="description <?= $replace_method === 'both' ? '' : 'hidden'; ?>" data-explanation="both"><?= __( 'EXPERIMENTAL: Use BOTH if you have images that dynamically change size (enlarge on hover, etc.)', 'shortpixel-adaptive-images' ); ?></p>
                                        <p class="description <?= $replace_method === 'srcset' ? '' : 'hidden'; ?>" data-explanation="srcset"><?= __( 'EXPERIMENTAL: Use SRCSET if you still encounter problems with specific content.', 'shortpixel-adaptive-images' ); ?></p>
                                        <div class="children-wrap<?= $replace_method === 'src' ? '' : ' hidden' ?>" data-parent="src">
                                            <input
                                                id="generate_noscript"
                                                type="checkbox"
                                                name="generate_noscript"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                                <?php checked( true, $options->settings_behaviour_generateNoscript, true ); ?>
                                            />
                                            <label for="generate_noscript" class="tgl-btn">
                                                <span></span>
                                                <?= sprintf( __( 'Generate %s tag', 'shortpixel-adaptive-images' ), esc_html( '<noscript>' ) ); ?>
                                            </label>
                                            <p class="description"><?= __( 'Generate the fallback html for the browsers that do not support JavaScript.', 'shortpixel-adaptive-images' ); ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'API URL', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                        <span class="dashicons dashicons-editor-help" title="Inline help"
                                              data-link="https://shortpixel.com/knowledge-base/article/486-spai---api-url"></span></div>
                                        <input
                                            type="text"
                                            name="api_url"
                                            data-type="string"
                                            size="40"
                                            class="spai_triggers_notification"
                                            placeholder="<?= ShortPixelAI::DEFAULT_API_AI . ShortPixelAI::DEFAULT_API_AI_PATH; ?>"
                                            value="<?= $options->settings_behaviour_apiUrl; ?>"
                                        />
                                        <p class="description">
                                            <?= __( 'Change this only if you want to use your own CDN (examples <a href="https://shortpixel.com/knowledge-base/article/180-can-i-use-a-different-cdn-with-shortpixel-adaptive-images" target="_blank">here</a> and <a href="https://shortpixel.com/knowledge-base/article/200-setup-your-stackpath-account-so-that-it-can-work-with-shortpixel-adaptive-images-api" target="_blank">here</a>) or if you want to set up your <a href="https://shortpixel.com/knowledge-base/article/569-how-to-serve-the-images-from-a-custom-domain" target="_blank">custom domain</a>.',
                                                'shortpixel-adaptive-images' ); ?>
                                        </p>
                                        <div class="notification_popup hidden">
                                            <p class="text">
                                                <?= __( 'If you will use your own CDN, ShortPixel will switch from counting the traffic (1 credit = 5MB) to counting the number of scaled & optimized images. Depending on the site, this can increase considerably the number of credits used. If you\'re instead using a DNS CNAME to our CDN, the traffic will continued to be counted as usual. <a href="https://shortpixel.com/knowledge-base/article/460-spai-new-how-are-the-credits-counted" target="_blank">How are credits counted?</a>',
                                                'shortpixel-adaptive-images' ); ?>
                                            </p>
                                            <input type="button" class="button blue_link" value="<?= __( 'Agree', 'shortpixel-adaptive-images' ); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <?= __( 'Lazy-load threshold', 'shortpixel-adaptive-images' ); ?>
                                    </th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/487-spai-lazy-load-threshold"></span></div>
                                        <?php
                                            $lazy_threshold = $options->get( 'lazy_threshold', [ 'settings', 'behaviour', 500 ] );
                                            $lazy_threshold = is_int( $lazy_threshold ) && $lazy_threshold >= 0 ? $lazy_threshold : 500;
                                        ?>

                                        <label><input
                                                type="number"
                                                name="lazy_threshold"
                                                data-type="int"
                                                min="0"
                                                max="10000"
                                                value="<?= $lazy_threshold; ?>"
                                            /> px.</label>
                                        <p class="description">
                                            <?= __( 'The lazy-load threshold identifies how early before entering the visible area of the page (viewport) a lazy image is loaded. For example, the default of 500px means that an image is loaded when it\'s closer than 500px to the viewport.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <?= __( 'Images hover handling', 'shortpixel-adaptive-images' ); ?>
                                    </th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/488-spai-images-hover-handling"></span></div>
                                        <input
                                            id="hover_handling"
                                            type="checkbox"
                                            name="hover_handling"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
                                            <?php checked( true, $options->settings_behaviour_hoverHandling, true ); ?>>
                                        <label for="hover_handling" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Handle the on hover swapping images.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'Handles the situations when hovering an image reveals another image. With this option turned on, the image "behind" is resized, optimized and served from the CDN.', 'shortpixel-adaptive-images' ); ?>
                                            <?= __( 'Only activate it if you need it.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <?= __( 'Logged-in users', 'shortpixel-adaptive-images' ); ?>
                                    </th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/489-spai-logged-in-users"></span></div>
                                        <input
                                                id="replace_logged_in"
                                                type="checkbox"
                                                name="replace_logged_in"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                            <?php checked( true, $options->get( 'replace_logged_in', [ 'settings', 'behaviour' ], true ), true ); ?>>
                                        <label for="replace_logged_in" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Optimize the images for logged-in users.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'Deactivate this option if the image optimization conflicts with your page builder. Please note that in this case you will no longer be able to use the <a href="https://shortpixel.com/knowledge-base/article/338-how-to-use-the-image-checker-tool" target="_blank">Image Checker Tool</a>.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <?php
                                        $lqip        = !!$options->settings_behaviour_lqip;
                                        $process_way = $options->settings_behaviour_processWay;
                                        $process_way = empty( $process_way ) ? 'cron' : $process_way;
                                    ?>
                                    <th scope="row">
                                        <?= __( 'LQ image placeholders', 'shortpixel-adaptive-images' ); ?>
                                    </th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/447-low-quality-image-placeholders-lqip-and-shortpixel-adaptive-images-faq"></span></div>
                                        <input
                                            id="lqip"
                                            type="checkbox"
                                            name="lqip"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
                                            <?php checked( true, $lqip, true ); ?>>
                                        <label for="lqip" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Create and use inline Low Quality Image Placeholders (LQIPs) instead of blank placeholders.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'The LQIPs are created after the first display of that particular image and are cached in the Uploads folder.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                        <div class="notification_popup hidden">
                                            <p class="text"><?= __( 'Please delete all levels of cache after activating this option.',
                                                    'shortpixel-adaptive-images' ); ?></p>
                                            <input type="button" class="button blue_link" value="<?= __( 'Activate Option', 'shortpixel-adaptive-images' ); ?>" />
                                        </div>
                                        <div class="children-wrap<?= $lqip ? '' : ' hidden' ?>" data-parent="lqip">
                                            <div class="shortpixel_radio_btns">
                                                <input id="cron" type="radio" name="process_way" data-type="string" value="cron" <?php checked( 'cron', $process_way, true ); ?>>
                                                <label for="cron" title="<?= __( 'Use WP Cron', 'shortpixel-adaptive-images' ); ?>">
                                                    <span>CRON</span>
                                                </label>
                                                <input id="instant" type="radio" name="process_way" data-type="string" value="instant" <?php checked( 'instant', $process_way, true ); ?>>
                                                <label for="instant" title="<?= __( 'Use AJAX calls', 'shortpixel-adaptive-images' ); ?>">
                                                    <span>INSTANT</span>
                                                </label>
                                            </div>
                                            <p class="description <?= $process_way === 'cron' ? '' : 'hidden'; ?>" data-explanation="cron"><?= __( 'Use WP Cron to create the LQIPs asynchronously and reduce the number of JavaScript calls.', 'shortpixel-adaptive-images' ); ?></p>
                                            <p class="description <?= $process_way === 'instant' ? '' : 'hidden'; ?>" data-explanation="instant"><?= __( 'EXPERIMENTAL: Use this if you have WP Cron deactivated.',
                                                    'shortpixel-adaptive-images' ); ?></p>
                                        </div>

                                        <div class="children-wrap<?= $lqip ? '' : ' hidden' ?>" data-parent="lqip">
                                            <button class="bordered_link" type="button" data-action="clear lqip cache"><?= __( 'Clear LQIP cache', 'shortpixel-adaptive-images' ); ?></button>
                                            <p class="description"><?= __( 'If you have replaced some of the images but kept the same name, please clear the cache.', 'shortpixel-adaptive-images' ); ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'Native lazy-loading', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/428-native-lazy-loading-on-wordpress-5-5-and-spai-compatibility"></span></div>
                                            <input
                                                id="native_lazy"
                                                type="checkbox"
                                                name="native_lazy"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                            <?php checked( true, $options->settings_behaviour_nativeLazy, true ); ?>/>
                                        <label for="native_lazy" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Use browser native lazy-loading if the browser supports it.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'When this option is enabled, the lazy-loading is handled directly by the browser and it usually has bigger thresholds than ShortPixel (which means more images will be loaded below the fold). Currently Firefox, Chrome and Edge are supporting native lazy-loading, among others. <strong>Safari does NOT support native lazy-loading!</strong>', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <?= __( 'Alter width and height', 'shortpixel-adaptive-images' ); ?>
                                    </th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/490-spai-alter-width-and-height"></span></div>
                                        <input
                                                id="alter2wh"
                                                type="checkbox"
                                                name="alter2wh"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                            <?php checked( true, $options->settings_behaviour_alter2wh, true ); ?>>
                                        <label for="alter2wh" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Modify the width and height attributes of the IMG tags to reflect the resized image size.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'Activate this if you have screen size issues with images that are handled by JavaScript after they are resized by ShortPixel.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <?= __( 'Sizes from postmeta', 'shortpixel-adaptive-images' ); ?>
                                    </th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/491-spai-sizes-from-postmeta"></span></div>
                                        <input
                                                id="sizespostmeta"
                                                type="checkbox"
                                                name="sizespostmeta"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                            <?php checked( true, $options->settings_behaviour_sizespostmeta, true ); ?>>
                                        <label for="sizespostmeta" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Check the postmeta table for image sizes, if the image is not present locally.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <div class="notification_popup hidden">
                                            <p class="text"><?= __( 'Please verify the database load after enabling this option, especially if you have a very large postmeta table! In some very specific cases, this option can lead to database performance issues.',
                                                    'shortpixel-adaptive-images' ); ?></p>
                                            <input type="button" class="button blue_link" value="<?= __( 'Activate Option', 'shortpixel-adaptive-images' ); ?>" />
                                        </div>
                                        <p class="description">
                                            <?= __( 'Activate this if you have offloaded images and you experience layout shifts while the page is rendered in the browser.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <?= __( 'Size breakpoints', 'shortpixel-adaptive-images' ); ?>
                                    </th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/507-spai-settings-size-breakpoints"></span></div>
                                        <input
                                                id="size_breakpoints"
                                                type="checkbox"
                                                name="size_breakpoints"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                            <?php checked( true, $options->settings_behaviour_sizeBreakpoints, true ); ?>>
                                        <label for="size_breakpoints" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Apply size breakpoints when resizing images.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <?php
                                        $size_breakpoints_base = $options->get( 'size_breakpoints_base', [ 'settings', 'behaviour', 50 ] );
                                        $size_breakpoints_base = is_int( $size_breakpoints_base ) && $size_breakpoints_base >= 0 ? $size_breakpoints_base : 50;
                                        ?>
                                        <label><?= __( 'Smallest resolution:', 'shortpixel-adaptive-images' ); ?>
                                            <input
                                                    type="number"
                                                    name="size_breakpoints_base"
                                                    data-type="int"
                                                    min="0"
                                                    max="500"
                                                    style="width:5em"
                                                    value="<?= $size_breakpoints_base; ?>"
                                            /> px.</label>
                                        <?php
                                        $size_breakpoints_rate = $options->get( 'size_breakpoints_rate', [ 'settings', 'behaviour', 10 ] );
                                        $size_breakpoints_rate = is_int( $size_breakpoints_rate ) && $size_breakpoints_rate >= 0 ? $size_breakpoints_rate : 10;
                                        ?>
                                        <label><?= __( 'Rounding factor:', 'shortpixel-adaptive-images' ); ?>
                                            <input
                                                    type="number"
                                                    name="size_breakpoints_rate"
                                                    data-type="int"
                                                    min="0"
                                                    max="100"
                                                    style="width:4em"
                                                    value="<?= $size_breakpoints_rate; ?>"
                                            /> %.</label>
                                        <p class="description">
                                            <?= __( 'The size breakpoints will make sure that if an image needs to be resized to two sizes that are very close, they will be resized to the same larger size, so there will be less cache misses on CDN, especially if you have less visited pages.', 'shortpixel-adaptive-images' ); ?>
                                            <?= __( 'The downside is that the images will not always be perfectly sized but in most of the cases a bit bigger than their display size.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <?= __( 'New AI engine', 'shortpixel-adaptive-images' ); ?>
                                    </th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/492-spai-use-new-ai-engine"></span></div>
                                        <input
                                                id="nojquery"
                                                type="checkbox"
                                                name="nojquery"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                            <?php checked( true, $options->settings_behaviour_nojquery > 0, true ); ?>>
                                        <label for="nojquery" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Use the new Adaptive Images engine', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'Use the new, improved, Adaptive Images replace engine that doesn\'t need jQuery and it\'s better optimized for new browsers.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                        <div class="notification_popup hidden">
                                            <p class="text"><?= __( 'Please check your site after activating this option, especially on older browsers. If you notice any issue, you can always revert by deactivating it.',
                                                    'shortpixel-adaptive-images' ); ?></p>
                                            <input type="button" class="button blue_link" value="<?= __( 'Activate Option', 'shortpixel-adaptive-images' ); ?>" />
                                        </div>
                                        <div class="children-wrap<?= $options->settings_behaviour_nojquery > 0 ? '' : ' hidden' ?>" data-parent="src">
                                            <input
                                                    id="sync_initial_parsing"
                                                    type="checkbox"
                                                    name="sync_initial_parsing"
                                                    class="tgl"
                                                    data-type="bool"
                                                    value="1"
                                                <?php checked( 1, $options->settings_behaviour_syncInitialParsing, true ); ?>
                                            />
                                            <label for="sync_initial_parsing" class="tgl-btn">
                                                <span></span>
                                                <?= __( 'Synchronous initial parsing', 'shortpixel-adaptive-images' ); ?>
                                            </label>
                                            <p class="description"><?= __( 'Enable this if you notice conflicts with dynamic content rendered by JS, or missing images.', 'shortpixel-adaptive-images' ); ?></p>
                                        </div>


                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'Top bar menu', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/540-settings-top-bar-menu"></span></div>
                                        <input
                                                id="topbarmenu"
                                                type="checkbox"
                                                name="topbarmenu"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                            <?php checked( 1, $options->settings_behaviour_topbarmenu, true ); ?>/>
                                        <label for="topbarmenu" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Display the ShortPixel menu in the admin top bar.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div id="areas" class="spai_settings_tab">
                            <table class="form-table">
                                <?php if(false) {  //TODO remove completely ?>
                                <tr>
                                    <th scope="row"><?= __( 'Serve SVGs through CDN', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/493-spai-serve-svgs-through-cdn"></span></div>
                                        <input
                                            id="serve_svg"
                                            type="checkbox"
                                            name="serve_svg"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
                                            <?php checked( true, $options->settings_areas_serveSvg, true ); ?>
                                        />
                                        <label for="serve_svg" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Serve the SVG images through the CDN', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'The SVG images are provided by the CDN without any changes.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <th scope="row"><?= __( 'Lazy-load the backgrounds', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/494-spai-lazy-load-the-backgrounds"></span></div>
                                        <input
                                            id="backgrounds_lazy_style"
                                            type="checkbox"
                                            name="backgrounds_lazy_style"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
                                            <?php checked( true, $options->settings_areas_backgroundsLazyStyle, true ); ?>
                                        />
                                        <label for="backgrounds_lazy_style" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Lazy-load the background images from inline STYLE blocks.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'This makes the backgrounds in inline STYLE blocks load after the browser capabilities and image sizes are determined. It also imposes a maximum width of the backgrounds equal to the viewport width.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                        <input
                                            id="backgrounds_lazy"
                                            type="checkbox"
                                            name="backgrounds_lazy"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
                                            <?php checked( true, $options->settings_areas_backgroundsLazy, true ); ?>
                                        />
                                        <label for="backgrounds_lazy" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Lazy-load and resize the background images in TAGS inline styles.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'This makes the backgrounds defined in the STYLE attribute of the tags load after the browser capabilities and image sizes are determined. It also imposes a maximum width of the backgrounds equal to the viewport width.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'Backgrounds maximum width', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/495-spai-backgrounds-maximum-width"></span></div>
                                        <?php
                                            $backgrounds_width = $options->settings_areas_backgroundsMaxWidth;
                                            $backgrounds_width = is_int( $backgrounds_width ) && $backgrounds_width >= 0 ? $backgrounds_width : 1920;
                                        ?>
                                        <label><input
                                                type="number"
                                                name="backgrounds_max_width"
                                                data-type="int"
                                                min="0"
                                                max="10000"
                                                value="<?= $backgrounds_width; ?>"
                                            /> px.</label>
                                        <p class="description">
                                            <?= __( 'Maximum width of the backgrounds, on all devices. Use to scale down huge backgrounds that are not lazy-loaded. Recommended value is 1920px.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'Replace in CSS files', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/273-how-does-the-replace-in-css-files-feature-work"></span></div>
                                        <input
                                            id="parse_css_files"
                                            type="checkbox" name="parse_css_files"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
                                            <?php checked( true, $options->settings_areas_parseCssFiles > 0, true ); ?>
                                        />
                                        <label for="parse_css_files" class="tgl-btn">
                                            <span></span>
                                            <?php
                                                $labelText = __( 'Replace background images and fonts in the CSS cached by %s.', 'shortpixel-adaptive-images' );
                                                if ( $integrations->has( 'wp-rocket', 'minify-css') && $integrations->has( 'wp-rocket', 'css-filter' ) ) {
                                                    echo sprintf($labelText , 'WP Rocket' ) . '<br>';
                                                }
                                                else if ( $integrations->has( 'swift-performance', 'merge_styles'  )
                                                    && $integrations->get ( 'swift-performance',  'plugin' )  === 'pro'
                                                ) {
                                                    echo sprintf( $labelText, 'Swift Performance ' . ucwords( $integrations->has( 'swift-performance', 'plugin' ) ) ) . '<br>';
                                                }
                                                else if ( $integrations->has( 'wp-fastest-cache' ) ) {
                                                    echo sprintf( $labelText, 'WP Fastest Cache' ) . '<br>';
                                                }
                                                else if ( $integrations->has( 'w3-total-cache' ) ) {
                                                    echo sprintf( $labelText, 'W3 Total Cache' ) . '<br>';
                                                }
                                                else if ( $integrations->has( 'litespeed-cache' ) ) {
                                                    echo sprintf( $labelText, 'LiteSpeed Cache' ) . '<br>';
                                                }
                                                else if ( $integrations->has( 'wp-optimize', 'enable_css' ) ) {
                                                    echo sprintf( $labelText, 'WP Optimize' ) . '<br>';
                                                }
                                                else {
                                                    _e( 'Minify the CSS, replace background image URLs and serve the CSS files from the CDN, as well as all the locally referred fonts.', 'shortpixel-adaptive-images' );
                                                    ?>
                                                <?php } ?>
                                        </label>
                                        <div class="notification_popup hidden">
                                            <p class="text"><?= __( 'Please check your website after activating this option. If you have any other plugins that minify or otherwise handle the CSS files, this could cause conflicts and the background images might not show.',
                                                    'shortpixel-adaptive-images' ); ?></p>
                                            <?php if(AccessControlHeaders::getServerName() === AccessControlHeaders::APACHE) { ?>
                                            <p class="text"><?= sprintf(__( 'ShortPixel will add the <strong>Header add Access-Control-Allow-Origin "%s"</strong> directive to your .htaccess file.',
                                                    'shortpixel-adaptive-images' ), '*'/*$cdn_domain*/); ?></p>
                                            <?php }
                                            else { ?>
                                                <p class="text"><?= __( 'Please make sure that your server sends the proper <strong>Access-Control-Allow-Origin</strong> header necessary for the initial redirect to origin from ShortPixel\'s CDN.',
                                                    'shortpixel-adaptive-images' )
                                                    . (AccessControlHeaders::getServerName() === AccessControlHeaders::NGINX
                                                        ? ' ' . __( 'You might need to add the following code to your Nginx config files:', 'shortpixel-adaptive-images' ) . '<pre>'
                                                            . AccessControlHeaders::getAllowOriginNginx()
                                                        : '<!-- SERVER: ' . getenv('SERVER_SOFTWARE') . ' -->'); ?>
                                                </pre></p>
                                            <?php } ?>
                                            <input type="button" class="button blue_link" value="<?= __( 'Activate Option', 'shortpixel-adaptive-images' ); ?>" />
                                        </div>
                                        <?php if ( ( !$integrations->has('wp-rocket', 'minify-css') || !$integrations->has('wp-rocket', 'css-filter' ) )
                                                  && !$integrations->has('wp-fastest-cache')
                                                  && !$integrations->has( 'w3-total-cache' )
                                                  && !$integrations->has( 'litespeed-cache' )
                                                  && !$integrations->has( 'wp-optimize', 'enable_css' )
                                                  && !(   $integrations->has('swift-performance', 'merge_styles')
                                                       && $integrations->get('swift-performance', 'plugin') === 'pro' ) ) {
                                            ?>
                                            <div class="children-wrap<?= $options->settings_areas_parseCssFiles > 0 ? '' : ' hidden' ?>">
                                                <?= __( 'Additional CSS domains: ', 'shortpixel-adaptive-images' ); ?>
                                                <input
                                                    type="text"
                                                    size="40"
                                                    name="css_domains"
                                                    data-type="string"
                                                    placeholder="mycdn.example.com, other.example.com"
                                                    value="<?= $options->settings_areas_cssDomains; ?>"
                                                />
                                            </div>
                                            <p class="description"><?php
                                                    _e( 'By default, only the CSS files served from the site domain are parsed to avoid unnecessary work on external CSS. If you serve the CSS files from another domain, please add it above. You can add multiple domains separated by commas.',
                                                        'shortpixel-adaptive-images' );
                                                ?></p>
                                            <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <?= __( 'Serve JS from the CDN', 'shortpixel-adaptive-images' ); ?>
                                    </th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/496-spai-serve-js-from-cdn"></span></div>
                                        <input
                                                id="js2cdn"
                                                type="checkbox"
                                                name="js2cdn"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                            <?php checked( true, $options->settings_areas_js2cdn, true ); ?>
                                        />
                                        <label for="js2cdn" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Minify and serve the JavaScript files from the CDN. The JS files from other domains are not affected by this option.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'Replace in the JS blocks', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/497-spai-replace-in-the-js-blocks"></span></div>
                                        <input
                                            id="parse_js"
                                            type="checkbox"
                                            name="parse_js"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
                                            <?php checked( true, $options->settings_areas_parseJs, true ); ?>
                                        />
                                        <label for="parse_js" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Parse JavaScript blocks to replace image URLs.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <div class="children-wrap<?= $options->settings_areas_parseJs ? '' : ' hidden' ?>">
                                            <input
                                                id="parse_js_lazy"
                                                type="checkbox"
                                                name="parse_js_lazy"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                                <?php checked( true, $options->settings_areas_parseJsLazy, true ); ?>
                                            />
                                            <label for="parse_js_lazy" class="tgl-btn">
                                                <span></span>
                                                <?= __( 'Lazy-load URLs in the JS blocks.', 'shortpixel-adaptive-images' ); ?>
                                            </label>
                                        </div>
                                        <p class="description">
                                            <?= __( 'Check this if you want the images that are provided in JavaScript blocks to be replaced. For some galleries or plugins the images won\'t appear resized unless you activate this option.',
                                                'shortpixel-adaptive-images' ); ?>
                                        </p>
                                        <div class="notification_popup hidden">
                                            <p class="text"><?= __( 'Please check your website after activating this option. Because it parses and replaces URLs inside the JavaScript blocks, it could, in rare cases, interfere with how the JavaScript works. Please also check if there are any errors in the browser console.',
                                                    'shortpixel-adaptive-images' ); ?></p>
                                            <input type="button" class="button blue_link" value="<?= __( 'Activate Option', 'shortpixel-adaptive-images' ); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'Replace in JSON data', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/280-what-does-this-json-ajax-option-in-your-settings"></span></div>
                                        <input
                                            id="parse_json"
                                            type="checkbox"
                                            name="parse_json"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
                                            <?php checked( true, $options->settings_areas_parseJson, true ); ?>
                                        />
                                        <label for="parse_json" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Also parse JSON AJAX calls to replace image URLs.', 'shortpixel-adaptive-images' ); ?>&nbsp;
                                        </label>
                                        <div class="children-wrap<?= $options->settings_areas_parseJson ? '' : ' hidden' ?>">
                                            <input
                                                id="parse_json_lazy"
                                                type="checkbox"
                                                name="parse_json_lazy"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                                <?php checked( true, $options->settings_areas_parseJsonLazy, true ); ?>
                                            />
                                            <label for="parse_json_lazy" class="tgl-btn">
                                                <span></span>
                                                <?= __( 'Lazy-load JSON URLs.', 'shortpixel-adaptive-images' ); ?>
                                            </label>
                                        </div>
                                        <p class="description clearfix">
                                            <?= __( 'Check this if you have specific pieces of content that are delivered by Javascript in JSON-encoded packages. Some galleries like the one in Thrive Architect, or posts grids with infinite scroll need that. Please note that in some cases, temporary CORS warnings could show up. <a href="https://shortpixel.com/knowledge-base/article/465-warning-access-to-image-has-been-blocked-by-cors-policy" target="_blank">Read more</a>.',
                                                'shortpixel-adaptive-images' ); ?>
                                        </p>
                                        <div class="notification_popup hidden">
                                            <p class="text"><?= __( 'Please check your website after activating this option. In rare cases, changing the URLs inside JSON blocks might interfere with how your site\'s JavaScript works.', 'shortpixel-adaptive-images' ); ?></p>
                                            <input type="button" class="button blue_link" value="<?= __( 'Activate Option', 'shortpixel-adaptive-images' ); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <?= __( 'Integrate with Lity', 'shortpixel-adaptive-images' ); ?>
                                    </th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/498-spai-integrate-with-lity"></span></div>
                                        <input
                                                id="lity"
                                                type="checkbox"
                                                name="lity"
                                                class="tgl"
                                                data-type="bool"
                                                value="1"
                                            <?php checked( true, $options->settings_areas_lity, true ); ?>>
                                        <label for="lity" class="tgl-btn">
                                            <span></span>
                                            <?= __( 'Integrate with the Lity lightbox.', 'shortpixel-adaptive-images' ); ?>
                                        </label>
                                        <p class="description">
                                            <?= __( 'Checks for the Lity lightbox component in the pages and replaces the images with properly resized ones. Activate this if your theme uses Lity.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div id="exclusions" class="spai_settings_tab">
                            <?php
                                $eager_selectors     = $options->settings_exclusions_eagerSelectors;
                                $no_resize_selectors = $options->settings_exclusions_noresizeSelectors;
                                $excluded_selectors  = $options->settings_exclusions_excludedSelectors;
                                $excluded_paths      = $options->settings_exclusions_excludedPaths;
                                $excluded_pages      = $options->settings_exclusions_excludedPages;

                                $split_selectors = [
                                    'eager_selectors'     => $controller->splitSelectors( $eager_selectors, ',' ),
                                    'no_resize_selectors' => $controller->splitSelectors( $no_resize_selectors, ',' ),
                                    'excluded_selectors'  => $controller->splitSelectors( $excluded_selectors, ',' ),
                                    'excluded_paths'      => $controller->splitSelectors( $excluded_paths, PHP_EOL ),
                                ];

                                $exclusion_limits = [
                                    'selectors' => 5,
                                    'paths'     => 10,
                                ];

                                $excluded_selectors_qty = count( $split_selectors[ 'eager_selectors' ] ) + count( $split_selectors[ 'no_resize_selectors' ] ) + count( $split_selectors[ 'excluded_selectors' ] );
                                $excluded_paths_qty     = count( $split_selectors[ 'excluded_paths' ] );
                            ?>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?= __( 'Excluded selectors', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <div class="spai-inline-help">
                                            <span class="dashicons dashicons-editor-help" title="Inline help"
                                                  data-link="https://shortpixel.com/knowledge-base/article/229-how-to-exclude-images-from-optimization-in-the-shortpixel-adaptive-images-plugin"></span></div>
                                        <p class="warning error-message<?= $excluded_selectors_qty <= $exclusion_limits[ 'selectors' ] ? ' hidden' : ''; ?>" data-limit="<?= $exclusion_limits[ 'selectors' ]; ?>">
                                            <?= str_replace( '{{QTY}}', $excluded_selectors_qty, __( 'You already have <span>{{QTY}}</span> selectors active. Please keep the number of exclusion selectors low for best performance.', 'shortpixel-adaptive-images' ) ); ?>
                                        </p>
                                        <p style="margin-bottom: 10px;">
                                            <?= __( 'You can also add the exclusions visually, using the Image Checker Tool.', 'shortpixel-adaptive-images' ); ?>
                                            <a href="https://shortpixel.com/knowledge-base/article/338-how-to-use-the-image-checker-tool" target="_blank"><?= __( 'Read more', 'shortpixel-adaptive-images' ); ?></a>.
                                        </p><p style="margin-bottom: 10px;">
                                            <?= __( 'Another method that doesn\'t involve <strong>Excluded selectors</strong>, is to add one of the following custom attributes to your IMG tags: <strong>data-spai-eager</strong>, <strong>data-spai-noresize</strong> or <strong>data-spai-excluded</strong>, depeding on which type of exclusion you need.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                        <div><label for="eager_selectors"><?= __( 'Don\'t lazy-load:', 'shortpixel-adaptive-images' ); ?></label><br>
                                            <textarea
                                                id="eager_selectors"
                                                name="eager_selectors"
                                                rows="5"
                                                data-type="string"
                                                data-exclusion-type="selectors"
                                                data-setting="exclusion"
                                            ><?= $eager_selectors; ?></textarea>
                                        </div>
                                        <div><label for="noresize_selectors"><?= __( 'Don\'t resize:', 'shortpixel-adaptive-images' ); ?></label><br>
                                            <textarea
                                                id="noresize_selectors"
                                                name="noresize_selectors"
                                                rows="5"
                                                data-type="string"
                                                data-exclusion-type="selectors"
                                                data-setting="exclusion"
                                            ><?= $no_resize_selectors; ?></textarea>
                                        </div>
                                        <div><label for="excluded_selectors"><?= __( 'Leave out completely:', 'shortpixel-adaptive-images' ); ?></label><br>
                                            <textarea
                                                id="excluded_selectors"
                                                name="excluded_selectors"
                                                rows="5"
                                                data-type="string"
                                                data-exclusion-type="selectors"
                                                data-setting="exclusion"
                                            ><?= $excluded_selectors; ?></textarea>
                                        </div>
                                        <p class="description">
                                            <?= __( 'Specify  a coma separated list of CSS selectors for images that should keep their original width on the page, or their original URLs.'
                                                . ' Needed for images that can, for example, zoom in on hover. Keep these lists as small as possible.'
                                                . ' Simple rules applied directly to the affected element (e.g. <strong>\'img.class\'</strong>, <strong>\'div#ID\'</strong> or <strong>\'li[itemprop=logo]\'</strong>) will be processed when the page is rendered.'
                                                . ' More complex rules (e.g. containing spaces or more HTML levels) will be processed using JavaScript and might have a slight delay, if the affected images are above the fold.',
                                                'shortpixel-adaptive-images' ); ?>
                                            <a href="https://shortpixel.com/knowledge-base/article/229-how-to-exclude-images-from-optimization-in-the-shortpixel-adaptive-images-plugin" target="_blank">
                                                <?= __( 'Read more', 'shortpixel-adaptive-images' ); ?>
                                            </a>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'Excluded URLs', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <p class="warning error-message<?= $excluded_paths_qty <= $exclusion_limits[ 'paths' ] ? ' hidden' : ''; ?>" data-limit="<?= $exclusion_limits[ 'paths' ]; ?>">
                                            <?= str_replace( '{{QTY}}', $excluded_paths_qty, __( 'You already have <span>{{QTY}}</span> URL exclusions active. Please keep the number of exclusion selectors low for best performance.', 'shortpixel-adaptive-images' ) ); ?>
                                        </p>
                                        <div>
                                            <textarea
                                                id="excluded_paths"
                                                name="excluded_paths"
                                                rows="5"
                                                data-type="string"
                                                data-exclusion-type="urls"
                                                data-setting="exclusion"
                                                data-separator="<?= PHP_EOL; ?>"
                                            ><?= $excluded_paths; ?></textarea>
                                        </div>
                                        <p class="description">
                                            <?= __( 'Exclude images based on their URL.'
                                                . ' The rule for gravatar.com is included by default because many sites use gravatar and these images cannot be optimized, '
                                                . 'but if you\'re sure your site doesn\'t include gravatar URLs, feel free to remove it. ', 'shortpixel-adaptive-images' ); ?>
                                            <a href="https://shortpixel.com/knowledge-base/article/229-how-to-exclude-images-from-optimization-in-the-shortpixel-adaptive-images-plugin" target="_blank">
                                                <?= __( 'Read more', 'shortpixel-adaptive-images' ); ?>
                                            </a>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?= __( 'Excluded pages', 'shortpixel-adaptive-images' ); ?></th>
                                    <td>
                                        <p class="warning error-message<?= $excluded_paths_qty <= $exclusion_limits[ 'paths' ] ? ' hidden' : ''; ?>" data-limit="<?= $exclusion_limits[ 'paths' ]; ?>">
                                            <?= str_replace( '{{QTY}}', $excluded_paths_qty, __( 'You already have <span>{{QTY}}</span> URL exclusions active. Please keep the number of exclusion selectors low for best performance.', 'shortpixel-adaptive-images' ) ); ?>
                                        </p>
                                        <div>
                                            <textarea
                                                    id="excluded_pages"
                                                    name="excluded_pages"
                                                    rows="5"
                                                    data-type="string"
                                                    data-exclusion-type="urls"
                                                    data-setting="exclusion"
                                                    data-separator="<?= PHP_EOL; ?>"
                                            ><?= $excluded_pages; ?></textarea>
                                        </div>
                                        <p class="description" style="margin-bottom:16px">
                                            <?= __( 'Exclude entire pages based on their URL, so that none of the images on these pages are optimized.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                        <p class="description">
                                            <?= __( 'For both <strong>Excluded URLs</strong> and <strong>Excluded pages</strong>:', 'shortpixel-adaptive-images' ); ?>
                                            <?= __( 'Specify a list of URL exclusion rules, one per line. An exclusion rule starts either with '
                                                    . '<strong>path:</strong> or with <strong>regex:</strong>. After the colon:', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                        <ul>
                                            <li>
                                                <i><?= __( 'If it\'s a <strong>regex:</strong> rule, you can specify a full regex <strong>between slashes</strong> (ex: /.*\.gif$/ excludes GIF images).', 'shortpixel-adaptive-images' ); ?></i>
                                            </li>
                                            <li>
                                                <i><?= __( 'If it\'s a <strong>path:</strong> rule, you can specify full URLs, '
                                                           . 'domain names like gravatar.com or paths like /my-custom-image-folder/.', 'shortpixel-adaptive-images' ); ?></i>
                                            </li>
                                            <li>
                                                <i><?= __( 'You can exclude just the domain by using a <strong>domain:</strong> rule, for example <strong>domain:cdninstagram.com</strong>.', 'shortpixel-adaptive-images' ); ?></i>
                                            </li>
                                        </ul>
                                        <p class="description">
                                            <?= __( ' You can test your regex online, for example here: <a href="https://regex101.com/" target="_blank">regex101.com</a>.', 'shortpixel-adaptive-images' ); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div id="simple" class="spai_settings_tab active">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?= __( 'Compression Level', 'shortpixel-adaptive-images' ); ?></th>
                                <td>
                                    <div class="spai-inline-help"><span class="dashicons dashicons-editor-help" title="Inline help" data-link="https://shortpixel.com/knowledge-base/article/11-lossy-glossy-or-lossless-which-one-is-the-best-for-me"></span></div>
				                    <?php
				                    $compression_level = $options->get( 'level', [ 'settings', 'compression' ], 'lossy' );
				                    ?>
                                    <div class="shortpixel_radio_btns" style="margin-right:10px">
                                        <input id="simple_lossy" type="radio" name="simple_level" data-type="string" value="lossy" <?php checked( 'lossy', $compression_level, true ); ?>>
                                        <label for="simple_lossy" title="<?= __( 'This is the recommended option in most cases, producing results that look the same as the original to the human eye.', 'shortpixel-adaptive-images' ); ?>">
                                            <span>Lossy</span>
                                        </label>
                                        <input id="simple_glossy" type="radio" name="simple_level" data-type="string" value="glossy" <?php checked( 'glossy', $compression_level, true ); ?>>
                                        <label for="simple_glossy" title="<?= __( 'Best option for photographers and other professionals that use very high quality images on their sites and want best compression while keeping the quality untouched.', 'shortpixel-adaptive-images' ); ?>">
                                            <span>Glossy</span>
                                        </label>
                                        <input id="simple_lossless" type="radio" name="simple_level" data-type="string" value="lossless" <?php checked( 'lossless', $compression_level, true ); ?>>
                                        <label
                                                for="simple_lossless"
                                                title="<?= __( 'Make sure not a single pixel looks different in the optimized image compared to the original. In some rare cases you will need to use this type of compression. Some technical drawings or images from vector graphics are possible situations.' ); ?>"
                                        >
                                            <span>Lossless</span>
                                        </label>
                                    </div>
				                    <?= sprintf(__( '<a href="%s" target="_blank">Run a few tests</a> to help you decide.', 'shortpixel-adaptive-images' ), apply_filters('spai_affiliate_link','https://shortpixel.com/online-image-compression')); ?>
                                    <p class="<?= $compression_level === 'lossy' ? '' : 'hidden'; ?>" data-explanation="lossy"><?=
					                    __( '<b>Lossy compression (recommended): </b>offers the best compression rate.</br> This is the recommended option for most users, producing results that look the same as the original to the human eye.',
						                    'shortpixel-adaptive-images' ); ?></p>
                                    <p class="<?= $compression_level === 'glossy' ? '' : 'hidden'; ?>" data-explanation="glossy"><?=
					                    __( '<b>Glossy compression: </b>creates images that are almost pixel-perfect identical to the originals.</br> Best option for photographers and other professionals that use very high quality images on their sites and want the best compression while keeping the quality untouched.',
						                    'shortpixel-adaptive-images' ); ?></p>
                                    <p class="<?= $compression_level === 'lossless' ? '' : 'hidden'; ?>" data-explanation="lossless"><?=
					                    __( '<b>Lossless compression: </b> the resulting image is pixel-identical to the original image.</br>Make sure not a single pixel looks different in the optimized image compared to the original.
										In some rare cases you will need to use this type of compression. Some technical drawings or images from vector graphics are possible situations.', 'shortpixel-adaptive-images' ); ?></p>
                                </td>
                            </tr>
                            <tr>
		                        <?php
		                        $ng_type_support = ( $options->settings_compression_pngToWebp || $options->settings_compression_jpgToWebp || $options->settings_compression_gifToWebp );
		                        $webp_support = $options->settings_compression_webp && $ng_type_support;


		                        if ( !$webp_support ) {
			                        $options->settings_compression_webp = false;
		                        }
		                        $include_label = __( 'Enable for <strong>%s</strong> images', 'shortpixel-adaptive-images' );
		                        ?>

                                <th scope="row"><?= __( 'Next-gen Images Support', 'shortpixel-adaptive-images' ); ?></th>
                                <td>
                                    <div class="spai-inline-help"><span class="dashicons dashicons-editor-help" title="Inline help" data-link="https://shortpixel.com/knowledge-base/article/482-spai-webp-support"></span></div>
			                        <input
                                            id="simple_webp"
                                            type="checkbox"
                                            name="simple_webp"
                                            class="tgl"
                                            data-type="bool"
                                            data-depender="true"
                                            value="1"
                                        <?= checked( 1, $webp_support, false ) ?> />
                                    <label for="simple_webp" class="tgl-btn">
                                        <span style="display:inline-block !important; float: none !important;margin: 1px 5px 1px 10px !important;"></span>
                                    </label>
                                    Serve the images in next-gen image formats to all the browsers that support them.
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?= __( 'Optimize the backgrounds', 'shortpixel-adaptive-images' ); ?></th>
                                <td>
                                    <div class="spai-inline-help">
                                        <span class="dashicons dashicons-editor-help" title="Inline help"
                                              data-link="https://shortpixel.com/knowledge-base/article/494-spai-lazy-load-the-backgrounds"></span></div>
                                    <input
                                            id="simple_optimize_backgrounds"
                                            type="checkbox"
                                            name="simple_optimize_backgrounds"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
				                        <?php checked( true, $options->settings_areas_parseCssFiles > 0, true ); ?>
                                    />
                                    <label for="simple_optimize_backgrounds" class="tgl-btn">
                                        <span></span>
				                        <?= __( 'Optimize and rescale the background images in CSS blocks and files', 'shortpixel-adaptive-images' ); ?>
                                    </label>
<!--                                    <p class="description">-->
<!--				                        --><?//= __( 'This makes the backgrounds in inline STYLE blocks load after the browser capabilities and image sizes are determined. It also imposes a maximum width of the backgrounds equal to the viewport width.', 'shortpixel-adaptive-images' ); ?>
<!--                                    </p>-->
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
			                        <?= __( 'Optimize JavaScript images', 'shortpixel-adaptive-images' ); ?>
                                </th>
                                <td>
                                    <div class="spai-inline-help">
                                        <span class="dashicons dashicons-editor-help" title="Inline help"
                                              data-link="https://shortpixel.com/knowledge-base/article/496-spai-serve-js-from-cdn"></span></div>
                                    <input
                                            id="simple_optimize_js_images"
                                            type="checkbox"
                                            name="simple_optimize_js_images"
                                            class="tgl"
                                            data-type="bool"
                                            value="1"
				                        <?php checked( true, $options->settings_areas_js2cdn, true ); ?>
                                    />
                                    <label for="simple_optimize_js_images" class="tgl-btn">
                                        <span></span>
				                        <?= __( 'Optimize JavaScript images', 'shortpixel-adaptive-images' ); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php } ?>
					<?php submit_button( '', 'blue_link', 'submit', true, $submit_button_attributes ); ?>
				</form>
                <?php if( !defined('SHORTPIXEL_NO_BANNER') || !SHORTPIXEL_NO_BANNER ): ?>
                    <section class='shortpixel-offer-wso'>
                        <span class="image">
                          <img src="<?= $controller->plugin_url .'assets/img/robo-wink.png' ?>" alt="ShortPixel" />
                        </span>
                        <span class="line"><h3>
                                ARE YOU CONCERNED WITH YOUR <span class="red">SITE SPEED?</span>
                          </h3>
                        </span>
                        <span class="line"><h3>
                           ALLOW ShortPixel SPECIALISTS TO FIND THE  SOLUTION FOR YOU.
                         </h3>
                        </span>
                        <span class="button-wrap">
                          <a href="https://wso.shortpixel.com/?utm_source=SPAI" target="_blank" class='banner-button' >Find out more</a>
                        </span>
                    </section>
                <?php endif; ?>
            </div>
			<div class="clear"></div>
		</div>
	</div>
