<?php

namespace WPDRMS\ASP\Modal\Factories;

use WPDRMS\ASP\Modal\Services\TimedModalService;

/**
 * Interface ModalFactoryInterface
 *
 * Defines the contract for creating modal configurations.
 *
 * @phpstan-import-type TimedModalData from TimedModalService
 */
interface ModalFactoryInterface {
	/**
	 * Generates and returns the complete TimedModalData array.
	 *
	 * @return Array<string, TimedModalData>
	 */
	public function createModals(): array;
}
