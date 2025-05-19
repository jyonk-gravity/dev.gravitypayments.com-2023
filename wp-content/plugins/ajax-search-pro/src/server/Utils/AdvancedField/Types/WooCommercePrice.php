<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;
use WPDRMS\ASP\Utils\Html;

class WooCommercePrice extends AbstractWooCommerceBase implements AdvancedFieldTypeInterface {
	private string $currency;
	private string $price_field;
	private string $regular_price_color;
	private string $sale_price_color;

	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		parent::__construct($result);
		$this->price_field         = $field_args['price_field'] ?? 'price_html';
		$this->currency            = function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : '';
		$this->regular_price_color =
			$field_args['regular_price_color'] ? "--regular-price-color:{$field_args['regular_price_color']};" : '';
		$this->sale_price_color    =
			$field_args['sale_price_color'] ? "--sale_price_color:{$field_args['sale_price_color']};" : '';
	}

	/**
	 * Add to cart
	 *
	 * ```
	 * {woo_price_html price_field=price_html}
	 * // price_field: string
	 * // regular_price_color: string
	 * // sale_price_color: string
	 * // quantity: 0|1
	 * // stock_only: 0|1
	 * ```
	 *
	 * @return string
	 */
	public function process(): string {
		if ( empty($this->product) ) {
			return '';
		}
		$p     = $this->product;
		$price = '';

		if ( $p->is_type('variable') && $this->price_field !== 'sale_price' ) {
			return $p->get_price_html();
		}

		switch ( $this->price_field ) {
			case 'regular_price':
				$price = $p->get_regular_price();
				break;
			case 'sale_price':
				if ( $p->is_on_sale() ) {
					$price = $p->get_sale_price();
				}
				break;
			case 'tax_price':
				$price = wc_get_price_including_tax($p);
				break;
			case 'price_html':
				$price = $p->get_price_html();
				break;
			default:
				$price = $p->get_price();
				break;
		}
		if ( $price !== '' ) {
			if ( $this->price_field !== 'price_html' ) {
				if ( $this->currency !== '' ) {
					$price = wc_price($price, array( 'currency' => $this->currency ));
				} else {
					$price = wc_price($price);
				}
			}
			if ( $this->price_field === 'price_html' ) {
				$price = Html::optimize(
					"<span style='{$this->regular_price_color}{$this->sale_price_color}' class='price'>$price</span>"
				);
			}
		}

		return $price;
	}
}
