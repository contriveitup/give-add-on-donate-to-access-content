<?php
/**
 * Functions
 * 
 * All custom functions can be found in this file
 * 
 * @since 1.0 
 */
 

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $give_dta_functions;

if( ! class_exists( 'Give_Donate_To_Access_Functions' ) ):

	class Give_Donate_To_Access_Functions {

		public function __construct() {			

		}


		/**
		 * Get settings for the plugin
		 * 
		 * @since 1.0
		 * 
		 * @return array|mix Settings 
		 */
		public static function give_dta_get_settings( $key = '' ) {

			$settings = array();

			$settings = get_option( 'give_dta_settings' );

			if( ! empty( $settings ) ) {

				if( '' != $key ) {
					$settings = $settings[ $key ];
				} else {
					$settings = (array) apply_filters( 'give_dta_get_settings', $settings );
				}
			}

			return $settings;
		}


		/**
		 * Check the status of donation and if a user should be allowed to access
		 * the content
		 * 
		 * @since 1.0
		 * 
		 * @param $give 				Core Give class object
		 * @param $content 				Content for the shortcode to output
		 * @param $restrict_content		The restricted content in case the donor has not made a donation
		 * 
		 * @return string Result
		 */
		public function give_dta_check_access( $give, $content, $restrict_content = '' ) {

			global $wp_query;

			//Initialize the varibales
			$id = ''; $field = ''; $value = ''; $donor = array(); $current_page_id = 0; $access_content = array(); $result = '';

			$result = $restrict_content;

			$current_page_id = $wp_query->post->ID;

			//Get Donor
			$donor = $this->give_dta_get_donor( $give );

			$is_restricted = $this->give_dta_is_donor_restricted( $donor, $current_page_id );

			if( $is_restricted ) {
				$result = $restrict_content;
			} else {
				$result = $content;
			}

			return $result;
		}


		/**
		 * Get the donor by id or email
		 * 
		 * Retrive a donor by id or email and according by it's login state
		 * 
		 * @since 1.0
		 * @param $give Give class object
		 * 
		 * @return array
		 */
		public static function give_dta_get_donor( $give ) {

			$donor = array(); $result = array(); $id = ''; $value = '';

			if ( is_user_logged_in() ) {
				$id = get_current_user_id();
			} elseif ( $give->session->get_session_expiration() !== false ) {
				// Session active?
				$id = $give->session->get( 'give_email' );
			}

			if( is_int( $id ) ) {
				$field 	= 'id';
				$value 	= $id;
			} else {
				$field = 'email';
				$value = $id;
			}

			//Get Donor
			$donor = $give->donors->get_donor_by( $field, $value );

			//If donor exists
			if( ! empty( $donor ) || ! is_null( $donor ) ) {
				$result = $donor;
			}

			return $result;
		}	


		/**
		 * Check if content is restrcied for donor or not
		 * 
		 * @since 1.0
		 * @param $donor array Donor user object
		 * @param $content string|int Content page, post id or slug
		 * 
		 * @return bool
		 */
		public static function give_dta_is_donor_restricted( $donor, $content ) {

			$is_restricted = true;

			//If donor exists
			if( ! empty( $donor ) ) {

				$payment_ids = $donor->payment_ids;

				$payment_ids = explode( ',', $payment_ids );

				//If there is a payment ID
				if( ! empty( $payment_ids ) ) :

					foreach ( $payment_ids as $payment_id ) {
						//Get content ID's to acess
						$access_content[] = get_post_meta( $payment_id, '_give_dta_access_to_content', true );
					}

					if( in_array( $content, $access_content ) ) {
						$is_restricted = false;
					}

				endif;
			}

			return $is_restricted;
		}

	} //End class Give_Donate_To_Access_Functions

endif; //End if class_exists check

$give_dta_functions = new Give_Donate_To_Access_Functions();