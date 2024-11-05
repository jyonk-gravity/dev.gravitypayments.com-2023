<?php /** @noinspection PhpUnused */

namespace WPDRMS\ASP\Models;

use ArrayAccess;
use WPDRMS\ASP\Patterns\ObjectAsArrayTrait;

/**
 * Search Query Arguments via ArrayAccess to solve type checks, but also
 * allows access to arguments as an array for backwards compatibility.
 *
 * Ex.:
 * $args = new SearchQueryArgs(['property1'=>'value1']);
 * $args->property1 === $args['property1'] (true)
 *
 * @implements ArrayAccess<string,mixed>
 *
 * @phpstan-type QueryFields array{
 *      fields?: string,
 *      join?: string,
 *      where?: string,
 *      orderby?: string,
 *      groupby?: string,
 *  }
 */
class SearchQueryArgs implements ArrayAccess {
	use ObjectAsArrayTrait;

	// ----------------------------------------------------------------
	// Constants for typings
	// ----------------------------------------------------------------
	const SUPPORTED_SEARCH_TYPES = array(
		'cpt',
		'taxonomies',
		'users',
		'blogs',
		'buddypress',
		'comments',
		'attachments',
		'peepso_groups',
		'peepso_activities',
	);

	const SUPPORTED_ENGINES = array(
		'regular',
		'index',
	);

	const SUPPORTED_KEYWORD_LOGICS = array(
		'and',
		'or',
		'andex',
		'orex',
	);

	const SUPPORTED_POST_FIELDS = array(
		'ids',
		'title',
		'content',
		'excerpt',
		'terms',
		'permalink',
	);

	const SUPPORTED_PRIMARY_ORDERINGS = array(
		'relevance DESC',
		'post_title DESC',
		'post_title ASC',
		'post_date DESC',
		'post_date ASC',
		'post_modified DESC',
		'post_modified ASC',
		'id DESC',
		'id ASC',
		'menu_order DESC',
		'menu_order ASC',
		'RAND()',
		'customfp DESC',
		'customfp ASC',
	);

	const SUPPORTED_SECONDARY_ORDERINGS = array(
		'relevance DESC',
		'post_title DESC',
		'post_title ASC',
		'post_date DESC',
		'post_date ASC',
		'post_modified DESC',
		'post_modified ASC',
		'id DESC',
		'id ASC',
		'menu_order DESC',
		'menu_order ASC',
		'RAND()',
		'customfs DESC',
		'customfs ASC',
	);

	const SUPPORTED_USER_PRIMARY_ORDERINGS = array(
		'relevance DESC',
		'title DESC',
		'title ASC',
		'date DESC',
		'date ASC',
		'id DESC',
		'id ASC',
		'menu_order DESC',
		'menu_order ASC',
		'RAND()',
		'customfp DESC',
		'customfp ASC',
	);

	const SUPPORTED_USER_SECONDARY_ORDERINGS = array(
		'relevance DESC',
		'title DESC',
		'title ASC',
		'date DESC',
		'date ASC',
		'id DESC',
		'id ASC',
		'menu_order DESC',
		'menu_order ASC',
		'RAND()',
		'customfs DESC',
		'customfs ASC',
	);
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// GENERIC arguments
	// ----------------------------------------------------------------
	/**
	 * @var string Search phrase
	 */
	public string $s = '';

	/**
	 * @var array<value-of<self::SUPPORTED_SEARCH_TYPES>>
	 */
	public array $search_type = array( 'cpt' );


	/**
	 * @var value-of<self::SUPPORTED_ENGINES>
	 */
	public string $engine = 'regular';

	/**
	 * Posts per page, for non ajax requests only. If 0, then get_option(posts_per_page) should be used.
	 *
	 * @var int
	 */
	public int $posts_per_page = 0;

	/**
	 * Current page of results, starts from 1
	 *
	 * @var int<1, max>
	 */
	public int $page = 1;

	/**
	 * @var value-of<self::SUPPORTED_KEYWORD_LOGICS>
	 */
	public string $keyword_logic = 'and';

	/**
	 * @var value-of<self::SUPPORTED_KEYWORD_LOGICS>|''
	 */
	public string $secondary_logic = '';

	/**
	 * Minimum word length of each word to be considered as a standalone word in the phrase (removed if shorter)
	 *
	 * @var int
	 */
	public int $min_word_length = 2;
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// POST and CUSTOM POST TYPE related arguments
	// ----------------------------------------------------------------
	/**
	 * @var string[]
	 */
	public array $post_type = array( 'post', 'page' );

