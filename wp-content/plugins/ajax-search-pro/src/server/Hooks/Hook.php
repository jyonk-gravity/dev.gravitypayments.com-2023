<?php

namespace WPDRMS\ASP\Hooks;

interface Hook {
	public function register(): void;

	public function deregister(): void;
}
