<?php

namespace WPDRMS\ASP\Options;

use WPDRMS\ASP\Core\Instances;
use WPDRMS\ASP\Core\Models\SearchInstance;
use WPDRMS\ASP\Options\Data\SearchOptions;
use WPDRMS\ASP\Options\Factories\OptionFactory;
use WPDRMS\ASP\Options\Models\BorderOption;
use WPDRMS\ASP\Options\Models\BoxShadowOption;
use WPDRMS\Backend\Options\Option;

class OptionsController {

	public function __construct() {
		$f = OptionFactory::instance();
		// $x = $f->create(BoxShadowOption::class);
		// echo $x->left; // Type hint yay!
		//
		// $x = $f->create(BorderOption::class);
		// echo $x->left; // Correctly saying property doesn't exist

		// $y = $f->create('border');

		$x  = Instances::getInstance()->get(1);
		$xx = $x->options->get('box_border');
		foreach ( $x as $inst ) {
			$box_border = $inst->options->get('box_border');
		}

		$test = new SearchOptions(
			array(
				'border' => '{}',
			)
		);

		$y = $test->get('box_border');
	}
}
