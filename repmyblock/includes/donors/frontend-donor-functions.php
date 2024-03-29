<?php
/**
 * Donors
 *
 * @package    WalkTheCounty
 * @subpackage Donors
 * @copyright  Copyright (c) 2018, WalkTheCountyWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      2.2.0
 */

/**
 * Get the donor's Gravatar with a nice fallback.
 *
 * The fallback uses the donor's
 *
 * @since 2.2.0
 *
 * @param WalkTheCounty_Donor $donor
 * @param int        $size
 *
 * @return string HTML output.
 */
function walkthecounty_get_donor_avatar( $donor, $size = 60 ) {
	ob_start();
	?>
	<div class="walkthecounty-donor__image">
		<?php
		// Check if gravatar exists.
		if ( walkthecounty_validate_gravatar( $donor->email ) ) {
			// Return avatar.
			echo get_avatar( $donor->email, $size );
		} else {
			// No gravatar = output initials.
			echo $donor->get_donor_initals();
		} ?>
	</div>
	<?php

	return apply_filters( 'walkthecounty_get_donor_avatar', ob_get_clean() );

}

/**
 * Determine whether a Gravatar exists for a donor or not.
 *
 * @since 2.2.0
 *
 * @param string|int $id_or_email
 *
 * @return bool
 */
function walkthecounty_validate_gravatar( $id_or_email ) {

	//id or email code borrowed from wp-includes/pluggable.php
	$email = '';
	if ( is_numeric( $id_or_email ) ) {
		$id   = (int) $id_or_email;
		$user = get_userdata( $id );
		if ( $user ) {
			$email = $user->user_email;
		}
	} elseif ( is_object( $id_or_email ) ) {
		// No avatar for pingbacks or trackbacks
		$allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
		if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) ) {
			return false;
		}

		if ( ! empty( $id_or_email->user_id ) ) {
			$id   = (int) $id_or_email->user_id;
			$user = get_userdata( $id );
			if ( $user ) {
				$email = $user->user_email;
			}
		} elseif ( ! empty( $id_or_email->comment_author_email ) ) {
			$email = $id_or_email->comment_author_email;
		}
	} else {
		$email = $id_or_email;
	}


	$hashkey   = md5( strtolower( trim( $email ) ) );
	$cache_key = WalkTheCounty_Cache::get_key( 'walkthecounty_valid_gravatars' );
	$data      = WalkTheCounty_Cache::get( $cache_key );
	$data      = ! empty( $data ) ? $data : array();


	if ( ! array_key_exists( $hashkey, $data ) ) {
		$uri = "http://www.gravatar.com/avatar/{$hashkey}?d=404";

		$response = wp_remote_head( $uri );

		$data[ $hashkey ] = 0;

		if ( ! is_wp_error( $response ) ) {
			$data[ $hashkey ] = absint( '200' == $response['response']['code'] );
		}

		WalkTheCounty_Cache::set( $cache_key, $data, DAY_IN_SECONDS );
	}

	return (bool) $data[ $hashkey ];
}


/**
 * Add a donor comment to a donation
 *
 * @param int    $donation_id  The donation ID to store a note for.
 * @param int    $donor        The donor ID to store a note for.
 * @param string $note         The note to store.
 * @param array  $comment_args Comment arguments.
 *
 * @since 2.2.0
 *
 * @return int The new note ID
 */
function walkthecounty_insert_donor_donation_comment( $donation_id, $donor, $note, $comment_args = array() ) {
	// Backward compatibility.
	if( ! walkthecounty_has_upgrade_completed('v230_move_donation_note' ) ) {
		$comment_args = wp_parse_args(
			$comment_args,
			array(
				'comment_approved' => 0,
				'comment_parent'   => walkthecounty_get_payment_form_id( $donation_id )
			)
		);

		$comment_id = WalkTheCounty_Comment::add( $donation_id, $note, 'payment', $comment_args );

		update_comment_meta( $comment_id, '_walkthecounty_donor_id', $donor );

		return $comment_id;
	}

	$comment_id = WalkTheCounty_Comment::add(
		array(
			'comment_ID'      => isset( $comment_args['comment_ID'] ) ? absint( $comment_args['comment_ID'] ) : 0,
			'comment_parent'  => $donation_id,
			'comment_content' => $note,
			'comment_type'    => 'donor_donation',
		)
	);

	WalkTheCounty()->comment->db_meta->update_meta( $comment_id, '_walkthecounty_donor_id', $donor );
	WalkTheCounty()->comment->db_meta->update_meta( $comment_id, '_walkthecounty_form_id', walkthecounty_get_payment_form_id( $donation_id ) );

	return $comment_id;
}


