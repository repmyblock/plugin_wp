<?php
/**
 * WalkTheCounty Admin Settings Class
 *
 * @package     WalkTheCounty
 * @subpackage  Classes/WalkTheCounty_Admin_Settings
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WalkTheCounty_Admin_Settings' ) ) :

	/**
	 * WalkTheCounty_Admin_Settings Class.
	 *
	 * @since 1.8
	 */
	class WalkTheCounty_Admin_Settings {

		/**
		 * Setting pages.
		 *
		 * @since 1.8
		 * @var   array List of settings.
		 */
		private static $settings = array();

		/**
		 * Setting filter and action prefix.
		 *
		 * @since 1.8
		 * @var   string setting fileter and action anme prefix.
		 */
		private static $setting_filter_prefix = '';

		/**
		 * Error messages.
		 *
		 * @since 1.8
		 * @var   array List of errors.
		 */
		private static $errors = array();

		/**
		 * Update messages.
		 *
		 * @since 1.8
		 * @var   array List of messages.
		 */
		private static $messages = array();

		/**
		 * Include the settings page classes.
		 *
		 * @since  1.8
		 * @return array
		 */
		public static function get_settings_pages() {
			/**
			 * Filter the setting page.
			 *
			 * Note: filter dynamically fire on basis of setting page slug.
			 * For example: if you register a setting page with walkthecounty-settings menu slug
			 *              then filter will be walkthecounty-settings_get_settings_pages
			 *
			 * @since 1.8
			 *
			 * @param array $settings Array of settings class object.
			 */
			self::$settings = apply_filters( self::$setting_filter_prefix . '_get_settings_pages', array() );

			return self::$settings;
		}

		/**
		 * Verify admin setting nonce
		 *
		 * @since  1.8.14
		 * @access public
		 *
		 * @return bool
		 */
		public static function verify_nonce() {
			if ( empty( $_REQUEST['_walkthecounty-save-settings'] ) || ! wp_verify_nonce( $_REQUEST['_walkthecounty-save-settings'], 'walkthecounty-save-settings' ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Save the settings.
		 *
		 * @since  1.8
		 * @return void
		 */
		public static function save() {
			$current_tab = walkthecounty_get_current_setting_tab();

			if ( ! self::verify_nonce() ) {
				echo '<div class="notice error"><p>' . esc_attr__( 'Action failed. Please refresh the page and retry.', 'walkthecounty' ) . '</p></div>';
				die();
			}

			/**
			 * Trigger Action.
			 *
			 * Note: action dynamically fire on basis of setting page slug and current tab.
			 * For example: if you register a setting page with walkthecounty-settings menu slug and general current tab name
			 *              then action will be walkthecounty-settings_save_general
			 *
			 * @since 1.8
			 */
			do_action( self::$setting_filter_prefix . '_save_' . $current_tab );

			self::add_message( 'walkthecounty-setting-updated', __( 'Your settings have been saved.', 'walkthecounty' ) );

			/**
			 * Trigger Action.
			 *
			 * Note: action dynamically fire on basis of setting page slug.
			 * For example: if you register a setting page with walkthecounty-settings menu slug
			 *              then action will be walkthecounty-settings_saved
			 *
			 * @since 1.8
			 */
			do_action( self::$setting_filter_prefix . '_saved' );
		}

		/**
		 * Add a message.
		 *
		 * @since  1.8
		 *
		 * @param  string $code    Message code (Note: This should be unique).
		 * @param  string $message Message text.
		 *
		 * @return void
		 */
		public static function add_message( $code, $message ) {
			self::$messages[ $code ] = $message;
		}

		/**
		 * Add an error.
		 *
		 * @since  1.8
		 *
		 * @param  string $code    Message code (Note: This should be unique).
		 * @param  string $message Message text.
		 *
		 * @return void
		 */
		public static function add_error( $code, $message ) {
			self::$errors[ $code ] = $message;
		}

		/**
		 * Output messages + errors.
		 *
		 * @since  1.8
		 * @return void
		 */
		public static function show_messages() {
			$notice_html = '';
			$classes     = 'walkthecounty-notice settings-error notice is-dismissible';

			self::$errors   = apply_filters( self::$setting_filter_prefix . '_error_notices', self::$errors );
			self::$messages = apply_filters( self::$setting_filter_prefix . '_update_notices', self::$messages );

			if ( 0 < count( self::$errors ) ) {
				foreach ( self::$errors as $code => $message ) {
					$notice_html .= sprintf(
						'<div id="setting-error-%1$s" class="%2$s error" style="display: none"><p><strong>%3$s</strong></p></div>',
						$code,
						$classes,
						$message
					);
				}
			}

			if ( 0 < count( self::$messages ) ) {
				foreach ( self::$messages as $code => $message ) {
					$notice_html .= sprintf(
						'<div id="setting-error-%1$s" class="%2$s updated" style="display: none"><p><strong>%3$s</strong></p></div>',
						$code,
						$classes,
						$message
					);
				}
			}

			echo $notice_html;
		}

		/**
		 * Settings page.
		 *
		 * Handles the display of the main walkthecounty settings page in admin.
		 *
		 * @since  1.8
		 * @return bool
		 */
		public static function output() {
			// Get current setting page.
			self::$setting_filter_prefix = walkthecounty_get_current_setting_page();

			// Bailout: Exit if setting page is not defined.
			if ( empty( self::$setting_filter_prefix ) ) {
				return false;
			}

			/**
			 * Trigger Action.
			 *
			 * Note: action dynamically fire on basis of setting page slug
			 * For example: if you register a setting page with walkthecounty-settings menu slug
			 *              then action will be walkthecounty-settings_start
			 *
			 * @since 1.8
			 */
			do_action( self::$setting_filter_prefix . '_start' );

			$current_tab     = walkthecounty_get_current_setting_tab();
			$current_section = walkthecounty_get_current_setting_section();

			// Include settings pages.
			$all_setting = self::get_settings_pages();

			/* @var object $current_setting_obj */
			$current_setting_obj = new StdClass;

			foreach ( $all_setting as $setting ) {
				if (
					method_exists( $setting, 'get_id' ) &&
					$current_tab === $setting->get_id()
				) {
					$current_setting_obj = $setting;
					break;
				}
			}

			// Save settings if data has been posted.
			if ( isset( $_POST['_walkthecounty-save-settings'] ) ) {
				self::save();
			}

			/**
			 * Filter the tabs for current setting page.
			 *
			 * Note: filter dynamically fire on basis of setting page slug.
			 * For example: if you register a setting page with walkthecounty-settings menu slug and general current tab name
			 *              then action will be walkthecounty-settings_tabs_array
			 *
			 * @since 1.8
			 */
			$tabs = apply_filters( self::$setting_filter_prefix . '_tabs_array', array() );

			include 'views/html-admin-settings.php';

			return true;
		}

		/**
		 * Get a setting from the settings API.
		 *
		 * @since  1.8
		 *
		 * @param string $option_name Option Name.
		 * @param string $field_id    Field ID.
		 * @param mixed  $default     Default.
		 *
		 * @return string|bool
		 */
		public static function get_option( $option_name = '', $field_id = '', $default = false ) {
			// Bailout.
			if ( empty( $option_name ) && empty( $field_id ) ) {
				return false;
			}

			if ( ! empty( $field_id ) && ! empty( $option_name ) ) {
				// Get field value if any.
				$option_value = get_option( $option_name );

				$option_value = ( is_array( $option_value ) && array_key_exists( $field_id, $option_value ) )
					? $option_value[ $field_id ]
					: $default;
			} else {
				// If option name is empty but not field name then this means, setting is direct store to option table under there field name.
				$option_name = ! $option_name ? $field_id : $option_name;

				// Get option value if any.
				$option_value = get_option( $option_name, $default );
			}

			/**
			 * Filter the option value
			 *
			 * @since 2.2.3
			 *
			 * @param mixed  $option_value
			 * @param string $option_name
			 * @param string $field_id
			 * @param mixed  $default
			 */
			return apply_filters( 'walkthecounty_admin_field_get_value', $option_value, $option_name, $field_id, $default );
		}

		/**
		 * Output admin fields.
		 *
		 * Loops though the walkthecounty options array and outputs each field.
		 *
		 * @todo Refactor this function
		 *
		 * @since  1.8
		 * @access public
		 *
		 * @param array  $sections     Opens array to output.
		 * @param string $option_name Opens array to output.
		 *
		 * @return void
		 */
		public static function output_fields( $sections, $option_name = '' ) {

			$current_page    = walkthecounty_get_current_setting_page();
			$current_tab     = walkthecounty_get_current_setting_tab();
			$current_section = walkthecounty_get_current_setting_section();
			$groups          = walkthecounty_get_settings_groups();

			if ( is_array( $groups ) && count( $groups ) > 0 ) {
				?>
				<div class="walkthecounty-settings-section-content">
					<div class="walkthecounty-settings-section-group-menu">
						<ul>
							<?php
							if ( is_array( $groups ) && count( $groups ) > 0 ) {
								$count = 1;

								foreach ( $groups as $slug => $group ) {
									$default_group = ( 1 === $count && empty( $_GET['group'] ) ) ? $slug : '';
									$current_group = ! empty( $_GET['group'] ) ? walkthecounty_clean( $_GET['group'] ) : $default_group;
									$active_class  = ( $slug === $current_group ) ? 'active' : '';

									echo sprintf(
										'<li><a class="%1$s" href="%2$s" data-group="%3$s">%4$s</a></li>',
										esc_html( $active_class ),
										esc_url( admin_url( "edit.php?post_type=walkthecounty_forms&page={$current_page}&tab={$current_tab}&section={$current_section}&group={$slug}" ) ),
										esc_html( $slug ),
										esc_html( $group )
									);
									$count++;
								}
							}
							?>
						</ul>
					</div>
					<div class="walkthecounty-settings-section-group-content">
						<?php
						$count = 1;
						foreach ( $sections as $group => $fields ) {
							if ( ! empty( $group ) ) {

								$default_group = ( 1 === $count && empty( $_GET['group'] ) ) ? $group : '';
								$current_group = ! empty( $_GET['group'] ) ? walkthecounty_clean( $_GET['group'] ) : $default_group;
								$hide_class    = $group !== $current_group ? 'walkthecounty-hidden' : '';
								?>
								<div id="walkthecounty-settings-section-group-<?php echo esc_attr( $group ); ?>" class="walkthecounty-settings-section-group <?php echo esc_html( $hide_class ); ?>">
									<?php
									foreach ( $fields as $value ) {
										if ( ! isset( $value['type'] ) ) {
											continue;
										}
										self::prepare_settings_field( $value, $option_name );
									}
									?>
								</div>
								<?php
							}

							$count++;
						}
						?>
					</div>
				</div>
				<?php
			} else {

				// Loop through each section.
				foreach ( $sections as $value ) {
					if ( ! isset( $value['type'] ) ) {
						continue;
					}
					self::prepare_settings_field( $value, $option_name );
				}
			}

		}

		/**
		 * This function will help you prepare the admin settings field.
		 *
		 * @since  2.5.5
		 * @access public
		 *
		 * @param array  $value       Settings Field Array.
		 * @param string $option_name Option Name.
		 *
		 * @return mixed
		 */
		public static function prepare_settings_field( $value, $option_name ) {

			$current_tab = walkthecounty_get_current_setting_tab();

			// Field Default values.
			$defaults = array(
				'id'               => '',
				'class'            => '',
				'css'              => '',
				'default'          => '',
				'desc'             => '',
				'table_html'       => true,
				'repeat'           => false,
				'repeat_btn_title' => __( 'Add Field', 'walkthecounty' ),
			);

			// Set title.
			$defaults['title'] = isset( $value['name'] ) ? $value['name'] : '';

			// Set default setting.
			$value = wp_parse_args( $value, $defaults );

			// Colorpicker field.
			$value['class'] = ( 'colorpicker' === $value['type'] ? trim( $value['class'] ) . ' walkthecounty-colorpicker' : $value['class'] );
			$value['type']  = ( 'colorpicker' === $value['type'] ? 'text' : $value['type'] );


			// Custom attribute handling.
			$custom_attributes = array();

			if ( ! empty( $value['attributes'] ) && is_array( $value['attributes'] ) ) {
				foreach ( $value['attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			// Description handling.
			$description = self::get_field_description( $value );

			// Switch based on type.
			switch ( $value['type'] ) {

				// Section Titles.
				case 'title':
					if ( ! empty( $value['title'] ) || ! empty( $value['desc'] ) ) {
						?>
						<div class="walkthecounty-setting-tab-header walkthecounty-setting-tab-header-<?php echo $current_tab; ?>">
							<?php if ( ! empty( $value['title'] ) ) : ?>
								<h2><?php echo self::get_field_title( $value ); ?></h2>
								<hr>
							<?php endif; ?>

							<?php if ( ! empty( $value['desc'] ) ) : ?>
								<?php echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) ); ?>
							<?php endif; ?>
						</div>
						<?php
					}

					if ( $value['table_html'] ) {
						echo '<table class="form-table walkthecounty-setting-tab-body walkthecounty-setting-tab-body-' . $current_tab . '">' . "\n\n";
					}

					if ( ! empty( $value['id'] ) ) {

						/**
						 * Trigger Action.
						 *
						 * Note: action dynamically fire on basis of field id.
						 *
						 * @since 1.8
						 */
						do_action( 'walkthecounty_settings_' . sanitize_title( $value['id'] ) );
					}

					break;

				// Section Ends.
				case 'sectionend':
					if ( ! empty( $value['id'] ) ) {

						/**
						 * Trigger Action.
						 *
						 * Note: action dynamically fire on basis of field id.
						 *
						 * @since 1.8
						 */
						do_action( 'walkthecounty_settings_' . sanitize_title( $value['id'] ) . '_end' );
					}

					if ( $value['table_html'] ) {
						echo '</table>';
					}

					if ( ! empty( $value['id'] ) ) {

						/**
						 * Trigger Action.
						 *
						 * Note: action dynamically fire on basis of field id.
						 *
						 * @since 1.8
						 */
						do_action( 'walkthecounty_settings_' . sanitize_title( $value['id'] ) . '_after' );
					}

					break;

				// Standard text inputs and subtypes like 'number'.
				case 'colorpicker':
				case 'hidden' :
					$value['wrapper_class'] = empty( $value['wrapper_class'] ) ? 'walkthecounty-hidden' : trim( $value['wrapper_class'] ) . ' walkthecounty-hidden';
				case 'text':
				case 'email':
				case 'number':
				case 'password' :
					$type = $value['type'];
					$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

					// Set default value for repeater field if not any value set yet.
					if ( $value['repeat'] && is_string( $option_value ) ) {
						$option_value = array( $value['default'] );
					}
					?>
				<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
					<th scope="row" class="titledesc">
						<label
								for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
					</th>
					<td class="walkthecounty-forminp walkthecounty-forminp-<?php echo sanitize_title( $value['type'] ) ?>">
						<?php if ( $value['repeat'] ) : ?>
							<?php foreach ( $option_value as $index => $field_value ) : ?>
								<p>
									<input
											name="<?php echo esc_attr( $value['id'] ); ?>[]"
											type="<?php echo esc_attr( $type ); ?>"
											style="<?php echo esc_attr( $value['css'] ); ?>"
											value="<?php echo esc_attr( $field_value ); ?>"
											class="walkthecounty-input-field<?php echo( empty( $value['class'] ) ? '' : ' ' . esc_attr( $value['class'] ) ); ?> <?php echo esc_attr( $value['id'] ); ?>"
										<?php echo implode( ' ', $custom_attributes ); ?>
									/>
									<span class="walkthecounty-remove-setting-field"
											title="<?php esc_html_e( 'Remove setting field', 'walkthecounty' ); ?>">-</span>
								</p>
							<?php endforeach; ?>
							<a href="#" data-id="<?php echo $value['id']; ?>"
									class="walkthecounty-repeat-setting-field button-secondary"><?php echo $value['repeat_btn_title']; ?></a>
						<?php else : ?>
							<input
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									type="<?php echo esc_attr( $type ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="walkthecounty-input-field<?php echo( empty( $value['class'] ) ? '' : ' ' . esc_attr( $value['class'] ) ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
							/>
						<?php endif; ?>
						<?php echo $description; ?>
					</td>
					</tr><?php
					break;

				// Textarea.
				case 'textarea':

					$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
					$default_attributes = array(
						'rows' => 10,
						'cols' => 60
					);
					$textarea_attributes = isset( $value['attributes'] ) ? $value['attributes'] : array();
					?>
					<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
						<th scope="row" class="titledesc">
							<label
									for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
						</th>
						<td class="walkthecounty-forminp walkthecounty-forminp-<?php echo sanitize_title( $value['type'] ) ?>">
									<textarea
										name="<?php echo esc_attr( $value['id'] ); ?>"
										id="<?php echo esc_attr( $value['id'] ); ?>"
										style="<?php echo esc_attr( $value['css'] ); ?>"
										class="<?php echo esc_attr( $value['class'] ); ?>"
										<?php echo walkthecounty_get_attribute_str( $textarea_attributes, $default_attributes ); ?>
									><?php echo esc_textarea( $option_value ); ?></textarea>
							<?php echo $description; ?>
						</td>
					</tr>
					<?php
					break;

				// Select boxes.
				case 'select' :
				case 'multiselect' :
					$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

					/**
					 * Insert page in option if missing.
					 *
					 * Check success_page setting in general settings.
					 */
					if (
						isset( $value['attributes'] ) &&
						false !== strpos( $value['class'], 'walkthecounty-select-chosen' ) &&
						in_array( 'data-search-type', array_keys( $value['attributes' ] ) ) &&
						'pages' === $value['attributes' ]['data-search-type'] &&
						! in_array( $option_value, array_keys( $value['options'] ) )
					) {
						$value['options'][ $option_value ] = get_the_title( $option_value );
					}
					?>
				<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
					</th>
					<td class="walkthecounty-forminp walkthecounty-forminp-<?php echo sanitize_title( $value['type'] ) ?>">
						<select
								name="<?php echo esc_attr( $value['id'] ); ?><?php if ( 'multiselect' === $value['type'] ) echo '[]'; ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); ?>
							<?php echo ( 'multiselect' === $value['type'] ) ? 'multiple="multiple"' : ''; ?>
						>

							<?php
							if ( ! empty( $value['options'] ) ) {
								foreach ( $value['options'] as $key => $val ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php

									if ( is_array( $option_value ) ) {
										selected( in_array( $key, $option_value ), true );
									} else {
										selected( $option_value, $key );
									}

									?>><?php echo $val ?></option>
									<?php
								}
							}
							?>

						</select> <?php echo $description; ?>
					</td>
					</tr><?php
					break;

				// Radio inputs.
				case 'radio_inline' :
					$value['class'] = empty( $value['class'] ) ? 'walkthecounty-radio-inline' : $value['class'] . ' walkthecounty-radio-inline';
				case 'radio' :
					$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
					?>
				<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
					<th scope="row" class="titledesc">
						<label
								for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
					</th>
					<td class="walkthecounty-forminp walkthecounty-forminp-<?php echo sanitize_title( $value['type'] ) ?> <?php echo( ! empty( $value['class'] ) ? $value['class'] : '' ); ?>">
						<fieldset>
							<ul>
								<?php
								foreach ( $value['options'] as $key => $val ) {
									?>
									<li>
										<label><input
													name="<?php echo esc_attr( $value['id'] ); ?>"
													value="<?php echo $key; ?>"
													type="radio"
													style="<?php echo esc_attr( $value['css'] ); ?>"
												<?php echo implode( ' ', $custom_attributes ); ?>
												<?php checked( $key, $option_value ); ?>
											/> <?php echo $val ?></label>
									</li>
									<?php
								}
								?>
								<?php echo $description; ?>
						</fieldset>
					</td>
					</tr><?php
					break;

				// Checkbox input.
				case 'checkbox' :
					$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
					?>
					<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
						<th scope="row" class="titledesc">
							<label
									for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
						</th>
						<td class="walkthecounty-forminp">
							<input
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									type="checkbox"
									class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
									value="1"
								<?php checked( $option_value, 'on' ); ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
							/>
							<?php echo $description; ?>
						</td>
					</tr>
					<?php
					break;

				// Multi Checkbox input.
				case 'multicheck' :
					$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
					$option_value = is_array( $option_value ) ? $option_value : array();
					?>
					<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
						<th scope="row" class="titledesc">
							<label
									for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
						</th>
						<td class="walkthecounty-forminp walkthecounty-forminp-<?php echo sanitize_title( $value['type'] ) ?> <?php echo( ! empty( $value['class'] ) ? $value['class'] : '' ); ?>">
							<fieldset>
								<ul>
									<?php
									foreach ( $value['options'] as $key => $val ) {
										?>
										<li>
											<label>
												<input
														name="<?php echo esc_attr( $value['id'] ); ?>[]"
														value="<?php echo $key; ?>"
														type="checkbox"
														style="<?php echo esc_attr( $value['css'] ); ?>"
													<?php echo implode( ' ', $custom_attributes ); ?>
													<?php if ( in_array( $key, $option_value ) ) {
														echo 'checked="checked"';
													} ?>
												/> <?php echo $val ?>
											</label>
										</li>
										<?php
									}
									?>
									<?php echo $description; ?>
							</fieldset>
						</td>
					</tr>
					<?php
					break;

				// File input field.
				case 'file' :
				case 'media' :
					$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
					$button_label = sprintf( __( 'Add or Upload %s', 'walkthecounty' ), ( 'file' === $value['type'] ? __( 'File', 'walkthecounty' ) : __( 'Image', 'walkthecounty' ) ) );
					$fvalue       = empty( $value['fvalue'] ) ? 'url' : $value['fvalue'];

					$allow_media_preview_tags = array( 'jpg', 'jpeg', 'png', 'gif', 'ico' );
					$preview_image_src        = $option_value ? ( 'id' === $fvalue ? wp_get_attachment_url( $option_value ) : $option_value ) : '';
					$preview_image_extension  = $preview_image_src ? pathinfo( $preview_image_src, PATHINFO_EXTENSION ) : '';
					$is_show_preview          = in_array( $preview_image_extension, $allow_media_preview_tags );
					?>
					<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
						<th scope="row" class="titledesc">
							<label
									for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
						</th>
						<td class="walkthecounty-forminp">
							<div class="walkthecounty-field-wrap">
								<label for="<?php echo $value['id'] ?>">
									<input
											name="<?php echo esc_attr( $value['id'] ); ?>"
											id="<?php echo esc_attr( $value['id'] ); ?>"
											type="text"
											class="walkthecounty-input-field<?php echo esc_attr( isset( $value['class'] ) ? ' ' . $value['class'] : '' ); ?>"
											value="<?php echo $option_value; ?>"
											style="<?php echo esc_attr( $value['css'] ); ?>"
										<?php echo implode( ' ', $custom_attributes ); ?>
									/>&nbsp;&nbsp;&nbsp;&nbsp;<input class="walkthecounty-upload-button button" type="button"
											data-fvalue="<?php echo $fvalue; ?>"
											data-field-type="<?php echo $value['type']; ?>"
											value="<?php echo $button_label; ?>">
									<?php echo $description ?>
									<div
											class="walkthecounty-image-thumb<?php echo ! $option_value || ! $is_show_preview ? ' walkthecounty-hidden' : ''; ?>">
										<span class="walkthecounty-delete-image-thumb dashicons dashicons-no-alt"></span>
										<img src="<?php echo $preview_image_src; ?>" alt="">
									</div>
								</label>
							</div>
						</td>
					</tr>
					<?php
					break;

				// WordPress Editor.
				case 'wysiwyg' :
					// Get option value.
					$option_value = self::get_option( $option_name, $value['id'], $value['default'] );

					// Get editor settings.
					$editor_settings = ! empty( $value['options'] ) ? $value['options'] : array();
					?>
				<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
					<th scope="row" class="titledesc">
						<label
								for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
					</th>
					<td class="walkthecounty-forminp">
						<?php wp_editor( $option_value, $value['id'], $editor_settings ); ?>
						<?php echo $description; ?>
					</td>
					</tr><?php
					break;

				// Custom: Email preview buttons field.
				case 'email_preview_buttons' :
					?>
				<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
					<th scope="row" class="titledesc">
						<label
								for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
					</th>
					<td class="walkthecounty-forminp">
						<?php walkthecounty_email_preview_buttons_callback( $value ); ?>
						<?php echo $description; ?>
					</td>
					</tr><?php
					break;

				// Custom: API field.
				case 'api' :
					walkthecounty_api_callback();
					echo $description;
					break;

				// Custom: Gateway API key.
				case 'api_key' :
					$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
					$type         = ! empty( $option_value ) ? 'password' : 'text';
					?>
				<tr valign="top" <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '' ?>>
					<th scope="row" class="titledesc">
						<label
								for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo self::get_field_title( $value ); ?></label>
					</th>
					<td class="walkthecounty-forminp walkthecounty-forminp-<?php echo sanitize_title( $value['type'] ) ?>">
						<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $type ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( trim( $option_value ) ); ?>"
								class="walkthecounty-input-field<?php echo( empty( $value['class'] ) ? '' : ' ' . esc_attr( $value['class'] ) ); ?>"
							<?php echo implode( ' ', $custom_attributes ); ?>
						/> <?php echo $description; ?>
					</td>
					</tr><?php
					break;

				// Note: only for internal use.
				case 'chosen' :

					// Get option value.
					$option_value     = self::get_option( $option_name, $value['id'], $value['default'] );
					$option_value     = is_array( $option_value ) ? array_fill_keys( $option_value, 'selected' ) : $option_value;
					$wrapper_class    = ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '';
					$type             = '';
					$allow_new_values = '';
					$name             = walkthecounty_get_field_name( $value );

					// Set attributes based on multiselect datatype.
					if ( 'multiselect' === $value['data_type'] ) {
						$type             = 'multiple';
						$allow_new_values = 'data-allows-new-values="true"';
						$name             = $name . '[]';
						$option_value     = empty( $option_value ) ? array() : $option_value;
					}

					$title_prefixes_value = ( is_array( $option_value ) && count( $option_value ) > 0 ) ?
						array_merge( $value['options'], $option_value ) :
						$value['options'];

					?>
					<tr valign="top" <?php echo $wrapper_class; ?>>
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_attr( self::get_field_title( $value ) ); ?></label>
						</th>
						<td class="walkthecounty-forminp walkthecounty-forminp-<?php echo esc_attr( $value['type'] ); ?>">
							<select
									class="walkthecounty-select-chosen walkthecounty-chosen-settings"
									style="<?php echo esc_attr( $value['style'] ); ?>"
									name="<?php echo esc_attr( $name ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
								<?php
								echo "{$type} {$allow_new_values}";
								echo implode( ' ', $custom_attributes );
								?>
							>
								<?php
								if ( is_array( $title_prefixes_value ) && count( $title_prefixes_value ) > 0 ) {
									foreach ( $title_prefixes_value as $key => $item_value ) {
										echo sprintf(
											'<option %1$s value="%2$s">%2$s</option>',
											( 'selected' === $item_value ) ? 'selected="selected"' : '',
											esc_attr( $key )
										);
									}
								}
								?>
							</select>
							<?php echo wp_kses_post( $description ); ?>
						</td>
					</tr>
					<?php
					break;

				// Custom: Log field.
				case 'logs' :

					// Get current section.
					$current_section = $_GET['section'] = walkthecounty_get_current_setting_section();

					/**
					 * Fires for each tab of logs view.
					 *
					 * @since 1.0
					 */
					do_action( "walkthecounty_logs_view_{$current_section}" );

					echo $description;
					break;

				// Custom: Data field.
				case 'data' :

					include WALKTHECOUNTY_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-data.php';

					echo $description;
					break;

				// Custom: WalkTheCounty Docs Link field type.
				case 'walkthecounty_docs_link' :
					$wrapper_class = ! empty( $value['wrapper_class'] ) ? 'class="' . $value['wrapper_class'] . '"' : '';
					?>
				<tr valign="top" <?php echo esc_html( $wrapper_class ); ?>>
					<td class="walkthecounty-docs-link" colspan="2">
						<p class="walkthecounty-docs-link">
							<a href="<?php echo esc_url( $value['url'] ); ?>" target="_blank">
								<?php
								echo sprintf(
									/* translators: %s Title */
									esc_html__( 'Need Help? See docs on "%s"', 'walkthecounty' ),
									esc_html( $value['title'] )
								);
								?>
								<span class="dashicons dashicons-editor-help"></span>
							</a>
						</p>
					</td>
					</tr><?php
					break;

				// Default: run an action
				// You can add or handle your custom field action.
				default:
					// Get option value.
					$option_value = self::get_option( $option_name, $value['id'], $value['default'] );
					do_action( 'walkthecounty_admin_field_' . $value['type'], $value, $option_value );
					break;
			}
		}

		/**
		 * Helper function to get the formatted description for a walkthecountyn form field.
		 * Plugins can call this when implementing their own custom settings types.
		 *
		 * @since  1.8
		 *
		 * @param  array $value The form field value array
		 *
		 * @return string The HTML description of the field.
		 */
		public static function get_field_description( $value ) {
			$description = '';

			// Support for both 'description' and 'desc' args.
			$description_key = isset( $value['description'] ) ? 'description' : 'desc';
			$value           = ( isset( $value[ $description_key ] ) && ! empty( $value[ $description_key ] ) ) ? $value[ $description_key ] : '';

			if ( ! empty( $value ) ) {
				$description = '<div class="walkthecounty-field-description">' . wp_kses_post( $value ) . '</div>';
			}

			return $description;
		}


		/**
		 * Helper function to get the formated title.
		 * Plugins can call this when implementing their own custom settings types.
		 *
		 * @since  1.8
		 *
		 * @param  array $value The form field value array
		 *
		 * @return array The description and tip as a 2 element array
		 */
		public static function get_field_title( $value ) {
			$title = esc_html( $value['title'] );

			// If html tag detected then allow them to print.
			if ( strip_tags( $title ) ) {
				$title = $value['title'];
			}

			return $title;
		}

		/**
		 * Save admin fields.
		 *
		 * Loops though the walkthecounty options array and outputs each field.
		 *
		 * @since  1.8
		 *
		 * @param  array  $options     Options array to output
		 * @param  string $option_name Option name to save output. If empty then option will be store in there own option name i.e option id.
		 *
		 * @return bool
		 */
		public static function save_fields( $options, $option_name = '' ) {

			// Fetch form posted super global data.
			$post_data = walkthecounty_clean( $_POST );

			// Bailout, if posted data doesn't exists.
			if ( empty( $post_data ) ) {
				return false;
			}

			$new_options      = array();
			$options_keys     = array_keys( $options );
			$is_vertical_tabs = is_array( $options_keys ) && count( $options_keys ) > 0 ? ctype_alnum( $options_keys[0] ) : false;

			if ( $is_vertical_tabs ) {

				// Loop through each vertical tabs related field options to destructure into single array.
				foreach( $options as $option ) {
					$new_options = array_merge( $new_options, $option );
				}

				// Assign new field options.
				$options = $new_options;
			}

			// Options to update will be stored here and saved later.
			$update_options = array();

			// Loop options and get values to save.
			foreach ( $options as $option ) {
				if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) ) {
					continue;
				}

				// Get posted value.
				if ( strstr( $option['id'], '[' ) ) {
					parse_str( $option['id'], $option_name_array );
					$field_option_name = current( array_keys( $option_name_array ) );
					$setting_name      = key( $option_name_array[ $field_option_name ] );
					$raw_value         = isset( $_POST[ $field_option_name ][ $setting_name ] ) ? wp_unslash( $_POST[ $field_option_name ][ $setting_name ] ) : null;
				} else {
					$field_option_name = $option['id'];
					$setting_name      = '';
					$raw_value         = isset( $_POST[ $option['id'] ] ) ? wp_unslash( $_POST[ $option['id'] ] ) : null;
				}

				// Format the value based on option type.
				switch ( $option['type'] ) {
					case 'checkbox' :
						$value = is_null( $raw_value ) ? '' : 'on';
						break;
					case 'wysiwyg'  :
					case 'textarea' :
						$value = wp_kses_post( trim( $raw_value ) );
						break;
					case 'multiselect' :
					case 'chosen' :
						$value = array_filter( array_map( 'walkthecounty_clean', (array) $raw_value ) );
						break;
					default :
						$value = walkthecounty_clean( $raw_value );
						break;
				}

				/**
				 * Sanitize the value of an option.
				 *
				 * @since 1.8
				 */
				$value = apply_filters( 'walkthecounty_admin_settings_sanitize_option', $value, $option, $raw_value );

				/**
				 * Sanitize the value of an option by option name.
				 *
				 * @since 1.8
				 */
				$value = apply_filters( "walkthecounty_admin_settings_sanitize_option_{$field_option_name}", $value, $option, $raw_value );

				if ( is_null( $value ) ) {
					continue;
				}

				// Check if option is an array and handle that differently to single values.
				if ( $field_option_name && $setting_name ) {
					if ( ! isset( $update_options[ $field_option_name ] ) ) {
						$update_options[ $field_option_name ] = get_option( $field_option_name, array() );
					}
					if ( ! is_array( $update_options[ $field_option_name ] ) ) {
						$update_options[ $field_option_name ] = array();
					}
					$update_options[ $field_option_name ][ $setting_name ] = $value;
				} else {
					$update_options[ $field_option_name ] = $value;
				}
			}

			// Save all options in our array or there own option name i.e. option id.
			if ( empty( $option_name ) ) {
				foreach ( $update_options as $name => $value ) {
					update_option( $name, $value, false );

					/**
					 * Trigger action.
					 *
					 * Note: This is dynamically fire on basis of option name.
					 *
					 * @since 1.8
					 */
					do_action( "walkthecounty_save_option_{$name}", $value, $name );
				}
			} else {
				$old_options    = ( $old_options = get_option( $option_name ) ) ? $old_options : array();
				$update_options = array_merge( $old_options, $update_options );

				update_option( $option_name, $update_options, false );

				/**
				 * Trigger action.
				 *
				 * Note: This is dynamically fire on basis of setting name.
				 *
				 * @since 1.8
				 */
				do_action( "walkthecounty_save_settings_{$option_name}", $update_options, $option_name, $old_options );
			}

			return true;
		}


		/**
		 * Check if admin saving setting or not.
		 *
		 * @since 1.8.17
		 *
		 * @return bool
		 */
		public static function is_saving_settings() {
			return self::verify_nonce();
		}

		/**
		 * Verify setting page
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param string $tab
		 * @param string $section
		 *
		 * @return bool
		 */
		public static function is_setting_page( $tab = '', $section = '' ) {
			$is_setting_page = false;

			// Are we accessing admin?
			if( ! is_admin() ) {
				return $is_setting_page;
			}

			// Are we accessing any walkthecounty page?
			if(
				! isset( $_GET['post_type'], $_GET['page'] )
				|| 'walkthecounty_forms' !== walkthecounty_clean( $_GET['post_type'] )
			) {
				return $is_setting_page;
			}

			// Check fo setting tab.
			if ( ! empty( $tab ) ) {
				$is_setting_page = ( $tab === walkthecounty_get_current_setting_tab() );
			}

			// Check fo setting section.
			if ( ! empty( $section ) ) {
				$is_setting_page = ( $section === walkthecounty_get_current_setting_section() );
			}

			return $is_setting_page;
		}
	}

endif;
