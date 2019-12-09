<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     WalkTheCounty
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if AJAX works as expected
 * Note: Do not use this function before init hook.
 *
 * @since  1.0
 *
 * @param bool $force Flag to test ajax by discarding cache result
 *
 * @return bool True if AJAX works, false otherwise
 */
function walkthecounty_test_ajax_works( $force = false ) {
	// Handle ajax.
	if ( doing_action( 'wp_ajax_nopriv_walkthecounty_test_ajax' ) ) {
		wp_die( 0, 200 );
	}

	// Check if the Airplane Mode plugin is installed.
	if ( class_exists( 'Airplane_Mode_Core' ) ) {

		$airplane = Airplane_Mode_Core::getInstance();

		if ( method_exists( $airplane, 'enabled' ) ) {

			if ( $airplane->enabled() ) {
				return true;
			}
		} else {

			if ( 'on' === $airplane->check_status() ) {
				return true;
			}
		}
	}

	add_filter( 'block_local_requests', '__return_false' );

	$works = WalkTheCounty_Cache::get( '_walkthecounty_ajax_works', true );

	if ( ! $works || $force ) {
		$params = array(
			'sslverify' => false,
			'timeout'   => 30,
			'body'      => array(
				'action' => 'walkthecounty_test_ajax',
			),
		);

		$ajax = wp_remote_post( walkthecounty_get_ajax_url(), $params );

		$works = true;

		if ( is_wp_error( $ajax ) ) {

			$works = false;

		} else {

			if ( empty( $ajax['response'] ) ) {
				$works = false;
			}

			if ( empty( $ajax['response']['code'] ) || 200 !== (int) $ajax['response']['code'] ) {
				$works = false;
			}

			if ( empty( $ajax['response']['message'] ) || 'OK' !== $ajax['response']['message'] ) {
				$works = false;
			}

			if ( ! isset( $ajax['body'] ) || 0 !== (int) $ajax['body'] ) {
				$works = false;
			}
		}

		if ( $works ) {
			WalkTheCounty_Cache::set( '_walkthecounty_ajax_works', '1', DAY_IN_SECONDS, true );
		}
	}

	/**
	 * Filter the output
	 *
	 * @since 1.0
	 */
	return apply_filters( 'walkthecounty_test_ajax_works', $works );
}

add_action( 'wp_ajax_nopriv_walkthecounty_test_ajax', 'walkthecounty_test_ajax_works' );

/**
 * Get AJAX URL
 *
 * @since  1.0
 *
 * @param array $query
 *
 * @return string
 */
function walkthecounty_get_ajax_url( $query = array() ) {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = walkthecounty_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	if ( ! empty( $query ) ) {
		$ajax_url = add_query_arg( $query, $ajax_url );
	}

	return apply_filters( 'walkthecounty_ajax_url', $ajax_url );
}

/**
 * Loads Checkout Login Fields via AJAX
 *
 * @since  1.0
 *
 * @return void
 */
function walkthecounty_load_checkout_login_fields() {
	/**
	 * Fire when render login fields via ajax.
	 *
	 * @since 1.7
	 */
	do_action( 'walkthecounty_donation_form_login_fields' );

	walkthecounty_die();
}

add_action( 'wp_ajax_nopriv_walkthecounty_checkout_login', 'walkthecounty_load_checkout_login_fields' );

/**
 * Load Checkout Fields
 *
 * @since  1.3.6
 *
 * @return void
 */
function walkthecounty_load_checkout_fields() {
	$form_id = isset( $_POST['form_id'] ) ? $_POST['form_id'] : '';

	ob_start();

	/**
	 * Fire to render registration/login form.
	 *
	 * @since 1.7
	 */
	do_action( 'walkthecounty_donation_form_register_login_fields', $form_id );

	$fields = ob_get_clean();

	wp_send_json( array(
		'fields' => wp_json_encode( $fields ),
		'submit' => wp_json_encode( walkthecounty_get_donation_form_submit_button( $form_id ) ),
	) );
}

add_action( 'wp_ajax_nopriv_walkthecounty_cancel_login', 'walkthecounty_load_checkout_fields' );
add_action( 'wp_ajax_nopriv_walkthecounty_checkout_register', 'walkthecounty_load_checkout_fields' );


/**
 * Retrieve a states drop down
 *
 * @since  1.0
 *
 * @return void
 */
