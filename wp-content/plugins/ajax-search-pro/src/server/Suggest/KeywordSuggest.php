<?php
namespace WPDRMS\ASP\Suggest;

defined('ABSPATH') or die("You can't access this file directly.");

class KeywordSuggest extends AbstractSuggest {

	private $suggest;
	private array $classes = array(
		'google'        => 'Google',
		'google_places' => 'GooglePlaces',
		'statistics'    => 'Statistics',
		'titles'        => 'PostTypeTitles',
		'tags'          => 'TaxonomyTerms',
		'terms'         => 'TaxonomyTerms',
	);

	public function __construct( string $source, $args ) {
		$args['taxonomy'] = $source === 'tags' ? 'post_tag' : $args['taxonomy'];
		$source           = $source === 'tags' ? 'terms' : $source;
		$class            = __NAMESPACE__ . '\\Suggest' . $this->classes[ $source ];
		$args             = apply_filters('asp/suggestions/args', $args, $source);
		$this->suggest    = new $class($args);
	}

	public function getKeywords( string $q ): array {
		return apply_filters('asp/suggestions/keywords', $this->suggest->getKeywords($q), $q);
	}
}
