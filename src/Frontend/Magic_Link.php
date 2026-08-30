<?php

/**
 * Guest restore-access magic links.
 *
 * @package DTAC_Give
 *
 * @since 3.0.0
 */

namespace DTAC\Frontend;

use DTAC\Frontend\Functions;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Email a signed, expiring link that restores guest donor access.
 *
 * @since 3.0.0
 */
class Magic_Link extends Functions
{


	/**
	 * Query flag for restore requests.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const QUERY_FLAG = 'dtac_give_restore';

	/**
	 * Form nonce action.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FORM_NONCE_ACTION = 'dtac_give_restore_access';

	/**
	 * Form nonce field.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const FORM_NONCE_FIELD = 'dtac_give_restore_nonce';

	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct()
	{

		parent::__construct();

		add_action('template_redirect', array($this, 'maybe_process'));
	}

	/**
	 * Handle restore form posts and consume signed links.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function maybe_process(): void
	{

		if (! empty($_POST['dtac_give_restore_email'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$this->handle_form();
			return;
		}

		if (empty($_GET[self::QUERY_FLAG])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$this->handle_link();
	}

	/**
	 * "Already donated?" form markup.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function form_html(): string
	{

		$message = '';

		if (! empty($_GET['dtac_give_restore_sent'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$message = '<p class="dtac-give-restore-notice">' . esc_html__('If that email has a qualifying donation, a restore link is on its way.', 'dtac-give') . '</p>';
		}

		if (! empty($_GET['dtac_give_restore_ok'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$message = '<p class="dtac-give-restore-notice">' . esc_html__('Access restored. You can continue to the content.', 'dtac-give') . '</p>';
		}

		if (! empty($_GET['dtac_give_restore_error'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$message = '<p class="dtac-give-restore-notice">' . esc_html__('That restore link is invalid or has expired.', 'dtac-give') . '</p>';
		}

		$html  = '<div class="dtac-give-restore-access">';
		$html .= $message;
		$html .= '<p>' . esc_html__('Already donated?', 'dtac-give') . '</p>';
		$html .= '<form method="post" action="">';
		if (function_exists('wp_nonce_field')) {
			$html .= wp_nonce_field(self::FORM_NONCE_ACTION, self::FORM_NONCE_FIELD, true, false);
		}
		$html .= '<p><label for="dtac_give_restore_email">' . esc_html__('Enter your donation email', 'dtac-give') . '</label></p>';
		$html .= '<p><input type="email" id="dtac_give_restore_email" name="dtac_give_restore_email" required="required" /></p>';
		$html .= '<p><button type="submit">' . esc_html__('Email me a restore link', 'dtac-give') . '</button></p>';
		$html .= '</form></div>';

		/**
		 * Filter the "Already donated?" restore form markup.
		 *
		 * @since 3.0.0
		 *
		 * @param string $html    Form markup.
		 * @param string $message Status notice currently shown, if any.
		 */
		return (string) apply_filters('dtac_give_restore_form_html', $html, $message);
	}

	/**
	 * Send a restore email when the posted address has qualifying donations.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function handle_form(): void
	{

		if (empty($_POST[self::FORM_NONCE_FIELD]) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::FORM_NONCE_FIELD])), self::FORM_NONCE_ACTION)) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$email = isset($_POST['dtac_give_restore_email']) ? sanitize_email(wp_unslash($_POST['dtac_give_restore_email'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ('' !== $email && is_email($email)) {
			$donor = $this->give_adapter()->get_donor_by_email($email);

			if ($donor && ! empty($this->give_adapter()->get_unlocked_content_ids($donor))) {
				$this->send_restore_email($email);
			}
		}

		$redirect = add_query_arg('dtac_give_restore_sent', '1', $this->current_url());
		wp_safe_redirect($redirect);
		exit;
	}

	/**
	 * Consume a signed restore link.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function handle_link(): void
	{

		$email = isset($_GET['dtac_give_email']) ? sanitize_email(wp_unslash($_GET['dtac_give_email'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$exp   = isset($_GET['dtac_give_exp']) ? absint(wp_unslash($_GET['dtac_give_exp'])) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token = isset($_GET['dtac_give_token']) ? sanitize_text_field(wp_unslash($_GET['dtac_give_token'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ('' === $email || ! is_email($email) || $exp < time() || '' === $token || ! hash_equals($this->make_token($email, $exp), $token)) {
			wp_safe_redirect(add_query_arg('dtac_give_restore_error', '1', $this->current_url_without_restore()));
			exit;
		}

		$donor = $this->give_adapter()->get_donor_by_email($email);

		if (! $donor || empty($this->give_adapter()->get_unlocked_content_ids($donor))) {
			wp_safe_redirect(add_query_arg('dtac_give_restore_error', '1', $this->current_url_without_restore()));
			exit;
		}

		dtac_give_remember_guest_access($email);

		/**
		 * Fires after a signed restore link has been accepted.
		 *
		 * @since 3.0.0
		 *
		 * @param string $email Donor email.
		 */
		do_action('dtac_give_access_restored', $email);

		wp_safe_redirect(add_query_arg('dtac_give_restore_ok', '1', $this->current_url_without_restore()));
		exit;
	}

	/**
	 * Mail a signed restore URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email Donor email.
	 *
	 * @return void
	 */
	private function send_restore_email(string $email): void
	{

		/**
		 * Filter how long a restore link stays valid, in seconds.
		 *
		 * @since 3.0.0
		 *
		 * @param int    $lifetime Link lifetime in seconds. Default one hour.
		 * @param string $email    Donor email.
		 */
		$lifetime = absint(apply_filters('dtac_give_restore_link_lifetime', HOUR_IN_SECONDS, $email));
		$exp      = time() + $lifetime;
		$link     = add_query_arg(
			array(
				self::QUERY_FLAG  => '1',
				'dtac_give_email' => $email,
				'dtac_give_exp'   => $exp,
				'dtac_give_token' => $this->make_token($email, $exp),
			),
			$this->current_url_without_restore()
		);

		$subject = esc_html__('Restore your donated content access', 'dtac-give');
		$body    = sprintf(
			/* translators: 1: signed restore URL, 2: human-readable link lifetime */
			esc_html__('Use this link to restore access. It expires in %2$s: %1$s', 'dtac-give'),
			esc_url($link),
			human_time_diff(time(), $exp)
		);

		/**
		 * Filter the restore email subject.
		 *
		 * @since 3.0.0
		 *
		 * @param string $subject Subject line.
		 * @param string $email   Donor email.
		 */
		$subject = (string) apply_filters('dtac_give_restore_email_subject', $subject, $email);

		/**
		 * Filter the restore email body.
		 *
		 * @since 3.0.0
		 *
		 * @param string $body  Email body.
		 * @param string $email Donor email.
		 * @param string $link  Signed restore URL.
		 */
		$body = (string) apply_filters('dtac_give_restore_email_body', $body, $email, $link);

		/**
		 * Filter the restore email headers.
		 *
		 * @since 3.0.0
		 *
		 * @param array  $headers Mail headers.
		 * @param string $email   Donor email.
		 */
		$headers = (array) apply_filters('dtac_give_restore_email_headers', array(), $email);

		wp_mail($email, $subject, $body, $headers);
	}

	/**
	 * HMAC token for an email and expiry.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email Donor email.
	 * @param int    $exp   Unix expiry.
	 *
	 * @return string
	 */
	private function make_token(string $email, int $exp): string
	{

		$salt = function_exists('wp_salt') ? wp_salt('nonce') : 'dtac-give';

		return hash_hmac('sha256', $email . '|' . $exp, $salt);
	}

	/**
	 * Current request URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	private function current_url(): string
	{

		$uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$uri = is_string($uri) ? $uri : '/';

		if (0 !== strpos($uri, '/') || 0 === strpos($uri, '//')) {
			$uri = '/';
		}

		if (function_exists('wp_validate_redirect')) {
			$uri = wp_validate_redirect($uri, '/');
		}

		return esc_url_raw(home_url($uri));
	}

	/**
	 * Current URL without restore query args.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	private function current_url_without_restore(): string
	{

		return remove_query_arg(
			array(
				self::QUERY_FLAG,
				'dtac_give_email',
				'dtac_give_exp',
				'dtac_give_token',
				'dtac_give_restore_sent',
				'dtac_give_restore_ok',
				'dtac_give_restore_error',
			),
			$this->current_url()
		);
	}
}