function walkthecounty_ajax_get_states_field() {
	$states_found   = false;
	$show_field     = true;
	$states_require = true;
	// Get the Country code from the $_POST.
	$country = sanitize_text_field( $_POST['country'] );

	// Get the field name from the $_POST.
	$field_name = sanitize_text_field( $_POST['field_name'] );

	$label        = __( 'State', 'walkthecounty' );
	$states_label = walkthecounty_get_states_label();

	$default_state = '';
	if ( walkthecounty_get_country() === $country ) {
		$default_state = walkthecounty_get_state();
	}

	// Check if $country code exists in the array key for states label.
	if ( array_key_exists( $country, $states_label ) ) {
		$label = $states_label[ $country ];
	}

	if ( empty( $country ) ) {
		$country = walkthecounty_get_country();
	}

	$states = walkthecounty_get_states( $country );
	if ( ! empty( $states ) ) {
		$args = array(
			'name'             => $field_name,
			'id'               => $field_name,
			'class'            => $field_name . '  walkthecounty-select',
			'options'          => $states,
			'show_option_all'  => false,
			'show_option_none' => false,
			'placeholder'      => $label,
			'selected'         => $default_state,
			'autocomplete'     => 'address-level1',
		);
		$data         = WalkTheCounty()->html->select( $args );
		$states_found = true;
	} else {
		$data = 'nostates';

		// Get the country list that does not have any states init.
		$no_states_country = walkthecounty_no_states_country_list();

		// Check if $country code exists in the array key.
		if ( array_key_exists( $country, $no_states_country ) ) {
			$show_field = false;
		}

		// Get the country list that does not require states.
		$states_not_required_country_list = walkthecounty_states_not_required_country_list();

		// Check if $country code exists in the array key.
		if ( array_key_exists( $country, $states_not_required_country_list ) ) {
			$states_require = false;
		}
	}

	$response = array(
		'success'        => true,
		'states_found'   => $states_found,
		'states_label'   => $label,
		'show_field'     => $show_field,
		'states_require' => $states_require,
		'data'           => $data,
		'default_state'  => $default_state,
		'city_require'   => ! array_key_exists( $country, walkthecounty_city_not_required_country_list() ),
	);
	wp_send_json( $response );
}

add_action( 'wp_ajax_walkthecounty_get_states', 'walkthecounty_ajax_get_states_field' );
add_action( 'wp_ajax_nopriv_walkthecounty_get_states', 'walkthecounty_ajax_get_states_field' );

/**
 * Retrieve donation forms via AJAX for chosen dropdown search field.
 *
 * @since  1.0
 *
 * @return void
 */
function walkthecounty_ajax_form_search() {
	$results = array();
	$search  = esc_sql( sanitize_text_field( $_POST['s'] ) );

	$args = array(
		'post_type'              => 'walkthecounty_forms',
		's'                      => $search,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'cache_results'          => false,
		'no_found_rows'          => true,
		'post_status'            => 'publish',
		'orderby'                => 'title',
		'order'                  => 'ASC',
		'posts_per_page'         => empty( $search ) ? 30 : -1,
	);

	/**
	 * Filter to modify Ajax form search args
	 *
	 * @since 2.1
	 *
	 * @param array $args Query argument for WP_query
	 *
	 * @return array $args Query argument for WP_query
	 */
	$args = (array) apply_filters( 'walkthecounty_ajax_form_search_args', $args );

	// get all the donation form.
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			global $post;

			$results[] = array(
				'id'   => $post->ID,
				'name' => $post->post_title,
			);
		}
		wp_reset_postdata();
	}

	/**
	 * Filter to modify Ajax form search result
	 *
	 * @since 2.1
	 *
	 * @param array $results Contain the Donation Form id
	 *
	 * @return array $results Contain the Donation Form id
	 */
	$results = (array) apply_filters( 'walkthecounty_ajax_form_search_response', $results );

	wp_send_json( $results );
}

add_action( 'wp_ajax_walkthecounty_form_search', 'walkthecounty_ajax_form_search' );
add_action( 'wp_ajax_nopriv_walkthecounty_form_search', 'walkthecounty_ajax_form_search' );

/**
 * Search the donors database via Ajax
 *
 * @since  1.0
 *
 * @return void
 */
function walkthecounty_ajax_donor_search() {
	global $wpdb;

	$search  = esc_sql( sanitize_text_field( $_POST['s'] ) );
	$results = array();
	if ( ! current_user_can( 'view_walkthecounty_reports' ) ) {
		$donors = array();
	} else {
		$donors = $wpdb->get_results( "SELECT id,name,email FROM $wpdb->donors WHERE `name` LIKE '%$search%' OR `email` LIKE '%$search%' LIMIT 50" );
	}

	if ( $donors ) {
		foreach ( $donors as $donor ) {

			$results[] = array(
				'id'   => $donor->id,
				'name' => $donor->name . ' (' . $donor->email . ')',
			);
		}
	}

	wp_send_json( $results );
}

