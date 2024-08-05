<?php
/**
 * WARNING! DO NOT EDIT THIS FILE DIRECTLY!
 *
 * FOR CUSTOM CSS USE THE PLUGIN THEME OPTIONS->CUSTOM CSS PANEL.
 */

/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");
?>

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.horizontal,
    <?php echo $asp_res_ids2; ?>.horizontal,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.horizontal {
    <?php wpdreams_gradient_css($style['hboxbg']); ?>
    <?php echo $style['hboxborder']; ?>
    <?php echo wpdreams_box_shadow_css($style['hboxshadow']); ?>
    display: none;
    visibility: hidden;
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.horizontal .results,
    <?php echo $asp_res_ids2; ?>.horizontal .results,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.horizontal .results {
    overflow-y: hidden;
	overflow-x: auto;
}


<?php if ( $style['h_res_show_scrollbar'] == 0 ): ?>
<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.horizontal .results .resdrg,
    <?php echo $asp_res_ids2; ?>.horizontal .results .resdrg,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.horizontal .results .resdrg {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
}
<?php else: ?>
	<?php if ($use_compatibility == true): ?>
	<?php echo $asp_res_ids1; ?>.horizontal .results,
	<?php echo $asp_res_ids2; ?>.horizontal .results,
	<?php endif; ?>
	<?php echo $asp_res_ids; ?>.horizontal .results {
		scrollbar-width: thin;
		scrollbar-color: <?php echo wpd_gradient_get_color_only($style['h_res_overflow_color']); ?> <?php echo wpd_gradient_get_color_only($style['hboxbg']); ?>;
	}
	<?php if ($use_compatibility == true): ?>
	<?php echo $asp_res_ids1; ?>.horizontal .results::-webkit-scrollbar,
	<?php echo $asp_res_ids2; ?>.horizontal .results::-webkit-scrollbar,
	<?php endif; ?>
	<?php echo $asp_res_ids; ?>.horizontal .results::-webkit-scrollbar {
		height: 7px;
	}

	<?php if ($use_compatibility == true): ?>
	<?php echo $asp_res_ids1; ?>.horizontal .results::-webkit-scrollbar-track,
	<?php echo $asp_res_ids2; ?>.horizontal .results::-webkit-scrollbar-track,
	<?php endif; ?>
	<?php echo $asp_res_ids; ?>.horizontal .results::-webkit-scrollbar-track {
		background: <?php echo wpd_gradient_get_color_only($style['hboxbg']); ?>;
	}
	<?php if ($use_compatibility == true): ?>
	<?php echo $asp_res_ids1; ?>.horizontal .results::-webkit-scrollbar-thumb,
	<?php echo $asp_res_ids2; ?>.horizontal .results::-webkit-scrollbar-thumb,
	<?php endif; ?>
	<?php echo $asp_res_ids; ?>.horizontal .results::-webkit-scrollbar-thumb {
	<?php if ($style['h_res_overflow_autohide']): ?>
		background: transparent;
	<?php else: ?>
		background: <?php echo wpd_gradient_get_color_only($style['h_res_overflow_color']); ?>;
	<?php endif; ?>
		border-radius: 5px;
		border: none;
	}
	<?php if ($style['v_res_overflow_autohide']): ?>
		<?php if ($use_compatibility == true): ?>
		<?php echo $asp_res_ids1; ?>.horizontal:hover .results::-webkit-scrollbar-thumb,
		<?php echo $asp_res_ids2; ?>.horizontal:hover .results::-webkit-scrollbar-thumb,
		<?php endif; ?>
		<?php echo $asp_res_ids; ?>.horizontal:hover .results::-webkit-scrollbar-thumb {
			background: <?php echo wpd_gradient_get_color_only($style['h_res_overflow_color']); ?>;
		}
		@media (hover: none), (max-width: 500px) {
			<?php if ($use_compatibility == true): ?>
			<?php echo $asp_res_ids1; ?>.horizontal .results::-webkit-scrollbar-thumb,
			<?php echo $asp_res_ids2; ?>.horizontal .results::-webkit-scrollbar-thumb,
			<?php endif; ?>
			<?php echo $asp_res_ids; ?>.horizontal .results::-webkit-scrollbar-thumb {
				background: <?php echo wpd_gradient_get_color_only($style['h_res_overflow_color']); ?>;
			}
		}
	<?php endif; ?>
<?php endif; ?>

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.horizontal .results .item,
    <?php echo $asp_res_ids2; ?>.horizontal .results .item,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.horizontal .results .item {
    height: <?php echo w_isset_def($style['horizontal_res_height'], 'auto'); ?>;
    width: <?php echo $style['hreswidth']; ?>;
    margin: 10px <?php echo $style['hressidemargin']; ?>;
    padding: <?php echo $style['hrespadding']; ?>;
    float: left;
    <?php wpdreams_gradient_css($style['hresultbg']); ?>
    <?php echo $style['hresultborder']; ?>
    <?php wpdreams_box_shadow_css($style['hresultshadow']); ?>
    <?php if ( $style['h_res_show_scrollbar'] == 0 ): ?>
        flex-shrink: 0;
        flex-grow: 0;
    <?php endif; ?>
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.horizontal .results .item:hover,
    <?php echo $asp_res_ids2; ?>.horizontal .results .item:hover,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.horizontal .results .item:hover {
    <?php wpdreams_gradient_css($style['hresulthbg']); ?>
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.horizontal .results .item .asp_image,
    <?php echo $asp_res_ids2; ?>.horizontal .results .item .asp_image,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.horizontal .results .item .asp_image {
    margin: 0 auto;
    <?php wpdreams_gradient_css($style['hresultbg']); ?>
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?>.horizontal .results .item .asp_image,
    <?php echo $asp_res_ids2; ?>.horizontal .results .item .asp_image,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.horizontal .results .item .asp_image {
    width: <?php echo $_vimagew ?>px;
    height: <?php echo $_vimageh; ?>px;
    <?php echo $style['hresultimageborder']; ?>
    float: none;
    margin: 0 auto 6px;
    position: relative;
	background-position: center;
	background-size: cover;
}

<?php if ($use_compatibility == true): ?>
	<?php echo $asp_res_ids1; ?>.horizontal .results .item .asp_image .void,
	<?php echo $asp_res_ids2; ?>.horizontal .results .item .asp_image .void,
<?php endif; ?>
<?php echo $asp_res_ids; ?>.horizontal .results .item .asp_image .void {
	position: absolute;
	width: 100%;
	height: 100%;
	top: 0;
	left: 0;
	<?php echo $style['hresultimageshadow']; ?>
}