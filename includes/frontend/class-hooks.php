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
class Donate_To_Access_Content_Give_Hooks extends Donate_To_Access_Content_Give_Functions {

	public function __construct() {

		// Add hidden fields to the donation form
		add_action( 'give_donation_form_top', array( $this, 'dtac_give_form_fields' ) );

		//save required donation data for access to the content
		add_action( 'give_complete_donation', array( $this, 'save_dtac_give_payment_meta' ) );
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
	public function dtac_give_form_fields( $form_id ) {
		global $wp_query;

		$current_page_id = ''; $dta_true_field = '';

		$content = ( isset( $_GET['dtac_give_content'] ) ? $_GET['dtac_give_content'] : '' );

		$current_page_id = $wp_query->post->ID;

		//If a query string is set by the plugin or it is being viewed in a shortcode
		if( isset( $_GET['dtac_give_content'] ) || $current_page_id != $form_id ) {

			if( '' != $content || $content >= 1 ) {
				$current_page_id = $content;	
			} else {
				$current_page_id = $wp_query->post->ID;
			}

			echo '<input type="hidden" name="dtac_give_content" value="'. $current_page_id .'" />';
			echo '<input type="hidden" name="dtac_give_process_donate_to_access" value="1" />';
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
	public function save_dtac_give_payment_meta( $payment_id ) {

		if( ! isset( $_POST['dtac_give_process_donate_to_access'] ) || $_POST['dtac_give_process_donate_to_access'] != 1 ) {
			return;
		}

		if( ! isset( $_POST['dtac_give_content'] ) || $_POST['dtac_give_content'] == '' ) {
			return;
		}

		$access_content = $_POST['dtac_give_content'];

		//If access to complete website is requested
		if( 'site' == $access_content ) {

			//Get donor's id from payment id
			$donor_id = dtac_give_get_donor_by_payment_id( $payment_id );

			//Add website access rights to donor meta table
			DTAC_GIVE()->give->donor_meta->add_meta( $donor_id, 'give_dtca_access_website', 'yes' );

			//update the access rights in the payments meta table as well
			update_post_meta( $payment_id, '_dtac_give_access_to_content', $access_content );

		} else {

			update_post_meta( $payment_id, '_dtac_give_access_to_content', $access_content );	
		}
	}

} //End class Donate_To_Access_Content_Give_Hooks

new Donate_To_Access_Content_Give_Hooks();