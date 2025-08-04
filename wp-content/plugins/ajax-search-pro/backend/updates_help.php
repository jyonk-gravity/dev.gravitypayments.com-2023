<?php
/* Prevent direct access */

use WPDRMS\ASP\Misc\PluginLicense;

defined('ABSPATH') or die("You can't access this file directly.");

$asp_locally_activated  = PluginLicense::isActivated();
$asp_remotely_activated = PluginLicense::isActivated(true);

if ( ASP_DEMO ) {
	$_POST = null;
}
?>
	<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/sidebar.css?v=' . ASP_CURR_VER; ?>" />
	<div id='wpdreams' class='asp-be asp_updates_help<?php echo isset($_COOKIE['asp-accessibility']) ? ' wd-accessible' : ''; ?>'>
		<?php do_action('asp_admin_notices'); ?>

		<!-- This forces custom Admin Notices location -->
		<div style="display:none;"><h2 style="display: none;"></h2></div>
		<!-- This forces custom Admin Notices location -->

		<div class="wpdreams-box" style="float: left;">
			<?php if ( wd_asp()->updates->needsUpdate(true) ) : ?>
				<?php wd_asp()->updates->printUpdateMessage(); ?>
			<?php endif; ?>
			<div class="wpd-half">
				<h3><?php echo __('Version status', 'ajax-search-pro'); ?></h3>
				<div class="item">
					<p><?php echo __('Installed version:', 'ajax-search-pro'); ?> <strong><?php echo ASP_CURR_VER_STRING; ?></strong></p>
					<p><?php echo __('Newest version: ', 'ajax-search-pro'); ?> <strong><?php echo wd_asp()->updates->getVersionString(); ?></strong></p>
					<p><?php echo __('Last update: ', 'ajax-search-pro'); ?> <strong><?php echo wd_asp()->updates->getLastUpdated(); ?></strong></p>
				</div>
				<h3><?php echo __('Support', 'ajax-search-pro'); ?></h3>
				<div class="item">
					<?php
					printf(
						__(
							'If you can\'t find the answer in the documentation or knowledge base, or if you are having other issues,
                feel free to <a href="%s" target="_blank">open a support ticket</a>.',
							'ajax-search-pro'
						),
						'https://wp-dreams.com/open-support-ticket-step-1/'
					);
					?>
				</div>
				<h3><?php echo __('Useful Resources', 'ajax-search-pro'); ?></h3>
				<div class="item">
					<ul>
						<li><a target="_blank" href="https://documentation.ajaxsearchpro.com/" title="Documentation"><?php echo __('Online Documentation', 'ajax-search-pro'); ?></a></li>
						<li><a target="_blank" href="https://knowledgebase.ajaxsearchpro.com/" title="Knowledge Base"><?php echo __('Knowledge base', 'ajax-search-pro'); ?></a></li>
						<li><a target="_blank" href="https://changelog.ajaxsearchpro.com/" title="Changelog"><?php echo __('Changelog', 'ajax-search-pro'); ?></a></li>
						<li><a target="_blank" href="https://documentation.ajaxsearchpro.com/plugin-updates/manual-updates"><?php echo __('How to manual update?', 'ajax-search-pro'); ?></a></li>
					</ul>
				</div>
			</div>
			<div class="wpd-half-last">
				<?php if ( ASP_DEMO == 0 ) : ?>
					<h3><?php echo __('License activation & Automatic updates', 'ajax-search-pro'); ?></h3>
					<div class="item<?php echo $asp_remotely_activated === false ? '' : ' hiddend'; ?>">
						<div class="asp_auto_update">
							<?php if ( $asp_locally_activated !== false ) : ?>
								<p class="noticeMsg">
									<?php echo __('Looks like this license was deactivated remotely or activated on a different site.', 'ajax-search-pro'); ?><br>
									<?php echo __('<strong>No worries!</strong> You can keep it that way, but this one will not recieve automatic updates, only stays verified.', 'ajax-search-pro'); ?>
								</p>
								<p>
									<?php echo __('To activate Automatic Updates, you can re-activate your license.', 'ajax-search-pro'); ?>
								</p>
							<?php else: ?>
							<p>
								<?php echo __('To activate Automatic Updates, please activate your license code with this site.', 'ajax-search-pro'); ?>
							</p>
							<?php endif; ?>
							<p>
								<?php echo vsprintf(
									__('Check <a href="%s" target="_blank">this documentation</a> to see where you can find your license key.', 'ajax-search-pro'),
									array("https://documentation.ajaxsearchpro.com/plugin-updates/automatic-updates/purchase-code")
								);
								?>
							</p>
							<label>
								<?php echo __('License key', 'ajax-search-pro'); ?>
							</label>
							<div class="errorMsg" style="display:none;"></div>
							<input type="text" name="asp_key" id="asp_key" value="">
							<input type="button" id="asp_activate" name="asp_activate" class="submit wd_button_blue" value="<?php echo esc_attr__('Activate', 'ajax-search-pro'); ?>">
							<span class="small-loading" style="display:none; vertical-align: middle;"></span>
							<?php if ( $asp_locally_activated === false ) : ?>
								<p class="infoMsg">
									<?php echo __('If you activated the plugin <b>with this site before</b>, and you see this activation form, just enter the purchase code again to re-activate.', 'ajax-search-pro'); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>
					<div class="item<?php echo $asp_locally_activated === false ? ' hiddend' : ''; ?> asp_auto_update">
						<p>
							<?php if ( $asp_remotely_activated !== false ) : ?>
								<?php echo __('License verified & auto-updates are activated for this site, with key: ', 'ajax-search-pro'); ?><br>
								<b><?php echo $asp_locally_activated; ?></b>
							<?php else: ?>
								<?php echo __('A license is verified locally on current site.', 'ajax-search-pro'); ?>
							<?php endif; ?><br>
						</p>
						<div class="errorMsg" style="display:none;"></div>
						<input type="button" class="submit wd_button_red" id="asp_deactivate" name="asp_deactivate" value="<?php echo esc_attr__('Remove', 'ajax-search-pro'); ?>">
						<span class="small-loading" style="display:none; vertical-align: middle;"></span>
						<p class="descMsg" style="text-align: left;margin-top: 10px;"><?php echo __('<b>NOTICE:</b> After removal there is a <b>30 minute</b> wait time until you can re-activate the same purchase code to prevent malicious activity.', 'ajax-search-pro'); ?></p>
					</div>
					<input type="hidden" id="asp_license_request_nonce" value="<?php echo wp_create_nonce( 'asp_license_request_nonce' ); ?>">
				<?php endif; ?>
				<div class="item">
					<h3><?php echo __("License Management"); ?></h3>
					<p>
						<?php
						echo vsprintf(
							__(
								'To see more details about your licenses, you can <a href="%s" target="_blank">login</a> to our support site.', 'ajax-search-pro'),
							array("https://wp-dreams.com/login")
						);
						?>
					</p>
					<p>
						<?php echo vsprintf(
							__("If you don't have an account yet, just create one and <a href='%s' target='_blank'>add</a> your existing licenses. They will be bound to that account.", 'ajax-search-pro'),
							array("https://wp-dreams.com/products/"));?>
					</p>
				</div>

			</div>
			<div class="clear"></div>
		</div>
		<?php require ASP_PATH . 'backend/sidebar.php'; ?>
		<div class="clear"></div>
	</div>
<?php
wp_enqueue_script(
	'wpd-backend-updates-help',
	plugin_dir_url(__FILE__) . 'settings/assets/updates_help.js',
	array(
		'jquery',
	),
	ASP_CURR_VER_STRING,
	true
);