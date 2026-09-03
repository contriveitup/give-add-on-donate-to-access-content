<?php

/**
 * Plugin Name: Give Addon - Donate To Access Content
 * Plugin URI: https://github.com/contriveitup/give-add-on-donate-to-access-content
 * Description: Give plugin Add-on ask users to donate in order to access content of a post or page. It can also restrict entire website to chosen post, page, category page, post types and much more...
 * Version: 3.0.0
 * Requires at least: 6.0
 * Tested up to: 7.1
 * Requires PHP: 8.1
 * Requires Plugins: give
 * Author: TheMediaAble
 * Author URI: https://themediaable.com
 * Text Domain: dtac-give
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/contriveitup/give-add-on-donate-to-access-content
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'DTAC_GIVE_MIN_PHP' ) ) {
	define( 'DTAC_GIVE_MIN_PHP', '8.1' );
}

if ( ! defined( 'DTAC_GIVE_PLUGIN_FILE' ) ) {
	define( 'DTAC_GIVE_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'DTAC_GIVE_PLUGIN_BASENAME' ) ) {
	define( 'DTAC_GIVE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'DTAC_GIVE_PLUGIN_DIR' ) ) {
	define( 'DTAC_GIVE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'DTAC_GIVE_PLUGIN_URL' ) ) {
	define( 'DTAC_GIVE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'DTAC_GIVE_PLUGIN_VERSION' ) ) {
	define( 'DTAC_GIVE_PLUGIN_VERSION', '3.0.0' );
}

/**
 * Whether the current PHP version meets the plugin requirement.
 *
 * Kept free of return types so this file can load on PHP older than 8.1.
 *
 * @since 3.0.0
 *
 * @return bool
 */
function dtac_give_php_version_is_supported() {
	return version_compare( PHP_VERSION, DTAC_GIVE_MIN_PHP, '>=' );
}

/**
 * Admin notice when PHP is too old.
 *
 * @since 3.0.0
 *
 * @return void
 */
function dtac_give_php_version_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$message = sprintf(
		/* translators: 1: current PHP version, 2: required PHP version */
		__( 'Donate to Access Content requires PHP %2$s or later. This site is running PHP %1$s. The plugin has been deactivated to avoid a fatal error. Please upgrade PHP, then activate the plugin again.', 'dtac-give' ),
		PHP_VERSION,
		DTAC_GIVE_MIN_PHP
	);

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html( $message )
	);
}

/**
 * Deactivate the plugin when PHP is below the required version.
 *
 * WordPress still loads an already-active plugin after an update, even when
 * Requires PHP is higher than the server. Bail before including 8.1+ files.
 *
 * @since 3.0.0
 *
 * @return void
 */
function dtac_give_deactivate_for_php_version() {
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	deactivate_plugins( DTAC_GIVE_PLUGIN_BASENAME );

	if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		unset( $_GET['activate'] );
	}
}

if ( ! dtac_give_php_version_is_supported() ) {
	add_action( 'admin_notices', 'dtac_give_php_version_notice' );
	add_action( 'admin_init', 'dtac_give_deactivate_for_php_version' );
	register_activation_hook( DTAC_GIVE_PLUGIN_FILE, 'dtac_give_deactivate_for_php_version' );
	return;
}

require_once DTAC_GIVE_PLUGIN_DIR . 'includes/class-donate-to-access-content-give-addon.php';

/**
 * Initialize main class instance.
 *
 * @since 2.0.0
 */
add_action( 'plugins_loaded', array( 'Donate_To_Access_Content_Give_Addon', 'dtac_give_instance' ) );
