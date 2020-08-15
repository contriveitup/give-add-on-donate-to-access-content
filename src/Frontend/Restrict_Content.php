<?php
/**
 * Restrict the content of the site
 *
 * @since 1.0.0
 */

namespace DTAC\Frontend;

use DTAC\Frontend\Functions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * This class is responsible for restricting the content and the functionality
 * related to restricting the content.
 *
 * @since 1.0.0
 *
 * @uses Class::Donate_To_Access_Content_Give_Functions
 */
class Restrict_Content extends Functions {

	/**
	 * Class constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'dtac_give_restrict_full' ) );
	}

	/**
	 * Restrict Full Content.
	 *
	 * This function will restrict the entire page, post, cats, etc...
	 * according to the settings selected in the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function dtac_give_restrict_full() {
		global $wp_query;

		$form_id          = (int) dtac_give_get_settings( 'dtac_give_restrict_access_give_form_id' );
		$restrict_website = dtac_give_get_settings( 'dtac_give_restrict_website' );
		$to_restrict      = dtac_give_get_settings( 'dtac_give_restrict_access_to' );

		if ( ! $form_id || 0 === $form_id ) {
			return;
		}

		// If whole website is restricted.
		if ( 'yes' === $restrict_website && ! is_admin() ) {

			$this->dtac_give_restrict_whole_site( $form_id );

		} else {

			if ( is_array( $to_restrict ) && ! empty( $to_restrict ) ) :

				// If pages.
				if ( in_array( 'pages', $to_restrict, true ) && is_page() ) {
					$this->dtac_give_restrict_pages( $form_id );
				}

				// If posts.
				if ( in_array( 'posts', $to_restrict, true ) && is_single() ) {
					$this->dtac_give_restrict_posts( $form_id );
				}

				// If categories.
				if ( in_array( 'cats', $to_restrict, true ) && ( is_archive() || is_single() ) ) {
					$this->dtac_give_restrict_cats( $form_id );
				}

				// If custom post types.
				if ( in_array( 'cpt', $to_restrict, true ) && is_singular() ) {
					$this->dtac_give_restrict_cpt( $form_id );
				}

				// If custom tax.
				if ( in_array( 'ctax', $to_restrict, true ) && is_tax() ) {
					$this->dtac_give_restrict_ctax( $form_id );
				}

			endif; // End if array check.
		}
	}

} // End class Restrict_Content.
