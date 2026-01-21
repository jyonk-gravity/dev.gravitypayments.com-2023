<?php

/*
 * Class for Workflow Review Rating
 *
 * @copyright   Copyright (c) 2016, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OW_Review_Rating Class
 *
 * @since 2.0
 */
class OW_Review_Rating {
	/**
	 * Set things up.
	 *
	 * @since 2.0
	 */
	public function __construct() {
		// display rating
		add_action( 'wp_ajax_hide_rating', array( $this, 'hide_rating' ) );
		add_action( 'wp_ajax_set_rating_interval', array( $this, 'set_rating_interval' ) );

		// action to count the post/pages processed and published via workflow
		add_action( 'owf_workflow_complete', array( $this, 'set_workflow_completed_post_count' ) );

		// show review rating block
		add_action( 'admin_init', array( $this, 'show_review_rating_block' ) );
	}

	/*
	 * Ajax Function - Set review notice if we have already rated the plugin
	 * @since 2.3
	 */
	public function hide_rating() {
		// check nonce
		check_ajax_referer( 'owf_rating_ajax_nonce', 'security' );
		update_option( 'oasiswf_review_notice', 'yes' );
		wp_send_json_success();
	}

	/*
	 * Ajax Function - Set review rating interval if we select to rate the plugin later
	 * @since 2.3
	 */
	public function set_rating_interval() {
		// check nonce
		check_ajax_referer( 'owf_rating_ajax_nonce', 'security' );
		$time_interval = date( 'Y-m-d', strtotime( '+48 hours' ) ); // phpcs:ignore
		update_option( 'oasiswf_review_rating_interval', $time_interval );
		wp_send_json_success();
	}

	/*
	 * Count the post/pages processed and published via workflow
	 * @since 2.3
	 */
	public function set_workflow_completed_post_count() {
		$workflow_completed_post_count = get_option( 'oasiswf_workflow_completed_post_count' );
		$count                         = $workflow_completed_post_count + 1;
		update_option( 'oasiswf_workflow_completed_post_count', $count );
	}

	/*
	 * Show the review rating block/admin notice when post/page submitted to workflow is 25/50/75
	 * @since 2.3
	 */
	public function show_review_rating_block() {
		global $wpdb;

		$already_rated       = get_option( 'oasiswf_review_notice' );
		$today               = date( "Y-m-d" ); // phpcs:ignore
		$get_rating_interval = get_option( 'oasiswf_review_rating_interval' );
		if ( ! empty( $get_rating_interval ) ) {
			$rating_interval_date_object = DateTime::createFromFormat( "Y-m-d", $get_rating_interval );
			$rating_interval_timestamp   = $rating_interval_date_object->getTimestamp();
		}
		// check whether user have already rated the plugin if not than display the notice
		if ( $already_rated == 'no' ) {
			$workflow_completed_post = get_option( 'oasiswf_workflow_completed_post_count' );

			if ( ( $workflow_completed_post > 25 && $workflow_completed_post < 35 ) &&
			     ( empty( $get_rating_interval ) || current_time( 'timestamp' ) > $rating_interval_timestamp ) ) {
				add_action( 'admin_notices', array( $this, 'show_review_rating' ) );
			}

			if ( ( $workflow_completed_post > 50 && $workflow_completed_post < 60 ) &&
			     ( empty( $get_rating_interval ) || current_time( 'timestamp' ) > $rating_interval_timestamp ) ) {
				add_action( 'admin_notices', array( $this, 'show_review_rating' ) );
			}

			if ( ( $workflow_completed_post > 75 && $workflow_completed_post < 100 ) &&
			     ( empty( $get_rating_interval ) || current_time( 'timestamp' ) > $rating_interval_timestamp ) ) {
				add_action( 'admin_notices', array( $this, 'show_review_rating' ) );
			}
		}
	}

	public function show_review_rating() {
		$ajax_nonce  = wp_create_nonce( "owf_rating_ajax_nonce" );
		$review_text = '<div class="review-notice success">';
		$review_text .= '<span>' . esc_html__( 'Thank you for using <b>Oasis Workflow', 'oasisworkflow' ) . '</b>. ';
		$review_text .= esc_html__( 'We hope Oasis Workflow has helped you to streamline your editorial review process.', 'oasisworkflow' ) . ' ';
		$review_text .= esc_html__( 'We welcome you to post a plugin review on WordPress, so that others can benefit from your experience.', 'oasisworkflow' ) . ' ';
		$review_text .= esc_html__( 'It also helps us to spread the word and boost our motivation.', 'oasisworkflow' ) . '</span>';
		$review_text .= '<ul class="review-rating-list">';
		$review_text .= '<li><a href="https://login.wordpress.org/?redirect_to=https%3A%2F%2Fwordpress.org%2Fsupport%2Fview%2Fplugin-reviews%2Foasis-workflow" target="_blank" title="' . esc_attr__( 'Ok, you deserved it', 'oasisworkflow' ) . '">' . esc_html__( 'Ok, you deserve it', 'oasisworkflow' ) . '.</a></li>';
		$review_text .= '<li><a href="javascript:void(0);" class="set-rating-interval" title="' . esc_attr__( 'Nope, may be later', 'oasisworkflow' ) . '">' . esc_html__( 'Nope, may be later', 'oasisworkflow' ) . '.</a></li>';
		$review_text .= '<li><a href="javascript:void(0);" class="hide-rating" title="' . esc_attr__( 'I already did', 'oasisworkflow' ) . '">' . esc_html__( 'I already did', 'oasisworkflow' ) . '.</a></li>';
		$review_text .= '</ul>';
		$review_text .= '<input type="hidden" name="owf_rating_ajax_nonce" id="owf_rating_ajax_nonce" value="' . $ajax_nonce . '" /></div>';

		$rating_block = OW_Utility::instance()->admin_notice( array(
			'type'    => 'review',
			'message' => $review_text
		) );
		echo $rating_block; // phpcs:ignore
	}


}

// construct an instance so that the actions get loaded
$ow_review_rating = new OW_Review_Rating();