<?php
/**
 * Donors
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Donors
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Processes a donor edit.
 *
 * @param array $args The $_POST array being passed.
 *
 * @since 1.0
 *
 * @return array|bool $output Response messages
 */
function walkthecounty_edit_donor( $args ) {

	$donor_edit_role = apply_filters( 'walkthecounty_edit_donors_role', 'edit_walkthecounty_payments' );

	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die( esc_html__( 'You do not have permission to edit this donor.', 'walkthecounty' ), esc_html__( 'Error', 'walkthecounty' ), array(
			'response' => 403,
		) );
	}

	if ( empty( $args ) ) {
		return false;
	}

	// Sanitize Data.
	$args = walkthecounty_clean( $args );

	$args = wp_parse_args(
		$args,
		array(
			'walkthecounty_anonymous_donor' => 0
		)
	);

	// Verify Nonce.
	if ( ! wp_verify_nonce( $args['_wpnonce'], 'edit-donor' ) ) {
		wp_die( esc_html__( 'Cheatin&#8217; uh?', 'walkthecounty' ), esc_html__( 'Error', 'walkthecounty' ), array(
			'response' => 400,
		) );
	}

	$donor_info = $args['donor_info'];
	$donor_id   = intval( $donor_info['id'] );

	$donor = new WalkTheCounty_Donor( $donor_id );

	// Bailout, if donor id doesn't exists.
	if ( empty( $donor->id ) ) {
		return false;
	}

	$defaults = array(
		'title'   => '',
		'name'    => '',
		'user_id' => 0,
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'zip'     => '',
		'state'   => '',
		'country' => '',
	);

	$donor_info = wp_parse_args( $donor_info, $defaults );

	if ( (int) $donor_info['user_id'] !== (int) $donor->user_id ) {

		// Make sure we don't already have this user attached to a donor.
		if ( ! empty( $donor_info['user_id'] ) && false !== WalkTheCounty()->donors->get_donor_by( 'user_id', $donor_info['user_id'] ) ) {
			walkthecounty_set_error(
				'walkthecounty-invalid-donor-user_id',
				sprintf(
					/* translators: %d User ID */
					__( 'The User ID #%d is already associated with a different donor.', 'walkthecounty' ),
					$donor_info['user_id']
				)
			);
		}

		// Make sure it's actually a user.
		$user = get_user_by( 'id', $donor_info['user_id'] );
		if ( ! empty( $donor_info['user_id'] ) && false === $user ) {
			walkthecounty_set_error(
				'walkthecounty-invalid-user_id',
				sprintf(
					/* translators: %d User ID */
					__( 'The User ID #%d does not exist. Please assign an existing user.', 'walkthecounty' ),
					$donor_info['user_id']
				)
			);
		}
	}

	// Bailout, if errors are present.
	if ( walkthecounty_get_errors() ) {
		return false;
	}

	$donor->update_meta( '_walkthecounty_anonymous_donor', absint( $args['walkthecounty_anonymous_donor'] ) );

	// Save company name in when admin update donor company name from dashboard.
	$donor->update_meta( '_walkthecounty_donor_company', sanitize_text_field( $args['walkthecounty_donor_company'] ) );

	// If First name of donor is empty, then fetch the current first name of donor.
	if ( empty( $donor_info['first_name'] ) ) {
		$donor_info['first_name'] = $donor->get_first_name();
	}

	// Sanitize the inputs.
	$donor_data               = array();
	$donor_data['name']       = trim( "{$donor_info['first_name']} {$donor_info['last_name']}" );
	$donor_data['first_name'] = $donor_info['first_name'];
	$donor_data['last_name']  = $donor_info['last_name'];
	$donor_data['title']      = $donor_info['title'];
	$donor_data['user_id']    = $donor_info['user_id'];

	$donor_data = apply_filters( 'walkthecounty_edit_donor_info', $donor_data, $donor_id );

	/**
	 * Filter the address
	 *
	 * @todo unnecessary filter because we are not storing donor address to user.
	 *
	 * @since 1.0
	 */
	$address = apply_filters( 'walkthecounty_edit_donor_address', array(), $donor_id );

	$donor_data = walkthecounty_clean( $donor_data );
	$address    = walkthecounty_clean( $address );

	$output = walkthecounty_connect_user_donor_profile( $donor, $donor_data, $address );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo wp_json_encode( $output );
		wp_die();
	}

	if ( $output['success'] ) {
		wp_safe_redirect( add_query_arg(
			array(
				'post_type'       => 'walkthecounty_forms',
				'page'            => 'walkthecounty-donors',
				'view'            => 'overview',
				'id'              => $donor_id,
				'walkthecounty-messages[]' => 'profile-updated'
			),
			esc_url( admin_url( 'edit.php' ) )
		) );
	}

	exit;

}

