<?php

/*
 * Service class for all document revision actions
 *
 * @copyright   Copyright (c) 2015, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * OW_Revision_Service Class
 *
 * @since 2.0
 */

class OW_Revision_Service {
	/*
	 * Set things up.
	 *
	 * @since 2.0
	 */

	public function __construct() {

		// only add_action for AJAX actions

		add_action( 'wp_ajax_get_original', array( $this, 'get_original' ) );

		add_action( 'wp_ajax_get_current_revision', array( $this, 'get_current_revision' ) );
		add_action( 'wp_ajax_is_make_revision_allowed', array( $this, 'is_make_revision_allowed' ) );
		add_action( 'wp_ajax_is_post_a_revision', array( $this, 'is_post_a_revision' ) );

		add_action( 'wp_ajax_alert_make_revision_on_publish_post', array(
			$this,
			'alert_make_revision_on_publish_post'
		) );

		add_action( 'wp_ajax_save_as_new_post_draft', array( $this, 'save_as_new_post_draft_ajax' ) );

		add_action( 'wp_ajax_keep_untrashed_revision', array( $this, 'keep_untrashed_revision' ) );




		add_action( 'wp_ajax_update_published_post', array( $this, 'update_revision_to_published_post' ) );
		
		// elementor hook to show make revision overlay on frontend
		add_action( 'elementor/editor/footer', array( $this, 'elementor_make_revision_overlay' ) );

	}

	/*
	 * AJAX function - Get the original version/published version of the post
	 *
	 * @since 3.0
	 */

	public static function copy_post_addslashes_to_strings_only( $value ) {
		return is_string( $value ) ? addslashes( $value ) : $value;
	}

	public function get_original() {
		// nonce check
		check_ajax_referer( 'owf_workflow_abort_nonce', 'security' );

		/* sanitize incoming data */
		$revision_post_id = isset( $_POST["post_id"] ) ? intval( $_POST["post_id"] ) : "";

		$original_post_id = get_post_meta( $revision_post_id, '_oasis_original', true );
		$off_revsion_4_workflow = get_option( "oasiswf_disable_workflow_4_revision" );
		$disable_workflow_4_revision = ! empty( $off_revsion_4_workflow ) ? true : false;

		$response = array(
			"original_post_id"     => $original_post_id,
			"disable_workflow_4_revision" => $disable_workflow_4_revision
		);

		if ( $original_post_id ) {
			wp_send_json_success( $response );
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Function - API to check whether to display compare button or not
	 *
	 * @param $data
	 *
	 * @return mixed $response
	 *
	 * @since 6.0
	 */
	public function api_check_is_compare_revision_allowed( $data ) {
		if ( ! wp_verify_nonce( $data->get_header( 'x_wp_nonce' ), 'wp_rest' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'oasisworkflow' ) );
		}

		// capability check
		if ( ! current_user_can( 'ow_make_revision' ) || ! current_user_can( 'ow_make_revision_others' ) ) {
			return new WP_Error( 'owf_rest_compare_revision',
				esc_html__( 'You are not allowed to check for revision.', 'oasisworkflow' ),
				array( 'status' => '403' ) );

		}

		$compare_button = array(
			"is_hidden" => true
		);

		$revision_post_id = intval( $data["post_id"] );

		$original_post_id  = get_post_meta( $revision_post_id, '_oasis_original', true );
		$hideCompareButton = get_option( "oasiswf_hide_compare_button" );

		// check if we need to show the compare button or not.
		if ( $original_post_id && $hideCompareButton == "" ) {
			$compare_button["is_hidden"] = false;
		}

		return $compare_button;
	}

	/*
	 * AJAX function - Get the current revision of the post
	 *
	 * @since 3.0
	 */

	/**
	 * Function - API to pass parameters to open revision compare screen
	 *
	 * @return mixed $response
	 *
	 * @since 6.0
	 */
	public function api_revision_compare( $data ) {
		if ( ! wp_verify_nonce( $data->get_header( 'x_wp_nonce' ), 'wp_rest' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'oasisworkflow' ) );
		}
		// capability check
		if ( ! current_user_can( 'ow_make_revision' ) || ! current_user_can( 'ow_make_revision_others' ) ) {
			return new WP_Error( 'owf_rest_revision_compare',
				esc_html__( 'You are not allowed to compare revision', 'oasisworkflow' ), array( 'status' => '403' ) );
		}

		return $revision_parameters = array(
			"revisionPrepareMessage" => esc_html__( "Preparing the revision compare. If the page doesn't get redirected to the compare page in 10 seconds,",
				'oasisworkflow' ),
			"clickHereText"          => esc_html__( 'click here', 'oasisworkflow' ),
			"absoluteURL"            => get_admin_url(),
			"nonce"                  => wp_create_nonce( 'owf_compare_revision_nonce' )
		);
	}

	public function get_current_revision() {
		// nonce check
		check_ajax_referer( 'owf_make_revision_ajax_nonce', 'security' );

		/* sanitize incoming data */
		$original_post_id    = isset( $_POST["post_id"] ) ? intval( $_POST["post_id"] ) : "";
		$revision_post_id    = get_post_meta( $original_post_id, '_oasis_current_revision', true );
		$revised_post_status = get_post_status( $revision_post_id );

		// 1. Checking post_id exists or not
		// 2. if that post even exists or not
		// 3. if that post_status not euqal to trash
		if ( $revision_post_id && $revised_post_status && $revised_post_status !== 'trash' ) {
			$response = array(
				'revision_post_id'    => $revision_post_id,
				'revision_post_title' => get_the_title( $revision_post_id ),
				'revision_post_link'  => get_edit_post_link( $revision_post_id )
			);
			wp_send_json_success( $response );
		} else {
			$this->owf_get_hidden_revisions( $original_post_id );
			delete_post_meta( $original_post_id, '_oasis_current_revision' );
			wp_send_json_error();
		}
	}

	/**
	 * Deletes all post revisions of a given post, if the 'oasiswf_delete_revision_on_copy' option is set to 'yes'.
	 *
	 * @param int $original_post_id The ID of the original post.
	 * @return void
	 */
	function owf_get_hidden_revisions( $original_post_id ) {

		global $wpdb;

		$delete_revision_on_copy = get_option( 'oasiswf_delete_revision_on_copy' );

		$action_history_table = OW_Utility::instance()->get_action_history_table_name();

		$original_post = get_post( $original_post_id );

		// Check if the original post exists
		if ( ! $original_post ) {
			return;
		}

		// Arguments for get_posts
		$args = array(
			'post_type'      => $original_post->post_type, // Custom post type
			'post_status'    => 'usedrev', // Only get published posts
			'meta_query'     => array(
				array(
					'key'     => '_oasis_original',
					'value' => $original_post_id,
					'compare' => '='
				),
			),
			'posts_per_page' => -1, // Retrieve all matching posts
		);
	
		// Get the posts
		$posts = get_posts($args);
	
		// Check if any posts were found
		if (!empty($posts)) {
			foreach ($posts as $post) {
				$revision_post_id = $post->ID;
				$original_post_id = get_post_meta( $revision_post_id, '_oasis_original', true );

				if ( ( $delete_revision_on_copy == "yes" ) ) {
					if ( ! empty( $original_post_id ) ) {
						$wpdb->update(
							$action_history_table, array(
							'post_id' => $original_post_id
						), array( 'post_id' => $revision_post_id ), array(
							'%d'
						), array( '%d' )
						);
						OW_Utility::instance()->logger( "deleting the revision" );
						$wpdb->delete( $wpdb->prefix . 'posts', array( 'ID' => $revision_post_id ) );
						// delete postmeta values
						do_action( "owf_delete_revision", $revision_post_id );
						$wpdb->delete( $wpdb->prefix . 'postmeta', array( 'post_id' => $revision_post_id ) );

						OW_Utility::instance()->logger( "revision deleted $revision_post_id" );
					}
				}
			}
		}
	}

	/*
	 * AJAX function - Check if make revision is allowed
	 * also takes into consideration the owf_is_make_revision_available_for_others filter
	 *
	 * @return boolean true if revision is allowed, false if not
	 * @since 3.1
	 */

	/**
	 * Function - API to check and return existing revision data
	 *
	 * @param $data
	 *
	 * @return mixed $response
	 *
	 * @since 6.0
	 */
	public function api_get_current_revision( $data ) {

		if ( ! wp_verify_nonce( $data->get_header( 'x_wp_nonce' ), 'wp_rest' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'oasisworkflow' ) );
		}

		// capability check
		if ( ! current_user_can( 'ow_make_revision' ) || ! current_user_can( 'ow_make_revision_others' ) ) {
			return new WP_Error( 'owf_rest_get_current_revision',
				esc_html__( 'You are not allowed to create revision of the post.', 'oasisworkflow' ),
				array( 'status' => '403' ) );
		}

