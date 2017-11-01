<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class containing hooks and filters used on the frontend
 * 
 * @since  1.0
 */
class Give_Donate_To_Access_Content_Hooks extends Give_Donate_To_Access_Content_Functions {

	public function __construct() {

		// Add hidden fields to the donation form
		add_action( 'give_donation_form_top', array( $this, 'give_dtac_form_fields' ) );

		//save required donation data for access to the content
		add_action( 'give_complete_donation', array( $this, 'save_give_dtac_payment_meta' ) );
	}

	/**
	 * Donation Form Fields
	 * 
	 * Add required hidden form fields to the donation form
	 * 
	 * @since 1.0
	 * 
	 * @return output form fields
	 */ 
	public function give_dtac_form_fields( $form_id ) {
		global $wp_query;

		$current_page_id = ''; $dta_true_field = '';

		$content = ( isset( $_GET['give_dtac_content'] ) ? $_GET['give_dtac_content'] : '' );

		$current_page_id = $wp_query->post->ID;

		//If a query string is set by the plugin or it is being viewed in a shortcode
		if( isset( $_GET['give_dtac_content'] ) || $current_page_id != $form_id ) {

			if( '' != $content || $content >= 1 ) {
				$current_page_id = $content;	
			} else {
				$current_page_id = $wp_query->post->ID;
			}

			echo '<input type="hidden" name="give_dtac_content" value="'. $current_page_id .'" />';
			echo '<input type="hidden" name="give_dtac_process_donate_to_access" value="1" />';
		}
	}

	
	/**
	 * Save Donation Data
	 * 
	 * Save the required donation data upon donation completion.
	 * 
	 * @since 1.0
	 * 
	 * @return void
	 */
	public function save_give_dtac_payment_meta( $payment_id ) {

		if( ! isset( $_POST['give_dtac_process_donate_to_access'] ) || $_POST['give_dtac_process_donate_to_access'] != 1 ) {
			return;
		}

		if( ! isset( $_POST['give_dtac_content'] ) || $_POST['give_dtac_content'] == '' ) {
			return;
		}

		$access_content = $_POST['give_dtac_content'];

		update_post_meta( $payment_id, '_give_dtac_access_to_content', $access_content );
	}

} //End class Give_Donate_To_Access_Content_Hooks

new Give_Donate_To_Access_Content_Hooks();