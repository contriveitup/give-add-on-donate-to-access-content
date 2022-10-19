<?php
/**
 * Form Settings Interface
 *
 * @category   Form_Settings
 * @package    DTAC_Give
 * @subpackage DTAC_Give_Form_Settings_Interface
 * @author     ContriveItUp <contriveitup@gmail.com>
 * @license    GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link       https://github.com/contriveitup/give-add-on-donate-to-access-content
 *
 * @since 2.0.0
 */

namespace DTAC\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface for Form Settings
 *
 * @category Form_Settings
 * @package  DTAC_Give_Form_Settings_Interface
 * @author   ContriveItUp <contriveitup@gmail.com>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://github.com/contriveitup/give-add-on-donate-to-access-content
 *
 * @since 2.0.0
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