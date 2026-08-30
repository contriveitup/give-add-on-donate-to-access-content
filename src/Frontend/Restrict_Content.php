<?php

/**
 * Restrict the content of the site
 *
 * @since 1.0.0
 */

namespace DTAC\Frontend;

use DTAC\Frontend\Functions;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * This class is responsible for restricting the content and the functionality
 * related to restricting the content.
 *
 * @since 1.0.0
 *
 * @uses Class::Donate_To_Access_Content_Give_Functions
 */
class Restrict_Content extends Functions
{





	/**
	 * Class constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{

		parent::__construct();

		add_action('wp', array($this, 'dtac_give_restrict_full'));
		add_action('send_headers', array($this, 'maybe_send_cache_headers'));
	}

	/**
	 * Prevent caches from storing restricted URLs.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function maybe_send_cache_headers(): void
	{

		if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
			return;
		}

		$should_nocache = dtac_give_is_whole_site_restricted();
		$post_id        = dtac_give_get_current_object_id();

		if (! $should_nocache && $post_id > 0 && dtac_give_is_post_restricted($post_id)) {
			$should_nocache = true;
		}

		/**
		 * Filter whether no-cache headers are sent for the current request.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $should_nocache Whether to send no-cache headers.
		 * @param int  $post_id        Current post ID, or 0.
		 */
		$should_nocache = (bool) apply_filters('dtac_give_send_nocache_headers', $should_nocache, $post_id);

		if (! $should_nocache) {
			return;
		}

		if (! defined('DONOTCACHEPAGE')) {
			define('DONOTCACHEPAGE', true);
		}

		if (! headers_sent()) {
			nocache_headers();
			header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
		}
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
	public function dtac_give_restrict_full()
	{

		if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
			return;
		}

		$restrict_website = dtac_give_get_settings('dtac_give_restrict_website');
		$to_restrict      = dtac_give_get_settings('dtac_give_restrict_access_to');
		$current_id       = dtac_give_get_current_object_id();
		$form_id          = dtac_give_get_form_id_for_content($current_id);

		if (dtac_give_should_bypass_restriction($current_id)) {
			return;
		}

		if (is_singular() && $current_id > 0 && dtac_give_post_has_metabox_restriction($current_id) && $form_id > 0) {
			if (self::dtac_give_is_donor_restricted($current_id)) {
				self::dtac_give_redirect_to_form($form_id, $current_id);
			}

			return;
		}

		if (! $form_id || 0 === $form_id) {
			return;
		}

		// If whole website is restricted.
		if ('yes' === $restrict_website) {
			$this->dtac_give_restrict_whole_site($form_id);
		} elseif (is_array($to_restrict) && ! empty($to_restrict)) {

			// If pages.
			if (in_array('pages', $to_restrict, true) && is_page()) {
				$this->dtac_give_restrict_pages($form_id);
			}

			// If posts.
			if (in_array('posts', $to_restrict, true) && is_single()) {
				$this->dtac_give_restrict_posts($form_id);
			}

			// If categories.
			if (in_array('cats', $to_restrict, true) && (is_archive() || is_single())) {
				$this->dtac_give_restrict_cats($form_id);
			}

			// If custom post types.
			if (in_array('cpt', $to_restrict, true) && is_singular()) {
				$this->dtac_give_restrict_cpt($form_id);
			}

			// If custom tax.
			if (in_array('ctax', $to_restrict, true) && is_tax()) {
				$this->dtac_give_restrict_ctax($form_id);
			}
		}
	}
} // End class Restrict_Content.
