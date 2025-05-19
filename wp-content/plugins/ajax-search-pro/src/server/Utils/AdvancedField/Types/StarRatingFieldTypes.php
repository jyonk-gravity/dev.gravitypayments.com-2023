<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;
use WPDRMS\ASP\Utils\Html;

class StarRatingFieldTypes implements AdvancedFieldTypeInterface {
	private string $star_color;
	private string $star_bg_color;
	private string $star_size;
	private int $max_rating;
	private string $average;

	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		if ( isset($field_args['rating_field'], $result) ) {
			$field_value = get_post_meta($result->id, $field_args['rating_field'], true);
			if ( $field_value !== false ) {
				$this->average = number_format( floatval($field_value), 2 );
			}
		}
		$this->star_color    = isset($field_args['star_color']) ? "--color: {$field_args['star_color']};" : '';
		$this->star_bg_color = isset($field_args['star_bg_color']) ? "--bg-color: {$field_args['star_bg_color']};" : '';
		$this->star_size     = isset($field_args['star_size']) ? "--size: {$field_args['star_size']};" : '';
		$this->max_rating    = max(( $field_args['max_rating'] ?? 5 ), 1);
	}

	/**
	 * Star Rating HTML
	 *
	 * @return string
	 */
	public function process(): string {
		if ( isset($this->average) ) {
			return Html::optimize(
				'<span class="average-rating" 
			style="--percent: calc(' . $this->average . '/'.$this->max_rating.'*100%);' . $this->star_color . $this->star_bg_color . $this->star_size . '"
			data-rating="' . $this->average . '"
			title="' . sprintf(__('Rated %s out of 5', 'woocommerce'), $this->average) . '">★★★★★</span>'
			);
		}

		return '';
	}
}
