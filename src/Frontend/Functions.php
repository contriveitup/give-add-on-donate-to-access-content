<?php

/**
 * Functions for frontend operations
 *
 * @since 1.0.0
 */

namespace DTAC\Frontend;

use DTAC\Give\Give_Adapter;

// Exit if accessed directly.
defined('ABSPATH') || exit;

if (! class_exists('Functions')) :

	/**
	 * Plugin specific funcitons.
	 *
	 * @since 1.0.0
	 */
	class Functions
	{



		/**
		 * Give object instance
		 *
		 * @since 2.0.0
		 *
		 * @var object|null
		 */
		protected $give;

		/**
		 * Give compatibility adapter.
		 *
		 * @since 3.0.0
		 *
		 * @var Give_Adapter|null
		 */
		protected $give_adapter;

		/**
		 * Class constructor.
		 *
		 * @since 3.0.0 Store the Give adapter instead of assuming Give() is always present.
		 * @since 2.0.0
		 *
		 * @return void
		 */
		public function __construct()
		{

			$this->give_adapter = dtac_give_adapter();
			$this->give         = function_exists('Give') ? Give() : null;
		}

		/**
		 * Give compatibility adapter.
		 *
		 * @since 3.0.0
		 *
		 * @return Give_Adapter
		 */
		protected function give_adapter(): Give_Adapter
		{

			if (! $this->give_adapter instanceof Give_Adapter) {
				$this->give_adapter = dtac_give_adapter();
			}

			return $this->give_adapter;
		}

		/**
		 * Send a gated visitor to the donation form.
		 *
		 * @since 3.0.0
		 *
		 * @param int   $form_id    Give form ID.
		 * @param mixed $content_id Content identifier being unlocked.
		 *
		 * @return void
		 */
		public static function dtac_give_redirect_to_form($form_id, $content_id): void
		{

			$form_id    = absint($form_id);
			$content_id = dtac_give_sanitize_content_id($content_id);
			$url        = dtac_give_donation_form_url($form_id, $content_id);

			/**
			 * Filter where a gated visitor is redirected.
			 *
			 * Return an empty string to cancel the redirect and render the page.
			 *
			 * @since 3.0.0
			 *
			 * @param string $url        Redirect target.
			 * @param string $content_id Sanitized content ID.
			 * @param int    $form_id    Give form ID.
			 */
			$url = (string) apply_filters('dtac_give_restriction_redirect_url', $url, $content_id, $form_id);

			if ('' === $url) {
				return;
			}

			/**
			 * Fires just before a gated visitor is redirected to the donation form.
			 *
			 * @since 3.0.0
			 *
			 * @param string $url        Redirect target.
			 * @param string $content_id Sanitized content ID.
			 * @param int    $form_id    Give form ID.
			 */
			do_action('dtac_give_before_restriction_redirect', $url, $content_id, $form_id);

			wp_safe_redirect($url);
			exit;
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
		public function dtac_give_check_access($content, $restrict_content = '')
		{

			$current_page_id = dtac_give_get_current_object_id();

			if (dtac_give_should_bypass_restriction($current_page_id)) {
				return $content;
			}

			if (self::dtac_give_is_donor_restricted($current_page_id)) {
				return $restrict_content;
			}

			return $content;
		}

		/**
		 * Check if content is restrcied for donor or not.
		 *
		 * @since 3.0.0 Use the Give adapter and strict content-ID matching.
		 * @since 1.0.0
		 *
		 * @param mixed $content Content page, post id or slug.
		 *
		 * @return bool
		 */
		public static function dtac_give_is_donor_restricted($content)
		{

			if (dtac_give_should_bypass_restriction($content)) {
				return false;
			}

			$is_restricted = true;
			$content_id    = dtac_give_sanitize_content_id($content);
			$donor         = dtac_give_get_donor();

			if ($donor && '' !== $content_id) {
				$access_content = dtac_give_adapter()->get_unlocked_content_ids($donor);

				if (in_array($content_id, $access_content, true)) {
					$is_restricted = false;
				}
			}

			/**
			 * Filter whether the current donor is still gated for a content ID.
			 *
			 * @since 3.0.0
			 *
			 * @param bool        $is_restricted Whether the donor is restricted.
			 * @param string      $content_id    Sanitized content ID.
			 * @param object|null $donor         Current donor, or null for guests.
			 */
			return (bool) apply_filters('dtac_give_is_donor_restricted', $is_restricted, $content_id, $donor);
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
		public function dtac_give_restrict_whole_site($form_id)
		{

			$donated = '';
			$donor   = dtac_give_get_donor();

			if ($donor) {
				$donor_id = $this->give_adapter()->get_donor_id($donor);
				$donated  = $this->give_adapter()->get_donor_meta($donor_id, Give_Adapter::SITE_ACCESS_META_KEY);
			}

			if (! $donated || 'yes' !== $donated) {

				$current_cpt_id = dtac_give_get_current_object_id();
				$access_to      = dtac_give_get_settings('dtac_give_access_to_pages');

				if (! is_page($access_to) && ! is_singular('give_forms') && (int) $current_cpt_id !== (int) $form_id) {
					self::dtac_give_redirect_to_form($form_id, 'site');
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
		public function dtac_give_restrict_pages($form_id)
		{

			$pages = dtac_give_get_settings('dtac_give_restrict_access_to_pages');

			$pages = (! empty($pages) ? $pages : array());

			$current_page = dtac_give_get_current_object_id();

			if (! empty($pages)) {

				if (is_page($pages)) {

					$is_restricted = self::dtac_give_is_donor_restricted($current_page);

					if ($is_restricted) {
						self::dtac_give_redirect_to_form($form_id, $current_page);
					}
				}
			}
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
		public function dtac_give_restrict_posts($form_id)
		{

			$posts = dtac_give_get_settings('dtac_give_restrict_access_to_posts');

			$posts = (! empty($posts) ? $posts : array());

			$current_post = dtac_give_get_current_object_id();

			if (! empty($posts)) {

				if (is_single($posts)) {

					$is_restricted = self::dtac_give_is_donor_restricted($current_post);

					if ($is_restricted) {
						self::dtac_give_redirect_to_form($form_id, $current_post);
					}
				}
			}
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
		public function dtac_give_restrict_cats($form_id)
		{

			$cats = dtac_give_get_settings('dtac_give_restrict_access_to_cats');

			$cats = (! empty($cats) ? $cats : array());

			$category = get_queried_object();

			if (! $category instanceof \WP_Term) {
				return;
			}

			$current_cat = 'c' . $category->term_id;

			if (! empty($cats)) {

				if (is_category($cats)) {

					$is_restricted = self::dtac_give_is_donor_restricted($current_cat);

					if ($is_restricted) {
						self::dtac_give_redirect_to_form($form_id, $current_cat);
					}
				}
			}
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
		public function dtac_give_restrict_cpt($form_id)
		{

			$cpts = dtac_give_get_settings('dtac_give_restrict_access_to_cpt');

			$cpts = (! empty($cpts) ? $cpts : array());

			$current_cpt = get_post_type();

			if (! empty($cpts)) {

				if (is_singular($cpts)) {

					$is_restricted = self::dtac_give_is_donor_restricted($current_cpt);

					if ($is_restricted) {
						self::dtac_give_redirect_to_form($form_id, $current_cpt);
					}
				}
			}
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
		public function dtac_give_restrict_ctax($form_id)
		{

			$ctaxs = dtac_give_get_settings('dtac_give_restrict_access_to_custom_tax');

			$ctaxs = (! empty($ctaxs) ? $ctaxs : array());

			$taxonomies     = dtac_give_get_custom_taxs_names();
			$queried_object = get_queried_object();

			if (! $queried_object instanceof \WP_Term) {
				return;
			}

			$current_ctax = 'c' . $queried_object->term_id;

			if (! empty($ctaxs)) {

				if (is_tax($taxonomies, $ctaxs)) {

					$is_restricted = self::dtac_give_is_donor_restricted($current_ctax);

					if ($is_restricted) {
						self::dtac_give_redirect_to_form($form_id, $current_ctax);
					}
				}
			}
		}
	} // End class Functions.

endif; // End if class_exists check.
