<?php

/**
 * This file contains all the functions that are common for both frontend
 * and backend
 *
 * @since  1.0.0
 */

defined('ABSPATH') || exit;

global $give;

/**
 * [$give]
 *
 * Save Core Give plugin class object in a
 * global variable $give.
 *
 * @var [object]
 */
$give = function_exists('Give') ? Give() : null;

/**
 * Give compatibility adapter.
 *
 * @since 3.0.0
 *
 * @return \DTAC\Give\Give_Adapter
 */
function dtac_give_adapter()
{

	return \DTAC\Give\Give_Adapter::instance();
}

/**
 * Sanitize a restricted-content identifier.
 *
 * Accepted values: numeric post IDs, "site", "c{term_id}", or a public CPT slug.
 *
 * @since 3.0.0
 *
 * @param mixed $content_id Raw content identifier.
 *
 * @return string
 */
function dtac_give_sanitize_content_id($content_id): string
{

	if (is_int($content_id) || (is_string($content_id) && is_numeric($content_id))) {
		$post_id = absint($content_id);

		return ($post_id > 0) ? (string) $post_id : '';
	}

	$content_id = sanitize_text_field((string) $content_id);

	if ('' === $content_id) {
		return '';
	}

	if ('site' === $content_id) {
		return 'site';
	}

	if (0 === strpos($content_id, 'c') && ('' === substr($content_id, 1) || is_numeric(substr($content_id, 1)))) {
		$term_id = absint(substr($content_id, 1));

		return ($term_id > 0) ? 'c' . $term_id : '';
	}

	$slug = sanitize_key($content_id);

	return ('' !== $slug) ? $slug : '';
}

/**
 * Normalize a settings list of IDs or slugs to strings.
 *
 * @since 3.0.0
 *
 * @param mixed $values Raw settings value.
 *
 * @return string[]
 */
function dtac_give_normalize_id_list($values): array
{

	if (! is_array($values)) {
		$values = ('' === $values || false === $values || null === $values) ? array() : array($values);
	}

	$normalized = array();

	foreach ($values as $value) {
		if (is_int($value) || (is_string($value) && is_numeric($value))) {
			$id = absint($value);

			if ($id > 0) {
				$normalized[] = (string) $id;
			}

			continue;
		}

		$value = sanitize_text_field((string) $value);

		if ('' !== $value) {
			$normalized[] = $value;
		}
	}

	return array_values(array_unique($normalized));
}

/**
 * Allowed restriction-type values for `dtac_give_restrict_access_to`.
 *
 * @since 3.0.0
 *
 * @return string[]
 */
function dtac_give_allowed_restriction_types(): array
{

	return array('pages', 'posts', 'cats', 'cpt', 'ctax');
}

/**
 * Sanitize posted `dtac_give_settings` values.
 *
 * Unknown keys are dropped. Existing option key names are unchanged.
 *
 * @since 3.0.0
 *
 * @param array $input Raw settings, typically from $_POST.
 *
 * @return array
 */
function dtac_give_sanitize_settings(array $input): array
{

	$clean = array();

	if (array_key_exists('dtac_give_restrict_access_give_form_id', $input)) {
		$clean['dtac_give_restrict_access_give_form_id'] = (string) absint($input['dtac_give_restrict_access_give_form_id']);
	}

	if (array_key_exists('dtac_give_restrict_message', $input)) {
		$clean['dtac_give_restrict_message'] = wp_kses_post((string) $input['dtac_give_restrict_message']);
	}

	if (array_key_exists('dtac_give_restrict_access_to', $input)) {
		$types = $input['dtac_give_restrict_access_to'];

		if (! is_array($types)) {
			$types = ('' === $types || false === $types || null === $types || 'none' === $types) ? array() : array($types);
		}

		$types = array_map('sanitize_key', $types);
		$clean['dtac_give_restrict_access_to'] = array_values(array_intersect($types, dtac_give_allowed_restriction_types()));
	}

	$id_list_keys = array(
		'dtac_give_restrict_access_to_pages',
		'dtac_give_restrict_access_to_posts',
		'dtac_give_restrict_access_to_cats',
		'dtac_give_restrict_access_to_custom_tax',
		'dtac_give_access_to_pages',
	);

	foreach ($id_list_keys as $list_key) {
		if (array_key_exists($list_key, $input)) {
			$clean[$list_key] = dtac_give_normalize_id_list($input[$list_key]);
		}
	}

	if (array_key_exists('dtac_give_restrict_access_to_cpt', $input)) {
		$cpts = dtac_give_normalize_id_list($input['dtac_give_restrict_access_to_cpt']);
		$cpts = array_map('sanitize_key', $cpts);
		$clean['dtac_give_restrict_access_to_cpt'] = array_values(array_filter($cpts));
	}

	if (array_key_exists('dtac_give_min_amount', $input)) {
		$amount = dtac_give_sanitize_amount($input['dtac_give_min_amount']);
		$clean['dtac_give_min_amount'] = (0.0 === $amount) ? '0' : (string) $amount;
	}

	if (array_key_exists('dtac_give_access_expires_days', $input)) {
		$clean['dtac_give_access_expires_days'] = (string) absint($input['dtac_give_access_expires_days']);
	}

	if (array_key_exists('dtac_give_leak_mode', $input)) {
		$mode = sanitize_key((string) $input['dtac_give_leak_mode']);
		$clean['dtac_give_leak_mode'] = in_array($mode, array('hide', 'excerpt'), true) ? $mode : 'hide';
	}

	if (array_key_exists('dtac_give_restrict_website', $input)) {
		$clean['dtac_give_restrict_website'] = ('yes' === $input['dtac_give_restrict_website']) ? 'yes' : 'no';
	}

	return $clean;
}

