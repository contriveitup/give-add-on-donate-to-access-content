<?php
/**
 * Plugin Shortcodes
 *
 * In this file you can find all the shortcodes used by this add-on.
 *
 * @package DTAC_Give
 *
 * @since 1.0.0
 */

namespace DTAC\Frontend;

use DTAC\Frontend\Functions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Shortcodes' ) ) {

	/**
	 * Register and Run plugin shortcodes.
	 *
	 * @since 1.0.0
	 */
	class Shortcodes extends Functions {

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			add_shortcode( 'cip_donate_to_access_content', [ $this, 'donate_to_access_give_shortcode_func' ] );
		}

		/**
		 * Restrict Access Shortcode.
		 *
		 * This shortcode will restrict access to the content until a donation is made.
		 *
		 * Usage : [cip_donate_to_access_content form_id=1 show='form|message'] Content to be restricted goes here [/cip_donate_to_access_content]
		 *
		 * Form ID is necessary and it will show a donation form by default when the content is restricted
		 *
		 * @since 1.0.0
		 *
		 * @param array  $atts Shortcode Attributes.
		 * @param string $content Shortcode content.
		 *
		 * @return void
		 */
		public function donate_to_access_give_shortcode_func( $atts, $content = null ) {

			global $wp_query;

			$a = shortcode_atts(
				array(
					'form_id' => '', // Give donation form ID.
					'show'    => 'form', // Options form or message.
				),
				$atts
			);

			// Revert back if a form id is not provided.
			if ( '' === $a['form_id'] ) {
				return;
			}

			$current_page_id = $wp_query->post->ID;

			// If show type is a form.
			if ( 'form' === $a['show'] ) :

				$restrict_content = do_shortcode( '[give_form id="' . $a['form_id'] . '"]' );

				$content = $this->dtac_give_check_access( $content, $restrict_content );

			endif;

			// If show type is a message.
			if ( 'message' === $a['show'] ) :

				$message       = dtac_give_get_settings( 'dtac_give_restrict_message' );
				$donation_link = dtac_give_donation_form_url( $a['form_id'], $current_page_id );
				$message       = str_replace( '%%donation_form_url%%', $donation_link, $message );
				$content       = $this->dtac_give_check_access( $content, $message );


			endif;

			return $content;
		}
	}
}