	/**
	 * @var string[]
	 */
	public array $post_status = array( 'publish' );
	public bool $has_password = false;

	/**
	 * @var array<value-of<self::SUPPORTED_POST_FIELDS>>
	 */
	public array $post_fields = array(
		'title',
		'ids',
		'excerpt',
		'terms',
	);

	public bool $post_custom_fields_all = false;

	/**
	 * When $post_custom_fields_all == true, this is ignored
	 *
	 * @var string[]
	 */
	public array $post_custom_fields = array();

	/**
	 * @var int[]
	 */
	public array $post_in = array();

	/**
	 * @var int[]
	 */
	public array $post_not_in = array();

	/**
	 * Secondary exclusion for manual override
	 *
	 * @var int[]
	 */
	public array $post_not_in2 = array();

	/**
	 * @var int[]
	 */
	public array $post_parent = array();

	/**
	 * @var int[]
	 */
	public array $post_parent_exclude = array();           // array -> post parent IDs

	/**
	 * Taxonomy filter support
	 *
	 * @var array{
	 *     taxonomy: string,
	 *     include?: int[],
	 *     exclude?: int[],
	 *     allow_empty: boolean
	 * }[]
	 */
	public array $post_tax_filter = array(                 // taxonomy filter support
		/*
		Example:
		array(
			'taxonomy'    => 'category',          // taxonomy name
			'include'     => array(1, 2, 3, 4),   // array of taxonomy term IDs to include
			'exclude'     => array(5, 6, 7, 8),   // array of taxonomy term IDs to exclude
			'allow_empty' => false                // allow (empty) items with no connection to any of the taxonomy terms filter
		)
		*/
	);

	/**
	 * Logic connecting post_tax_filter groups
	 *
	 * @var "AND"|"OR"
	 */
	public string $_taxonomy_group_logic = 'AND';

	/**
	 * Meta Query Support
	 *
	 * @var array{
	 *     key: string,
	 *     value: mixed,
	 *     operator: '<'|'>'|"<>"|"="|'BETWEEN'|'LIKE'|'NOT LIKE'|'IN',
	 *     allow_missing: bool
	 * }[]
	 */
	public array $post_meta_filter = array(
		/*
		Example:
		array(
			'key'     => 'age',         // meta key
			'value'   => array( 3, 4 ), // int|float|string|array|timestamp|datetime
			// @param string|array compare
			// Numeric Operators
			//      '<' -> less than
			//      '>' -> more than
			//      '<>' -> not equals
			//      '=' -> equals
			//      'BETWEEN' -> between two values
			// String Operators
			//      'LIKE'
			//      'NOT LIKE'
			//      'IN'
			'operator' => 'BETWEEN',
			'allow_missing' => false   // allow match if this custom field is unset
		)
		*/
	);

	/**
	 * Logic connecting post_meta_filter groups
	 *
	 * @var "OR"|"AND"
	 */
	public string $post_meta_filter_logic = 'AND';

	/**
	 * Should results which does not have the custom field attached to them be included
	 *
	 * @var bool
	 */
	public bool $post_meta_allow_missing = false;

	/**
	 * Date Query Support
	 *
	 * @var array{
	 *     year: int,
	 *     month: int<1,12>,
	 *     day: int<1,31>,
	 *     operator: 'include'|'exclude',
	 *     interval: 'before'|'after'
	 * }|array{
	 *     date: string,
	 *     operator: 'include'|'exclude',
	 *     interval: 'before'|'after'
	 * }|array{}
	 */
	public array $post_date_filter = array(
		/*
		Example:
		array(
			'year'  => 2015,            // year, month, day ...
			'month' => 6,
			'day'   => 1,
			'date'  => "2015-06-01",     // .. or date parameter in y-m-d format
			'operator' => 'include',    // include|exclude
			'interval' => 'before'      // before|after
		)
		*/
	);

	/**
	 * @var array{
	 *     include?:int[],
	 *     exclude?:int[]
	 * }
	 */
	public array $post_user_filter = array(
		/*
		'include' => (1, 2, 3, 4),  // include by IDs
		'exclude' => (5, 6, 7, 8)   // exclude by IDs
		*/
	);

