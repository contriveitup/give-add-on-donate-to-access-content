<?php
/**
 * Process a form
 *
 * @package DTAC_Give
 *
 * @since 2.0.0
 */

namespace DTAC\Controllers\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Process the form
 */
class Process {

	/**
	 * Name for admin nonce.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private $admin_nonce_name = 'save_dtac_settings';

	/**
	 * Value for admin nonce.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private $admin_nonce_value = 'cip_dtac_give_admin';

	/**
	 * Class constructor.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		if ( dtac_is_valid_array( $_POST, 'dtac_save_admin_settings', true ) && $this->nonce_validated() ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$this->process_form( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
	}

	/**
	 * Validate WP Nonce
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	private function nonce_validated(): bool {

		return (bool) check_admin_referer( $this->admin_nonce_name, $this->admin_nonce_value );
	}

	/**
	 * Process Form Data
	 *
	 * @param array $post_data $_POST data.
	 *
	 * @return void
	 */
	private function process_form( array $post_data ) {

		unset( $post_data['_wp_http_referer'] );
		unset( $post_data[ $this->admin_nonce_value ] );
		unset( $post_data['dtac_save_admin_settings'] );

		foreach ( $post_data as $key => $value ) {
			unset( $value );

			if ( ! is_string( $key ) || 0 === strpos( $key, '_' ) ) {
				unset( $post_data[ $key ] );
			}
		}

		$post_data = dtac_give_sanitize_settings( $post_data );

		update_option( 'dtac_give_settings', $post_data );

		if ( function_exists( 'add_settings_error' ) ) {
			add_settings_error(
				'dtac_give_settings',
				'dtac_give_settings_updated',
				__( 'Settings saved.', 'dtac-give' ),
				'updated'
			);
		}
	}
}