add_action( 'wp_ajax_walkthecounty_donor_search', 'walkthecounty_ajax_donor_search' );


/**
 * Searches for users via ajax and returns a list of results
 *
 * @since  1.0
 *
 * @return void
 */
function walkthecounty_ajax_search_users() {
	$results = array();

	if ( current_user_can( 'manage_walkthecounty_settings' ) ) {

		$search = esc_sql( sanitize_text_field( $_POST['s'] ) );

		$get_users_args = array(
			'number' => 9999,
			'search' => $search . '*',
		);

		$get_users_args = apply_filters( 'walkthecounty_search_users_args', $get_users_args );

		$found_users = apply_filters( 'walkthecounty_ajax_found_users', get_users( $get_users_args ), $search );
		$results     = array();

		if ( $found_users ) {

			foreach ( $found_users as $user ) {

				$results[] = array(
					'id'   => $user->ID,
					'name' => esc_html( $user->user_login . ' (' . $user->user_email . ')' ),
				);
			}
		}
	}// End if().

	wp_send_json( $results );

}

add_action( 'wp_ajax_walkthecounty_user_search', 'walkthecounty_ajax_search_users' );


/**
 * Queries page by title and returns page ID and title in JSON format.
 *
 * Note: this function in for internal use.
 *
 * @since 2.1
 *
 * @return string
 */
function walkthecounty_ajax_pages_search() {
	$data = array();
	$args = array(
		'post_type' => 'page',
		's'         => walkthecounty_clean( $_POST['s'] ),
	);

	$query = new WP_Query( $args );

	// Query posts by title.
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			$data[] = array(
				'id'   => get_the_ID(),
				'name' => get_the_title(),
			);
		}
	}

	wp_send_json( $data );
}

add_action( 'wp_ajax_walkthecounty_pages_search', 'walkthecounty_ajax_pages_search' );

/**
 * Retrieve Categories via AJAX for chosen dropdown search field.
 *
 * @since  2.1
 *
 * @return void
 */
function walkthecounty_ajax_categories_search() {
	$results = array();

	/**
	 * Filter to modify Ajax tags search args
	 *
	 * @since 2.1
	 *
	 * @param array $args argument for get_terms
	 *
	 * @return array $args argument for get_terms
	 */
	$args = (array) apply_filters( 'walkthecounty_forms_categories_dropdown_args', array(
		'number'     => 30,
		'name__like' => esc_sql( sanitize_text_field( $_POST['s'] ) )
	) );

	$categories = get_terms( 'walkthecounty_forms_category', $args );

	foreach ( $categories as $category ) {
		$results[] = array(
			'id'   => $category->term_id,
			'name' => $category->name,
		);
	}

	/**
	 * Filter to modify Ajax tags search result
	 *
	 * @since 2.1
	 *
	 * @param array $results Contain the categories id and name
	 *
	 * @return array $results Contain the categories id and name
	 */
	$results = (array) apply_filters( 'walkthecounty_forms_categories_dropdown_responce', $results );

	wp_send_json( $results );
}

add_action( 'wp_ajax_walkthecounty_categories_search', 'walkthecounty_ajax_categories_search' );

/**
 * Retrieve Tags via AJAX for chosen dropdown search field.
 *
 * @since  2.1
 *
 * @return void
 */
function walkthecounty_ajax_tags_search() {
	$results = array();

	/**
	 * Filter to modify Ajax tags search args
	 *
	 * @since 2.1
	 *
	 * @param array $args argument for get_terms
	 *
	 * @return array $args argument for get_terms
	 */
	$args = (array) apply_filters( 'walkthecounty_forms_tags_dropdown_args', array(
		'number'     => 30,
		'name__like' => esc_sql( sanitize_text_field( $_POST['s'] ) )
	) );

	$categories = get_terms( 'walkthecounty_forms_tag', $args );

	foreach ( $categories as $category ) {
		$results[] = array(
			'id'   => $category->term_id,
			'name' => $category->name,
		);
	}

	/**
	 * Filter to modify Ajax tags search result
	 *
	 * @since 2.1
	 *
	 * @param array $results Contain the tags id and name
	 *
	 * @return array $results Contain the tags id and name
	 */
	$results = (array) apply_filters( 'walkthecounty_forms_tags_dropdown_responce', $results );

	wp_send_json( $results );
}

add_action( 'wp_ajax_walkthecounty_tags_search', 'walkthecounty_ajax_tags_search' );

/**
 * Check for Price Variations (Multi-level donation forms)
 *
 * @since  1.5
 *
 * @return void
 */
