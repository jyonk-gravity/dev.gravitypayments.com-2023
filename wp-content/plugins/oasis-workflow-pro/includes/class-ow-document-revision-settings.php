<?php
/**
 * Settings class for Workflow document revision settings
 *
 * @copyright   Copyright (c) 2015, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * OW_Document_Revision_Settings Class
 *
 * @since 2.0
 */
class OW_Document_Revision_Settings {

	/**
	 * @var string document revision group name
	 */
	protected $ow_doc_revision_group_name = 'ow-settings-doc-revision';

	/**
	 * @var string doc revision title prefix option name
	 */
	protected $ow_doc_revision_title_prefix_option_name = 'oasiswf_doc_revision_title_prefix';

	/**
	 * @var string doc revision title suffix option name
	 */
	protected $ow_doc_revision_title_suffix_option_name = 'oasiswf_doc_revision_title_suffix';

	/**
	 * @var string copy children on revision option name
	 */
	protected $ow_copy_children_on_revision_option_name = 'oasiswf_copy_children_on_revision';

	/**
	 * @var string delete revision on copy option name
	 */
	protected $ow_delete_revision_on_copy_option_name = 'oasiswf_delete_revision_on_copy';
	
	/**
	 * @var string force preview url option name
	 * @since 10.2
	 */
	protected $ow_force_preview_url_option_name = 'oasiswf_force_preview_url';

	/**
	 * @var string permanently delete revision immediately 
	 * @since 10.2
	 */
	protected $ow_delete_revision_immediately_option_name = 'oasiswf_delete_revision_immediately';

	/**
	 * @var string activate revision process option name
	 */
	protected $ow_activate_revision_process_option_name = 'oasiswf_activate_revision_process';

	/**
	 * @var string hide compare button option name
	 */
	protected $ow_hide_compare_button_option_name = 'oasiswf_hide_compare_button';
	
	/**
	 * @var string Allow title update option name
	 * @since 10.2
	 */
	protected $ow_allow_title_update_option_name = 'oasiswf_allow_title_update';
	
	/**
	 * @var string hide compare button option name
	 */
	protected $ow_disable_workflow_4_revision_option_name = 'oasiswf_disable_workflow_4_revision';

	/**
	 * @var string revise workflow by roles option name
	 */
	protected $ow_revise_post_make_revision_overlay_option_name = 'oasiswf_revise_post_make_revision_overlay';

	/**
	 * @var string preserve revision of revised article
	 */
	protected $ow_preserve_revision_of_revised_article_option_name = 'oasiswf_preserve_revision_of_revised_article';

	/**
	 * Set things up.
	 *
	 * @since 2.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ) );
	}

	// White list our options using the Settings API
	public function init_settings() {
		register_setting( $this->ow_doc_revision_group_name, $this->ow_doc_revision_title_prefix_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );
		register_setting( $this->ow_doc_revision_group_name, $this->ow_doc_revision_title_suffix_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );
		register_setting( $this->ow_doc_revision_group_name, $this->ow_copy_children_on_revision_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );
		register_setting( $this->ow_doc_revision_group_name, $this->ow_delete_revision_on_copy_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );
		register_setting( $this->ow_doc_revision_group_name, $this->ow_force_preview_url_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );
		register_setting( $this->ow_doc_revision_group_name, $this->ow_delete_revision_immediately_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );
		register_setting( $this->ow_doc_revision_group_name, $this->ow_activate_revision_process_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );
		register_setting( $this->ow_doc_revision_group_name, $this->ow_hide_compare_button_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );
		register_setting( $this->ow_doc_revision_group_name, $this->ow_allow_title_update_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );
		register_setting( $this->ow_doc_revision_group_name, $this->ow_disable_workflow_4_revision_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );
		register_setting( $this->ow_doc_revision_group_name, $this->ow_revise_post_make_revision_overlay_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );
		register_setting( $this->ow_doc_revision_group_name, $this->ow_preserve_revision_of_revised_article_option_name,
			array( $this, 'sanitize_doc_revision_settings' ) );

	}

	/**
	 * sanitize and validate the input (if required)
	 *
	 * @param string $input
	 *
	 * @return string sanitized value
	 *
	 * @since 2.0
	 */
	public function sanitize_doc_revision_settings( $input ) {
		return sanitize_text_field( $input );
	}

