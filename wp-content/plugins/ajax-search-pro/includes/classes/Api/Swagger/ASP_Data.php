<?php /** @noinspection PhpLanguageLevelInspection */

namespace WPDRMS\ASP\Api\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema]
class ASP_Data {
	#[OA\Property]
	/**
	 * Title including advanced title field data
	 */
	public string $title;

	#[OA\Property]
	/**
	 * Result ID
	 */
	public int $id;

	#[OA\Property]
	/**
	 * Result blog ID
	 */
	public ?int $blogid;

	#[OA\Property]
	/**
	 * Result blog ID
	 */
	public string $date;

	#[OA\Property]
	/**
	 * Content including advanced title field data
	 */
	public string $content;

	#[OA\Property]
	/**
	 * Excerpt data
	 */
	public ?string $excerpt;

	#[OA\Property]
	/**
	 * Post type
	 */
	public ?string $post_type;

	#[OA\Property]
	/**
	 * Content type
	 *
	 * @var "pagepost"|"user"|"term"|"blog"|"bp_group"|"bp_activity"|"comment"
	 */
	public string $content_type;

	#[OA\Property]
	/**
	 * Author
	 */
	public ?string $author;

	#[OA\Property]
	/**
	 * URL of the search result when search ID is not set
	 */
	public ?string $asp_guid;

	#[OA\Property]
	/**
	 * URL of the search result when search ID is set
	 */
	public ?string $url;

	#[OA\Property]
	/**
	 * Image URL when search ID is set
	 *
	 * @var string
	 */
	public ?string $image;
}