	/**
	 * @var value-of<self::SUPPORTED_PRIMARY_ORDERINGS>
	 */
	public string $post_primary_order = 'relevance DESC';

	/**
	 * @var value-of<self::SUPPORTED_SECONDARY_ORDERINGS>
	 */
	public string $post_secondary_order = 'post_date DESC';

	/**
	 * @var ''|'numeric'|'string'
	 */
	public string $post_primary_order_metatype = '';

	/**
	 * @var ''|'numeric'|'string'
	 */
	public string $post_secondary_order_metatype = '';


	// ----------------------------------------------------------------
	// 3. ATTACHMENT search related arguments
	// ----------------------------------------------------------------
	/**
	 * @var bool
	 */
	public bool $attachments_use_index      = false;
	public bool $attachments_search_title   = true;
	public bool $attachments_search_content = true;
	public bool $attachments_search_caption = true;
	public bool $attachments_search_terms   = false;
	public bool $attachments_search_ids     = true;
	public bool $attachments_cf_filters     = true;
	public bool $attachment_use_image       = true;

	/**
	 * @var string[]
	 */
	public array $attachment_mime_types = array( 'image/jpeg', 'image/gif', 'image/png', 'image/tiff', 'image/x-icon' );

	/**
	 * @var int[]
	 */
	public array $attachment_exclude = array();

	/**
	 * @var "page"|"file"|"parent"
	 */
	public string $attachment_link_to = 'page';

	/**
	 * Only applies if "attachment_link_to" is set to "parent"
	 *
	 * @var "page"|"file"
	 */
	public string $attachment_link_to_secondary = 'page';
	public bool $attachment_pdf_image           = false;
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// BUDDYPRESS related arguments
	// ----------------------------------------------------------------
	/**
	 * @var bool
	 */
	public bool $bp_groups_search         = false;
	public bool $bp_groups_search_public  = true;
	public bool $bp_groups_search_private = true;
	public bool $bp_groups_search_hidden  = true;
	public bool $bp_activities_search     = true;
	// ----------------------------------------------------------------


	// ----------------------------------------------------------------
	// TAXONOMY TERM search related arguments
	// ----------------------------------------------------------------
	/**
	 * @var string[]
	 */
	public array $taxonomy_include = array( 'category', 'post_tag' );

	/**
	 * @var int[]
	 */
	public array $taxonomy_terms_exclude = array();

	public bool $taxonomy_terms_exclude_empty = false;

	/**
	 * @var int[]
	 */
	public array $taxonomy_terms_exclude2     = array();
	public bool $taxonomy_terms_search_titles = true;

	public bool $taxonomy_terms_search_description = true;
	public bool $taxonomy_terms_search_term_meta   = false;
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// USER SEARCH
	// ----------------------------------------------------------------
	/**
	 * @var bool
	 */
	public bool $user_login_search        = true;
	public bool $user_display_name_search = true;
	public bool $user_first_name_search   = true;
	public bool $user_last_name_search    = true;
	public bool $user_bio_search          = true;
	public bool $user_email_search        = false;

	/**
	 * @var string[]
	 */
	public array $user_search_meta_fields = array();

	/**
	 * @var string[]
	 */
	public array $user_search_bp_fields = array();

	/**
	 * @var string[]
	 */
	public array $user_search_exclude_roles = array();

	/**
	 * @var int[]
	 */
	public array $user_search_exclude_ids = array();

	/**
	 * @var int[]
	 */
	public array $user_search_include_ids = array();

	/**
	 * User Meta Query Support
	 *
	 * @var array{
	 *     key: string,
	 *     value: mixed,
	 *     operator: '<'|'>'|"<>"|"="|'BETWEEN'|'LIKE'|'NOT LIKE'|'IN',
	 *     allow_missing: bool
	 * }[]
	 */
	public array $user_meta_filter = array(      // meta_query support
		/*
		Example:
		array(
			'key'     => 'age',         // meta key
			'value'   => array( 3, 4 ), // mixed|array
			// @param string|array compare
			// Numeric Operators
			//      '<' -> less than
			//      '>' -> more than
			//      '<>' -> not equals
			//      '=' -> equals
			//      'BETWEEN' -> between two values
			// String Operators
			//      'LIKE'
			//      'NOT LIKE'
			//      'IN'
			//
			'operator' => 'BETWEEN',
			'allow_missing' => false   // allow match if this custom field is unset
		)
		*/
	);


