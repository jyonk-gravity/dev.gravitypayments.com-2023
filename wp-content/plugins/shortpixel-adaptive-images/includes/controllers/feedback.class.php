<?php

	namespace ShortPixel\AI;

	use ShortPixelAI;

	class Feedback {
		/**
		 * @var \ShortPixel\AI\Feedback $instance
		 */
		private static $instance;

		/**
		 * @var \ShortPixelAI $ctrl AI Controller
		 */
		protected $controller;

		/**
		 * Single ton implementation
		 *
		 * @param \ShortPixelAI $controller
		 *
		 * @return \ShortPixel\AI\Feedback
		 */
		public static function _( $controller ) {
			return self::$instance instanceof self ? self::$instance : new self( $controller );
		}

		public static function send( $reason, $suggestions = null, $anonymous = false ) {
			if ( !is_string( $reason ) || empty( $reason ) ) {
				return false;
			}

			$wordpress = self::collectWordpressData( ShortPixelAI::is_beta() );

			$wordpress[ 'deactivated_plugin' ][ 'uninstall_reason' ]  = $reason;
			$wordpress[ 'deactivated_plugin' ][ 'uninstall_details' ] = '';

			if ( !empty( $suggestions ) ) {
				$wordpress[ 'deactivated_plugin' ][ 'uninstall_details' ] .= $suggestions;
			}

			if ( !$anonymous ) {
				$wordpress[ 'deactivated_plugin' ][ 'uninstall_details' ] .= ( empty( $wordpress[ 'deactivated_plugin' ][ 'uninstall_details' ] ) ? '' : PHP_EOL . PHP_EOL ) . 'Domain: ' . \ShortPixelDomainTools::get_site_domain();
			}

			$body = [
				'user'      => self::collectUserData( $anonymous ),
				'wordpress' => $wordpress,
			];

			$spai_key = Options::_()->settings_general_apiKey;

			if ( !empty( $spai_key ) && !!\ShortPixelDomainTools::get_cdn_domain_usage(null, $spai_key ) && !$anonymous ) {
				$body[ 'key' ] = $spai_key;
			}

            return Request::post( ShortPixelAI::SP_API . 'v2/feedback.php', true, [ 'body' => $body ] );
		}

		/**
		 * Method generates Feedback popup
		 */
		public function generatePopUp() {
			$plugin_data = get_plugin_data( SHORTPIXEL_AI_PLUGIN_FILE );

			?>
			<div class="deactivation-popup hidden" data-type="wrapper" data-slug="<?= $plugin_data[ 'TextDomain' ]; ?>">
				<div class="overlay">
					<div class="close"></div>
					<div class="body">
						<section class="title-wrap"><?= __( 'Sorry to see you go', 'shortpixel-adaptive-images' ); /*$plugin_data[ 'Name' ];*/ ?></section>
						<section class="messages-wrap">
							<p><?= __( 'Before you deactivate the plugin, would you quickly give us your reason for doing so?', 'shortpixel-adaptive-images' ); ?></p>
						</section>
						<section class="options-wrap">
                            <label>
                                <input type="radio" name="feedback" value="temp">
                                <?= __( 'Temporary deactivation', 'shortpixel-adaptive-images' ); ?>
                            </label>
							<label>
								<input type="radio" name="feedback" value="setup">
								<?= __( 'Set up is too difficult', 'shortpixel-adaptive-images' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="documentation">
								<?= __( 'Lack of documentation', 'shortpixel-adaptive-images' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="features">
								<?= __( 'Not the features I wanted', 'shortpixel-adaptive-images' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="better-plugin">
								<?= __( 'Found a better plugin', 'shortpixel-adaptive-images' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="incompatibility">
								<?= __( 'Incompatible with theme or plugin', 'shortpixel-adaptive-images' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="maintenance">
								<?= __( 'Other', 'shortpixel-adaptive-images' ); ?>
							</label>
						</section>
						<section class="messages-wrap hidden" data-feedback>
							<p class="hidden" data-feedback="setup"><?= __( 'What was the difficult part?', 'shortpixel-adaptive-images' ); ?></p>
							<p class="hidden" data-feedback="documentation"><?= __( 'What can we describe more?', 'shortpixel-adaptive-images' ); ?></p>
							<p class="hidden" data-feedback="features"><?= __( 'How could we improve?', 'shortpixel-adaptive-images' ); ?></p>
							<p class="hidden" data-feedback="better-plugin"><?= __( 'Can you mention it?', 'shortpixel-adaptive-images' ); ?></p>
							<p class="hidden" data-feedback="incompatibility"><?= __( 'With what plugin or theme is incompatible?', 'shortpixel-adaptive-images' ); ?></p>
							<p class="hidden" data-feedback="maintenance"><?= __( 'Please specify:', 'shortpixel-adaptive-images' ); ?></p>
						</section>
						<section class="options-wrap hidden" data-feedback>
							<label>
								<textarea name="suggestions" rows="2"></textarea>
							</label>
						</section>
						<section class="messages-wrap hidden" data-feedback>
							<p><?= __( 'Would you like to share your e-mail with us so that we can write you back?', 'shortpixel-adaptive-images' ); ?></p>
						</section>
						<section class="options-wrap hidden" data-feedback>
							<label>
								<input type="checkbox" name="anonymous" value="1">
								<?= __( 'No, I\'d like to stay anonymous', 'shortpixel-adaptive-images' ); ?>
							</label>
						</section>
						<section class="messages-wrap">
							<p><?= __( 'Please, choose one of the options presented below to decide what we should do with the plugin\'s settings.', 'shortpixel-adaptive-images' ); ?></p>
						</section>
						<section class="options-wrap">
							<label>
								<input type="radio" name="settings" value="keep" checked>
								<?= __( 'Keep plugin data as it is', 'shortpixel-adaptive-images' ); ?>
							</label>
							<label>
								<input type="radio" name="settings" value="remove">
								<?= __( 'Remove all plugin data and settings', 'shortpixel-adaptive-images' ); ?>
							</label>
                            <?php if(false) { ?>
							<label>
								<input type="radio" name="settings" value="revert">
								<?= __( 'Revert settings to the <strong>1.x.x</strong> version', 'shortpixel-adaptive-images' ); ?>
							</label>
                            <?php } ?>
						</section>
						<section class="buttons-wrap clearfix">
							<button class="dark_blue_link" data-action="deactivation"><?= __( 'Deactivate', 'shortpixel-adaptive-images' ); ?></button>
						</section>
					</div>
					<div class="scroll-down hidden">
						<div class="mouse">
							<div class="wheel"></div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		private static function collectWordpressData( $detailed = false ) {
			$current_plugin = get_plugin_data( SHORTPIXEL_AI_PLUGIN_FILE );

			// Plugin data
			$data = [
				'deactivated_plugin' => [
					'slug'    => $current_plugin[ 'TextDomain' ],
					'name'    => $current_plugin[ 'Name' ],
					'version' => $current_plugin[ 'Version' ],
					'author'  => $current_plugin[ 'AuthorName' ],
				],
			];

			if ( !!$detailed ) {
				$data[ 'locale' ]      = ( get_bloginfo( 'version' ) >= 4.7 ) ? get_user_locale() : get_locale();
				$data[ 'wp_version' ]  = get_bloginfo( 'version' );
				$data[ 'multisite' ]   = is_multisite();
				$data[ 'php_version' ] = PHP_VERSION;
				$data[ 'themes' ]      = self::getThemes();
				$data[ 'plugins' ]     = self::getPlugins();
			}

			return $data;
		}

		/**
		 * Get a list of installed plugins
		 */
		private static function getPlugins() {
			if ( !function_exists( 'get_plugins' ) ) {
				include ABSPATH . '/wp-admin/includes/plugin.php';
			}

			$plugins   = get_plugins();
			$option    = get_option( 'active_plugins', [] );
			$active    = [];
			$installed = [];
			foreach ( $plugins as $id => $info ) {
				if ( in_array( $id, $active ) ) {
					continue;
				}

				$id = explode( '/', $id );
				$id = ucwords( str_replace( '-', ' ', $id[ 0 ] ) );

				$installed[] = $id;
			}

			foreach ( $option as $id ) {
				$id = explode( '/', $id );
				$id = ucwords( str_replace( '-', ' ', $id[ 0 ] ) );

				$active[] = $id;
			}

			return [
				'installed' => $installed,
				'active'    => $active,
			];
		}

		/**
		 * Get current themes
		 *
		 * @return array
		 */
		private static function getThemes() {
			$theme = wp_get_theme();

			return [
				'installed' => self::getInstalledThemes(),
				'active'    => [
					'slug'    => get_stylesheet(),
					'name'    => $theme->get( 'Name' ),
					'version' => $theme->get( 'Version' ),
					'author'  => $theme->get( 'Author' ),
				],
			];
		}

		/**
		 * Get an array of installed themes
		 *
		 * @return array
		 */
		private static function getInstalledThemes() {
			$installed = wp_get_themes();
			$theme     = get_stylesheet();
			$data      = [];

			foreach ( $installed as $slug => $info ) {
				if ( $slug === $theme ) {
					continue;
				}

				$data[ $slug ] = [
					'slug'    => $slug,
					'name'    => $info->get( 'Name' ),
					'version' => $info->get( 'Version' ),
					'author'  => $info->get( 'Author' ),
				];
			}

			return $data;
		}

		/**
		 * Collect user data.
		 *
		 * @param bool $anonymous
		 *
		 * @return array
		 */
		private static function collectUserData( $anonymous ) {
			$user = wp_get_current_user();

			$return = [
				'email'      => '',
				'first_name' => '',
				'last_name'  => '',
				'domain'     => '',
			];

			if ( $user && !$anonymous ) {
				$return[ 'email' ]      = $user->user_email;
				$return[ 'first_name' ] = $user->first_name;
				$return[ 'last_name' ]  = $user->last_name;
				$return[ 'domain' ]     = \ShortPixelDomainTools::get_site_domain();
			}

			return $return;
		}

		/**
		 * Feedback constructor.
		 *
		 * @param \ShortPixelAI $controller
		 */
		private function __construct( $controller ) {
			if ( !isset( self::$instance ) || !self::$instance instanceof self ) {
				self::$instance = $this;
			}

			$this->controller = $controller;

			add_action( 'admin_footer-plugins.php', [ $this, 'generatePopUp' ] );
			add_action( 'wp_ajax_shortpixel_ai_handle_feedback_action', [ 'ShortPixel\AI\Feedback\Actions', 'handle' ] );
		}
	}
