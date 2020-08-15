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

		if ( dtac_is_valid_array( $_POST, 'dtac_save_admin_settings', true ) && $this->nonce_validated() ) { // phpcs:ignore
			$this->process_form( $_POST ); // phpcs:ignore
		}
	}

	/**
	 * Validate WP Nonce
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	private function nonce_validated() : bool {

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

		update_option( 'dtac_give_settings', $post_data );
	}
}
