<?php

namespace WPDRMS\ASP\Asset;

interface AssetInterface {
	public function register(): void;

	public function deregister(): void;
}
