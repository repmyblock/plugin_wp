<?php
/**
 * Admin Plugins
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Plugins
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugins row action links
 *
 * @since 1.4
 *
 * @param array $actions An array of plugin action links.
 *
 * @return array An array of updated action links.
 */
function walkthecounty_plugin_action_links( $actions ) {
	$new_actions = array(
		'settings' => sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'edit.php?post_type=walkthecounty_forms&page=walkthecounty-settings' ),
			__( 'Settings', 'walkthecounty' )
		),
	);

	return array_merge( $new_actions, $actions );
}

add_filter( 'plugin_action_links_' . WALKTHECOUNTY_PLUGIN_BASENAME, 'walkthecounty_plugin_action_links' );


/**
 * Plugin row meta links
 *
 * @since 1.4
 *
 * @param array $plugin_meta An array of the plugin's metadata.
 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
 *
 * @return array
 */
function walkthecounty_plugin_row_meta( $plugin_meta, $plugin_file ) {
	if ( WALKTHECOUNTY_PLUGIN_BASENAME !== $plugin_file ) {
		return $plugin_meta;
	}

	$new_meta_links = array(
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url(
				add_query_arg(
					array(
						'utm_source'   => 'plugins-page',
						'utm_medium'   => 'plugin-row',
						'utm_campaign' => 'admin',
					), 'https://walkthecountywp.com/documentation/'
				)
			),
			__( 'Documentation', 'walkthecounty' )
		),
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url(
				add_query_arg(
					array(
						'utm_source'   => 'plugins-page',
						'utm_medium'   => 'plugin-row',
						'utm_campaign' => 'admin',
					), 'https://walkthecountywp.com/addons/'
				)
			),
			__( 'Add-ons', 'walkthecounty' )
		),
	);

	return array_merge( $plugin_meta, $new_meta_links );
}

add_filter( 'plugin_row_meta', 'walkthecounty_plugin_row_meta', 10, 2 );


/**
 * Get the Parent Page Menu Title in admin section.
 * Based on get_admin_page_title WordPress Function.
 *
 * @since 1.8.17
 *
 * @global array $submenu
 * @global string $plugin_page
 *
 * @return string $title Page title
 */
function walkthecounty_get_admin_page_menu_title() {
	$title = '';
	global $submenu, $plugin_page;

	foreach ( array_keys( $submenu ) as $parent ) {
		if ( 'edit.php?post_type=walkthecounty_forms' !== $parent ) {
			continue;
		}

		foreach ( $submenu[ $parent ] as $submenu_array ) {
			if ( $plugin_page !== $submenu_array[2] ) {
				continue;
			}

			$title = isset( $submenu_array[0] ) ?
				$submenu_array[0] :
				$submenu_array[3];
		}
	}

	return $title;
}

/**
 * Store recently activated WalkTheCounty's addons to wp options.
 *
 * @since 2.1.0
 */
function walkthecounty_recently_activated_addons() {
	// Check if action is set.
	if ( isset( $_REQUEST['action'] ) ) {
		$plugin_action = ( '-1' !== $_REQUEST['action'] ) ? $_REQUEST['action'] : ( isset( $_REQUEST['action2'] ) ? $_REQUEST['action2'] : '' );
		$plugins       = array();

		switch ( $plugin_action ) {
			case 'activate': // Single add-on activation.
				$plugins[] = $_REQUEST['plugin'];
				break;
			case 'activate-selected': // If multiple add-ons activated.
				$plugins = $_REQUEST['checked'];
				break;
		}


		if ( ! empty( $plugins ) ) {

			$walkthecounty_addons = walkthecounty_get_recently_activated_addons();

			foreach ( $plugins as $plugin ) {
				// Get plugins which has 'WalkTheCounty-' as prefix.
				if ( stripos( $plugin, 'WalkTheCounty-' ) !== false ) {
					$walkthecounty_addons[] = $plugin;
				}
			}

			if ( ! empty( $walkthecounty_addons ) ) {
				// Update the WalkTheCounty's activated add-ons.
				update_option( 'walkthecounty_recently_activated_addons', $walkthecounty_addons, false );
			}
		}
	}
}

