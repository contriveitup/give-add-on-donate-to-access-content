<?php
/**
 * Add a WYSIWYG field.
 *
 * @package DTAC_Give
 *
 * @since 2.0.0
 */

namespace DTAC\Controllers\Form\Fields;

use DTAC\Interfaces\InterfaceFormFields;
use DTAC\Controllers\Form\Fields\Traits\Field_Options;

/**
 * Class with settings and html for WYSIWYG
 */
class Wysiwyg implements InterfaceFormFields {

	use Field_Options;

	/**
	 * Field Settings.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Class constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings Form fields options.
	 *
	 * @return void
	 */
	public function __construct( array $settings ) {
		$this->options = $settings;
	}

	/**
	 * Get editor content
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	private function wysiwyg_content() : string {

		$content = $this->wysiwyg_default( $this->options );

		return $content;
	}

	/**
	 * Get editor content
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	private function wysiwyg_id() : string {

		$id = $this->field_name_id( $this->options );

		return $id;
	}

	/**
	 * Get editor description
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	private function wysiwyg_description() : string {

		$desc = $this->description( $this->options );

		return '<p class="help">' . $desc . '</p>';
	}

	/**
	 * Output Field HTML.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function html() {

		wp_editor( $this->wysiwyg_content(), $this->wysiwyg_id() );

		echo $this->wysiwyg_description(); // phpcs:ignore
	}
}
