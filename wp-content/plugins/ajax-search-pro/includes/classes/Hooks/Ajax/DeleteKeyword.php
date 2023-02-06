<?php
namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Misc\Statistics;
use WPDRMS\ASP\Utils\Ajax;

if (!defined('ABSPATH')) die('-1');


class DeleteKeyword extends AbstractAjax {
	function handle() {
		if (isset($_POST['keywordid'])) {
			echo Statistics::deleteKw($_POST['keywordid'] + 0);
			exit;
		}
		Ajax::prepareHeaders();
		echo 0;
		die();
	}
}