// Add add-on plugins to wp option table.
add_action( 'activated_plugin', 'walkthecounty_recently_activated_addons', 10 );

/**
 * Create new menu in plugin section that include all the add-on
 *
 * @since 2.1.0
 *
 * @param $plugin_menu
 *
 * @return mixed
 */
function walkthecounty_filter_addons_do_filter_addons( $plugin_menu ) {
	global $plugins;

	$walkthecounty_addons = wp_list_pluck( walkthecounty_get_plugins( array( 'only_add_on' => true ) ), 'Name' );

	if( ! empty( $walkthecounty_addons ) ) {
		foreach ( $plugins['all'] as $file => $plugin_data ) {

			if ( in_array( $plugin_data['Name'], $walkthecounty_addons ) ) {
				$plugins['walkthecounty'][ $file ]           = $plugins['all'][ $file ];
				$plugins['walkthecounty'][ $file ]['plugin'] = $file;

				// Replicate the next step.
				if ( current_user_can( 'update_plugins' ) ) {
					$current = get_site_transient( 'update_plugins' );

					if ( isset( $current->response[ $file ] ) ) {
						$plugins['walkthecounty'][ $file ]['update'] = true;
						$plugins['walkthecounty'][ $file ] = array_merge( (array) $current->response[ $file ], $plugins['walkthecounty'][ $file ] );
					} elseif ( isset( $current->no_update[ $file ] ) ){
						$plugins['walkthecounty'][ $file ] = array_merge( (array) $current->no_update[ $file ], $plugins['walkthecounty'][ $file ] );
					}
				}
			}
		}
	}

	return $plugin_menu;

}

add_filter( 'show_advanced_plugins', 'walkthecounty_filter_addons_do_filter_addons' );
add_filter( 'show_network_active_plugins', 'walkthecounty_filter_addons_do_filter_addons' );

/**
 * Keep activating the same add-on when admin activate or deactivate from WalkTheCounty Menu
 *
 * @since 2.2.0
 *
 * @param $action
 * @param $result
 */
function walkthecounty_prepare_filter_addons_referer( $action, $result ) {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( is_object( $screen ) && $screen->base === 'plugins' && ! empty( $_REQUEST['plugin_status'] ) && $_REQUEST['plugin_status'] === 'walkthecounty' ) {
		global $status;
		$status = 'walkthecounty';
	}
}

add_action( 'check_admin_referer', 'walkthecounty_prepare_filter_addons_referer', 10, 2 );

/**
 * Make the WalkTheCounty Menu as an default menu and update the Menu Name
 *
 * @since 2.1.0
 *
 * @param $views
 *
 * @return mixed
 */
function walkthecounty_filter_addons_filter_addons( $views ) {

	global $status, $plugins;

	if ( ! empty( $plugins['walkthecounty'] ) ) {
		$class = '';

		if ( 'walkthecounty' === $status ) {
			$class = 'current';
		}

		$views['walkthecounty'] = sprintf(
			'<a class="%s" href="plugins.php?plugin_status=walkthecounty"> %s <span class="count">(%s) </span></a>',
			$class,
			__( 'WalkTheCounty', 'walkthecounty' ),
			count( $plugins['walkthecounty'] )
		);
	}

	return $views;
}

add_filter( 'views_plugins', 'walkthecounty_filter_addons_filter_addons' );
add_filter( 'views_plugins-network', 'walkthecounty_filter_addons_filter_addons' );

/**
 * Set the WalkTheCounty as the Main menu when admin click on the WalkTheCounty Menu in Plugin section.
 *
 * @since 2.1.0
 *
 * @param $plugins
 *
 * @return mixed
 */
function walkthecounty_prepare_filter_addons( $plugins ) {
	global $status;

	if ( isset( $_REQUEST['plugin_status'] ) && 'walkthecounty' === $_REQUEST['plugin_status'] ) {
		$status = 'walkthecounty';
	}

	return $plugins;
}

add_filter( 'all_plugins', 'walkthecounty_prepare_filter_addons' );


/**
 * Display the upgrade notice message.
 *
 * @param array $data Array of plugin metadata.
 * @param array $response An array of metadata about the available plugin update.
 *
 * @since 2.1
 */
