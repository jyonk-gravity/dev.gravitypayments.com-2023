<?php

namespace WPDRMS\Backend\Options;

class TaxonomySelect extends AbstractOption {
	protected
		$default_args = array(
			"type" => 'include'
		),
		$taxonomies = array(),
		$selected = array(),
		$exclude = array(
			'product_visibility', 'product_type'
		);

	function __construct($args) {
		parent::__construct($args);

		$this->value = array_filter($this->value, function($taxonomy){
			return taxonomy_exists($taxonomy);
		});

		$taxonomies = get_taxonomies(array('_builtin' => false), 'objects', 'and');
		foreach ( $taxonomies as $taxonomy ) {
			if ( !in_array($taxonomy->name, $this->exclude) ) {
				$label = isset($tax->object_type, $tax->object_type[0]) ?
					$tax->object_type[0] . ' - ' . $taxonomy->labels->name : $taxonomy->labels->name;
				if ( !in_array($taxonomy->name, $this->value) ) {
					$this->taxonomies[$taxonomy->name] = $label;
				}
				if ( in_array($taxonomy->name, $this->value) ) {
					$this->selected[$taxonomy->name] = $label;
				}
			}
		}
	}

	public static function value( $value, $default_value = null ) {
		return array_filter(explode('|', $value));
	}

	protected static function outputValue( $value ) {
		// No need to decode
		return implode('|', $value);
	}

	function render() {
		?>
		<div class='wpdreamsTaxonomySelect' data-id="<?php echo self::$num; ?>" id='wpdreamsTaxonomySelect-<?php echo self::$num; ?>'>
			<fieldset>
				<legend><?php echo $this->label; ?></legend>
				<div class="sortablecontainer" id="sortablecontainer<?php echo self::$num; ?>">
					<div class="arrow-all arrow-all-left"></div>
					<div class="arrow-all arrow-all-right"></div>
					<p><?php echo __('Available taxonomies', 'ajax-search-pro'); ?></p>
					<ul id="sortable<?php echo self::$num; ?>" class="connectedSortable connectedSortable<?php echo self::$num; ?>">
						<?php foreach($this->taxonomies as $taxonomy => $label ): ?>
							<li class="ui-state-default" data-taxonomy="<?php echo $taxonomy; ?>">
								<?php echo $label; ?>
								<span class="extra_info">[<?php echo $taxonomy; ?>]</span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="sortablecontainer">
					<p><?php echo __('Drag here the post types you want to', 'ajax-search-pro'); ?><b><?php echo $this->args['type']; ?>!</b></p>
					<ul id="sortable_conn<?php echo self::$num; ?>" class="connectedSortable connectedSortable<?php echo self::$num; ?>">
						<?php foreach($this->selected as $taxonomy => $label ): ?>
							<li class="ui-state-default" data-taxonomy="<?php echo $taxonomy; ?>">
								<?php echo $label; ?>
								<span class="extra_info">[<?php echo $taxonomy; ?>]</span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<input isparam="1" type="hidden"
					   value="<?php echo self::outputValue($this->value); ?>"
					   name="<?php echo $this->name; ?>">
			</fieldset>
		</div>
		<?php
	}
}