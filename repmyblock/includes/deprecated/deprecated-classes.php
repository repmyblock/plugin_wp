<?php
/**
 * Handle renamed classes.
 *
 * @package WalkTheCounty
 */


/**
 * Instantiate old properties for backwards compatibility.
 *
 * @param $instance WalkTheCounty()
 *
 * @return WalkTheCounty
 */
function walkthecounty_load_deprecated_properties( $instance ) {

	// If a property is renamed then it gets placed below.
	$instance->customers     = new WalkTheCounty_DB_Customers();
	$instance->customer_meta = new WalkTheCounty_DB_Customer_Meta();

	return $instance;

}

add_action( 'walkthecounty_init', 'walkthecounty_load_deprecated_properties', 10, 1 );

/**
 * WalkTheCounty_DB_Customers Class (deprecated)
 *
 * This class is for interacting with the customers' database table.
 *
 * @since 1.0
 */
class WalkTheCounty_DB_Customers extends WalkTheCounty_DB_Donors {

	/**
	 * WalkTheCounty_DB_Customers constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * There are certain responsibility of this function:
	 *  1. handle backward compatibility for deprecated functions
	 *
	 * @since 1.8.8
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		$deprecated_function_arr = array(
			'get_customer_by',
			'walkthecounty_update_customer_email_on_user_update',
			'get_customers',
		);

		if ( in_array( $name, $deprecated_function_arr ) ) {
			switch ( $name ) {
				case 'get_customers':
					$args = ! empty( $arguments[0] ) ? $arguments[0] : array();

					return $this->get_donors( $args );
				case 'get_customer_by':
					$field    = ! empty( $arguments[0] ) ? $arguments[0] : 'id';
					$donor_id = ! empty( $arguments[1] ) ? $arguments[1] : 0;

					return $this->get_donor_by( $field, $donor_id );

				case 'walkthecounty_update_customer_email_on_user_update':
					$user_id       = ! empty( $arguments[0] ) ? $arguments[0] : 0;
					$old_user_data = ! empty( $arguments[1] ) ? $arguments[1] : false;

					return $this->update_donor_email_on_user_update( $user_id, $old_user_data );
			}
		}
	}

}


/**
 * WalkTheCounty_Customer Class (Deprecated)
 *
 * @since 1.0
 */
class WalkTheCounty_Customer extends WalkTheCounty_Donor {

	/**
	 * WalkTheCounty_Customer constructor.
	 *
	 * @param bool $_id_or_email
	 * @param bool $by_user_id
	 */
	public function __construct( $_id_or_email = false, $by_user_id = false ) {
		parent::__construct( $_id_or_email, $by_user_id );
	}

	/**
	 * There are certain responsibility of this function:
	 *  1. handle backward compatibility for deprecated functions
	 *
	 * @since 1.8.8
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
	}

}


/**
 * WalkTheCounty_DB_Customer_Meta Class (Deprecated)
 *
 * @since 1.0
 */
class WalkTheCounty_DB_Customer_Meta extends WalkTheCounty_DB_Donor_Meta {

	/**
	 * WalkTheCounty_DB_Customer_Meta constructor.
	 */
	public function __construct() {
		parent::__construct();
	}


	/**
	 * There are certain responsibility of this function:
	 *  1. handle backward compatibility for deprecated functions
	 *
	 * @since 1.8.8
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {

	}

}
