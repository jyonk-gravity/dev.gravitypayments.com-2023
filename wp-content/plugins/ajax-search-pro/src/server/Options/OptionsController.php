<?php

namespace WPDRMS\ASP\Options;

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
	}
}
