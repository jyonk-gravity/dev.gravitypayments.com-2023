<?php foreach ( $filter->get() as $button ): ?>
<div class="<?php echo $button->container_class; ?>">
	<?php
	/**
	 * The type="button" is REQUIRED!
	 * Otherwise it defaults back to type="submit", which will cause a "click" event to fire when the user
	 * focuses an input in the settings form and then hints "Enter".
	 *
	 * @see https://jsfiddle.net/b7oandjw/
	 */
	?>
    <button type="button" class="<?php echo $button->button_class; ?>"><?php echo esc_html($button->label); ?></button>
</div>
<?php endforeach; ?>