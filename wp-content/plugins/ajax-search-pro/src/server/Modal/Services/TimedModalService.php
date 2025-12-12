<?php

namespace WPDRMS\ASP\Modal\Services;

use WPDRMS\ASP\Modal\Factories\ModalFactoryInterface;
use WPDRMS\ASP\Modal\Models\TimedModal;
use WPDRMS\ASP\Patterns\SingletonTrait;

/**
 * @phpstan-import-type ModalButtons from TimedModal
 * @phpstan-type TimedModalData array{
 *      heading: string,
 *      content: string,
 *      max_times_shown: int,
 *      first_delay: int,
 *      delay: int,
 *      close_on_background_click?: boolean,
 *      callback: Array<string, string>,
 *      type: 'info'|'warning',
 *      buttons?: ModalButtons,
 *  }
 *
 * @phpstan-type StoredTimedModalData array{
 *     last_shown: int,
 *     times_shown: int
 *  }
 */
class TimedModalService {
	use SingletonTrait;

	const MODAL_OPTION_NAME          = '_asp_timed_modal_data';
	const DISPLAYED_COOKIE_PREFIX    = '_asp_timed_modal_';
	const CLICKED_OKAY_COOKIE_PREFIX = '_asp_timed_modal_okay_';

	/**
	 * @var Array<string, TimedModalData>
	 */
	private array $modals;

	/**
	 * @var ModalFactoryInterface
	 */
	private ModalFactoryInterface $modal_factory;

	/**
	 * Constructor with dependency injection.
	 *
	 * @param ModalFactoryInterface $modal_factory
	 */
	public function __construct( ModalFactoryInterface $modal_factory ) {
		$this->modal_factory = $modal_factory;
		// Use the injected ModalFactory to create modals
		$this->modals = $this->modal_factory->createModals();
	}

	/**
	 * Initializes the modal queue, removes modals that no longer should be in queue
	 *
	 * @return void
	 */
	public function init(): void {
		$modal_data      = get_site_option(self::MODAL_OPTION_NAME);
		$options_changed = false;
		if ( $modal_data === false ) {
			$modal_data = array();
		}
		foreach ( $this->modals as $name => $data ) {
			if ( isset($modal_data[ $name ]) ) {
				continue;
			}
			$modal_data[ $name ] = array(
				'last_shown'  => time(),
				'times_shown' => 0,
			);
			$options_changed     = true;
		}
		foreach ( $this->modals as $name => $data ) {
			if ( isset($_COOKIE[ self::CLICKED_OKAY_COOKIE_PREFIX . $name ]) ) { // Clicked okay, don't show again
				$modal_data[ $name ]['times_shown'] = $this->modals[ $name ]['max_times_shown'];
				$modal_data[ $name ]['last_shown']  = time();
				$options_changed                    = true;
				setcookie(self::CLICKED_OKAY_COOKIE_PREFIX . $name, '', -1, '/');
				setcookie(self::DISPLAYED_COOKIE_PREFIX . $name, '', -1, '/');
			} elseif ( isset($_COOKIE[ self::DISPLAYED_COOKIE_PREFIX . $name ]) ) { // Dismissed, increase counter
				++$modal_data[ $name ]['times_shown'];
				$modal_data[ $name ]['last_shown'] = time();
				$options_changed                   = true;
				setcookie(self::DISPLAYED_COOKIE_PREFIX . $name, '', -1, '/');
			}
		}
		if ( $options_changed ) {
			$modal_data = array_intersect_key($modal_data, $this->modals);
			update_site_option(self::MODAL_OPTION_NAME, $modal_data);
		}
	}


	/**
	 * Gets the next modal that should be displayed from the queue.
	 *
	 * @return TimedModal|null
	 */
	public function get(): ?TimedModal {
		/**
		 * @var Array<string, StoredTimedModalData> $modals
		 */
		$modals = get_site_option(self::MODAL_OPTION_NAME, array());
		foreach ( $modals as $name => $data ) {
			if ( !isset($this->modals[ $name ]) ) {
				continue;
			}
			$delay = $data['times_shown'] === 0 ? $this->modals[ $name ]['first_delay'] : $this->modals[ $name ]['delay'];
			if (
				$data['times_shown'] < $this->modals[ $name ]['max_times_shown'] &&
				is_callable($this->modals[ $name ]['callback']) &&
				call_user_func($this->modals[ $name ]['callback']) &&
				( $delay + $data['last_shown'] ) < time()
			) {
				return apply_filters(
					'asp/timed_modal/get',
					new TimedModal(
						$this->modals[ $name ]['type'],
						$name,
						self::DISPLAYED_COOKIE_PREFIX . $name,
						self::CLICKED_OKAY_COOKIE_PREFIX . $name,
						$this->modals[ $name ]['heading'],
						$this->modals[ $name ]['content'],
						$this->modals[ $name ]['show_close_icon'] ?? true,
						$this->modals[ $name ]['close_on_background_click'] ?? true,
						$this->modals[ $name ]['buttons'] ?? null,
					)
				);
			}
		}
		return apply_filters('asp/timed_modal/get', null);
	}

	/**
	 * Wipes out the modal data from the database
	 *
	 * @return void
	 */
	public function wipe(): void {
		delete_option(self::MODAL_OPTION_NAME);
	}
}
