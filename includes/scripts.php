<?php
/**
 * Enqueue Admin & Frontend JS & CSS Scripts
 * 
 * @since  1.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Give_Donate_To_Access_Content_Scripts {

	/**
	 * [__construct]
	 * 
	 * Class Constructor
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'give_dtac_admin_scripts' ) );
	}


	public function give_dtac_admin_scripts() {

		$script_path 	= GIVE_DTAC_PLUGIN_URL . 'assets/js/'; //Path to JS folder
		$style_path 	= GIVE_DTAC_PLUGIN_URL . 'assets/css/'; // Path to CSS folder

		/**
		 * If plugin's settings page is loaded or displayed
		 */
		if( is_dtac_plugin_settings_page() ) {

			//JS Scripts
			wp_register_script( 'give-dtac-select2-js', $script_path . 'select2.min.js', array( 'jquery' ), '4.0.5', true );
			wp_enqueue_script( 'give-dtac-select2-js' );	

			//CSS Scripts	
			wp_register_style( 'give-dtac-select2', $style_path . 'select2.min.css' );
			wp_enqueue_style( 'give-dtac-select2' );

		}// End if is_dtac_plugin_settings_page() check			

		/**
		 * JS Scripts
		 */
		wp_register_script( 'give-dtac-admin-js', $script_path . 'admin.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'give-dtac-admin-js' );			

		/**
		 * CSS Styles
		 */
		wp_register_style( 'give-dtac-admin', $style_path . 'admin.css' );
		wp_enqueue_style( 'give-dtac-admin' );
	}

}// End class Give_Donate_To_Access_Content_Scripts

new Give_Donate_To_Access_Content_Scripts();