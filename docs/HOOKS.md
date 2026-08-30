# Hooks reference

All hooks are prefixed `dtac_give_`. Add them from a theme `functions.php`, an
mu-plugin, or a companion plugin. Nothing here requires editing plugin files.

Text domain for any string you return: `dtac-give`.

## Contents

- [Bootstrap](#bootstrap)
- [Restriction decisions](#restriction-decisions)
- [Access grants and expiry](#access-grants-and-expiry)
- [Gate output](#gate-output)
- [Redirects and URLs](#redirects-and-urls)
- [Guest restore](#guest-restore)
- [Leak protection](#leak-protection)
- [Shortcode and block](#shortcode-and-block)
- [Donor lookup](#donor-lookup)
- [Settings and admin](#settings-and-admin)
- [Recipes](#recipes)

---

## Bootstrap

| Hook | Type | Args | Notes |
| --- | --- | --- | --- |
| `dtac_give_before_plugin_setup` | action | – | Fires before admin, frontend, and CLI modules load. |
| `dtac_give_after_plugin_setup` | action | – | Fires once every module is loaded. Safe place to add your own filters. |

## Restriction decisions

| Hook | Type | Args | Notes |
| --- | --- | --- | --- |
| `dtac_give_is_whole_site_restricted` | filter | `bool $restricted` | Toggle the whole-site gate at runtime. |
| `dtac_give_is_post_restricted` | filter | `bool $restricted`, `int $post_id` | Final say on whether a post is gated. |
| `dtac_give_visitor_can_view_post` | filter | `bool $can_view`, `int $post_id` | Final say on whether the current visitor gets through. |
| `dtac_give_is_donor_restricted` | filter | `bool $is_restricted`, `string $content_id`, `object\|null $donor` | Per content-ID donor check used by the shortcode, block, and redirects. |
| `dtac_give_should_bypass_restriction` | filter | `bool $bypass`, `mixed $content_id` | Skip the gate entirely for a request (memberships, roles, preview links). |
| `dtac_give_current_user_can_edit_post` | filter | `bool $can_edit`, `int $post_id` | Editors are never gated, so this doubles as a role bypass. |
| `dtac_give_restrictable_post_types` | filter | `string[] $types` | Post types the plugin may gate. |
| `dtac_give_is_grantable_content_id` | filter | `bool $allowed`, `string $content_id` | Anti-spoofing whitelist. Fires for `site`, numeric IDs, `c{term_id}`, and CPT slugs. |
| `dtac_give_send_nocache_headers` | filter | `bool $should_nocache`, `int $post_id` | Control the no-store headers sent on gated URLs. |

## Access grants and expiry

| Hook | Type | Args | Notes |
| --- | --- | --- | --- |
| `dtac_give_min_amount` | filter | `float $amount`, `string $content_id` | Minimum donation that unlocks a content ID. `0.0` means any amount. |
| `dtac_give_expiry_days` | filter | `int $days`, `string $content_id` | Access lifetime in days. `0` means never expires. |
| `dtac_give_form_id_for_content` | filter | `int $form_id`, `string $content_id` | Give form used to unlock a content ID. |
| `dtac_give_donation_unlocks_content` | filter | `bool $unlocks`, `int $donation_id`, `string $content_id` | Runs after the amount and expiry checks. Use it to revoke refunded donations. |
| `dtac_give_complete_donation_statuses` | filter | `string[] $statuses` | Donation statuses that count as complete. Defaults to `publish` and `complete`. |
| `dtac_give_unlocked_content_ids` | filter | `string[] $content_ids`, `object $donor` | Everything a donor has unlocked. Values are re-sanitized afterwards. |
| `dtac_give_should_grant_access` | filter | `bool $grant`, `int $donation_id`, `string $content_id` | Return `false` to skip writing unlock meta. |
| `dtac_give_before_access_granted` | action | `int $donation_id`, `string $content_id` | Fires before unlock meta is written. |
| `dtac_give_access_granted` | action | `int $donation_id`, `string $content_id`, `object\|null $donor` | Fires after a donation unlocks content. Good for CRM sync or custom email. |

## Gate output

| Hook | Type | Args | Notes |
| --- | --- | --- | --- |
| `dtac_give_restriction_output` | filter | `string $output`, `int $form_id`, `string $show`, `string $content_id` | The full gate markup (form or message plus restore form). |
| `dtac_give_restriction_message` | filter | `string $message`, `int $form_id`, `string $content_id` | The `show="message"` text. `%%donation_form_url%%` is already replaced. Output is passed through `wp_kses_post()`. |
| `dtac_give_show_restore_form` | filter | `bool $show_restore`, `int $form_id`, `string $content_id` | Append or drop the "Already donated?" form. |
| `dtac_give_unlocked_content_html` | filter | `string $html`, `string[] $unlocked` | Markup of `[dtac_my_unlocked_content]`. |
| `dtac_give_unlocked_content_item_html` | filter | `string $item`, `string $content_id`, `string $url` | A single `<li>` in that list. |
| `dtac_give_no_unlocked_content_html` | filter | `string $message` | Shown when the visitor has unlocked nothing. |

## Redirects and URLs

| Hook | Type | Args | Notes |
| --- | --- | --- | --- |
| `dtac_give_donation_form_url` | filter | `string $url`, `int $form_id`, `string $content_id` | The donation form URL, after query args are added. |
| `dtac_give_redirection_query_string_array` | filter | `array $query_args`, `array $query_args` | Query args appended to that URL. Second argument is a duplicate kept for backward compatibility. |
| `dtac_give_restriction_redirect_url` | filter | `string $url`, `string $content_id`, `int $form_id` | Where a gated visitor is sent. Return `''` to cancel the redirect and render the page. |
| `dtac_give_before_restriction_redirect` | action | `string $url`, `string $content_id`, `int $form_id` | Fires immediately before the redirect. |

## Guest restore

| Hook | Type | Args | Notes |
| --- | --- | --- | --- |
| `dtac_give_guest_access_duration` | filter | `int $duration`, `string $email` | Guest cookie lifetime in seconds. Default 30 days. |
| `dtac_give_guest_access_remembered` | action | `string $email` | Fires when a guest email is stored for later restores. |
| `dtac_give_restore_form_html` | filter | `string $html`, `string $message` | The "Already donated?" form markup. |
| `dtac_give_restore_link_lifetime` | filter | `int $lifetime`, `string $email` | Signed link validity in seconds. Default one hour. |
| `dtac_give_restore_email_subject` | filter | `string $subject`, `string $email` | |
| `dtac_give_restore_email_body` | filter | `string $body`, `string $email`, `string $link` | |
| `dtac_give_restore_email_headers` | filter | `array $headers`, `string $email` | Add `Content-Type: text/html` here for HTML email. |
| `dtac_give_access_restored` | action | `string $email` | Fires after a valid restore link is consumed. |

## Leak protection

| Hook | Type | Args | Notes |
| --- | --- | --- | --- |
| `dtac_give_leak_mode` | filter | `string $mode` | `hide` or `excerpt`. Anything else falls back to `hide`. |
| `dtac_give_restricted_message` | filter | `string $message`, `int $post_id` | Placeholder used in feeds, search, and oEmbed. |
| `dtac_give_restricted_excerpt` | filter | `string $excerpt`, `int $post_id` | Teaser used in `excerpt` mode. Passed through `wp_kses_post()`. |
| `dtac_give_restricted_excerpt_length` | filter | `int $words`, `int $post_id` | Word count when a post has no manual excerpt. Default 40. |

## Shortcode and block

| Hook | Type | Args | Notes |
| --- | --- | --- | --- |
| `dtac_give_shortcode_default_atts` | filter | `array $defaults`, `array $atts` | Register extra `[cip_donate_to_access_content]` attributes. |
| `dtac_give_shortcode_atts` | filter | `array $a`, `array $atts` | Parsed attributes. |
| `dtac_give_shortcode_output` | filter | `string $output`, `array $a`, `string $content` | Final shortcode output. |
| `dtac_give_restricted_block_attributes` | filter | `array $attributes` | Attribute schema of `dtac/restricted-content`. Editor controls still need JS. |
| `dtac_give_restricted_block_output` | filter | `string $output`, `array $attributes`, `string $content` | Final block output. |

## Donor lookup

| Hook | Type | Args | Notes |
| --- | --- | --- | --- |
| `dtac_give_donor_field` | filter | `string $field` | `user_id` or `email`. Legacy 1.x/2.x hook; only applied when one of these two filters is used. |
| `dtac_give_donor_value` | filter | `mixed $value` | User ID or email to look the donor up by. |

## Settings and admin

| Hook | Type | Args | Notes |
| --- | --- | --- | --- |
| `dtac_give_get_settings` | filter | `array $settings` | Whole `dtac_give_settings` option before a key is read. |
| `dtac_give_admin_settings` | filter | `array $settings` | Settings-page field definitions. |
| `dtac_give_admin_array` | filter | `array $options`, `array $options` | Dropdown options: `restrict_access_to`, `yes_no`, `leak_mode`. |
| `dtac_give_get_form_args` | filter | `array $args`, `array $args` | Query args for the Give form picker. |
| `dtac_give_cpt_args` | filter | `array $args`, `array $args` | Args for `get_post_types()` in admin. |
| `dtac_give_cpt_output_parameter` | filter | `string $output` | `names` or `objects`. |
| `dtac_give_cpt_operator` | filter | `string $operator` | `and` or `or`. |
| `dtac_give_custom_tax_args` | filter | `array $args`, `array $args` | Args for `get_taxonomies()`. |
| `dtac_give_custom_tax_output_value` | filter | `string $output` | `objects` or `names`. |

> Filters whose second argument repeats the first date back to 1.x. The duplicate
> is kept so existing callbacks keep working.

---

## Recipes

Let subscribers through without donating:

```php
add_filter(
	'dtac_give_should_bypass_restriction',
	function ( $bypass ) {
		return $bypass || current_user_can( 'read' ) && is_user_logged_in();
	}
);
```

Revoke access when a donation was refunded in your CRM:

```php
add_filter(
	'dtac_give_donation_unlocks_content',
	function ( $unlocks, $donation_id ) {
		return $unlocks && ! get_post_meta( $donation_id, '_my_crm_refunded', true );
	},
	10,
	2
);
```

Charge more for one post:

```php
add_filter(
	'dtac_give_min_amount',
	function ( $amount, $content_id ) {
		return ( '128' === $content_id ) ? 25.00 : $amount;
	},
	10,
	2
);
```

Show a lightbox instead of redirecting to the form:

```php
add_filter( 'dtac_give_restriction_redirect_url', '__return_empty_string' );
```

Send the restore link as HTML:

```php
add_filter(
	'dtac_give_restore_email_headers',
	function ( $headers ) {
		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		return $headers;
	}
);
```

Log every unlock:

```php
add_action(
	'dtac_give_access_granted',
	function ( $donation_id, $content_id ) {
		error_log( sprintf( 'DTAC unlocked %s via donation %d', $content_id, $donation_id ) );
	},
	10,
	2
);
```
