<?php

namespace WPDRMS\ASP\Modal\Callbacks;

use WPDRMS\ASP\Utils\Server;
use WPDRMS\ASP\Misc\PluginLicense;

class ModalCallbacks {
	/**
	 * Determines whether to show the 'activate_license' modal.
	 *
	 * @return bool
	 */
	public static function shouldShowActivateLicense(): bool {
		return !ASP_DEMO
			&& !Server::isLocalEnvironment()
			&& !PluginLicense::isActivated();
	}

	/**
	 * Determines whether to show the 'take_survey' modal.
	 *
	 * @return bool
	 */
	public static function shouldShowTakeSurvey(): bool {
		return !ASP_DEMO;
	}

	/**
	 * When used, the modal will be displayed every time
	 *
	 * @return bool
	 */
	public static function shouldShowEveryTime(): bool {
		return true;
	}
}
