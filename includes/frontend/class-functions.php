<?php
/**
 * Functions for frontend operations
 * 
 * @since 1.0 
 */
 

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Give_Donate_To_Access_Functions' ) ):

	class Give_Donate_To_Access_Content_Functions {


		/**
		 * [__construct]
		 * 
		 * Class constructor
		 * 
		 * @since  1.0
		 */
		public function __construct() {			
			
		}


		/**
		 * Check the status of donation and if a user should be allowed to access
		 * the content
		 * 
		 * @since 1.0
		 *
		 * @param $content 				Content for the shortcode to output
		 * @param $restrict_content		The restricted content in case the donor has not made a donation
		 * 
		 * @return string Result
		 */
		public function give_dtac_check_access( $content, $restrict_content = '' ) {

			global $wp_query;

			//Initialize the varibales
			$id = ''; $field = ''; $value = ''; $donor = array(); $current_page_id = 0; $access_content = array(); $result = '';

			$result = $restrict_content;

			$current_page_id = $wp_query->post->ID;

			//Get Donor
			$donor = give_dtac_get_donor();

			$is_restricted = $this->give_dtac_is_donor_restricted( $donor, $current_page_id );

			if( $is_restricted ) {
				$result = $restrict_content;
			} else {
				$result = $content;
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
		public static function give_dtac_is_donor_restricted( $donor, $content ) {

			$is_restricted = true;

			//If donor exists
			if( ! empty( $donor ) ) {

				$payment_ids = $donor->payment_ids;

				$payment_ids = explode( ',', $payment_ids );

				//If there is a payment ID
				if( ! empty( $payment_ids ) ) :

					foreach ( $payment_ids as $payment_id ) {
						//Get content ID's to acess
						$access_content[] = get_post_meta( $payment_id, '_give_dtac_access_to_content', true );
					}

					if( in_array( $content, $access_content ) ) {
						$is_restricted = false;
					}

				endif;
			}

			return $is_restricted;
		}


		/**
		 * Restrict Pages
		 * 
		 * @since 1.0
		 * @param $form_id int
		 * 
		 * @return void
		 */
		public function give_dtac_restrict_pages( $form_id ) {
			global $wp_query;

			$pages = give_dtac_get_settings( 'give_dtac_restrict_access_to_pages' );

			$pages = ( ! empty( $pages ) ? $pages : array() );

			$current_page = $wp_query->post->ID;

			if( ! empty( $pages ) ) {

				if( is_page( $pages ) ) {

					$donor = give_dtac_get_donor();

					$is_restricted = $this->give_dtac_is_donor_restricted( $donor, $current_page );

					if( $is_restricted ) {
						wp_safe_redirect( give_dtac_donation_form_url( $form_id, $current_page ) );
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
		 * 
		 * @return void
		 */
		public function give_dtac_restrict_posts( $form_id ) {
			global $wp_query;

			$posts = give_dtac_get_settings( 'give_dtac_restrict_access_to_posts' );

			$posts = ( ! empty( $posts ) ? $posts : array() );

			$current_post = $wp_query->post->ID;

			if( ! empty( $posts ) ) {

				if( is_single( $posts ) ) {

					$donor = give_dtac_get_donor();

					$is_restricted = $this->give_dtac_is_donor_restricted( $donor, $current_post );

					if( $is_restricted ) {
						wp_safe_redirect( give_dtac_donation_form_url( $form_id, $current_post ) );
						exit;	
					}
				}// End if is_page check
			}// End if empty check
		}


		/**
		 * Restrict Categories
		 * 
		 * Restrict categories archive page or if a single post is being displayed and 
		 * the post are in any of the categories selected from the settings
		 * 
		 * @since 1.0
		 * 
		 * @param $form_id int Donation Form ID
		 * 
		 * @return void
		 */
		public function give_dtac_restrict_cats( $form_id ) {
			global $wp_query;

			$cats = give_dtac_get_settings( 'give_dtac_restrict_access_to_cats' );

			$cats = ( ! empty ( $cats ) ? $cats: array() );

			$category 		= get_queried_object();
			$current_cat 	= $category->term_id;

			if( ! empty( $cats ) ) {

				if( is_category( $cats ) || ( is_single() && in_category( $cats ) ) ) {

					$donor = give_dtac_get_donor();

					$is_restricted = $this->give_dtac_is_donor_restricted( $donor, $current_cat );

					if( $is_restricted ) {
						wp_safe_redirect( give_dtac_donation_form_url( $form_id, $current_cat ) );
						exit;	
					}
				}// End if is_page check
			}// End if empty check
		}


		/**
		 * [give_dtac_restrict_cpt]
		 * 
		 * Restrict a custom post types
		 * 
		 * @since  1.0
		 * 
		 * @param  [int] $form_id [Donation Form ID for redirection]
		 * 
		 * @return [void]          
		 */
		public function give_dtac_restrict_cpt( $form_id ) {
			global $wp_query;

			$cpts = give_dtac_get_settings( 'give_dtac_restrict_access_to_cpt' );

			$cpts = ( ! empty( $cpts ) ? $cpts : array() );

			$current_cpt = get_post_type();

			if( ! empty( $cpts ) ) {

				if( is_singular( $cpts ) ) {

					$donor = give_dtac_get_donor();

					$is_restricted = $this->give_dtac_is_donor_restricted( $donor, $current_cpt );

					if( $is_restricted ) {
						wp_safe_redirect( give_dtac_donation_form_url( $form_id, $current_cpt ) );
						exit;	
					}
				}// End if is_page check
			}// End if empty check
		}



		/**
		 * [give_dtac_restrict_ctax]
		 * 
		 * Restrict a custom taxonomy archive page
		 * 
		 * @since  1.0
		 * 
		 * @param  [int] $form_id [Donation Form ID for redirection]
		 * 
		 * @return [void]          
		 */
		public function give_dtac_restrict_ctax( $form_id ) {
			global $wp_query;

			$ctaxs = give_dtac_get_settings( 'give_dtac_restrict_access_to_custom_tax' );

			$ctaxs = ( ! empty( $ctaxs ) ? $ctaxs : array() );

			$queried_object = get_queried_object();
			$current_ctax 	= $queried_object->term_id;

			if( ! empty( $ctaxs ) ) {

				if( is_tax( $ctaxs ) ) {

					$donor = give_dtac_get_donor();

					$is_restricted = $this->give_dtac_is_donor_restricted( $donor, $current_ctax );

					if( $is_restricted ) {
						wp_safe_redirect( give_dtac_donation_form_url( $form_id, $current_ctax ) );
						exit;	
					}
				}// End if is_page check
			}// End if empty check
		}

	} //End class Give_Donate_To_Access_Functions

endif; //End if class_exists check