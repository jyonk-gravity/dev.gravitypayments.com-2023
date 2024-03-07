<?php
/** @noinspection PhpUnusedParameterInspection */
/** @noinspection PhpMissingReturnTypeInspection */

/** @noinspection PhpUnused */

namespace WPDRMS\ASP\Hooks\Filters;

use WP_Post;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * Advanced Custom Fields related Hooks
 */
class ACF extends AbstractFilter {

	/**
	 * @return void
	 */
	public function handle() {}

	/**
	 * @param array<string, mixed> $values
	 * @param WP_Post              $the_post
	 * @param string               $field
	 * @return array<string|int, mixed>
	 */
	public function indexRepeaterAndFlexFields( array $values, WP_Post $the_post, string $field ): array {
		if ( function_exists('have_rows') && function_exists( 'the_row') ) {
			if ( have_rows($field, $the_post->ID) ) {
				while ( have_rows($field, $the_post->ID ) ) {
					$row = the_row();
					foreach ( $row as $sub_field ) {
						$values[] = $sub_field;
					}
				}
			}
		}

		return $values;
	}
}
