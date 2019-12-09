<?php
/**
 * Insert donor comment to donation.
 *
 * @since 2.2.0
 *
 * @param int   $donation_id
 * @param array $donation_data
 *
 */
function __walkthecounty_insert_donor_donation_comment( $donation_id, $donation_data ) {
	$is_anonymous_donation = isset( $_POST['walkthecounty_anonymous_donation'] )
		? absint( $_POST['walkthecounty_anonymous_donation'] )
		: 0;

	if ( ! empty( $_POST['walkthecounty_comment'] ) ) {
		$comment_meta = array( 'author_email' => $donation_data['user_info']['email'] );

		if( ! walkthecounty_has_upgrade_completed('v230_move_donation_note' ) ) {
			// Backward compatibility.
			$comment_meta = array( 'comment_author_email' => $donation_data['user_info']['email'] );
		}

		$comment_id = walkthecounty_insert_donor_donation_comment(
			$donation_id,
			$donation_data['user_info']['donor_id'],
			trim( $_POST['walkthecounty_comment'] ), // We are sanitizing comment in WalkTheCounty_comment:add
			$comment_meta
		);
	}

	walkthecounty_update_meta( $donation_id, '_walkthecounty_anonymous_donation', $is_anonymous_donation );
}

add_action( 'walkthecounty_insert_payment', '__walkthecounty_insert_donor_donation_comment', 10, 2 );


/**
 * Validate donor comment
 *
 * @since 2.2.0
 */
function __walkthecounty_validate_donor_comment() {
	// Check wp_check_comment_data_max_lengths for comment length validation.
	if ( ! empty( $_POST['walkthecounty_comment'] ) ) {
		$max_lengths = wp_get_comment_fields_max_lengths();
		$comment     = walkthecounty_clean( $_POST['walkthecounty_comment'] );

		if ( mb_strlen( $comment, '8bit' ) > $max_lengths['comment_content'] ) {
			walkthecounty_set_error( 'comment_content_column_length', __( 'Your comment is too long.', 'walkthecounty' ) );
		}
	}
}
add_action( 'walkthecounty_checkout_error_checks', '__walkthecounty_validate_donor_comment', 10, 1 );


/**
 * Update donor comment status when donation status update
 *
 * @since 2.2.0
 *
 * @param $donation_id
 * @param $status
 */
function __walkthecounty_update_donor_donation_comment_status( $donation_id, $status ) {
	$approve = absint( 'publish' === $status );

	/* @var WP_Comment $note */
	$donor_comment = walkthecounty_get_donor_donation_comment( $donation_id, walkthecounty_get_payment_donor_id( $donation_id ) );

	if( $donor_comment instanceof WP_Comment ) {
		wp_set_comment_status( $donor_comment->comment_ID, (string) $approve );
	}
}

add_action( 'walkthecounty_update_payment_status', '__walkthecounty_update_donor_donation_comment_status', 10, 2 );

/**
 * Remove donor comment when donation delete
 *
 * @since 2.2.0
 *
 * @param $donation_id
 */
function __walkthecounty_remove_donor_donation_comment( $donation_id ) {
	/* @var WP_Comment $note */
	$donor_comment = walkthecounty_get_donor_donation_comment( $donation_id, walkthecounty_get_payment_donor_id( $donation_id ) );

	if( $donor_comment instanceof WP_Comment ) {
		wp_delete_comment( $donor_comment->comment_ID );
	}
}

add_action( 'walkthecounty_payment_deleted', '__walkthecounty_remove_donor_donation_comment', 10 );
