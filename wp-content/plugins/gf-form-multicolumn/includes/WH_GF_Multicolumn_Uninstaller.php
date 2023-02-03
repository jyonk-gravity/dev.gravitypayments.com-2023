<?php

namespace WH\GF\Multicolumn\Classes;

use Exception;

/**
 * Fired during the deletion of the plugin.
 *
 * This class defines all code necessary to run during the plugin's
 * deletion.
 *
 * @since       3.1.0
 * Class        WH_GF_Multicolumn_Uninstaller
 * @package     WH\GF\Multicolumn\Classes
 * @subpackage  gf-form-multicolumn/includes
 * @author      jj <jj@webholism.com>
 */
class WH_GF_Multicolumn_Uninstaller {
	public function gfmc_uninstall() {
		$forms = \GFAPI::get_forms();
		foreach ( $forms as $form ) {
			$form = $this->remove_gfmc_fields( $form );
			try {
				\GFAPI::update_form( $form );
			}
			catch ( Exception $e ) {
				WH_GF_Multicolumn_Logger::log( 'ERROR',
				                               'Uninstall exception ' . $e . ' with form: ' .
				                               serialize( $form ) );
			}
		}
	}

	private function remove_gfmc_fields( $form ) {
		if ( isset( $form['gfmcEnableCSS'] ) ) {
			unset( $form['gfmcEnableCSS'] );
		}
		if ( isset( $form['gfmcEnableJS'] ) ) {
			unset( $form['gfmcEnableJS'] );
		}

		$fields = array_filter( $form['fields'], function ( $v, $k ) {
			return ( $this->is_gfmc_form_field( $v ) );
		}, ARRAY_FILTER_USE_BOTH );

		unset ( $form['fields'] );
		$form['fields'] = $fields;

		return $form;
	}

	private function is_gfmc_form_field( $formField ) {
		if ( ! $this->is_column_field_type( $formField )
		     && ! $this->is_section_column_field_type( $formField ) ) {
			return $formField;
		}

		return null;
	}

	private function is_column_field_type( $formField ) {
		return $formField['type'] === 'column_start' ||
		       $formField['type'] === 'column_end' ||
		       $formField['type'] === 'column_break';
	}

	private function is_section_column_field_type( $formField ) {
		if ( $formField['type'] === 'section' ) {
			if ( $formField['cssClass'] === 'split-start' ||
			     $formField['cssClass'] === 'split-end' ) {
				return true;
			}
		}

		return false;
	}
}
