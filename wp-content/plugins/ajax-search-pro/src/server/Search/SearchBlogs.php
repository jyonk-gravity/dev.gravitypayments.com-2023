<?php
namespace WPDRMS\ASP\Search;

defined('ABSPATH') || die("You can't access this file directly.");

class SearchBlogs extends AbstractSearch {
	protected function doSearch(): void {
		$args = &$this->args;

		$sd = $args['_sd'] ?? array();

		/* There are no blog images available, return nothing for polaroid mode */
		if ( $args['_ajax_search'] && isset($sd['resultstype']) &&
			$sd['resultstype'] === 'polaroid' && $sd['pifnoimage'] === 'removeres' ) {
			return;
		}

		$s = $this->s;

		if ( $args['_limit'] > 0 ) {
			$limit = $args['_limit'];
		} elseif ( $args['_ajax_search'] ) {
			$limit = $args['blogs_limit'];
		} else {
			$limit = $args['blogs_limit_override'];
		}

		if ( $limit <= 0 ) {
			return;
		}

		$blogresults = array();

		$blog_list = wpdreams_get_blog_list(0, 'all');
		foreach ( $blog_list as $bk => $blog ) {
			if ( in_array($blog['blog_id'], $args['blog_exclude']) ) { // @phpcs:ignore
				unset($blog_list[ $bk ]);
			}
			$_det = get_blog_details($blog['blog_id']);
			if ( $_det === false ) {
				unset($blog_list[ $bk ]);
				continue;
			}
			$blog_list[ $bk ]['name']    = $_det->blogname;
			$blog_list[ $bk ]['siteurl'] = $_det->siteurl;
			$blog_list[ $bk ]['match']   = 0;
		}

		foreach ( $blog_list as $bk => $blog ) {
			$pos = strpos(strtolower($blog['name']), $s);
			if ( $pos !== false ) {
				$blog_list[ $bk ]['match'] = 1;
			}
		}

		foreach ( $blog_list as $blog ) {
			if ( $blog['match'] ) {
				switch_to_blog($blog['blog_id']);
				$blogresults[] = (object) array(
					'title'          => $blog['name'],
					'link'           => get_bloginfo('url'),
					'content'        => get_bloginfo('description'),
					'author'         => '',
					'date'           => '',
					'content_type'   => 'blog',
					'g_content_type' => 'blogs',
					'blogid'         => $blog['blog_id'],
					'id'             => $blog['blog_id'],
					'relevance'      => 1,
				);
				restore_current_blog();
			}
		}
		if ( $sd['blogtitleorderby'] === 'asc' ) {
			$blogresults = array_reverse($blogresults);
		}

		$this->results_count = count($blogresults);

		if ( !$args['_ajax_search'] && $this->results_count > $limit ) {
			$this->results_count = $limit;
		}

		$blogresults = array_slice($blogresults, $args['_call_num'] * $limit, $limit);

		$this->results      = $blogresults;
		$this->return_count = count($this->results);
	}

	protected function postProcess(): void {}
}
