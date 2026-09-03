<?php

/**
 * Settings-page FAQ.
 *
 * @package DTAC_Give
 *
 * @since 3.0.0
 */

namespace DTAC\Admin;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Public FAQ card on the DTAC settings page.
 *
 * @since 3.0.0
 */
class Faq
{


    /**
     * Render the FAQ card.
     *
     * @since 3.0.0
     *
     * @return void
     */
    public static function render(): void
    {

        $items = self::items();

        echo '<div class="dtac-faq">';
        echo '<h2 class="dtac-card__title">' . esc_html__('FAQ', 'dtac-give') . '</h2>';
        echo '<p class="dtac-card__intro">' . esc_html__('Common questions about restricting content until a donation is made.', 'dtac-give') . '</p>';

        echo '<div class="dtac-faq__list">';

        foreach ($items as $item) {
            if (empty($item['question']) || empty($item['answer'])) {
                continue;
            }

            echo '<details class="dtac-faq__item">';
            echo '<summary class="dtac-faq__question">' . esc_html((string) $item['question']) . '</summary>';
            echo '<div class="dtac-faq__answer">' . wp_kses_post((string) $item['answer']) . '</div>';
            echo '</details>';
        }

        echo '</div>';
        echo '</div>';
    }

    /**
     * FAQ entries shown on the settings page.
     *
     * @since 3.0.0
     *
     * @return array<int,array{question:string,answer:string}>
     */
    public static function items(): array
    {

        return array(
            array(
                'question' => __('What can I restrict?', 'dtac-give'),
                'answer'   => __('Pages, posts, category archives, custom post types, custom taxonomy archives, and a portion of a page via the shortcode or Restricted Content block. Give form pages are never gated.', 'dtac-give'),
            ),
            array(
                'question' => __('Do I have to use the shortcode?', 'dtac-give'),
                'answer'   => __('No. Settings lists and the Restrict with donation metabox redirect entire URLs to the Give form. Use the shortcode or block only when the rest of the page should stay public. The shortcode requires a form_id.', 'dtac-give'),
            ),
            array(
                'question' => __('A category is restricted. Can visitors still open a post in that category?', 'dtac-give'),
                'answer'   => __('Yes on the front end, unless that post is also restricted. Category and custom-taxonomy settings gate archive pages only.', 'dtac-give'),
            ),
            array(
                'question' => __('Who gets access after donating?', 'dtac-give'),
                'answer'   => __('Give donors, not WordPress login. A logged-in subscriber without a matching donor record still sees the gate. Guests can use Already donated? to restore access by email.', 'dtac-give'),
            ),
            array(
                'question' => __('Will a small donation unlock paid content?', 'dtac-give'),
                'answer'   => __('Not if you set a minimum donation amount globally or on the metabox. The completed donation must meet or exceed that amount.', 'dtac-give'),
            ),
            array(
                'question' => __('Does access last forever?', 'dtac-give'),
                'answer'   => __('By default yes (0 days). Set expiry globally or per post to require a newer donation. Metabox 0 means that post never expires.', 'dtac-give'),
            ),
            array(
                'question' => __('What is leak mode?', 'dtac-give'),
                'answer'   => __('It controls REST, feeds, search, and oEmbed. Hide completely withholds the secret body. Show excerpt only allows a teaser. Front-end URLs still use the redirect or shortcode gate.', 'dtac-give'),
            ),
            array(
                'question' => __('Can someone unlock content by changing the donation URL?', 'dtac-give'),
                'answer'   => __('No. Only whitelisted content IDs are stored. A spoofed or junk dtac_give_content value is ignored.', 'dtac-give'),
            ),
        );
    }
}
