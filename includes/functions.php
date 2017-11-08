<?php
/**
 * This file contains all the functions that are common for both frontend
 * and backend
 * 
 * @since  1.0 
 */
 
if ( ! defined( 'ABSPATH' ) ) {
 	exit; // Exit if accessed directly
}

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
function dtac_give_get_settings( $key = '' ) {

	$settings = array();

	$settings = get_option( 'dtac_give_settings' );

	if( ! empty( $settings ) ) {

		if( '' != $key ) {
			$settings = $settings[ $key ];
		} else {
			$settings = (array) apply_filters( 'dtac_give_get_settings', $settings );
		}
	}

	return $settings;
}



if( ! function_exists( 'dtac_give_get_donor' ) ):

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
	function dtac_give_get_donor() {
		
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

		$field = ( is_int( $id ) ? 'user_id' : 'email' );

		$field = apply_filters( 'dtac_give_donor_field', $field );
		$value = apply_filters( 'dtac_give_donor_value', $id );


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
function dtac_give_donation_form_url( $form_id, $current_page_id ) {

	$form_url = get_permalink( $form_id );

	$query_args = array(
					'dtac_give_content' => $current_page_id,
				);

	$query_args = apply_filters( 'dtac_give_redirection_query_string_array', $query_args, $query_args );

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


/**
 * [dtac_give_get_custom_taxs]
 * 
 * Get all registered custom taxonomies
 * 
 * @since  1.0
 * 
 * @return [array] 
 */
function dtac_give_get_custom_taxs() {

	$taxomonies = array();

	$args = array(); //Only get public tax and ignore built-in taxomonies
	$args = apply_filters( 'dtac_give_custom_tax_args', $args, $args );

	$output = apply_filters( 'dtac_give_custom_tax_output_value', 'objects' ); // or names

	$taxonomies = get_taxonomies( $args, $output ); 

	return $taxonomies;
}


/**
 * [dtac_give_get_custom_taxs_names]
 * 
 * Get names of all registered taxonomies and return it in an array
 * 
 * @since  1.0
 * 
 * @return [array] 
 */
function dtac_give_get_custom_taxs_names() {

	$result = array();

	$taxonomies = dtac_give_get_custom_taxs(); //Get custom taxonomies object array

	if  ( $taxonomies ){

		foreach ( $taxonomies  as $taxonomy ) {

			$result[] = $taxonomy->name; 
		}

	}

	return $result;
}


if( ! function_exists( 'dtac_give_get_donor_by_payment_id' ) ){

	/**
	 * [dtac_give_get_donor_by_payment_id]
	 * 
	 * Get donor by payment id. Useful when using hooks and filters which have only
	 * payment id as parameter
	 * 
	 * @since  1.0
	 * 
	 * @param  [int] $payment_id [ID of the payment]
	 * 
	 * @return [int]             
	 */
	function dtac_give_get_donor_by_payment_id( $payment_id ){

		$result = '';

		$donor_id = get_post_meta( $payment_id, '_give_payment_customer_id', true );

		if( '' != $donor_id ) {
			$result = $donor_id;
		}

		return $result;
	}

}//End if function_exists check