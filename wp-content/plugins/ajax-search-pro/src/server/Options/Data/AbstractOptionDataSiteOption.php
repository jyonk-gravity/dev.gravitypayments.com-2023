<?php

namespace WPDRMS\ASP\Options\Data;

use Exception;
use WPDRMS\ASP\Patterns\SingletonTrait;

/**
 * Option Model with actual DB communication (save and load)
 *
 * @see AbstractOptionData
 */
abstract class AbstractOptionDataSiteOption extends AbstractOptionData implements OptionDataORM {
	protected const OPTION_NAME = '';

	use SingletonTrait;

	private function __construct() {
		$this->load();
		parent::__construct();
	}

	/**
	 * Loads the stored data from the DB
	 *
	 * @return static
	 */
	public function load(): self {
		if ( static::OPTION_NAME !== '' ) {
			$stored_option = get_site_option( static::OPTION_NAME );
			if ( !is_array($stored_option) ) {
				$stored_option = array();
			}
			$this->args = $stored_option;
		}

		/**
		 * Very important to reset the options array
		 * This will trigger a new instantiation with ->get(), ->getAll() etc..
		 * when manually called after construction.
		 */
		$this->options = array();

		return $this;
	}

	/**
	 * Saves the object data to the DB
	 *
	 * @return static
	 * @throws Exception
	 */
	public function save(): self {
		if ( static::OPTION_NAME !== '' ) {
			update_site_option(
				static::OPTION_NAME,
				/**
				 * This ensures that serialize in the update_site_option() stores
				 * the class as array (similarly to json_serialize)
				 */
				array_map(
					function ( $o ) {
						return $o->jsonSerialize(); // @phpstan-ignore-line
					},
					$this->getAll()
				)
			);
		}
		return $this;
	}

	/**
	 * Saves the default object data to the DB
	 *
	 * @return static
	 * @throws Exception
	 */
	public function saveDefaults(): self {
		if ( static::OPTION_NAME !== '' ) {
			update_site_option(
				static::OPTION_NAME,
				/**
				 * This ensures that serialize in the update_site_option() stores
				 * the class as array (similarly to json_serialize)
				 */
				array_map(
					function ( $o ) {
						return $o->jsonSerialize(); // @phpstan-ignore-line
					},
					$this->getDefaults()
				)
			);
		}
		return $this;
	}
}
