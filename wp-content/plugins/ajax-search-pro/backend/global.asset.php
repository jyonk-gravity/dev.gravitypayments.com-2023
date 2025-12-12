<?php
return array(
	'ASP_BACKEND' => array(
		'admin_url'       => admin_url(),
		'ajaxurl'         => admin_url('admin-ajax.php'),
		'home_url'        => home_url('/'),
		'rest_url'        => apply_filters('asp/rest/base_url/', rest_url()),
		'backend_ajaxurl' => admin_url('admin-ajax.php'),
		'asp_url'         => ASP_URL,
		'upload_url'      => wd_asp()->upload_url,
		'is_multisite'    => is_multisite(),
		'is_multilang'    => asp_is_multilang(),
	),
);