function walkthecounty_check_for_form_price_variations() {

	if ( ! current_user_can( 'edit_walkthecounty_forms', get_current_user_id() ) ) {
		die( '-1' );
	}

	$form_id = absint( $_POST['form_id'] );
	$form    = get_post( $form_id );

	if ( 'walkthecounty_forms' !== $form->post_type ) {
		die( '-2' );
	}

	if ( walkthecounty_has_variable_prices( $form_id ) ) {
		$variable_prices = walkthecounty_get_variable_prices( $form_id );

		if ( $variable_prices ) {
			$ajax_response = '<select class="walkthecounty_price_options_select walkthecounty-select walkthecounty-select" name="walkthecounty_price_option">';

			if ( isset( $_POST['all_prices'] ) ) {
				$ajax_response .= '<option value="all">' . esc_html__( 'All Levels', 'walkthecounty' ) . '</option>';
			}

			foreach ( $variable_prices as $key => $price ) {

				$level_text = ! empty( $price['_walkthecounty_text'] ) ? esc_html( $price['_walkthecounty_text'] ) : walkthecounty_currency_filter( walkthecounty_format_amount( $price['_walkthecounty_amount'], array( 'sanitize' => false ) ) );

				$ajax_response .= '<option value="' . esc_attr( $price['_walkthecounty_id']['level_id'] ) . '">' . $level_text . '</option>';
			}
			$ajax_response .= '</select>';
			echo $ajax_response;
		}
	}

	walkthecounty_die();
}

add_action( 'wp_ajax_walkthecounty_check_for_form_price_variations', 'walkthecounty_check_for_form_price_variations' );


/**
 * Check for Variation Prices HTML  (Multi-level donation forms)
 *
 * @since  1.6
 *
 * @return void
 */
