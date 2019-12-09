<?php
/**
 * HTML elements
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_HTML_Elements
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WalkTheCounty_HTML_Elements Class
 *
 * A helper class for outputting common HTML elements, such as product drop downs.
 *
 * @since 1.0
 */
class WalkTheCounty_HTML_Elements {
	/**
	 * Instance.
	 *
	 * @since  1.0
	 * @access private
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  1.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  1.0
	 * @access public
	 * @return WalkTheCounty_HTML_Elements
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}


	/**
	 * Donations Dropdown
	 *
	 * Renders an HTML Dropdown of all the donations.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args Arguments for the dropdown.
	 *
	 * @return string       Donations dropdown.
	 */
	public function donations_dropdown( $args = array() ) {

		$defaults = array(
			'name'        => 'donations',
			'id'          => 'donations',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => false,
			'number'      => 30,
			'placeholder' => __( 'Select a donation', 'walkthecounty' ),
		);

		$args = wp_parse_args( $args, $defaults );

		$payments = new WalkTheCounty_Payments_Query( array(
			'number' => $args['number'],
		) );

		$payments = $payments->get_payments();

		$options = array();

		// Provide nice human readable options.
		if ( $payments ) {
			$options[0] = $args['placeholder'];
			foreach ( $payments as $payment ) {

				$options[ absint( $payment->ID ) ] = esc_html( '#' . $payment->ID . ' - ' . $payment->email . ' - ' . $payment->form_title );

			}
		} else {
			$options[0] = __( 'No donations found.', 'walkthecounty' );
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'],
			'options'          => $options,
			'chosen'           => $args['chosen'],
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'select_atts'      => $args['select_atts'],
			'show_option_all'  => false,
			'show_option_none' => false,
		) );

