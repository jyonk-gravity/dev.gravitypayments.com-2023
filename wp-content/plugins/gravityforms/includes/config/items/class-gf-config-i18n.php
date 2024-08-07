<?php

namespace Gravity_Forms\Gravity_Forms\Config\Items;

use Gravity_Forms\Gravity_Forms\Config\GF_Config_Collection;
use Gravity_Forms\Gravity_Forms\Config\GF_Config;
use Gravity_Forms\Gravity_Forms\Config\GF_Configurator;

/**
 * Config items for Theme I18N
 *
 * @since 2.6
 */
class GF_Config_I18n extends GF_Config {

	protected $name               = 'gform_i18n';
	protected $script_to_localize = 'gform_gravityforms';

	/**
	 * Config data.
	 *
	 * @return array[]
	 */
	public function data() {
		return array(
			'datepicker' => array(
				'days'     => array(
					'monday'    => esc_html__( 'Mo', 'gravityforms' ),
					'tuesday'   => esc_html__( 'Tu', 'gravityforms' ),
					'wednesday' => esc_html__( 'We', 'gravityforms' ),
					'thursday'  => esc_html__( 'Th', 'gravityforms' ),
					'friday'    => esc_html__( 'Fr', 'gravityforms' ),
					'saturday'  => esc_html__( 'Sa', 'gravityforms' ),
					'sunday'    => esc_html__( 'Su', 'gravityforms' ),
				),
				'months'   => array(
					'january'   => esc_html__( 'January', 'gravityforms' ),
					'february'  => esc_html__( 'February', 'gravityforms' ),
					'march'     => esc_html__( 'March', 'gravityforms' ),
					'april'     => esc_html__( 'April', 'gravityforms' ),
					'may'       => esc_html_x('May', 'Full month name', 'gravityforms'),
					'june'      => esc_html__( 'June', 'gravityforms' ),
					'july'      => esc_html__( 'July', 'gravityforms' ),
					'august'    => esc_html__( 'August', 'gravityforms' ),
					'september' => esc_html__( 'September', 'gravityforms' ),
					'october'   => esc_html__( 'October', 'gravityforms' ),
					'november'  => esc_html__( 'November', 'gravityforms' ),
					'december'  => esc_html__( 'December', 'gravityforms' ),
				),
				'firstDay' => array(
					'value'   => absint( get_option( 'start_of_week' ) ),
					'default' => 1,
				),
				'iconText' => esc_html__( 'Select date', 'gravityforms' ),

			),
		);
	}
}