/**
 * Setting keys that restrict content, keyed by content-ID shape.
 *
 * @since 3.0.0
 *
 * @return array<string,string>
 */
function dtac_give_restriction_setting_keys(): array
{

	return array(
		'pages' => 'dtac_give_restrict_access_to_pages',
		'posts' => 'dtac_give_restrict_access_to_posts',
		'cats'  => 'dtac_give_restrict_access_to_cats',
		'cpt'   => 'dtac_give_restrict_access_to_cpt',
		'ctax'  => 'dtac_give_restrict_access_to_custom_tax',
	);
}

/**
 * Enabled restriction types from settings.
 *
 * @since 3.0.0
 *
 * @return string[]
 */
function dtac_give_enabled_restriction_types(): array
{

	$types = dtac_give_get_settings('dtac_give_restrict_access_to');

	if (! is_array($types)) {
		$types = array();
	}

	return array_values(
		array_filter(
			$types,
			static function ($type) {
				return is_string($type) && '' !== $type;
			}
		)
	);
}

/**
 * Whether whole-site restriction is enabled.
 *
 * @since 3.0.0
 *
 * @return bool
 */
function dtac_give_is_whole_site_restricted(): bool
{

	return 'yes' === dtac_give_get_settings('dtac_give_restrict_website');
}

/**
 * Public post types that can be restricted by ID or shortcode.
 *
 * @since 3.0.0
 *
 * @return string[]
 */
function dtac_give_restrictable_post_types(): array
{

	$types = get_post_types(
		array(
			'public' => true,
		),
		'names'
	);

	if (! is_array($types)) {
		$types = array('post', 'page');
	}

	unset($types['give_forms'], $types['attachment']);

	/**
	 * Filter public post types that DTAC may restrict.
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $types Post type slugs.
	 */
	return array_values((array) apply_filters('dtac_give_restrictable_post_types', $types));
}

/**
 * Whether a post contains the restrict-content shortcode.
 *
 * @since 3.0.0
 *
 * @param int $post_id Post ID.
 *
 * @return bool
 */
function dtac_give_post_has_restrict_shortcode(int $post_id): bool
{

	if ($post_id <= 0) {
		return false;
	}

	$post = get_post($post_id);

	if (! $post instanceof \WP_Post) {
		return false;
	}

	return has_shortcode((string) $post->post_content, 'cip_donate_to_access_content');
}

/**
 * Whether a post contains the restricted-content block.
 *
 * @since 3.0.0
 *
 * @param int $post_id Post ID.
 *
 * @return bool
 */
function dtac_give_post_has_restrict_block(int $post_id): bool
{

	if ($post_id <= 0) {
		return false;
	}

	$post = get_post($post_id);

	if (! $post instanceof \WP_Post) {
		return false;
	}

	if (function_exists('has_block') && has_block('dtac/restricted-content', $post)) {
		return true;
	}

	return false !== strpos((string) $post->post_content, '<!-- wp:dtac/restricted-content');
}

/**
 * Per-post restriction meta keys.
 *
 * @since 3.0.0
 *
 * @return array<string,string>
 */
function dtac_give_post_meta_keys(): array
{

	return array(
		'restrict'    => '_dtac_give_restrict',
		'form_id'     => '_dtac_give_form_id',
		'min_amount'  => '_dtac_give_min_amount',
		'expiry_days' => '_dtac_give_expiry_days',
	);
}

/**
 * Per-post restriction mode: yes, no, or empty to inherit globals.
 *
 * @since 3.0.0
 *
 * @param int $post_id Post ID.
 *
 * @return string
 */
function dtac_give_get_post_restriction_mode(int $post_id): string
{

	if ($post_id <= 0) {
		return '';
	}

	$keys = dtac_give_post_meta_keys();
	$mode = get_post_meta($post_id, $keys['restrict'], true);
	$mode = is_string($mode) ? $mode : '';

	return in_array($mode, array('yes', 'no'), true) ? $mode : '';
}

/**
 * Whether a post is restricted by its metabox toggle.
 *
 * @since 3.0.0
 *
 * @param int $post_id Post ID.
 *
 * @return bool
 */
