<?php

/**
 * Compatibility wrapper around GiveWP public APIs.
 *
 * @package DTAC_Give
 *
 * @since 3.0.0
 */

namespace DTAC\Give;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Isolate GiveWP calls so missing or older versions degrade safely.
 *
 * @since 3.0.0
 */
class Give_Adapter
{


	/**
	 * Content meta key stored on a donation.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const CONTENT_META_KEY = '_dtac_give_access_to_content';

	/**
	 * Donor meta key for whole-site access.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const SITE_ACCESS_META_KEY = 'give_dtca_access_website';

	/**
	 * Pending content cookie name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const PENDING_CONTENT_COOKIE = 'dtac_give_pending_content';

	/**
	 * Guest restore-access cookie name.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const GUEST_ACCESS_COOKIE = 'dtac_give_guest_access';

	/**
	 * Singleton instance.
	 *
	 * @since 3.0.0
	 *
	 * @var Give_Adapter|null
	 */
	private static $instance = null;

	/**
	 * Cached Give core instance when available.
	 *
	 * @since 3.0.0
	 *
	 * @var object|null
	 */
	private $give = null;

	/**
	 * Singleton accessor.
	 *
	 * @since 3.0.0
	 *
	 * @return Give_Adapter
	 */
	public static function instance(): Give_Adapter
	{

		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function __construct()
	{

		if (function_exists('Give')) {
			$this->give = Give();
		}
	}

	/**
	 * Prevent cloning.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function __clone()
	{
		_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'dtac-give'), '3.0.0');
	}

	/**
	 * Prevent unserializing.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __wakeup()
	{
		_doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'dtac-give'), '3.0.0');
	}

	/**
	 * Whether Give core is available.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function is_available(): bool
	{

		return (null !== $this->give);
	}

	/**
	 * Detect a GiveWP visual-builder (v3/v4) form.
	 *
	 * @since 3.0.0
	 *
	 * @param int $form_id Donation form ID.
	 *
	 * @return bool
	 */
	public function is_v3_form(int $form_id): bool
	{

		if ($form_id <= 0) {
			return false;
		}

		$utils = '\\Give\\Helpers\\Form\\Utils';

		if (class_exists($utils) && method_exists($utils, 'isV3Form')) {
			return (bool) $utils::isV3Form($form_id);
		}

		if ($this->give && isset($this->give->form_meta) && is_object($this->give->form_meta)) {
			return (bool) $this->give->form_meta->get_meta($form_id, 'formBuilderSettings', true);
		}

		return false;
	}

	/**
	 * Get the current donor or null.
	 *
	 * @since 3.0.0
	 *
	 * @return object|null
	 */
	public function get_current_donor()
	{

		if (is_user_logged_in()) {
			$donor = $this->get_donor_by_user_id((int) get_current_user_id());

			if ($donor) {
				return $donor;
			}
		}

		$email = $this->get_session_email();

		if ('' !== $email) {
			return $this->get_donor_by_email($email);
		}

		$email = dtac_give_get_guest_access_email();

		if ('' !== $email) {
			return $this->get_donor_by_email($email);
		}

		return null;
	}

	/**
	 * Get a donor by WordPress user ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $user_id WordPress user ID.
	 *
	 * @return object|null
	 */
	public function get_donor_by_user_id(int $user_id)
	{

		if ($user_id <= 0) {
			return null;
		}

		$donor_class = '\\Give\\Donors\\Models\\Donor';

		if (class_exists($donor_class) && method_exists($donor_class, 'whereUserId')) {
			$donor = $donor_class::whereUserId($user_id);

			if ($donor) {
				return $donor;
			}
		}

		return $this->get_legacy_donor_by('user_id', $user_id);
	}

	/**
	 * Get a donor by email.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email Donor email.
	 *
	 * @return object|null
	 */
	public function get_donor_by_email(string $email)
	{

		$email = sanitize_email($email);

		if ('' === $email || ! is_email($email)) {
			return null;
		}

		$donor_class = '\\Give\\Donors\\Models\\Donor';

		if (class_exists($donor_class) && method_exists($donor_class, 'whereEmail')) {
			$donor = $donor_class::whereEmail($email);

			if ($donor) {
				return $donor;
			}
		}

		return $this->get_legacy_donor_by('email', $email);
	}

	/**
	 * Get a donor by donation ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int $donation_id Donation ID.
	 *
	 * @return object|null
	 */
	public function get_donor_by_donation_id(int $donation_id)
	{

		if ($donation_id <= 0) {
			return null;
		}

		$donation_class = '\\Give\\Donations\\Models\\Donation';

		if (class_exists($donation_class) && method_exists($donation_class, 'find')) {
			$donation = $donation_class::find($donation_id);

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- GiveWP Donation model property.
			if (is_object($donation) && ! empty($donation->donorId)) {
				$donor_class = '\\Give\\Donors\\Models\\Donor';

				if (class_exists($donor_class) && method_exists($donor_class, 'find')) {
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- GiveWP Donation model property.
					$donor = $donor_class::find((int) $donation->donorId);

					if ($donor) {
						return $donor;
					}
				}
			}
		}

		$donor_id = (int) $this->get_donation_meta($donation_id, '_give_payment_donor_id');

		if ($donor_id <= 0) {
			$donor_id = (int) $this->get_donation_meta($donation_id, '_give_payment_customer_id');
		}

		if ($donor_id <= 0) {
			return null;
		}

		return $this->get_legacy_donor_by('id', $donor_id);
	}

	/**
	 * Numeric donor ID from a donor object.
	 *
	 * @since 3.0.0
	 *
	 * @param object|null $donor Donor object.
	 *
	 * @return int
	 */
	public function get_donor_id($donor): int
	{

		if (! is_object($donor) || ! isset($donor->id)) {
			return 0;
		}

		return (int) $donor->id;
	}

	/**
	 * Content IDs unlocked by a donor's completed donations.
	 *
	 * @since 3.0.0
	 *
	 * @param object|null $donor Donor object.
	 *
	 * @return array
	 */
	public function get_unlocked_content_ids($donor): array
	{

		if (! is_object($donor)) {
			return array();
		}

		$content_ids = array();

		foreach ($this->get_donor_donation_ids($donor) as $donation_id) {
			if (! $this->is_donation_complete($donation_id)) {
				continue;
			}

			$content_id = $this->get_donation_meta($donation_id, self::CONTENT_META_KEY);
			$content_id = dtac_give_sanitize_content_id($content_id);

			if ('' === $content_id) {
				continue;
			}

			if (! dtac_give_donation_unlocks_content($donation_id, $content_id)) {
				continue;
			}

			$content_ids[] = $content_id;
		}

		$donor_id = $this->get_donor_id($donor);

		if ($donor_id > 0 && 'yes' === $this->get_donor_meta($donor_id, self::SITE_ACCESS_META_KEY)) {
			if (dtac_give_get_min_amount('site') <= 0.0 && dtac_give_get_expiry_days('site') <= 0) {
				$content_ids[] = 'site';
			}
		}

		/**
		 * Filter the content IDs a donor has unlocked.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $content_ids Sanitized content IDs.
		 * @param object   $donor       Donor object.
		 */
		$content_ids = (array) apply_filters('dtac_give_unlocked_content_ids', array_values(array_unique($content_ids)), $donor);

		return array_values(array_unique(array_filter(array_map('dtac_give_sanitize_content_id', $content_ids))));
	}

	/**
	 * Donation IDs belonging to a donor.
	 *
	 * @since 3.0.0
	 *
	 * @param object $donor Donor object.
	 *
	 * @return int[]
	 */
	public function get_donor_donation_ids($donor): array
	{

		$donation_ids = array();

		if (is_object($donor) && method_exists($donor, 'donations')) {
			try {
				$query     = $donor->donations();
				$donations = (is_object($query) && method_exists($query, 'getAll')) ? $query->getAll() : array();
				$donations = is_array($donations) ? $donations : array();

				foreach ($donations as $donation) {
					if (is_object($donation) && ! empty($donation->id)) {
						$donation_ids[] = (int) $donation->id;
					}
				}
			} catch (\Throwable $exception) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// Fall through to the legacy payment_ids string.
			}
		}

		if (empty($donation_ids) && is_object($donor) && ! empty($donor->payment_ids)) {
			$legacy_ids = array_filter(array_map('absint', explode(',', (string) $donor->payment_ids)));

			foreach ($legacy_ids as $legacy_id) {
				if ($legacy_id > 0) {
					$donation_ids[] = $legacy_id;
				}
			}
		}

		return array_values(array_unique($donation_ids));
	}

