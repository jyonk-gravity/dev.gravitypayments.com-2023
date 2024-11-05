<div class="item item-rlayout item-rlayout-horizontal">
    <p><?php echo __('These options are hidden, because the <span>vertical</span> results layout is selected.', 'ajax-search-pro'); ?></p>
    <p><?php echo __('You can change that under the <a href="#402" data-asp-os-highlight="resultstype" tabid="402">Layout Options -> Results layout</a> panel,
        <br>..or choose a <a href="#601" tabid="601">different theme</a> with a different pre-defined layout.', 'ajax-search-pro'); ?></p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("hhidedesc", __('Hide description if images are available', 'ajax-search-pro'), $sd['hhidedesc']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo('h_res_show_scrollbar', __('Display the results scrollbar?', 'ajax-search-pro'), $sd['h_res_show_scrollbar']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('When turned OFF, the results box will break space, instead of showing a horizontal scroll.', 'ajax-search-pro'); ?>
    </p>
</div>

<fieldset>
	<legend><?php echo __('Horizontal result sizes', 'ajax-search-pro'); ?></legend>
	<div class="item item-flex-nogrow item-flex-wrap wpd-horizontal-res-width">
		<p class="infoMsg item-flex-grow item-flex-100">
			<?php echo __('For witdh % (percentage) values only work if the <strong>Display the results scrollbar</strong> option is <strong>turned OFF</strong> above.', 'ajax-search-pro'); ?>
		</p>
		<?php
		$o = new wpdreamsTextSmall("h_item_width", __('Result width', 'ajax-search-pro'), array(
			'icon' => 'desktop',
			'value' => $sd['h_item_width']
		));
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsTextSmall("h_item_width_tablet", '', array(
			'icon' => 'tablet',
			'value' => $sd['h_item_width_tablet']
		));
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsTextSmall("h_item_width_phone", '', array(
			'icon' => 'phone',
			'value' => $sd['h_item_width_phone']
		));
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg item-flex-grow item-flex-100">
			<?php echo __('Default: <strong>150px</strong>', 'ajax-search-pro'); ?>
			<?php echo sprintf(
				__('Use with <a href="%s" target="_blank">CSS units</a> (like %s or %s or %s ..)', 'ajax-search-pro'),
				'https://www.w3schools.com/cssref/css_units.asp', '200px', '32%', 'auto', '200px'
			); ?>
		</div>
	</div>
	<div class="item item-flex-nogrow item-flex-wrap wpd-horizontal-res-height">
		<?php
		$o = new wpdreamsTextSmall("h_item_height", __('Result height', 'ajax-search-pro'), array(
			'icon' => 'desktop',
			'value' => $sd['h_item_height']
		));
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsTextSmall("h_item_height_tablet", '', array(
			'icon' => 'tablet',
			'value' => $sd['h_item_height_tablet']
		));
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsTextSmall("h_item_height_phone", '', array(
			'icon' => 'phone',
			'value' => $sd['h_item_height_phone']
		));
		$params[$o->getName()] = $o->getData();
		?>
		<div class="descMsg item-flex-grow item-flex-100">
			<?php echo __('Default: <strong>auto</strong>', 'ajax-search-pro'); ?>
			<?php echo __('Use values in pixels or auto only, ex: 200px, auto. % values will not work.') ?>
		</div>
	</div>
	<div class="item item-flex-nogrow item-flex-wrap wpd-horizontal-res-image">
		<?php
		$o = new wpdreamsTextSmall("h_image_height", __('Image height', 'ajax-search-pro'), array(
			'icon' => 'desktop',
			'value' => $sd['h_image_height']
		));
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsTextSmall("h_image_height_tablet", '', array(
			'icon' => 'tablet',
			'value' => $sd['h_image_height_tablet']
		));
		$params[$o->getName()] = $o->getData();
		$o = new wpdreamsTextSmall("h_image_height_phone", '', array(
			'icon' => 'phone',
			'value' => $sd['h_image_height_phone']
		));
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg item-flex-grow item-flex-100">
			<?php echo __('Default: <strong>150px</strong>. Only <strong>auto</strong> or <strong>px</strong> values are accepted.', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item" wd-disable-on="h_res_show_scrollbar:1">
		<?php
		$o = new wpdreamsCustomSelect("h_item_alignment", __('Result item alignment', 'ajax-search-pro'),
			array(
				'selects' => array(
					array('option' => __('Center', 'ajax-search-pro'), 'value' => 'center'),
					array('option' => __('Left', 'ajax-search-pro'), 'value' => 'left'),
					array('option' => __('Right', 'ajax-search-pro'), 'value' => 'right'),
				),
				'value' => $sd['h_item_alignment']
			));
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('Default: <strong>Center</strong>. Sets where each results is aligned in the results container.', 'ajax-search-pro'); ?><br>
			<?php echo __('Applies only when the <strong>Display the results scrollbar?</strong> option is turned OFF above.', 'ajax-search-pro'); ?>
		</p>
	</div>
</fieldset>

<div class="item"><?php
    $o = new wpdreamsNumericUnit("hressidemargin", __('Result side margin', 'ajax-search-pro'), array(
        'value' => $sd['hressidemargin'],
        'units'=>array('px'=>'px')));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsNumericUnit("hrespadding", __('Result padding', 'ajax-search-pro'), array(
        'value' => $sd['hrespadding'],
        'units'=>array('px'=>'px')));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("hboxbg", __('Result container background color', 'ajax-search-pro'), $sd['hboxbg']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBorder("hboxborder", __('Results container border', 'ajax-search-pro'), $sd['hboxborder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<fieldset>
    <legend><?php echo __('Custom scrollbar', 'ajax-search-pro') ?></legend>
    <div class="item item-flex-nogrow">
    <?php
    $o = new wpdreamsYesNo('h_res_overflow_autohide', __('Auto hide the scrollbar?', 'ajax-search-pro'), $sd['h_res_overflow_autohide']);
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsGradient("h_res_overflow_color", __('Scrollbar color', 'ajax-search-pro'), $sd['h_res_overflow_color']);
    $params[$o->getName()] = $o->getData();
    ?>
    </div>
</fieldset>
<div class="item">
    <?php
    $o = new wpdreamsBoxShadow("hboxshadow", __('Results container box shadow', 'ajax-search-pro'), $sd['hboxshadow']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsAnimations("hresultinanim", __('Result item incoming animation', 'ajax-search-pro'), $sd['hresultinanim']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("hresultbg", __('Result item background color', 'ajax-search-pro'), $sd['hresultbg']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("hresulthbg", __('Result item mouse hover background color', 'ajax-search-pro'), $sd['hresulthbg']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBorder("hresultborder", __('Results item border', 'ajax-search-pro'), $sd['hresultborder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBoxShadow("hresultshadow", __('Results item box shadow', 'ajax-search-pro'), $sd['hresultshadow']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBorder("hresultimageborder", __('Results image border', 'ajax-search-pro'), $sd['hresultimageborder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBoxShadow("hresultimageshadow", __('Results image box shadow', 'ajax-search-pro'), $sd['hresultimageshadow']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>