add_action( 'walkthecounty_edit-donor', 'walkthecounty_edit_donor', 10, 1 );

/**
 * Save a donor note.
 *
 * @param array $args The $_POST array being passed.
 *
 * @since 1.0
 *
 * @return int The Note ID that was saved, or 0 if nothing was saved.
 */
function walkthecounty_donor_save_note( $args ) {

	$donor_view_role = apply_filters( 'walkthecounty_view_donors_role', 'view_walkthecounty_reports' );

	if ( ! is_admin() || ! current_user_can( $donor_view_role ) ) {
		wp_die( __( 'You do not have permission to edit this donor.', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array(
			'response' => 403,
		) );
	}

	if ( empty( $args ) ) {
		return false;
	}

	$donor_note = trim( walkthecounty_clean( $args['donor_note'] ) );
	$donor_id   = (int) $args['customer_id'];
	$nonce      = $args['add_donor_note_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'add-donor-note' ) ) {
		wp_die( __( 'Cheatin&#8217; uh?', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array(
			'response' => 400,
		) );
	}

	if ( empty( $donor_note ) ) {
		walkthecounty_set_error( 'empty-donor-note', __( 'A note is required.', 'walkthecounty' ) );
	}

	if ( walkthecounty_get_errors() ) {
		return false;
	}

	$donor    = new WalkTheCounty_Donor( $donor_id );
	$new_note = $donor->add_note( $donor_note );

	/**
	 * Fires before inserting donor note.
	 *
	 * @param int    $donor_id The ID of the donor.
	 * @param string $new_note Note content.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_pre_insert_donor_note', $donor_id, $new_note );

	if ( ! empty( $new_note ) && ! empty( $donor->id ) ) {

		ob_start();
		?>
		<div class="donor-note-wrapper dashboard-comment-wrap comment-item">
			<span class="note-content-wrap">
				<?php echo stripslashes( $new_note ); ?>
			</span>
		</div>
		<?php
		$output = ob_get_contents();
		ob_end_clean();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			echo $output;
			exit;
		}

		return $new_note;

	}

	return false;

}

add_action( 'walkthecounty_add-donor-note', 'walkthecounty_donor_save_note', 10, 1 );


/**
 * Disconnect a user ID from a donor
 *
 * @param array $args Array of arguments.
 *
 * @since 1.0
 *
 * @return bool|array If the disconnect was successful.
 */
function walkthecounty_disconnect_donor_user_id( $args ) {

	$donor_edit_role = apply_filters( 'walkthecounty_edit_donors_role', 'edit_walkthecounty_payments' );

	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this donor.', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array(
			'response' => 403,
		) );
	}

	if ( empty( $args ) ) {
		return false;
	}

	$donor_id = (int) $args['customer_id'];

	$nonce = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-donor' ) ) {
		wp_die( __( 'Cheatin&#8217; uh?', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array(
			'response' => 400,
		) );
	}

	$donor = new WalkTheCounty_Donor( $donor_id );
	if ( empty( $donor->id ) ) {
		return false;
	}

	$user_id = $donor->user_id;

	/**
	 * Fires before disconnecting user ID from a donor.
	 *
	 * @param int $donor_id The ID of the donor.
	 * @param int $user_id  The ID of the user.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_pre_donor_disconnect_user_id', $donor_id, $user_id );

	$output     = array();
	$donor_args = array(
		'user_id' => 0,
	);

	$redirect_url     = admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id=' ) . $donor_id;
	$is_donor_updated = $donor->update( $donor_args );

	if ( $is_donor_updated ) {

		// Set meta for disconnected donor id and user id for future reference if needed.
		update_user_meta( $user_id, '_walkthecounty_disconnected_donor_id', $donor->id );
		$donor->update_meta( '_walkthecounty_disconnected_user_id', $user_id );

		$redirect_url = add_query_arg(
			'walkthecounty-messages[]',
			'disconnect-user',
			$redirect_url
		);

		$output['success'] = true;

	} else {
		$output['success'] = false;
		walkthecounty_set_error( 'walkthecounty-disconnect-user-fail', __( 'Failed to disconnect user from donor.', 'walkthecounty' ) );
	}

	$output['redirect'] = $redirect_url;

	/**
	 * Fires after disconnecting user ID from a donor.
	 *
	 * @param int $donor_id The ID of the donor.
	 *
	 * @since 1.0
	 */
	do_action( 'walkthecounty_post_donor_disconnect_user_id', $donor_id );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;

}

