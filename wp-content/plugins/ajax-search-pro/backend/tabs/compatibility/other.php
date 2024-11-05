<div class="item">
	<?php $o = new wpdreamsYesNo("rest_api_enabled", __('Enable the REST API?', 'ajax-search-pro'),
		$com_options['rest_api_enabled']
	); ?>
	<p class='descMsg'>
		<?php echo sprintf( __('You can download the <a target="_blank" href="%s">OpenAPI Swagger file here</a>.'),
			ASP_URL . 'swagger.yaml' ); ?>
		<?php echo sprintf( __('Check the <a target="_blank" href="%s">REST API</a> section of the knowledge base for more info.'),
			'https://knowledgebase.ajaxsearchpro.com/other/rest-api' ); ?>
	</p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomPostTypesAll("meta_box_post_types", __('Display the Ajax Search Pro Meta Boxes on these post types', 'ajax-search-pro'),
        $com_options['meta_box_post_types']);
    ?>
	<p class='descMsg'>
		<?php echo __('Displays the Negative keywords & Additional keywords Meta Boxes and the Classic Editor buttons on the selected post types only.'); ?>
	</p>
</div>