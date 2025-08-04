<?php

namespace WPDRMS\ASP\Utils\Imagely;

class NggImage {
	/**
	 * @var \Imagely\NGG\DataTypes\Image[]
	 */
	private static array $images_cache = array();

	/**
	 * Gets the NGG image object from the related post (extras post) ID
	 *
	 * @param int $image_post_id
	 * @return \Imagely\NGG\DataTypes\Image|null
	 */
	public static function getImageFromPostId( int $image_post_id ): ?\Imagely\NGG\DataTypes\Image {
		if ( isset(self::$images_cache[ $image_post_id ]) ) {
			return self::$images_cache[ $image_post_id ];
		}
		$items = \Imagely\NGG\DataMappers\Image::get_instance()
			->select()->where( array( 'extras_post_id = %d', $image_post_id ) )->run_query( false, true );
		if ( empty($items) || !( $items[0] instanceof \Imagely\NGG\DataTypes\Image ) ) {
			return null;
		}
		self::$images_cache[ $image_post_id ] = $items[0];
		return $items[0];
	}

	/**
	 * Gets the full image URL from the related post (extras post) ID
	 *
	 * @param int $image_post_id
	 * @return string
	 */
	public static function getImageUrl( int $image_post_id ): string {
		$image = self::getImageFromPostId( $image_post_id );
		if ( $image === null ) {
			return '';
		}
		return \Imagely\NGG\DataStorage\Manager::get_instance()->get_image_url($image);
	}

	/**
	 * Gets the image title from the related post (extras post) ID
	 *
	 * @param int $image_post_id
	 * @return string
	 */
	public static function getImageTitle( int $image_post_id ): string {
		$image = self::getImageFromPostId( $image_post_id );
		if ( $image === null ) {
			return '';
		}
		return $image->title ?? $image->alttext ?? $image->filename ?? '';
	}

	/**
	 * Gets the image description from the related post (extras post) ID
	 *
	 * @param int $image_post_id
	 * @return string
	 */
	public static function getImageDescription( int $image_post_id ): string {
		$image = self::getImageFromPostId( $image_post_id );
		if ( $image === null ) {
			return '';
		}
		return $image->description ?? '';
	}

	/**
	 * Gets the image file name from the related post (extras post) ID
	 *
	 * @param int $image_post_id
	 * @return string
	 */
	public static function getImageFileName( int $image_post_id ): string {
		$image = self::getImageFromPostId( $image_post_id );
		if ( $image === null ) {
			return '';
		}
		return $image->filename ?? '';
	}

	/**
	 * Gets the image tags from the related post (extras post) ID
	 *
	 * @param int $image_post_id
	 * @return string[]
	 */
	public static function getImageTags( int $image_post_id ): array {
		$image = self::getImageFromPostId( $image_post_id );
		if ( $image === null ) {
			return array();
		}
		$tags = wp_get_object_terms( $image->pid, 'ngg_tag', 'fields=names' );
		if ( is_wp_error($tags) ) {
			return array();
		}
		return $tags;
	}
}
