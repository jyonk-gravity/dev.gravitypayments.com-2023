<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;
use WC_Product;

/**
 * Base class for WooCommerce fields with automated product check on constructor call
 */
abstract class AbstractWooCommerceBase {
	protected ?WC_Product $product;

	public function __construct( ?stdClass $result ) {
		$this->product = $this->getProduct( $result );
	}

	private function getProduct( ?stdClass $result ): ?WC_Product {
		if ( !function_exists('wc_get_product') ) {
			return null;
		}
		if ( is_null($result) || !isset($result->post_type) ) {
			return null;
		}
		if ( $result->post_type !== 'product' && $result->post_type !== 'product_variation' ) {
			return null;
		}

		$product = wc_get_product($result->id);
		if ( empty($product) ) {
			return null;
		}

		return $product;
	}
}
