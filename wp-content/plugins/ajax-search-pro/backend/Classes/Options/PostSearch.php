<?php

namespace WPDRMS\Backend\Options;
use WPDRMS\ASP\Query\SearchQuery;
use WPDRMS\ASP\Utils\Ajax;

class PostSearch extends AbstractOption  {
	use PostTypeTrait;

	protected $default_args = array(
		'callback' => '',       // javacsript function name in the windows scope | if empty, shows results
		'placeholder' => 'Search in post types..',
		'search_values' => 0,
		'limit' => 10,
		'delimiter' => '!!!CPTRES!!!',
		'controls_position' => 'right',
		'class' => ''
	);

	function render() {
		$post_title = get_the_title($this->value) . " (" . get_post_type($this->value) . ")";
		?>
		<div class='wd_cpt_search<?php echo $this->args['class'] != '' ? ' '.$this->args['class'] : "";?>'
			 id='wd_cpt_search-<?php echo self::$num; ?>'>
			<label for='wd_cpt_search-input-<?php echo self::$num; ?>'><?php echo $this->label; ?></label>
			<?php if ($this->args['controls_position'] == 'left') $this->printControls(); ?>
			<input type="search"
											   class="hiddend wd_cpt_search"
											   value=""
											   id='wd_cpt_search-input-<?php echo self::$num; ?>'
											   placeholder="<?php echo $this->args['placeholder']; ?>"/>
			<input type='hidden'
				   name="<?php echo $this->name; ?>"
				   isparam="1"
				   value="<?php echo self::outputValue($this->value); ?>">
			<input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
			<?php if ($this->args['controls_position'] != 'left') $this->printControls(); ?>
			<div class="wd_cpt_search_res"></div>
			<span class="wp_cpt_search_selected "><span class="fa fa-ban"></span><span><?php echo esc_html($post_title); ?></span></span>
		</div>
		<?php
	}

	private function printControls() {
		?>
		<span class="loading-small hiddend"></span>
		<div class="wd_ts_close hiddend">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve">
					<polygon id="x-mark-icon" points="438.393,374.595 319.757,255.977 438.378,137.348 374.595,73.607 255.995,192.225 137.375,73.622 73.607,137.352 192.246,255.983 73.622,374.625 137.352,438.393 256.002,319.734 374.652,438.378 "></polygon>
				</svg>
		</div>
		<?php
	}

	public static function search() {
		global $wpdb;
		$phrase = trim($_POST['wd_phrase']);
		$data = json_decode(base64_decode($_POST['wd_args']), true);

		$post_types = get_post_types(array(
			"public" => true,
			"_builtin" => false
		), "names", "OR");

		$post_types = array_diff($post_types, self::$NON_DISPLAYABLE_POST_TYPES);

		$asp_query = new SearchQuery(array(
			"s" => $phrase,
			"_ajax_search" => false,
			'keyword_logic' => 'and',
			'secondary_logic' => 'or',
			"posts_per_page" => 20,
			'post_type' => $post_types,
			'post_status' => array(),
			'post_fields' => array(
				'title', 'ids'
			)
		));

		$results = $asp_query->posts;
		Ajax::prepareHeaders();
		print_r($data['delimiter'] . json_encode($results) . $data['delimiter']);
		die();
	}

	public static function registerAjax() {
		if ( !has_action('wp_ajax_wd_search_cb_cpt') ) {
			add_action('wp_ajax_wd_search_cb_cpt', array(get_called_class(), 'search'));
		}
	}
}