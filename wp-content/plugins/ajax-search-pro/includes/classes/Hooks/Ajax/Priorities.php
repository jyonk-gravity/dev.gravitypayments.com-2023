<?php
namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Utils\Ajax;

if (!defined('ABSPATH')) die('-1');


class Priorities extends AbstractAjax {
	function handle() {
		if ( !empty($_POST['ptask']) ) {
			Ajax::prepareHeaders();
			if ( ASP_DEMO && $_POST['ptask'] == "set" ) {
				echo "!!PSASPSTART!!0!!PSASPEND!!";
				die();
			}

			if ( $_POST['ptask'] == "get" )
				\WPDRMS\ASP\Misc\Priorities::ajax_get_posts();
			else if ( $_POST['ptask'] == "set" )
				\WPDRMS\ASP\Misc\Priorities::ajax_set_priorities();
		}
		die();
	}
}