<?php
/**
 * Functions for frontend operations
 *
 * @since 1.0.0
 */

namespace DTAC\Frontend;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Functions' ) ) :

	/**
	 * Plugin specific funcitons.
	 *
	 * @since 1.0.0
	 */
	class Functions {

		/**
		 * Give object instance
		 *
		 * @since 2.0.0
		 *
		 * @var object
		 */
		protected $give;


		/**
		 * Class constructor.
		 *
		 * @since 2.0.0
		 *
		 * @return void
		 */
		public function __construct() {

			$this->give = Give();
		}

		/**
		 * Check the status of donation and if a user should be allowed to access
		 * the content.
		 *
		 * @since 1.0.0
		 *
		 * @param string $content Content for the shortcode to output.
		 * @param string $restrict_content The restricted content in case the donor has not made a donation.
		 *
		 * @return string
		 */
		public function dtac_give_check_access( $content, $restrict_content = '' ) {

			global $wp_query;

			// Initialize the varibales.
			$id              = '';
			$field           = '';
			$value           = '';
			$donor           = array();
			$current_page_id = 0;
			$access_content  = array();
			$result          = '';

			$result = $restrict_content;

			$current_page_id = $wp_query->post->ID;

			$is_restricted = self::dtac_give_is_donor_restricted( $current_page_id );

			if ( $is_restricted ) {
				$result = $restrict_content;
			} else {
				$result = $content;
			}

			return $result;
		}

		/**
		 * Check if content is restrcied for donor or not.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $content Content page, post id or slug.
		 *
		 * @return bool
		 */
		public static function dtac_give_is_donor_restricted( $content ) {

			$is_restricted = true;

			$donor = dtac_give_get_donor();

			// If donor exists.
			if ( ! empty( $donor ) ) {

				$payment_ids = $donor->payment_ids;

				$payment_ids = explode( ',', $payment_ids );

				// If there is a payment ID.
				if ( ! empty( $payment_ids ) ) :

					foreach ( $payment_ids as $payment_id ) {

						// Get content ID's to acess.
						$access_content[] = get_post_meta( $payment_id, '_dtac_give_access_to_content', true );
					}

					if ( in_array( $content, $access_content ) ) {
						$is_restricted = false;
					}

				endif;
			}

			return $is_restricted;
		}

		/**
		 * Restrict Access to complete website unless a donor has made a donation.
		 *
		 * @since 1.0.0
		 *
		 * @param int $form_id Donation Form ID.
		 *
		 * @return void
		 */
		public function dtac_give_restrict_whole_site( $form_id ) {
			global $wp_query;

			$donated = '';

			$donor = dtac_give_get_donor();

			if ( $donor ) {
				$donated = $this->give->donor_meta->get_meta( $donor->id, 'give_dtca_access_website', true );
			}

			if ( ! $donated || 'yes' !== $donated ) {

				$current_cpt 	= get_post_type();
				$current_cpt_id = $wp_query->post->ID;
				$access_to 		= dtac_give_get_settings( 'dtac_give_access_to_pages' );

				if ( ! is_page( $access_to ) && ! is_singular( 'give_forms' ) && $current_cpt_id != $form_id ) {
					wp_safe_redirect( dtac_give_donation_form_url( $form_id, 'site' ) );
					exit;
				}
			}
		}

		/**
		 * Restrict Pages.
		 *
		 * @since 1.0.0
		 *
		 * @param int $form_id ID of the Form.
		 *
		 * @return void
		 */
		public function dtac_give_restrict_pages( $form_id ) {
			global $wp_query;

			$pages = dtac_give_get_settings( 'dtac_give_restrict_access_to_pages' );

			$pages = ( ! empty( $pages ) ? $pages : array() );

			$current_page = $wp_query->post->ID;

			if ( ! empty( $pages ) ) {

				if ( is_page( $pages ) ) {

					$is_restricted = self::dtac_give_is_donor_restricted( $current_page );

					if ( $is_restricted ) {
						wp_safe_redirect( dtac_give_donation_form_url( $form_id, $current_page ) );
						exit;
					}
				} // End if is_page check.
			} // End if empty check.
		}

		/**
		 * Restrict Posts.
		 *
		 * @since 1.0.0
		 *
		 * @param int $form_id ID of the form.
		 *
		 * @return void
		 */
		public function dtac_give_restrict_posts( $form_id ) {
			global $wp_query;

			$posts = dtac_give_get_settings( 'dtac_give_restrict_access_to_posts' );

			$posts = ( ! empty( $posts ) ? $posts : array() );

			$current_post = $wp_query->post->ID;

			if ( ! empty( $posts ) ) {

				if ( is_single( $posts ) ) {

					$is_restricted = self::dtac_give_is_donor_restricted( $current_post );

					if ( $is_restricted ) {
						wp_safe_redirect( dtac_give_donation_form_url( $form_id, $current_post ) );
						exit;
					}

				} // End if is_page check.
			} // End if empty check.
		}


		/**
		 * Restrict Categories.
		 *
		 * Restrict categories archive page or if a single post is being displayed and
		 * the post are in any of the categories selected from the settings.
		 *
		 * @since 1.0.0
		 *
		 * @param int $form_id Donation Form ID.
		 *
		 * @return void
		 */
		public function dtac_give_restrict_cats( $form_id ) {
			global $wp_query;

			$cats = dtac_give_get_settings( 'dtac_give_restrict_access_to_cats' );

			$cats = ( ! empty ( $cats ) ? $cats : array() );

			$category    = get_queried_object();
			$current_cat = 'c' . $category->term_id;

			if ( ! empty( $cats ) ) {

				if ( is_category( $cats ) ) {

					$is_restricted = self::dtac_give_is_donor_restricted( $current_cat );

					if ( $is_restricted ) {
						wp_safe_redirect( dtac_give_donation_form_url( $form_id, $current_cat ) );
						exit;
					}

				} // End if is_page check.
			} // End if empty check.
		}

		/**
		 * Restrict a custom post types.
		 *
		 * @since 1.0.0
		 *
		 * @param int $form_id Donation Form ID for redirection.
		 *
		 * @return void
		 */
		public function dtac_give_restrict_cpt( $form_id ) {
			global $wp_query;

			$cpts = dtac_give_get_settings( 'dtac_give_restrict_access_to_cpt' );

			$cpts = ( ! empty( $cpts ) ? $cpts : array() );

			$current_cpt = get_post_type();

			if ( ! empty( $cpts ) ) {

				if ( is_singular( $cpts ) ) {

					$is_restricted = self::dtac_give_is_donor_restricted( $current_cpt );

					if ( $is_restricted ) {
						wp_safe_redirect( dtac_give_donation_form_url( $form_id, $current_cpt ) );
						exit;
					}

				} // End if is_page check.
			} // End if empty check.
		}

		/**
		 * Restrict a custom taxonomy archive page
		 *
		 * @since 1.0.0
		 *
		 * @param int $form_id Donation Form ID for redirection.
		 *
		 * @return void
		 */
		public function dtac_give_restrict_ctax( $form_id ) {
			global $wp_query;

			$ctaxs = dtac_give_get_settings( 'dtac_give_restrict_access_to_custom_tax' );

			$ctaxs = ( ! empty( $ctaxs ) ? $ctaxs : array() );

			$taxonomies     = dtac_give_get_custom_taxs_names(); // Get names of all registered taxonomies.
			$queried_object = get_queried_object();
			$current_ctax   = 'c' . $queried_object->term_id; // Currently displayed tax ID.

			if ( ! empty( $ctaxs ) ) {

				if ( is_tax( $taxonomies, $ctaxs ) ) {

					$is_restricted = self::dtac_give_is_donor_restricted( $current_ctax );

					if ( $is_restricted ) {
						wp_safe_redirect( dtac_give_donation_form_url( $form_id, $current_ctax ) );
						exit;
					}

				} // End if is_page check
			} // End if empty check
		}

	} // End class Functions.

endif; // End if class_exists check.
