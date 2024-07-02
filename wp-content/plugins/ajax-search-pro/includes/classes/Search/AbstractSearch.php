<?php
namespace WPDRMS\ASP\Search;

use stdClass;
use WPDRMS\ASP\Models\SearchQueryArgs;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') || die("You can't access this file directly.");

/**
 * Search Abstract
 *
 * @phpstan-type ResultObj stdClass&object{
 *     id: int,
 *     blogid: int,
 *     relevance: int
 * }
 *
 * @phpstan-type QueryParts array<int, array<int, array<string>>>
 */
abstract class AbstractSearch {
	/**
	 * @var int Total results count (unlimited)
	 */
	public int $results_count = 0;

	/**
	 * @var int Actual results count, results which are returned
	 */
	public int $return_count = 0;

	/**
	 * @var SearchQueryArgs Parameters
	 */
	protected SearchQueryArgs $args;

	protected string $pre_field = '';
	protected string $suf_field = '';
	protected string $pre_like  = '';
	protected string $suf_like  = '';

	/**
	 * @var int the remaining limit (number of items to look for)
	 */
	protected int $remaining_limit;
	/**
	 * @var int the start of the limit
	 */
	protected int $limit_start = 0;
	/**
	 * @var int remaining limit modifier
	 */
	protected int $remaining_limit_mod = 10;

	/**
	 * @var array<string, mixed> Submitted options from the front end
	 */
	protected array $options;

	/**
	 * @var ResultObj[] Results
	 */
	protected array $results = array();
	/**
	 * @var string The search phrase
	 */
	protected string $s;
	/**
	 * @var string[] Each search phrase
	 */
	protected array $_s; // @phpcs:ignore
	/**
	 * @var string The reversed search phrase
	 */
	protected string $sr;
	/**
	 * @var string[] Each reversed search phrase
	 */
	protected array $_sr; // @phpcs:ignore

	/**
	 * @var int the current blog ID
	 */
	protected int $c_blogid;
	/**
	 * @var string the final search query
	 */
	protected string $query;

	/**
	 * @param SearchQueryArgs $args Parameters
	 */
	public function __construct( SearchQueryArgs $args ) {
		$this->args = $args;

		if ( isset($args['_remaining_limit_mod']) ) {
			$this->remaining_limit_mod = $args['_remaining_limit_mod'];
		}

		$this->c_blogid = get_current_blog_id();
	}

	/**
	 * Initiates the search operation
	 *
	 * @param string $s Search Phrase
	 * @return ResultObj[]
	 */
	public function search( string $s = '' ): array {

		if ( MB::strlen($s) > 120 ) {
			$s = MB::substr($s, 0, 120);
		}

		$this->prepareKeywords($s);
		$this->doSearch();
		$this->postProcess();

		return $this->results;
	}

