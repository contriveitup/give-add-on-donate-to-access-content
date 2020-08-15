<?php
/**
 * Form Settings Interface
 *
 * @package DTAC_Give
 *
 * @since 2.0.0
 */

namespace DTAC\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface for Form Settings
 */
interface InterfaceFormSettings {

	/**
	 * Generate a settings array for the form
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function dtac_form_settings() : array;
}