function dtac_give_post_has_metabox_restriction(int $post_id): bool
{

	return 'yes' === dtac_give_get_post_restriction_mode($post_id);
}

/**
 * Sanitize a money amount.
 *
 * @since 3.0.0
 *
 * @param mixed $amount Raw amount.
 *
 * @return float
 */
function dtac_give_sanitize_amount($amount): float
{

	if (is_string($amount)) {
		$amount = str_replace(',', '', $amount);
	}

	if (! is_numeric($amount)) {
		return 0.0;
	}

	$amount = (float) $amount;

	return ($amount > 0) ? round($amount, 2) : 0.0;
}

/**
 * Minimum donation amount for a content ID.
 *
 * Per-post meta overrides the global default. Existing setting keys are unchanged.
 *
 * @since 3.0.0
 *
 * @param mixed $content_id Content identifier.
 *
 * @return float
 */
function dtac_give_get_min_amount($content_id = ''): float
{

	$content_id = dtac_give_sanitize_content_id($content_id);
	$amount     = 0.0;

	if (is_numeric($content_id)) {
		$keys  = dtac_give_post_meta_keys();
		$saved = get_post_meta((int) $content_id, $keys['min_amount'], true);

		if ('' !== $saved && false !== $saved && null !== $saved) {
			$amount = dtac_give_sanitize_amount($saved);
		}
	}

	if ($amount <= 0.0) {
		$amount = dtac_give_sanitize_amount(dtac_give_get_settings('dtac_give_min_amount'));
	}

	/**
	 * Filter the minimum donation amount required to unlock content.
	 *
	 * @since 3.0.0
	 *
	 * @param float  $amount     Minimum amount.
	 * @param string $content_id Sanitized content ID.
	 */
	return (float) apply_filters('dtac_give_min_amount', $amount, $content_id);
}

/**
 * Access expiry in days for a content ID. Zero means never expires.
 *
 * @since 3.0.0
 *
 * @param mixed $content_id Content identifier.
 *
 * @return int
 */
function dtac_give_get_expiry_days($content_id = ''): int
{

	$content_id = dtac_give_sanitize_content_id($content_id);
	$days       = -1;

	if (is_numeric($content_id)) {
		$keys  = dtac_give_post_meta_keys();
		$saved = get_post_meta((int) $content_id, $keys['expiry_days'], true);

		if ('' !== $saved && false !== $saved && null !== $saved) {
			$days = absint($saved);
		}
	}

	if ($days < 0) {
		$days = absint(dtac_give_get_settings('dtac_give_access_expires_days'));
	}

	/**
	 * Filter access expiry in days.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $days       Days until access expires.
	 * @param string $content_id Sanitized content ID.
	 */
	$days = (int) apply_filters('dtac_give_expiry_days', $days, $content_id);

	return ($days > 0) ? $days : 0;
}

/**
 * Give form ID used to unlock a content ID.
 *
 * @since 3.0.0
 *
 * @param mixed $content_id Content identifier.
 *
 * @return int
 */
function dtac_give_get_form_id_for_content($content_id = ''): int
{

	$content_id = dtac_give_sanitize_content_id($content_id);
	$form_id    = 0;

	if (is_numeric($content_id)) {
		$keys    = dtac_give_post_meta_keys();
		$form_id = absint(get_post_meta((int) $content_id, $keys['form_id'], true));
	}

	if ($form_id <= 0) {
		$form_id = absint(dtac_give_get_settings('dtac_give_restrict_access_give_form_id'));
	}

	return $form_id;
}

/**
 * Whether a completed donation still unlocks a content ID.
 *
 * @since 3.0.0
 *
 * @param int    $donation_id Donation ID.
 * @param string $content_id  Content identifier.
 *
 * @return bool
 */
function dtac_give_donation_unlocks_content(int $donation_id, string $content_id): bool
{

	if ($donation_id <= 0) {
		return false;
	}

	$content_id = dtac_give_sanitize_content_id($content_id);

	if ('' === $content_id) {
		return false;
	}

	$adapter = dtac_give_adapter();
	$minimum = dtac_give_get_min_amount($content_id);

	if ($minimum > 0.0 && $adapter->get_donation_amount($donation_id) < $minimum) {
		return false;
	}

	$days = dtac_give_get_expiry_days($content_id);

	if ($days > 0) {
		$donated = $adapter->get_donation_timestamp($donation_id);

		if ($donated <= 0) {
			return false;
		}

		$day = defined('DAY_IN_SECONDS') ? DAY_IN_SECONDS : 86400;

		if ((time() - $donated) > ($days * $day)) {
			return false;
		}
	}

	return true;
}

/**
 * Human-readable label for a content ID.
 *
 * @since 3.0.0
 *
 * @param string $content_id Content identifier.
 *
 * @return string
 */
