<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/gf-form-multicolumn/
 * @since      3.1.0
 *
 * @package    gf-form-multicolumn
 * @subpackage gf-form-multicolumn/includes/public
 */

namespace WH\GF\Multicolumn\Site;

use Exception;

class WH_GF_Multicolumn_Public_Form_Current {
	private $gfLegacyVersion;
	private $pluginDirectory;
	private $rowColumnArray = [];
	private $columnCounter;
	private $rowCounter = 1;

	private $instanceCounter;

	public function __construct( $version, $form ) {
		try {
			$this->pluginDirectory = $this->get_gf_install_path();

			$this->instanceCounter = 0;

			$this->calculate_row_and_column_count( $form );

			// Stores form legacy value for ul/li (1), or divs (2) layout
			$this->gfLegacyVersion = isset( $form['markupVersion'] ) ? (int) $form['markupVersion'] : 1;

			$this->structure_form_elements();
		}
		catch ( Exception $e ) {
			error_log( $e->getMessage() );
		}
	}

	/**
	 * @throws Exception
	 */
	private function get_gf_install_path() {

		if ( file_exists( WP_PLUGIN_DIR .
		                  '/gravityforms/gravityforms.php' ) ) {
			return ( WP_PLUGIN_DIR . '/gravityforms/gravityforms.php' );
		} else if ( file_exists( WPMU_PLUGIN_DIR .
		                         '/gravityforms/gravityforms.php' ) ) {
			return ( WPMU_PLUGIN_DIR . '/gravityforms/gravityforms.php' );
		}
		throw new Exception( 'Gravity Forms Multiple Columns cannot locate Gravity Forms plugin. Abort processing.' );
	}

	private function calculate_row_and_column_count( $form ) {
		$rowCount    = 1;
		$columnCount = 0;

		foreach ( $form['fields'] as $formField ) {
			if ( $formField['type'] === 'column_start' ) {
				// Determine counters based on form state
				$columnCount = 1;
			} elseif ( $formField['type'] === 'column_break' ) {
				$columnCount ++;
			} elseif ( $formField['type'] === 'column_end' ) {
				$this->rowColumnArray[ $rowCount ] = $columnCount;
				$rowCount ++;
			}
		}
	}

	private function structure_form_elements() {
		$this->instanceCounter ++;

		$this->rowCounter    = 1;
		$this->columnCounter = 0;

		$gfInstallation      = get_plugin_data( $this->pluginDirectory );
		$gravityFormsVersion = $gfInstallation['Version'];

		if ( substr_count( $gravityFormsVersion, '.' ) > 1 ) {
			$lastPeriod          = strrpos( $gravityFormsVersion, '.' ) - 1;
			$gravityFormsVersion = (float) substr( $gravityFormsVersion, 0,
			                                       strlen
			                                       ( $gravityFormsVersion )
			                                       - $lastPeriod );
		} else {
			$gravityFormsVersion = (float) $gravityFormsVersion;
		}

		if ( $gravityFormsVersion >= 2.5 && $this->gfLegacyVersion == 2 ) {
			add_filter(
				'gform_field_container',
				array ( $this, 'define_output_elements_2_5' ),
				10, 6
			);
		} else {
			add_filter(
				'gform_field_container',
				array ( $this, 'define_output_elements' ),
				10, 6
			);
		}
	}

