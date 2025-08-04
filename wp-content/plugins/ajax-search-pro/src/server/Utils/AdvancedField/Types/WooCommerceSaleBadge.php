<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;
use WPDRMS\ASP\Utils\Html;

class WooCommerceSaleBadge extends AbstractWooCommerceBase implements AdvancedFieldTypeInterface {
	private string $text;
	private string $style;
	private string $position;
	private string $font_size;
	private string $font_color;
	private string $background_color;

	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		parent::__construct($result);
		$this->text             = $field_args['text'] ?? 'Sale!';
		$this->style            = $field_args['style'] ?? 'box';
		$this->position         = $field_args['position'] ?? 'top-right';
		$this->font_size        =
			isset($field_args['font_size']) ? "--font-size:{$field_args['font_size']};" : '';
		$this->font_color       =
			isset($field_args['font_color']) ? "--font-color:{$field_args['font_color']};" : '';
		$this->background_color =
			isset($field_args['bg_color']) ? "--background-color:{$field_args['bg_color']};" : '';
	}

	/**
	 * Add to cart
	 *
	 * ```
	 * {woo_sale_badge_html style='box'}
	 * // font_size: string
	 * // font_color: string
	 * // bg_color: string
	 * // style: 'box'|'capsule'|'round'
	 * ```
	 *
	 * @return string
	 */
	public function process(): string {
		if ( empty($this->product) || !$this->product->is_on_sale() ) {
			return '';
		}

		return Html::optimize(
			"
<div style='" . esc_attr($this->font_color . $this->background_color . $this->font_size) . "' 
class='sale-badge sale-badge-" . esc_attr($this->style) . ' sale-badge-' . esc_attr($this->position) . "'>" . esc_html($this->text) . '</div>
'
		);
	}
}
