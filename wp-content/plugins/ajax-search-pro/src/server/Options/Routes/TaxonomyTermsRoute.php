<?php

namespace WPDRMS\ASP\Options\Routes;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WPDRMS\ASP\Rest\AbstractRest;

class TaxonomyTermsRoute extends AbstractRest {
	public function registerRoutes(): void {
		register_rest_route(
			ASP_DIR,
			'options/taxonomies/get',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getTaxonomies',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);
		register_rest_route(
			ASP_DIR,
			'options/taxonomy_terms/get',
			array(
				'methods'             => 'GET',
				'callback'            => array(
					$this,
					'getTerms',
				),
				'permission_callback' => array(
					$this,
					'allowOnlyAdmins',
				),
			)
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function getTaxonomies( WP_REST_Request $request ): WP_REST_Response {
		try {
			$taxonomies = get_taxonomies(array(), 'objects');
			return new WP_REST_Response(
				$taxonomies,
				is_wp_error($taxonomies) ? 500 : 200
			);
		} catch ( \Exception $e ) {
			return new WP_REST_Response(
				new WP_Error('taxonomies_get', $e->getMessage()),
				400
			);
		}
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function getTerms( WP_REST_Request $request ): WP_REST_Response {
		try {
			$taxonomy = $request->get_param('taxonomy');
			$term_ids = $request->get_param('term_ids');
			$terms    = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'include'    => $term_ids,
					'hide_empty' => false,
				)
			);
			return new WP_REST_Response(
				$terms,
				is_wp_error($terms) ? 500 : 200
			);
		} catch ( \Exception $e ) {
			return new WP_REST_Response(
				new WP_Error('taxonomy_terms_get', $e->getMessage()),
				400
			);
		}
	}
}