add_action( 'walkthecounty_disconnect-userid', 'walkthecounty_disconnect_donor_user_id', 10, 1 );

/**
 * Add an email address to the donor from within the admin and log a donor note.
 *
 * @param array $args Array of arguments: nonce, donor id, and email address.
 *
 * @since 1.7
 *
 * @return mixed If DOING_AJAX echos out JSON, otherwise returns array of success (bool) and message (string).
 */
function walkthecounty_add_donor_email( $args ) {

	$donor_id = '';
	$donor_edit_role = apply_filters( 'walkthecounty_edit_donors_role', 'edit_walkthecounty_payments' );

	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this donor.', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array(
			'response' => 403,
		) );
	}

	$output = array();
	if ( empty( $args ) || empty( $args['email'] ) || empty( $args['customer_id'] ) ) {
		$output['success'] = false;
		if ( empty( $args['email'] ) ) {
			$output['message'] = __( 'Email address is required.', 'walkthecounty' );
		} elseif ( empty( $args['customer_id'] ) ) {
			$output['message'] = __( 'Donor ID is required.', 'walkthecounty' );
		} else {
			$output['message'] = __( 'An error has occurred. Please try again.', 'walkthecounty' );
		}
	} elseif ( ! wp_verify_nonce( $args['_wpnonce'], 'walkthecounty_add_donor_email' ) ) {
		$output = array(
			'success' => false,
			'message' => __( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'walkthecounty' ),
		);
	} elseif ( ! is_email( $args['email'] ) ) {
		$output = array(
			'success' => false,
			'message' => __( 'Invalid email.', 'walkthecounty' ),
		);
	} else {
		$email    = sanitize_email( $args['email'] );
		$donor_id = (int) $args['customer_id'];
		$primary  = 'true' === $args['primary'] ? true : false;
		$donor    = new WalkTheCounty_Donor( $donor_id );
		if ( false === $donor->add_email( $email, $primary ) ) {
			if ( in_array( $email, $donor->emails ) ) {
				$output = array(
					'success' => false,
					'message' => __( 'Email already associated with this donor.', 'walkthecounty' ),
				);
			} else {
				$output = array(
					'success' => false,
					'message' => __( 'Email address is already associated with another donor.', 'walkthecounty' ),
				);
			}
		} else {
			$redirect = admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id=' . $donor_id . '&walkthecounty-messages[]=email-added' );
			$output   = array(
				'success'  => true,
				'message'  => __( 'Email successfully added to donor.', 'walkthecounty' ),
				'redirect' => $redirect,
			);

			$user       = wp_get_current_user();
			$user_login = ! empty( $user->user_login ) ? $user->user_login : __( 'System', 'walkthecounty' );
			$donor_note = sprintf( __( 'Email address %1$s added by %2$s', 'walkthecounty' ), $email, $user_login );
			$donor->add_note( $donor_note );

			if ( $primary ) {
				$donor_note = sprintf( __( 'Email address %1$s set as primary by %2$s', 'walkthecounty' ), $email, $user_login );
				$donor->add_note( $donor_note );
			}
		}
	} // End if().

	do_action( 'walkthecounty_post_add_donor_email', $donor_id, $args );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;
}

