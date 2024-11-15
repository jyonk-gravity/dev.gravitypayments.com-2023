<?php

	namespace ShortPixel\AI\Page;

	class Constants {
		private static $instance;

		/**
		 * @var \ShortPixelAI
		 */
		private $ctrl = null;

		public $onBoarding;

		/**
		 * Single ton implementation
		 *
		 * @param \ShortPixelAI $controller
		 *
		 * @return \ShortPixel\AI\Page\Constants
		 */
		public static function _( $controller = null ) {
			return self::$instance instanceof self ? self::$instance : new self( $controller );
		}

		public function renderSocialBlock( $arguments = [] ) {
			echo $this->getSocialBlock( $arguments );
		}

		public function getSocialBlock( $arguments = [] ) {
			$socials = [
				'twitter'  => [
					'link'   => 'https://twitter.com/intent/tweet?text=' . urlencode( sprintf(__( '%s I\'ve just instantly optimized all of the images from my website with the ShortPixel Adaptive Images plugin. Truly a magical experience! Check it out %s',
							'shortpixel-adaptive-images' ), '✨', '👇' ) ) . '&hashtags=shortpixel,wordpress' . '&url=' . urlencode( 'https://wordpress.org/plugins/shortpixel-adaptive-images' ),
					'action' => __( 'Tweet', 'shortpixel-adaptive-images' ),
				],
				'facebook' => [
					'link'   => 'https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fwww.facebook.com%2FShortPixel&display=popup&ref=plugin&src=like&kid_directed_site=0',
					'action' => __( 'Share', 'shortpixel-adaptive-images' ),
				],
			];

			$socials = array_merge( $socials, $arguments );

			// templates
			$block  = '<div class="socials-block"><div class="message-wrap">' . __( 'Had a magical moment? You can help more people experience it too by sharing it!', 'shortpixel-adaptive-images' ) . '</div><div class="buttons-wrap clearfix">{{ BUTTONS }}</div></div>';
			$button = '<a href="{{ LINK }}" data-social="{{ SOCIAL }}">{{ ACTION }}</a>';

			$buttons = [];

			foreach ( $socials as $social => $data ) {
				$buttons[] = str_replace( [ '{{ LINK }}', '{{ SOCIAL }}', '{{ ACTION }}' ], [ $data[ 'link' ], $social, $data[ 'action' ] ], $button );
			}

			return str_replace( '{{ BUTTONS }}', implode( '', $buttons ), $block );
		}

		/**
		 * Constants constructor.
		 *
		 * @param \ShortPixelAI $controller
		 */
		private function __construct( $controller = null ) {
			if ( !isset( self::$instance ) || !self::$instance instanceof self ) {
				$this->ctrl     = $controller;
				self::$instance = $this;
			}

			$shortpixel_api_keys = [
				'ai' => $this->ctrl->options->settings_general_apiKey,
				'io' => get_option( 'wp-short-pixel-apiKey', null ),
			];

			$domain_status    = \ShortPixelDomainTools::get_domain_status( true );
			$domain_cdn_usage = \ShortPixelDomainTools::get_cdn_domain_usage( null, empty( $shortpixel_api_keys[ 'ai' ] ) ? $shortpixel_api_keys[ 'io' ] : $shortpixel_api_keys[ 'ai' ] );
			$fsuUrl = apply_filters('spai_affiliate_link', 'https://shortpixel.com/fsu');
			if(count(explode('/', $fsuUrl)) == 4) {
			    $fsuUrl .= '/af/LVENHYZ28044';
            }

			$not_associated_message = '<p>' . __( 'Get 5Gb more free traffic and 500Mb free monthly traffic with a ShortPixel account', 'shortpixel-adaptive-images' ) . '</p>
			<a href="'. $fsuUrl . '" class="bordered_link" target="_blank">' . __( 'Create an account', 'shortpixel-adaptive-images' ) . '</a>
			<a href="' . apply_filters('spai_affiliate_link','https://shortpixel.com/login/_/associated-domains') . '" class="bordered_link" target="_blank">' . __( 'I have an account, associate this domain', 'shortpixel-adaptive-images' ) . '</a>
			<br>';

			$associated_message = '<p>' . sprintf( __( 'Please enter the API key below to display detailed stats in the plugin settings. You can also skip this and check detailed credits info in your account on the site. <a href="%s" target="_blank"><strong>Login</strong></a>',
					'shortpixel-adaptive-images' ), apply_filters('spai_affiliate_link','https://shortpixel.com/login/_/dashboard') ) . '</p>' .
			                      '<div class="action-wrap"><label>' . __( 'API key:',
					'shortpixel-adaptive-images' ) . ' <input type="text" name="api_key" size="30" value="{{API KEY}}"/></label><button class="blue_link" data-action="save key">' . __( 'Save', 'shortpixel-adaptive-images' ) . '</button></div>';

			if ( $domain_status->HasAccount && !isset($domain_cdn_usage->quota) ) {
				$associated_message = str_replace( '{{API KEY}}', '', $associated_message );
			}
			else if ( $domain_status->HasAccount && isset($domain_cdn_usage->quota) ) {
				$associated_message = str_replace( '{{API KEY}}', empty( $shortpixel_api_keys[ 'ai' ] ) ? $shortpixel_api_keys[ 'io' ] : $shortpixel_api_keys[ 'ai' ], $associated_message );
			}
			else if ( !$domain_status->HasAccount && !empty( $shortpixel_api_keys[ 'io' ] ) ) {
				$not_associated_message = '<p>' . __( 'We have detected that you are using our other plugin <strong>ShortPixel Image Optimizer</strong>. Do you want to use the same account?' ) . '</p>' .
				                          '<button class="blue_link" data-action="use same account">' . __( 'OK, let\'s use the same', 'shortpixel-adaptive-images' ) . '</button>' .
				                          '<br>';
			}

			$this->onBoarding = [
				'titles'   => [
					__( 'Check', 'shortpixel-adaptive-images' ),
					__( 'Configure', 'shortpixel-adaptive-images' ),
					__( 'Measure', 'shortpixel-adaptive-images' ),
					__( 'Credits', 'shortpixel-adaptive-images' ),
				],
				'messages' => [
					'<p>' . sprintf( __( 'Clear all caches and <a href="%s" target="_blank">browse your website</a> to verify that all is looking good.
			Make sure you also verify pages that have sliders, galleries or otherwise complex display effects.',
						'shortpixel-adaptive-images' ), site_url() ) . '
			<a href="https://shortpixel.com/knowledge-base/article/240-is-shortpixel-adaptive-images-working-well-on-my-website" target="_blank">' . __( 'How to check?', 'shortpixel-adaptive-images' ) . '</a>
			</p>
			<br>
			<button class="dark_blue_link next_icon">' . __( 'Next', 'shortpixel-adaptive-images' ) . '</button>
			<a href="' . apply_filters('spai_affiliate_link','https://shortpixel.com/contact') . '" class="blue_link" target="_blank">' . __( 'Found an issue', 'shortpixel-adaptive-images' ) . '</a>
			<button class="blue_link fast-forward_link" data-action="go to settings">' . __( 'Fast-forward to Settings', 'shortpixel-adaptive-images' ) . '</a>',

					'<p>' . __( 'Configure your ShortPixel settings to get the maximum out of ShortPixel.', 'shortpixel-adaptive-images' ) . '</p>
			<button class="bordered_link" data-action="run front worker">' . __( 'Let ShortPixel check my homepage and recommend settings changes', 'shortpixel-adaptive-images' ) . '</button>
			<br>
			<button class="dark_blue_link next_icon">' . __( 'Next', 'shortpixel-adaptive-images' ) . '</button>
			<button class="blue_link fast-forward_link" data-action="go to settings">' . __( 'Fast-forward to Settings', 'shortpixel-adaptive-images' ) . '</a>',

					'<p>'
					. __( 'Press the buttons below to check, using GTMetrix, your site speed Before and After the plugin installation.',
						'shortpixel-adaptive-images' ) . '</p>
					<p>'
					. __( 'First, press the <strong>Before</strong> button, and the GTMetrix test page will open in a new tab where you need to press the <strong>"Test your site"</strong> button.', 'shortpixel-adaptive-images' ) . '</p>
					<p>'
					. __( 'Then press the <strong>After</strong> button. To allow the ShortPixel CDN to optimize and cache the images and thus deliver the best performance, '
                                              . '<strong>please wait for about a minute</strong> and then press the <strong>"Re-Test"</strong> button on the top right corner of this GTMetrix page.',
						'shortpixel-adaptive-images' ) . '</p>
			<a href="https://gtmetrix.com/?url=' . home_url() . '?PageSpeed=off" class="bordered_link" target="_blank">' . __( 'Before', 'shortpixel-adaptive-images' ) . '</a>
			<a href="https://gtmetrix.com/?url=' . home_url() . '" class="bordered_link" target="_blank">' . __( 'After', 'shortpixel-adaptive-images' ) . '</a>
			<br>
			<button class="dark_blue_link next_icon">' . __( 'Next', 'shortpixel-adaptive-images' ) . '</button>
			<button class="blue_link fast-forward_link" data-action="go to settings">' . __( 'Fast-forward to Settings', 'shortpixel-adaptive-images' ) . '</button>',

					( $domain_status->HasAccount ? $associated_message : $not_associated_message ) . '<button class="dark_blue_link done">' . __( 'Done', 'shortpixel-adaptive-images' ) . '</button>
			' . $this->getSocialBlock(),
				],
			];
		}
	}