function walkthecounty_in_plugin_update_message( $data, $response ) {
	$new_version           = $data['new_version'];
	$current_version_parts = explode( '.', WALKTHECOUNTY_VERSION );
	$new_version_parts     = explode( '.', $new_version );

	// If it is a minor upgrade then return.
	if ( version_compare( $current_version_parts[0] . '.' . $current_version_parts[1], $new_version_parts[0] . '.' . $new_version_parts[1], '=' ) ) {

		return;
	}

	// Get the upgrade notice from the trunk.
	$upgrade_notice = walkthecounty_get_plugin_upgrade_notice( $new_version );

	// Display upgrade notice.
	echo apply_filters( 'walkthecounty_in_plugin_update_message', $upgrade_notice ? '</p>' . wp_kses_post( $upgrade_notice ) . '<p class="dummy">' : '' );
}

// Display upgrade notice.
add_action( 'in_plugin_update_message-' . WALKTHECOUNTY_PLUGIN_BASENAME, 'walkthecounty_in_plugin_update_message', 10, 2 );


/**
 * Get the upgrade notice from WordPress.org.
 *
 * Note: internal purpose use only
 *
 * @since 2.1
 *
 * @param string $new_version New verison of the plugin.
 *
 * @return string
 */
function walkthecounty_get_plugin_upgrade_notice( $new_version ) {

	// Cache the upgrade notice.
	$transient_name = "walkthecounty_upgrade_notice_{$new_version}";
	$upgrade_notice = get_transient( $transient_name );

	if ( false === $upgrade_notice ) {
		$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/walkthecounty/trunk/readme.txt' );

		if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
			$upgrade_notice = walkthecounty_parse_plugin_update_notice( $response['body'], $new_version );
			set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
		}
	}

	return $upgrade_notice;
}


/**
 * Parse update notice from readme file.
 *
 * Note: internal purpose use only
 *
 * @since 2.1
 *
 * @param  string $content Content of the readme.txt file.
 * @param  string $new_version The version with current version is compared.
 *
 * @return string
 */
function walkthecounty_parse_plugin_update_notice( $content, $new_version ) {
	$version_parts     = explode( '.', $new_version );
	$check_for_notices = array(
		$version_parts[0] . '.0',
		$version_parts[0] . '.0.0',
		$version_parts[0] . '.' . $version_parts[1] . '.' . '0',
	);

	// Regex to extract Upgrade notice from the readme.txt file.
	$notice_regexp = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( $new_version ) . '\s*=|$)~Uis';

	$upgrade_notice = '';

	foreach ( $check_for_notices as $check_version ) {
		if ( version_compare( WALKTHECOUNTY_VERSION, $check_version, '>' ) ) {
			continue;
		}

		$matches = null;

		if ( preg_match( $notice_regexp, $content, $matches ) ) {
			$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

			if ( version_compare( trim( $matches[1] ), $check_version, '=' ) ) {
				$upgrade_notice .= '<p class="walkthecounty-plugin-upgrade-notice">';

				foreach ( $notices as $index => $line ) {
					$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
				}

				$upgrade_notice .= '</p>';
			}

			if ( ! empty( $upgrade_notice ) ) {
				break;
			}
		}
	}

	return wp_kses_post( $upgrade_notice );
}


/**
 * Add styling to the plugin upgrade notice.
 *
 * @since 2.1
 */
function walkthecounty_plugin_notice_css() {
	?>
	<style type="text/css">
		#walkthecounty-update .walkthecounty-plugin-upgrade-notice {
			font-weight: 400;
			background: #fff8e5 !important;
			border-left: 4px solid #ffb900;
			border-top: 1px solid #ffb900;
			padding: 9px 0 9px 12px !important;
			margin: 0 -12px 0 -16px !important;
		}

		#walkthecounty-update .walkthecounty-plugin-upgrade-notice:before {
			content: '\f348';
			display: inline-block;
			font: 400 18px/1 dashicons;
			speak: none;
			margin: 0 8px 0 -2px;
			vertical-align: top;
		}

		#walkthecounty-update .dummy {
			display: none;
		}
	</style>
	<?php
}

add_action( 'admin_head', 'walkthecounty_plugin_notice_css' );

