<?php

namespace WPDRMS\ASP\Search;

use WPDRMS\ASP\Utils\Html;
use WPDRMS\ASP\Utils\MB;
use WPDRMS\ASP\Utils\Pdf;
use WPDRMS\ASP\Utils\Post;
use WPDRMS\ASP\Utils\Str;

class SearchMediaIndex extends SearchIndex {

	protected function doSearch(): void {
		$args                         = &$this->args;
		$args['post_type']            = array( 'attachment' );
		$args['posts_limit']          = $args['attachments_limit'];
		$args['posts_limit_override'] = $args['attachments_limit_override'];
		$args['post_not_in2']         = $args['attachment_exclude'];

		parent::doSearch();
	}

	protected function postProcess(): void {
		$args        = &$this->args;
		$s           = $this->s;
		$_s          = $this->_s;
		$sd          = $args['_sd'] ?? array();
		$com_options = wd_asp()->o['asp_compatibility'];

		// No post-processing if the search data param is missing or explicitly set
		if ( empty($args['_sd']) || !$args['_post_process'] ) {
			return;
		}

		foreach ( $this->results as $k => $r ) {
			if ( !isset($r->post_mime_type) ) {
				$r->post_mime_type = get_post_mime_type( $r->id );
			}
			if ( !isset($r->guid) ) {
				$r->guid = get_the_guid( $r->id );
			}

			$r->title = get_the_title($r->id);
			if ( !empty($sd['advtitlefield']) ) {
				$r->title = $this->advField(
					array(
						'main_field_slug'  => 'titlefield',
						'main_field_value' => $r->title,
						'r'                => $r,
						'field_pattern'    => stripslashes( $sd['advtitlefield'] ),
					),
					$com_options['use_acf_getfield']
				);
			}

			$image_settings = $sd['image_options'];
			$image_args     = array(
				'get_content'        => false,
				'get_excerpt'        => false,
				'image_sources'      => array(
					$image_settings['image_source1'],
					$image_settings['image_source2'],
					$image_settings['image_source3'],
					$image_settings['image_source4'],
					$image_settings['image_source5'],
				),
				'image_source_size'  => $image_settings['image_source_featured'] === 'original' ? 'full' : $image_settings['image_source_featured'],
				'image_default'      => $image_settings['image_default'],
				'image_number'       => $sd['image_parser_image_number'],
				'image_custom_field' => $image_settings['image_custom_field'],
				'exclude_filenames'  => $sd['image_parser_exclude_filenames'],
				'image_width'        => $image_settings['image_width'],
				'image_height'       => $image_settings['image_height'],
				'apply_the_content'  => $image_settings['apply_content_filter'],
				'image_cropping'     => $image_settings['image_cropping'],
				'image_transparency' => $image_settings['image_transparency'],
				'image_bg_color'     => $image_settings['image_bg_color'],
			);
			if (
				$r->post_mime_type === 'application/pdf' &&
				$args['attachment_pdf_image']
			) {
				$r->image = Pdf::getThumbnail($r->id, false, $image_args['image_source_size']);
			}

			if ( empty($r->image) && $args['attachment_use_image'] && $r->guid !== '' ) {
				$r->image = Post::parseImage($r, $image_args);
			}

			// --------------------------------- URL -----------------------------------
			if ( $args['attachment_link_to'] === 'file' ) {
				$_url = wp_get_attachment_url( $r->id );
				if ( $_url !== false ) {
					$this->results[ $k ]->link = $_url;
				}
			} elseif ( $args['attachment_link_to'] === 'parent' ) {
				$parent_id = wp_get_post_parent_id( $r->id );
				if ( !is_wp_error($parent_id) && !empty($parent_id) ) {
					// Change the link to parent post permalink
					$r->link = get_permalink( $parent_id );
				} elseif ( $args['attachment_link_to_secondary'] === 'file' ) {
					$_url = wp_get_attachment_url($r->id);
					if ( $_url !== false ) {
						$this->results[ $k ]->link = $_url;
					}
				}
			} else {
				$_url = get_attachment_link($r->id);
				if ( !empty($_url) ) {
					$this->results[ $k ]->link = $_url;
				}
			}
			// --------------------------------------------------------------------------

			if ( $r->content === '' ) {
				$_content = get_post_meta($r->id, '_asp_attachment_text', true);
				$_content = wd_strip_tags_ws($_content, $sd['striptagsexclude']);
			} else {
				$_content = $r->content;
			}

			// Get the words from around the search phrase, or just the description
			if ( $_content !== '' ) {
				if ( $sd['description_context'] && count($_s) > 0 && $s !== '' ) {
					$_content = Str::getContext($_content, $sd['descriptionlength'], $sd['description_context_depth'], $s, $_s);
				} elseif ( MB::strlen($_content) > $sd['descriptionlength'] ) {
					$_content = wd_substr_at_word($_content, $sd['descriptionlength']);
				}
			}

			if ( !empty($sd['advdescriptionfield']) ) {
				$description_length = $sd['descriptionlength'];
				$cb = function ( $value, $field, $results, $field_args ) use ( $description_length, $sd, $s, $_s ) {
					$value      = Post::dealWithShortcodes($value, $sd['shortcode_op'] === 'remove');
					$strip_tags = $field_args['strip_tags'] ?? 1;
					if ( strpos($field, 'html') === false && $strip_tags ) {
						$value = Html::stripTags($value, $sd['striptagsexclude']);
						if ( $sd['description_context'] && count( $_s ) > 0 && $s !== '' ) {
							$value = Str::getContext($value, $description_length, $sd['description_context_depth'], $s, $_s);
						} elseif ( $value !== '' && ( MB::strlen( $value ) > $description_length ) ) {
							$value = wd_substr_at_word($value, $description_length);
						}
					}
					return $value;
				};
				add_filter('asp_cpt_advanced_field_value', $cb, 10, 4);
				$_content = $this->advField(
					array(
						'main_field_slug'  => 'descriptionfield',
						'main_field_value' => $_content,
						'r'                => $r,
						'field_pattern'    => stripslashes( $sd['advdescriptionfield'] ),
					),
					$com_options['use_acf_getfield']
				);
				remove_filter('asp_cpt_advanced_field_value', $cb);
			}

			$r->content = Str::fixSSLURLs(wd_closetags($_content));

			// --------------------------------- DATE -----------------------------------
			if ( isset($sd['showdate']) && $sd['showdate'] ) {
				$post_time = strtotime($this->results[ $k ]->date);

				if ( $sd['custom_date'] ) {
					$date_format = w_isset_def($sd['custom_date_format'], 'Y-m-d H:i:s');
				} else {
					$date_format = get_option('date_format', 'Y-m-d') . ' ' . get_option('time_format', 'H:i:s');
				}

				$this->results[ $k ]->date = @date_i18n($date_format, $post_time); // @phpcs:ignore
			}
			// --------------------------------------------------------------------------
		}
	}
}
