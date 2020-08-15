<?php
/**
 * Generate Form Fields
 *
 * @package DTAC_Give
 *
 * @since 2.0.0
 */

namespace DTAC\Controllers\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Add form fiels to the form class
 *
 * @since 2.0.0
 */
abstract class Form_Fields {

	/**
	 * Display a form field
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings Type of field.
	 *
	 * @return object
	 */
	protected function output_field( array $settings ) : object {

		$class_name = 'DTAC\Controllers\Form\Fields\\' . trim( ucwords( str_replace( '-', '_', $settings['type'] ) ) );

		if ( class_exists( $class_name ) ) {
			return new $class_name( $settings );
		}

		return new \DTAC\Controllers\Form\Fields\Text( $settings );
	}

	/**
	 * Submit Button for the form
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	abstract protected function submit_button() : void;

	/**
	 * Nonce Field for the form
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	abstract protected function form_nonce_field() : void;
}
