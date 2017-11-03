<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class is responsible for restricting the content and the functionality
 * related to restricting the content
 * 
 * @since  1.0
 * 
 * @access public
 */
class Give_Donate_To_Access_Content_Restrict_Cotent extends Give_Donate_To_Access_Content_Functions {

	/**
	 * [__construct]
	 * 
	 * Class constructor
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'give_dtac_restrict_full' ) );
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
	public function give_dtac_restrict_full() {
		global $wp_query;

		$to_restrcit = give_dtac_get_settings( 'give_dtac_restrict_access_to' );

		if( is_array( $to_restrcit ) && ! empty( $to_restrcit ) ):

			$form_id = (int) give_dtac_get_settings( 'give_dtac_restrict_access_give_form_id' );

			if( ! $form_id ) {
				return;
			}

			//If pages 
			if( in_array( 'pages', $to_restrcit ) && is_page() ) {
				$this->give_dtac_restrict_pages( $form_id );
			}

			//If posts 
			if( in_array( 'posts', $to_restrcit ) && is_single() ) {
				$this->give_dtac_restrict_posts( $form_id );
			}

			//If categories 
			if( in_array( 'cats', $to_restrcit ) && ( is_archive() || is_single() ) ) {
				$this->give_dtac_restrict_cats( $form_id );
			}

			//If custom post types
			if( in_array( 'cpt', $to_restrcit ) && is_singular() ) {
				$this->give_dtac_restrict_cpt( $form_id );
			}

			//If custom tax 
			if( in_array( 'ctax', $to_restrcit ) && is_tax() ) {
				$this->give_dtac_restrict_ctax( $form_id );
			}



		endif; //End if array check

	}

}// End class Give_Donate_To_Access_Content_Restrict_Cotent

new Give_Donate_To_Access_Content_Restrict_Cotent();