		/* sanitize incoming data */
		$original_post_id    = intval( sanitize_text_field( $data["post_id"] ) );
		$revision_post_id    = get_post_meta( $original_post_id, '_oasis_current_revision', true );
		$revised_post_status = get_post_status( $revision_post_id );

		if ( $revision_post_id && $revised_post_status !== 'trash' ) {
			$response = array(
				'revision_post_id' => $revision_post_id,
				'url'              => admin_url(),
				'revisionExist'    => true
			);

			return $response;
		} else {
			delete_post_meta( $original_post_id, '_oasis_current_revision' );
			$response = array(
				'url'           => admin_url(),
				'revisionExist' => false
			);

			return $response;
		}
	}

	public function is_make_revision_allowed() {
		// nonce check
		check_ajax_referer( 'owf_make_revision_ajax_nonce', 'security' );

		/* sanitize incoming data */
		$post_id = isset( $_POST["post_id"] ) ? intval( $_POST["post_id"] ) : "";
		
		$post = get_post( $post_id );

		$post_status         = get_post_status( $post->ID );
		// show 'Make Revision' to selected post/page types only
		$allowed_post_types = get_option( 'oasiswf_show_wfsettings_on_post_types' );
		$original_post_id   = get_post_meta( $post->ID, '_oasis_original', true );

		// Get all applicable roles
		$ow_process_flow    = new OW_Process_Flow();
		$is_role_applicable = $ow_process_flow->check_is_role_applicable( $post->ID );

		$is_revise_action_available = $this::is_make_revision_available_by_role( $post );

		if ( ( $post_status == 'publish' || $post_status == 'future' || $post_status == 'private' ) &&
		     in_array( $post->post_type, $allowed_post_types ) &&
		     get_option( "oasiswf_activate_revision_process" ) == "active" &&
		     get_option( "oasiswf_activate_workflow" ) == "active"
		     && ( $is_revise_action_available )
		     && $is_role_applicable == true
		     && empty( $original_post_id ) ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/*
	 * AJAX function - Checks if there is an existing revision for this original post
	 * @since 3.1
	 */

	public function is_make_revision_available_by_role( $post ) {
		$post_author_id = 0;
		if ( $post ) {
			$post_author_id = $post->post_author;
		}

		$is_revise_action_available = false;
		if ( current_user_can( 'ow_make_revision_others' ) ) {
			$is_revise_action_available = true;
		}
		if ( current_user_can( 'ow_make_revision' ) && get_current_user_id() == $post_author_id ) {
			$is_revise_action_available = true;
		}

		return $is_revise_action_available;
	}

	/**
	 * Function - API to check whether make revision allowed for the user role
	 *
	 * @param $data
	 *
	 * @return mixed $response
	 *
	 * @since 6.0
	 */
	public function api_is_make_revision_allowed( $data ) {
		if ( ! wp_verify_nonce( $data->get_header( 'x_wp_nonce' ), 'wp_rest' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'oasisworkflow' ) );
		}

		// capability check
		if ( ! current_user_can( 'ow_make_revision' ) || ! current_user_can( 'ow_make_revision_others' ) ) {
			return new WP_Error( 'owf_rest_is_make_revision_allowed',
				esc_html__( 'You are not allowed to create revision of the post.', 'oasisworkflow' ),
				array( 'status' => '403' ) );
		}

		/* sanitize incoming data */
		$post_id = intval( sanitize_text_field( $data["post_id"] ) );

		$post        = get_post( $post_id );
		$post_type   = $post->post_type;
		$post_status = $post->post_status;

		$allowed_post_types       = get_option( 'oasiswf_show_wfsettings_on_post_types' );
		$make_revision_enabled    = get_option( 'oasiswf_activate_revision_process' );
		$workflow_process_enabled = get_option( "oasiswf_activate_workflow" );

		$is_revise_action_available = $this::is_make_revision_available_by_role( $post );

		// Check for displaying revision button
		$show_button = false;
		if ( $is_revise_action_available && in_array( $post_type, $allowed_post_types ) &&
		     $make_revision_enabled == "active" && $workflow_process_enabled == "active" ) {
			$show_button = true;
		}

		// Check for displaying the modal for forcing to make revision
		$show_overlay = false;
		if ( $post_status === "publish" ) {
			$owf_skip_workflow_filter = false;
			$show_overlay             = true;

			if ( has_filter( 'owf_skip_workflow' ) ) {
				$owf_skip_workflow_filter = apply_filters( 'owf_skip_workflow', $post_id );
			}

			// check the capability and filter value
			if ( current_user_can( 'ow_skip_workflow' ) || $owf_skip_workflow_filter == true ) {
				$show_overlay = false;
			}
		}
		$response = array( "showButton" => $show_button, "showOverlay" => $show_overlay );

		return $response;
	}


	/**
	 * AJAX - check if the post that being restored from trash is a revision post or not
	 */
	public function is_post_a_revision() {
		// nonce check
		check_ajax_referer( 'owf_make_revision_ajax_nonce', 'security' );

		/* sanitize incoming data */
		$untrashed_post_id = isset( $_POST['untrash_post_id'] ) ? intval( $_POST['untrash_post_id'] ) : "";
		$original_post_id  = trim( get_post_meta( $untrashed_post_id, '_oasis_original', true ) );

		$result = array(
			"is_restored" => true
		);

		if ( ! empty( $original_post_id ) ) { // looks like the post being restored is a revision
			// lets find out if there is an existing revision for this original post
			$current_revision_post_id = trim( get_post_meta( $original_post_id, '_oasis_current_revision', true ) );

			//looks like there isn't any new revision yet, so lets connect this restored post to the original again
			if ( empty( $current_revision_post_id ) ) {
				update_post_meta( $original_post_id, '_oasis_current_revision', $untrashed_post_id );
				wp_untrash_post( $untrashed_post_id );
				wp_send_json_success( $result );
			} else {
				// we are dealing with multiple revision posts..
				ob_start();
				include_once OASISWF_PATH . 'includes/pages/subpages/make-revision-untrashed.php';
				$result = ob_get_contents();
				ob_get_clean();
				wp_send_json_success( htmlentities( $result ) );
			}
		} else { // looks like there isn't any revision, this is the original post, so simply restore it
			wp_untrash_post( $untrashed_post_id );
			wp_send_json_success( $result );
		}
	}

	/**
	 * AJAX function - Display make revision overlay on publish post before editing the post
	 *
	 * @since 3.3
	 */
	public function alert_make_revision_on_publish_post() {
		// nonce check
		check_ajax_referer( 'owf_make_revision_ajax_nonce', 'security' );

		/* sanitize incoming data */
		if ( isset( $_POST["post_id"] ) ) {
			$post_id = intval( sanitize_text_field( $_POST["post_id"] ) );
		}

		// additional filter for deciding if the user can skip the workflow or not.
		// for example, based on tag or categories etc.

		$owf_skip_workflow_filter = false;
		$show_overlay             = true;

		if ( has_filter( 'owf_skip_workflow' ) ) {
			$owf_skip_workflow_filter = apply_filters( 'owf_skip_workflow', $post_id );
		}

		// check the capability and filter value
		if ( current_user_can( 'ow_skip_workflow' ) || $owf_skip_workflow_filter == true ) {
			$show_overlay = false;
		}

		// capability check
		// if user can skip the workflow then do not show make revision overlay
		if ( ( $show_overlay == true ) &&
		     ( current_user_can( 'ow_make_revision' ) || current_user_can( 'ow_make_revision_others' ) ) ) {
			ob_start();
			include_once( OASISWF_PATH . "includes/pages/subpages/make-revision-overlay.php" );
			$result = ob_get_contents();
			ob_end_clean();

			wp_send_json_success( htmlentities( $result ) );
		} else {
			wp_send_json_error();
		}
	}

	/*
	 * AJAX function - To untrash the post
	 *
	 * @since 3.0
	 */

	public function save_as_new_post_draft_ajax() {
		// nonce check
		check_ajax_referer( 'owf_make_revision_ajax_nonce', 'security' );

		if ( ! current_user_can( 'ow_make_revision' ) && ! current_user_can( 'ow_make_revision_others' ) ) {
			wp_die( esc_html__( 'You are not allowed to make the revision.' ) );
		}

		$id = '';
		if ( isset( $_POST['post'] ) && sanitize_text_field( $_POST["post"] ) ) {
			$id = intval( $_POST['post'] );
		}

		$new_post_id  = $this->save_as_new_post( 'draft', $id );
		$redirect_url = admin_url( "post.php?action=edit&post=$new_post_id" );
		$redirect_url = apply_filters( 'owf_redirect_after_revision', $redirect_url, $new_post_id );

		wp_send_json_success( esc_sql( $redirect_url ) );
	}

	private function save_as_new_post( $status = '', $post_id = '' ) {

        if ( empty( $post_id ) ) {
			wp_die( esc_html__( 'No post to copy has been supplied!', "oasisworkflow" ) );
            return false;
		}

		$post = get_post( $post_id );

		// check capability
		$is_make_revision_allowed = $this::is_make_revision_available_by_role( $post );

		if ( ! $is_make_revision_allowed ) {
			wp_die( esc_html__( 'You are not allowed to make the revision.' ) );
		}

		// nullify $_POST (coming from ajax call) as some plugins, like PODS have a hook on save_post
		// and will fail on wp_insert_post()
		$_POST = array();

		// Copy the post and insert it
		if ( isset( $post ) && $post != null ) {
			$new_id = $this->create_copy( $post, $status );

			return $new_id;
			exit;
		} else {
			wp_die( esc_html__( 'Copy failed, could not find original:', "oasisworkflow" ) . ' ' . esc_attr( $post_id ) );
		}
	}


	/**
	 * Create a copy from a post
	 *
	 * @param $post
	 * @param string $status
	 * @param string $parent_id
	 *
	 * @return int|void|WP_Error
	 */
	public function create_copy( $post, $status = '', $parent_id = '' ) {

		global $wpdb;

		// We don't want to clone revisions
		if ( $post->post_type == 'revision' ) {
			return;
		}

		$prefix          = '';
		$suffix          = '';
		$new_post_author = '';
		if ( $post->post_type != 'attachment' ) {
			$prefix = get_option( 'oasiswf_doc_revision_title_prefix' );
			$suffix = get_option( 'oasiswf_doc_revision_title_suffix' );
			if ( ! empty( $prefix ) ) {
				$prefix .= " ";
			}
			if ( ! empty( $suffix ) ) {
				$suffix = " " . $suffix;
			}

			// reset the status of the revision to draft
			$status          = 'draft';
			$new_post_author = get_current_user_id();
		} elseif ( $post->post_type == 'attachment' ) {
			$new_post_author = $post->post_author;
		}

		$new_post = array(
			'menu_order'     => $post->menu_order,
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => htmlspecialchars_decode( htmlspecialchars( wp_slash( $post->post_content ) ) ),
			'post_excerpt'   => $post->post_excerpt,
			'post_mime_type' => $post->post_mime_type,
			'post_parent'    => $new_post_parent = empty( $parent_id ) ? $post->post_parent : $parent_id,
			'post_password'  => $post->post_password,
			'post_status'    => $new_post_status = ( empty( $status ) ) ? $post->post_status : $status,
			'post_title'     => $prefix . $post->post_title . $suffix,
			'post_type'      => $post->post_type,
		);

		$new_post_id = wp_insert_post( $new_post );

		// update the post title with the updated post title.
		// wp_update_post, strips the html from the post title, so using wpdb->update to directly update the DB
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_title' => $prefix . $post->post_title . $suffix
			),
			array( 'ID' => $new_post_id )
		);

		// If you have written a plugin which uses non-WP database tables to save
		// information about a post you can hook this action to dupe that data.
		if ( $post->post_type == 'page' ||
		     ( function_exists( 'is_post_type_hierarchical' ) && is_post_type_hierarchical( $post->post_type ) ) ) {
			do_action( 'owf_duplicate_page', $new_post_id, $post );
		} else {
			do_action( 'owf_duplicate_post', $new_post_id, $post );
		}
		delete_post_meta( $new_post_id, '_oasis_original' );
		add_post_meta( $new_post_id, '_oasis_original', $post->ID );

		// add the revised post id as a post meta data to the original post
		// this will help us to identify if there are any active revisions currently being worked on
		add_post_meta( $post->ID, '_oasis_current_revision', $new_post_id );

		return $new_post_id;
	}

	/**
	 * Function - API to create post revision
	 *
	 * @param $data
	 *
	 * @return mixed $response
	 *
	 * @since 6.0
	 */
	public function api_create_post_revision( $data ) {
		if ( ! wp_verify_nonce( $data->get_header( 'x_wp_nonce' ), 'wp_rest' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'oasisworkflow' ) );
		}

		if ( ! current_user_can( 'ow_make_revision' ) && ! current_user_can( 'ow_make_revision_others' ) ) {
			return new WP_Error( 'owf_rest_create_post_revision',
				esc_html__( 'You are not allowed to create revision of the post', 'oasisworkflow' ),
				array( 'status' => '403' ) );
		}

		$post_id = intval( $data['post_id'] );

		$new_post_id  = $this->save_as_new_post( 'draft', $post_id );
		$redirect_url = admin_url( "post.php?action=edit&post=$new_post_id" );
		$redirect_url = apply_filters( 'owf_redirect_after_revision', $redirect_url, $new_post_id );

		return $response = array(
			"revision_post_url" => esc_sql( $redirect_url )
		);

	}

	public function keep_untrashed_revision() {
		// nonce check
		check_ajax_referer( 'owf_make_revision_ajax_nonce', 'security' );

		// sanitize incoming data
		if ( isset( $_POST['untrash_post_id'] ) ) {
			$untrash_post_id = intval( $_POST['untrash_post_id'] );
		}

		// now from revise postID we will get original post id
		$original_post_id = trim( get_post_meta( $untrash_post_id, '_oasis_original', true ) );

		// now from original postID we will get current revision post id
		$current_revision_post_id = get_post_meta( $original_post_id, '_oasis_current_revision', true );
		// trash the current revision post
		wp_trash_post( $current_revision_post_id );
		// restore the trashed post
		wp_untrash_post( $untrash_post_id );

		update_post_meta( $original_post_id, '_oasis_current_revision', $untrash_post_id );
		wp_send_json_success();
	}

	/**
	 * AJAX function - To copy revision to published post if revision post is aborted
	 *
	 * @since 5.1
	 */
	public function update_revision_to_published_post() {

		// nonce check
		check_ajax_referer( 'owf_update_published_nonce', 'security' );

		if ( isset( $_POST['revision_post_id'] ) ) {
			$revised_post_id = intval( $_POST['revision_post_id'] );
		}

		OW_Utility::instance()->logger( "copy aborted revision to published post" );

		$this->update_published_post( $revised_post_id );

		$link = admin_url() . "admin.php?page=oasiswf-inbox";

		wp_send_json_success( array( 'redirect' => $link ) );
	}

	/**
	 * update published post with the revised contents
	 *
	 * @param $revised_post_id
	 */
	public function update_published_post( $revised_post_id ) {
		global $wpdb;

		$revised_post_id = intval( $revised_post_id );

		$revised_post = get_post( $revised_post_id );

		//do we want to copy revisions of revised post onto the original post
		$preserve_revision = get_option( 'oasiswf_preserve_revision_of_revised_article' );

		$original_post_id = get_post_meta( $revised_post_id, '_oasis_original', true );
		if ( empty( $original_post_id ) ) {
			return; // we are probably dealing with an incorrect article
		}

		$original_post      = get_post( $original_post_id );
		$original_post_name = $original_post->post_name;

		if ( $revised_post->post_type != 'attachment' ) {
			$prefix = get_option( 'oasiswf_doc_revision_title_prefix' );
			$suffix = get_option( 'oasiswf_doc_revision_title_suffix' );
		}

		$post_title = $revised_post->post_title;

		$special_chars = '[]()';

		// check if prefix contains parentheses
		if ( OW_Utility::instance()->has_special_char( $prefix,
			$special_chars ) ) { // then we have to do some special treatment to remove the prefix
			$prefix     = addcslashes( $prefix, $special_chars );
			$post_title = preg_replace( '@' . $prefix . '{1}@', '', $post_title );
		} else { // usual way to remove the prefix
			$post_title = preg_replace( '/' . $prefix . '/', '', $post_title );
		}

		// check if suffix contains parentheses
		if ( OW_Utility::instance()->has_special_char( $suffix,
			$special_chars ) ) { // then we have to do some special treatment to remove the suffix
			$suffix     = addcslashes( $suffix, $special_chars );
			$post_title = preg_replace( '@' . $suffix . '{1}@', '', $post_title );
		} else { // usual way to remove the suffix
			$post_title = preg_replace( '/' . $suffix . '/', '', $post_title );
		}

		// remove any whitespaces before and after the title
		$post_title = trim( $post_title );

		$ow_process_flow = new OW_Process_Flow();

		if ( $preserve_revision === 'yes' ) {
			$this->copy_revised_article_with_revisions_to_original( $revised_post, $original_post_id, $post_title );
		} else {
			$this->copy_revised_article_to_original( $revised_post, $original_post_id, $post_title );
		}

		// additional filter for allowing revised permalink changes.
		$owf_update_revision_permalink = false;
		if ( has_filter( 'owf_update_revision_permalink' ) ) {
			$owf_update_revision_permalink = apply_filters( 'owf_update_revision_permalink', $original_post_id,
				$revised_post_id );
		}

		$post_name = $original_post_name;
		if ( $owf_update_revision_permalink == true ) {
			$post_name = $revised_post->post_name;
		}

		// update the post title with the updated post title.
		// update post name, as was changing slug if prefix and suffix is empty
		// wp_update_post, strips the html from the post title, so using wpdb->update to directly update the DB
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_title' => $post_title,
				'post_name'  => $post_name
			),
			array( 'ID' => $original_post_id )
		);

		// finally change the revised post status to usedrevision again.
		$ow_process_flow->ow_update_post_status( $revised_post->ID, "usedrev" );

		// once we are done updating with revised item, delete the post metadata
		//delete_post_meta( $revised_post->ID, '_oasis_original' );
		delete_post_meta( $original_post_id, '_oasis_current_revision' );

		if ( $revised_post->post_type == 'page' || ( function_exists( 'is_post_type_hierarchical' ) &&
		                                             is_post_type_hierarchical( $revised_post->post_type ) ) ) {
			do_action( 'owf_update_published_page', $original_post_id, $revised_post );
		} else {
			do_action( 'owf_update_published_post', $original_post_id, $revised_post );
		}
	}

	/**
	 * First copies the revisions of revised article to original article and then
	 * Copies the final revised article to original article
	 *
	 * @param int $revised_post
	 * @param int $original_post_id
	 * @param string $post_title
	 *
	 * @since 4.8
	 */
	private function copy_revised_article_with_revisions_to_original( $revised_post, $original_post_id, $post_title ) {
		global $wpdb;

		$revision_args = array(
			'orderby' => 'ID',
			'order'   => 'ASC',
		);

		$revised_post_revisions = wp_get_post_revisions( $revised_post->ID, $revision_args );

		// If empty revised post revisions than directly copy revisied article to original
		if ( empty( $revised_post_revisions ) ) {

			$this->copy_revised_article_to_original( $revised_post, $original_post_id, $post_title );

		} else {

			$ow_process_flow = new OW_Process_Flow();

			$original_post = get_post( $original_post_id );

			// loop through all the revisions of the "copy-of"
			// copy contents of each revision to the original post and update it
			// This way, we do not loose any revision history made on the "copy-of".
			
			do_action( 'owf_before_revision_merge', $original_post_id, $revised_post->ID );

			foreach ( $revised_post_revisions as $revised_post_revision ) {
				$published_post = array(
					'ID'             => $original_post_id,
					'menu_order'     => $original_post->menu_order,
					'comment_status' => $revised_post_revision->comment_status,
					'ping_status'    => $revised_post_revision->ping_status,
					'post_content'   => htmlspecialchars_decode( htmlspecialchars( wp_slash( $revised_post_revision->post_content ) ) ),
					'post_excerpt'   => $revised_post_revision->post_excerpt,
					'post_mime_type' => $revised_post_revision->post_mime_type,
					'post_parent'    => $new_post_parent = empty( $parent_id ) ? $revised_post->post_parent
						: $parent_id,
					'post_password'  => $revised_post_revision->post_password,
					'post_title'     => $post_title,
					'post_type'      => $revised_post->post_type
				);

				remove_action( 'save_post', array( $ow_process_flow, 'check_unauthorized_post_update' ), 10 );

				wp_update_post( $published_post );

				add_action( 'save_post', array( $ow_process_flow, 'check_unauthorized_post_update' ), 10, 1 );

				// get the latest revision created by wp_update_post and
				$all_revisions   = wp_get_post_revisions( $original_post_id );
				$latest_revision = current( $all_revisions ); //http://php.net/manual/en/function.current.php

				if ( class_exists( 'acf_pro' ) ) { // applicable to pro version of ACF
					$this->add_post_meta_info( $latest_revision->ID, $revised_post_revision );
				}

				// update the post title with the updated post title.
				// wp_update_post, strips the html from the post title, so using wpdb->update to directly update the DB
				// update the post author on the latest revision by revision post author
				$wpdb->update(
					$wpdb->posts,
					array(
						'post_title'    => $post_title,
						'post_author'   => $revised_post_revision->post_author,
						'post_date'     => $revised_post_revision->post_date,
						'post_date_gmt' => $revised_post_revision->post_date_gmt
					),
					array( 'ID' => $latest_revision->ID )
				);

			}
			
			do_action( 'owf_after_revision_merge', $original_post_id, $revised_post->ID );
		}
	}

	/**
	 * Copies the final revised article to original article
	 *
	 * @param int $revised_post
	 * @param string $original_post_id
	 * @param array $post_title
	 *
	 * @since 4.8
	 */
	private function copy_revised_article_to_original( $revised_post, $original_post_id, $post_title ) {
		global $wpdb;

		$ow_process_flow = new OW_Process_Flow();

		$original_post = get_post( $original_post_id );

		$published_post = array(
			'ID'             => $original_post_id,
			'menu_order'     => $original_post->menu_order,
			'comment_status' => $revised_post->comment_status,
			'ping_status'    => $revised_post->ping_status,
			'post_content'   => htmlspecialchars_decode( htmlspecialchars( wp_slash( $revised_post->post_content ) ) ),
			'post_excerpt'   => $revised_post->post_excerpt,
			'post_mime_type' => $revised_post->post_mime_type,
			'post_parent'    => $new_post_parent = empty( $parent_id ) ? $revised_post->post_parent : $parent_id,
			'post_password'  => $revised_post->post_password,
			'post_title'     => $post_title,
			'post_type'      => $revised_post->post_type
		);
		remove_action( 'save_post', array( $ow_process_flow, 'check_unauthorized_post_update' ), 10 );

		wp_update_post( $published_post );

		add_action( 'save_post', array( $ow_process_flow, 'check_unauthorized_post_update' ), 10, 1 );

		// get the latest revision created by wp_update_post and
		$all_revisions   = wp_get_post_revisions( $original_post_id );
		$latest_revision = current( $all_revisions ); //http://php.net/manual/en/function.current.php

		// If revision exist than only update it.
		if ( $latest_revision ) {
			// update the post title with the updated post title.
			// wp_update_post, strips the html from the post title, so using wpdb->update to directly update the DB
			// update the post author on the latest revision by revision post author
			$wpdb->update(
				$wpdb->posts,
				array(
					'post_title'  => $post_title,
					'post_author' => $revised_post->post_author
				),
				array( 'ID' => $latest_revision->ID )
			);
		}
	}

	/**
	 * Create post meta for revisions.
	 * When creating revisions of post, call this function to add meta to revisions.
	 *
	 * @param $post_id
	 * @param $revised_post
	 *
	 * @since 4.8
	 */
	private function add_post_meta_info( $post_id, $revised_post ) {
		global $wpdb;

		// sanitize the data
		$post_id = intval( $post_id );

		// get post meta keys from the revision post
		$post_meta_keys = get_post_custom_keys( $revised_post->ID );
		if ( ! is_array( $post_meta_keys ) ) {
			$post_meta_keys = array();
		}

		if ( empty( $post_meta_keys ) ) {
			return;
		}

		// Filter to remove post meta keys
		$ignore_postmeta_keys = array();
		$ignore_keys          = apply_filters( 'owf_unset_postmeta', $ignore_postmeta_keys );

		foreach ( $post_meta_keys as $meta_key ) {
			$meta_key = trim( $meta_key );

			if ( '_edit_lock' == $meta_key || //ignore keys like _edit_last, _edit_lock
			     '_edit_last' == $meta_key ||
			     '_vc_post_settings' == $meta_key ||
			     substr( $meta_key, 0, 6 ) === "_oasis" ||
			     // ignore any keys starting with '_oasis', like _oasis_task_priority
			     substr( $meta_key, 0, 3 ) === "ow_" ||
			     in_array( $meta_key, $ignore_keys ) ) { // ignore any keys starting with 'ow_'
				continue;
			}

			$revised_meta_values = get_post_custom_values( $meta_key, $revised_post->ID );
			$meta_values_count   = count( $revised_meta_values );

			// loop through the meta values to find what's added, modified and deleted.
			for ( $i = 0; $i < $meta_values_count; $i ++ ) {
				$new_meta_value = maybe_unserialize( $revised_meta_values[ $i ] );

				$post_meta_value = get_post_meta( $post_id, $meta_key, true );
				if ( $post_meta_value != null ) {
					continue;
				}

				// add only if we do not have an existing meta for the given revision_post_id
				$new_meta_value = $this->copy_post_addslashes_deep( $new_meta_value );
				add_metadata( 'post', $post_id, $meta_key, $new_meta_value );
			}
		}
	}

	public function copy_post_addslashes_deep( $value ) {
		if ( function_exists( 'map_deep' ) ) {
			return map_deep( $value, array( 'OW_Revision_Service', 'copy_post_addslashes_to_strings_only' ) );
		} else {
			return wp_slash( $value );
		}
	}

	public function save_as_new_post_draft() {
		$id = null;
		if ( isset( $_GET['post'] ) && sanitize_text_field( $_GET["post"] ) ) {
			$id = intval( $_GET['post'] );
		}

		$new_post_id = $this->save_as_new_post( 'draft', $id );
		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		die();
	}

	/**
	 * When updating the original post with revision, sometimes the post content hasn't been changed. But even
	 * in that case, we should create a new revision of the post.
	 *
	 * Helpful in copy_revised_article_with_revisions_to_original
	 *
	 * @param $post_has_changed
	 * @param $last_revision
	 * @param $post
	 *
	 * @return bool
	 *
	 */
	public function has_post_changed( $post_has_changed, $last_revision, $post ) {
		$revision_post_id = get_post_meta( $post->ID, '_oasis_current_revision', true );

		if ( ! empty( $revision_post_id ) ) {
			return true;
		} else {
			return $post_has_changed;
		}

	}

	/**
	 * Hook - Schedule or Publish the revision
	 *
	 * @param $post_id
	 *
	 * @since 3.0
	 *
	 */
	public function schedule_or_publish_revision( $post_id ) {
		/* sanitize incoming data */
		$post_id = intval( sanitize_text_field( $post_id ) );

		OW_Utility::instance()->logger( "Inside schedule or publish revision" );

		$new_id          = $post_id;
		$ow_process_flow = new OW_Process_Flow();

		$revision_post = get_post( $new_id );

		OW_Utility::instance()->logger( "Revision Post Status:" . $revision_post->post_status );

		if ( $revision_post->post_status ==
		     "currentrev" ) { // looks like the revision post status is publish, so update the revision immediately
			OW_Utility::instance()->logger( "Inside publish revision immediately" );
			$this->update_published_post( $post_id );
		} else { // user provided a publish date (future date hopefully)

			OW_Utility::instance()->logger( "Inside scheduling a revision update event" );
			$args = array( $new_id );
			$ow_process_flow->ow_update_post_status( $new_id, "owf_scheduledrev" );
			$is_scheduled = wp_schedule_single_event( strtotime( $revision_post->post_date_gmt ),
				'oasiswf_schedule_revision_update', $args );

			$timestamp = wp_next_scheduled( 'oasiswf_schedule_revision_update', $args );
			OW_Utility::instance()->logger( "Next Scheduled Event: " . gmdate( "Y-m-d H:i:s", $timestamp ) );
		}
	}

	/**
	 * Hook - Copy the meta information of a post to another post
	 *
	 * @param $revised_post_id ID of the revision
	 * @param $original_post   original published post
	 *
	 * @since 3.0
	 */
	public function copy_post_meta_info( $revised_post_id, $original_post ) {
		$post_meta_keys = get_post_custom_keys( $original_post->ID );
		if ( empty( $post_meta_keys ) ) {
			return;
		}

		// Filter to remove post meta keys
		$ignore_postmeta_keys = array();
		$ignore_keys          = apply_filters( 'owf_unset_postmeta', $ignore_postmeta_keys );


		foreach ( $post_meta_keys as $meta_key ) {
			$meta_key_trim = trim( $meta_key );
			if ( '_edit_lock' == $meta_key_trim || //ignore keys like _edit_last, _edit_lock
			     '_edit_last' == $meta_key_trim ||
			     substr( $meta_key, 0, 6 ) === "_oasis" || // ignore any keys starting with '_oasis'
			     substr( $meta_key, 0, 3 ) === "ow_" ||
			     in_array( $meta_key_trim, $ignore_keys ) ) { // ignore any keys starting with 'ow_'
				continue;
			}
			$meta_values = get_post_custom_values( $meta_key, $original_post->ID );
			foreach ( $meta_values as $meta_value ) {
				$meta_value = maybe_unserialize( $meta_value );
				$meta_value = $this->copy_post_addslashes_deep( $meta_value );
				add_post_meta( $revised_post_id, $meta_key, $meta_value, false );
			}
		}
	}

	/*
	 * Filter Hook - Add "Make Revision" link to the post list page.
	 * Verifies various conditions before showing/hiding the "Make Revision" link.
	 * 1. is the post published/scheduled/private
	 * 2. is the post_type allowed to participate in workflows
	 * 3. is the revision process active
	 * 4. is the workflow process active
	 * 5. is make revision available to current user's role
	 * 6. no other revisions exist for this post.
	 *
	 * @since 3.0
	 */

	/**
	 * Hook - Copy the taxonomies of a post to another post
	 *
	 * @param $revised_post_id ID of the revision
	 * @param $original_post   original published post
	 *
	 * @since 3.0
	 */
	public function copy_post_copy_post_taxonomies( $revised_post_id, $original_post ) {
		global $wpdb;
		if ( isset( $wpdb->terms ) ) {
			// Clear default category (added by wp_insert_post)
			wp_set_object_terms( $revised_post_id, null, 'category' );

			// translations (wpml / polylang) should never be copied
			$ignored_taxonomies = ['post_translations'];
			$ignored_taxonomies = apply_filters( 'owf_unset_revisions_taxonomies', $ignored_taxonomies );

			$post_taxonomies = get_object_taxonomies( $original_post->post_type );
			foreach ( $post_taxonomies as $taxonomy ) {
				if ( in_array( $taxonomy, $ignored_taxonomies ) ) {
					continue;
				}
				$post_terms = wp_get_object_terms( $original_post->ID, $taxonomy, array( 'orderby' => 'term_order' ) );
				$terms      = array();
				for ( $i = 0; $i < count( $post_terms ); $i ++ ) {
					$terms[] = $post_terms[ $i ]->slug;
				}
				wp_set_object_terms( $revised_post_id, $terms, $taxonomy );
			}
		}
	}

	/**
	 * Hook - Copy the attachments
	 * It simply copies the table entries, actual file won't be duplicated
	 *
	 * @param $revised_post_id ID of the revision
	 * @param $original_post   original published post
	 *
	 * @since 3.0
	 */
	public function copy_post_copy_children( $revised_post_id, $original_post ) {
		// get children
		$children = get_posts( array(
			'post_type'   => 'any',
			'numberposts' => - 1,
			'post_status' => 'any',
			'post_parent' => $original_post->ID
		) );

		$copy_children_on_revision = get_option( 'oasiswf_copy_children_on_revision' );
		// clone old children
		foreach ( $children as $child ) {
			if ( $copy_children_on_revision == 'yes' ) {
				$this->create_copy( $child, '', $revised_post_id );
			}
		}
	}

	/**
	 * Hook - Copy the meta information of the revised post back to the published post
	 *
	 * @param $original_post_id
	 * @param $revised_post
	 *
	 * @since 3.0
	 */
	public function update_post_meta_info( $original_post_id, $revised_post ) {

		OW_Utility::instance()->logger( "inside update_post_meta_info" );

		// sanitize the data
		$original_post_id = intval( $original_post_id );

		$post_meta_keys_original = get_post_custom_keys( $original_post_id );

		if ( ! is_array( $post_meta_keys_original ) ) {
			$post_meta_keys_original = array();
		}

		$post_meta_keys_revision = get_post_custom_keys( $revised_post->ID );
		if ( ! is_array( $post_meta_keys_revision ) ) {
			$post_meta_keys_revision = array();
		}

		$post_meta_keys = array_unique( array_merge( $post_meta_keys_original, $post_meta_keys_revision ) );

		if ( empty( $post_meta_keys ) ) {
			return;
		}

		// Filter to remove post meta keys
		$ignore_postmeta_keys = array();
		$ignore_keys          = apply_filters( 'owf_unset_postmeta', $ignore_postmeta_keys );

		foreach ( $post_meta_keys as $meta_key ) {
			$meta_key = trim( $meta_key );
//         OW_Utility::instance()->logger( "the meta key is: " . $meta_key);
			if ( '_edit_lock' == $meta_key || //ignore keys like _edit_last, _edit_lock
			     '_edit_last' == $meta_key ||
			     '_vc_post_settings' == $meta_key ||
			     substr( $meta_key, 0, 6 ) === "_oasis" ||
			     // ignore any keys starting with '_oasis', like _oasis_task_priority
			     substr( $meta_key, 0, 3 ) === "ow_" ||
			     in_array( $meta_key, $ignore_keys ) ) {// ignore any keys starting with 'ow_'
				continue;
			}

			$revised_meta_values  = array();
			$original_meta_values = array();

			if ( get_post_custom_values( $meta_key, $revised_post->ID ) ) {
				$revised_meta_values = get_post_custom_values( $meta_key, $revised_post->ID );
			}

			if ( get_post_custom_values( $meta_key, $original_post_id ) ) {
				$original_meta_values = get_post_custom_values( $meta_key, $original_post_id );
			}

			// find the bigger array of the two
			$meta_values_count = count( $revised_meta_values ) > count( $original_meta_values )
				? count( $revised_meta_values ) : count( $original_meta_values );

			// loop through the meta values to find what's added, modified and deleted.
			for ( $i = 0; $i < $meta_values_count; $i ++ ) {
				$new_meta_value = "";
				// delete if the revised post doesn't have that key
				if ( count( $revised_meta_values ) >= $i + 1 ) {
					$new_meta_value = maybe_unserialize( $revised_meta_values[ $i ] );
					$new_meta_value = $this->copy_post_addslashes_deep( $new_meta_value );
				} else {
					$old_meta_value = maybe_unserialize( $original_meta_values[ $i ] );
					$old_meta_value = $this->copy_post_addslashes_deep( $old_meta_value );
//               OW_Utility::instance()->logger( "deleting from original");
					delete_post_meta( $original_post_id, $meta_key, $old_meta_value );
					continue;
				}

				// old meta values got updated, so simply update it
				if ( count( $original_meta_values ) >= $i + 1 ) {
					$old_meta_value = maybe_unserialize( $original_meta_values[ $i ] );
//               OW_Utility::instance()->logger( "updating on original");
					update_post_meta( $original_post_id, $meta_key, $new_meta_value, $old_meta_value );
				}

				// new meta values got added, so add it
				if ( count( $original_meta_values ) < $i + 1 ) {
//             	OW_Utility::instance()->logger( "adding to original");
					add_post_meta( $original_post_id, $meta_key, $new_meta_value );
				}
			}
		}
	}

	/**
	 * Hook - Copy the attachments
	 * It simply copies the table entries, actual file won't be duplicated
	 *
	 * @param $post_id
	 *
	 * @since 3.0
	 */
	public function update_post_update_children( $original_post_id, $revision_post ) {

		global $wpdb;
		// if attachments are added during the revision of the post update post parent of the attachment.
		$attached_media = get_attached_media( '', $revision_post->ID );
		if ( $attached_media ) {
			foreach ( $attached_media as $media ) {
				$wpdb->update(
					$wpdb->posts,
					array(
						'post_parent' => $original_post_id
					),
					array( 'ID' => $media->ID )
				);
			}
		}

		// get children
		$children = get_posts( array(
			'post_type'   => 'any',
			'numberposts' => - 1,
			'post_status' => 'any',
			'post_parent' => $revision_post->ID
		) );

		$copy_children_on_revision = get_option( 'oasiswf_copy_children_on_revision' );
		// update children
		foreach ( $children as $child ) {
			if ( $copy_children_on_revision == 'yes' ) {
				$this->update_published_post( $child->ID );
			}
		}
	}

	/*
	 * Check if Make Revision is available for the logged in user role.
	 * Also checks the custom role - PostAuthor, if PostAuthor is selected, check if the current_user_id is the post author
	 *
	 * @return boolean true if make revision is allowed, false if not
	 * @since 3.1
	 */

	/**
	 * Hook - delete revision after it merge into original
	 * also merge the history back to original
	 *
	 * @since 3.0
	 */
	public function delete_revision_after_merge_complete() {
		global $wpdb;

		$delete_revision_on_copy = get_option( 'oasiswf_delete_revision_on_copy' );

		$action_history_table = OW_Utility::instance()->get_action_history_table_name();

		//delete_post_meta( $revised_post->ID, '_oasis_original' );
		$args          = array(
			'post_type'   => 'any',
			'numberposts' => - 1,
			'post_status' => 'usedrev'
		);
		$revision_post = get_posts( $args );

		if ( ! empty( $revision_post ) ) {
			foreach ( $revision_post as $post ) {
				$revision_post_id = $post->ID;
				$original_post_id = get_post_meta( $revision_post_id, '_oasis_original', true );

				if ( ( $delete_revision_on_copy == "yes" ) ) {
					if ( ! empty( $original_post_id ) ) {
						$wpdb->update(
							$action_history_table, array(
							'post_id' => $original_post_id
						), array( 'post_id' => $revision_post_id ), array(
							'%d'
						), array( '%d' )
						);
						OW_Utility::instance()->logger( "deleting the revision" );
						$wpdb->delete( $wpdb->prefix . 'posts', array( 'ID' => $revision_post_id ) );
						// delete postmeta values
						do_action( "owf_delete_revision", $revision_post_id );
						$wpdb->delete( $wpdb->prefix . 'postmeta', array( 'post_id' => $revision_post_id ) );
					}
				}
			}
		}
	}

	public function new_draft_link_row( $actions, $post ) {

		$option = get_option( 'oasiswf_custom_workflow_terminology' );

		$make_revision_label = ! empty( $option['makeRevisionText'] ) ? sanitize_text_field( $option['makeRevisionText'] )
			: esc_html__( 'Make Revision', "oasisworkflow" );
		$post_status         = get_post_status( $post->ID );
		// show 'Make Revision' to selected post/page types only
		$allowed_post_types = get_option( 'oasiswf_show_wfsettings_on_post_types' );
		$original_post_id   = get_post_meta( $post->ID, '_oasis_original', true );

		// Get all applicable roles
		$ow_process_flow    = new OW_Process_Flow();
		$is_role_applicable = $ow_process_flow->check_is_role_applicable( $post->ID );

		$is_revise_action_available = $this::is_make_revision_available_by_role( $post );

		if ( ( $post_status == 'publish' || $post_status == 'future' || $post_status == 'private' ) &&
		     in_array( $post->post_type, $allowed_post_types ) &&
		     get_option( "oasiswf_activate_revision_process" ) == "active" &&
		     get_option( "oasiswf_activate_workflow" ) == "active"
		     && ( $is_revise_action_available )
		     && $is_role_applicable
		     && empty( $original_post_id ) ) {
			$actions['ow_edit_as_new_draft'] = "<a href='javascript:void(0);' class='ow-make-revision'
         	postid='$post->ID'
         	title='" . esc_attr__( 'Make Revision', "oasisworkflow" ) .
			                                   "'>{$make_revision_label}</a><span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
		}

		$hide_compare_button = get_option( "oasiswf_hide_compare_button" );

		$revision_post_id = get_post_meta( $original_post_id, '_oasis_current_revision', true );
		if ( empty( $hide_compare_button ) &&
		     ( $post_status != 'publish' || $post_status != 'future' ) &&
		     $post_status != 'trash' &&
		     ( $revision_post_id ) ) {

			$href = add_query_arg(
				array(
					'revision' => esc_attr( $revision_post_id ),
					'_nonce' => wp_create_nonce('owf_compare_revision_nonce'),
				),
				'post.php?page=oasiswf-revision'
			);
			$actions['compare_revision'] = '<a href="' . esc_url( $href ) . '" class="compare-post-revision" title="'
			                               . esc_attr__( 'Compare', "oasisworkflow" )
			                               . '">' . esc_html__( 'Compare', "oasisworkflow" ) . '</a>';
		}

		return $actions;
	}

	public function get_clone_post_link( $post_id = 0 ) {

		// sanitize the data
		$post_id = intval( sanitize_text_field( $post_id ) );

		if ( ! $post = get_post( $post_id ) ) {
			return;
		}

		$action_name = "save_as_new_post_draft";
		$action      = '?action=' . $action_name . '&post=' . $post->ID;

		return esc_url( admin_url( "admin.php" . $action ) );
	}

	/**
	 * Compare tags between the original and the revision
	 *
	 * @param int $original_post_id
	 * @param int $revision_post_id
	 *
	 * @return array comparison data with heading
	 *
	 * @since 3.4
	 * @since 5.1 return array
	 */
	public function compare_tags( $original_post_id, $revision_post_id ) {

		$data = array();

		$tags          = wp_get_post_tags( $revision_post_id );
		$revision_tags = $original_tags = array();
		foreach ( $tags as $tag ) {
			$revision_tags[] = $tag->name;
		}

		$tags = wp_get_post_tags( $original_post_id );
		foreach ( $tags as $tag ) {
			$original_tags[] = $tag->name;
		}

		$data['original_tag'] = implode( ", ", $original_tags );
		$data['revision_tag'] = implode( ", ", $revision_tags );

		return $data;
	}

	/**
	 * Compare categories between the original and the revision
	 *
	 * @param int $original_post_id
	 * @param int $revision_post_id
	 *
	 * @return array comparision data with heading
	 *
	 * @since 3.4
	 * @since 3.8 included compare for custom categories as well
	 * @since 5.1 return array
	 */
	public function compare_categories( $original_post_id, $revision_post_id ) {
		$data = array();

		$revise_categories   = $original_categories = "";
		$revise_categories   = OW_Utility::instance()->get_post_categories( $revision_post_id );
		$original_categories = OW_Utility::instance()->get_post_categories( $original_post_id );

		$data['original_category'] = $original_categories;
		$data['revision_category'] = $revise_categories;

		return $data;
	}

	/**
	 * Function - add action row link for browsing revision of the post.
	 *
	 * @param array $actions
	 * @param object $post
	 *
	 * @return array
	 * @since 5.8
	 */