	/**
	 * Whether a donation should unlock content.
	 *
	 * @since 3.0.0
	 *
	 * @param int $donation_id Donation ID.
	 *
	 * @return bool
	 */
	public function is_donation_complete(int $donation_id): bool
	{

		if ($donation_id <= 0) {
			return false;
		}

		$status = $this->get_donation_status($donation_id);

		return in_array($status, $this->complete_donation_statuses(), true);
	}

	/**
	 * Donation post status.
	 *
	 * @since 3.0.0
	 *
	 * @param int $donation_id Donation ID.
	 *
	 * @return string
	 */
	public function get_donation_status(int $donation_id): string
	{

		$donation_class = '\\Give\\Donations\\Models\\Donation';

		if (class_exists($donation_class) && method_exists($donation_class, 'find')) {
			$donation = $donation_class::find($donation_id);

			if (is_object($donation) && isset($donation->status)) {
				$status = $donation->status;

				if (is_object($status) && method_exists($status, 'getValue')) {
					return (string) $status->getValue();
				}

				if (is_string($status)) {
					return $status;
				}
			}
		}

		$post_status = get_post_status($donation_id);

		return is_string($post_status) ? $post_status : '';
	}

	/**
	 * Statuses that grant access.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function complete_donation_statuses(): array
	{

		$statuses = array('publish', 'complete');

		/**
		 * Filter donation statuses that unlock content.
		 *
		 * @since 3.0.0
		 *
		 * @param array $statuses Status slugs.
		 */
		return (array) apply_filters('dtac_give_complete_donation_statuses', $statuses);
	}

	/**
	 * Raw donation amount.
	 *
	 * @since 3.0.0
	 *
	 * @param int $donation_id Donation ID.
	 *
	 * @return float
	 */
	public function get_donation_amount(int $donation_id): float
	{

		if ($donation_id <= 0) {
			return 0.0;
		}

		$donation_class = '\\Give\\Donations\\Models\\Donation';

		if (class_exists($donation_class) && method_exists($donation_class, 'find')) {
			$donation = $donation_class::find($donation_id);

			if (is_object($donation) && isset($donation->amount)) {
				$amount = $donation->amount;

				if (is_object($amount) && method_exists($amount, 'formatToDecimal')) {
					return (float) $amount->formatToDecimal();
				}

				if (is_object($amount) && isset($amount->value)) {
					return (float) $amount->value;
				}

				if (is_numeric($amount)) {
					return (float) $amount;
				}
			}
		}

		if (function_exists('give_donation_amount')) {
			$formatted = give_donation_amount(
				$donation_id,
				array(
					'currency' => false,
					'amount'   => true,
				)
			);

			if (is_numeric($formatted)) {
				return (float) $formatted;
			}
		}

		$total = $this->get_donation_meta($donation_id, '_give_payment_total');

		return is_numeric($total) ? (float) $total : 0.0;
	}

	/**
	 * Unix timestamp for a donation.
	 *
	 * @since 3.0.0
	 *
	 * @param int $donation_id Donation ID.
	 *
	 * @return int
	 */
	public function get_donation_timestamp(int $donation_id): int
	{

		if ($donation_id <= 0) {
			return 0;
		}

		$donation_class = '\\Give\\Donations\\Models\\Donation';

		if (class_exists($donation_class) && method_exists($donation_class, 'find')) {
			$donation = $donation_class::find($donation_id);

			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- GiveWP Donation model property.
			if (is_object($donation) && isset($donation->createdAt) && $donation->createdAt instanceof \DateTimeInterface) {
				return $donation->createdAt->getTimestamp();
			}
			// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			if (is_object($donation) && ! empty($donation->date)) {
				$timestamp = strtotime((string) $donation->date);

				if ($timestamp) {
					return (int) $timestamp;
				}
			}
		}

		$post = get_post($donation_id);

		if ($post instanceof \WP_Post && ! empty($post->post_date_gmt) && '0000-00-00 00:00:00' !== $post->post_date_gmt) {
			$timestamp = strtotime($post->post_date_gmt . ' UTC');

			if ($timestamp) {
				return (int) $timestamp;
			}
		}

		if ($post instanceof \WP_Post && ! empty($post->post_date)) {
			$timestamp = strtotime($post->post_date);

			if ($timestamp) {
				return (int) $timestamp;
			}
		}

		return 0;
	}

	/**
	 * Email address from a donor object.
	 *
	 * @since 3.0.0
	 *
	 * @param object|null $donor Donor object.
	 *
	 * @return string
	 */
	public function get_donor_email($donor): string
	{

		if (! is_object($donor)) {
			return '';
		}

		if (! empty($donor->email) && is_email($donor->email)) {
			return sanitize_email($donor->email);
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- GiveWP Donor model property.
		if (! empty($donor->emailAddress) && is_email($donor->emailAddress)) {
			return sanitize_email($donor->emailAddress);
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		return '';
	}

	/**
	 * Persist a guest email on the Give session when possible.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email Donor email.
	 *
	 * @return void
	 */
	public function set_session_email(string $email): void
	{

		$email = sanitize_email($email);

		if ('' === $email || ! is_email($email)) {
			return;
		}

		if ($this->give && isset($this->give->session) && is_object($this->give->session) && method_exists($this->give->session, 'set')) {
			$this->give->session->set('give_email', $email);
		}
	}

	/**
	 * Get donation meta.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $donation_id Donation ID.
	 * @param string $meta_key    Meta key.
	 *
	 * @return mixed
	 */
	public function get_donation_meta(int $donation_id, string $meta_key)
	{

		if ($donation_id <= 0 || '' === $meta_key) {
			return '';
		}

		if (function_exists('give_get_meta')) {
			return give_get_meta($donation_id, $meta_key, true);
		}

		return get_post_meta($donation_id, $meta_key, true);
	}

	/**
	 * Update donation meta.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $donation_id Donation ID.
	 * @param string $meta_key    Meta key.
	 * @param mixed  $meta_value  Meta value.
	 *
	 * @return bool
	 */
	public function update_donation_meta(int $donation_id, string $meta_key, $meta_value): bool
	{

		if ($donation_id <= 0 || '' === $meta_key) {
			return false;
		}

		if (function_exists('give_update_meta')) {
			return (bool) give_update_meta($donation_id, $meta_key, $meta_value);
		}

		return (bool) update_post_meta($donation_id, $meta_key, $meta_value);
	}

	/**
	 * Get donor meta.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $donor_id Donor ID.
	 * @param string $meta_key Meta key.
	 *
	 * @return mixed
	 */
	public function get_donor_meta(int $donor_id, string $meta_key)
	{

		if ($donor_id <= 0 || '' === $meta_key) {
			return '';
		}

		if ($this->give && isset($this->give->donor_meta) && is_object($this->give->donor_meta)) {
			return $this->give->donor_meta->get_meta($donor_id, $meta_key, true);
		}

		return '';
	}

	/**
	 * Add donor meta.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $donor_id   Donor ID.
	 * @param string $meta_key   Meta key.
	 * @param mixed  $meta_value Meta value.
	 *
	 * @return bool
	 */
	public function add_donor_meta(int $donor_id, string $meta_key, $meta_value): bool
	{

		if ($donor_id <= 0 || '' === $meta_key) {
			return false;
		}

		if ($this->give && isset($this->give->donor_meta) && is_object($this->give->donor_meta)) {
			return (bool) $this->give->donor_meta->add_meta($donor_id, $meta_key, $meta_value);
		}

		return false;
	}

	/**
	 * Session or purchase-session email for guests.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_session_email(): string
	{

		if ($this->give && isset($this->give->session) && is_object($this->give->session)) {
			$session = $this->give->session;

			if (method_exists($session, 'get_session_expiration') && false !== $session->get_session_expiration() && method_exists($session, 'get')) {
				$email = $session->get('give_email');

				if (is_email($email)) {
					return sanitize_email($email);
				}
			}

			if (method_exists($session, 'get')) {
				$email = $session->get('give_email');

				if (is_email($email)) {
					return sanitize_email($email);
				}
			}
		}

		if (function_exists('give_get_purchase_session')) {
			$purchase = give_get_purchase_session();

			if (is_array($purchase) && ! empty($purchase['user_email']) && is_email($purchase['user_email'])) {
				return sanitize_email($purchase['user_email']);
			}
		}

		$accessor = '\\Give\\Session\\SessionDonation\\DonationAccessor';

		if (class_exists($accessor)) {
			try {
				$session_donation = new $accessor();

				if (method_exists($session_donation, 'get')) {
					$data = $session_donation->get();

					// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- GiveWP session donation property.
					if (is_object($data) && ! empty($data->donorEmail) && is_email($data->donorEmail)) {
						return sanitize_email($data->donorEmail);
					}
					// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				}
			} catch (\Throwable $exception) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// Ignore session accessor failures and continue without a guest email.
			}
		}

		return '';
	}

	/**
	 * Legacy donor lookup.
	 *
	 * @since 3.0.0
	 *
	 * @param string $field Lookup field.
	 * @param mixed  $value Lookup value.
	 *
	 * @return object|null
	 */
	private function get_legacy_donor_by(string $field, $value)
	{

		if (! $this->give || ! isset($this->give->donors) || ! is_object($this->give->donors)) {
			return null;
		}

		if (! method_exists($this->give->donors, 'get_donor_by')) {
			return null;
		}

		$donor = $this->give->donors->get_donor_by($field, $value);

		return (! empty($donor) && is_object($donor)) ? $donor : null;
	}
}
