<?php
/**
 * This file contains all the functions that are common for both frontend
 * and backend
 * 
 * @since  1.0 
 */
 
global $give;
/**
 * [$give]
 * 
 * Save Core Give plugin class object in a 
 * global variable $give
 * 
 * @var [object]
 */
$give = Give();


/**
 * Get settings for the plugin
 * 
 * @since 1.0
 * 
 * @return array|mix Settings 
 */
function give_dtac_get_settings( $key = '' ) {

	$settings = array();

	$settings = get_option( 'give_dtac_settings' );

	if( ! empty( $settings ) ) {

		if( '' != $key ) {
			$settings = $settings[ $key ];
		} else {
			$settings = (array) apply_filters( 'give_dtac_get_settings', $settings );
		}
	}

	return $settings;
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
if( ! function_exists( 'give_dtac_get_donor' ) ):

	function give_dtac_get_donor() {
		
		global $give;

		$donor = array(); $result = array(); $id = ''; $value = '';

		/**
		 * Get user unique identity by user state
		 */
		if ( is_user_logged_in() ) {

			//Get user id if logged-in
			$id = get_current_user_id();

		} elseif ( $give->session->get_session_expiration() !== false ) {

			// get email is session is active
			$id = $give->session->get( 'give_email' );
		}

		$field = ( is_int( $id ) ? 'id' : 'email' );

		$field = apply_filters( 'give_dtac_donor_field', $field );
		$value = apply_filters( 'give_dtac_donor_value', $id );

		/**
		 * [$donor]
		 * 
		 * Get and saves donor in a varibale
		 * 
		 * @uses get_donor_by funciton from Give core plugin
		 * 
		 * @var [array]
		 */
		$donor = $give->donors->get_donor_by( $field, $value );


		//If donor exists
		if( ! empty( $donor ) || ! is_null( $donor ) ) {
			$result = $donor;
		}

		return $result;

	}// End function

endif; //End if function_exists check


/**
 * Generate Donation Form URL from form id with query args
 * 
 * @since 1.0
 * @param $form_id int
 * @param $current_page_id int
 * 
 * @return string  
 */
function give_dtac_donation_form_url( $form_id, $current_page_id ) {

	$form_url = get_permalink( $form_id );

	$query_args = array(
					'give_dta_content' => $current_page_id,
				);

	$query_args = apply_filters( 'give_dtac_redirection_query_string_array', $query_args, $query_args );

	$form_url = esc_url( add_query_arg( $query_args, $form_url ) );

	return $form_url;
}


/**
 * [is_dtac_plugin_settings_page]
 * 
 * Check if current admin page viewed is the settings page of this plugin.
 * 
 * @since  1.0
 * 
 * @return boolean
 */
function is_dtac_plugin_settings_page() {

	$is_admin_settings_page = false;

	$page 	= ( isset( $_GET['page'] ) && $_GET['page'] == 'give-settings' ? true : false );
	$tab 	= ( isset( $_GET['tab'] ) && $_GET['tab'] == 'donateaccess' ? true : false );

	if( $page && $tab ) {
		$is_admin_settings_page = true;
	}

	return $is_admin_settings_page;
}