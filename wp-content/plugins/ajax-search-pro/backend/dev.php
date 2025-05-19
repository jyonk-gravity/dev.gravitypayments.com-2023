<div class="wdo" id="wdo"><div id="asp-root"></div></div>
<?php
$metadata = require_once ASP_PATH . 'build/js/dev.asset.php';
wp_enqueue_script(
	'wpd-asp-dev',
	ASP_URL_NP . 'build/js/dev.js',
	$metadata['dependencies'],
	$metadata['version'],
	array( 'in_footer' =>true ),
);
