<?php
/**
 * Enqueue Admin & Frontend JS & CSS Scripts
 *
 * @since  1.0.0
 */

namespace DTAC\Setup;

defined( 'ABSPATH' ) || exit;

/**
 * Add scripts to WordPress.
 *
 * @since 1.0.0
 */
class Enqueue_Scripts {

	/**
	 * [__construct]
	 *
	 * Class Constructor.
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'dtac_give_admin_scripts' ) );
	}

	/**
	 * Add admin scripts.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function dtac_give_admin_scripts() : void {

		if ( ! is_dtac_plugin_settings_page() ) {
			return;
		}

		$script_path 	= DTAC_GIVE_PLUGIN_URL . 'assets/js/'; // Path to JS folder.
		$style_path 	= DTAC_GIVE_PLUGIN_URL . 'assets/css/'; // Path to CSS folder.

		/**
		 * Bulma CSS
		 */
		wp_register_style( 'dtac-give-bulma', $style_path . 'bulma.css', array(), DTAC_GIVE_PLUGIN_VERSION );
		wp_enqueue_style( 'dtac-give-bulma' );

		/**
		 * Plugin Select 2 CSS Styles
		 */
		wp_register_style( 'dtac-give-admin-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), DTAC_GIVE_PLUGIN_VERSION );
		wp_enqueue_style( 'dtac-give-admin-select2' );

		/**
		 * Plugin CSS Styles
		 */
		wp_register_style( 'dtac-give-admin', $style_path . 'style.css', array(), DTAC_GIVE_PLUGIN_VERSION );
		wp_enqueue_style( 'dtac-give-admin' );

		/**
		 * Plugin Select2
		 */
		wp_register_script( 'dtac-give-admin-select-2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array( 'jquery' ), DTAC_GIVE_PLUGIN_VERSION, true );
		wp_enqueue_script( 'dtac-give-admin-select-2-js' );

		/**
		 * Plugin JS Scripts
		 */
		wp_register_script( 'dtac-give-admin-js', $script_path . 'main.js', array( 'jquery' ), DTAC_GIVE_PLUGIN_VERSION, true );
		wp_enqueue_script( 'dtac-give-admin-js' );
	}

} // End class Enqueue_Scripts.
