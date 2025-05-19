<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;
use WPDRMS\ASP\Utils\Html;

class WooCommerceStockStatus extends AbstractWooCommerceBase implements AdvancedFieldTypeInterface {
	private string $display_on_statuses;
	private bool $display_stock;

	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		parent::__construct($result);
		$this->display_on_statuses = $field_args['display_on_statuses'] ?? 'all';
		$this->display_stock       = $field_args['display_stock'] ?? false;
	}


	/**
	 * Stock Status Field Usage
	 *
	 * ```
	 * {woo_stock_status_html display_stock=0 display_on_statuses='all'}
	 * // display_stock: 0|1
	 * // display_on_statuses: 'all' or comma sepearated 'instock,outofstock,onbackorder'
	 * ```
	 *
	 * @return string
	 */
	public function process(): string {
		if ( empty($this->product) ) {
			return '';
		}

		$stock_status      = $this->product->get_stock_status();
		$stock_status_html = '';
		$quantity_html     = '';
		if ( $stock_status === 'instock' && $this->displayOnStatus('instock') ) {
			if (
				$this->display_stock &&
				$this->product->is_in_stock() &&
				$this->product->managing_stock()
			) {
				$quantity = $this->product->get_stock_quantity();
				if ( $quantity !== '' ) {
					$quantity_html = "<span class='quantity'>($quantity)</span>";
				}
			}

			$stock_status_html = "<span class='in-stock'>" . __( 'In stock', 'woocommerce' ) . '</span>';
		} elseif ( $stock_status === 'outofstock' && $this->displayOnStatus('outofstock') ) {
			$stock_status_html = "<span class='out-of-stock'>" . __( 'Out of stock', 'woocommerce' ) . '</span>';
		} elseif ( $stock_status === 'onbackorder' && $this->displayOnStatus('onbackorder') ) {
			$stock_status_html = "<span class='on-backorder'>" . __( 'Available on backorder', 'woocommerce' ) . '</span>';
		}

		if ( $stock_status_html !== '' ) {
			return Html::optimize("<span class='stock'>$stock_status_html&nbsp;$quantity_html</span>");
		}

		return '';
	}

	private function displayOnStatus( string $status ): bool {
		return $this->display_on_statuses === 'all' || str_contains($this->display_on_statuses, $status);
	}
}
