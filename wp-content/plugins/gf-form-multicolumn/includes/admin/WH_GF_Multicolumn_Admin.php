<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/gf-form-multicolumn/
 * @since      3.1.0
 *
 * @package    gf-form-multicolumn
 * @subpackage gf-form-multicolumn/includes/admin
 */

namespace WH\GF\Multicolumn\Admin;

use DateTime;
use GFAPI;
use GFCommon;
use WH\GF\Multicolumn\Admin\Field\WH_GF_Multicolumn_Field_Column_End;
use WH\GF\Multicolumn\Admin\Field\WH_GF_Multicolumn_Field_Column_Start;
use WH\GF\Multicolumn\Admin\Field\WH_GF_Multicolumn_Field_Column_Separator;
use WH\GF\Multicolumn\Admin\Field\WH_GF_Multicolumn_Field_Group;

define( 'GF_MULTICOLUMN_FIELD_GROUP_TITLE', 'multiple_columns_fields' );

class WH_GF_Multicolumn_Admin {
	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->add_fields();

		add_action( 'gform_admin_pre_render',
		            array ( $this, 'admin_pre_render_function' ) );

		add_filter( 'gform_form_settings',
		            array ( $this, 'display_gfmc_form_settings' ),
		            10, 2 );

		add_filter( 'gform_tooltips', array ( $this, 'gfmc_tooltips' ) );

		// Called to update Settings page options
		add_filter( 'gform_pre_form_settings_save',
		            array (
			            $this,
			            'save_form_settings_to_include_gfmc_fields',
		            ) );

