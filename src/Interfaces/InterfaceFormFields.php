<?php
/**
 * Interface for Form Fields
 *
 * @category   Form_Fields
 * @package    DTAC_Give
 * @subpackage DTAC_Give_Form_Fields_Interface
 * @author     ContriveItUp <contriveitup@gmail.com>
 * @license    GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link       https://github.com/contriveitup/give-add-on-donate-to-access-content
 *
 * @since 2.0.0
 */

namespace DTAC\Interfaces;

/**
 * Form Fields interface to include required
 * functions.
 *
 * @category Form_Fields
 * @package  DTAC_Give_Form_Fields_Interface
 * @author   ContriveItUp <contriveitup@gmail.com>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://github.com/contriveitup/give-add-on-donate-to-access-content
 *
 * @since 2.0.0
 */
interface InterfaceFormFields {


	/**
	 * Must have a HTML function.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function html();
}