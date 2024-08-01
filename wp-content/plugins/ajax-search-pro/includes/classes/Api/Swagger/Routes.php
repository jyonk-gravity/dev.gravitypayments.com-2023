<?php /** @noinspection PhpLanguageLevelInspection */

/**
 * Swagger related stuff, to make it easy all is in this directory.
 * This code is not used anywhere, it's only for swagger generator.
 */

namespace WPDRMS\ASP\Api\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
	version: "1.0",
	title: "Ajax Search Pro for WordPress"
)]
final class Routes {
	#[OA\Get(
		path: '/wp-json/ajax-search-pro/v0/search',
		description: 'Generic search',
		tags: ['search'],
		parameters: [
			new OA\Parameter(name: 's', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
			new OA\Parameter(name: 'id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
		],
		responses: [
			new OA\Response(
				response: 200,
				description: 'Results array.',
				content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'object', ref: WP_Post_ASP::class))
			),
		]
	)]
	public function searchRoute() {}


	#[OA\Get(
		path: '/wp-json/ajax-search-pro/v0/woo_search',
		description: 'WooCommerce Specific Search',
		tags: ['woocommerce'],
		parameters: [
			new OA\Parameter(name: 's', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
			new OA\Parameter(name: 'id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
		],
		responses: [
			new OA\Response(
				response: 200,
				description: 'Product results array.',
				content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'object', ref: WP_Post_ASP::class))
			),
		]
	)]
	public function wooSearchRoute() {}
}

