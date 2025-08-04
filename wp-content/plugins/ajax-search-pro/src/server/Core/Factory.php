<?php

namespace WPDRMS\ASP\Core;

use WPDRMS\ASP\Asset\AssetInterface;
use WPDRMS\ASP\BlockEditor\ASPBlock;
use WPDRMS\ASP\BlockEditor\BlockEditorAssets;
use WPDRMS\ASP\BlockEditor\BlockInterface;
use WPDRMS\ASP\Integration\Imagely\NextGenGallery;
use WPDRMS\ASP\Options\OptionAssets;
use WPDRMS\ASP\Options\Routes\DirectoriesRoute;
use WPDRMS\ASP\Options\Routes\IndexTableOptionsRoute;
use WPDRMS\ASP\Options\Routes\SearchOptionsRoute;
use WPDRMS\ASP\Options\Routes\TaxonomyTermsRoute;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Rest\RestInterface;
use WPDRMS\ASP\Rest\TimedModalRoutes;

/**
 * Returns all class instances for a given interface name
 *
 * @see .phpstorm.meta.php for corrected type hints
 */
class Factory {
	use SingletonTrait;

	const SUPPORTED_INTERFACES = array(
		'Rest'        => array(
			TimedModalRoutes::class,
			DirectoriesRoute::class,
			TaxonomyTermsRoute::class,
			SearchOptionsRoute::class,
			IndexTableOptionsRoute::class,
		),
		'Asset'       => array(
			OptionAssets::class,
		),
		'Block'       => array(
			ASPBlock::class,
		),
		'Integration' => array(
			NextGenGallery::class,
		),
	);

	/**
	 * Get all the objects array for a given interface
	 *
	 * @param key-of<self::SUPPORTED_INTERFACES> $interface_name
	 * @param mixed[]                            $args
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
