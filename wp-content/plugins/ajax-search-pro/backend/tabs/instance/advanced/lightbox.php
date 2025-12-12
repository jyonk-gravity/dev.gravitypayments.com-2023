<div class="item">
	<?php
	$o = new wpdreamsYesNo("lightbox_overlay", __('Display the lightbox overlay?', 'ajax-search-pro'),  $sd['lightbox_overlay']);
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsTextSmall("lightbox_overlay_opacity", __('Overlay Opacity (0-1)', 'ajax-search-pro'),
			$sd['lightbox_overlay_opacity']);
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsColorPicker("lightbox_overlay_color", __('Overlay color', 'ajax-search-pro'),
			$sd['lightbox_overlay_color']);
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("lightbox_navigation", __('Display arrow navigation?', 'ajax-search-pro'),
			$sd['lightbox_navigation']);
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("lightbox_enable_keyboard", __('Enable keyboard navigation?', 'ajax-search-pro'),
			$sd['lightbox_enable_keyboard']);
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("lightbox_disable_right_click", __('Disable right mouse click on images?', 'ajax-search-pro'),
			$sd['lightbox_disable_right_click']);
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("lightbox_close_icon", __('Display the close icon?', 'ajax-search-pro'),
			$sd['lightbox_close_icon']);
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("lightbox_doc_close", __('Close the lighbox when the user clicks outside of it?', 'ajax-search-pro'),
			$sd['lightbox_doc_close']);
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("lightbox_disable_scroll", __('Disable document scrolling when lightbox is opened?', 'ajax-search-pro'),
			$sd['lightbox_disable_scroll']);
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsTextSmall("lightbox_animation_speed", __('Animation speed', 'ajax-search-pro'),
			$sd['lightbox_animation_speed']);
	?>ms
	<div class="descMsg">
		<?php echo __('Default: 250', 'ajax-search-pro'); ?>
	</div>
</div>