/**
 * Retrieve all donor comment attached to a donation
 *
 * Note: currently donor can only add one comment per donation
 *
 * @param int    $donation_id The donation ID to retrieve comment for.
 * @param int    $donor_id    The donor ID to retrieve comment for.
 * @param string $search      Search for comment that contain a search term.
 *
 * @since 2.2.0
 *
 * @return WP_Comment|array
 */
function walkthecounty_get_donor_donation_comment( $donation_id, $donor_id, $search = '' ) {
	// Backward compatibility.
	if( ! walkthecounty_has_upgrade_completed('v230_move_donation_note' ) ) {

		$comments = WalkTheCounty_Comment::get(
			$donation_id,
			'payment',
			array(
				'number'     => 1,
				'meta_query' => array(
					array(
						'key'   => '_walkthecounty_donor_id',
						'value' => $donor_id
					)
				)
			),
			$search
		);

		$comment = ! empty( $comments ) ? current( $comments ) : array();

		return $comment;
	}

	$comments = WalkTheCounty()->comment->db->get_comments( array(
		'number'         => 1,
		'comment_parent' => $donation_id,
		'comment_type'   => 'donor_donation',
	) );

	return ( ! empty( $comments ) ? current( $comments ) : array() );
}

/**
 * Retrieve donor comment id attached to a donation
 *
 * Note: currently donor can only add one comment per donation
 *
 * @param int    $donation_id The donation ID to retrieve comment for.
 * @param int    $donor_id    The donor ID to retrieve comment for.
 * @param string $search      Search for comment that contain a search term.
 *
 * @since 2.2.0
 *
 * @return int
 */
function walkthecounty_get_donor_donation_comment_id( $donation_id, $donor_id, $search = '' ) {
	/* @var WP_Comment|array $comment */
	$comment    = walkthecounty_get_donor_donation_comment( $donation_id, $donor_id, $search );
	$comment_id = $comment instanceof WP_Comment ? $comment->comment_ID : 0;

	return $comment_id;
}


/**
 * Gets the donor donation comment HTML
 *
 * @param WP_Comment|int $comment    The comment object or ID.
 * @param int            $payment_id The payment ID the note is connected to.
 *
 * @since 2.2.0
 *
 * @return string
 */
function walkthecounty_get_donor_donation_comment_html( $comment, $payment_id = 0 ) {

	if ( is_numeric( $comment ) ) {
		$comment = get_comment( $comment );
	}

	$date_format = walkthecounty_date_format() . ', ' . get_option( 'time_format' );

	$comment_html = sprintf(
		'<div class="walkthecounty-payment-note" id="walkthecounty-payment-note-%s"><p><strong>%s</strong>&nbsp;&ndash;&nbsp;<span style="color:#aaa;font-style:italic;">%s</span><br/>%s</p></div>',
		$comment->comment_ID,
		get_comment_author( $comment->comment_ID ),
		date_i18n( $date_format, strtotime( $comment->comment_date ) ),
		$comment->comment_content
	);

	return $comment_html;

}

/**
 * Retrieves a name initials (first name and last name).
 *
 * @since   2.3.0
 *
 * @param array $args
 *
 * @return string
 */
function walkthecounty_get_name_initial( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'firstname' => '',
			'lastname'  => '',
		)
	);

	$first_name_initial = mb_substr( $args['firstname'], 0, 1, 'utf-8' );
	$last_name_initial  = mb_substr( $args['lastname'], 0, 1, 'utf-8' );

	$name_initial = trim( $first_name_initial . $last_name_initial );

	/**
	 * Filter the name initial
	 *
	 * @since 2.3.0
	 */
	return apply_filters( 'walkthecounty_get_name_initial', $name_initial, $args );
}