function dtac_give_get_content_label(string $content_id): string
{

	$content_id = dtac_give_sanitize_content_id($content_id);

	if ('' === $content_id) {
		return '';
	}

	if ('site' === $content_id) {
		return esc_html__('Entire website', 'dtac-give');
	}

	if (is_numeric($content_id)) {
		$title = get_the_title((int) $content_id);

		return is_string($title) && '' !== $title ? $title : sprintf(
			/* translators: %s: post ID */
			esc_html__('Content #%s', 'dtac-give'),
			$content_id
		);
	}

	if (0 === strpos($content_id, 'c') && is_numeric(substr($content_id, 1))) {
		$term = get_term((int) substr($content_id, 1));

		if ($term instanceof \WP_Term) {
			return $term->name;
		}

		return sprintf(
			/* translators: %s: term ID */
			esc_html__('Term #%s', 'dtac-give'),
			substr($content_id, 1)
		);
	}

	return $content_id;
}

/**
 * Permalink for an unlocked content ID when one exists.
 *
 * @since 3.0.0
 *
 * @param string $content_id Content identifier.
 *
 * @return string
 */
function dtac_give_get_content_url(string $content_id): string
{

	$content_id = dtac_give_sanitize_content_id($content_id);

	if (is_numeric($content_id)) {
		$url = get_permalink((int) $content_id);

		return is_string($url) ? $url : '';
	}

	if (0 === strpos($content_id, 'c') && is_numeric(substr($content_id, 1))) {
		$url = get_term_link((int) substr($content_id, 1));

		return is_string($url) ? $url : '';
	}

	if ('site' === $content_id) {
		$url = home_url('/');

		return is_string($url) ? $url : '';
	}

	return '';
}

/**
 * Guest restore-access cookie email, if present and valid.
 *
 * @since 3.0.0
 *
 * @return string
 */
function dtac_give_get_guest_access_email(): string
{

	$cookie = \DTAC\Give\Give_Adapter::GUEST_ACCESS_COOKIE;

	if (empty($_COOKIE[$cookie])) {
		return '';
	}

	$email = sanitize_email(wp_unslash($_COOKIE[$cookie]));

	return is_email($email) ? $email : '';
}

/**
 * Remember a guest email so later visits can restore donor access.
 *
 * @since 3.0.0
 *
 * @param string $email Donor email.
 *
 * @return void
 */
