<?php

/**
 * Settings-page how-to-use walkthrough.
 *
 * @package DTAC_Give
 *
 * @since 3.0.0
 */

namespace DTAC\Admin;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Non-technical usage guide below the DTAC settings form.
 *
 * @since 3.0.0
 */
class How_To_Use
{


    /**
     * Render the how-to-use card.
     *
     * @since 3.0.0
     *
     * @return void
     */
    public static function render(): void
    {

        $form_id = self::example_form_id();

        $restrict_form = sprintf(
            '[cip_donate_to_access_content form_id="%d" show="form"]
%s
[/cip_donate_to_access_content]',
            $form_id,
            __('Put the content only donors should see here.', 'dtac-give')
        );

        $restrict_message = sprintf(
            '[cip_donate_to_access_content form_id="%d" show="message"]
%s
[/cip_donate_to_access_content]',
            $form_id,
            __('Put the content only donors should see here.', 'dtac-give')
        );

        $unlocked = '[dtac_my_unlocked_content]';

        echo '<div class="dtac-howto">';
        echo '<h2 class="dtac-card__title">' . esc_html__('How to use this plugin', 'dtac-give') . '</h2>';
        echo '<p class="dtac-card__intro">' . esc_html__('You do not need to write code. Pick one method below. Save the settings form above first so the default Give form is set.', 'dtac-give') . '</p>';

        self::section(
            __('1. Restrict a whole page or post', 'dtac-give'),
            array(
                __('In the form above, choose the default Give Donation Form ID (Donations → All Forms if you need the number).', 'dtac-give'),
                __('Under Restrict Access To?, choose Pages and/or Posts.', 'dtac-give'),
                __('In Restrict Pages or Restrict Posts, select the content guests should not see.', 'dtac-give'),
                __('Click Save Changes. Visit that URL in a private/incognito window: guests go to the donation form; after a qualifying donation they see the page.', 'dtac-give'),
            )
        );

        self::section(
            __('2. Restrict one page from the editor', 'dtac-give'),
            array(
                __('Edit the page or post.', 'dtac-give'),
                __('Open the Restrict with donation box in the sidebar (Document / page settings).', 'dtac-give'),
                __('Set Restrict this content to Yes. Optionally set a different form, a minimum amount, or expiry days.', 'dtac-give'),
                __('Choose No if this page is in a restricted list but should stay public.', 'dtac-give'),
                __('Update the page, then test logged out.', 'dtac-give'),
            )
        );

        echo '<section class="dtac-howto__section">';
        echo '<h3 class="dtac-howto__heading">' . esc_html__('3. Hide only part of a page (shortcode)', 'dtac-give') . '</h3>';
        echo '<p>' . esc_html__('Use this when the rest of the page should stay public and only a section is for donors. The shortcode must include form_id or nothing is shown.', 'dtac-give') . '</p>';

        echo '<p><strong>' . esc_html__('Block editor (Gutenberg)', 'dtac-give') . '</strong></p>';
        echo '<ol class="dtac-howto__steps">';
        echo '<li>' . esc_html__('Edit the page. Click the + inserter.', 'dtac-give') . '</li>';
        echo '<li>' . esc_html__('Search for Shortcode and add that block.', 'dtac-give') . '</li>';
        echo '<li>' . esc_html__('Paste one of the examples below. Replace the inner sentence with your secret content.', 'dtac-give') . '</li>';
        echo '<li>' . esc_html__('Update the page. Logged-out visitors see the Give form (or your restriction message) in that spot only.', 'dtac-give') . '</li>';
        echo '</ol>';

        echo '<p><strong>' . esc_html__('Classic editor', 'dtac-give') . '</strong></p>';
        echo '<ol class="dtac-howto__steps">';
        echo '<li>' . esc_html__('Edit the page. Switch to the Text tab if you use the visual editor.', 'dtac-give') . '</li>';
        echo '<li>' . esc_html__('Paste the shortcode around the text you want to hide.', 'dtac-give') . '</li>';
        echo '<li>' . esc_html__('Update the page and test logged out.', 'dtac-give') . '</li>';
        echo '</ol>';

        echo '<p><strong>' . esc_html__('Show the donation form in place of the secret content', 'dtac-give') . '</strong></p>';
        self::code($restrict_form);

        echo '<p><strong>' . esc_html__('Show the Restrict Content Message instead of a form', 'dtac-give') . '</strong></p>';
        echo '<p class="dtac-howto__note">' . esc_html__('The message comes from the setting above. Put %%donation_form_url%% in that message where the donate link should appear.', 'dtac-give') . '</p>';
        self::code($restrict_message);

        echo '<p class="dtac-howto__note">' . esc_html__('The form_id in these examples is your saved default Give form. Change it if this page should use a different form. Find IDs under Donations → All Forms.', 'dtac-give') . '</p>';
        echo '</section>';

        self::section(
            __('4. Hide only part of a page (block)', 'dtac-give'),
            array(
                __('Edit the page in the block editor. Click + and search for Restricted Content (Widgets).', 'dtac-give'),
                __('Add the blocks you want to hide inside it (paragraphs, images, and so on).', 'dtac-give'),
                __('In the block sidebar, pick the Give form and whether to show a donation form or the restriction message.', 'dtac-give'),
                __('Update the page and test logged out.', 'dtac-give'),
            )
        );

        echo '<section class="dtac-howto__section">';
        echo '<h3 class="dtac-howto__heading">' . esc_html__('5. List what a donor has unlocked', 'dtac-give') . '</h3>';
        echo '<p>' . esc_html__('Add this shortcode on any page (Shortcode block or classic editor), or insert the My Unlocked Content block.', 'dtac-give') . '</p>';
        self::code($unlocked);
        echo '<p class="dtac-howto__note">' . esc_html__('Visitors who have not donated see “No unlocked content found.”', 'dtac-give') . '</p>';
        echo '</section>';

        self::section(
            __('6. After someone donates', 'dtac-give'),
            array(
                __('A completed donation unlocks the content they came from, if the amount meets any minimum you set.', 'dtac-give'),
                __('Guests who donated on another device can use Already donated? on the gate and open the email link.', 'dtac-give'),
                __('Donations → the Unlocked Content column, and Unlock insights in the sidebar, show what was granted.', 'dtac-give'),
            )
        );

        echo '<section class="dtac-howto__section">';
        echo '<h3 class="dtac-howto__heading">' . esc_html__('Things to remember', 'dtac-give') . '</h3>';
        echo '<ul class="dtac-howto__bullets">';
        echo '<li>' . esc_html__('Restricting a category or custom taxonomy only locks that archive page, not every post in it. Restrict the post too if the single URL should be gated.', 'dtac-give') . '</li>';
        echo '<li>' . esc_html__('Give donation form pages are never restricted. Always test as a logged-out visitor (private window).', 'dtac-give') . '</li>';
        echo '<li>' . esc_html__('Access follows the Give donor (email / donor record), not a normal WordPress subscriber login.', 'dtac-give') . '</li>';
        echo '<li>' . esc_html__('Editors who can edit the post can still preview it without donating.', 'dtac-give') . '</li>';
        echo '</ul>';
        echo '</section>';

        echo '</div>';
    }

