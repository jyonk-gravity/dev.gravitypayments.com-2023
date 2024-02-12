<?php

namespace WPDRMS\Backend\Options;

use WPDRMS\ASP\Utils\Ajax;

class CustomFieldSearch extends AbstractOption  {
        protected $default_args = array(
            'callback' => '',       // javascript function name in the windows scope | if empty, shows results
            'search_values' => 0,
            'limit' => 15,
            'delimiter' => '!!!CFRES!!!',
            'controls_position' => 'right',
            'class' => '',
            'usermeta' => 0,
            'show_pods' => false
        );

		public static function registerAjax() {
			if ( !has_action('wp_ajax_wd_search_cf') ) {
				add_action('wp_ajax_wd_search_cf', array(get_called_class(), 'search'));
			}
		}

        function render() {
            ?>
            <div class='wd_cf_search<?php echo $this->args['class'] != '' ? ' '.$this->args['class'] : "";?>'
                 id='wd_cf_search-<?php echo self::$num; ?>'>
                <?php if ($this->args['controls_position'] == 'left') $this->printControls(); ?>
                <?php echo $this->label; ?> <input type="search" name="<?php echo $this->name; ?>"
                                                   class="wd_cf_search"
                                                   value="<?php echo self::outputValue($this->value); ?>"
                                                   placeholder="<?php esc_attr_e('Search custom fields..', 'ajax-search-pro'); ?>"/>
                <input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
                <?php if ($this->args['controls_position'] != 'left') $this->printControls(); ?>
                <div class="wd_cf_search_res"></div>
            </div>
            <?php
        }

        private function printControls() {
            ?>
            <span class="loading-small hiddend"></span>
            <div class="wd_ts_close hiddend">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 512 512" xml:space="preserve">
                        <polygon id="x-mark-icon" points="438.393,374.595 319.757,255.977 438.378,137.348 374.595,73.607 255.995,192.225 137.375,73.622 73.607,137.352 192.246,255.983 73.622,374.625 137.352,438.393 256.002,319.734 374.652,438.378 "></polygon>
                    </svg>
            </div>
            <?php
        }

        public static function search() {
            global $wpdb;

            // Exact matches
            $phrase = trim($_POST['wd_phrase']) . '%';
            $data = json_decode(base64_decode($_POST['wd_args']), true);
            if ($data['usermeta'])
                $table = $wpdb->usermeta;
            else
                $table = $wpdb->postmeta;
            if ($data['search_values'] == 1) {
                $cf_query = $wpdb->prepare(
                    "SELECT DISTINCT(meta_key) FROM $table WHERE meta_key LIKE '%s' OR meta_value LIKE '%s' ORDER BY meta_key ASC LIMIT %d",
                    $phrase, $phrase, $data['limit']);
            } else {
                $cf_query = $wpdb->prepare(
                    "SELECT DISTINCT(meta_key) FROM $table WHERE meta_key LIKE '%s' ORDER BY meta_key ASC LIMIT %d",
                    $phrase, $data['limit']);
            }
            $cf_results = $wpdb->get_results( $cf_query );

            $remaining_limit = $data['limit'] - count($cf_results);
            if ( $remaining_limit > 0 ) {
                // Fuzzy matches
                $not_in_query = '';
                $not_in = array();
                foreach ($cf_results as $r) {
                    $not_in[] = $r->meta_key;
                }
                if (count($not_in) > 0) {
                    $not_in_query = " AND meta_key NOT IN ('" . implode("','", $not_in) . "')";
                }
                $phrase = '%' . trim($_POST['wd_phrase']) . '%';
                if ($data['search_values'] == 1) {
                    $cf_query = $wpdb->prepare(
                        "SELECT DISTINCT(meta_key) FROM $table WHERE (meta_key LIKE '%s' OR meta_value LIKE '%s') $not_in_query ORDER BY meta_key ASC LIMIT %d",
                        $phrase, $phrase, $remaining_limit);
                } else {
                    $cf_query = $wpdb->prepare(
                        "SELECT DISTINCT(meta_key) FROM $table WHERE (meta_key LIKE '%s') $not_in_query ORDER BY meta_key ASC LIMIT %d",
                        $phrase, $remaining_limit);
                }
                $cf_results = array_merge($cf_results, $wpdb->get_results($cf_query));
            }

            if ( $data['show_pods'] )
                $pods_fields = self::searchPods($_POST['wd_phrase']);
            else
                $pods_fields = array();

			Ajax::prepareHeaders();
            print_r($data['delimiter'] . json_encode(array_merge($pods_fields, $cf_results)) . $data['delimiter']);
            die();
        }

        private static function searchPods($s): array {
            $ret = array();
            if ( function_exists('pods_api') ) {
                // Filter table storage based fields only
                $pods = get_posts(array(
                    'fields'          => 'ids',
                    'posts_per_page'  => -1,
                    'post_type' => '_pods_pod',
                    'meta_query' => array(
                        array(
                            'key' => 'storage',
                            'value' => 'table',
                            'compare' => 'LIKE'
                        )
                    )
                ));
                if ( !is_wp_error($pods) && !empty($pods) ) {
                    $pods_fields = get_posts(array(
                        'fields' => 'post_name',
                        'posts_per_page' => -1,
                        's' => $s,
                        'post_type' => '_pods_field',
                        'post_parent__in' => $pods // Only filtered parents by table storage type
                    ));
                    if ( !is_wp_error($pods_fields) && !empty($pods_fields) ) {
                        foreach ($pods_fields as $f) {
                            $ret[] = array('meta_key' => '__pods__' . $f->post_name);
                        }
                    }
                }
            }

            return $ret;
        }
}