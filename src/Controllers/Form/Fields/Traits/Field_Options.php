<?php

/**
 * Common options used in all form field
 *
 * @package DTAC_Give
 *
 * @since 2.0.0
 */

namespace DTAC\Controllers\Form\Fields\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Field options
 *
 * @since 2.0.0
 */
trait Field_Options {


	/**
	 * Get field css classes
	 *
	 * @since 2.0.0
	 *
	 * @param array $options Field Options.
	 *
	 * @return string
	 */
	protected function css_classes( array $options ): string {

		if ( ! dtac_is_valid_array( $options, 'class', true ) ) {
			return '';
		}

		if ( dtac_is_valid_array( $options['class'] ) ) {

			$classes = array();

			foreach ( $options['class'] as $class_name ) {

				$classes[] = sanitize_html_class( $class_name );
			}

			return implode( ' ', $classes );
		}

		return sanitize_html_class( $options['class'] );
	}

	/**
	 * Get field name/css id
	 *
	 * @since 2.0.0
	 *
	 * @param array $options Field Options.
	 *
	 * @return string
	 */
	protected function field_name_id( array $options ): string {

		if ( ! dtac_is_valid_array( $options, 'id', true ) ) {
			return '';
		}

		return esc_html( $options['id'] );
	}

	/**
	 * Get field default value for inputs.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options Field Options.
	 *
	 * @return string
	 */
	protected function input_default( array $options ): string {

		if ( ! dtac_is_valid_array( $options, 'default', true ) ) {
			return '';
		}

		$db_default = dtac_give_get_settings( $this->field_name_id( $options ) );

		if ( is_scalar( $db_default ) && '' !== (string) $db_default ) {
			return esc_attr( (string) $db_default );
		}

		return isset( $options['default'] ) ? esc_attr( (string) $options['default'] ) : '';
	}

	/**
	 * Get field default value for select.
	 *
	 * @since 2.1.0
	 *
	 * @param array $options Field Options.
	 *
	 * @return string
	 */
	protected function input_default_select( array $options ): string {

		if ( ! dtac_is_valid_array( $options, 'default', true ) ) {
			return '';
		}

		$db_default = dtac_give_get_settings( $this->field_name_id( $options ) );

		if ( is_scalar( $db_default ) && '' !== (string) $db_default ) {
			return (string) $db_default;
		}

		return isset( $options['default'] ) ? (string) $options['default'] : '';
	}

	/**
	 * Get field default value for inputs for multi
	 * select types.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options Field Options.
	 *
	 * @return array
	 */
	protected function input_default_multiple( array $options ): array {

		if ( ! dtac_is_valid_array( $options, 'default', true ) ) {
			return array();
		}

		$db_default = dtac_give_get_settings( $this->field_name_id( $options ) );

		if ( ! empty( $db_default ) ) {
			return array_map( 'strval', (array) $db_default );
		}

		return isset( $options['default'] ) ? array_map( 'strval', (array) $options['default'] ) : array();
	}

	/**
	 * Get field default value for wysiwyg.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options Field Options.
	 *
	 * @return string
	 */
	protected function wysiwyg_default( array $options ): string {

		if ( ! dtac_is_valid_array( $options, 'default', true ) ) {
			return '';
		}

		return isset( $options['default'] ) ? wp_kses_post( (string) $options['default'] ) : '';
	}

	/**
	 * Get field description
	 *
	 * @since 2.0.0
	 *
	 * @param array $options Field Options.
	 *
	 * @return string
	 */
	protected function description( array $options ): string {

		if ( ! dtac_is_valid_array( $options, 'desc', true ) ) {
			return '';
		}

		return wp_kses( $options['desc'], dtac_allowed_html_tags() );
	}

	/**
	 * Get field attributes.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options Field Options.
	 *
	 * @return string
	 */
	protected function extra_attributes( array $options ): string {

		if ( ! dtac_is_valid_array( $options, 'attrs', true ) ) {
			return '';
		}

		if ( ! dtac_is_valid_array( $options['attrs'] ) ) {
			return '';
		}

		$attrs = '';

		foreach ( $options['attrs'] as $attr_key => $attr_value ) {

			$attrs .= esc_html( $attr_key ) . '="' . esc_html( $attr_value ) . '" ';
		}

		return $attrs;
	}

	/**
	 * Output field attributes.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options Options array.
	 *
	 * @return void
	 */
	protected function field_attributes( array $options ): void {

		$attributes  = ( '' !== $this->field_name_id( $options ) ) ? 'id="' . $this->field_name_id( $options ) . '" ' : '';
		$attributes .= ( '' !== $this->extra_attributes( $options ) ) ? $this->extra_attributes( $options ) : '';
		$attributes .= ( '' !== $this->css_classes( $options ) ) ? 'class="' . $this->css_classes( $options ) . '" ' : '';

		$field_type = isset( $options['type'] ) ? (string) $options['type'] : '';

		if ( in_array( $field_type, multiple_input_types(), true ) ) {

			$attributes .= ( '' !== $this->field_name_id( $options ) ) ? 'name="' . $this->field_name_id( $options ) . '[]"' : '';
		} else {

			$attributes .= ( '' !== $this->field_name_id( $options ) ) ? 'name="' . $this->field_name_id( $options ) . '"' : '';
		}

		echo $attributes; // phpcs:ignore
	}
}
