<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpd_termsKeywordSuggest')) {
	class wpd_termsKeywordSuggest extends wpd_keywordSuggestAbstract {

		function __construct($args = array()) {
			$defaults = array(
				'maxCount' => 10,
				'maxCharsPerWord' => 25,
				'taxonomy' => 'post_tag',
				'match_start' => false,
                'search_id' => 0
			);
			$args = wp_parse_args( $args, $defaults );

			$this->maxCount = $args['maxCount'];
			$this->maxCharsPerWord = $args['maxCharsPerWord'];
			$this->taxonomy = $args['taxonomy'];
			$this->matchStart = $args['match_start'];
            $this->searchID = $args['search_id'];
		}

		function getKeywords($q) {
			$res = array();

            $exclude = array();
            if ( $this->searchID > 0 ) {
                $search_args = ASP_Helpers::toQueryArgs($this->searchID, array());
                if ( !empty($search_args['taxonomy_terms_exclude']) ) {
                    if ( is_array($search_args['taxonomy_terms_exclude']) )
                        $exclude = $search_args['taxonomy_terms_exclude'];
                    else
                        $exclude = explode(',', $search_args['taxonomy_terms_exclude']);
                    foreach ($exclude as $k=>$v)
                        $exclude[$k] = trim($v);
                }
                if ( !empty($search_args['taxonomy_terms_exclude2']) ) {
                    $exclude = array_merge($exclude, $search_args['taxonomy_terms_exclude2']);
                }

                if ( isset($search_args['post_tax_filter']) ) {
                    foreach ($search_args['post_tax_filter'] as $filter) {
                        if ( $filter['taxonomy'] == $this->taxonomy ) {
                            $exclude = array_merge($exclude, $filter['exclude']);
                        }
                    }
                }

                if ( is_array($exclude) ) {
                    $exclude = implode(',', array_unique( $exclude) );
                }
            }

            if ( $this->matchStart ) {
                $tags = get_terms(array($this->taxonomy), array('name__like' => $q, 'number' => $this->maxCount, 'hide_empty' => false, 'exclude' => $exclude));
                foreach ($tags as $tag) {
                    if (!is_object($tag)) continue;
                    $t = ASP_mb::strtolower($tag->name);
                    $q = ASP_mb::strtolower($q);
                    if (
                        $t != $q &&
                        ('' != $str = wd_substr_at_word($t, $this->maxCharsPerWord))
                    ) {
                        if ( ASP_mb::strpos($t, $q) === 0 )
                            $res[] = $str;
                    }
                }
            } else {
                $tags = wpd_get_terms(array(
                    'taxonomy' => $this->taxonomy,
                    'fields' => 'names',
                    'number' => 25000,
                    'hide_empty' => false,
                    'exclude' => $exclude
                ));
                if ( !is_wp_error($tags) && count($tags) > 0 )
                    $res = ASP_Helpers::get_similar_text($tags, $q, $this->maxCount);
            }
			return $res;
		}

	}
}