	/**
	 * do validate and sanitize selected roles
	 *
	 * @param array $selected_roles
	 *
	 * @return array
	 */
	public function validate_revise_action_settings( $selected_roles ) {
		$roles = array();
		if ( count( $selected_roles ) > 0 ) {

			// Sanitize the value
			$roles = array_map( 'esc_attr', $selected_roles );

			foreach ( $selected_roles as $selected_role ) {
				array_push( $roles, $selected_role );
			}
		}

		return $roles;
	}

	/**
	 * generate the page
	 *
	 * @since 2.0
	 */
	public function add_settings_page() {
		$doc_revision_title_prefix          = get_option( $this->ow_doc_revision_title_prefix_option_name );
		$doc_revision_title_suffix          = get_option( $this->ow_doc_revision_title_suffix_option_name );
		$copy_children_on_revision          = get_option( $this->ow_copy_children_on_revision_option_name );
		$delete_revision_on_copy            = get_option( $this->ow_delete_revision_on_copy_option_name );
		$delete_revision_immediately        = get_option( $this->ow_delete_revision_immediately_option_name );
		$force_preview_url        			= get_option( $this->ow_force_preview_url_option_name );
		$hide_compare_button                = get_option( $this->ow_hide_compare_button_option_name );
		$allow_title_update                = get_option( $this->ow_allow_title_update_option_name );
		$disable_workflow_4_revision        = get_option( $this->ow_disable_workflow_4_revision_option_name );
		$activate_revision_process          = get_option( $this->ow_activate_revision_process_option_name );
		$doc_revision_make_revision_overlay = get_option( $this->ow_revise_post_make_revision_overlay_option_name );
		$preserved_revisions                = get_option( $this->ow_preserve_revision_of_revised_article_option_name );
		?>
        <form id="wf_settings_form" method="post" action="options.php">
			<?php
			settings_fields( $this->ow_doc_revision_group_name ); // adds nonce for current settings page
			?>
            <div id="workflow-terminology-setting">
                <div id="settingstuff">
                    <div class="select-info">
						<?php
						$str = "";
						if ( $activate_revision_process == "active" ) {
							$str = "checked=true";
						}
						?>
                        <label class="settings-title"><input type="checkbox"
                                                             name="<?php echo esc_attr( $this->ow_activate_revision_process_option_name ); ?>"
                                                             value="active" <?php echo esc_attr( $str ); ?> />&nbsp;&nbsp;<?php echo esc_html__( "Activate Revision process?",
								"oasisworkflow" ); ?>
                        </label>
                    </div>

                    <div class="select-info">
                        <label class="settings-title">
							<?php echo esc_html__( "Title prefix:", "oasisworkflow" ); ?>
                        </label>
                        <input type="text"
                               id="<?php echo esc_attr( $this->ow_doc_revision_title_prefix_option_name ); ?>"
                               name="<?php echo esc_attr( $this->ow_doc_revision_title_prefix_option_name ); ?>"
                               value="<?php echo esc_attr( $doc_revision_title_prefix ); ?>"/>
                        <span
                            class="description"><?php echo esc_html__( "Prefix to be added before the original title, e.g. \"Copy of\" (blank for no prefix)",
								"oasisworkflow" ); ?> </span>
                    </div>

                    <div class="select-info">
                        <label class="settings-title">
							<?php echo esc_html__( "Title suffix:", "oasisworkflow" ); ?>
                        </label>
                        <input type="text"
                               id="<?php echo esc_attr( $this->ow_doc_revision_title_suffix_option_name ); ?>"
                               name="<?php echo esc_attr( $this->ow_doc_revision_title_suffix_option_name ); ?>"
                               value="<?php echo esc_attr( $doc_revision_title_suffix ); ?>"/>
                        <span
                            class="description"><?php echo esc_html__( "Suffix to be added after the original title, e.g. \"(dup)\" (blank for no suffix)",
								"oasisworkflow" ); ?> </span>
                    </div>

                    <div class="select-info">
						<?php $check = ( $copy_children_on_revision == "yes" ) ? "checked=true" : ""; ?>
                        <input type="checkbox"
                               id="<?php echo esc_attr( $this->ow_copy_children_on_revision_option_name ); ?>"
                               name="<?php echo esc_attr( $this->ow_copy_children_on_revision_option_name ); ?>"
                               value="yes" <?php echo esc_attr( $check ); ?> />&nbsp;&nbsp;
                        <label
							for="<?php echo esc_attr( $this->ow_copy_children_on_revision_option_name ); ?>"
                            class="settings-title"><?php echo esc_html__( "Revise children articles on parent revision?",
								"oasisworkflow" ); ?> </label>
                        <br/>
                        <span class="description">
                         <?php echo esc_html__( "(Applicable to hierarchical post types)", "oasisworkflow" ); ?>
                      </span>
                    </div>

                    <div class="select-info">
						<?php $check = ( $preserved_revisions == "yes" ) ? "checked=true" : ""; ?>
                        <input type="checkbox"
                               id="<?php echo esc_attr( $this->ow_preserve_revision_of_revised_article_option_name ); ?>"
                               name="<?php echo esc_attr( $this->ow_preserve_revision_of_revised_article_option_name ); ?>"
                               value="yes" <?php echo esc_attr( $check ); ?> />&nbsp;&nbsp;
                        <label
							for="<?php echo esc_attr( $this->ow_preserve_revision_of_revised_article_option_name ); ?>"
                            class="settings-title"><?php echo esc_html__( "Preserve the revisions of the revised article?",
								"oasisworkflow" ); ?> </label>
                        <span class="description">
                         <?php echo esc_html__( "(Useful for strict auditing purposes)", "oasisworkflow" ); ?>
                      </span>
                        <br/>
                        <span class="description">
								 	<?php echo esc_html__( "(When updating the published article with revised content, copy the revisions of the revised article.)",
									    "oasisworkflow" ); ?>
                      </span>
                    </div>

                    <div class="select-info">
						<?php $check = ( $delete_revision_on_copy == "yes" ) ? "checked=true" : ""; ?>
                        <input type="checkbox"
                               id="<?php echo esc_attr( $this->ow_delete_revision_on_copy_option_name ); ?>"
                               name="<?php echo esc_attr( $this->ow_delete_revision_on_copy_option_name ); ?>"
                               value="yes" <?php echo esc_attr( $check ); ?> />&nbsp;&nbsp;
                        <label
							for="<?php echo esc_attr( $this->ow_delete_revision_on_copy_option_name ); ?>"
                            class="settings-title"><?php echo esc_html__( "Delete the revision after it's copied over to the original article?",
								"oasisworkflow" ); ?> </label>
                        <br/>
                        <span class="description">
								 	<?php echo esc_html__( "(The workflow history of the revision will get added to the workflow history of the original article.)",
									    "oasisworkflow" ); ?>
                      </span>
					  <br/>
					  <br/>
					  <label class="indented-label">
					  <?php 
					  $perma_delete_disabled = $delete_revision_on_copy == "yes" ? "" : 'disabled';
					  $check = ( $delete_revision_on_copy == "yes" && $delete_revision_immediately == "yes" ) ? "checked=true" : ""; ?>
                        <input type="checkbox"
                               id="<?php echo esc_attr( $this->ow_delete_revision_immediately_option_name ); ?>"
                               name="<?php echo esc_attr( $this->ow_delete_revision_immediately_option_name ); ?>"
                               value="yes" <?php echo esc_attr( $check ); ?> <?php echo $perma_delete_disabled; ?> />&nbsp;&nbsp;
								<?php echo esc_html__( "Permanently delete the revision immediately after the workflow is completed.", "oasisworkflow" ); ?></label>
                    </div>

					<div class="select-info">
						<?php $check = ( $force_preview_url == "yes" ) ? "checked=true" : ""; ?>
                        <input type="checkbox"
                               id="<?php echo esc_attr( $this->ow_force_preview_url_option_name ); ?>"
                               name="<?php echo esc_attr( $this->ow_force_preview_url_option_name ); ?>"
                               value="yes" <?php echo esc_attr( $check ); ?> />&nbsp;&nbsp;
                        <label
						for="<?php echo esc_attr( $this->ow_force_preview_url_option_name ); ?>"
                            class="settings-title"><?php echo esc_html__( "Force to show the preview URL on the revision?",
								"oasisworkflow" ); ?> </label>
                        <br/>
                        <span class="description">
								 	<?php echo esc_html__( "(If the preview URL is not set, it will be set to the original article URL.)",
									    "oasisworkflow" ); ?>
                      </span>
                    </div>

					<div class="select-info">
						<?php $check = ( $allow_title_update == "yes" ) ? "checked=true" : ""; ?>
                        <input type="checkbox" id="<?php echo esc_attr( $this->ow_allow_title_update_option_name ); ?>"
                               name="<?php echo esc_attr( $this->ow_allow_title_update_option_name ); ?>"
                               value="yes" <?php echo esc_attr( $check ); ?> />&nbsp;&nbsp;
                        <label class="settings-title" for="<?php echo esc_attr( $this->ow_allow_title_update_option_name ); ?>"><?php echo esc_html__( "Allow title update on revision?",
								"oasisworkflow" ); ?> </label>

						<br/>
						<span class="description">
						<?php echo esc_html__( "If the permalink breaks during task submission and sign-off within the workflow, we can regenerate the post title (post_name) to ensure the permalink is properly restored for the revision.",
						"oasisworkflow" ); ?>
                    </div>

                    <div class="select-info">
						<?php $check = ( $hide_compare_button == "yes" ) ? "checked=true" : ""; ?>
                        <input type="checkbox" id="<?php echo esc_attr( $this->ow_hide_compare_button_option_name ); ?>"
                               name="<?php echo esc_attr( $this->ow_hide_compare_button_option_name ); ?>"
                               value="yes" <?php echo esc_attr( $check ); ?> />&nbsp;&nbsp;
                        <label for="<?php echo esc_attr( $this->ow_hide_compare_button_option_name ); ?>" class="settings-title"><?php echo esc_html__( "Hide Compare Button?",
								"oasisworkflow" ); ?> </label>
                    </div>
                    
					<div class="select-info">
						<?php $check = ( $disable_workflow_4_revision == "yes" ) ? "checked=true" : ""; ?>
                        <input type="checkbox" id="<?php echo esc_attr( $this->ow_disable_workflow_4_revision_option_name ); ?>"
                               name="<?php echo esc_attr( $this->ow_disable_workflow_4_revision_option_name ); ?>"
                               value="yes" <?php echo esc_attr( $check ); ?> />&nbsp;&nbsp;
                        <label for="<?php echo esc_attr( $this->ow_disable_workflow_4_revision_option_name ); ?>" class="settings-title"><?php echo esc_html__( "Disable workflows for Revisions?",
								"oasisworkflow" ); ?> </label>
                    </div>

                    <div class="select-info">
                        <div class="list-section-heading">
                            <label class="settings-title">
								<?php echo esc_html__( "Make Revision Overlay Message:", "oasisworkflow" ); ?>
                            </label>
                            <br/>
                            <span class="description">
								 	<?php echo esc_html__( "(For published articles, display a popup window with a message to let users know that they need to revise the article before making any changes.)",
									    "oasisworkflow" ); ?>
							 	 </span>
                        </div>
                    </div>
                    <div class="ow-revision-overlay">
                     <textarea id="<?php echo esc_attr( $this->ow_revise_post_make_revision_overlay_option_name ); ?>"
                               name="<?php echo esc_attr( $this->ow_revise_post_make_revision_overlay_option_name ); ?>"
                               cols="60" rows="4"
                               class="regular-text"><?php echo esc_textarea( $doc_revision_make_revision_overlay ); ?></textarea>
                    </div>

                    <div class="select-info full-width">
                        <div id="owf_settings_button_bar">
                            <input type="submit" id="revisionSettingSave"
                                   class="button button-primary button-large"
                                   value="<?php echo esc_attr__( "Save", "oasisworkflow" ); ?>"/>
                        </div>
                    </div>

                </div>
            </div>
        </form>
		<?php
	}

}

$ow_document_revision_settings = new OW_Document_Revision_Settings();
?>