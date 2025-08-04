<?php

namespace WPDRMS\ASP\Utils\AdvancedField\Types;

use stdClass;
use WP_Error;
use WP_Term;

class TaxonomyFieldTypes extends AbstractWooCommerceBase implements AdvancedFieldTypeInterface {
	protected string $taxonomy;
	protected int $count;
	protected string $separator;
	protected string $separation;
	protected string $orderby;
	protected string $order;
	protected string $exclude;
	protected string $term_color;
	protected string $separator_color;

	protected bool $clickable;
	protected bool $clickable_new_tab;


	protected ?stdClass $result;

	public function __construct( string $field, array $field_args, ?stdClass $result ) {
		parent::__construct($result);

		$this->taxonomy          = $field_args['taxonomy'] ?? '';
		$this->count             = intval($field_args['count'] ?? 5);
		$this->count             = $this->count === 0 ? 5 : $this->count;
		$this->separation        = $field_args['separation'] ?? 'text';
		$this->separator         = $this->separation === 'text' ? ( $field_args['separator'] ?? ', ' ) : '</li><li>';
		$this->orderby           = $field_args['orderby'] ?? 'name';
		$this->order             = $field_args['order'] ?? 'ASC';
		$this->exclude           = $field_args['exclude'] ?? '';
		$this->clickable_new_tab = $field_args['clickable_new_tab'] ?? '';
		$this->clickable         = $field_args['clickable'] ?? '';
		$this->term_color        = $field_args['term_color'] ?? '';
		$this->separator_color   = $field_args['separator_color'] ?? '';
		$this->result            = $result;
	}

	public function process(): string {
		if ( $this->taxonomy === '' || is_null($this->result) ) {
			return '';
		}

		$args = array(
			'taxonomy'   => $this->taxonomy,
			'orderby'    => $this->orderby,
			'order'      => $this->order,
			'object_ids' => $this->result->id,
			'exclude'    => $this->exclude,
			'number'     => $this->count,
		);

		$args = apply_filters('asp/utils/advanced-field/field-types/taxonomy/args', $args, $this->result);

		if ( empty($args) ) {
			return '';
		}

		/**
		 * @var WP_Term[]|WP_Error $terms
		 */
		$terms = get_terms($args);
		if ( $terms instanceof WP_Error || empty($terms) ) {
			return '';
		}

		$terms = apply_filters('asp/utils/advanced-field/field-types/taxonomy/terms', $terms, $args, $this->result);

		$term_style = $this->term_color ? " style='color:" . esc_attr($this->term_color) . ";'" : '';
		$terms      = array_filter(
			array_map(
				function ( WP_Term $term ) use ( $term_style ) {
					if ( $this->clickable ) {
						$link = get_term_link($term);
						if ( $link instanceof WP_Error ) {
							return '';
						}
						$target = $this->clickable_new_tab ?'_new' :'_self';
						return "<a class='asp__af-tt-link'$term_style href='$link' target='$target'>$term->name</a>";
					} else {
						return "<span class='asp__af-tt-nolink'$term_style>$term->name</span>";
					}
				},
				$terms
			)
		);

		if ( empty($terms) ) {
			return '';
		}

		if ( $this->separation === 'text' ) {
			$separator_style = $this->separator_color ? " style='color:" . esc_attr($this->separator_color) . ";'" : '';
			$terms_html      = implode("<span class='asp__af-tt-separator'$separator_style>$this->separator</span>", $terms);
			return "<span class='asp__af-tt-container'>$terms_html</span>";
		} else {
			$partial_html = implode($this->separator, $terms);
			return "<ul class='asp__af-tt-container'><li>$partial_html</li></ul>";
		}
	}
}
