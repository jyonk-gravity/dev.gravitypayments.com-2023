<?php

	namespace ShortPixel\AI;

	use ShortPixel\AI\Options\Option;
	use ShortPixel\AI\Options\Category;
	use ShortPixel\AI\Options\Collection;

	class Options {
		/**
		 * @var \ShortPixel\AI\Options $instance
		 */
		private static $instance;

		/**
		 * @var string $optionsName Options collection stored in database using this name
		 */
		private static $optionsName = 'short_pixel_ai_options';

		/**
		 * Single ton implementation
		 *
		 * @return \ShortPixel\AI\Options
		 */
		public static function _() {
			return self::$instance instanceof self ? self::$instance : new self();
		}

		private function __construct() {
			if ( !isset( self::$instance ) || !self::$instance instanceof self ) {
				self::$instance = $this;
			}

			add_action( 'admin_init', [ $this, 'init' ] );
		}

		public function init() {
			// Registering settings
			register_setting( 'short_pixel_ai_option_group', self::$optionsName );
		}

		/**
		 * Method returns options magically
		 *
		 * @param $name
		 *
		 * @return mixed
		 */
		public function __get( $name ) {
			$exploded_name = $this->explodeMagicName( $name );

			return $this->get( $exploded_name[ 'name' ], $exploded_name[ 'categories' ], null );
		}

		/**
		 * Method sets options magically
		 *
		 * @param $name
		 * @param $value
		 *
		 * @return mixed
		 */
		public function __set( $name, $value ) {
			$exploded_name = $this->explodeMagicName( $name );

			return $this->set( $value, $exploded_name[ 'name' ], $exploded_name[ 'categories' ] );
		}

		/**
		 * Method gets a ShortPixel AI's option from the database
		 *
		 * @param string|array|null $category Options category
		 * @param string|null       $name     Name of option
		 * @param mixed             $default  Default value of requested option
		 *
		 * @return mixed
		 */
		public function get( $name = null, $category = null, $default = null ) {
			if ( !function_exists( 'get_option' ) ) {
				return false;
			}

			$collection = get_option( self::$optionsName, Collection::_() );
			$collection = $collection instanceof Collection ? $collection : Collection::_();
            //echo("aaaiciiii:"); var_dump($collection);

			$name     = $this->prepareName( $name );
			$category = $this->prepareCategories( $category );

			$collection = $this->walker( $collection, $category );

			// return empty( $name ) ? $collection : ( $collection->{$name} instanceof Category || $collection->{$name} instanceof Option ? $collection->{$name} : Option::_() );
			return empty( $name )
				? $collection
				: ( isset( $collection->__dyna->{$name} )
					? $collection->__dyna->{$name}
					: (isset($collection->{$name})
                        ? $collection->{$name}
                        : $default
                    )
				);
		}

		/**
		 * Method sets a ShortPixel AI's option into the database
		 *
		 * @param mixed        $value    Value of option
		 * @param string|null  $name     Name of option
		 * @param string|array $category Options category
		 *
		 * @return bool|Options
		 */
		public function set( $value, $name = null, $category = null ) {
			if ( !function_exists( 'update_option' ) ) {
				return false;
			}

			$collection = $this->get();

			$name = $this->prepareName( $name );

			$category = $this->prepareCategories( $category );

			$this->creator( $collection, $category, $name, $value );

			update_option( self::$optionsName, $collection );

			return $this;
		}

		public function delete( $name, $category = null ) {
			if ( !function_exists( 'update_option' ) || empty( $name ) ) {
				return false;
			}

			$collection = $this->get();

			$name     = $this->prepareName( $name );
			$category = $this->prepareCategories( $category );

			$this->remover( $collection, $category, $name );

			update_option( self::$optionsName, $collection );

			return $this;
		}

		public function clearCollection() {
			if ( !function_exists( 'delete_option' ) ) {
				return false;
			}

			delete_option( self::$optionsName );

			return $this;
		}

		/**
		 * Method walks through the options object, detects depth of the specified categories and returns seek options then
		 *
		 * @param Collection $collection
		 * @param array      $categories
		 * @param int        $index
		 *
		 * @return object|null
		 */
		private function walker( $collection, $categories, $index = 0 ) {
			if ( ( $collection instanceof Collection || $collection instanceof Category ) && $index < count( $categories ) ) {
				if ( $collection->{$categories[ $index ]} instanceof Category ) {
					return $this->walker( $collection->{$categories[ $index ]}, $categories, $index + 1 );
				}
				else {
					return $collection;
				}
			}
			else {
				return $collection;
			}
		}

		/**
		 * Method creates required categories and options
		 *
		 * @param Collection $collection
		 * @param array      $categories
		 * @param string     $name
		 * @param mixed      $value
		 * @param int        $index
		 */
		private function creator( &$collection, $categories, $name, $value, $index = 0 ) {
			if ( ( $collection instanceof Collection || $collection instanceof Category ) && $index < count( $categories ) ) {
				if ( !$collection->{$categories[ $index ]} instanceof Category ) {
					$collection->{$categories[ $index ]} = Category::_();
				}

				$this->creator( $collection->{$categories[ $index ]}, $categories, $name, $value, $index + 1 );
			}
			else {
				if ( !empty( $name ) ) {
					$value = $this->optionCreator( $value );

					$collection->{$name} = $value;
				}
			}
		}

		/**
		 * Method creates Option using standard types
		 *
		 * @param mixed $option
		 *
		 * @return \ShortPixel\AI\Options\Option
		 */
		private function optionCreator( $option ) {
			if ( ( ( is_array( $option ) && $this->isAssoc( $option ) ) || is_object( $option ) ) ) {
				$options = Option::_();

                if($option instanceof Options\Option || $option instanceof Options\Category || $option instanceof Options\Collection) {
                    $option = $option->getData();
                }

				foreach ( $option as $key => $item ) {
					$key = $this->prepareName( $key );

					if ( ( is_array( $item ) && $this->isAssoc( $item ) ) || is_object( $item ) ) {
						$options->{$key} = $this->optionCreator( $item );
					}
					else {
						$options->{$key} = $item;
					}
				}

				return $options;
			}
			else {
				return $option;
			}
		}

		/**
		 * Method removes sought categories or options
		 *
		 * @param Collection $collection
		 * @param array      $categories
		 * @param string     $name
		 * @param int        $index
		 */
		private function remover( &$collection, $categories, $name, $index = 0 ) {
			if ( ( $collection instanceof Collection || $collection instanceof Category ) && $index < count( $categories ) ) {
				if ( $collection->{$categories[ $index ]} instanceof Category ) {
					$this->remover( $collection->{$categories[ $index ]}, $categories, $name, $index + 1 );
				}
			}
			else {
				if ( !empty( $name ) ) {
					$collection->unsetProperty( $name );
				}
			}
		}

		/**
		 * Method prepares categories for following manipulations
		 *
		 * @param $categories
		 *
		 * @return array|false
		 */
		private function prepareCategories( $categories ) {
			if ( empty( $categories ) ) {
				return [];
			}

			if ( !is_array( $categories ) && !empty( $categories ) ) {
				// transforming category to snake_case
				$categories = [ $this->prepareName( $categories ) ];
			}
			else if ( is_array( $categories ) ) {
				foreach ( $categories as $index => $item ) {
					$item = $this->prepareName( $item );

					if ( is_string( $item ) && !empty( $item ) ) {
						$categories[ $index ] = $item;
					}
					else {
						unset( $categories[ $index ] );
					}
				}
			}
			else {
				return [];
			}

			return $categories;
		}

		private function prepareName( $name ) {
			if ( is_object( $name ) || is_array( $name ) ) {
				return false;
			}

			$name = (string) $name;

			if ( empty( $name ) ) {
				return false;
			}

			return Converter::toSnakeCase( $name );
		}

		private function explodeMagicName( $name ) {
			$split_name = explode( '_', $name );
			$split_qty  = count( $split_name );

			$return = [ 'name' => null, 'categories' => null ];

			if ( $split_qty > 1 ) {
				$return[ 'name' ]       = Converter::fromCamelCase( lcfirst( array_pop( $split_name ) ) );
				$return[ 'categories' ] = $split_name;

				foreach ( $return[ 'categories' ] as $index => $category ) {
					$return[ 'categories' ][ $index ] = Converter::fromCamelCase( lcfirst( $category ) );
				}
			}
			else {
				$return[ 'name' ] = Converter::fromCamelCase( lcfirst( $split_name[ 0 ] ) );
			}

			return $return;
		}

		private function isAssoc( $array ) {
			if ( !is_array( $array ) || !$array ) {
				return false;
			}

			return array_keys( $array ) !== range( 0, count( $array ) - 1 );
		}
	}
