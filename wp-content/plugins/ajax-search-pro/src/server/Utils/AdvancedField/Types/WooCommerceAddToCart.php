<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;
use WPDRMS\ASP\Utils\Html;

class WooCommerceAddToCart extends AbstractWooCommerceBase implements AdvancedFieldTypeInterface {
	private string $text_color;
	private string $button_color;
	private string $justification;
	private bool $display_quantity;
	private int $quantity;
	private bool $stock_only;

	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		parent::__construct($result);
		$this->text_color       = $field_args['text_color'] ?? '#FFF';
		$this->justification    = $field_args['justification'] ?? 'left';
		$this->button_color     = $field_args['button_color'] ?? '#6246d7';
		$this->display_quantity = $field_args['display_quantity'] ?? false;
		$this->quantity         = intval($field_args['quantity'] ?? 1);
		$this->stock_only       = $field_args['stock_only'] ?? true;
	}

	/**
	 * Add to cart
	 *
	 * ```
	 * {woo_add_to_cart_html display_quantity=0 quantity=1 stock_only=1}
	 * // button_color: string
	 * // text_color: string
	 * // display_quantity: 0|1
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

		if ( $this->stock_only && !$this->product->is_in_stock() ) {
			return '';
		}

		$is_variable      = $this->product->is_type( 'variable' );
		$display_quantity = $this->display_quantity && !$is_variable;
		$link             = !$is_variable ? get_permalink(wc_get_page_id('shop')) : $this->product->get_permalink();
		$ajax             = !$is_variable ? ' ajax-add-to-cart' : '';
		$text             = !$is_variable ? __('Add to cart', 'woocommerce') : __('Choose variation', 'woocommerce');
		ob_start();
		?>
		<div class="add-to-cart-container" style="justify-content: <?php echo esc_attr($this->justification); ?>">
			<?php if ( $display_quantity ) : ?>
				<input  type="number" step="1" min="1"
						max="99999"
						name="quantity"
						value="<?php echo $this->quantity; // @phpcs:ignore ?>"
						title="Quantity"
						class="add-to-cart-quantity" size="4" pattern="[0-9]*" inputmode="numeric" />
			<?php endif; ?>
			<a href="<?php echo esc_attr($link); ?>"
				style="
						background: <?php echo esc_attr($this->button_color); ?>;
						color: <?php echo esc_attr($this->text_color); ?>;
				"
				data-quantity="<?php echo $this->quantity; // @phpcs:ignore ?>"
				class="add-to-cart-button<?php echo esc_attr($ajax); ?><?php echo $is_variable ? ' add-to-cart-variable' : ''; ?>"
				data-product_id="<?php echo esc_attr($this->product->get_id()); ?>"
				data-product_sku="<?php echo esc_attr($this->product->get_sku()); ?>"
				rel="nofollow"><?php echo esc_html($text); ?></a>
		</div>
		<?php
		$output = ob_get_clean();
		return $output === false ? '' : Html::optimize($output);
	}
}
