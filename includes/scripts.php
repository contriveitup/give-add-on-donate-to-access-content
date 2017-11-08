<?php
/**
 * Enqueue Admin & Frontend JS & CSS Scripts
 * 
 * @since  1.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Donate_To_Access_Content_Give_Scripts {

	/**
	 * [__construct]
	 * 
	 * Class Constructor
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'dtac_give_admin_scripts' ) );
	}


	public function dtac_give_admin_scripts() {

		$script_path 	= DTAC_GIVE_PLUGIN_URL . 'assets/js/'; //Path to JS folder
		$style_path 	= DTAC_GIVE_PLUGIN_URL . 'assets/css/'; // Path to CSS folder

		/**
		 * If plugin's settings page is loaded or displayed
		 */
		if( is_dtac_plugin_settings_page() ) {

			//JS Scripts
			wp_register_script( 'dtac-give-select2-js', $script_path . 'select2.min.js', array( 'jquery' ), '4.0.5', true );
			wp_enqueue_script( 'dtac-give-select2-js' );	

			//CSS Scripts	
			wp_register_style( 'dtac-give-select2', $style_path . 'select2.min.css' );
			wp_enqueue_style( 'dtac-give-select2' );

		}// End if is_dtac_plugin_settings_page() check			

		/**
		 * JS Scripts
		 */
		wp_register_script( 'dtac-give-admin-js', $script_path . 'admin.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'dtac-give-admin-js' );			

		/**
		 * CSS Styles
		 */
		wp_register_style( 'dtac-give-admin', $style_path . 'admin.css' );
		wp_enqueue_style( 'dtac-give-admin' );
	}

}// End class Donate_To_Access_Content_Give_Scripts

new Donate_To_Access_Content_Give_Scripts();