	/**
	 * @var value-of<self::SUPPORTED_USER_PRIMARY_ORDERINGS>
	 */
	public string $user_primary_order = 'relevance DESC';

	/**
	 * @var value-of<self::SUPPORTED_USER_SECONDARY_ORDERINGS>
	 */
	public string $user_secondary_order = 'date DESC';

	/**
	 * @var ''|'numeric'|'string'
	 */
	public string $user_primary_order_metatype = '';

	/**
	 * @var ''|'numeric'|'string'
	 */
	public string $user_secondary_order_metatype = '';
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// BLOG NAME SEARCH
	// ----------------------------------------------------------------
	/**
	 * @var int[]
	 */
	public array $blog_exclude = array();
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// Peepso Group & Activity
	// ----------------------------------------------------------------

	/**
	 * @var array<int<1,3>>
	 */
	public array $peepso_group_privacy = array();

	/**
	 * @var array<'title'|'content'|'categories'>
	 */
	public array $peepso_group_fields = array();

	/**
	 * @var array<int<1,3>>
	 */
	public array $peepso_group_not_in = array();

	/**
	 * @var array<'peepso-post'|'peepso-comment'>
	 */
	public array $peepso_activity_types = array();

	/**
	 * @var array<int<0,2>>
	 */
	public array $peepso_group_activity_privacy = array();
	public bool $peepso_activity_follow         = false;

	/**
	 * @var int[]
	 */
	public array $peepso_activity_not_in = array();
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// 10. QUERY FIELDS
	// ----------------------------------------------------------------
	/**
	 * @var QueryFields
	 */
	public array $cpt_query = array(
		'fields'  => '',
		'join'    => '',
		'where'   => '',
		'orderby' => '',
		'groupby' => '',
	);

	/**
	 * @var QueryFields
	 */
	public array $term_query = array(
		'fields'  => '',
		'join'    => '',
		'where'   => '',
		'orderby' => '',
		'groupby' => '',
	);

	/**
	 * @var QueryFields
	 */
	public array $user_query = array(
		'fields'  => '',
		'join'    => '',
		'where'   => '',
		'orderby' => '',
		'groupby' => '',
	);

	/**
	 * @var QueryFields
	 */
	public array $attachment_query = array(
		'fields'  => '',
		'join'    => '',
		'where'   => '',
		'orderby' => '',
		'groupby' => '',
	);

	/**
	 * @var QueryFields
	 */
	public array $buddypress_groups_query = array(
		'fields'  => '',
		'join'    => '',
		'where'   => '',
		'orderby' => '',
		'groupby' => '',
	);

	/**
	 * @var QueryFields
	 */
	public array $buddypress_activities_query = array(
		'fields'  => '',
		'join'    => '',
		'where'   => '',
		'orderby' => '',
		'groupby' => '',
	);
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// INDEX TABLE SEARCH
	// ----------------------------------------------------------------
	/**
	 * @var int
	 */
	public int $it_pool_size_one   = 500;
	public int $it_pool_size_two   = 800;
	public int $it_pool_size_three = 2000;
	public int $it_pool_size_rest  = 2000;
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// SEARCH RESULTS LIMITS
	// {source}_limit -> live search
	// {source}_limit_override -> results page limit
	// ----------------------------------------------------------------
	/**
	 * @var int
	 */
	public int $posts_limit                  = 10;
	public int $posts_limit_override         = 50;
	public bool $posts_limit_distribute      = false;
	public int $taxonomies_limit             = 10;
	public int $taxonomies_limit_override    = 20;
	public int $users_limit                  = 10;
	public int $users_limit_override         = 20;
	public int $blogs_limit                  = 10;
	public int $blogs_limit_override         = 20;
	public int $buddypress_limit             = 10;
	public int $buddypress_limit_override    = 20;
	public int $comments_limit               = 10;
	public int $comments_limit_override      = 20;
	public int $attachments_limit            = 10;
	public int $attachments_limit_override   = 20;
	public int $peepso_groups_limit          = 10;
	public int $peepso_groups_limit_override = 20;
	// ----------------------------------------------------------------

	// ----------------------------------------------------------------
	// MULTI-LANGUAGE
	// ----------------------------------------------------------------
	/**
	 * @var string
	 */
	public string $_qtranslate_lang = 'en';
	public string $_wpml_lang       = '';

