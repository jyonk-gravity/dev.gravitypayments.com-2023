<?php

namespace WH\GF\Multicolumn\Admin\Field;

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

use GF_Field;
use GF_Fields;
use WH\GF\Multicolumn\Classes\WH_GF_Multicolumn_Logger;

define( 'WH_GF_MULTICOLUMN_FIELD_GROUP_TITLE', 'Multiple Columns Fields' );

class WH_GF_Multicolumn_Field_Group extends
	GF_Field {
	public $type = 'multicolumn_group';

	private $buttonLabel;
	private $button_group = GF_MULTICOLUMN_FIELD_GROUP_TITLE;

	/*
	 * Prevent adding a separate button for this field group
	 */
	public function get_form_editor_button() {
		return null;
	}

	public function add_button( $field_groups ) {
		$field_groups = $this->add_custom_field_group( $field_groups );

		return parent::add_button( $field_groups );
	}

	public function add_custom_field_group( $field_groups ) {
		$this->set_button_label();
		foreach ( $field_groups as $field_group ) {
			if ( $field_group['name'] === $this->button_group ) {
				return $field_groups;
			}
		}

		$field_groups[] = array (
			'name'   => $this->button_group,
			'label'  => __( $this->buttonLabel, 'gf-form-multicolumn' ),
			'fields' => array (),
		);

		return $field_groups;
	}

	private function set_button_label() {
		$this->buttonLabel = __( 'Multiple Columns Fields',
		                         'gf-form-multicolumn' );
	}
}

try {
	GF_Fields::register( new WH_GF_Multicolumn_Field_Group() );
}
catch ( \Exception $e ) {
	WH_GF_Multicolumn_Logger::log( 'ERROR',
	                               'Register WH_GF_Multicolumn_Field_Group exception ' . $e );
}