    /**
     * Form ID used in copy-paste shortcode examples.
     *
     * @since 3.0.0
     *
     * @return int
     */
    public static function example_form_id(): int
    {

        $form_id = 0;

        if (function_exists('dtac_give_get_settings')) {
            $form_id = absint(dtac_give_get_settings('dtac_give_restrict_access_give_form_id'));
        }

        return ($form_id > 0) ? $form_id : 1;
    }

    /**
     * Numbered how-to section.
     *
     * @since 3.0.0
     *
     * @param string   $heading Heading.
     * @param string[] $steps   Steps.
     *
     * @return void
     */
    private static function section(string $heading, array $steps): void
    {

        echo '<section class="dtac-howto__section">';
        echo '<h3 class="dtac-howto__heading">' . esc_html($heading) . '</h3>';
        echo '<ol class="dtac-howto__steps">';

        foreach ($steps as $step) {
            $step = (string) $step;

            if ('' === $step) {
                continue;
            }

            echo '<li>' . esc_html($step) . '</li>';
        }

        echo '</ol>';
        echo '</section>';
    }

    /**
     * Escaped copy-paste example.
     *
     * @since 3.0.0
     *
     * @param string $code Shortcode example.
     *
     * @return void
     */
    private static function code(string $code): void
    {

        echo '<pre class="dtac-howto__code"><code>' . esc_html($code) . '</code></pre>';
    }
}
