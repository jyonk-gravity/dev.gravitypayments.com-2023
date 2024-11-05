<?php

namespace WH\GF\Multicolumn\Admin\Field;

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

use GF_Field;
use GF_Fields;
use WH\GF\Multicolumn\Classes\WH_GF_Multicolumn_Logger;

class WH_GF_Multicolumn_Field_Column_Start extends
	GF_Field {
	public $type = 'column_start';

	public function get_form_editor_inline_script_on_page_render() {
		$script = sprintf( "function SetDefaultValues_%s(field) {field.label = '%s';}",
		                   $this->type,
		                   $this->get_form_editor_field_title() ) . PHP_EOL;

		return $script;
	}

	public function get_form_editor_field_title() {
		return __( 'Row Start', 'gf-form-multicolumn' );
	}

	public function get_form_editor_field_settings() {
		return array (
			'css_class_setting',
		);
	}

	public function is_conditional_logic_supported() {
		return false;
	}

	public function get_field_label( $force_frontend_label, $value ) {
		return $this->get_form_editor_field_title();
	}

	public function get_form_editor_button() {
		return array (
			'group' => GF_MULTICOLUMN_FIELD_GROUP_TITLE,
			'text'  => $this->get_form_editor_field_title(),
		);
	}
}

try {
	GF_Fields::register( new WH_GF_Multicolumn_Field_Column_Start() );
}
catch ( \Exception $e ) {
	WH_GF_Multicolumn_Logger::log( 'ERROR',
	                               'Register WH_GF_Multicolumn_Field_Column_Start exception ' . $e );
}
