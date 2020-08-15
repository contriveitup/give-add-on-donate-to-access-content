<?php
/**
 * Load Form
 *
 * @package DTAC_Give
 *
 * @since 2.0.0
 */

namespace DTAC\Controllers\Form;

use DTAC\Interfaces\InterfaceFormSettings;
use DTAC\Controllers\Form\Form_Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Form
 *
 * @since 2.0.0
 */
class Form extends Form_Fields {

	/**
	 * Capture form settings.
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	protected $form_settings;

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
	 * @param InterfaceFormSettings $form_settings Form Settings.
	 *
	 * @return void
	 */
	public function __construct( InterfaceFormSettings $form_settings ) {

		$this->form_settings = $form_settings;
	}

	/**
	 * Open HTML <form> tag.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	private function start_form_tag() : void {

		echo '<form action="" method="post">';
	}

	/**
	 * Close HTML <form> tag.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	private function close_form_tag() : void {

		echo '</form>';
	}

	/**
	 * Form fields parent wrapper open.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	private function form_fields_parent_container_open() : void {

		echo '<fieldset>';
	}

	/**
	 * Form fields parent wrapper close.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	private function form_fields_parent_container_close() : void {

		echo '</fieldset>';
	}

	/**
	 * Form Field individual wrapper open. Ideally this would come in
	 * settings loop for fields.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	private function form_field_wrapper_open() : void {

		echo '<div class="field dtac-form-field">';
	}

	/**
	 * Form Field individual wrapper close. Ideally this would come in
	 * settings loop for fields.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	private function form_field_wrapper_close() : void {

		echo '</div>';
	}

	/**
	 * Form Field label
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	private function form_field_label( $label ) : void {

		echo '<label class="label">' . esc_html( $label ) . '</label>';
	}

	/**
	 * Display form field
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings Array of settings.
	 *
	 * @return void
	 */
	private function form_field( array $settings ) : void {

		$this->output_field( $settings )->html();
	}

	/**
	 * Form Submit Button.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	protected function submit_button() : void {

		$submit_button = '<button type="submit" class="button is-primary mt-5" name="dtac_save_admin_settings">' . esc_html__( 'Save Changes', 'dtac-give' ) . '</button>';

		echo $submit_button; // phpcs:ignore
	}

	/**
	 * Admin form none field.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function form_nonce_field() : void {

		wp_nonce_field( $this->admin_nonce_name, $this->admin_nonce_value );
	}

	/**
	 * Output Form Fields
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function output() {

		$settings_array = (array) $this->form_settings->dtac_form_settings();

		$this->start_form_tag();

		$this->form_nonce_field();

		$this->form_fields_parent_container_open();

		foreach ( $settings_array as $settings ) {
			$this->form_field_wrapper_open();
			$this->form_field_label( $settings['name'] );
			$this->form_field( $settings );
			$this->form_field_wrapper_close();
		}

		$this->submit_button();

		$this->form_fields_parent_container_close();

		$this->close_form_tag();
	}
}
