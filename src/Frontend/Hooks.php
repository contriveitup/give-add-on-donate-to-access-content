<?php

/**
 * Contains hooks and filters to alter Give plugin data
 *
 * @since 1.0.0
 */

namespace DTAC\Frontend;

use DTAC\Frontend\Functions;
use DTAC\Give\Give_Adapter;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class containing hooks and filters used on the frontend.
 *
 * @since 1.0.0
 */
class Hooks extends Functions
{


	/**
	 * Class constructor. Fires Give plugin hooks.
	 *
	 * @since 3.0.0 Register v3/v4 donation-created hooks.
	 * @since 1.0.0
	 */
	public function __construct()
	{

		parent::__construct();

		// Persist a content ID from the form URL for visual-builder grants.
		add_action('template_redirect', array($this, 'maybe_remember_requested_content'));

		// Add hidden fields to the donation form.
		add_action('give_donation_form_top', array($this, 'dtac_give_form_fields'));

		// Save required donation data for access to the content.
		add_action('give_complete_donation', array($this, 'save_dtac_give_payment_meta'));
		add_action('givewp_donation_created', array($this, 'save_dtac_give_donation_model'));
		add_action('givewp_donation_form_processing_donation_created', array($this, 'save_dtac_give_donation_model'));
	}

	/**
	 * Store a requested content ID before a v3/v4 form is submitted.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function maybe_remember_requested_content(): void
	{

		if (empty($_GET['dtac_give_content'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$content_id = dtac_give_sanitize_content_id(wp_unslash($_GET['dtac_give_content'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ('' === $content_id || ! dtac_give_is_grantable_content_id($content_id)) {
			return;
		}

		dtac_give_remember_pending_content($content_id);
	}

	/**
	 * Donation Form Fields.
	 *
	 * Add required hidden form fields to the donation form.
	 *
	 * @since 3.0.0 Skip visual-builder forms and sanitize the content ID.
	 * @since 1.0.0
	 *
	 * @param int $form_id ID of Give Donation Form.
	 *
	 * @return void
	 */
	public function dtac_give_form_fields($form_id)
	{

		$form_id = absint($form_id);

		if ($form_id && $this->give_adapter()->is_v3_form($form_id)) {
			return;
		}

		$current_page_id = dtac_give_get_current_object_id();
		$content         = '';

		if (isset($_GET['dtac_give_content'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$content = dtac_give_sanitize_content_id(wp_unslash($_GET['dtac_give_content'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ('' === $content) {
			$content = dtac_give_get_pending_content();
		}

		if ('' !== $content) {
			$current_page_id = $content;
		}

		$content_id = dtac_give_sanitize_content_id($current_page_id);

		if ('' === $content_id || (int) $content_id === $form_id || ! dtac_give_is_grantable_content_id($content_id)) {
			return;
		}

		echo '<input type="hidden" name="dtac_give_content" value="' . esc_attr($content_id) . '" />';
		echo '<input type="hidden" name="dtac_give_process_donate_to_access" value="1" />';
		wp_nonce_field(dtac_give_grant_nonce_action(), dtac_give_grant_nonce_field(), false);
	}

	/**
	 * Save Donation Data.
	 *
	 * Save the required donation data upon donation completion.
	 *
	 * @since 3.0.0 Route through the shared grant helper.
	 * @since 1.0.0
	 *
	 * @param int|string $payment_id ID of the payment.
	 *
	 * @return void
	 */
	public function save_dtac_give_payment_meta($payment_id)
	{

		if (! $this->should_process_legacy_grant()) {
			return;
		}

		$this->grant_access_for_donation((int) $payment_id, dtac_give_get_requested_content_id());
	}

	/**
	 * Persist unlock meta from a GiveWP Donation model.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $donation Donation model or donation ID.
	 *
	 * @return void
	 */
	public function save_dtac_give_donation_model($donation)
	{

		$donation_id = 0;

		if (is_object($donation) && isset($donation->id)) {
			$donation_id = (int) $donation->id;
		} elseif (is_numeric($donation)) {
			$donation_id = (int) $donation;
		}

		if ($donation_id <= 0) {
			return;
		}

		$this->grant_access_for_donation($donation_id, dtac_give_get_requested_content_id());
	}

	/**
	 * Whether the legacy complete-donation hook should grant access.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	private function should_process_legacy_grant(): bool
	{

		if (isset($_POST['dtac_give_process_donate_to_access']) && '1' === sanitize_text_field(wp_unslash($_POST['dtac_give_process_donate_to_access']))) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return $this->grant_nonce_is_valid();
		}

		return ('' !== dtac_give_get_requested_content_id());
	}

	/**
	 * Whether the v2 hidden-field nonce is present and valid.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	private function grant_nonce_is_valid(): bool
	{

		$field = dtac_give_grant_nonce_field();

		if (empty($_POST[$field])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return false;
		}

		return (bool) wp_verify_nonce(
			sanitize_text_field(wp_unslash($_POST[$field])), // phpcs:ignore WordPress.Security.NonceVerification.Missing
			dtac_give_grant_nonce_action()
		);
	}

	/**
	 * Write unlock meta for a donation.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $donation_id Donation ID.
	 * @param string $content_id  Restricted content identifier.
	 *
	 * @return void
	 */
	private function grant_access_for_donation(int $donation_id, string $content_id): void
	{

		if ($donation_id <= 0) {
			return;
		}

		$content_id = dtac_give_sanitize_content_id($content_id);

		if ('' === $content_id || ! dtac_give_is_grantable_content_id($content_id)) {
			return;
		}

		/**
		 * Filter whether a donation should unlock the requested content.
		 *
		 * Runs after the grantable-ID whitelist and before any meta is written,
		 * so returning false silently skips the grant.
		 *
		 * @since 3.0.0
		 *
		 * @param bool   $grant       Whether to grant access.
		 * @param int    $donation_id Donation ID.
		 * @param string $content_id  Sanitized content ID.
		 */
		if (! apply_filters('dtac_give_should_grant_access', true, $donation_id, $content_id)) {
			return;
		}

		$existing = (string) $this->give_adapter()->get_donation_meta($donation_id, Give_Adapter::CONTENT_META_KEY);

		if ($existing === $content_id) {
			return;
		}

		/**
		 * Fires before unlock meta is written for a donation.
		 *
		 * @since 3.0.0
		 *
		 * @param int    $donation_id Donation ID.
		 * @param string $content_id  Sanitized content ID.
		 */
		do_action('dtac_give_before_access_granted', $donation_id, $content_id);

		$this->give_adapter()->update_donation_meta($donation_id, Give_Adapter::CONTENT_META_KEY, $content_id);

		if ('site' === $content_id) {
			$donor    = $this->give_adapter()->get_donor_by_donation_id($donation_id);
			$donor_id = $this->give_adapter()->get_donor_id($donor);

			if ($donor_id > 0) {
				$current = $this->give_adapter()->get_donor_meta($donor_id, Give_Adapter::SITE_ACCESS_META_KEY);

				if ('yes' !== $current) {
					$this->give_adapter()->add_donor_meta($donor_id, Give_Adapter::SITE_ACCESS_META_KEY, 'yes');
				}
			}
		}

		dtac_give_clear_pending_content();

		$donor = $this->give_adapter()->get_donor_by_donation_id($donation_id);
		$email = $this->give_adapter()->get_donor_email($donor);

		if ('' !== $email) {
			dtac_give_remember_guest_access($email);
		}

		/**
		 * Fires once a donation has unlocked a content ID.
		 *
		 * Use this to send custom emails, sync a CRM, or log unlocks.
		 *
		 * @since 3.0.0
		 *
		 * @param int         $donation_id Donation ID.
		 * @param string      $content_id  Sanitized content ID.
		 * @param object|null $donor       Donor object, or null when unavailable.
		 */
		do_action('dtac_give_access_granted', $donation_id, $content_id, $donor);
	}
} // End class Hooks.