		// Called on form update on Edit Form page
		add_action( 'gform_after_save_form', array (
			$this,
			'update_form_settings_to_include_gfmc_fields',
		), 10, 2 );
	}

	public function admin_pre_render_function( $form ) {
		if ( wp_script_is( 'gfmc_scripts_admin' ) ) {
			echo GFCommon::is_form_editor() ? "<script type='text/javascript'>
        gform.addFilter( 'gform_validation_error_form_editor', 'gfmc_validate_form_columns', 10, 'gfmc_validate_form_columns_" . $form['id'] . "');
        </script>" : null;
		}

		return $form;
	}

	private function add_fields() {
		$gfmcFieldGroup           = new WH_GF_Multicolumn_Field_Group();
		$gfmcFieldColumnStart     = new WH_GF_Multicolumn_Field_Column_Start();
		$gfmcFieldColumnEnd       = new WH_GF_Multicolumn_Field_Column_End();
		$gfmcFieldColumnSeparator = new
		WH_GF_Multicolumn_Field_Column_Separator();
	}

	public function display_gfmc_form_settings( $settings, $form ) {
		$enable_gfmc_css_checked = isset ( $form['gfmcEnableCSS'] ) && $form['gfmcEnableCSS'] === true ?
			'checked="checked"' : '';
		$enable_gfmc_js_checked  = isset ( $form['gfmcEnableJS'] ) && $form['gfmcEnableJS'] ===
		                                                              true ? 'checked="checked"' : '';

		// Settings - CSS form element definition
		$gfmcEnableCSSHeaderString =
			'<th>' . __( 'Enable CSS', 'gf-form-multicolumn' ) .
			' ' . gform_tooltip( 'gfmc_enable_css', '', true ) .
			'</th>';

		$gfmcEnableCSSDataString = '<td>
		       <input id="gfmc_enable_css" type="checkbox" value="1" name="gfmcEnableCSS" '
		                           . $enable_gfmc_css_checked . '>
		       <label for="gfmc_enable_css">' .
		                           __( 'Load CSS Stylesheet',
		                               'gf-form-multicolumn' ) .
		                           '</label></td>';


		// Define the Settings Page custom elements layout
		// Note that the HTML name needs to align with the Settings variable
		// name
		$settings['Multiple Columns']['gfmcEnableCSS'] =
			'<tr>' . $gfmcEnableCSSHeaderString . $gfmcEnableCSSDataString . '</tr>';


		// Settings - JS form element definition
		$gfmcEnableJSHeaderString = '<th>' . __( 'Enable JS',
		                                         'gf-form-multicolumn' ) . ' ' .
		                            gform_tooltip( 'gfmc_enable_js', '',
		                                           true ) . '</th>';
		$gfmcEnableJSDataString   = '<td>
		    <input id="gfmc_enable_js" type="checkbox" value="1" name="gfmcEnableJS" ' .
		                            $enable_gfmc_js_checked . '>
		    <label for="gfmc_enable_js">' . __( 'Load JS Script',
		                                        'gf-form-multicolumn' ) . '</label></td>';

		$settings['Multiple Columns']['gfmcEnableJS']
			= '<tr>' . $gfmcEnableJSHeaderString .
			  $gfmcEnableJSDataString .
			  '</tr>';

		return $settings;
	}

	// This is a filter hook and will return the modified form
	public function save_form_settings_to_include_gfmc_fields( $form ) {
		$form = $this->set_gfmc_settings_if_not_present ( $form );

		$form['gfmcEnableCSS'] = rgpost ('gfmcEnableCSS') === '1' ? true :
			false;
		$form['gfmcEnableJS'] = rgpost ('gfmcEnableJS') === '1' ? true : false;

		return $form;
	}

	// This is a action hook and will return the modified form
	public function update_form_settings_to_include_gfmc_fields( $form ) {
		$form = $this->set_gfmc_settings_if_not_present ( $form );

		GFAPI::update_form( $form );
	}

	private function set_gfmc_settings_if_not_present ( $form ) {
		if ( !isset ( $form['gfmcEnableCSS'] ) ) {
			$form['gfmcEnableCSS'] = true;
		}
		if ( !isset ( $form['gfmcEnableJS'] ) ) {
			$form['gfmcEnableJS'] = false;
		}

		if ( ! isset ( $form['is_active'] ) ) {
			$form['is_active'] = true;
		}

		$now = new DateTime();
		if ( array_key_exists( 'date_created', $form ) ) {
			$form['date_updated'] = $now->format( 'Y-m-d H:i:s' );
		} else {
			$form['date_created'] = $now->format( 'Y-m-d H:i:s' );
		}

		if ( ! isset ( $form['is_trash'] ) ) {
			$form['is_trash'] = false;
		}

		return ( $form );
	}

	private function do_gfmc_fields_exist_in_form( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field['type'] === 'column_start' ||
			     $field['type'] === 'column_break' ||
			     $field['type'] === 'column_end' ) {
				return true;
			}
		}

		return false;
	}

	// Updates form with GFMC specific settings
	private function
	update_form_with_gfmc_settings_if_gfmc_fields_present_in_form(
		$form
	) {
		// Apply the following to the relevant multicolumn forms only
		if ( $this->do_gfmc_fields_exist_in_form( $form ) ) {
			// Check if the CSS parameter is a new addition to the form
			if ( isset ( $form['gfmcEnableCSS'] ) ) {
				$form['gfmcEnableCSS'] = rgpost( 'gfmcEnableCSS' ) === 1 ||
				                         rgpost( 'gfmcEnableCSS' ) === '1' ||
				                         rgpost( 'gfmcEnableCSS' ) === true ||
				                         rgpost( 'gfmcEnableCSS' ) === 'true';
			} else {
				$form['gfmcEnableCSS'] = true;
			}

			// Check if the JS parameter is a new addition to the form
			if ( isset ( $form['gfmcEnableJS'] ) ) {
				$form['gfmcEnableJS'] = rgpost( 'gfmcEnableJS' ) === 1 ||
				                        rgpost( 'gfmcEnableJS' ) === '1' ||
				                        rgpost( 'gfmcEnableJS' ) === true ||
				                        rgpost( 'gfmcEnableJS' ) === 'true';
			} else {
				$form['gfmcEnableJS'] = false;
			}
		} else {
			$form['gfmcEnableCSS'] = false;
			$form['gfmcEnableJS']  = false;
		}

		// Note that newly created forms lack three fields when this function
		// is implemented: is_active, date_created, and is_trash.
		if ( ! isset ( $form['is_active'] ) ) {
			$form['is_active'] = 1;
		}

		$now = new DateTime();
		if ( array_key_exists( 'date_created', $form ) ) {
			$form['date_updated'] = $now->format( 'Y-m-d H:i:s' );
		} else {
			$form['date_created'] = $now->format( 'Y-m-d H:i:s' );
		}

		if ( ! isset ( $form['is_trash'] ) ) {
			$form['is_trash'] = 0;
		}

		return $form;
	}

	public function gfmc_tooltips() {
		$tooltips['gfmc_enable_css']              = __( 'Load MultiColumn specific CSS file (note that deactivating this may result in the plugin layout breaking).',
		                                                'gf-form-multicolumn' );
		$tooltips['gfmc_enable_js']               = __( 'Load MultiColumn specific JS file which alters spacing when using conditional logic elements.',
		                                                'gf-form-multicolumn' );
		$tooltips['form_multiple_columns_fields'] = __( 'Use elements to construct column divisions in the form.',
		                                                'gf-form-multicolumn' );

		return $tooltips;
	}
}
