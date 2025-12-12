<?php

namespace WPDRMS\ASP\Modal\Factories;

use Exception;
use InvalidArgumentException;
use WPDRMS\ASP\Modal\Services\TimedModalService;

/**
 * Builder class for creating individual modal configurations.
 *
 * @phpstan-import-type TimedModalData from TimedModalService
 */
class ModalBuilder {

	/** @var TimedModalData|array{} */
	private array $modal = array();

	/**
	 * Sets the heading of the modal.
	 *
	 * @param string $heading
	 * @return self
	 */
	public function setHeading( string $heading ): self {
		$this->modal['heading'] = $heading;
		return $this;
	}

	/**
	 * Sets the content of the modal.
	 *
	 * @param string $content
	 * @return self
	 */
	public function setContent( string $content ): self {
		$this->modal['content'] = $content;
		return $this;
	}

	/**
	 * Sets the maximum number of times the modal can be shown.
	 *
	 * @param int $max_times_shown
	 * @return self
	 */
	public function setMaxTimesShown( int $max_times_shown ): self {
		$this->modal['max_times_shown'] = $max_times_shown;
		return $this;
	}

	/**
	 * Sets the initial delay before showing the modal.
	 *
	 * @param int $first_delay
	 * @return self
	 */
	public function setFirstDelay( int $first_delay ): self {
		$this->modal['first_delay'] = $first_delay;
		return $this;
	}

	/**
	 * Sets the subsequent delay between showing the modal.
	 *
	 * @param int $delay
	 * @return self
	 */
	public function setDelay( int $delay ): self {
		$this->modal['delay'] = $delay;
		return $this;
	}

	/**
	 * Sets whether the modal should have a close icon displayed
	 *
	 * @param bool $show_close_icon
	 * @return self
	 */
	public function setShowCloseIcon( bool $show_close_icon ): self {
		$this->modal['show_close_icon'] = $show_close_icon;
		return $this;
	}

	/**
	 * Sets whether the modal should close when clicking the background.
	 *
	 * @param bool $close_on_background_click
	 * @return self
	 */
	public function setCloseOnBackgroundClick( bool $close_on_background_click ): self {
		$this->modal['close_on_background_click'] = $close_on_background_click;
		return $this;
	}

	/**
	 * Sets the callback to determine if the modal should be shown.
	 *
	 * @param callable $callback
	 * @return self
	 */
	public function setCallback( callable $callback ): self {
		$this->modal['callback'] = $callback;
		return $this;
	}

	/**
	 * Sets the type of the modal ('info' or 'warning').
	 *
	 * @param 'info'|'warning' $type
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function setType( string $type ): self {
		if ( !in_array($type, array( 'info', 'warning' ), true) ) {
			throw new InvalidArgumentException("Type must be 'info' or 'warning'.");
		}
		$this->modal['type'] = $type;
		return $this;
	}

	/**
	 * Sets the buttons configuration for the modal.
	 *
	 * @param array<string, array<string, mixed>> $buttons
	 * @return self
	 */
	public function setButtons( array $buttons ): self {
		$this->modal['buttons'] = $buttons;
		return $this;
	}

	/**
	 * Builds and returns the modal configuration array.
	 *
	 * @return TimedModalData
	 *
	 * @throws InvalidArgumentException If required fields are missing.
	 * @throws Exception
	 */
	public function build(): array {
		$required_fields = array(
			'heading',
			'content',
			'max_times_shown',
			'first_delay',
			'delay',
			'callback',
			'type',
		);

		if ( empty($this->modal) ) {
			throw new Exception('Modal data is empty.'); // @phpcs:ignore
		}

		foreach ( $required_fields as $field ) {
			if ( !array_key_exists($field, $this->modal) ) {
				throw new InvalidArgumentException("Missing required field: {$field}"); // @phpcs:ignore
			}
		}
		return $this->modal;
	}
}