//   public function ootb_revision_link_row( $actions, $post ) {
//      $post_status        = get_post_status( $post->ID );
//      $allowed_post_types = get_option( 'oasiswf_show_wfsettings_on_post_types' );
//
//      $all_revisions   = wp_get_post_revisions( $post->ID );
//      $latest_revision = current( $all_revisions );
//
//      if ( ( $post_status == 'publish' || $post_status == 'future' || $post_status == 'private' ) &&
//           in_array( $post->post_type, $allowed_post_types ) &&
//           get_option( "oasiswf_activate_workflow" ) == "active"
//           && ( $latest_revision ) ) {
//         $actions['ow_ootb_revision'] = '<a class="hide-if-no-js" href="' . admin_url() . 'revision.php?revision=' . $latest_revision->ID . '"><span aria-hidden="true">' . esc_attr( __( 'Browse Revisions', "oasisworkflow" ) ) . '</span></a>';
//      }
//
//      return $actions;
//   }

	/**
	 * Compare featured image between the original and the revision
	 *
	 * @param int $original_post_id
	 * @param int $revision_post_id
	 *
	 * @return array comparision data with heading
	 *
	 * @since 3.4
	 * @since 5.1 return array
	 */
	public function compare_featured_image( $original_post_id, $revision_post_id ) {
		$data = array();

		$revision_feature_image = wp_get_attachment_url( get_post_thumbnail_id( $revision_post_id ) );
		$original_feature_image = wp_get_attachment_url( get_post_thumbnail_id( $original_post_id ) );

		$data['original_image'] = $original_feature_image;
		$data['revision_image'] = $revision_feature_image;

		return $data;
	}

	/**
	 * Triggering worklfow for revision
	 *
	 * @param [type] $args
	 * @param [type] $enable
	 * @return void
	 */
	public function show_revision_for_workflow( $args, $enable ) {
		$_ENV = $args;

		$worklfow_ids = get_post( $post_id );

		// check if post exists or not
		if( $worklfow_ids ) :
			$oasis_is_in_workflow = get_post_meta( $post_id, '_is_oasis_workflow', true );

			$is_revision = get_post_meta( $post_id, '_is_original_exists', true );

			
		endif;
	}

	/*
	 * Function - Workaround for inconsistent wp_slash.
	 */

	/**
	 * Create comparision table if wp_text_diff() is empty
	 *
	 * @param string $original_field_data
	 * @param string $revision_field_data
	 *
	 * @return string $acf_field_diff
	 * @since 5.1
	 */
	public function get_comparison_table( $original_field_data, $revision_field_data ) {

		$acf_field_diff
			            = '<table class="diff"><colgroup><col class="content diffsplit left"><col class="content diffsplit middle"><col class="content diffsplit right"></colgroup><tbody><tr>';
		$acf_field_diff .= '<td>' . $original_field_data . '</td><td></td><td>' . $revision_field_data . '</td>';
		$acf_field_diff .= '</tr></tbody>';
		$acf_field_diff .= '</table>';

		return $acf_field_diff;
	}

	/**
	 * Function - Hide restore revision button for user not having ow_skip_workflow capability
	 *
	 * @since 5.8
	 */
	public function remove_revision_restore() {
		if ( ! current_user_can( 'ow_skip_workflow' ) ) {
			echo '<style> .restore-revision { visibility: hidden; } </style>';
		}
	}

	/**
	 * Update revision post slug if user doesn't add suffix or prefix to the revision post
	 *
	 * @since 7.9
	 */
	public function ow_revision_update_slug( $data, $postarr ) {
		$original_post_id = get_post_meta( $postarr['ID'], '_oasis_original', true );
		$oasis_is_in_workflow = get_post_meta( $postarr['ID'], '_oasis_is_in_workflow', true );
		$prefix           = get_option( 'oasiswf_doc_revision_title_prefix' );
		$suffix           = get_option( 'oasiswf_doc_revision_title_suffix' );
		$OW_PREFIX        = empty( trim( $prefix ) ) ? 'copy-of-' : strtolower( $prefix );

		$length = strlen( $OW_PREFIX );

		if ( ! empty( $original_post_id ) && ! empty( $oasis_is_in_workflow ) ) {
			if ( ! ( substr( $data['post_name'], 0, $length ) === $OW_PREFIX ) && empty( trim( $suffix ) ) ) {
				if ( ! ( strtolower( substr( $postarr['post_title'], 0, $length ) ) === $OW_PREFIX ) ) {
					$data['post_name'] = sanitize_title( $OW_PREFIX . $data['post_title'] );
				}
			}
		}

		return $data;
	}

	/**
	 * Function - Force preview permalink when needed
	 *
	 * @param string $html
	 * @param int $post_id
	 * @param string $new_title
	 * @param string $new_slug
	 * @param object $post
	 *
	 * @since 10.2
	 * 
	 * @return string
	 */
	public function force_preview_permalink_when_needed($html, $post_id, $new_title, $new_slug, $post) {
		// Only apply when the post is not published
		if ($post->post_status === 'publish') {
			return $html;
		}
	
		// Only apply when the post is a revision
		$original_post_id = get_post_meta($post_id, '_oasis_original', true);
		if (empty($original_post_id)) {
			return $html;
		}

		// Only apply when the revision setting is enabled
		$revision_setting = get_option("oasiswf_activate_revision_process");
		if ($revision_setting != "active") {
			return $html;
		}

		// Only apply when the workflow setting is enabled
		$workflow_setting = get_option("oasiswf_activate_workflow");
		if ($workflow_setting != "active") {
			return $html;
		}

		// Only apply when the workflow setting is enabled
		$force_preview_url =  get_option( "oasiswf_force_preview_url" );
		if ( $force_preview_url != "yes" ) {
			return $html;
		}
	
		// Get native WordPress preview URL
		$preview_url = get_preview_post_link($post_id);

		// If the preview URL is empty, return the original HTML
		// This can happen if the post is not in a state that allows previewing
		// or if the post is not a revision
		if (empty($preview_url)) {
			return $html;
		}

		// Check if the original post exists
		$original_post = get_post($original_post_id);
		if (empty($original_post)) {
			return $html;
		}
	
		// Make label translatable
		$label = esc_html__('Revision Preview URL:', 'oasisworkflow');
	
		// Show it in the same format WordPress uses
		$html = '<strong>' . esc_html($label) . '</strong> <a href="' . esc_url($preview_url) . '" target="_blank">' . esc_html($preview_url) . '</a>';

		$html = apply_filters('oasiswf_force_preview_permalink', $html, $post_id, $new_title, $new_slug, $post);
	
		return $html;
	}

	/**
	 * Function - Delete the revision post immediately after the merge is complete
	 *
	 * @param string $hook
	 *
	 * @since 10.2
	 */
	public function delete_revision_immediately( $hook ) {

		if ( $hook !== 'toplevel_page_oasiswf-inbox' ) return;

		if ( isset( $_GET['delete_on_load'], $_GET['post_id'] ) ) {
			
			$post_id = absint( $_GET['post_id'] );
			
			OW_Utility::instance()->logger( "delete_revision_immediately hook triggered" );

			// check if post exists or not
			$revision_post = get_post( $post_id );
			if ( ! $revision_post ) {
				OW_Utility::instance()->logger( "delete_revision_immediately ---- post not found" );
				return;
			}

			OW_Utility::instance()->logger( "delete_revision_immediately ---- post found" );

			// Check revision delete setting is enabled
			$delete_revision_on_copy = get_option( 'oasiswf_delete_revision_on_copy' );

			// Check if the revision delete immediatly setting is enabled
			$delete_revision_immediately = get_option( 'oasiswf_delete_revision_immediately' );

			if (  $delete_revision_on_copy != "yes" || $delete_revision_immediately != "yes" ) {
				OW_Utility::instance()->logger( "revision delete setting is not enabled" );
				return;
			}

			// Delete the post if it exists and user has permission
			$permanently_delete = (bool) apply_filters( "owf_permanently_delete_revision", true, $post_id );
			wp_delete_post($post_id, $permanently_delete);
			OW_Utility::instance()->logger( "revision deleted immediately $post_id:$permanently_delete" );


			// Remove the query params from the URL (for clean UI)
			wp_register_script( 'owf-inbox-cleanup', '', [], '', true );
			wp_enqueue_script( 'owf-inbox-cleanup' );
			wp_add_inline_script( 'owf-inbox-cleanup', "
				const url = new URL(window.location);
				url.searchParams.delete('post_id');
				url.searchParams.delete('delete_on_load');
				window.history.replaceState({}, document.title, url.pathname + url.search);
			" );
		}

	}
	
	public function elementor_make_revision_overlay() {
		// get current post id
		$post_id = get_the_ID();
		
		if ( empty( $post_id ) ) {
			return;
		}
		
		$post = get_post( $post_id );
		$post_status = $post->post_status;
		
		// check if the post status is published
		if ( $post_status !== 'publish' ) {
			return;
		}
		
		// additional filter for deciding if the user can skip the workflow or not.
		$owf_skip_workflow_filter = false;
		$show_overlay = true;
		
		if ( has_filter( 'owf_skip_workflow' ) ) {
			$owf_skip_workflow_filter = apply_filters( 'owf_skip_workflow', $post_id );
		}
		
		// check the capability and filter value
		if ( current_user_can( 'ow_skip_workflow' ) || $owf_skip_workflow_filter == true ) {
			$show_overlay = false;
		}
		
		if ( ! $show_overlay ) {
			return;
		}
		
		// capability check
		if ( current_user_can( 'ow_make_revision' ) || current_user_can( 'ow_make_revision_others' ) ) {
			
			wp_enqueue_script( 'owf-workflow-util', OASISWF_URL . 'js/pages/workflow-util.js', '', OASISWF_VERSION,
				true );
			wp_enqueue_script( 'owf_make_revision', OASISWF_URL . 'js/pages/subpages/make-revision.js',
				array( 'jquery' ), OASISWF_VERSION, true );
			wp_enqueue_script( 'owf_duplicate_post', OASISWF_URL . 'js/pages/subpages/ow-duplicate-post.js',
				array( 'jquery' ), OASISWF_VERSION, true );
			wp_enqueue_style( 'owf-oasis-workflow-css', OASISWF_URL . 'css/pages/oasis-workflow.css', false,
				OASISWF_VERSION, 'all' );
			OW_Plugin_Init::enqueue_and_localize_simple_modal_script();
			$ow_process_flow = new OW_Process_Flow();
			$ow_process_flow->enqueue_and_localize_make_revision_script();

			/**
			 * enqueue status dropdown js
			 *
			 * @since 2.1
			 */
			wp_register_script( 'owf-post-statuses', OASISWF_URL . 'js/pages/ow-status-dropdown.js', array( 'jquery' ),
				OASISWF_VERSION );
			wp_enqueue_script( 'owf-post-statuses' );
			
			// Add hidden fields required by the JS
			echo '<input type="hidden" id="hi_post_id" value="' . esc_attr( $post_id ) . '" />';
			echo '<input type="hidden" id="owf_make_revision" value="' . wp_create_nonce( 'owf_make_revision_ajax_nonce' ) . '" />';
			echo '<input type="hidden" id="owf_make_revision_ajax_nonce" value="' . wp_create_nonce( 'owf_make_revision_ajax_nonce' ) . '" />';
			
			// Include the overlay HTML
			// The make-revision-overlay.php file is at includes/pages/subpages/make-revision-overlay.php
			include_once( OASISWF_PATH . "includes/pages/subpages/make-revision-overlay.php" );
			
			// Add inline script to trigger the modal
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
                    // Force show it via simplemodal
                    // The make-revision-overlay.php content is hidden by default (owf-hidden class)
                    // We can clone it or use it as is.
                    // The 'make-revision-overlay-submit-div' is the ID in the php file.
                    
                    var overlayId = '#make-revision-overlay-submit-div';
                    
                    if ( $(overlayId).length > 0 ) {
                        $(overlayId).owfmodal({
                            onShow: function (dialog) {
                                var modal = this;
                                // prevent closing
                                $('.simplemodal-close').hide();
                            },
                            containerCss: {
                                height: 200, 
                                width: 400
                            }
                        });
                    }
				});
			</script>
			<style>
				/* Ensure modal is on top of Elementor */
				#simplemodal-container {
					z-index: 99999 !important;
					background-color: transparent !important;
					box-shadow: none !important;
				}
				#simplemodal-overlay {
					z-index: 99998 !important;
					background-color: rgba(0, 0, 0, 0.5) !important;
                    opacity: 0.8 !important;
				}
                
                /* Scope styles to avoid conflicts */
                body.elementor-editor-active .revision-wrap {
                    background: #fff;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                    text-align: center;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    max-width: 400px;
                    margin: 0 auto;
                }

                body.elementor-editor-active #make-revision-overlay-submit-div .dialog-title {
                    font-size: 20px;
                    font-weight: 600;
                    color: #1f2937;
                    margin-bottom: 15px;
                    display: block;
                }

                body.elementor-editor-active #make-revision-overlay-submit-div p {
                    font-size: 14px;
                    line-height: 1.5;
                    color: #4b5563;
                    margin-bottom: 25px;
                }

                body.elementor-editor-active .revision-wrap .ow-btn-group {
                    display: flex;
                    justify-content: center;
                    gap: 12px;
                    margin-top: 20px;
                }

                body.elementor-editor-active .revision-wrap input[type="button"] {
                    padding: 10px 20px;
                    font-size: 14px;
                    font-weight: 500;
                    border-radius: 5px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    border: none;
                    height: auto;
                    line-height: normal;
                }

                body.elementor-editor-active .revision-wrap input#make_revision_overlay {
                    background-color: #2271b1;
                    color: white;
                }

                body.elementor-editor-active .revision-wrap input#make_revision_overlay:hover {
                    background-color: #135e96;
                    transform: translateY(-1px);
                }

                body.elementor-editor-active .revision-wrap input#make_revision_overlay_cancel {
                    background-color: #f3f4f6;
                    color: #4b5563;
                    border: 1px solid #d1d5db;
                }

                body.elementor-editor-active .revision-wrap input#make_revision_overlay_cancel:hover {
                    background-color: #e5e7eb;
                    color: #1f2937;
                    transform: translateY(-1px);
                }

                /* Loader styling */
                body.elementor-editor-active .revision-wrap .changed-data-set span.loading {
                    display: inline-block;
                    margin-left: 8px;
                    vertical-align: middle;
                }
                
                /* Hide default title passed from PHP if not needed, or style it */
                #make-revision-overlay-submit-div > div.dialog-title {
                     display: none; /* We might want to hide the wrapper one if inner structure allows, or style it above */
                }
                
                /* Adjust existing structure visibility if needed */
                #make-revision-overlay-submit-div {
                    display: block !important; /* Managed by simplemodal but ensure content is visible inside */
                }
			</style>
			<?php
		}
	}

}