	/**
	 * @param null|string $s Search Phrase
	 * @return void
	 */
	public function prepareKeywords( ?string $s ): void {

		if ( $s !== null ) {
			$keyword = $s;
		} else {
			$keyword = $this->args['s'];
		}

		// This is the "japanese" ideographic space character, replaced by the regular space
		$keyword = preg_replace('@[ 　]@u', ' ', $keyword);

		$keyword     = $this->compatibility($keyword);
		$keyword_rev = Str::reverse($keyword);

		$this->s  = Str::escape( $keyword );
		$this->sr = Str::escape( $keyword_rev );

		/**
		 * Avoid double escape, explode the $keyword instead of $this->s
		 * Regex to match individual words and phrases between double quotes
		 */
		if (
			preg_match_all( '/«.*?»/', $keyword, $m ) > 0 && // Only if there is at lease one complete «text» match
			preg_match_all( '/«.*?(»|$)|((?<=[\t «,+])|^)[^\t »,+]+/', $keyword, $matches )
		) {
			$this->_s = $this->parseSearchTerms(  $matches[0] );
		} elseif (
			preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $keyword, $matches )
		) {
			$this->_s = $this->parseSearchTerms(  $matches[0] );
		} else {
			$this->_s = $this->parseSearchTerms( explode(' ', $keyword) );
		}
		if (
			preg_match_all( '/«.*?»/', $keyword_rev, $m ) > 0 && // Only if there is at lease one complete «text» match
			preg_match_all( '/«.*?(»|$)|((?<=[\t «,+])|^)[^\t »,+]+/', $keyword_rev, $matches )
		) {
			$this->_sr = $this->parseSearchTerms(  array_reverse($matches[0]) );
		} elseif ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $keyword_rev, $matches ) ) {
			$this->_sr = $this->parseSearchTerms(  array_reverse($matches[0]) );
		} else {
			$this->_sr = $this->parseSearchTerms( array_reverse( explode(' ', $keyword_rev ) ) );
		}

		foreach ( $this->_s as $k =>$w ) {
			if ( MB::strlen($w) < $this->args['min_word_length'] ) {
				unset($this->_s[ $k ]);
			}
		}

		foreach ( $this->_sr as $k =>$w ) {
			if ( MB::strlen($w) < $this->args['min_word_length'] ) {
				unset($this->_sr[ $k ]);
			}
		}

		/**
		 * Reindex the arrays, in case there are missing keys from the previous removals
		 */
		if ( count($this->_s) > 0 ) {
			$this->_s  = array_values($this->_s);
			$this->_sr = array_values($this->_sr);
		}
	}

	/**
	 * Check if the terms are suitable for searching.
	 *
	 * @param string[] $terms Terms to check.
	 * @return string[] Terms
	 */
	protected function parseSearchTerms( array $terms ): array {
		$checked = array();

		foreach ( $terms as $term ) {
			// keep before/after spaces when term is for exact match
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim($term, "\"'");
			} elseif ( preg_match( '/^«.+»$/', $term ) ) { // same for russian quotes
				$term = trim($term, "«»'");
			} else {
				$term = trim($term, "\"' ");
			}
			if ( $term !== '' ) {
				$checked[] = $term;
			}
		}

		if ( count($checked) > 0 ) {
			$checked = Str::escape(
				array_slice(array_unique($checked), 0, $this->args['_keyword_count_limit'])
			);
		}

		return $checked;
	}

	/**
	 * The search function
	 */
	abstract protected function doSearch(): void;

	/**
	 * Post-processing abstract
	 */
	protected function postProcess(): void {}

	/**
	 * Converts the keyword to the correct case and sets up the pre-suff fields.
	 *
	 * @param string $s
	 * @return string
	 */
	protected function compatibility( string $s ): string {
		/**
		 *  On forced case sensitivity: Let's add BINARY keyword before the LIKE
		 *  On forced case in-sensitivity: Append the lower() function around each field
		 */
		if ( $this->args['_db_force_case'] === 'sensitivity' ) {
			$this->pre_like = 'BINARY ';
		} elseif ( $this->args['_db_force_case'] === 'insensitivity' ) {
			if ( function_exists( 'mb_convert_case' ) ) {
				$s = mb_convert_case( $s, MB_CASE_LOWER, 'UTF-8' );
			} else {
				$s = strtoupper( $s );
			}
			// if no mb_ functions :(

			$this->pre_field .= 'lower(';
			$this->suf_field .= ')';
		}

		/**
		 *  Check if utf8 is forced on LIKE
		 */
		if ( $this->args['_db_force_utf8_like'] ) {
			$this->pre_like .= '_utf8';
		}

		/**
		 *  Check if unicode is forced on LIKE, but only apply if utf8 is not
		 */
		if ( $this->args['_db_force_unicode'] && !$this->args['_db_force_utf8_like'] ) {
			$this->pre_like .= 'N';
		}

		return $s;
	}

	/**
	 * Builds the query from the parts
	 *
	 * @param QueryParts $parts
	 *
	 * @return string query
	 */
	protected function buildQuery( array $parts ): string {

		$args     = &$this->args;
		$kw_logic = str_replace('ex', '', $args['keyword_logic'] );
		$kw_logic = $kw_logic !== 'and' && $kw_logic !== 'or' ? 'and' : $kw_logic;

		$r_parts = array(); // relevance parts

		/*------------------------- Build like --------------------------*/
		$exact_query    = '';
		$like_query_arr = array();
		foreach ( $parts as $k =>$part ) {
			if ( $k === 0 ) {
				$exact_query = '(' . implode(' OR ', $part[0]) . ')';
			} else {
				$like_query_arr[] = '(' . implode(' OR ', $part[0]) . ')';
			}
		}
		$like_query = implode(' ' . strtoupper($kw_logic) . ' ', $like_query_arr);

		// When $exact query is empty, then surely $like_query must be empty too, see above
		if ( $exact_query === '' ) {
			$like_query = '(1)';
		} elseif ( $like_query !== '' ) {
			$like_query = "( $exact_query OR $like_query )";
		} else {
			$like_query = "( $exact_query )";
		}
		/*---------------------------------------------------------------*/

		/*---------------------- Build relevance ------------------------*/
		foreach ( $parts as $part ) {
			if ( isset($part[1]) && count($part[1]) > 0 ) {
				$r_parts = array_merge( $r_parts, $part[1] );
			}
		}
		$relevance = implode( ' + ', $r_parts );
		if ( !$args['_post_use_relevance'] || $relevance === '' ) {
			$relevance = '(1)';
		} else {
			$relevance = "($relevance)";
		}
		/*---------------------------------------------------------------*/

		if ( isset($this->remaining_limit) ) {
			if ( $this->limit_start !== 0 ) {
				$limit = $this->limit_start . ', ' . $this->remaining_limit;
			} else {
				$limit = $this->remaining_limit;
			}
		} else {
			$limit = 10;
		}

		return str_replace(
			array( '{relevance_query}', '{like_query}', '{remaining_limit}' ),
			array( $relevance, $like_query, $limit ),
			$this->query
		);
	}
}