/**
 * Get list of add-on last activated.
 *
 * @since 2.1.3
 *
 * @return mixed|array list of recently activated add-on
 */
function walkthecounty_get_recently_activated_addons() {
	return get_option( 'walkthecounty_recently_activated_addons', array() );
}

/**
 * Renders the WalkTheCounty Deactivation Survey Form.
 * Note: only for internal use
 *
 * @since 2.2
 */
function walkthecounty_deactivation_popup() {
	// Bailout.
	if ( ! current_user_can( 'delete_plugins' ) ) {
		walkthecounty_die();
	}

	$results = array();

	// Start output buffering.
	ob_start();
	?>

	<h2 id="deactivation-survey-title">
		<img src="<?php echo esc_url( WALKTHECOUNTY_PLUGIN_URL ) ?>/assets/dist/images/walkthecounty-icon-full-circle.svg">
		<span><?php esc_html_e( 'WalkTheCountyWP Deactivation', 'walkthecounty' ); ?></span>
	</h2>
	<form class="deactivation-survey-form" method="POST">
		<p><?php esc_html_e( 'If you have a moment, please let us know why you are deactivating WalkTheCounty. All submissions are anonymous and we only use this feedback to improve this plugin.', 'walkthecounty' ); ?></p>

		<div>
			<label class="walkthecounty-field-description">
				<input type="radio" name="walkthecounty-survey-radios" value="1">
				<?php esc_html_e( "I'm only deactivating temporarily", 'walkthecounty' ); ?>
			</label>
		</div>

		<div>
			<label class="walkthecounty-field-description">
				<input type="radio" name="walkthecounty-survey-radios" value="2">
				<?php esc_html_e( 'I no longer need the plugin', 'walkthecounty' ); ?>
			</label>
		</div>

		<div>
			<label class="walkthecounty-field-description">
				<input type="radio" name="walkthecounty-survey-radios" value="3" data-has-field="true">
				<?php esc_html_e( 'I found a better plugin', 'walkthecounty' ); ?>
			</label>

			<div class="walkthecounty-survey-extra-field">
				<p><?php esc_html_e( 'What is the name of the plugin?', 'walkthecounty' ); ?></p>
				<input type="text" name="user-reason" class="widefat">
			</div>
		</div>

		<div>
			<label class="walkthecounty-field-description">
				<input type="radio" name="walkthecounty-survey-radios" value="4">
				<?php esc_html_e( 'I only needed the plugin for a short period', 'walkthecounty' ); ?>
			</label>
		</div>

		<div>
			<label class="walkthecounty-field-description">
				<input type="radio" name="walkthecounty-survey-radios" value="5" data-has-field="true">
				<?php esc_html_e( 'The plugin broke my site', 'walkthecounty' ); ?>
			</label>

			<div class="walkthecounty-survey-extra-field">
				<p><?php
					printf(
						'%1$s %2$s %3$s',
						__( "We're sorry to hear that, check", 'walkthecounty' ),
						'<a href="https://wordpress.org/support/plugin/walkthecounty">WalkTheCountyWP Support</a>.',
						__( 'Can you describe the issue?', 'walkthecounty' )
					);
					?>
				</p>
				<textarea disabled name="user-reason" class="widefat" rows="6"></textarea disabled>
			</div>
		</div>

		<div>
			<label class="walkthecounty-field-description">
				<input type="radio" name="walkthecounty-survey-radios" value="6" data-has-field="true">
				<?php esc_html_e( 'The plugin suddenly stopped working', 'walkthecounty' ); ?>
			</label>

			<div class="walkthecounty-survey-extra-field">
				<p><?php
					printf(
						'%1$s %2$s %3$s',
						__( "We're sorry to hear that, check", 'walkthecounty' ),
						'<a href="https://wordpress.org/support/plugin/walkthecounty">WalkTheCountyWP Support</a>.',
						__( 'Can you describe the issue?', 'walkthecounty' )
					);
					?>
				</p>
				<textarea disabled name="user-reason" class="widefat" rows="6"></textarea disabled>
			</div>
		</div>

		<div>
			<label class="walkthecounty-field-description">
				<input type="radio" name="walkthecounty-survey-radios" value="7" data-has-field="true">
				<?php esc_html_e( 'Other', 'walkthecounty' ); ?>
			</label>

			<div class="walkthecounty-survey-extra-field">
				<p><?php esc_html_e( "Please describe why you're deactivating WalkTheCounty", 'walkthecounty' ); ?></p>
				<textarea disabled name="user-reason" class="widefat" rows="6"></textarea disabled>
			</div>
		</div>

		<div id="survey-and-delete-data">
			<p>
				<label>
					<input type="checkbox" name="confirm_reset_store" value="1">
					<?php esc_html_e( 'Would you like to delete all WalkTheCountyWP data?', 'walkthecounty' ); ?>
				</label>
				<section class="walkthecounty-field-description">
					<?php esc_html_e( 'By default the custom roles, WalkTheCountyWP options, and database entries are not deleted when you deactivate WalkTheCounty. If you are deleting WalkTheCountyWP completely from your website and want those items removed as well check this option. Note: This will permanently delete all WalkTheCountyWP data from your database.', 'walkthecounty' ); ?>
				</section>
			</p>
		</div>
		<?php
		$current_user       = wp_get_current_user();
		$current_user_email = $current_user->user_email;
		?>
		<input type="hidden" name="current-user-email" value="<?php echo $current_user_email; ?>">
		<input type="hidden" name="current-site-url" value="<?php echo esc_url( get_bloginfo( 'url' ) ); ?>">
		<input type="hidden" name="walkthecounty-export-class" value="WalkTheCounty_Tools_Reset_Stats">
		<?php wp_nonce_field( 'walkthecounty_ajax_export', 'walkthecounty_ajax_export' ); ?>
	</form>

	<?php

	// Echo content (deactivation form) from the output buffer.
	$output = ob_get_clean();

	$results['html'] = $output;

	wp_send_json( $results );
}

