<?php

namespace WPDRMS\ASP\Utils\Inflect;

use Doctrine\Inflector\InflectorFactory;
use WPDRMS\ASP\Patterns\SingletonTrait;

class InflectController {
	use SingletonTrait;

	/**
	 * Gets all inflections for the given language
	 *
	 * @param string[] $words
	 * @param string   $language
	 * @return string[]
	 */
	public function get( array $words, string $language = 'english' ): array {
		$inflector   = InflectorFactory::createForLanguage($language)->build();
		$inflections = array();
		foreach ( $words as $word ) {
			$singular = $inflector->singularize($word);
			$plural   = $inflector->pluralize($word);

			if ( $singular !== $word ) {
				$inflections[] = $singular;
			}

			if ( $plural !== $word ) {
				$inflections[] = $plural;
			}
		}

		return array_unique($inflections);
	}
}
