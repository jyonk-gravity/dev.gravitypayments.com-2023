<div class="wdo" id="wdo"><div id="asp-root"></div></div>
<?php

use WPDRMS\ASP\Utils\Script;

$metadata = require_once ASP_PATH . 'build/css/wdo-global.asset.php';
wp_enqueue_style(
	'wpo-global',
	ASP_URL_NP . 'build/css/wdo-global.css',
	$metadata['dependencies'],
	$metadata['version'],
);



$metadata = require_once ASP_PATH . 'build/js/statistics.asset.php';
wp_enqueue_script(
	'wpd-asp-statistics',
	ASP_URL_NP . 'build/js/statistics.js',
	$metadata['dependencies'],
	$metadata['version'],
	array( 'in_footer' =>true ),
);

$metadata = require_once __DIR__ . '/global.asset.php';
// wp_add_inline_script('wpd-asp-statistics', $metadata, 'before');
Script::objectToInlineScript(
	'wpd-asp-statistics',
	'ASP_BACKEND',
	$metadata['ASP_BACKEND'],
	'before',
	true,
);