// construct an instance so that the actions get loaded
$ow_revision_service = new OW_Revision_Service();
add_action( 'owf_revision_workflow_complete', array( $ow_revision_service, 'schedule_or_publish_revision' ), 10, 1 );
add_action( 'admin_action_save_as_new_post_draft', array( $ow_revision_service, 'save_as_new_post_draft' ) );

add_action( 'oasiswf_schedule_revision_update', array( $ow_revision_service, 'update_published_post' ), 10, 1 );

/**
 * @since 10.2
 */
add_filter('get_sample_permalink_html', array( $ow_revision_service, 'force_preview_permalink_when_needed' ), 10, 5);

/**
 * @since 10.2
 */
add_action( 'admin_enqueue_scripts', array( $ow_revision_service, 'delete_revision_immediately' ), 50, 1 );

add_action( 'owf_duplicate_post', array( $ow_revision_service, 'copy_post_meta_info' ), 10, 2 );
add_action( 'owf_duplicate_page', array( $ow_revision_service, 'copy_post_meta_info' ), 10, 2 );

add_action( 'owf_update_published_post', array( $ow_revision_service, 'update_post_meta_info' ), 10, 2 );
add_action( 'owf_update_published_page', array( $ow_revision_service, 'update_post_meta_info' ), 10, 2 );

