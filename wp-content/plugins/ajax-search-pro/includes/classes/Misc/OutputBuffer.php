<?php /** @noinspection HttpUrlsUsage */

namespace WPDRMS\ASP\Misc;

/**
 * Class only kept to avoid fatal errors during activation
 *
 * @deprecated 4.27
 */
class OutputBuffer {

	function obClose(): bool {
		return false;
	}

	function getInstance() {
		return new self();
	}
}
