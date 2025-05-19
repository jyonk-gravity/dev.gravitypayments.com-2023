<?php

namespace WPDRMS\ASP\Core;

use WPDRMS\ASP\Asset\AssetInterface;
use WPDRMS\ASP\BlockEditor\ASPBlock;
use WPDRMS\ASP\BlockEditor\BlockEditorAssets;
use WPDRMS\ASP\BlockEditor\BlockInterface;
use WPDRMS\ASP\Options\OptionAssets;
use WPDRMS\ASP\Options\Routes\SearchOptionsRoute;
use WPDRMS\ASP\Options\Routes\TaxonomyTermsRoute;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Rest\RestInterface;
use WPDRMS\ASP\Rest\TimedModalRoutes;

/**
 * @phpstan-type FactorySupports RestInterface|AssetInterface|BlockInterface
 * @phpstan-type FactoryResults FactorySupports[]
 */
class Factory {
	use SingletonTrait;

	const SUPPORTED_INTERFACES = array(
		RestInterface::class  => array(
			TimedModalRoutes::class,
			TaxonomyTermsRoute::class,
			SearchOptionsRoute::class,
		),
		AssetInterface::class => array(
			OptionAssets::class,
		),
		BlockInterface::class => array(
			ASPBlock::class,
		),
	);

	/**
	 * Get the objects from
	 *
	 * @template T of FactorySupports
	 * @param class-string<T>   $interface_name
	 * @param array<mixed>|null $args
	 * @return T[]
	 * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
	 */
	public function get( string $interface_name, ?array $args = null ): array {
		if ( !isset(self::SUPPORTED_INTERFACES[ $interface_name ]) ) {
			return array();
		}
		$classes = self::SUPPORTED_INTERFACES[ $interface_name ];
		return array_map(
			function ( $class_name ) use ( $args ) {
				if ( method_exists($class_name, 'instance') ) {
					if ( is_array($args) ) {
						return $class_name::instance(...$args);
					} else {
						return $class_name::instance();
					}
				}
				if ( is_array($args) ) {
					return new $class_name(...$args); // @phpstan-ignore-line
				} else {
					return new $class_name(); // @phpstan-ignore-line
				}
			},
			$classes
		);
	}
}
