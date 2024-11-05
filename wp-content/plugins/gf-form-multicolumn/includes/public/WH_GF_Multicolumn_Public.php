<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/gf-form-multicolumn/
 * @since      3.1.1
 *
 * @package    gf-form-multicolumn
 * @subpackage gf-form-multicolumn/public
 */

namespace WH\GF\Multicolumn\Site;

\GFForms::include_addon_framework();

class WH_GF_Multicolumn_Public {
	private $plugin_name;
	private $version;

	private $form;
	// Note: it appears that the markupVersion value is available is not
	// accessible in the constructor of this class.
	private $gfLegacyVersion;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param   string  $plugin_name  The name of the plugin.
	 * @param   string  $version      The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function display() {
		add_filter( 'gform_pre_render', [ $this, 'pre_render_form' ], 10, 4 );

		// To run the conditional logic javascript mutator on AJAX form load.
		add_action( 'gform_register_init_scripts',
		            array ( $this, 'run_conditional_function' ), 10, 2 );
	}

	public function pre_render_form( $form ) {
		if ( $form !== false ) {
			$this->form = $form;

			$gfmcForm = new
			WH_GF_Multicolumn_Public_Form_Current( $this->version, $form );
		}

		// Stores form legacy value for ul/li (1), or divs (2) layout.
		$this->gfLegacyVersion = $form['markupVersion'] ?? 1;

		return $form;
	}

	public function dequeue_selected_scripts( $form = '', $is_ajax = false ) {
		if ( ! rgar( $form, 'gfmcEnableJS' ) ) {
			wp_dequeue_script( 'gfmc_scripts_public' );
		}
		if ( ! rgar( $form, 'gfmcEnableCSS' ) ) {
			wp_dequeue_style( 'gfmc_styles' );
		}
	}

	private function is_gf_form_multicolumn_element_on_page( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field['type'] === 'column_start' || $field['type'] === 'column_end' || ( $field['type'] === 'section' && ( strpos( $field['cssClass'],
			                                                                                                                         'split-start' ) !== false ) ) ) {
				return true;
			}
		}

		return false;
	}

	/* This is used for running the conditional logic mutator within AJAX
	forms. */
	public function run_conditional_function( $form ) {
		if ( $this->is_gfmc_javascript_to_be_loaded($form) ) {
			$script = 'gfFormsAddConditionalColumns(' .
			          $this->get_gfmc_javascript_version() . ')';

			\GFFormDisplay::add_init_script( $form['id'],
			                                 'gfmc-conditional-ajax',
			                                 \GFFormDisplay::ON_PAGE_RENDER,
			                                 $script );
		}
	}

	private function is_gfmc_javascript_to_be_loaded($form) {
		if ( rgar( $form, 'gfmcEnableJS' ) &&
		     ( $this->is_gf_form_multicolumn_element_on_page( $form ) ) ) {
			return true;
		}

		return false;
	}

	private function get_gfmc_javascript_version() {
		return ( floatval( \GFForms::$version ) >= 2.5 && $this->gfLegacyVersion >
		                                                  1 )
			? '2.5' : '2.4';
	}
}
