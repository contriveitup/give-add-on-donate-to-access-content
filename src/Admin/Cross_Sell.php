<?php

/**
 * Settings-page cross-sell for themediaable plugins.
 *
 * @package DTAC_Give
 *
 * @since 3.0.0
 */

namespace DTAC\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Recommended plugin card on the DTAC settings page.
 *
 * @since 3.0.0
 */
class Cross_Sell {


	/**
	 * WordPress.org slug for Signals Dispatch for WooCommerce.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private const SIGNALS_SLUG = 'signals-dispatch-for-woocommerce';

	/**
	 * Render the Signals Dispatch recommendation.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function render(): void {

		$plugin       = self::signals_plugin();
		$is_active    = self::is_plugin_active( $plugin['file'] );
		$is_installed = self::is_plugin_installed( $plugin['file'] );

		echo '<div class="dtac-cross-sell">';
		echo '<p class="dtac-cross-sell__eyebrow">' . esc_html__( 'From themediaable', 'dtac-give' ) . '</p>';
		echo '<h2 class="dtac-card__title">' . esc_html( $plugin['name'] ) . '</h2>';
		echo '<p class="dtac-card__intro">' . esc_html( $plugin['blurb'] ) . '</p>';

		echo '<ul class="dtac-cross-sell__features">';
		foreach ( $plugin['features'] as $feature ) {
			echo '<li>' . esc_html( $feature ) . '</li>';
		}
		echo '</ul>';

		echo '<div class="dtac-cross-sell__actions">';

		if ( $is_active ) {
			echo '<span class="dtac-cross-sell__status">' . esc_html__( 'Installed and active', 'dtac-give' ) . '</span>';
			printf(
				'<a class="button" href="%s">%s</a>',
				esc_url( $plugin['url'] ),
				esc_html__( 'View on WordPress.org', 'dtac-give' )
			);
		} elseif ( $is_installed ) {
			printf(
				'<a class="button button-primary" href="%s">%s</a>',
				esc_url( self::activate_url( $plugin['file'] ) ),
				esc_html__( 'Activate plugin', 'dtac-give' )
			);
			printf(
				'<a class="button" href="%s">%s</a>',
				esc_url( $plugin['url'] ),
				esc_html__( 'Learn more', 'dtac-give' )
			);
		} else {
			printf(
				'<a class="button button-primary" href="%s">%s</a>',
				esc_url( self::install_url( $plugin['slug'] ) ),
				esc_html__( 'Install now', 'dtac-give' )
			);
			printf(
				'<a class="button" href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
				esc_url( $plugin['url'] ),
				esc_html__( 'View details', 'dtac-give' )
			);
		}

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Cross-sell copy and links for Signals Dispatch.
	 *
	 * @since 3.0.0
	 *
	 * @return array{name:string,slug:string,file:string,url:string,blurb:string,features:array<int,string>}
	 */
	private static function signals_plugin(): array {

		return array(
			'name'     => __( 'Signals Dispatch for WooCommerce', 'dtac-give' ),
			'slug'     => self::SIGNALS_SLUG,
			'file'     => self::SIGNALS_SLUG . '/signals-dispatch-for-woocommerce.php',
			'url'      => 'https://wordpress.org/plugins/signals-dispatch-for-woocommerce/',
			'blurb'    => __( 'Send automated WhatsApp order notifications from WooCommerce with templates, delivery tracking, and checkout consent.', 'dtac-give' ),
			'features' => array(
				__( 'WhatsApp Business Cloud API order alerts', 'dtac-give' ),
				__( 'Template messages with order variables', 'dtac-give' ),
				__( 'Guided setup wizard and delivery logs', 'dtac-give' ),
			),
		);
	}

	/**
	 * Whether a plugin file is installed.
	 *
	 * @since 3.0.0
	 *
	 * @param string $plugin_file Plugin basename.
	 *
	 * @return bool
	 */
	private static function is_plugin_installed( string $plugin_file ): bool {

		if ( ! function_exists( 'get_plugins' ) ) {
			$plugin_php = ABSPATH . 'wp-admin/includes/plugin.php';

			if ( is_readable( $plugin_php ) ) {
				require_once $plugin_php;
			}
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			return false;
		}

		$plugins = get_plugins();

		return isset( $plugins[ $plugin_file ] );
	}

	/**
	 * Whether a plugin file is active.
	 *
	 * @since 3.0.0
	 *
	 * @param string $plugin_file Plugin basename.
	 *
	 * @return bool
	 */
	private static function is_plugin_active( string $plugin_file ): bool {

		if ( function_exists( 'is_plugin_active' ) ) {
			return (bool) is_plugin_active( $plugin_file );
		}

		if ( ! function_exists( 'get_option' ) ) {
			return false;
		}

		$active = get_option( 'active_plugins', array() );

		return is_array( $active ) && in_array( $plugin_file, $active, true );
	}

	/**
	 * Admin install URL for a WordPress.org plugin slug.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return string
	 */
	private static function install_url( string $slug ): string {

		if ( ! function_exists( 'self_admin_url' ) || ! function_exists( 'wp_nonce_url' ) ) {
			return 'https://wordpress.org/plugins/' . $slug . '/';
		}

		return wp_nonce_url(
			self_admin_url( 'update.php?action=install-plugin&plugin=' . rawurlencode( $slug ) ),
			'install-plugin_' . $slug
		);
	}

	/**
	 * Admin activation URL for an installed plugin.
	 *
	 * @since 3.0.0
	 *
	 * @param string $plugin_file Plugin basename.
	 *
	 * @return string
	 */
	private static function activate_url( string $plugin_file ): string {

		if ( ! function_exists( 'self_admin_url' ) || ! function_exists( 'wp_nonce_url' ) ) {
			return admin_url( 'plugins.php' );
		}

		return wp_nonce_url(
			self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin_file ) ),
			'activate-plugin_' . $plugin_file
		);
	}
}
