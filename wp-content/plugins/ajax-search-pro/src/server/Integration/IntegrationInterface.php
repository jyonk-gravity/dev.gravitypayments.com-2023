<?php

namespace WPDRMS\ASP\Integration;

interface IntegrationInterface {
	public function load(): void;

	public function unload(): void;
}