		return $output;
	}

	/**
	 * WalkTheCounty Forms Dropdown
	 *
	 * Renders an HTML Dropdown of all the WalkTheCounty Forms.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args Arguments for the dropdown.
	 *
	 * @return string      WalkTheCounty forms dropdown.
	 */
	public function forms_dropdown( $args = array() ) {

		$defaults = array(
			'name'        => 'forms',
			'id'          => 'forms',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => false,
			'number'      => 30,
			'placeholder' => esc_attr__( 'All Forms', 'walkthecounty' ),
			'data'        => array(
				'search-type' => 'form',
			),
			'query_args'  => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$form_args = wp_parse_args(
			$args['query_args'],
			array(
				'post_type'      => 'walkthecounty_forms',
				'orderby'        => 'ID',
				'order'          => 'DESC',
				'posts_per_page' => $args['number'],
			)
		);

		/**
		 * Filter the forms dropdown.
		 *
		 * @since 2.3.0
		 *
		 * @param array $form_args Arguments for forms_dropdown query.
		 *
		 * @return array Arguments for forms_dropdown query.
		 */
		$form_args = apply_filters( 'walkthecounty_forms_dropdown_args', $form_args );

		$cache_key = WalkTheCounty_Cache::get_key( 'walkthecounty_forms', $form_args, false );

		// Get forms from cache.
		$forms = WalkTheCounty_Cache::get_db_query( $cache_key );

		if ( is_null( $forms ) ) {
			$forms = new WP_Query( $form_args );
			$forms = $forms->posts;
			WalkTheCounty_Cache::set_db_query( $cache_key, $forms );
		}

		$options = array();

		// Ensure the selected.
		if ( false !== $args['selected'] && $args['selected'] !== 0 ) {
			$options[ $args['selected'] ] = get_the_title( $args['selected'] );
		}

		$options[0] = esc_html__( 'No forms found.', 'walkthecounty' );
		if ( ! empty( $forms ) ) {
			$options[0] = $args['placeholder'];
			foreach ( $forms as $form ) {
				$form_title = empty( $form->post_title )
					? sprintf( __( 'Untitled (#%s)', 'walkthecounty' ), $form->ID )
					: $form->post_title;

				$options[ absint( $form->ID ) ] = esc_html( $form_title );
			}
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'],
			'options'          => $options,
			'chosen'           => $args['chosen'],
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'show_option_all'  => false,
			'show_option_none' => false,
			'data'             => $args['data'],
		) );

		return $output;
	}

	/**
	 * Donors Dropdown
	 *
	 * Renders an HTML Dropdown of all donors.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args Arguments for the dropdown.
	 *
	 * @return string      Donors dropdown.
	 */
	public function donor_dropdown( $args = array() ) {

		$defaults = array(
			'name'        => 'donors',
			'id'          => 'donors',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => true,
			'placeholder' => esc_attr__( 'Select a Donor', 'walkthecounty' ),
			'number'      => 30,
			'data'        => array(
				'search-type' => 'donor',
			),
		);

		$args = wp_parse_args( $args, $defaults );

		$donors = WalkTheCounty()->donors->get_donors( array(
			'number' => $args['number'],
		) );

		$options = array();

		if ( $donors ) {
			$options[0] = esc_html__( 'No donor attached', 'walkthecounty' );
			foreach ( $donors as $donor ) {
				$donor                           = walkthecounty_get_name_with_title_prefixes( $donor );
				$options[ absint( $donor->id ) ] = esc_html( $donor->name . ' (' . $donor->email . ')' );
			}
		} else {
			$options[0] = esc_html__( 'No donors found.', 'walkthecounty' );
		}

		if ( ! empty( $args['selected'] ) ) {

			// If a selected customer has been specified, we need to ensure it's in the initial list of customers displayed.
			if ( ! array_key_exists( $args['selected'], $options ) ) {

				$donor = new WalkTheCounty_Donor( $args['selected'] );

				if ( $donor ) {
					$donor                                  = walkthecounty_get_name_with_title_prefixes( $donor );
					$options[ absint( $args['selected'] ) ] = esc_html( $donor->name . ' (' . $donor->email . ')' );

				}
			}
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'] . ' walkthecounty-customer-select',
			'options'          => $options,
			'multiple'         => $args['multiple'],
			'chosen'           => $args['chosen'],
			'show_option_all'  => false,
			'show_option_none' => false,
			'data'             => $args['data'],
		) );

		return $output;
	}

	/**
	 * Categories Dropdown
	 *
	 * Renders an HTML Dropdown of all the Categories.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $name     Name attribute of the dropdown. Default is 'walkthecounty_forms_categories'.
	 * @param  int    $selected Category to select automatically. Default is 0.
	 * @param  array  $args     Select box options.
	 *
	 * @return string           Categories dropdown.
	 */
	public function category_dropdown( $name = 'walkthecounty_forms_categories', $selected = 0, $args = array() ) {
		$categories = get_terms( 'walkthecounty_forms_category', apply_filters( 'walkthecounty_forms_category_dropdown', array() ) );

		$options = array();

		foreach ( $categories as $category ) {
			$options[ absint( $category->term_id ) ] = esc_html( $category->name );
		}

		$output = $this->select( wp_parse_args( $args, array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => esc_html__( 'All Categories', 'walkthecounty' ),
			'show_option_none' => false,
		) ) );

		return $output;
	}

	/**
	 * Tags Dropdown
	 *
	 * Renders an HTML Dropdown of all the Tags.
	 *
	 * @since  1.8
	 * @access public
	 *
	 * @param  string $name     Name attribute of the dropdown. Default is 'walkthecounty_forms_tags'.
	 * @param  int    $selected Tag to select automatically. Default is 0.
	 * @param  array  $args     Select box options.
	 *
	 * @return string           Tags dropdown.
	 */
	public function tags_dropdown( $name = 'walkthecounty_forms_tags', $selected = 0, $args = array() ) {
		$tags = get_terms( 'walkthecounty_forms_tag', apply_filters( 'walkthecounty_forms_tag_dropdown', array() ) );

		$options = array();

		foreach ( $tags as $tag ) {
			$options[ absint( $tag->term_id ) ] = esc_html( $tag->name );
		}

		$output = $this->select( wp_parse_args( $args, array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => esc_html__( 'All Tags', 'walkthecounty' ),
			'show_option_none' => false,
		) ) );

		return $output;
	}

	/**
	 * Years Dropdown
	 *
	 * Renders an HTML Dropdown of years.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $name         Name attribute of the dropdown. Default is 'year'.
	 * @param  int    $selected     Year to select automatically. Default is 0.
	 * @param  int    $years_before Number of years before the current year the dropdown should start with. Default is 5.
	 * @param  int    $years_after  Number of years after the current year the dropdown should finish at. Default is 0.
	 *
	 * @return string               Years dropdown.
	 */
	public function year_dropdown( $name = 'year', $selected = 0, $years_before = 5, $years_after = 0 ) {
		$current    = date( 'Y' );
		$start_year = $current - absint( $years_before );
		$end_year   = $current + absint( $years_after );
		$selected   = empty( $selected ) ? date( 'Y' ) : $selected;
		$options    = array();

		while ( $start_year <= $end_year ) {
			$options[ absint( $start_year ) ] = $start_year;
			$start_year ++;
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false,
		) );

		return $output;
	}

	/**
	 * Months Dropdown
	 *
	 * Renders an HTML Dropdown of months.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $name     Name attribute of the dropdown. Default is 'month'.
	 * @param  int    $selected Month to select automatically. Default is 0.
	 *
	 * @return string           Months dropdown.
	 */
	public function month_dropdown( $name = 'month', $selected = 0 ) {
		$month    = 1;
		$options  = array();
		$selected = empty( $selected ) ? date( 'n' ) : $selected;

		while ( $month <= 12 ) {
			$options[ absint( $month ) ] = walkthecounty_month_num_to_name( $month );
			$month ++;
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false,
		) );

		return $output;
	}

	/**
	 * Dropdown
	 *
	 * Renders an HTML Dropdown.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args Arguments for the dropdown.
	 *
	 * @return string      The dropdown.
	 */
	public function select( $args = array() ) {
		$defaults = array(
			'options'          => array(),
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'autocomplete'     => 'no',
			'selected'         => 0,
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'select_atts'      => false,
			'show_option_all'  => __( 'All', 'walkthecounty' ),
			'show_option_none' => __( 'None', 'walkthecounty' ),
			'data'             => array(),
			'readonly'         => false,
			'disabled'         => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$data_elements = '';
		foreach ( $args['data'] as $key => $value ) {
			$data_elements .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		$multiple = '';
		if ( $args['multiple'] ) {
			$multiple = 'MULTIPLE';
		}

		if ( $args['chosen'] ) {
			$args['class'] .= ' walkthecounty-select-chosen';
		}

		$placeholder = '';
		if ( $args['placeholder'] ) {
			$placeholder = $args['placeholder'];
		}

		$output = sprintf(
			'<select name="%1$s" id="%2$s" autocomplete="%8$s" class="walkthecounty-select %3$s" %4$s %5$s placeholder="%6$s" data-placeholder="%6$s" %7$s>',
			esc_attr( $args['name'] ),
			esc_attr( sanitize_key( str_replace( '-', '_', $args['id'] ) ) ),
			esc_attr( $args['class'] ),
			$multiple,
			$args['select_atts'],
			$placeholder,
			$data_elements,
			$args['autocomplete']
		);

		if ( $args['show_option_all'] ) {
			if ( $args['multiple'] ) {
				$selected = selected( true, in_array( 0, $args['selected'] ), false );
			} else {
				$selected = selected( $args['selected'], 0, false );
			}
			$output .= '<option value="all"' . $selected . '>' . esc_html( $args['show_option_all'] ) . '</option>';
		}

		if ( ! empty( $args['options'] ) ) {

			if ( $args['show_option_none'] ) {
				if ( $args['multiple'] ) {
					$selected = selected( true, in_array( - 1, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], - 1, false );
				}
				$output .= '<option value="-1"' . $selected . '>' . esc_html( $args['show_option_none'] ) . '</option>';
			}

			foreach ( $args['options'] as $key => $option ) {

				if ( $args['multiple'] && is_array( $args['selected'] ) ) {
					$selected = selected( true, in_array( $key, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], $key, false );
				}

				$output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>';
			}
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Checkbox
	 *
	 * Renders an HTML Checkbox.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args Arguments for the Checkbox.
	 *
	 * @return string      The checkbox.
	 */
	public function checkbox( $args = array() ) {
		$defaults = array(
			'name'    => null,
			'current' => null,
			'class'   => 'walkthecounty-checkbox',
			'options' => array(
				'disabled' => false,
				'readonly' => false,
			),
		);

		$args = wp_parse_args( $args, $defaults );

		$options = '';
		if ( ! empty( $args['options']['disabled'] ) ) {
			$options .= ' disabled="disabled"';
		} elseif ( ! empty( $args['options']['readonly'] ) ) {
			$options .= ' readonly';
		}

		$output = '<input type="checkbox"' . $options . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['name'] ) . '" class="' . $args['class'] . ' ' . esc_attr( $args['name'] ) . '" ' . checked( 1, $args['current'], false ) . ' />';

		return $output;
	}

	/**
	 * Text Field
	 *
	 * Renders an HTML Text field.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args Arguments for the text field.
	 *
	 * @return string      The text field.
	 */
	public function text( $args = array() ) {
		// Backwards compatibility.
		if ( func_num_args() > 1 ) {
			$args = func_get_args();

			$name  = $args[0];
			$value = isset( $args[1] ) ? $args[1] : '';
			$label = isset( $args[2] ) ? $args[2] : '';
			$desc  = isset( $args[3] ) ? $args[3] : '';
		}

		$defaults = array(
			'name'         => isset( $name ) ? $name : 'text',
			'value'        => isset( $value ) ? $value : null,
			'label'        => isset( $label ) ? $label : null,
			'desc'         => isset( $desc ) ? $desc : null,
			'placeholder'  => '',
			'class'        => 'regular-text',
			'disabled'     => false,
			'autocomplete' => '',
			'data'         => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$disabled = '';
		if ( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$data = '';
		if ( ! empty( $args['data'] ) ) {
			foreach ( $args['data'] as $key => $value ) {
				$data .= 'data-' . $key . '="' . $value . '" ';
			}
		}

		$output = '<span id="walkthecounty-' . sanitize_key( $args['name'] ) . '-wrap">';
		
		// Don't output label when the label is empty.
		if ( ! empty( $args['label'] ) ) {
			$output .= '<label class="walkthecounty-label" for="walkthecounty-' . sanitize_key( $args['name'] ) . '">' . esc_html( $args['label'] ) . '</label>';
		}

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="walkthecounty-description">' . esc_html( $args['desc'] ) . '</span>';
		}

		$output .= '<input type="text" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['name'] ) . '" autocomplete="' . esc_attr( $args['autocomplete'] ) . '" value="' . esc_attr( $args['value'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $args['class'] . '" ' . $data . '' . $disabled . '/>';

		$output .= '</span>';

		return $output;
	}

	/**
	 * Date Picker
	 *
	 * Renders a date picker field.
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  array $args Arguments for the date picker.
	 *
	 * @return string      The date picker.
	 */
	public function date_field( $args = array() ) {

		if ( empty( $args['class'] ) ) {
			$args['class'] = 'walkthecounty_datepicker';
		} elseif ( ! strpos( $args['class'], 'walkthecounty_datepicker' ) ) {
			$args['class'] .= ' walkthecounty_datepicker';
		}

		return $this->text( $args );
	}

	/**
	 * Textarea
	 *
	 * Renders an HTML textarea.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args Arguments for the textarea.
	 *
	 * @return string      The textarea.
	 */
	public function textarea( $args = array() ) {
		$defaults = array(
			'name'     => 'textarea',
			'value'    => null,
			'label'    => null,
			'desc'     => null,
			'class'    => 'large-text',
			'disabled' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$disabled = '';
		if ( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$output = '<span id="walkthecounty-' . sanitize_key( $args['name'] ) . '-wrap">';

		$output .= '<label class="walkthecounty-label" for="walkthecounty-' . sanitize_key( $args['name'] ) . '">' . esc_html( $args['label'] ) . '</label>';

		$output .= '<textarea name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['name'] ) . '" class="' . $args['class'] . '"' . $disabled . '>' . esc_attr( $args['value'] ) . '</textarea>';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="walkthecounty-description">' . esc_html( $args['desc'] ) . '</span>';
		}

		$output .= '</span>';

		return $output;
	}

	/**
	 * User Search Field
	 *
	 * Renders an ajax user search field.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args Arguments for the search field.
	 *
	 * @return string      The text field with ajax search.
	 */
	public function ajax_user_search( $args = array() ) {

		$defaults = array(
			'name'        => 'users',
			'id'          => 'users',
			'class'       => 'walkthecounty-ajax-user-search',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => true,
			'number'      => 30,
			'select_atts' => '',
			'placeholder' => __( 'Select a user', 'walkthecounty' ),
			'data'        => array(
				'search-type' => 'user',
			),
		);

		$args = wp_parse_args( $args, $defaults );

		// Set initial args.
		$get_users_args = array(
			'number' => $args['number'],
		);

		// Ensure selected user is not included in initial query.
		// This is because sites with many users, it's not a guarantee the selected user will be returned.
		if ( ! empty( $args['selected'] ) ) {
			$get_users_args['exclude'] = $args['selected'];
		}

		// Initial users array.
		$users = apply_filters( 'walkthecounty_ajax_user_search_initial_results', get_users( $get_users_args ), $args );

		// Now add the selected user to the $users array if the arg is present.
		if ( ! empty( $args['selected'] ) ) {
			$selected_user = apply_filters( 'walkthecounty_ajax_user_search_selected_results', get_users( "include={$args['selected']}" ), $args );;
			$users = array_merge( $users, $selected_user );
		}

		$options = array();

		if ( $users ) {
			$options[0] = $args['placeholder'];
			foreach ( $users as $user ) {
				$options[ absint( $user->ID ) ] = esc_html( $user->user_login . ' (' . $user->user_email . ')' );
			}
		} else {
			$options[0] = __( 'No users found.', 'walkthecounty' );
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'],
			'options'          => $options,
			'chosen'           => $args['chosen'],
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'select_atts'      => $args['select_atts'],
			'show_option_all'  => false,
			'show_option_none' => false,
			'data'             => $args['data'],
		) );

		return $output;

	}

}
