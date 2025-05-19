<?php
namespace WPDRMS\ASP\Utils;

use WP_Error;
use WP_Term;
use WP_User;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Post') ) {
	class Post {
		public static function dealWithShortcodes( string $content, bool $remove = true ): string {
			// Remove unneccessary gutemberg blocks
			$_content = Str::removeGutenbergBlocks($content, array( 'core-embed/*' ));

			// Deal with the shortcodes here, for more accuracy
			if ( $remove ) {
				if ( $_content != '' ) {
					// Remove shortcodes, keep the content, really fast and effective method
					/* @noinspection All */
					$_content = preg_replace('~(?:\[/?)[^\]]+/?\]~su', '', $_content);
				}
			} elseif ( $_content != '' ) {
				$_content = apply_filters( 'the_content', $_content );
			}

			return $_content;
		}

		/**
		 * Fetches an image from the image sources
		 *
		 * @param $post - post object
		 * @param $args array
		 * @return string image URL
		 */
		public static function parseImage( $post, array $args ): string {
			$args = wp_parse_args(
				$args,
				array(
					'get_content'        => true,
					'get_excerpt'        => true,
					'image_sources'      => array( 'featured' ),
					'image_source_size'  => 'full',
					'image_default'      => '',
					'image_number'       => 1,
					'image_custom_field' => '',
					'exclude_filenames'  => '',
					'image_width'        => 70,
					'image_height'       => 70,
					'apply_the_content'  => true,
					'image_cropping'     => false,
					'image_transparency' => true,
					'image_bg_color'     => 'rgba(255, 255, 255, 1)',
				)
			);
			if ( method_exists($post, 'get_id') ) {
				$id = $post->get_id();
			} else {
				$id = $post->ID ?? ( $post->id ?? false );
			}
			if ( empty($id) ) {
				return '';
			}
			$excerpt = $post->excerpt ?? ( $post->post_excerpt ?? '' );
			$content = $post->content ?? ( $post->post_content ?? '' );
			if ( !isset( $post->image ) || $post->image == null ) {
				$im = '';
				foreach ( $args['image_sources'] as $source ) {
					switch ( $source ) {
						case 'featured':
							if ( $post->post_type == 'attachment' && strpos($post->post_mime_type, 'image/') !== false ) {
								$imx = wp_get_attachment_image_src($id, $args['image_source_size'], false);
							}
							if ( isset($imx, $imx[0]) && !is_wp_error($imx) && $imx !== false ) {
								$im = $imx[0];
							} else {
								$imx = wp_get_attachment_image_src(
									get_post_thumbnail_id($id),
									$args['image_source_size'],
									false
								);
								if ( !is_wp_error($imx) && $imx !== false && isset($imx[0]) ) {
									$im = $imx[0];
								}
							}
							break;
						case 'content':
							$content = $args['get_content'] ? get_post_field('post_content', $id) : $content;
							if ( $args['apply_the_content'] ) {
								$content = apply_filters('the_content', $content);
							}
							$im = asp_get_image_from_content( $content, $args['image_number'], $args['exclude_filenames'] );
							break;
						case 'excerpt':
							$excerpt = $args['get_excerpt'] ? get_post_field('post_excerpt', $id) : $excerpt;

							$im = asp_get_image_from_content( $excerpt, $args['image_number'], $args['exclude_filenames'] );
							break;
						case 'screenshot':
							$im = 'https://s.wordpress.com/mshots/v1/' . urlencode( get_permalink( $post->id ) ) .
								'?w=' . $args['image_width'] . '&h=' . $args['image_height'];
							break;
						case 'post_format':
							$format = get_post_format( $post->id );

							switch ( $format ) {
								case 'audio':
									$im = ASP_URL_NP . 'img/post_format/audio.png';
									break;
								case 'video':
									$im = ASP_URL_NP . 'img/post_format/video.png';
									break;
								case 'quote':
									$im = ASP_URL_NP . 'img/post_format/quote.png';
									break;
								case 'image':
									$im = ASP_URL_NP . 'img/post_format/image.png';
									break;
								case 'gallery':
									$im = ASP_URL_NP . 'img/post_format/gallery.png';
									break;
								case 'link':
									$im = ASP_URL_NP . 'img/post_format/link.png';
									break;
								default:
									$im = ASP_URL_NP . 'img/post_format/default.png';
									break;
							}
							break;
						case 'custom':
							if ( $args['image_custom_field'] != '' ) {
								$val = get_post_meta( $post->id, $args['image_custom_field'], true );
								if ( is_array($val) && !empty($val) ) {
									$val = reset($val);
								}
								if ( $val != null && $val != '' ) {
									if ( is_numeric($val) ) {
										$im = wp_get_attachment_image_url( $val, $args['image_source_size'] );
									} else {
										$im = $val;
									}
								}
							}
							break;
						case 'default':
							if ( $args['image_default'] != '' ) {
								$im = $args['image_default'];
							}
							break;
						default:
							$im = '';
							break;
					}
					if ( $im != null && $im != '' ) {
						break;
					}
				}
				if ( !is_wp_error($im) ) {
					if ( $args['image_cropping'] ) {
						if ( strpos( $im, 'mshots/v1' ) === false && strpos( $im, '.gif' ) === false ) {
							$bfi_params = array(
								'width'  => $args['image_width'],
								'height' => $args['image_height'],
								'crop'   => true,
							);
							if ( !$args['image_transparency'] ) {
								$bfi_params['color'] = wpdreams_rgb2hex($args['image_bg_color']);
							}

							$im = asp_bfi_thumb( $im, $bfi_params );
						}
					}
					return Str::fixSSLURLs($im);
				}
				return '';
			} else {
				return Str::fixSSLURLs($post->image);
			}
		}

		public static function getEarliestPostDate( $args = array( 'post_type' => 'post' ) ): string {
			$args  = wp_parse_args(
				$args,
				array(
					'orderby'        => 'date',
					'order'          => 'ASC',
					'posts_per_page' => 1,
					'post_status'    => array( 'inherit', 'publish' ),
				)
			);
			$posts = get_posts($args);
			if ( !is_wp_error($posts) && isset($posts[0], $posts[0]->post_date) ) {
				return $posts[0]->post_date;
			} else {
				return '-4y 0m 0d';
			}
		}

		public static function getLatestPostDate( $args = array( 'post_type' => 'post' ) ): string {
			$args  = wp_parse_args(
				$args,
				array(
					'orderby'        => 'date',
					'order'          => 'DESC',
					'posts_per_page' => 1,
					'post_status'    => array( 'inherit', 'publish' ),
				)
			);
			$posts = get_posts($args);
			if ( !is_wp_error($posts) && isset($posts[0], $posts[0]->post_date) ) {
				return $posts[0]->post_date;
			} else {
				return '0y 0m 0d';
			}
		}

		/**
		 * Gets the custom field value, supporting ACF get_field() and WooCommerce multi currency
		 *
		 * @param string $field      Custom field label
		 * @param object $r          Result object
		 * @param bool   $use_acf    If true, will use the get_field() function from ACF
		 * @param array  $field_args Additional field arguments
		 * @return mixed
		 */
		public static function getCFValue( string $field, $r, bool $use_acf, array $field_args = array() ) {
			$ret             = '';
			$price_fields    = array( '_price', '_price_html', '_tax_price', '_sale_price', '_regular_price' );
			$datetime_fields = array(
				'_EventStartDate',
				'_EventStartDateUTC',
				'_EventEndDate',
				'_EventEndDateUTC',
				'_event_start_date',
				'_event_end_date',
				'_event_start',
				'_event_end',
				'_event_start_local',
				'_event_end_local',
			);

			$separator = $field_args['separator'] ?? ', ';
			$separator = strval($separator);

			if ( ( in_array($field, $datetime_fields) || isset($field_args['date_format']) ) && isset($r->post_type) ) {
				$field_values = get_post_custom_values($field, $r->id);
				if ( isset($field_values[0]) ) {
					if ( isset($field_args['date_format']) ) {
						$ret = date_i18n( $field_args['date_format'], strtotime( $field_values[0] ) );
					} else {
						$ret = date_i18n( get_option( 'date_format' ), strtotime( $field_values[0] ) );
					}
				}
			} elseif ( in_array($field, $price_fields) &&
				isset($r->post_type) &&
				in_array($r->post_type, array( 'product', 'product_variation' )) &&
				function_exists('wc_get_product')
			) { // Is this a WooCommerce price related field?
				$ret = WooCommerce::formattedPriceWithCurrency($r->id, $field);
			} else { // ...or just a regular field?
				if ( $use_acf && function_exists('get_field') ) {
					$field_values = get_field($field, $r->id, true);
				} else {
					$field_values = get_post_meta($r->id, $field);
				}
				if ( !is_null($field_values) && $field_values !== '' && $field_values !== false ) {
					$ret = self::processCFValue(
						$field_values,
						isset($field_args['is_post_id']) ? 'post_relationship' : 'post_meta'
					);
					$ret = Str::anyToString( $ret, $separator );
				}
			}

			return $ret;
		}

		public static function getMetaValueArray(
			int $post_id,
			string $field,
			// string $separator = ', ',
			string $source = 'post_meta',
			bool $use_acf = true
		): array {
			$ret = array();
			if ( $use_acf && function_exists('get_field') ) {
				$field_values = get_field($field, $post_id, true);
			} else {
				$field_values = get_post_meta($post_id, $field);
			}
			if ( !is_null($field_values) && $field_values !== '' && $field_values !== false ) {
				$ret = self::processCFValue($field_values, $source);
			}
			return $ret;
		}

		/**
		 * @param mixed  $value
		 * @param string $source
		 * @return string[]
		 */
		public static function processCFValue( $value, string $source = 'post_meta' ): array {
			if ( is_array($value) ) {
				if ( isset($value['label']) ) {
					return is_array($value['label']) ? $value['label'] : array( $value['label'] );
				} elseif ( isset($value['ID']) && is_numeric($value['ID']) ) {
					return self::processCFValue( $value['ID'], $source);
				} else {
					$ret = array();
					foreach ( $value as $v ) {
						$ret = array_merge($ret, self::processCFValue( $v, $source));
					}
					return array_unique(array_filter($ret, fn( $v )=>$v !==''));
				}
			} elseif ( is_object($value) ) { // In case of objects try fetching the IDs
				if ( $value instanceof WP_Term ) {
					return array( $value->name );
				} elseif ( $value instanceof WP_User ) {
					return array( $value->display_name );
				} elseif ( $value instanceof \WP_Post ) {
					$title = get_the_title($value->ID);
					if ( is_wp_error($title) || $title === '' ) {
						return array( Str::anyToString($value) );
					}
					return array( $title );
				}
			} elseif ( is_numeric($value) ) {
				switch ( $source ) {
					case 'post_relationship':
						$title = get_the_title( intval($value) );
						if ( is_wp_error($title) || $title === '' ) {
							return array( strval($value) );
						}
						return array( $title );
					case 'user_relationship':
						$user = get_user_by( 'id', intval($value) );
						if ( $user === false ) {
							return array( strval($value) );
						}
						return array( $user->display_name );
					case 'taxonomy_relationship':
						/**
						 * @var WP_Error|WP_Term $term
						 */
						$term = get_term( intval($value) );
						if ( $term instanceof WP_Error ) {
							return array( strval($value) );
						}
						return array( $term->name );
					default:
						return array( strval($value) );
				}
			} else {
				return array_filter(array( Str::anyToString( $value ) ), fn( $v )=>$v !=='');
			}

			return array();
		}

		public static function getPODsValue( $field, $r ): array {
			$values = array();
			if ( strpos($field, '_pods_') !== false && isset($r->id, $r->post_type) ) {
				$field = str_replace('_pods_', '', $field);
				if ( function_exists('pods') ) {
					$p = pods($r->post_type, $r->id);
					if ( is_object($p) ) {
						$values = $p->field($field, false);
						$values = is_array($values) ? $values : array( $values );
					}
				}
			}
			return $values;
		}
	}
}
