<?php
/*
 * Settings class for External Users settings
 *
 * @copyright   Copyright (c) 2020, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       7.2
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/*
 * OW_External_User_Settings Class
 *
 * @since 7.2
 */

class OW_External_User_Settings {

	// external user settings option name
	protected $ow_external_user_option_name = "oasiswf_external_user_settings";

	/**
	 * Set things up.
	 *
	 * @since 7.2
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ) );
	}

	// White list our options using the Settings API
	public function init_settings() {
		register_setting( 'ow-settings-external-users', $this->ow_external_user_option_name,
			array( $this, 'validate_external_user_settings' ) );
	}

	/**
	 * Validate and sanitize all user input data
	 *
	 * @param array $input
	 *
	 * @return array
	 * @since 7.2
	 */
	public function validate_external_user_settings( array $input ) {
		$external_user_array = array();
		$error_message       = array();

		if ( array_key_exists( "ID", $input ) ) {
			$user_id = array_map( 'intval', $input['ID'] );

			// the first element in the array is always empty, so remove that element.
			// it's the one which is used to clone, when the user hits the plus sign, to add external user.
			array_splice( $user_id, 0, 1 );
			$user_id_count = count( $user_id );
		}

		if ( array_key_exists( "fname", $input ) ) {
			$user_fname = array_map( 'esc_attr', $input['fname'] );

			// the first element in the array is always empty, so remove that element.
			// it's the one which is used to clone, when the user hits the plus sign, to add external user.
			array_splice( $user_fname, 0, 1 );
		}

		if ( array_key_exists( "lname", $input ) ) {
			$user_lname = array_map( 'esc_attr', $input['lname'] );

			// the first element in the array is always empty, so remove that element.
			// it's the one which is used to clone, when the user hits the plus sign, to add external user.
			array_splice( $user_lname, 0, 1 );
		}

		if ( array_key_exists( "email", $input ) ) {
			$user_email = array_map( 'esc_attr', $input['email'] );

			// the first element in the array is always empty, so remove that element.
			// it's the one which is used to clone, when the user hits the plus sign, to add external user.
			array_splice( $user_email, 0, 1 );
		}

		if ( ! empty( $user_id_count ) && $user_id_count > 0 ) {
			for ( $index = 0; $index < $user_id_count; $index ++ ) {
				// if whole row is empty then discard it
				if ( ( $user_fname[ $index ] === "" ) && ( $user_lname[ $index ] === "" ) &&
				     ( $user_email[ $index ] === "" ) ) {
					continue;
				}

				// Validation
				if ( ( $user_fname[ $index ] === "" ) && ( $user_lname[ $index ] === "" ) ) :
					$error_index     = $index + 1;
					$error_message[] = esc_html__( "Row ", "oasisworkflow" ) . $error_index .
					                   esc_html__( " of external user details requires either First Name or Last Name.",
						                   "oasisworkflow" );
				endif;
				if ( $user_email[ $index ] === "" ) :
					$error_index     = $index + 1;
					$error_message[] = esc_html__( "Row ", "oasisworkflow" ) . $error_index .
					                   esc_html__( " of external user details requires Email Address.", "oasisworkflow" );
				endif;

				if ( ! empty( $error_message ) ) :
					add_settings_error(
						'ow-settings-external-users',
						'external_user_settings',
						'<p>' . implode( "<br>", $error_message ) . '</p>',
						'error'
					);
				endif;

				// Create unique 5 digit user ID if new external user is added
				$ID                         = isset( $user_id[ $index ] ) && $user_id[ $index ] !== 0
					? $user_id[ $index ]
					: wp_rand( 10000, 99999 );
				$external_user_array[ $ID ] = array(
					"fname" => $user_fname[ $index ],
					"lname" => $user_lname[ $index ],
					"email" => $user_email[ $index ]
				);
			}
		}

		return $external_user_array;
	}

