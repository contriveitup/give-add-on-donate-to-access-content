<?php
/**
 * Shortcodes
 * 
 * In this file you can find all the shortcodes used by this add-on
 * 
 * @since 1.0 
 */
 
 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $give_dta_functions;


/**
 * Restrict Access Shortcode
 * 
 * This shortcode will restrict access to the content until a donation is made.
 * 
 * Usage : [give_donate_to_access form_id=1 show='form|message'] Content to be restricted goes here [/give_donate_to_access_func]
 * 
 * Form ID is necessary and it will show a donation form by default when the content is restricted
 * 
 * @since 1.0
 * 
 * @param array $atts
 * @param string $content
 * 
 * @return viod|output HTML
 */
function give_donate_to_access_func( $atts, $content = null ) {
	global $give_dta_functions, $wp_query;

    $a = 	shortcode_atts( 
	    		array(
					'form_id' 	=> '', //Give donation form ID
					'show' 	=> 'form', //options form or message
				), 
				$atts 
    		);

    //Revert back if a form id is not provided.
    if( '' == $a['form_id']  ) {
    	return;
    }

    //Core Give class object
    $give = Give();

    $current_page_id = $wp_query->post->ID;

    //If show type is a form
    if( $a['show'] == 'form' ) : 

    	$restrict_content = do_shortcode( '[give_form id="'.$a['form_id'].'"]' );

    	$content = $give_dta_functions->give_dta_check_access( $give, $content, $restrict_content );

    endif;


    //If show type is a message
    if( $a['show'] == 'message' ) : 

    	$message = $give_dta_functions->give_dta_get_settings( 'give_dta_restrict_message' );

    	$give_form_link = get_permalink( $a['form_id'] );

    	$donation_link = 	esc_url( 
                                add_query_arg( array(
    							    'give_dta_content' => $current_page_id,
    							), $give_form_link ) 
                            );

    	$message = str_replace( '%%donation_link%%', $donation_link, $message );

    	$content = $give_dta_functions->give_dta_check_access( $give, $content, $message );
    	

    endif;

    return $content;
}
add_shortcode( 'give_donate_to_access', 'give_donate_to_access_func' );