	public function define_output_elements( $field_container, $field ) {
		if ( $this->is_gfmc_field_type( $field->type ) ) {
			$cssClass   = $field->cssClass !== '' ? ' ' . esc_attr
				($field->cssClass) : '';

			// Eliminate division by zero error
			$divisor = ( isset( $this->rowColumnArray[ $this->rowCounter ] ) > 0
			) ? $this->rowColumnArray[ $this->rowCounter ] : 1;

			$widthPercentage = floor( 100 / $divisor );

			if ( $field->type === 'column_start' ) {
				$this->columnCounter ++;

				$htmlOutput = '<li class="gfmc-column gfmc-column'
				              . $widthPercentage . ' gfmc-row-' .
				              $this->rowCounter
				              . '-column gfmc-row-' . $this->rowCounter . '-col-' .
				              $this->columnCounter . '-of-' . $divisor . $cssClass .
				              '"';

				$htmlOutput .= '><ul class="flex-wrapper flex-wrapper-' . $divisor
				               . '">';

				return $htmlOutput;
			}
			if ( $field->type === 'column_break' ) {
				$this->columnCounter ++;

				$htmlOutput = '</ul></li><li class="gfmc-column gfmc-column'
				              . $widthPercentage . ' gfmc-row-' .
				              $this->rowCounter . '-column gfmc-row-' .
				              $this->rowCounter . '-col-' .
				              $this->columnCounter . '-of-' . $divisor . $cssClass . '"';

				$htmlOutput .= '><ul class="flex-wrapper flex-wrapper-' . $divisor
				               . '">';

				return $htmlOutput;
			}
			if ( $field->type === 'column_end' ) {
				$this->columnCounter = 0;
				$this->rowCounter ++;

				return ( '</ul></li>' );
			}
			if ( $field->type === 'row_break' || $field->type === 'page' ) {
				$this->columnCounter = 0;

				return '</ul><ul class="gform_fields top_label form_sublabel_below description_below">';
			}
		}

		return ( $field_container );
	}

	public function define_output_elements_2_5( $field_container, $field, $form
	) {
		if ( $this->is_gfmc_field_type( $field->type ) ) {
			$cssClass   = $field->cssClass !== '' ? ' ' . esc_attr
				($field->cssClass) : '';
			if ( isset ($this->rowColumnArray[ $this->rowCounter ])) {
				$columnWidthCount = $this->rowColumnArray[ $this->rowCounter ] * 2;
				$columnWidth      = is_numeric( ( 100 - $columnWidthCount ) /
				                                $this->rowColumnArray[ $this->rowCounter ] )
					? floor( ( 100 - $columnWidthCount ) /
					         $this->rowColumnArray[ $this->rowCounter ] ) : 1;

				if ( $field->type === 'column_start' ) {
					return ( $this->generate_start_of_row( $cssClass,
					                                       $columnWidth ) );

				}
				if ( $field->type === 'column_break' ) {
					return ( $this->generate_break_in_row( $cssClass,
					                                       $columnWidthCount,
					                                       $columnWidth ) );
				}
				if ( $field->type === 'column_end' ) {
					return ( $this->generate_end_of_row() );
				}
			} elseif ( isset ( $this->rowColumnArray[ $this->rowCounter + 1]
			) ) {
				$this->rowCounter++;
			}
		}

		return ( $field_container );
	}

	private function is_gfmc_field_type( $fieldType ) {
		if ( $fieldType === 'column_start' || $fieldType === 'column_break'
		     || $fieldType === 'column_end' ) {
			return true;
		}

		return false;
	}

	private function generate_start_of_row( $cssClass, $columnWidth,
		$setWidth	= false
	) {
		$cssClass = !(isset($cssClass)) ? '' : $cssClass;
		$this->columnCounter ++;

		return '<div class="gfmc-container"><div class="gfield gfmc-column gfmc-field'.$cssClass.'">';
	}

	private function generate_break_in_row(
		$cssClass, $columnWidthCount, $columnWidth, $setWidth = false
	) {
		$cssClass = !(isset($cssClass)) ? '' : $cssClass;
		$this->columnCounter ++;

		return '</div><div class="gfield gfmc-column gfmc-field'.$cssClass.'">';
	}

	private function generate_end_of_row() {
		$this->columnCounter = 0;
		if ( isset( $this->rowColumnArray[ $this->rowCounter + 1 ] ) ) {
			$this->rowCounter ++;
		}

		return ( '</div></div>' );
	}
}
