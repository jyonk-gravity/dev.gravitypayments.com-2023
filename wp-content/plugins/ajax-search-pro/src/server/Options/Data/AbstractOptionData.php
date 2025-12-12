<?php

namespace WPDRMS\ASP\Options\Data;

use Exception;
use JsonSerializable;
use WPDRMS\ASP\Options\Factories\OptionFactory;
use WPDRMS\ASP\Options\Models\Option;
use WPDRMS\ASP\Utils\ArrayUtils;

/**
 * Handles option generation on the fly from DB stored values.
 *
 * This class does not communicate options to the DB, it is only a container.
 * For data storage handling use AbstractOptionDataORM
 *
 * @see .phpstorm.meta.php to specify Option types for get() method
 */
abstract class AbstractOptionData implements OptionData, JsonSerializable {
	/**
	 * All options must be also defined as public properties for type hinting and quick access
	 */
	// public BorderOption $sample_option_name1;
	// public ShadowOption $sample_option_name2;
	protected const OPTIONS = array(
		'sample_option_name1' => array(
			'type'         => 'border', // Factory type, defined in \WPDRMS\ASP\Options\Factories\OptionFactory

			/**
			 * Default arguments passed to the factory constructor
			 */
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
	 * @var Array<string, string|Array<mixed>>
	 */
	protected array $args;

	/**
	 * @var Array<string, Option>
	 */
	protected array $options;


	/**
	 * @param Array<string, string> $data
	 * @throws Exception
	 */
	public function __construct( array $data = array() ) {
		$this->setArgs($data);

		// This will initialize all options and populates the class properties
		$this->getAll();
	}

	public function setArgs( array $args, $merge = true ): self {
		$this->args    = $merge ? array_merge($this->args ?? array(), $args) : $args;
		$this->options = array();
		return $this;
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

		if ( isset($this->args[ $option_name ]) ) {
			if ( is_string($this->args[ $option_name ]) ) {
				$args = json_decode($this->args[ $option_name ]);
			} else {
				$args = $this->args[ $option_name ];
			}
			if ( isset(static::OPTIONS[ $option_name ]['default_args']) ) {
				$args = ArrayUtils::arrayMergeRecursiveDistinct(
					static::OPTIONS[ $option_name ]['default_args'],
					$args
				);
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

		if ( !property_exists($this, $option_name) ) {
			// phpcs:ignore
			throw new Exception(
				"Property '$option_name' should explicitly exist in " . get_class($this) . '! Please define it!' // phpcs:ignore
			);
		}

		unset($this->{$option_name});
		$this->{$option_name} = $this->options[ $option_name ];

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

	public function getDefault( string $option_name ): Option {
		if ( !isset(static::OPTIONS[ $option_name ]) ) {
			throw new Exception('Option key invalid!');
		}

		if ( isset(static::OPTIONS[ $option_name ]['default_args']) ) {
			$args = static::OPTIONS[ $option_name ]['default_args'];
		} else {
			$args = array();
		}

		return OptionFactory::instance()->create(
			static::OPTIONS[ $option_name ]['type'],
			$args
		);
	}

	/**
	 * @return Option[]
	 * @throws Exception
	 */
	public function getDefaults(): array {
		$options = array();
		foreach ( static::OPTIONS as $key => $option ) {
			$options[] = $this->getDefault( $key );
		}
		return $options;
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
