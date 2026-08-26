<?php

/**
 * Add a multi select input field.
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
 * (Multiple)
 */
class Multi_Select implements InterfaceFormFields {


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
	 * @param array $default_value Default value from DB or user.
	 *
	 * @return void
	 */
	public function select_options( array $default_value = array() ): void {

		foreach ( $this->options['options'] as $key => $option ) {

			$key = (string) $key;

			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $key ),
				selected( in_array( $key, $default_value, true ), true, false ),
				esc_html( $option )
			);
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
				<select <?php $this->field_attributes( $this->options ); ?> multiple="multiple">
					<?php $this->select_options( $this->input_default_multiple( $this->options ) ); ?>
				</select>
			</div>
			<p class="help mt-3"><?php echo wp_kses_post( $this->description( $this->options ) ); ?></p>
		</div>
		<?php
	}
}
