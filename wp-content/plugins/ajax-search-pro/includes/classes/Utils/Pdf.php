<?php
namespace WPDRMS\ASP\Utils;

use Exception;
use Imagick;

if ( !defined('ABSPATH') ) {
	die('-1');
}

/**
 * PDF file related utils
 */
class Pdf {
	public static function getThumbnail( int $pdf_id, bool $regenerate = false, string $size = 'full' ): string {
		/**
		 * Fetch the PDF thumbnail generated by WordPress if it exists
		 */
		if ( apply_filters('asp/utils/pdf/thumbnail/get_core', true) ) {
			$attached_image_url = wp_get_attachment_image_url( $pdf_id, $size );
			if ( $attached_image_url !== false ) {
				return $attached_image_url;
			}
		}

		if ( !$regenerate ) {
			$stored_thumbnail = self::getStoredThumbnail( $pdf_id, $size );
			if ( $stored_thumbnail !== '' ) {
				return $stored_thumbnail;
			}
		}

		if ( class_exists('\\Imagick') ) {
			set_time_limit( 5 );

			$image_sizes = wp_get_registered_image_subsizes();
			if ( isset($image_sizes[ $size ]) ) {
				$max_width  = min($image_sizes[ $size ]['width'], 512);
				$max_height = min($image_sizes[ $size ]['height'], 512);
			} else {
				$max_width  = 512;
				$max_height = 512;
			}

			$quality     = 60;
			$type        = 'png';
			$page_number = 0;
			$resolution  = ceil( max( $max_height, $max_width ) * 0.16 );
			$bgcolor     = 'white';

			$filepath = get_attached_file( $pdf_id );

			if ( $filepath === false ) {
				return '';
			}

			$new_filename = sanitize_file_name( basename( $filepath ) . '-' . $max_width . 'x' . $max_height . '.' . $type );
			$new_filename = wp_unique_filename( dirname( $filepath ), $new_filename );
			$new_filepath = str_replace( basename( $filepath ), $new_filename, $filepath );

			try {
				$imagick = new Imagick();
				$imagick->setResolution( $resolution, $resolution );
				$imagick->readimage( $filepath . '[' . $page_number . ']' );
				$imagick->setCompressionQuality( $quality );
				$imagick->scaleImage( $max_width, $max_height, true );
				$imagick->setImageFormat( $type );
				$imagick->setImageBackgroundColor( $bgcolor );
				if ( method_exists( 'Imagick', 'setImageAlphaChannel' ) ) {
					if ( defined('Imagick::ALPHACHANNEL_REMOVE') ) {
						$imagick->setImageAlphaChannel( Imagick::ALPHACHANNEL_REMOVE );
					} else {
						$imagick->setImageAlphaChannel( 11 );
					}
				}
				if ( method_exists( '\\Imagick', 'mergeImageLayers' ) ) {
					$imagick->mergeImageLayers( Imagick::LAYERMETHOD_FLATTEN );
				} elseif ( method_exists( '\\Imagick', 'flattenImages' ) ) {
					/** @noinspection PhpDeprecationInspection */
					$imagick = $imagick->flattenImages();
				}
				$imagick->stripImage();
				$imagick->writeImage( $new_filepath );
				$imagick->clear();
				update_post_meta( $pdf_id, '_asp_pdf_thumbnail_' . $size, $new_filename );

				$thumbnail_filepath = str_replace( basename( $filepath ), $new_filename, $filepath );
				if ( file_exists( $thumbnail_filepath ) ) {
					$thumbnail_url = wp_get_attachment_url( $pdf_id );
					if ( $thumbnail_url !== false ) {
						return str_replace( basename( $filepath ), $new_filename, $thumbnail_url );
					}
				}
			} catch ( Exception $err ) {
				return '';
			}
		}

		return '';
	}

	private static function getStoredThumbnail( int $pdf_id, string $size ): string {
		$thumbnail = get_post_meta( $pdf_id, '_asp_pdf_thumbnail_' . $size, true );
		if ( empty($thumbnail) ) {
			return '';
		}
		$filepath = get_attached_file( $pdf_id );
		if ( $filepath === false ) {
			delete_post_meta( $pdf_id, '_asp_pdf_thumbnail_' . $size );
			return '';
		}
		$thumbnail_filepath = str_replace(basename($filepath), $thumbnail, $filepath);
		if ( !file_exists($thumbnail_filepath) ) {
			delete_post_meta( $pdf_id, '_asp_pdf_thumbnail_' . $size );
			return '';
		}
		$thumbnail_url = wp_get_attachment_url($pdf_id);
		if ( $thumbnail_url !== false ) {
			return str_replace(basename($filepath), $thumbnail, $thumbnail_url);
		}

		return '';
	}
}
