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
 * Class with settings and html for Select Box
 */
class Select implements InterfaceFormFields {

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
	 * Option for select box.
	 *
	 * @since 2.0.0
	 *
	 * @param string $default_value Default value from DB or user.
	 *
	 * @return void
	 */
	public function select_options( string $default_value = '' ) : void {

		$selected = '';

		foreach ( $this->options['options'] as $key => $option ) {

			$selected = ( $key === $default_value ) ? ' selected' : '';

			echo '<option value="' . esc_html( $key ) . '" ' . $selected . '>' . esc_html( $option ) . '</option>';
		}
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
			<div class="select is-fullwidth">
				<select <?php $this->field_attributes( $this->options ); ?>>
					<?php $this->select_options( $this->input_default_select( $this->options ) ); ?>
				</select>
			</div>
			<p class="help mt-3"><?php echo $this->description( $this->options ); ?></p>
		</div>
		<?php
	}
}
