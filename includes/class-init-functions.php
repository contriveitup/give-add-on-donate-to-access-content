<?php
/**
 * Functions
 * 
 * All init functions can be found in this file
 * 
 * @since 1.0 
 */
 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Give_Donate_To_Access_Init_Functions' ) ):

	class Give_Donate_To_Access_Init_Functions {

		public function __construct() {

			// Add hidden fields to the donation form
			add_action( 'give_donation_form_top', array( $this, 'give_dta_form_fields' ) );

			//save required donation data for access to the content
			add_action( 'give_complete_donation', array( $this, 'save_give_dta_payment_meta' ) );

			add_action( 'wp', array( $this, 'give_dta_restrict_full' ) );
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
		public function give_dta_form_fields( $form_id ) {
			global $wp_query;

			$current_page_id = ''; $dta_true_field = '';

			$content = ( isset( $_GET['give_dta_content'] ) ? $_GET['give_dta_content'] : '' );

			$current_page_id = $wp_query->post->ID;

			//If a query string is set by the plugin or it is being viewed in a shortcode
			if( isset( $_GET['give_dta_content'] ) || $current_page_id != $form_id ) {

				if( '' != $content || $content >= 1 ) {
					$current_page_id = $content;	
				} else {
					$current_page_id = $wp_query->post->ID;
				}

				echo '<input type="hidden" name="give_dta_content" value="'. $current_page_id .'" />';
				echo '<input type="hidden" name="give_dta_process_donate_to_access" value="1" />';
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
		public function save_give_dta_payment_meta( $payment_id ) {

			if( ! isset( $_POST['give_dta_process_donate_to_access'] ) || $_POST['give_dta_process_donate_to_access'] != 1 ) {
				return;
			}

			if( ! isset( $_POST['give_dta_content'] ) || $_POST['give_dta_content'] == '' ) {
				return;
			}

			$access_content = $_POST['give_dta_content'];

			update_post_meta( $payment_id, '_give_dta_access_to_content', $access_content );
		}


		/**
		 * Plugin Frontend Init Functions
		 * 
		 * Functions hooked to WordPress init hook
		 * 
		 * @since 1.0
		 * 
		 * @return void
		 */
		public function give_dta_init_functions() {
			
		}


		/**
		 * Restict Full Content
		 * 
		 * This function will restirct the entire page, post, cats, etcc...
		 * according to the settings selected in the admin area.
		 * 
		 * @since 1.0
		 * 
		 * @return void redirection
		 */
		public function give_dta_restrict_full() {
			global $wp_query;

			$to_restrcit = Give_Donate_To_Access_Functions::give_dta_get_settings( 'give_dta_restrict_access_to' );

			$give = Give();

			if( is_array( $to_restrcit ) && ! empty( $to_restrcit ) ):

				$form_id = (int) Give_Donate_To_Access_Functions::give_dta_get_settings( 'give_dta_restrict_access_give_form_id' );

				if( ! $form_id ) {
					return;
				}

				//If pages 
				if( in_array( 'pages', $to_restrcit ) ) {
					$this->give_dta_restrict_pages( $form_id, $give );
				}

				//If posts 
				if( in_array( 'posts', $to_restrcit ) ) {
					$this->give_dta_restrict_posts( $form_id, $give );
				}

				//If categories 
				if( in_array( 'cats', $to_restrcit ) ) {
					$this->give_dta_restrict_cats( $form_id, $give );
				}

			endif; //End if array check

		}


		/**
		 * Generate Donation Form URL from form id with query args
		 * 
		 * @since 1.0
		 * @param $form_id int
		 * @param $current_page_id int
		 * 
		 * @return string  
		 */
		public function give_dta_donation_form_url( $form_id, $current_page_id ) {

			$form_url = get_permalink( $form_id );

			$form_url = esc_url( 
                            add_query_arg( 
                            	array(
							    	'give_dta_content' => $current_page_id,
								), 
								$form_url 
							) 
                    	);

			return $form_url;
		}


		/**
		 * Restrict Pages
		 * 
		 * @since 1.0
		 * @param $form_id int
		 * @param $give object
		 * 
		 * @return void
		 */
		public function give_dta_restrict_pages( $form_id, $give ) {
			global $wp_query;

			$pages = Give_Donate_To_Access_Functions::give_dta_get_settings( 'give_dta_restrict_access_to_pages' );

			$pages = ( '' != $pages ? explode( ',', $pages ) : array() );

			$current_page = $wp_query->post->ID;

			if( ! empty( $pages ) ) {

				if( is_page( $pages ) ) {

					$donor = Give_Donate_To_Access_Functions::give_dta_get_donor( $give );

					$is_restricted = Give_Donate_To_Access_Functions::give_dta_is_donor_restricted( $donor, $current_page );

					if( $is_restricted ) {
						wp_safe_redirect( $this->give_dta_donation_form_url( $form_id, $current_page ) );
						exit;	
					}
				}// End if is_page check
			}// End if empty check
		}


		/**
		 * Restrict Posts
		 * 
		 * @since 1.0
		 * @param $form_id int
		 * @param $give object
		 * 
		 * @return void
		 */
		public function give_dta_restrict_posts( $form_id, $give ) {
			global $wp_query;

			$posts = Give_Donate_To_Access_Functions::give_dta_get_settings( 'give_dta_restrict_access_to_posts' );

			$posts = ( '' != $posts ? explode( ',', $posts ) : array() );

			$current_post = $wp_query->post->ID;

			if( ! empty( $posts ) ) {

				if( is_single( $posts ) ) {

					$donor = Give_Donate_To_Access_Functions::give_dta_get_donor( $give );

					$is_restricted = Give_Donate_To_Access_Functions::give_dta_is_donor_restricted( $donor, $current_post );

					if( $is_restricted ) {
						wp_safe_redirect( $this->give_dta_donation_form_url( $form_id, $current_post ) );
						exit;	
					}
				}// End if is_page check
			}// End if empty check
		}


		/**
		 * Restrict Categories
		 * 
		 * @since 1.0
		 * @param $form_id int
		 * @param $give object
		 * 
		 * @return void
		 */
		public function give_dta_restrict_cats( $form_id, $give ) {
			global $wp_query;

			$cats = Give_Donate_To_Access_Functions::give_dta_get_settings( 'give_dta_restrict_access_to_cats' );

			$cats = ( '' != $cats ? explode( ',', $cats ) : array() );

			$current_cat = $wp_query->post->ID;

			if( ! empty( $cats ) ) {

				if( is_category( $cats ) ) {

					$donor = Give_Donate_To_Access_Functions::give_dta_get_donor( $give );

					$is_restricted = Give_Donate_To_Access_Functions::give_dta_is_donor_restricted( $donor, $current_cat );

					if( $is_restricted ) {
						wp_safe_redirect( $this->give_dta_donation_form_url( $form_id, $current_cat ) );
						exit;	
					}
				}// End if is_page check
			}// End if empty check
		}

	} //End class Give_Donate_To_Access_Init_Functions

endif; //End if class_exists check

return new Give_Donate_To_Access_Init_Functions();