add_action( 'walkthecounty_add_donor_email', 'walkthecounty_add_donor_email', 10, 1 );


/**
 * Remove an email address to the donor from within the admin and log a donor note and redirect back to the donor interface for feedback.
 *
 * @since  1.7
 *
 * @return bool|null
 */
function walkthecounty_remove_donor_email() {
	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}
	if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
		return false;
	}
	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'walkthecounty-remove-donor-email' ) ) {
		wp_die( __( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array(
			'response' => 403,
		) );
	}

	$donor = new WalkTheCounty_Donor( $_GET['id'] );
	if ( $donor->remove_email( $_GET['email'] ) ) {
		$url        = add_query_arg( 'walkthecounty-messages[]', 'email-removed', admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id=' . $donor->id ) );
		$user       = wp_get_current_user();
		$user_login = ! empty( $user->user_login ) ? $user->user_login : __( 'System', 'walkthecounty' );
		$donor_note = sprintf( __( 'Email address %1$s removed by %2$s', 'walkthecounty' ), $_GET['email'], $user_login );
		$donor->add_note( $donor_note );
	} else {
		$url = add_query_arg( 'walkthecounty-messages[]', 'email-remove-failed', admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id=' . $donor->id ) );
	}

	wp_safe_redirect( $url );
	exit;
}

add_action( 'walkthecounty_remove_donor_email', 'walkthecounty_remove_donor_email', 10 );


/**
 * Set an email address as the primary for a donor from within the admin and log a donor note
 * and redirect back to the donor interface for feedback
 *
 * @since  1.7
 *
 * @return bool|null
 */
function walkthecounty_set_donor_primary_email() {
	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}

	if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
		return false;
	}

	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'walkthecounty-set-donor-primary-email' ) ) {
		wp_die( __( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'walkthecounty' ), __( 'Error', 'walkthecounty' ), array(
			'response' => 403,
		) );
	}

	$donor = new WalkTheCounty_Donor( $_GET['id'] );

	if ( $donor->set_primary_email( $_GET['email'] ) ) {
		$url        = add_query_arg( 'walkthecounty-messages[]', 'primary-email-updated', admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id=' . $donor->id ) );
		$user       = wp_get_current_user();
		$user_login = ! empty( $user->user_login ) ? $user->user_login : __( 'System', 'walkthecounty' );
		$donor_note = sprintf( __( 'Email address %1$s set as primary by %2$s', 'walkthecounty' ), $_GET['email'], $user_login );

		$donor->add_note( $donor_note );
	} else {
		$url = add_query_arg( 'walkthecounty-messages[]', 'primary-email-failed', admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors&view=overview&id=' . $donor->id ) );
	}

	wp_safe_redirect( $url );
	exit;
}

add_action( 'walkthecounty_set_donor_primary_email', 'walkthecounty_set_donor_primary_email', 10 );


/**
 * This function will process the donor deletion.
 *
 * @param array $args Donor Deletion Arguments.
 *
 * @since 2.2
 */
function walkthecounty_process_donor_deletion( $args ) {
	// Bailout.
	if ( ! isset( $args['walkthecounty-donor-delete-confirm'] ) ) {
		return;
	}

	$donor_edit_role = apply_filters( 'walkthecounty_edit_donors_role', 'edit_walkthecounty_payments' );

	// Verify user capabilities to proceed for deleting donor.
	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die(
			esc_html__( 'You do not have permission to delete donors.', 'walkthecounty' ),
			esc_html__( 'Error', 'walkthecounty' ),
			array(
				'response' => 403,
			)
		);
	}

	$nonce_action = '';
	if ( 'delete_bulk_donor' === $args['walkthecounty_action'] ) {
		$nonce_action = 'bulk-donors';
	} elseif ( 'delete_donor' === $args['walkthecounty_action'] ) {
		$nonce_action = 'walkthecounty-delete-donor';
	}

	// Verify Nonce for deleting bulk donors.
	walkthecounty_validate_nonce( $args['_wpnonce'], $nonce_action );

	$redirect_args            = array();
	$donor_ids                = ( isset( $args['donor'] ) && is_array( $args['donor'] ) ) ? $args['donor'] : array( $args['donor_id'] );
	$redirect_args['order']   = ! empty( $args['order'] ) ? $args['order'] : 'DESC';
	$redirect_args['orderby'] = ! empty( $args['orderby'] ) ? strtolower( $args['orderby'] ) : 'id';
	$redirect_args['s']       = ! empty( $args['s'] ) ? $args['s'] : '';
	$delete_donor             = ! empty( $args['walkthecounty-donor-delete-confirm'] ) ? walkthecounty_is_setting_enabled( $args['walkthecounty-donor-delete-confirm'] ) : false;
	$delete_donation          = ! empty( $args['walkthecounty-donor-delete-records'] ) ? walkthecounty_is_setting_enabled( $args['walkthecounty-donor-delete-records'] ) : false;

	if ( count( $donor_ids ) > 0 ) {

		// Loop through the selected donors to delete.
		foreach ( $donor_ids as $donor_id ) {

			$donor = new WalkTheCounty_Donor( $donor_id );

			// Proceed only if valid donor id is provided.
			if ( $donor->id > 0 ) {

				/**
				 * Fires before deleting donor.
				 *
				 * @param int  $donor_id     The ID of the donor.
				 * @param bool $delete_donor Confirm Donor Deletion.
				 * @param bool $delete_donation  Confirm Donor related donations deletion.
				 *
				 * @since 1.0
				 */
				do_action( 'walkthecounty_pre_delete_donor', $donor->id, $delete_donor, $delete_donation );

				// Proceed only, if user confirmed whether they need to delete the donor.
				if ( $delete_donor ) {

					// Delete donor and linked donations.
					$donor_delete_status = walkthecounty_delete_donor_and_related_donation( $donor, array(
						'delete_donation' => $delete_donation,
					) );

					if ( 1 === $donor_delete_status ) {
						$redirect_args['walkthecounty-messages[]'] = 'donor-deleted';
					} elseif ( 2 === $donor_delete_status ) {
						$redirect_args['walkthecounty-messages[]'] = 'donor-donations-deleted';
					}
				} else {
					$redirect_args['walkthecounty-messages[]'] = 'confirm-delete-donor';
				}
			} else {
				$redirect_args['walkthecounty-messages[]'] = 'invalid-donor-id';
			} // End if().
		} // End foreach().
	} else {
		$redirect_args['walkthecounty-messages[]'] = 'no-donor-found';
	} // End if().

	$redirect_url = add_query_arg(
		$redirect_args,
		admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-donors' )
	);

	wp_safe_redirect( $redirect_url );
	walkthecounty_die();

}
add_action( 'walkthecounty_delete_donor', 'walkthecounty_process_donor_deletion' );
add_action( 'walkthecounty_delete_bulk_donor', 'walkthecounty_process_donor_deletion' );
