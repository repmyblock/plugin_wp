<?php
/**
 * WalkTheCounty Form Widget
 *
 * @package     WalkTheCountyWP
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WalkTheCounty Form widget
 *
 * @since 1.0
 */
class WalkTheCounty_Forms_Widget extends WP_Widget {

	/**
	 * The widget class name
	 *
	 * @var string
	 */
	protected $self;

	/**
	 * Instantiate the class
	 */
	public function __construct() {
		$this->self = get_class( $this );

		parent::__construct(
			strtolower( $this->self ),
			esc_html__( 'WalkTheCountyWP - Donation Form', 'walkthecounty' ),
			array(
				'description' => esc_html__( 'Display a WalkTheCountyWP Donation Form in your theme\'s widget powered sidebar.', 'walkthecounty' ),
			)
		);

		add_action( 'widgets_init', array( $this, 'widget_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_widget_scripts' ) );
	}

	/**
	 * Load widget assets only on the widget page
	 *
	 * @param string $hook Use it to target a specific admin page.
	 *
	 * @return void
	 */
	public function admin_widget_scripts( $hook ) {

		// Directories of assets.
		$js_dir     = WALKTHECOUNTY_PLUGIN_URL . 'assets/js/admin/';
		$js_plugins = WALKTHECOUNTY_PLUGIN_URL . 'assets/js/plugins/';
		$css_dir    = WALKTHECOUNTY_PLUGIN_URL . 'assets/css/';

		// Use minified libraries if SCRIPT_DEBUG is turned off.
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// Widget Script.
		if ( 'widgets.php' === $hook ) {

			wp_enqueue_script( 'walkthecounty-admin-widgets-scripts', $js_dir . 'admin-widgets' . $suffix . '.js', array( 'jquery' ), WALKTHECOUNTY_VERSION, false );
		}
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args     Display arguments including before_title, after_title,
	 *                        before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		$title   = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$title   = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$form_id = (int) $instance['id'];

		echo $args['before_widget']; // XSS ok.

		/**
		 * Fires before widget settings form in the admin area.
		 *
		 * @param integer $form_id Form ID.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_before_forms_widget', $form_id );

		echo $title ? $args['before_title'] . $title . $args['after_title'] : ''; // XSS ok.

		walkthecounty_get_donation_form( $instance );

		echo $args['after_widget']; // XSS ok.

		/**
		 * Fires after widget settings form in the admin area.
		 *
		 * @param integer $form_id Form ID.
		 *
		 * @since 1.0
		 */
		do_action( 'walkthecounty_after_forms_widget', $form_id );
	}