function walkthecounty_check_for_form_price_variations_html() {
	if ( ! current_user_can( 'edit_walkthecounty_payments', get_current_user_id() ) ) {
		wp_die();
	}

	$form_id    = ! empty( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : false;
	$payment_id = ! empty( $_POST['payment_id'] ) ? absint( $_POST['payment_id'] ) : false;
	if ( empty( $form_id ) || empty( $payment_id ) ) {
		wp_die();
	}

	$form = get_post( $form_id );
	if ( ! empty( $form->post_type ) && 'walkthecounty_forms' !== $form->post_type ) {
		wp_die();
	}

	if ( ! walkthecounty_has_variable_prices( $form_id ) || ! $form_id ) {
		esc_html_e( 'n/a', 'walkthecounty' );
	} else {
		$prices_atts = array();
		if ( $variable_prices = walkthecounty_get_variable_prices( $form_id ) ) {
			foreach ( $variable_prices as $variable_price ) {
				$prices_atts[ $variable_price['_walkthecounty_id']['level_id'] ] = walkthecounty_format_amount( $variable_price['_walkthecounty_amount'], array( 'sanitize' => false ) );
			}
		}

		// Variable price dropdown options.
		$variable_price_dropdown_option = array(
			'id'               => $form_id,
			'name'             => 'walkthecounty-variable-price',
			'chosen'           => true,
			'show_option_all'  => '',
			'show_option_none' => '',
			'select_atts'      => 'data-prices=' . esc_attr( json_encode( $prices_atts ) ),
		);

		if ( $payment_id ) {
			// Payment object.
			$payment = new WalkTheCounty_Payment( $payment_id );

			// Payment meta.
			$payment_meta                               = $payment->get_meta();
			$variable_price_dropdown_option['selected'] = $payment_meta['price_id'];
		}

		// Render variable prices select tag html.
		walkthecounty_get_form_variable_price_dropdown( $variable_price_dropdown_option, true );
	}

	walkthecounty_die();
}

add_action( 'wp_ajax_walkthecounty_check_for_form_price_variations_html', 'walkthecounty_check_for_form_price_variations_html' );

/**
 * Send Confirmation Email For Complete Donation History Access.
 *
 * @since 1.8.17
 *
 * @return bool
 */
function walkthecounty_confirm_email_for_donation_access() {

	// Verify Security using Nonce.
	if ( ! check_ajax_referer( 'walkthecounty_ajax_nonce', 'nonce' ) ) {
		return false;
	}

	// Bail Out, if email is empty.
	if ( empty( $_POST['email'] ) ) {
		return false;
	}

	$donor = WalkTheCounty()->donors->get_donor_by( 'email', walkthecounty_clean( $_POST['email'] ) );
	if ( WalkTheCounty()->email_access->can_send_email( $donor->id ) ) {
		$return     = array();
		$email_sent = WalkTheCounty()->email_access->send_email( $donor->id, $donor->email );

		$return['status']  = 'success';

		if ( ! $email_sent ) {
			$return['status']  = 'error';
			$return['message'] = WalkTheCounty_Notices::print_frontend_notice(
				__( 'Unable to send email. Please try again.', 'walkthecounty' ),
				false,
				'error'
			);
		}

		/**
		 * Filter to modify access mail send notice
		 *
		 * @since 2.1.3
		 *
		 * @param string Send notice message for email access.
		 *
		 * @return  string $message Send notice message for email access.
		 */
		$message = (string) apply_filters( 'walkthecounty_email_access_mail_send_notice', __( 'Please check your email and click on the link to access your complete donation history.', 'walkthecounty' ) );

		$return['message'] = WalkTheCounty_Notices::print_frontend_notice(
			$message,
			false,
			'success'
		);


	} else {
		$value             = WalkTheCounty()->email_access->verify_throttle / 60;
		$return['status']  = 'error';

		/**
		 * Filter to modify email access exceed notices message.
		 *
		 * @since 2.1.3
		 *
		 * @param string $message email access exceed notices message
		 * @param int $value email access exceed times
		 *
		 * @return string $message email access exceed notices message
		 */
		$message = (string) apply_filters(
			'walkthecounty_email_access_requests_exceed_notice',
			sprintf(
				__( 'Too many access email requests detected. Please wait %s before requesting a new donation history access link.', 'walkthecounty' ),
				sprintf( _n( '%s minute', '%s minutes', $value, 'walkthecounty' ), $value )
			),
			$value
		);

		$return['message'] = WalkTheCounty_Notices::print_frontend_notice(
			$message,
			false,
			'error'
		);
	}

	echo json_encode( $return );
	walkthecounty_die();
}

add_action( 'wp_ajax_nopriv_walkthecounty_confirm_email_for_donations_access', 'walkthecounty_confirm_email_for_donation_access' );

/**
 * Render receipt by ajax
 * Note: only for internal use
 *
 * @since 2.2.0
 */
function __walkthecounty_get_receipt(){
	
	$get_data = walkthecounty_clean( filter_input_array( INPUT_GET ) );
	
	if( ! isset( $get_data['shortcode_atts'] ) ) {
		walkthecounty_die();
	}

	$atts = (array) json_decode( $get_data['shortcode_atts'] );
	$data = walkthecounty_receipt_shortcode( $atts );

	wp_send_json( $data );
}
add_action( 'wp_ajax_get_receipt', '__walkthecounty_get_receipt' );
add_action( 'wp_ajax_nopriv_get_receipt', '__walkthecounty_get_receipt' );

/**
 * Get ajax url to render content from other website into thickbox
 * Note: only for internal use
 *
 * @param array $args
 *
 * @return string
 * @since 2.5.0
 */
function walkthecounty_modal_ajax_url( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'action'   => 'walkthecounty_get_content_by_ajax',
			'_wpnonce' => wp_create_nonce( 'walkthecounty_get_content_by_ajax' ),
		)
	);

	return add_query_arg( $args, admin_url( '/admin-ajax.php' ) );
}


/**
 * Return content from url
 * Note: only for internal use
 * @todo use get_version endpoint to read changelog or cache add-ons infro from update_plugins option
 *
 * @return string
 * @since 2.5.0
 *
 */
function walkthecounty_get_content_by_ajax_handler() {
	check_admin_referer( 'walkthecounty_get_content_by_ajax' );

	if ( empty( $_GET['url'] ) ) {
		die();
	}

	// Handle changelog render request.
	if(
		! empty( $_GET['show_changelog'] )
		&& (int) walkthecounty_clean( $_GET['show_changelog'] )
	) {
		$msg = __( 'Sorry, unable to load changelog.', 'walkthecounty' );
		$url = urldecode_deep( walkthecounty_clean( $_GET['url'] ) );

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			echo "$msg<br><br><code>Error: {$response->get_error_message()}</code>" ;
			exit;
		}

		$response = wp_remote_retrieve_body( $response );


		if( false === strpos( $response,  '== Changelog ==' ) ) {
			echo $msg;
			exit;
		}

		$changelog = explode( '== Changelog ==', $response );
		$changelog = end( $changelog );

		echo walkthecounty_get_format_md( $changelog );
	}

	do_action( 'walkthecounty_get_content_by_ajax_handler' );

	exit;
}

add_action( 'wp_ajax_walkthecounty_get_content_by_ajax', 'walkthecounty_get_content_by_ajax_handler' );

