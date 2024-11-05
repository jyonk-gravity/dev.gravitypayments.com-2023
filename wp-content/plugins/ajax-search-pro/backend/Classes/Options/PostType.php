<?php

namespace WPDRMS\Backend\Options;

class PostType extends AbstractOption {
	use PostTypeTrait;

	protected $default_args = array(
		"exclude" => array(),
		"include" => array()
	),
	$postTypes;

	function __construct($args) {
		parent::__construct($args);

		$this->value = array_filter($this->value, function($post_type){
			return post_type_exists($post_type);
		});

		$this->args['exclude'] = array_unique(
			array_merge($this->args['exclude'], self::$NON_DISPLAYABLE_POST_TYPES)
		);
		if ( !empty($this->args['exclude']) &&  !empty($this->args['include']) ) {
			$this->args['exclude'] = array_diff($this->args['exclude'], $this->args['include']);
		}
		$this->postTypes = get_post_types('', "objects");

		if ( !is_wp_error($this->postTypes) && is_array($this->postTypes) ) {
			foreach ($this->postTypes as $k => $v) {
				if ( count($this->args['exclude']) > 0 && in_array($k, $this->args['exclude']) ) {
					unset($this->postTypes[$k]);
					continue;
				}
				if ( $k == 'attachment' ) {
					$v->labels->name = 'Attachment - Media';
				}
			}
		} else {
			$this->postTypes = array();
		}
	}

	public static function value( $value, $default_value = null ) {
		if (gettype($value) === 'string' && substr($value, 0, strlen('_decode_')) === '_decode_') {
			$value = substr($value, strlen('_decode_'));
			$value = json_decode(base64_decode($value), true);
		}

		return self::compatibility( $value );
	}

	protected static function outputValue( $value ) {
		// No need to decode
		if (gettype($value) === 'string' && substr($value, 0, strlen('_decode_')) === '_decode_') {
			return $value;
		} else {
			return '_decode_' . base64_encode(json_encode($value));
		}
	}

	function render() {
	?>
	<div class='wpdreamsCustomPostTypes' data-id="<?php echo self::$num; ?>" id='wpdreamsCustomPostTypes-<?php echo self::$num; ?>'>
		<fieldset>
			<legend><?php echo $this->label; ?></legend>
			<div class="sortablecontainer" id="sortablecontainer<?php echo self::$num; ?>">
				<div class="arrow-all arrow-all-left"></div>
				<div class="arrow-all arrow-all-right"></div>
				<p><?php echo __('Available post types', 'ajax-search-pro'); ?></p>
				<ul id="sortable<?php echo self::$num; ?>" class="connectedSortable connectedSortable<?php echo self::$num; ?>">
					<?php foreach($this->postTypes as $post_type => $data ): ?>
						<?php if ( !in_array($post_type, $this->value) ): ?>
						<li class="ui-state-default" data-ptype="<?php echo $post_type; ?>">
							<?php echo $data->labels->name; ?>
							<span class="extra_info">[<?php echo $post_type; ?>]</span>
						</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="sortablecontainer">
				<p><?php echo __('Drag here the post types you want to use!', 'ajax-search-pro'); ?></p>
				<ul id="sortable_conn<?php echo self::$num; ?>" class="connectedSortable connectedSortable<?php echo self::$num; ?>">
					<?php foreach($this->value as $post_type ): ?>
						<?php if ( isset($this->postTypes[$post_type]) ): ?>
						<li class="ui-state-default" data-ptype="<?php echo $post_type; ?>">
							<?php echo $this->postTypes[$post_type]->labels->name; ?>
							<span class="extra_info">[<?php echo $post_type; ?>]</span>
						</li>
						<?php endif; ?>
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