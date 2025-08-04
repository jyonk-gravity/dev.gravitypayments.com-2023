<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;
use WPDRMS\ASP\Utils\Html;

class WooCommerceStarRating extends AbstractWooCommerceBase implements AdvancedFieldTypeInterface {
	private string $star_color;
	private string $star_bg_color;
	private string $star_size;

	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		parent::__construct($result);
		$this->star_color    = isset($field_args['star_color']) ? "--color: {$field_args['star_color']};" : '';
		$this->star_bg_color = isset($field_args['star_bg_color']) ? "--bg-color: {$field_args['star_bg_color']};" : '';
		$this->star_size     = isset($field_args['star_size']) ? "--size: {$field_args['star_size']};" : '';
	}

	/**
	 * Star Rating HTML
	 *
	 * @return string
	 */
	public function process(): string {
		if ( empty($this->product) ) {
			return '';
		}
		$average = $this->product->get_average_rating();

		if ( isset($average) ) {
			return Html::optimize(
				'<span class="average-rating" 
			style="--percent: calc(' . esc_attr($average) . '/5*100%);' . esc_attr($this->star_color . $this->star_bg_color . $this->star_size) . '"
			data-rating="' . esc_attr($average) . '"
			title="' . sprintf(esc_attr__('Rated %s out of 5', 'woocommerce'), $average) . '">★★★★★</span>'
			);
		}

		return '';
	}
}
