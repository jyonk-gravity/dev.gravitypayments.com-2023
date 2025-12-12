<?php

namespace WPDRMS\ASP\Integration\Imagely;

use WP_Error;
use WP_Post;
use WPDRMS\ASP\Integration\Integration;
use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Utils\Imagely\NggImage;

class NextGenGallery implements Integration {
	use SingletonTrait;

	public function register(): void {
		if ( !class_exists('\Imagely\NGG\DataMappers\Image') ) {
			return;
		}
		add_filter('asp_results', array( $this, 'searchResults' ), 10, 4);
		add_filter('asp/utils/advanced-field/raw_value', array( $this, 'resultAdvancedFields' ), 10, 4);
		add_filter('asp/utils/advanced-field/field-types/taxonomy/args', array( $this, 'resultAdvancedFieldsTaxonomy' ), 10, 4);
		add_filter(
			'asp/index/database/get_posts_to_index/query/add_where_post_status',
			array( $this, 'indexTablePostStatusFix' ),
			10,
			2
		);
		add_filter(
			'asp/index/hooks/post_update/allowed_statuses',
			array( $this, 'indexTablePostUpdateFix' ),
			10,
			2
		);

		add_filter('asp_index_post', array( $this, 'indexTablePost' ), 10, 2);
		add_filter('asp_index_terms', array( $this, 'indexTableTerms' ), 10, 3);
	}

	public function deregister(): void {
		remove_filter('asp_results', array( $this, 'searchResults' ));
		remove_filter('asp/utils/advanced-field/raw_value', array( $this, 'resultAdvancedFields' ));
		remove_filter('asp/utils/advanced-field/field-types/taxonomy/args', array( $this, 'resultAdvancedFieldsTaxonomy' ));
		remove_filter('asp/index/database/get_posts_to_index/query/add_where_post_status', array( $this, 'indexTablePostStatusFix' ));
		remove_filter('asp_index_post', array( $this, 'indexTablePost' ) );
		remove_filter('asp_index_terms', array( $this, 'indexTableTerms' ) );
	}

	/**
	 * Fixes live search results
	 *
	 * @hook asp_results
	 * @param array $results
	 * @return array
	 */
	public function searchResults( array $results, $search_id, $is_ajax, $args ): array {
		foreach ( $results as $k => $r ) {
			if ( !isset($r->post_type) ) {
				continue;
			}
			if ( $r->post_type === 'ngg_pictures' ) {
				$r->image = NggImage::getImageUrl($r->id);

				if ( count($args['attachment_include_directories']) > 0 ) {
					$found = false;
					foreach ( $args['attachment_include_directories'] as $include_dir ) {
						if ( str_contains( $r->image, $include_dir) ) {
							$found = true;
							break;
						}
					}
					if ( !$found ) {
						unset($results[ $k ]);
						continue;
					}
				}

				foreach ( $args['attachment_exclude_directories'] as $exclude_dir ) {
					if ( str_contains( $r->image, $exclude_dir) ) {
						unset($results[ $k ]);
						continue 2;
					}
				}

				$r->link = $r->image;
			}
		}
		return $results;
	}

	/**
	 * Fixes advanced title and content fields
	 *
	 * @hook asp/utils/advanced-field/raw_value
	 * @param string $value
	 * @param string $field
	 * @param object $result
	 * @param array  $args
	 * @return string
	 */
	public function resultAdvancedFields( string $value, string $field, $result, array $args ) {
		if (
			$field !== 'result_field' ||
			$result->post_type !== 'ngg_pictures'
		) {
			return $value;
		}

		if ( $args['field_name'] === 'title' ) {
			return NggImage::getImageTitle($result->id);
		}

		if ( $args['field_name'] === 'content' ) {
			return NggImage::getImageDescription($result->id);
		}

		return $value;
	}

	/**
	 * Fixes taxonomy term advanced title field
	 *
	 * @hook asp/utils/advanced-field/field-types/taxonomy/args
	 * @param Array<string, mixed> $args
	 * @param object               $result
	 * @return Array<string, mixed>
	 */
	public function resultAdvancedFieldsTaxonomy( array $args, $result ) {
		if ( $result->post_type !== 'ngg_pictures' ) {
			return array();
		}
		$image = NggImage::getImageFromPostId( $result->id );
		if ( $image === null ) {
			return array();
		}

		$args['object_ids'] = $image->pid;
		return $args;
	}

	/**
	 * Allows indexing "draft" ngg_pictures post type for indexed search
	 *
	 * @hook asp/index/database/get_posts_to_index/query/add_where_post_status
	 * @param string $add_where
	 * @param array  $args
	 * @return string
	 */
	public function indexTablePostStatusFix( string $add_where, array $args ) {
		if ( !in_array('ngg_pictures', $args['post_types'], true) ) {
			return $add_where;
		}

		return $add_where . " OR ( post.post_status = 'draft' AND post.post_type = 'ngg_pictures') ";
	}

	/**
	 * Allows indexing "draft" ngg_pictures post type when they are saved
	 *
	 * @hook asp/index/hooks/post_update/allowed_statuses
	 * @param string[] $allowed_statuses
	 * @param int      $post_id
	 * @return array
	 */
	public function indexTablePostUpdateFix( array $allowed_statuses, int $post_id ) {
		$post_type = get_post_type($post_id);
		if ( $post_type === 'ngg_pictures' ) {
			$allowed_statuses[] = 'draft';
			$allowed_statuses   = array_unique($allowed_statuses);
		}

		return $allowed_statuses;
	}

	/**
	 * Fixes post fields on index table process
	 *
	 * @hook asp_index_post
	 * @param WP_Post $post
	 * @param array   $args
	 * @return WP_Post|null
	 */
	public function indexTablePost( WP_Post $post, array $args ): ?WP_Post {
		if ( $post->post_type !== 'ngg_pictures' ) {
			return $post;
		}
		$file_url = NggImage::getImageUrl( $post->ID );
		if ( count($args['attachment_include_directories']) > 0 ) {
			$found = false;
			foreach ( $args['attachment_include_directories'] as $include_dir ) {
				if ( str_contains( $file_url, $include_dir) ) {
					$found = true;
					break;
				}
			}
			if ( !$found ) {
				return null;
			}
		}
		foreach ( $args['attachment_exclude_directories'] as $exclude_dir ) {
			if ( str_contains( $file_url, $exclude_dir) ) {
				return null;
			}
		}
		$post->post_title   = NggImage::getImageTitle( $post->ID );
		$post->post_content =
			NggImage::getImageDescription( $post->ID ) . ' ' .
			NggImage::getImageFileName( $post->ID );

		return $post;
	}

	/**
	 * Fixes taxonomy term indexing for ngg_tag
	 *
	 * @param array|WP_Error $terms
	 * @param string         $taxonomy
	 * @param WP_Post        $post
	 * @return string[]
	 */
	public function indexTableTerms( $terms, string $taxonomy, WP_Post $post ) {
		if ( $post->post_type !== 'ngg_pictures' || $taxonomy !== 'ngg_tag' ) {
			return $terms;
		}
		return NggImage::getImageTags( $post->ID );
	}
}
