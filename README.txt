=== Donate to Access Content ===
Contributors: contriveitup, rupakdhiman
Tags: donate, give, restrict-content, membership, donations
Donate link: https://www.paypal.me/contriveitup
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.1
Requires Plugins: give
Stable tag: 3.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Unofficial GiveWP add-on that hides selected WordPress content until a visitor completes a qualifying donation.

== Description ==

Donate to Access Content is a free, unofficial add-on for [GiveWP](https://wordpress.org/plugins/give/). It is not an official GiveWP product.

Site owners can hide posts, pages, custom post types, category archives, or a portion of a page until a visitor donates through a Give form. After a completed donation, that donor can return and see the original content.

= What you can restrict =

* Individual pages and posts (settings lists or the Restrict with donation metabox)
* Category and custom taxonomy archive pages
* Entire public custom post types
* A portion of a page with the `[cip_donate_to_access_content]` shortcode or the Restricted Content block

Give donation form pages are never treated as restricted content.

= After a donation =

Access is based on Give donors, not a WordPress login. The same donor can return later as:

* A logged-in WordPress user linked to that Give donor
* A guest whose browser still has the guest-access cookie
* A guest who uses "Already donated?" and opens the signed email link

Optional settings include a minimum donation amount, access expiry in days, and how restricted posts appear in REST, RSS, search, and oEmbed.

= Requirements =

* WordPress 6.0 or later
* PHP 8.1 or later
* GiveWP active (legacy v2 forms and visual-builder v3 / v4 forms)
* A published Give donation form to use as the default gate

Development source: https://github.com/contriveitup/give-add-on-donate-to-access-content

= Third-party libraries =

Admin settings use Select2 4.0.13 (MIT License), bundled in assets/vendor/select2/. Unminified JS and CSS are included next to the minified files.

== Installation ==

1. Install and activate [GiveWP](https://wordpress.org/plugins/give/).
2. Install and activate Donate to Access Content.
3. Create or choose a published Give donation form (Donations → All Forms).
4. Go to Settings → DTAC, set the default form, and choose what to restrict.
5. Save changes, then visit a restricted URL in a private/incognito window to confirm guests see the gate.

== Frequently Asked Questions ==

= Is this an official GiveWP add-on? =

No. It requires GiveWP but is maintained independently.

= What shortcode restricts content? =

`[cip_donate_to_access_content form_id="1" show="form"]` wraps the secret content. `form_id` is required. `show` can be `form` (default) or `message`.

List content the current donor has unlocked with `[dtac_my_unlocked_content]`.

= If a category is restricted, can visitors still open a post in that category? =

Yes, unless that post is also restricted. Category and custom-taxonomy settings gate archives, not every singular post in the term.

= Who gets access after a donation? =

Give donors. A logged-in subscriber without a matching donor record still sees the gate.

= Can guests recover access after clearing cookies? =

Yes. On gated output, use "Already donated?" with the email from the donation. If that address has qualifying unlocks, WordPress sends a signed link that expires in one hour. The confirmation notice is the same whether or not the email has donations.

= Will a small donation unlock paid content? =

Not if you set a minimum donation amount globally or on the metabox. `0` means any completed donation qualifies.

= Does access last forever? =

By default yes. Set access expiry in days globally or per post. Metabox `0` means that post never expires, even if a global TTL is set.

= What happens if I update to 3.0 on PHP older than 8.1? =

Version 3.0 requires PHP 8.1. If the plugin is already active on PHP 7.4 or 8.0, WordPress still loads it after an update. The bootstrap shows an admin error and deactivates the plugin instead of causing a fatal error. Upgrade PHP, then activate the plugin again.

== Changelog ==

= 3.0.0: September 3, 2026 =
* Added: GiveWP 3.x / 4.x visual-builder grant path alongside legacy v2 forms.
* Added: Per-post metabox for restrict, donation form, minimum amount, and access expiry.
* Added: Blocks dtac/restricted-content and dtac/my-unlocked-content.
* Added: Guest restore via signed email link and [dtac_my_unlocked_content] shortcode.
* Added: REST, feed, search, and oEmbed leak protection.
* Added: Donations-list Unlocked Content column and settings-page unlock counts.
* Added: Settings page visual refresh with grouped sections.
* Added: Local Select2 4.0.13 assets (no CDN) for WordPress.org guideline compliance.
* Updated: Requires WordPress 6.0+, PHP 8.1+, GiveWP 4.16.x (tested up to WordPress 7.1).
* Updated: Ships a plugin autoloader so Composer vendor is not required in the zip.
* Fixed: Access-grant spoofing via content-ID whitelist.
* Fixed: Cache headers on restricted URLs.
* Fixed: Uninstall now removes plugin settings and related donation/donor meta.
* Fixed: Updating on PHP older than 8.1 deactivates with an admin notice instead of a parse fatal.

See changelog.txt for earlier versions.

= 2.1.0: July 30, 2022 =
* Added: Function dtac_get_give_forms to list Give forms in Admin Settings.
* Added: Function input_default_select for default values in the select box.
* Updated: NPM packages and code improvements.

== Upgrade Notice ==

= 3.0.0 =
Requires PHP 8.1 and an active GiveWP install. Existing restriction lists and the [cip_donate_to_access_content] shortcode still work. Upgrade PHP before updating if the site is on 8.0 or older.