function dtac_give_remember_guest_access(string $email): void
{

	$email = sanitize_email($email);

	if ('' === $email || ! is_email($email)) {
		return;
	}

	$cookie = \DTAC\Give\Give_Adapter::GUEST_ACCESS_COOKIE;
	$expire = time() + (30 * (defined('DAY_IN_SECONDS') ? DAY_IN_SECONDS : 86400));
	$path   = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
	$domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';

	if (! headers_sent()) {
		setcookie(
			$cookie,
			$email,
			array(
				'expires'  => $expire,
				'path'     => $path,
				'domain'   => $domain,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	$_COOKIE[$cookie] = $email;

	dtac_give_adapter()->set_session_email($email);
}

/**
 * Render restricted content for guests (form or message).
 *
 * @since 3.0.0
 *
 * @param int    $form_id    Give form ID.
 * @param string $show       `form` or `message`.
 * @param mixed  $content_id Content identifier.
 *
 * @return string
 */
function dtac_give_get_restriction_output(int $form_id, string $show = 'form', $content_id = ''): string
{

	if ($form_id <= 0) {
		$form_id = dtac_give_get_form_id_for_content($content_id);
	}

	if ($form_id <= 0) {
		return '';
	}

	$content_id = dtac_give_sanitize_content_id($content_id);

	if ('' === $content_id) {
		$content_id = (string) dtac_give_get_current_object_id();
	}

	if ('' !== $content_id) {
		dtac_give_remember_pending_content($content_id);
	}

	if ('message' === $show) {
		$message = dtac_give_get_settings('dtac_give_restrict_message');
		$message = is_string($message) ? $message : '';
		$link    = esc_url(dtac_give_donation_form_url($form_id, $content_id));
		$output  = wp_kses_post(str_replace('%%donation_form_url%%', $link, $message));
	} else {
		$output = do_shortcode('[give_form id="' . absint($form_id) . '"]');
	}

	if (class_exists('\\DTAC\\Frontend\\Magic_Link', false)) {
		$output .= \DTAC\Frontend\Magic_Link::form_html();
	}

	return $output;
}

/**
 * HTML list of content the current donor has unlocked.
 *
 * @since 3.0.0
 *
 * @return string
 */
function dtac_give_get_unlocked_content_html(): string
{

	$donor = dtac_give_get_donor();

	if (! $donor) {
		return '<p>' . esc_html__('No unlocked content found.', 'dtac-give') . '</p>';
	}

	$unlocked = dtac_give_adapter()->get_unlocked_content_ids($donor);

	if (empty($unlocked)) {
		return '<p>' . esc_html__('No unlocked content found.', 'dtac-give') . '</p>';
	}

	$html = '<ul class="dtac-give-unlocked-content">';

	foreach ($unlocked as $content_id) {
		$label = dtac_give_get_content_label((string) $content_id);
		$url   = dtac_give_get_content_url((string) $content_id);

		if ('' !== $url) {
			$html .= '<li><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></li>';
		} else {
			$html .= '<li>' . esc_html($label) . '</li>';
		}
	}

	$html .= '</ul>';

	return $html;
}

/**
 * Whether a sanitized content ID is allowed to be granted.
 *
 * Allowed: `site` when whole-site restriction is on; numeric IDs in
 * restricted pages/posts or shortcode pages; `c{term_id}` in category or
 * custom-tax lists; CPT slugs in the CPT list.
 *
 * @since 3.0.0
 *
 * @param mixed $content_id Content identifier.
 *
 * @return bool
 */
function dtac_give_is_grantable_content_id($content_id): bool
{

	$content_id = dtac_give_sanitize_content_id($content_id);

	if ('' === $content_id) {
		return false;
	}

	if ('site' === $content_id) {
		$allowed = dtac_give_is_whole_site_restricted();

		/**
		 * Filter whether a content ID may be granted.
		 *
		 * @since 3.0.0
		 *
		 * @param bool   $allowed    Whether the ID is grantable.
		 * @param string $content_id Sanitized content ID.
		 */
		return (bool) apply_filters('dtac_give_is_grantable_content_id', $allowed, $content_id);
	}

	$types = dtac_give_enabled_restriction_types();
	$keys  = dtac_give_restriction_setting_keys();

	if (is_numeric($content_id)) {
		$allowed = false;

		if (in_array('pages', $types, true) && in_array($content_id, dtac_give_normalize_id_list(dtac_give_get_settings($keys['pages'])), true)) {
			$allowed = true;
		}

		if (! $allowed && in_array('posts', $types, true) && in_array($content_id, dtac_give_normalize_id_list(dtac_give_get_settings($keys['posts'])), true)) {
			$allowed = true;
		}

		if (! $allowed && dtac_give_post_has_restrict_shortcode((int) $content_id)) {
			$allowed = true;
		}

		if (! $allowed && dtac_give_post_has_restrict_block((int) $content_id)) {
			$allowed = true;
		}

		if (! $allowed && dtac_give_post_has_metabox_restriction((int) $content_id)) {
			$allowed = true;
		}

		if ('no' === dtac_give_get_post_restriction_mode((int) $content_id)) {
			$allowed = false;
		}

		return (bool) apply_filters('dtac_give_is_grantable_content_id', $allowed, $content_id);
	}

	if (0 === strpos($content_id, 'c') && is_numeric(substr($content_id, 1))) {
		$term_id = substr($content_id, 1);
		$allowed = false;

		if (in_array('cats', $types, true) && in_array($term_id, dtac_give_normalize_id_list(dtac_give_get_settings($keys['cats'])), true)) {
			$allowed = true;
		}

		if (! $allowed && in_array('ctax', $types, true) && in_array($term_id, dtac_give_normalize_id_list(dtac_give_get_settings($keys['ctax'])), true)) {
			$allowed = true;
		}

		return (bool) apply_filters('dtac_give_is_grantable_content_id', $allowed, $content_id);
	}

	$allowed = in_array('cpt', $types, true) && in_array($content_id, dtac_give_normalize_id_list(dtac_give_get_settings($keys['cpt'])), true);

	return (bool) apply_filters('dtac_give_is_grantable_content_id', $allowed, $content_id);
}

/**
 * Whether a post is currently restricted by settings or shortcode.
 *
 * @since 3.0.0
 *
 * @param int $post_id Post ID.
 *
 * @return bool
 */
function dtac_give_is_post_restricted(int $post_id): bool
{

	if ($post_id <= 0) {
		return false;
	}

	$post_type = get_post_type($post_id);

	if ('give_forms' === $post_type) {
		return false;
	}

	if (dtac_give_is_whole_site_restricted()) {
		$access_pages = dtac_give_normalize_id_list(dtac_give_get_settings('dtac_give_access_to_pages'));
		$form_id      = (int) dtac_give_get_settings('dtac_give_restrict_access_give_form_id');

		if ((string) $post_id === (string) $form_id || in_array((string) $post_id, $access_pages, true)) {
			return false;
		}

		return true;
	}

	if ('no' === dtac_give_get_post_restriction_mode($post_id)) {
		return false;
	}

	if (dtac_give_post_has_metabox_restriction($post_id)) {
		return true;
	}

	if (dtac_give_post_has_restrict_shortcode($post_id) || dtac_give_post_has_restrict_block($post_id)) {
		return true;
	}

	$types     = dtac_give_enabled_restriction_types();
	$keys      = dtac_give_restriction_setting_keys();
	$post_id_s = (string) $post_id;
	$post_type = get_post_type($post_id);

	if (in_array('pages', $types, true) && 'page' === $post_type && in_array($post_id_s, dtac_give_normalize_id_list(dtac_give_get_settings($keys['pages'])), true)) {
		return true;
	}

	if (in_array('posts', $types, true) && 'post' === $post_type && in_array($post_id_s, dtac_give_normalize_id_list(dtac_give_get_settings($keys['posts'])), true)) {
		return true;
	}

	if (in_array('cpt', $types, true) && is_string($post_type) && in_array($post_type, dtac_give_normalize_id_list(dtac_give_get_settings($keys['cpt'])), true)) {
		return true;
	}

	if (in_array('cats', $types, true)) {
		$cat_ids = array_map('absint', dtac_give_normalize_id_list(dtac_give_get_settings($keys['cats'])));
		$cat_ids = array_filter($cat_ids);

		if (! empty($cat_ids) && has_term($cat_ids, 'category', $post_id)) {
			return true;
		}
	}

	if (in_array('ctax', $types, true)) {
		$term_ids = array_map('absint', dtac_give_normalize_id_list(dtac_give_get_settings($keys['ctax'])));
		$term_ids = array_filter($term_ids);

		if (! empty($term_ids)) {
			$taxonomies = dtac_give_get_custom_taxs_names();

			foreach ($taxonomies as $taxonomy) {
				if (has_term($term_ids, $taxonomy, $post_id)) {
					return true;
				}
			}
		}
	}

	return false;
}

/**
 * Whether the current visitor may view a restricted post.
 *
 * @since 3.0.0
 *
 * @param int $post_id Post ID.
 *
 * @return bool
 */
function dtac_give_visitor_can_view_post(int $post_id): bool
{

	if ($post_id <= 0) {
		return false;
	}

	if (! dtac_give_is_post_restricted($post_id)) {
		return true;
	}

	$donor = dtac_give_get_donor();

	if (! $donor) {
		return false;
	}

	$unlocked = dtac_give_adapter()->get_unlocked_content_ids($donor);

	if (in_array('site', $unlocked, true) || in_array((string) $post_id, $unlocked, true)) {
		return true;
	}

	$post_type = get_post_type($post_id);

	if (is_string($post_type) && '' !== $post_type && in_array($post_type, $unlocked, true)) {
		return true;
	}

	foreach ($unlocked as $content_id) {
		if (0 !== strpos((string) $content_id, 'c') || ! is_numeric(substr((string) $content_id, 1))) {
			continue;
		}

		$term_id = absint(substr((string) $content_id, 1));

		if ($term_id <= 0) {
			continue;
		}

		if (has_term($term_id, 'category', $post_id)) {
			return true;
		}

		foreach (dtac_give_get_custom_taxs_names() as $taxonomy) {
			if (has_term($term_id, $taxonomy, $post_id)) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Leak-prevention mode: hide or excerpt.
 *
 * Adds `dtac_give_leak_mode` with default `hide`. Existing keys are unchanged.
 *
 * @since 3.0.0
 *
 * @return string
 */
function dtac_give_get_leak_mode(): string
{

	$mode = dtac_give_get_settings('dtac_give_leak_mode');
	$mode = is_string($mode) ? $mode : '';

	if (! in_array($mode, array('hide', 'excerpt'), true)) {
		$mode = 'hide';
	}

	/**
	 * Filter REST/feed/search leak mode.
	 *
	 * @since 3.0.0
	 *
	 * @param string $mode `hide` or `excerpt`.
	 */
	$mode = (string) apply_filters('dtac_give_leak_mode', $mode);

	return in_array($mode, array('hide', 'excerpt'), true) ? $mode : 'hide';
}

/**
 * Grant-flow nonce action.
 *
 * @since 3.0.0
 *
 * @return string
 */
function dtac_give_grant_nonce_action(): string
{

	return 'dtac_give_grant_access';
}

/**
 * Grant-flow nonce field name.
 *
 * @since 3.0.0
 *
 * @return string
 */
function dtac_give_grant_nonce_field(): string
{

	return 'dtac_give_grant_nonce';
}

/**
 * Current singular post ID, or 0.
 *
 * @since 3.0.0
 *
 * @return int
 */
function dtac_give_get_current_object_id(): int
{

	if (is_singular()) {
		$object_id = get_queried_object_id();

		if ($object_id > 0) {
			return (int) $object_id;
		}
	}

	$object = get_queried_object();

	if ($object instanceof \WP_Post) {
		return (int) $object->ID;
	}

	return 0;
}

/**
 * Remember a pending content ID for v3/v4 form grants.
 *
 * @since 3.0.0
 *
 * @param mixed $content_id Content identifier.
 *
 * @return void
 */
function dtac_give_remember_pending_content($content_id): void
{

	$content_id = dtac_give_sanitize_content_id($content_id);

	if ('' === $content_id) {
		return;
	}

	$cookie = \DTAC\Give\Give_Adapter::PENDING_CONTENT_COOKIE;

	if (! headers_sent()) {
		$expire = time() + HOUR_IN_SECONDS;
		$path   = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
		$domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';

		setcookie(
			$cookie,
			$content_id,
			array(
				'expires'  => $expire,
				'path'     => $path,
				'domain'   => $domain,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	$_COOKIE[$cookie] = $content_id;
}

/**
 * Pending content ID stored for the current visitor.
 *
 * @since 3.0.0
 *
 * @return string
 */
function dtac_give_get_pending_content(): string
{

	$cookie = \DTAC\Give\Give_Adapter::PENDING_CONTENT_COOKIE;

	if (empty($_COOKIE[$cookie])) {
		return '';
	}

	return dtac_give_sanitize_content_id(wp_unslash($_COOKIE[$cookie]));
}

/**
 * Clear the pending content cookie after a successful grant.
 *
 * @since 3.0.0
 *
 * @return void
 */
function dtac_give_clear_pending_content(): void
{

	$cookie = \DTAC\Give\Give_Adapter::PENDING_CONTENT_COOKIE;

	if (! headers_sent()) {
		$path   = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
		$domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';

		setcookie(
			$cookie,
			'',
			array(
				'expires'  => time() - HOUR_IN_SECONDS,
				'path'     => $path,
				'domain'   => $domain,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	unset($_COOKIE[$cookie]);
}

/**
 * Content ID from a URL query string.
 *
 * @since 3.0.0
 *
 * @param string $url URL to parse.
 *
 * @return string
 */
function dtac_give_content_id_from_url(string $url): string
{

	if ('' === $url) {
		return '';
	}

	$query = wp_parse_url($url, PHP_URL_QUERY);

	if (! is_string($query) || '' === $query) {
		return '';
	}

	$args = array();
	wp_parse_str($query, $args);

	if (empty($args['dtac_give_content'])) {
		return '';
	}

	return dtac_give_sanitize_content_id($args['dtac_give_content']);
}

/**
 * Resolve the content ID being unlocked for the current donation.
 *
 * @since 3.0.0
 *
 * @return string
 */
function dtac_give_get_requested_content_id(): string
{

	$sources = array($_POST, $_GET, $_REQUEST); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended

	foreach ($sources as $source) {
		if (! empty($source['dtac_give_content'])) {
			$content_id = dtac_give_sanitize_content_id(wp_unslash($source['dtac_give_content']));

			if ('' !== $content_id) {
				return $content_id;
			}
		}
	}

	$referer = wp_get_referer();

	if ($referer) {
		$content_id = dtac_give_content_id_from_url($referer);

		if ('' !== $content_id) {
			return $content_id;
		}
	}

	if (! empty($_SERVER['HTTP_REFERER'])) {
		$content_id = dtac_give_content_id_from_url(esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])));

		if ('' !== $content_id) {
			return $content_id;
		}
	}

	if (function_exists('give_get_purchase_session')) {
		$purchase = give_get_purchase_session();

		if (is_array($purchase)) {
			if (! empty($purchase['post_data']['dtac_give_content'])) {
				$content_id = dtac_give_sanitize_content_id($purchase['post_data']['dtac_give_content']);

				if ('' !== $content_id) {
					return $content_id;
				}
			}

			if (! empty($purchase['formEntry']) && is_object($purchase['formEntry']) && ! empty($purchase['formEntry']->currentUrl)) {
				$content_id = dtac_give_content_id_from_url((string) $purchase['formEntry']->currentUrl);

				if ('' !== $content_id) {
					return $content_id;
				}
			}
		}
	}

	return dtac_give_get_pending_content();
}


/**
 * Get settings for the plugin
 *
 * @param string $key Setting name key.
 *
 * @since 1.0.0
 *
 * @return mixed
 */
function dtac_give_get_settings(string $key = '')
{

	$settings = get_option('dtac_give_settings', array());

	if (! is_array($settings)) {
		$settings = array();
	}

	$settings = (array) apply_filters('dtac_give_get_settings', $settings);

	if ('' === $key) {
		return $settings;
	}

	return $settings[$key] ?? '';
}

if (! function_exists('dtac_give_get_donor')) :

	/**
	 * Get the donor by id or email
	 *
	 * Retrive a donor by id or email and according by it's login state
	 *
	 * @since 3.0.0 Return null when no donor is found.
	 * @since 1.0.0
	 *
	 * @return object|null
	 */
	function dtac_give_get_donor()
	{

		$donor = dtac_give_adapter()->get_current_donor();

		/**
		 * Filter the current donor lookup field for backward compatibility.
		 *
		 * @since 1.0.0
		 *
		 * @param string $field Donor lookup field.
		 */
		$field = apply_filters('dtac_give_donor_field', is_user_logged_in() ? 'user_id' : 'email');

		/**
		 * Filter the current donor lookup value for backward compatibility.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $value Donor lookup value.
		 */
		$value = apply_filters(
			'dtac_give_donor_value',
			is_user_logged_in() ? get_current_user_id() : dtac_give_adapter()->get_session_email()
		);

		if (has_filter('dtac_give_donor_field') || has_filter('dtac_give_donor_value')) {
			if ('user_id' === $field) {
				$filtered = dtac_give_adapter()->get_donor_by_user_id((int) $value);
			} else {
				$filtered = dtac_give_adapter()->get_donor_by_email((string) $value);
			}

			if ($filtered) {
				$donor = $filtered;
			}
		}

		return $donor ? $donor : null;
	} // End function.

endif; // End if function_exists check.

/**
 * Generate Donation Form URL from form id with query args
 *
 * @since 1.0.0
 *
 * @param int $form_id ID of the form.
 * @param int $current_page_id Current Page ID.
 *
 * @return string
 */
function dtac_give_donation_form_url($form_id, $current_page_id)
{

	$content_id = dtac_give_sanitize_content_id($current_page_id);
	$form_url   = get_permalink($form_id);

	if (! is_string($form_url) || '' === $form_url) {
		return '';
	}

	$query_args = array('dtac_give_content' => $content_id);

	$query_args = apply_filters('dtac_give_redirection_query_string_array', $query_args, $query_args);

	if ('' !== $content_id) {
		dtac_give_remember_pending_content($content_id);
	}

	return add_query_arg($query_args, $form_url);
}

/**
 * [is_dtac_plugin_settings_page]
 *
 * Check if current admin page viewed is the settings page of this plugin.
 *
 * @since  1.0.0
 *
 * @return boolean
 */
function is_dtac_plugin_settings_page(): bool
{

	$is_admin_settings_page = false;

	if (isset($_GET['page']) && 'dtac' === sanitize_key(wp_unslash($_GET['page']))) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_admin_settings_page = true;
	}

	return $is_admin_settings_page;
}

/**
 * [dtac_give_get_custom_taxs]
 *
 * Get all registered custom taxonomies
 *
 * @since  1.0.0
 *
 * @return [array]
 */
function dtac_give_get_custom_taxs()
{

	$taxomonies = array();

	$args = array(); // Only get public tax and ignore built-in taxomonies.
	$args = apply_filters('dtac_give_custom_tax_args', $args, $args);

	$output = apply_filters('dtac_give_custom_tax_output_value', 'objects'); // or names.

	$taxonomies = get_taxonomies($args, $output);

	return $taxonomies;
}


/**
 * [dtac_give_get_custom_taxs_names]
 *
 * Get names of all registered taxonomies and return it in an array
 *
 * @since  1.0.0
 *
 * @return [array]
 */
function dtac_give_get_custom_taxs_names()
{

	$result = array();

	$taxonomies = dtac_give_get_custom_taxs(); // Get custom taxonomies object array.

	if ($taxonomies) {

		foreach ($taxonomies as $taxonomy) {
			if (is_object($taxonomy) && isset($taxonomy->name)) {
				$result[] = $taxonomy->name;
			}
		}
	}

	return $result;
}


if (! function_exists('dtac_give_get_donor_by_payment_id')) {

	/**
	 * [dtac_give_get_donor_by_payment_id]
	 *
	 * Get donor by payment id. Useful when using hooks and filters which have only
	 * payment id as parameter.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $payment_id ID of the payment.
	 *
	 * @return int
	 */
	function dtac_give_get_donor_by_payment_id($payment_id)
	{

		$donor = dtac_give_adapter()->get_donor_by_donation_id((int) $payment_id);

		return dtac_give_adapter()->get_donor_id($donor);
	}
} // End if function_exists check.

/**
 * Check if a class has implemented a given interface.
 *
 * @since 2.0.0
 *
 * @param string $class_name     Name of the class.
 * @param string $interface_name Name of the interface.
 *
 * @return bool
 */
function has_implemented_interface(string $class_name, string $interface_name): bool
{

	if (! class_exists($class_name) || ! interface_exists($interface_name)) {
		return false;
	}

	$class = new ReflectionClass($class_name);

	return $class->implementsInterface($interface_name);
}

/**
 * Contains an array key or array is valid.
 *
 * By default it checks for an empty and if the array is actually a
 * type of array
 *
 * @since 2.0.0
 *
 * @param mixed  $value     Array to check.
 * @param string $key       Array key to check if it exists in the array.
 * @param bool   $check_key Only check key if this is true. Default false.
 *
 * @return bool
 */
function dtac_is_valid_array($value, string $key = '', bool $check_key = false): bool
{

	if (is_array($value)) {

		if (! empty($value)) {

			if ($check_key) {

				return array_key_exists($key, $value);
			}

			return true;
		}
	}

	return false;
}

/**
 * Multi Select input types.
 *
 * Array of input types where we can select more than
 * one option.
 *
 * @since 2.0.0
 *
 * @return array
 */
function multiple_input_types(): array
{

	return array(
		'multi-select',
		'checkbox',
	);
}

/**
 * Allowed HTML tags in a string.
 *
 * Used by: wp_kses()
 *
 * @see https://codex.wordpress.org/Function_Reference/wp_kses
 *
 * @since 2.0.0
 *
 * @return array
 */
function dtac_allowed_html_tags(): array
{

	return array(
		'a'      => array(
			'href'  => array(),
			'title' => array(),
		),
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
	);
}