add_action( 'owf_duplicate_post', array( $ow_revision_service, 'copy_post_copy_post_taxonomies' ), 10, 2 );
add_action( 'owf_duplicate_page', array( $ow_revision_service, 'copy_post_copy_post_taxonomies' ), 10, 2 );

add_action( 'owf_update_published_post', array( $ow_revision_service, 'copy_post_copy_post_taxonomies' ), 10, 2 );
add_action( 'owf_update_published_page', array( $ow_revision_service, 'copy_post_copy_post_taxonomies' ), 10, 2 );

add_action( 'owf_duplicate_post', array( $ow_revision_service, 'copy_post_copy_children' ), 10, 2 );
add_action( 'owf_duplicate_page', array( $ow_revision_service, 'copy_post_copy_children' ), 10, 2 );

add_action( 'owf_update_published_post', array( $ow_revision_service, 'update_post_update_children' ), 10, 2 );
add_action( 'owf_update_published_page', array( $ow_revision_service, 'update_post_update_children' ), 10, 2 );

// Delete revision ( copy-of ) post
add_action( 'oasiswf_revision_delete_schedule', array( $ow_revision_service, 'delete_revision_after_merge_complete' ) );

add_filter( 'post_row_actions', array( $ow_revision_service, 'new_draft_link_row' ), 10, 2 );
add_filter( 'page_row_actions', array( $ow_revision_service, 'new_draft_link_row' ), 10, 2 );

add_filter( 'wp_save_post_revision_post_has_changed', array( $ow_revision_service, 'has_post_changed' ), 10, 3 );

// For ootb revision browsing
//add_filter( 'post_row_actions', array( $ow_revision_service, 'ootb_revision_link_row' ), 10, 2 );
//add_filter( 'page_row_actions', array( $ow_revision_service, 'ootb_revision_link_row' ), 10, 2 );
add_action( 'admin_head', array( $ow_revision_service, 'remove_revision_restore' ) );

// update slug for revision post
add_filter( 'wp_insert_post_data', array( $ow_revision_service, 'ow_revision_update_slug' ), 99, 2 );