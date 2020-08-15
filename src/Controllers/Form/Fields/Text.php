<?php
/**
 * Add a select input field.
 *
 * @package DTAC_Give
 *
 * @since 2.0.0
 */

namespace DTAC\Controllers\Form\Fields;

use DTAC\Interfaces\InterfaceFormFields;
use DTAC\Controllers\Form\Fields\Traits\Field_Options;

defined( 'ABSPATH' ) || exit;

/**
 * Class with settings and html for Text Box
 */
class Text implements InterfaceFormFields {

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
	 * Value for the input box
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	private function input_value() {

		echo 'value="' . $this->input_default( $this->options ) . '"'; // phpcs:ignore
	}

	/**
	 * Output Field HTML.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function html() {
		?>
		<div class="control">
			<input type="text" <?php $this->field_attributes( $this->options ); ?> <?php $this->input_value(); ?> />
			<p class="help mt-3"><?php echo $this->description( $this->options ); ?></p>
		</div>
		<?php
	}
}
