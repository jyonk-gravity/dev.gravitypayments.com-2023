<?php

namespace WPDRMS\ASP\BlockEditor;

interface BlockInterface {
	/**
	 * Block registration handler
	 *
	 * @return void
	 */
	public function register(): void;
}