	/**
	 * generate the page
	 *
	 * @since 7.2
	 */
	public function add_settings_page() {
		$external_user_options = get_option( $this->ow_external_user_option_name );
		?>
        <form id="external_user_settings_form" method="post" action="options.php">
			<?php
			settings_fields( 'ow-settings-external-users' ); // adds nonce for current settings page
			?>
            <div id="external-user-setting">
                <div id="settingstuff">
                    <div class="select-info">
                  <span class="description">
                     <strong><?php echo esc_html__( "Manage external users (non-WordPress users) for workflow notifications.",
		                     "oasisworkflow" ); ?></strong>
                     <strong><?php echo esc_html__( 'These users will be available on the Email tab under "Other Email Settings" section.',
		                     "oasisworkflow" ); ?></strong>
                  </span>
                    </div>
                    <div class="select-info">
						<?php $class = ""; ?>
                        <ul class="external-user-label">
                            <li><?php echo esc_html__( 'First Name', 'oasisworkflow' ); ?></li>
                            <li><?php echo esc_html__( 'Last Name', 'oasisworkflow' ); ?></li>
                            <li><?php echo esc_html__( 'Email', 'oasisworkflow' ); ?></li>
                            <li></li>
                        </ul>
                        <div class="external-user-block">
                            <div class="owf-hidden">
                                <input type="hidden" name="<?php echo esc_attr($this->ow_external_user_option_name) ?>[ID][]"
                                       value=""/>
                                <input type="text" class="regular-text eu-name"
                                       name="<?php echo esc_attr($this->ow_external_user_option_name) ?>[fname][]" value=""/>
                                <input type="text" class="regular-text eu-name"
                                       name="<?php echo esc_attr($this->ow_external_user_option_name) ?>[lname][]" value=""/>
                                <input type="text" class="regular-text eu-name"
                                       name="<?php echo esc_attr($this->ow_external_user_option_name) ?>[email][]" value=""/>
                                <span class="icon-remove remove-external-users">
                           <img src="<?php echo esc_url( OASISWF_URL ) ?>/img/trash.png" title="remove"/>
                        </span>
                            </div>
							<?php
							if ( $external_user_options ) {
								$class = "owf-hidden";
								foreach ( $external_user_options as $key => $value ) { ?>
                                    <div class="owf-external-users">
                                        <input type="hidden"
                                               name="<?php echo esc_attr($this->ow_external_user_option_name) ?>[ID][]"
                                               value="<?php echo esc_attr($key); ?>"/>
                                        <input type="text" class="regular-text eu-name"
                                               name="<?php echo esc_attr($this->ow_external_user_option_name) ?>[fname][]"
                                               value="<?php echo esc_attr( $value['fname'] ); ?>"/>
                                        <input type="text" class="regular-text eu-name"
                                               name="<?php echo esc_attr($this->ow_external_user_option_name) ?>[lname][]"
                                               value="<?php echo esc_attr( $value['lname'] ); ?>"/>
                                        <input type="text" class="regular-text eu-name"
                                               name="<?php echo esc_attr($this->ow_external_user_option_name) ?>[email][]"
                                               value="<?php echo esc_attr( $value['email'] ); ?>"/>
                                        <span class="icon-remove remove-external-users">
                                 <img src="<?php echo esc_url( OASISWF_URL ) ?>/img/trash.png" title="remove"/>
                              </span>
                                    </div>
									<?php
								}
							}
							?>
                            <div class="no-external-user <?php echo esc_attr( $class ); ?>">
								<?php echo esc_html__( 'No external users found. Click the "+ Add User" button to add external users.',
									'oasisworkflow' ); ?>
                            </div>
                            <div class="owf-eu-button">
                                <input type="button" name="add-external-users" id="add-external-users"
                                       value="<?php esc_attr_e( '+ Add User', 'oasisworkflow' ); ?>"
                                       class="button button-primary add-external-users"/>
                            </div>
                        </div>
                    </div>
                    <div class="select-info full-width">
                        <div id="owf_settings_button_bar">
                            <input type="submit" id="settingSave"
                                   class="button button-primary button-large"
                                   value="<?php esc_attr_e( "Save", "oasisworkflow" ); ?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="col-wrap">

        </div>
		<?php
	}

}

$ow_external_user_settings = new OW_External_User_Settings();