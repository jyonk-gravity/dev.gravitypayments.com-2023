<?php

namespace WPDRMS\ASP\Options\Data;

use Exception;
use JsonSerializable;
use WPDRMS\ASP\Options\Factories\OptionFactory;
use WPDRMS\ASP\Options\Models\Option;

/**
 * Handles option generation on the fly from DB stored values.
 *
 * @see .phpstorm.meta.php to specify Option types for get() method
 */
abstract class AbstractOptionData implements OptionData, JsonSerializable {
	protected const OPTIONS = array(
		'sample_option_name1' => array(
			'type'         => 'border',
			'default_args' => array(
				'width' => 2,
				'color' => 'blue',
			),
		),
		'sample_option_name2' => array(
			'type' => 'shadow',
		),
	);


	/**
	 * @var Array<string, string>
	 */
	protected array $data;

	/**
	 * @var Array<string, Option>
	 */
	protected array $options;


	/**
	 * @param Array<string, string> $data
	 */
	public function __construct( array $data ) {
		$this->setData($data);
	}

	public function setData( array $data ): void {
		$this->data    = $data;
		$this->options = array();
	}

	/**
	 * @param string $option_name
	 * @return Option
	 * @throws Exception
	 */
	public function get( string $option_name ): Option {
		if ( isset($this->options[ $option_name ]) ) {
			return $this->options[ $option_name ];
		}

		if ( !isset(static::OPTIONS[ $option_name ]) ) {
			throw new Exception('Option key invalid!');
		}

		if ( isset($this->data[ $option_name ]) ) {
			if ( is_string($this->data[ $option_name ]) ) {
				$args = json_decode($this->data[ $option_name ]);
			} else {
				$args = $this->data[ $option_name ];
			}
		} elseif ( isset(static::OPTIONS[ $option_name ]['default_args']) ) {
			$args = static::OPTIONS[ $option_name ]['default_args'];
		} else {
			$args = array();
		}

		$this->options[ $option_name ] = OptionFactory::instance()->create(
			static::OPTIONS[ $option_name ]['type'],
			$args
		);

		return $this->options[ $option_name ];
	}

	/**
	 * @return Option[]
	 * @throws Exception
	 */
	public function getAll(): array {
		foreach ( static::OPTIONS as $key => $option ) {
			$this->get( $key );
		}
		return $this->options;
	}


	public function jsonSerialize(): array {
		try {
			return $this->getAll();
		} catch ( Exception $e ) {
			return array();
		}
	}

	public function toJson(): string {
		try {
			$res = wp_json_encode($this->getAll());
		} catch ( Exception $e ) {
			return '{}';
		}
		return $res === false ? '{}' : $res;
	}
}
