<?php

namespace WPDRMS\ASP\Modal\Factories;

use Exception;
use WPDRMS\ASP\Modal\Callbacks\ModalCallbacks;
use WPDRMS\ASP\Modal\Services\TimedModalService;

/**
 * Factory class for generating TimedModalData.
 *
 * @phpstan-import-type TimedModalData from TimedModalService
 */
class ModalFactory implements ModalFactoryInterface {

	/**
	 * Generates and returns the complete TimedModalData array.
	 *
	 * @return Array<string, TimedModalData>
	 * @throws Exception
	 */
	public function createModals(): array {
		$modals = array();

		// Activate License Modal
		$modals['activate_license'] = ( new ModalBuilder() )
			->setHeading('Please verify your license!')
			->setContent(
				'
                Verifying the license ensures that the plugin is secure and automatically up to date.<br>
                Don\'t worry, it takes only a minute!
            '
			)
			->setMaxTimesShown(1000)
			->setFirstDelay(0)
			->setDelay(0)
			->setCloseOnBackgroundClick(false)
			->setCallback(array( ModalCallbacks::class, 'shouldShowActivateLicense' ))
			->setType('info')
			->setButtons(
				array(
					'okay' => array(
						'type' => 'okay',
						'text' => 'Let\'s go!',
						'href' => '/wp-admin/admin.php?page=asp_updates_help',
					),
				)
			)
			->build();

		// Take Survey Modal
		/*
		$modals['take_survey'] = ( new ModalBuilder() )
			->setHeading('Help us improve Ajax Search Pro!')
			->setContent('Please take a super quick survey, it will help us tremendously. Thank you!')
			->setMaxTimesShown(2)
			->setFirstDelay(2 *3600)
			->setDelay(3600 *24)
			->setCallback(array( ModalCallbacks::class, 'shouldShowTakeSurvey' ))
			->setType('info')
			->setButtons(
				array(
					'okay'   => array(
						'type'               => 'okay',
						'text'               => 'Sure! Let\'s go!',
						'href'               => 'https://us9.list-manage.com/survey?u=370663b5e3df02747aa5673ed&id=7040339d37&attribution=false',
						'target'             => '_blank',
						'dismmisses_forever' => true,
					),
					'cancel' => array(
						'type' => 'cancel',
						'text' => 'Remind me Later',
					),
					'never'  => array(
						'type'               => 'secondary',
						'text'               => 'No thank you!',
						'dismmisses_forever' => true,
					),
				)
			)
			->build();
		*/

		return $modals;
	}
}
