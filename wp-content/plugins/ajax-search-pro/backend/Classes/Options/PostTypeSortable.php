<?php

namespace WPDRMS\Backend\Options;

class PostTypeSortable extends AbstractOption {
	use PostTypeTrait;

	function __construct($args) {
		parent::__construct($args);

		$post_types = get_post_types(array(
			"public" => false,
			"_builtin" => true
		), "names", "OR");
		$post_types = array_diff($post_types, self::$NON_DISPLAYABLE_POST_TYPES);

		foreach ( $post_types as $type ) {
			if ( !in_array($type, $this->value) ) {
				$this->value[] = $type;
			}
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
		<div class='wd_post_type_sortalbe' id='wd_post_type_sortalbe-<?php echo self::$num; ?>'>
			<div class="sortablecontainer" style="float:right;">
				<p><?php echo $this->label; ?></p>
				<ul id="sortable<?php echo self::$num; ?>" class="connectedSortable">
					<?php foreach($this->value as $post_type ): ?>
						<li class="ui-state-default" data-post_type="<?php echo esc_attr($post_type); ?>">
							<?php echo esc_html($post_type); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<input isparam=1 type='hidden' value="<?php echo self::outputValue($this->value); ?>" name='<?php echo $this->name; ?>'>
			<div class="clear"></div>
		</div>
		<?php
	}
}