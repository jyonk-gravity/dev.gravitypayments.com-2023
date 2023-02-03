<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SearchWP_Live_Search_Settings.
 *
 * The SearchWP Live Ajax Search settings.
 *
 * @since 1.7.0
 */
class SearchWP_Live_Search_Settings {

	/**
	 * Hooks.
	 *
	 * @since 1.7.0
	 */
	public function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );

        if ( SearchWP_Live_Search_Utils::is_searchwp_active() ) {
            $this->hooks_searchwp_enabled();
        } else {
	        $this->hooks_searchwp_disabled();
        }
	}

	/**
	 * Outputs the assets needed for the Settings page.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function assets() {

		if ( ! SearchWP_Live_Search_Utils::is_settings_page() ) {
			return;
		}

		wp_enqueue_style(
			'searchwp-live-search-styles',
			SEARCHWP_LIVE_SEARCH_PLUGIN_URL . 'assets/styles/admin/style.css',
			[],
			SEARCHWP_LIVE_SEARCH_VERSION
		);

		if ( ! SearchWP_Live_Search_Utils::is_searchwp_active() ) {
			// FontAwesome.
			wp_enqueue_style(
				'searchwp-font-awesome',
				SEARCHWP_LIVE_SEARCH_PLUGIN_URL . 'assets/vendor/fontawesome/css/font-awesome.min.css',
				null,
				'4.7.0'
			);
		}
	}

	/**
	 * Hooks if SearchWP is enabled.
	 *
	 * @since 1.7.0
	 */
	private function hooks_searchwp_enabled() {

		if ( ! SearchWP_Live_Search_Utils::is_settings_page() ) {
			return;
		}

        if ( class_exists( '\\SearchWP\\Admin\\NavTab' ) ) {
	        new \SearchWP\Admin\NavTab( [
		        'page'       => 'searchwp-live-search',
		        'tab'        => 'settings',
		        'label'      => esc_html__( 'Settings', 'searchwp-live-ajax-search' ),
		        'is_default' => true,
	        ] );
        }

		add_action( 'searchwp\settings\view',  [ $this, 'output' ] );
    }

	/**
	 * Hooks if SearchWP is disabled.
	 *
	 * @since 1.7.0
	 */
	private function hooks_searchwp_disabled() {

		add_action( 'in_admin_header', [ $this, 'header_searchwp_disabled' ], 100 );
		add_filter( 'admin_footer_text', [ $this, 'admin_footer_rate_us_searchwp_disabled' ], 1, 2 );
		add_filter( 'update_footer', [ $this, 'admin_footer_hide_wp_version_searchwp_disabled' ], PHP_INT_MAX );
    }

	/**
	 * Return array containing markup for all the appropriate settings fields.
	 *
	 * @since 1.7.0
	 *
	 * @return array
	 */
	private function get_settings_fields() {

		$fields   = [];
		$settings = searchwp_live_search()
            ->get( 'Settings_Api' )
            ->get_registered_settings();

		foreach ( $settings as $slug => $args ) {
			$fields[ $slug ] = $this->output_field( $args );
		}

		return apply_filters( 'searchwp_live_search_settings_fields', $fields );
	}

	/**
	 * Settings page output.
	 *
	 * @since 1.7.0
	 */
	public function output() {

		?>
		<div class="edit-post-meta-boxes-area">
			<div id="poststuff">
				<div class="meta-box-sortables">
					<div class="searchwp-settings">
						<div class="searchwp-live-search-settings">
                            <?php $this->output_form(); ?>
                        </div>
						<?php $this->output_after_settings(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Settings form output.
	 *
	 * @since 1.7.0
	 */
	private function output_form() {

		$fields = $this->get_settings_fields();

		?>
		<form class="searchwp-admin-settings-form" method="post">
			<input type="hidden" name="action" value="update-settings">
			<input type="hidden" name="view" value="settings">
			<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'searchwp-live-search-settings-nonce' ) ); ?>">
			<?php
			foreach ( $fields as $field ) {
				echo $field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
            <p class="submit">
                <button type="submit" class="searchwp-btn searchwp-btn-md searchwp-btn-accent" name="searchwp-live-search-settings-submit">
	                <?php esc_html_e( 'Save Settings', 'searchwp-live-ajax-search' ); ?>
                </button>
            </p>
		</form>
		<?php
	}

	/**
	 * Settings field output.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Field config.
	 *
	 * @return string
	 */
	private function output_field( $args ) {

		// Define default callback for this field type.
		$callback = ! empty( $args['type'] ) && method_exists( $this, 'output_field_' . $args['type'] ) ? [ $this, 'output_field_' . $args['type'] ] : '';

		if ( empty( $callback ) ) {
			return '';
		}

		// Custom row classes.
		$class = ! empty( $args['class'] ) ? SearchWP_Live_Search_Utils::sanitize_classes( (array) $args['class'], [ 'convert' => true ] ) : '';

		// Build standard field markup and return.
		$output = '<div class="searchwp-settings-row searchwp-settings-row-' . sanitize_html_class( $args['type'] ) . ' ' . $class . '" id="searchwp-setting-row-' . sanitize_key( $args['slug'] ) . '">';

		if ( ! empty( $args['name'] ) ) {
			$output .= '<span class="searchwp-setting-label">';
			$output .= '<label for="searchwp-setting-' . sanitize_key( $args['slug'] ) . '">' . esc_html( $args['name'] ) . '</label>';
			$output .= '</span>';
		}

		$output .= '<span class="searchwp-setting-field">';

		// Get returned markup from callback.
		$output .= call_user_func( $callback, $args );

		if ( ! empty( $args['desc_after'] ) ) {
			$output .= $args['desc_after'];
		}

		$output .= '</span>';

		$output .= '</div>';

		return $output;
	}

	/**
	 * Settings checkbox field output.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Field config.
	 *
	 * @return string
	 */
	private function output_field_checkbox( $args ) {

		$default = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
		$value   = searchwp_live_search()->get( 'Settings_Api' )->get( $args['slug'], $default );
		$slug    = sanitize_key( $args['slug'] );
		$checked = ! empty( $value ) ? checked( 1, $value, false ) : '';

        $output = '<div class="searchwp-setting-checkbox-container">';

		$output .= '<input type="checkbox" id="searchwp-setting-' . $slug . '" name="' . $slug . '" ' . $checked . '>';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Settings select field output.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Field config.
	 *
	 * @return string
	 */
	private function output_field_select( $args ) {

		$default = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
		$slug    = sanitize_key( $args['slug'] );
		$value   = searchwp_live_search()->get( 'Settings_Api' )->get( $slug, $default );
		$data    = isset( $args['data'] ) ? (array) $args['data'] : [];
		$attr    = isset( $args['attr'] ) ? (array) $args['attr'] : [];

		foreach ( $data as $name => $val ) {
			$data[ $name ] = 'data-' . sanitize_html_class( $name ) . '="' . esc_attr( $val ) . '"';
		}

		$data = implode( ' ', $data );
		$attr = implode( ' ', array_map( 'sanitize_html_class', $attr ) );

		$output = '<select id="searchwp-live-search-setting-' . $slug . '" name="' . $slug . '" ' . $data . $attr . '>';

		foreach ( $args['options'] as $option => $name ) {
			if ( empty( $args['selected'] ) ) {
				$selected = selected( $value, $option, false );
			} else {
				$selected = is_array( $args['selected'] ) && in_array( $option, $args['selected'], true ) ? 'selected' : '';
			}
			$output .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
		}

		$output .= '</select>';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
		}

		return $output;
	}

	/**
	 * Settings content field output.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Field config.
	 *
	 * @return string
	 */
	private function output_field_content( $args ) {

		return ! empty( $args['content'] ) ? $args['content'] : '';
	}

	/**
	 * Renders the header if SearchWP is disabled.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function header_searchwp_disabled() {

		if ( ! SearchWP_Live_Search_Utils::is_settings_page() ) {
			return;
		}

		?>
        <div class="searchwp-settings-header">
            <p class="searchwp-logo" title="SearchWP">
                <svg width="258" height="54" viewBox="0 0 258 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <mask id="searchwp-logo-path-1" fill="white">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.64822 10C3.68926 10 2.8667 10.6802 2.70237 11.625C2.17483 14.6579 1.1113 21.3405 0.999997 26.5C0.885929 31.7877 2.13398 39.917 2.71185 43.3644C2.87135 44.316 3.69656 45 4.6614 45H35.3455C36.3038 45 37.1251 44.3254 37.2902 43.3814C37.8738 40.042 39.1135 32.2614 39 27C38.8879 21.8054 37.8063 14.783 37.2804 11.6397C37.1211 10.6876 36.2951 10 35.3298 10H4.64822ZM7.37673 15C6.87334 15 6.44909 15.3729 6.39019 15.8728C6.15313 17.885 5.59328 22.9843 5.49997 27C5.40432 31.1165 6.43975 37.0664 6.84114 39.2007C6.92896 39.6676 7.33744 40 7.81258 40H32.1806C32.6554 40 33.0637 39.6681 33.1518 39.2015C33.555 37.068 34.5957 31.117 34.5 27C34.4067 22.9836 33.8419 17.883 33.6028 15.8717C33.5435 15.3722 33.1195 15 32.6165 15H7.37673Z"></path>
                    </mask>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.64822 10C3.68926 10 2.8667 10.6802 2.70237 11.625C2.17483 14.6579 1.1113 21.3405 0.999997 26.5C0.885929 31.7877 2.13398 39.917 2.71185 43.3644C2.87135 44.316 3.69656 45 4.6614 45H35.3455C36.3038 45 37.1251 44.3254 37.2902 43.3814C37.8738 40.042 39.1135 32.2614 39 27C38.8879 21.8054 37.8063 14.783 37.2804 11.6397C37.1211 10.6876 36.2951 10 35.3298 10H4.64822ZM7.37673 15C6.87334 15 6.44909 15.3729 6.39019 15.8728C6.15313 17.885 5.59328 22.9843 5.49997 27C5.40432 31.1165 6.43975 37.0664 6.84114 39.2007C6.92896 39.6676 7.33744 40 7.81258 40H32.1806C32.6554 40 33.0637 39.6681 33.1518 39.2015C33.555 37.068 34.5957 31.117 34.5 27C34.4067 22.9836 33.8419 17.883 33.6028 15.8717C33.5435 15.3722 33.1195 15 32.6165 15H7.37673Z" fill="#BFCDC2"></path>
                    <path d="M2.70237 11.625L3.68758 11.7963L3.68758 11.7963L2.70237 11.625ZM0.999997 26.5L1.99976 26.5216L0.999997 26.5ZM2.71185 43.3644L1.72561 43.5297L1.72561 43.5297L2.71185 43.3644ZM37.2902 43.3814L36.3051 43.2092L36.3051 43.2092L37.2902 43.3814ZM39 27L39.9998 26.9784L39.9998 26.9784L39 27ZM37.2804 11.6397L38.2667 11.4747L38.2667 11.4747L37.2804 11.6397ZM6.39019 15.8728L7.38332 15.9898L7.38332 15.9898L6.39019 15.8728ZM5.49997 27L4.50024 26.9768L4.50024 26.9768L5.49997 27ZM6.84114 39.2007L5.85837 39.3855L5.85837 39.3855L6.84114 39.2007ZM33.1518 39.2015L34.1344 39.3872L34.1344 39.3872L33.1518 39.2015ZM34.5 27L33.5003 27.0232L33.5003 27.0232L34.5 27ZM33.6028 15.8717L34.5959 15.7537L34.5959 15.7537L33.6028 15.8717ZM3.68758 11.7963C3.76712 11.339 4.16678 11 4.64822 11V9C3.21174 9 1.96627 10.0214 1.71716 11.4536L3.68758 11.7963ZM1.99976 26.5216C2.10928 21.445 3.16018 14.8285 3.68758 11.7963L1.71716 11.4536C1.18949 14.4874 0.113321 21.236 0.000229326 26.4784L1.99976 26.5216ZM3.69809 43.1991C3.11803 39.7386 1.88804 31.7004 1.99976 26.5216L0.000229326 26.4784C-0.116187 31.875 1.14994 40.0954 1.72561 43.5297L3.69809 43.1991ZM4.6614 44C4.17442 44 3.7751 43.6585 3.69809 43.1991L1.72561 43.5297C1.96761 44.9735 3.2187 46 4.6614 46V44ZM35.3455 44H4.6614V46H35.3455V44ZM36.3051 43.2092C36.2257 43.6632 35.8296 44 35.3455 44V46C36.778 46 38.0246 44.9875 38.2752 43.5535L36.3051 43.2092ZM38.0002 27.0216C38.1113 32.1712 36.8906 39.8593 36.3051 43.2092L38.2752 43.5535C38.8571 40.2247 40.1157 32.3516 39.9998 26.9784L38.0002 27.0216ZM36.2941 11.8047C36.8203 14.9499 37.8899 21.9075 38.0002 27.0216L39.9998 26.9784C39.886 21.7034 38.7923 14.6161 38.2667 11.4747L36.2941 11.8047ZM35.3298 11C35.8151 11 36.2169 11.343 36.2941 11.8047L38.2667 11.4747C38.0254 10.0322 36.7751 9 35.3298 9V11ZM4.64822 11H35.3298V9H4.64822V11ZM7.38332 15.9898C7.38336 15.9896 7.3833 15.9902 7.38294 15.9913C7.38259 15.9924 7.3821 15.9936 7.38151 15.9947C7.38035 15.9969 7.37924 15.998 7.37868 15.9985C7.37814 15.999 7.37752 15.9994 7.3767 15.9997C7.37546 16.0002 7.37504 16 7.37673 16V14C6.3724 14 5.516 14.7463 5.39706 15.7558L7.38332 15.9898ZM6.4997 27.0232C6.59182 23.059 7.14639 18.0009 7.38332 15.9898L5.39706 15.7558C5.15987 17.7691 4.59475 22.9096 4.50024 26.9768L6.4997 27.0232ZM7.82391 39.0158C7.4205 36.8708 6.40684 31.0197 6.4997 27.0232L4.50024 26.9768C4.4018 31.2132 5.45901 37.262 5.85837 39.3855L7.82391 39.0158ZM7.81258 39C7.81089 39 7.81113 38.9998 7.81236 39.0002C7.81328 39.0006 7.81444 39.0011 7.81575 39.0022C7.81709 39.0033 7.81896 39.0052 7.82076 39.0082C7.82278 39.0116 7.82369 39.0147 7.82391 39.0158L5.85837 39.3855C6.03687 40.3346 6.8661 41 7.81258 41V39ZM32.1806 39H7.81258V41H32.1806V39ZM32.1692 39.0158C32.1694 39.0146 32.1704 39.0116 32.1724 39.0082C32.1742 39.0052 32.176 39.0033 32.1774 39.0022C32.1787 39.0012 32.1798 39.0006 32.1808 39.0002C32.182 38.9998 32.1823 39 32.1806 39V41C33.1264 41 33.9552 40.3355 34.1344 39.3872L32.1692 39.0158ZM33.5003 27.0232C33.5931 31.0198 32.5744 36.8715 32.1692 39.0158L34.1344 39.3872C34.5356 37.2645 35.5982 31.2143 35.4997 26.9768L33.5003 27.0232ZM32.6098 15.9897C32.8487 17.9999 33.4081 23.0588 33.5003 27.0232L35.4997 26.9768C35.4052 22.9083 34.835 17.7661 34.5959 15.7537L32.6098 15.9897ZM32.6165 16C32.6182 16 32.6178 16.0002 32.6166 15.9997C32.6157 15.9994 32.6151 15.999 32.6145 15.9985C32.614 15.998 32.6128 15.9968 32.6117 15.9946C32.6111 15.9935 32.6106 15.9923 32.6102 15.9912C32.6099 15.99 32.6098 15.9895 32.6098 15.9897L34.5959 15.7537C34.476 14.745 33.6199 14 32.6165 14V16ZM7.37673 16H32.6165V14H7.37673V16Z" fill="#839788" mask="url(#searchwp-logo-path-1)"></path>
                    <path d="M9.5 23.0986C9.5 18.2201 10.032 13.6522 10.9582 9.72544C11.0451 9.35737 11.2336 9.14094 11.4843 8.99615C11.7552 8.8397 12.1209 8.75586 12.5574 8.72296C12.9882 8.69049 13.4395 8.71017 13.8729 8.72906L13.8812 8.72942C13.8932 8.72995 13.9052 8.73047 13.9173 8.731C14.3109 8.74823 14.7412 8.76706 15.0539 8.70618C16.4728 8.42987 17.5874 8.49381 18.3833 8.62349C18.7824 8.68852 19.1049 8.77064 19.3539 8.83829C19.3934 8.84901 19.4329 8.85991 19.4714 8.87053C19.5491 8.89195 19.6227 8.91224 19.6834 8.92769C19.7612 8.94749 19.8849 8.97812 20 8.97812C20.1096 8.97812 20.2685 8.96387 20.453 8.94662C20.4915 8.94303 20.5321 8.93919 20.575 8.93513C20.7508 8.91852 20.9652 8.89826 21.2272 8.87626C21.8772 8.82167 22.8189 8.75654 24.1406 8.71516C24.3511 8.70857 24.6513 8.67218 24.9751 8.63295C25.1189 8.61552 25.2675 8.59752 25.4148 8.58133C25.9221 8.52557 26.4788 8.48166 27.0095 8.50767C27.5452 8.53392 28.0123 8.62995 28.3633 8.8228C28.6944 9.00471 28.9364 9.27866 29.0418 9.72544C29.968 13.6522 30.5 18.2201 30.5 23.0986C30.5 29.6909 29.5287 35.7116 27.9288 40.349C26.9506 43.1844 25.7887 44.0814 24.5899 44.3709C23.9542 44.5245 23.2621 44.5193 22.484 44.4665C22.2698 44.4519 22.0467 44.4334 21.8173 44.4144C21.2383 44.3665 20.619 44.3152 20 44.3152C19.381 44.3152 18.7617 44.3665 18.1827 44.4144C17.9533 44.4334 17.7302 44.4519 17.516 44.4665C16.7379 44.5193 16.0458 44.5245 15.4101 44.3709C14.2113 44.0814 13.0494 43.1844 12.0712 40.349C10.4713 35.7116 9.5 29.6909 9.5 23.0986Z" fill="white" stroke="#839788"></path>
                    <path d="M10.81 39.5H29.19C29.9607 39.5 30.6059 40.0839 30.6826 40.8507L31.2826 46.8507C31.3709 47.7338 30.6775 48.5 29.79 48.5H10.21C9.32254 48.5 8.62912 47.7338 8.71742 46.8507L9.31742 40.8507C9.3941 40.0839 10.0393 39.5 10.81 39.5Z" fill="#BFCDC2" stroke="#839788"></path>
                    <path d="M14.9962 1.5H24.9962C25.2723 1.5 25.4962 1.72386 25.4962 2V10.5H14.4962V2C14.4962 1.72386 14.72 1.5 14.9962 1.5Z" stroke="#839788" stroke-width="3" stroke-linejoin="round"></path>
                    <path d="M12.9962 5.5H26.9962C27.8246 5.5 28.4962 6.17157 28.4962 7V11.5H11.4962V7C11.4962 6.17157 12.1677 5.5 12.9962 5.5Z" fill="#BFCDC2" stroke="#839788"></path>
                    <path d="M9.99615 8.5H29.9962C30.8246 8.5 31.4962 9.17157 31.4962 10V14.5H8.49615V10C8.49615 9.17157 9.16773 8.5 9.99615 8.5Z" fill="#BFCDC2" stroke="#839788"></path>
                    <path d="M6.5086 47.5H33.4914C34.1611 47.5 34.7497 47.944 34.9337 48.5879L35.7908 51.5879C36.0646 52.5461 35.3451 53.5 34.3485 53.5H5.65146C4.65489 53.5 3.9354 52.5461 4.20917 51.5879L5.06632 48.5879C5.2503 47.944 5.83888 47.5 6.5086 47.5Z" fill="#BFCDC2" stroke="#839788"></path>
                    <path d="M9.24164 35.8023L20.3256 26.1427L31.5967 17.5118" stroke="#839788" stroke-width="2"></path>
                    <path d="M30.6903 35.7466L19.6433 26.1174L8.39868 17.508" stroke="#839788" stroke-width="2"></path>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M57.3987 34.9305C57.7699 40.9716 60.4093 44.7017 70.0595 44.7017C74.4722 44.7017 81.6067 43.9313 81.6067 35.7414C81.6067 29.9436 78.06 28.3218 73.8536 27.2271L67.2551 25.4837C65.523 25.0377 64.2446 24.4296 64.2446 22.3213C64.2446 20.0102 65.8117 19.4426 68.9047 19.4426C74.1835 19.4426 74.3072 21.0644 74.5959 23.497H80.6994C80.5757 18.3074 77.8538 14.334 69.1522 14.334C59.9969 14.334 58.0586 18.7128 58.0586 22.8889C58.0586 28.2002 61.2341 29.903 64.822 30.8355L71.9978 32.7411C74.5959 33.4304 75.4207 34.4034 75.4207 36.1874C75.4207 39.1471 73.1525 39.5931 70.0595 39.5931C64.2034 39.5931 63.7497 37.9713 63.5023 34.9305H57.3987ZM98.7626 37.9308C98.1852 39.5525 96.8656 39.958 94.6798 39.958C91.4631 39.958 90.4321 38.9849 90.2259 34.9305H104.866C104.866 25.6459 102.598 22.1591 94.9685 22.1591C85.9782 22.1591 84.3285 27.0244 84.3285 33.1466C84.3285 42.3096 87.9989 44.58 94.7211 44.58C100.618 44.58 103.918 42.8366 104.619 37.9308H98.7626ZM90.2671 31.3626C90.4733 27.9164 91.3394 26.7811 94.6798 26.7811C97.3192 26.7811 98.8863 27.4704 99.1338 31.3626H90.2671ZM113.568 29.1732C113.856 27.3487 114.434 26.7 117.279 26.7C119.96 26.7 120.991 27.2676 120.991 29.2543V30.5517L113.815 31.6464C110.64 32.133 107.506 33.9169 107.506 38.6606C107.506 42.3096 109.361 44.58 114.558 44.58C118.022 44.58 120.125 43.5664 121.238 42.4312V44.0529H126.723V28.4029C126.723 23.7403 123.548 22.1591 117.527 22.1591C111.671 22.1591 108.454 23.6592 108.165 29.1732H113.568ZM120.991 35.9036C120.991 38.5389 120.125 40.404 116.62 40.404C114.063 40.404 113.238 39.512 113.238 37.8902C113.238 35.6198 114.805 35.1738 116.991 34.8494L120.991 34.2007V35.9036ZM137.528 44.0529V36.1468C137.528 31.2815 137.941 27.6325 142.477 27.6325C143.632 27.6325 144.292 27.7542 144.292 27.7542V22.3618C144.292 22.3618 143.838 22.2402 142.807 22.2402C139.961 22.2402 138.518 23.1321 137.198 25.3621V22.6862H131.796V44.0529H137.528ZM166.809 29.903C166.231 24.6728 163.551 22.1591 157.282 22.1591C148.746 22.1591 146.684 26.7811 146.684 33.5925C146.684 39.7958 148.374 44.58 156.994 44.58C164.169 44.58 166.355 41.2554 166.809 36.4712H161.076C160.911 38.62 159.839 39.958 156.705 39.958C153.901 39.958 152.581 38.9038 152.581 33.3898C152.581 27.8758 153.901 26.7811 156.994 26.7811C159.922 26.7811 160.623 27.8353 161.076 29.903H166.768H166.809ZM176.541 14.9828H170.809V44.0529H176.541V32.3357C176.541 28.2813 177.325 26.7811 181.119 26.7811C184.79 26.7811 184.79 28.1596 184.79 33.0249V44.0529H190.522V32.0113C190.522 25.2404 189.202 22.1591 182.728 22.1591C180.501 22.1591 177.902 22.524 176.541 24.6323V14.9828ZM208.09 44.0529L212.75 22.9294L217.369 44.0529H223.844C227.184 34.3629 229.865 24.6728 231.762 14.9828H225.576C224.545 21.9564 222.937 28.93 220.833 35.9036L216.132 14.9828H209.41L204.873 35.7819C202.853 28.8489 201.286 21.9158 200.172 14.9828H193.739C195.636 24.6728 198.316 34.3629 201.657 44.0529H208.09ZM235.309 44.0529H241.412V33.4304H247.145C253.001 33.4304 257.991 32.3357 257.991 24.1052C257.991 15.5504 252.63 14.9828 246.485 14.9828H235.309V44.0529ZM245.619 19.9697C250.732 19.9697 251.805 20.5779 251.805 23.8619C251.805 27.9569 250.155 28.4434 245.248 28.4434H241.412V19.9697H245.619Z" fill="#839788"></path>
                </svg>
            </p>
        </div>
        <div class="searchwp-settings-subheader">
            <nav class="searchwp-settings-header-nav">
                <ul>
                    <li class="searchwp-settings-nav-tab-wrapper searchwp-settings-nav-tab-active postbox-wrapper searchwp-settings-nav-tab-searchwp-live-search-wrapper">
                        <a href="https://searchwp-plugin.local/wp-admin/admin.php?page=searchwp-live-search" class="searchwp-settings-nav-tab searchwp-settings-nav-tab-active postbox searchwp-settings-nav-tab-searchwp-live-search">
                            <span><?php esc_html_e( 'Settings', 'searchwp-live-ajax-search' ); ?></span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <hr class="wp-header-end">
		<?php
	}

	/**
	 * Renders the page content if SearchWP is disabled.
	 *
	 * @since 1.7.0
	 */
	public function page_searchwp_disabled() {

		?>
        <div class="searchwp-admin-wrap wrap">
            <div class="searchwp-settings-view">
				<?php searchwp_live_search()->get( 'Settings' )->output(); ?>
            </div>
        </div>
		<?php
	}

	/**
	 * After settings content output.
	 *
	 * @since 1.7.0
	 */
    private function output_after_settings() {

	    if ( SearchWP_Live_Search_Utils::is_searchwp_active() ) {
		    return;
	    }

	    ?>
        <div class="searchwp-settings-cta">
            <h5><?php esc_html_e( 'Get SearchWP Pro and Unlock all the Powerful Features', 'searchwp-live-ajax-search' ); ?></h5>
            <p><?php esc_html_e( 'Thank you for being a loyal SearchWP Live Ajax Search user. Upgrade to SearchWP Pro to unlock all the powerful features and experience why SearchWP is the best WordPress search plugin.', 'searchwp-live-ajax-search' ); ?></p>
            <p>
			    <?php
			    printf(
				    wp_kses( /* translators: %s - star icons. */
					    esc_html__( 'We know that you will truly love SearchWP Pro. It’s used on over 30,000 smart WordPress websites and is consistently rated 5-stars (%s) by our customers.', 'searchwp-live-ajax-search' ),
					    [
						    'i' => [
							    'class'       => [],
							    'aria-hidden' => [],
						    ],
					    ]
				    ),
				    str_repeat( '<i class="fa fa-star" aria-hidden="true"></i>', 5 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			    );
			    ?>
            </p>
            <h6><?php esc_html_e( 'Pro Features:', 'searchwp-live-ajax-search' ); ?></h6>
            <div class="list">
                <ul>
                    <li><?php esc_html_e( 'Search all custom field data', 'searchwp-live-ajax-search' ); ?></li>
                    <li><?php esc_html_e( 'Make ecommerce metadata discoverable in search results', 'searchwp-live-ajax-search' ); ?></li>
                    <li><?php esc_html_e( 'Search PDF, .doc, .txt and other static documents', 'searchwp-live-ajax-search' ); ?></li>
                    <li><?php esc_html_e( 'Search custom database tables and other custom content', 'searchwp-live-ajax-search' ); ?></li>
                    <li><?php esc_html_e( 'Make your media library (images, videos, etc.) searchable', 'searchwp-live-ajax-search' ); ?></li>
                </ul>
                <ul>
                    <li><?php esc_html_e( 'Search categories, tags and even custom taxonomies', 'searchwp-live-ajax-search' ); ?></li>
                    <li><?php esc_html_e( 'Easy integration with all WordPress themes and page builders', 'searchwp-live-ajax-search' ); ?></li>
                    <li><?php esc_html_e( 'Advanced search metrics and insights on visitor activity', 'searchwp-live-ajax-search' ); ?></li>
                    <li><?php esc_html_e( 'Multiple custom search engines for different types of content', 'searchwp-live-ajax-search' ); ?></li>
                    <li><?php esc_html_e( 'WooCommerce & Easy Digital Downloads support', 'searchwp-live-ajax-search' ); ?></li>
                </ul>
            </div>
            <p><a href="https://searchwp.com/?utm_source=WordPress&utm_medium=Settings+Upgrade+Bottom+Link&utm_campaign=Live+Ajax+Search&utm_content=Get+SearchWP+Pro+Today+and+Unlock+all+the+Powerful+Features" target="_blank" rel="noopener noreferrer" title="<?php esc_html_e( 'Get SearchWP Pro Today', 'searchwp-live-ajax-search' ); ?>"><?php esc_html_e( 'Get SearchWP Pro Today and Unlock all the Powerful Features', 'searchwp-live-ajax-search' ); ?> &raquo;</a></p>
            <p>
	            <?php
	            echo wp_kses(
		            __( '<strong>Bonus:</strong> SearchWP Live Ajax Search users get <span class="green">50% off the regular price</span>, automatically applied at checkout!', 'searchwp-live-ajax-search' ),
		            [
			            'strong' => [],
			            'span'   => [
				            'class' => [],
			            ],
		            ]
	            );
	            ?>
            </p>
        </div>
	    <?php
    }

	/**
	 * When user is on a SearchWP related admin page, display footer text
	 * that graciously asks them to rate us.
	 *
	 * @since 1.7.0
	 *
	 * @param string $text Footer text.
	 *
	 * @return string
	 */
	public function admin_footer_rate_us_searchwp_disabled( $text ) {

		global $current_screen;

		if ( empty( $current_screen->id ) || strpos( $current_screen->id, 'searchwp-live-search' ) === false ) {
			return $text;
		}

		$url = 'https://wordpress.org/support/plugin/searchwp-live-ajax-search/reviews/?filter=5#new-post';

		return sprintf(
			wp_kses( /* translators: $1$s - SearchWP plugin name; $2$s - WP.org review link; $3$s - WP.org review link. */
				__( 'Please rate %1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%3$s" target="_blank" rel="noopener">WordPress.org</a> to help us spread the word. Thank you from the SearchWP team!', 'searchwp-live-ajax-search' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			'<strong>SearchWP Live Ajax Search</strong>',
			$url,
			$url
		);
	}

	/**
	 * Hide the wp-admin area "Version x.x" in footer on SearchWP pages.
	 *
	 * @since 1.7.0
	 *
	 * @param string $text Default "Version x.x" or "Get Version x.x" text.
	 *
	 * @return string
	 */
	public function admin_footer_hide_wp_version_searchwp_disabled( $text ) {

		// Reset text if we're not on a SearchWP screen or page.
		if ( SearchWP_Live_Search_Utils::is_settings_page() ) {
			return '';
		}

		return $text;
	}

	/**
	 * Output "Did you know" block.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	public static function get_dyk_block_output() {

		if ( SearchWP_Live_Search_Utils::is_searchwp_active() ) {
			return '';
		}

		ob_start();

		?>
        <div class="searchwp-settings-dyk">
            <h5><?php esc_html_e( 'Did You Know?', 'searchwp-live-ajax-search' ); ?></h5>
            <p>
	            <?php
	            echo wp_kses(
		            __( 'By default, WordPress doesn’t make all your content searchable. <strong><em>That’s frustrating</em></strong>, because it leaves your visitors unable to find what they are looking for!', 'searchwp-live-ajax-search' ),
		            [
			            'strong' => [],
			            'em'     => [],
		            ]
	            );
	            ?>
            </p>
            <p><?php esc_html_e( 'With SearchWP Pro, you can overcome this obstacle and deliver the best, most relevant search results based on all your content, such as custom fields, ecommerce data, categories, PDF documents, rich media and more!', 'searchwp-live-ajax-search' ); ?></p>
            <p><a href="https://searchwp.com/?utm_source=WordPress&utm_medium=Settings+Did+You+Know+Upgrade+Link&utm_campaign=Live+Ajax+Search&utm_content=Get+SearchWP+Pro+Today" target="_blank" rel="noopener noreferrer" title="<?php esc_html_e( 'Get SearchWP Pro Today', 'searchwp-live-ajax-search' ); ?>"><?php esc_html_e( 'Get SearchWP Pro Today', 'searchwp-live-ajax-search' ); ?> &raquo;</a></p>
            <p>
	            <?php
	            echo wp_kses(
		            __( '<strong>Bonus:</strong> SearchWP Live Ajax Search users get <span class="green">50% off the regular price</span>, automatically applied at checkout!', 'searchwp-live-ajax-search' ),
		            [
			            'strong' => [],
			            'span'   => [
				            'class' => [],
			            ],
		            ]
	            );
	            ?>
            </p>
        </div>
		<?php

		return ob_get_clean();
	}
}