	/**
	 * Output the settings update form.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$defaults = array(
			'title'                 => '',
			'id'                    => '',
			'float_labels'          => 'global',
			'display_style'         => 'modal',
			'show_content'          => 'none',
			'continue_button_title' => '',
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		// Backward compatibility: Set float labels as default if, it was set as empty previous.
		$instance['float_labels'] = empty( $instance['float_labels'] ) ? 'global' : $instance['float_labels'];

		// Query WalkTheCounty Forms.
		$args = array(
			'post_type'      => 'walkthecounty_forms',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);

		$walkthecounty_forms = get_posts( $args );
		?>
		<div class="walkthecounty_forms_widget_container">

			<?php // Widget: widget Title. ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'walkthecounty' ); ?></label>
				<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" /><br>
				<small class="walkthecounty-field-description"><?php esc_html_e( 'Leave blank to hide the widget title.', 'walkthecounty' ); ?></small>
			</p>

			<?php // Widget: WalkTheCounty Form. ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>"><?php esc_html_e( 'WalkTheCountyWP Form:', 'walkthecounty' ); ?></label>
				<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>">
					<option value="current"><?php esc_html_e( '- Select -', 'walkthecounty' ); ?></option>
					<?php foreach ( $walkthecounty_forms as $walkthecounty_form ) { ?>
						<?php /* translators: %s: Title */ ?>
						<?php $form_title = empty( $walkthecounty_form->post_title ) ? sprintf( __( 'Untitled (#%s)', 'walkthecounty' ), $walkthecounty_form->ID ) : $walkthecounty_form->post_title; ?>
						<option <?php selected( absint( $instance['id'] ), $walkthecounty_form->ID ); ?> value="<?php echo esc_attr( $walkthecounty_form->ID ); ?>"><?php echo esc_html( $form_title ); ?></option>
					<?php } ?>
				</select><br>
				<small class="walkthecounty-field-description"><?php esc_html_e( 'Select a WalkTheCountyWP Form to embed in this widget.', 'walkthecounty' ); ?></small>
			</p>

			<?php // Widget: Display Style. ?>
			<p class="walkthecounty_forms_display_style_setting_row">
				<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>"><?php esc_html_e( 'Display Style:', 'walkthecounty' ); ?></label><br>
				<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-onpage"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-onpage" name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>" value="onpage" <?php checked( $instance['display_style'], 'onpage' ); ?>> <?php echo esc_html__( 'All Fields', 'walkthecounty' ); ?></label>
				&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-reveal"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-reveal" name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>" value="reveal" <?php checked( $instance['display_style'], 'reveal' ); ?>> <?php echo esc_html__( 'Reveal', 'walkthecounty' ); ?></label>
				&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-modal"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-modal" name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>" value="modal" <?php checked( $instance['display_style'], 'modal' ); ?>> <?php echo esc_html__( 'Modal', 'walkthecounty' ); ?></label>
				&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-button"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>-button" name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>" value="button" <?php checked( $instance['display_style'], 'button' ); ?>> <?php echo esc_html__( 'Button', 'walkthecounty' ); ?></label><br>
				<small class="walkthecounty-field-description">
					<?php echo esc_html__( 'Select a WalkTheCountyWP donation form style.', 'walkthecounty' ); ?>
				</small>
			</p>

			<?php // Widget: Continue Button Title. ?>
			<p class="walkthecounty_forms_continue_button_title_setting_row">
				<label for="<?php echo esc_attr( $this->get_field_id( 'continue_button_title' ) ); ?>"><?php esc_html_e( 'Button Text:', 'walkthecounty' ); ?></label>
				<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'continue_button_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'continue_button_title' ) ); ?>" value="<?php echo esc_attr( $instance['continue_button_title'] ); ?>" /><br>
				<small class="walkthecounty-field-description"><?php esc_html_e( 'The button label for displaying the additional payment fields.', 'walkthecounty' ); ?></small>
			</p>

			<?php // Widget: Floating Labels. ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>"><?php esc_html_e( 'Floating Labels (optional):', 'walkthecounty' ); ?></label><br>
				<label for="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-global"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-global" name="<?php echo esc_attr( $this->get_field_name( 'float_labels' ) ); ?>" value="global" <?php checked( $instance['float_labels'], 'global' ); ?>> <?php echo esc_html__( 'Global Option', 'walkthecounty' ); ?></label>
				&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-enabled"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-enabled" name="<?php echo esc_attr( $this->get_field_name( 'float_labels' ) ); ?>" value="enabled" <?php checked( $instance['float_labels'], 'enabled' ); ?>> <?php echo esc_html__( 'Enabled', 'walkthecounty' ); ?></label>
				&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-disabled"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>-disabled" name="<?php echo esc_attr( $this->get_field_name( 'float_labels' ) ); ?>" value="disabled" <?php checked( $instance['float_labels'], 'disabled' ); ?>> <?php echo esc_html__( 'Disabled', 'walkthecounty' ); ?></label><br>
				<small class="walkthecounty-field-description">
					<?php
					printf(
						/* translators: %s: Documentation link to http://docs.walkthecountywp.com/form-floating-labels */
						__( 'Override the <a href="%s" target="_blank">floating labels</a> setting for this WalkTheCountyWP form.', 'walkthecounty' ),
						esc_url( 'http://docs.walkthecountywp.com/form-floating-labels' )
					);
					?>
					</small>
			</p>

			<?php // Widget: Display Content. ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>"><?php esc_html_e( 'Display Content (optional):', 'walkthecounty' ); ?></label><br>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-none"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-none" name="<?php echo esc_attr( $this->get_field_name( 'show_content' ) ); ?>" value="none" <?php checked( $instance['show_content'], 'none' ); ?>> <?php echo esc_html__( 'None', 'walkthecounty' ); ?></label>
				&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-above"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-above" name="<?php echo esc_attr( $this->get_field_name( 'show_content' ) ); ?>" value="above" <?php checked( $instance['show_content'], 'above' ); ?>> <?php echo esc_html__( 'Above', 'walkthecounty' ); ?></label>
				&nbsp;&nbsp;<label for="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-below"><input type="radio" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'show_content' ) ); ?>-below" name="<?php echo esc_attr( $this->get_field_name( 'show_content' ) ); ?>" value="below" <?php checked( $instance['show_content'], 'below' ); ?>> <?php echo esc_html__( 'Below', 'walkthecounty' ); ?></label><br>
				<small class="walkthecounty-field-description"><?php esc_html_e( 'Override the display content setting for this WalkTheCountyWP form.', 'walkthecounty' ); ?></small>
		</div>
		<?php
	}

	/**
	 * Register the widget
	 *
	 * @return void
	 */
	public function widget_init() {
		register_widget( $this->self );
	}

	/**
	 * Update the widget
	 *
	 * @param array $new_instance The new options.
	 * @param array $old_instance The previous options.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$this->flush_widget_cache();

		return $new_instance;
	}

	/**
	 * Flush widget cache
	 *
	 * @return void
	 */
	public function flush_widget_cache() {
		wp_cache_delete( $this->self, 'widget' );
	}
}

new WalkTheCounty_Forms_Widget();