add_action( 'wp_ajax_walkthecounty_deactivation_popup', 'walkthecounty_deactivation_popup' );

/**
 * Ajax callback after the deactivation survey form has been submitted.
 * Note: only for internal use
 *
 * @since 2.2
 */
function walkthecounty_deactivation_form_submit() {

	if ( ! check_ajax_referer( 'deactivation_survey_nonce', 'nonce', false ) ) {
		wp_send_json_error();
	}

	$form_data = walkthecounty_clean( wp_parse_args( $_POST['form-data'] ) );

	// Get the selected radio value.
	$radio_value = isset( $form_data['walkthecounty-survey-radios'] ) ? $form_data['walkthecounty-survey-radios'] : 0;

	// Get the reason if any radio button has an optional text field.
	$user_reason = isset( $form_data['user-reason'] ) ? $form_data['user-reason'] : '';

	// Get the email of the user who deactivated the plugin.
	$user_email = isset( $form_data['current-user-email'] ) ? $form_data['current-user-email'] : '';

	// Get the URL of the website on which WalkTheCounty plugin is being deactivated.
	$site_url = isset( $form_data['current-site-url'] ) ? $form_data['current-site-url'] : '';

	// Get the value of the checkbox for deleting WalkTheCounty's data.
	$delete_data = isset( $form_data['confirm_reset_store'] ) ? $form_data['confirm_reset_store'] : '';

	/**
	 * Make a POST request to the endpoint to send the survey data.
	 */
	$response = wp_remote_post(
		'http://survey.walkthecountywp.com/wp-json/walkthecounty/v2/survey/',
		array(
			'body' => array(
				'radio_value'        => $radio_value,
				'user_reason'        => $user_reason,
				'current_user_email' => $user_email,
				'site_url'           => $site_url,
			),
		)
	);

	// Check if the data is sent and stored correctly.
	$response = wp_remote_retrieve_body( $response );

	if ( 'true' === $response ) {
		if ( '1' === $delete_data ) {
			wp_send_json_success(
				array(
					'delete_data' => true,
				)
			);
		} else {
			wp_send_json_success(
				array(
					'delete_data' => false,
				)
			);
		}
	} else {
		wp_send_json_error();
	}
}

add_action( 'wp_ajax_deactivation_form_submit', 'walkthecounty_deactivation_form_submit' );