	/**
	 * If true, then items without translations are also included
	 *
	 * @var bool
	 */
	public bool $_wpml_allow_missing_translations = true;
	public string $_polylang_lang                 = '';
	// ----------------------------------------------------------------


	// ----------------------------------------------------------------
	// MISC
	// ----------------------------------------------------------------
	/**
	 * Number of words in the search phrase allowed
	 *
	 * @var int
	 */
	public int $_keyword_count_limit = 6;

	/**
	 * Enable/Disable exact matches. When true, regular engine is used automatically.
	 *
	 * @var bool
	 */
	public bool $_exact_matches = false;

	/**
	 * Where the exact matches should apply
	 *
	 * @var "anywhere"|"start"|"end"
	 */
	public string $_exact_match_location = 'anywhere';

	/**
	 * @var int[]
	 */
	public array $_exclude_page_parent_child = array();

	/**
	 * Forced case sensitivity
	 *
	 * @var "none"|"sensitivity"|"insensitivity"
	 */
	public string $_db_force_case = 'none';

	/**
	 * Forced UTF8 LIKE queries
	 *
	 * @var bool
	 */
	public bool $_db_force_utf8_like = false;

	/**
	 * Force UNICODE LIKE queries
	 *
	 * @var bool
	 */
	public bool $_db_force_unicode = false;

	/**
	 * Post processing includes image/advanced content fields etc.. processing
	 *
	 * @var bool
	 */
	public bool $_post_process = true;

	/**
	 * Current Page ID
	 *
	 * @var int
	 */
	public int $_page_id = 0;

	/**
	 * Remaining Limit Modifier
	 *
	 * This is used mostly for more results overall limit.
	 * Overall Limit = LIMIT * _remaining_limit_mod
	 *
	 * @var int
	 */
	public int $_remaining_limit_mod = 1000;

	/**
	 * Only via AJAX - if the filters have been touched by the user
	 *
	 * @var bool
	 */
	public bool $filters_changed = false;

	/**
	 * Only via AJAX - if the filters are on the initial state
	 *
	 * @var bool
	 */
	public bool $filters_initial = true;

	/**
	 * @var int[]
	 */
	public array $_selected_blogs = array();

	// ----------------------------------------------------------------
	// DO NOT TOUCH SECTION BELOW
	// The variables below are set during execution
	// phpcs:disable
	// ----------------------------------------------------------------
	/**
	 * Search instance ID
	 *
	 * @var int
	 */
	public int $_id = -1;

	/**
	 * Search instance data
	 *
	 * @var array<string, mixed>
	 */
	public array $_sd = array();

	/**
	 * Overall results limit, if >=0, then evenly distributed between sources
	 *
	 * @var int
	 */
	public int $limit = 0;

	/**
	 * Calculated limit for the next search source based on the previous limit parameter
	 *
	 * @var int
	 */
	public int $_limit = 0;

	/**
	 * Number of posts found overall at any point on mixed search queries
	 *
	 * @var int
	 */
	public int $global_found_posts = 0;

	/**
	 * Needs to be set explicitly to true in search Ajax Handler class
	 *
	 * @var bool
	 */
	public bool $_ajax_search = false;

	/**
	 * True when the query is initiated via a REST API call (non-ajax)
	 *
	 * @var bool
	 */
	public bool $is_wp_json = false;

	/**
	 * Number of the consecutive ajax requests with the same configuration triggered by
	 * clicking on the 'More results...' link
	 * This is required to calculate the correct start of the result slicing
	 *
	 * @var int
	 */
	public int $_call_num                        = 0;

	/**
	 * Show more results feature enabled (only used via ajax search instance)
	 *
	 * @var bool
	 */
	public bool $_show_more_results = false;

	public bool $_is_autopopulate                 = false;

	public int $_charcount                        = 0;
	public string $_post_primary_order_metakey   = '';
	public string $_post_secondary_order_metakey = '';
	public bool $_post_get_content               = false;
	public bool $_post_get_excerpt               = false;
	public bool $_post_allow_empty_tax_term      = false;
	public bool $_post_use_relevance             = true;
	public bool $_post_tags_active               = false;
	public bool $_post_tags_empty                = false;
	public string $_user_primary_order_metakey   = '';
	public string $_user_secondary_order_metakey = '';

	public string $woo_currency                  = '';

	public bool   $_switch_on_preprocess         = false;
	// phpcs:enable
	// ----------------------------------------------------------------
}
