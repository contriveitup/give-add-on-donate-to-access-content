<?php

/**
 * Tests for min amount, expiry, and per-post restriction helpers.
 *
 * @package DTAC_Give
 */

use PHPUnit\Framework\TestCase;

/**
 * Unlock rule tests.
 */
class DTAC_Give_Unlock_Rules_Test extends TestCase
{

    /**
     * Reset stubs.
     *
     * @return void
     */
    protected function setUp(): void
    {

        parent::setUp();

        $GLOBALS['dtac_give_test_settings']   = array();
        $GLOBALS['dtac_give_test_posts']      = array();
        $GLOBALS['dtac_give_test_terms']      = array();
        $GLOBALS['dtac_give_test_post_meta']  = array();
        $GLOBALS['dtac_give_test_is_admin']   = false;
        $GLOBALS['dtac_give_test_give_forms'] = array();
    }

    /**
     * Reset globals after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {

        $GLOBALS['dtac_give_test_settings']   = array();
        $GLOBALS['dtac_give_test_posts']      = array();
        $GLOBALS['dtac_give_test_terms']      = array();
        $GLOBALS['dtac_give_test_post_meta']  = array();
        $GLOBALS['dtac_give_test_is_admin']   = false;
        $GLOBALS['dtac_give_test_give_forms'] = array();
        $_GET                                 = array();
        $_POST                                = array();
        $_REQUEST                             = array();
        $_COOKIE                              = array();

        parent::tearDown();
    }

    /**
     * Amount sanitizer strips commas and rejects junk.
     *
     * @return void
     */
    public function test_sanitize_amount()
    {

        $this->assertSame(1000.5, dtac_give_sanitize_amount('1,000.50'));
        $this->assertSame(10.0, dtac_give_sanitize_amount(10));
        $this->assertSame(0.0, dtac_give_sanitize_amount('abc'));
        $this->assertSame(0.0, dtac_give_sanitize_amount(-5));
    }

    /**
     * Metabox restrict=yes makes a post grantable and restricted.
     *
     * @return void
     */
    public function test_metabox_restriction_is_grantable()
    {

        $GLOBALS['dtac_give_test_posts'][88] = (object) array(
            'ID'           => 88,
            'post_content' => 'No shortcode here',
            'post_type'    => 'post',
            'post_excerpt' => '',
        );

        $GLOBALS['dtac_give_test_post_meta'][88]['_dtac_give_restrict'] = 'yes';

        $this->assertTrue(dtac_give_is_grantable_content_id('88'));
        $this->assertTrue(dtac_give_is_post_restricted(88));
    }

    /**
     * Metabox restrict=no opts a listed post out of restriction.
     *
     * @return void
     */
    public function test_metabox_no_opts_out_of_lists()
    {

        $GLOBALS['dtac_give_test_posts'][90] = (object) array(
            'ID'           => 90,
            'post_content' => '',
            'post_type'    => 'page',
            'post_excerpt' => '',
        );

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_access_to'       => array('pages'),
            'dtac_give_restrict_access_to_pages' => array('90'),
        );

        $GLOBALS['dtac_give_test_post_meta'][90]['_dtac_give_restrict'] = 'no';

        $this->assertFalse(dtac_give_is_grantable_content_id('90'));
        $this->assertFalse(dtac_give_is_post_restricted(90));
        $this->assertTrue(dtac_give_should_bypass_restriction(90));
    }

    /**
     * Metabox restrict=no opts a post out of whole-site restriction too.
     *
     * @return void
     */
    public function test_metabox_no_opts_out_of_whole_site()
    {

        $GLOBALS['dtac_give_test_posts'][91] = (object) array(
            'ID'           => 91,
            'post_content' => '<!-- wp:dtac/restricted-content --><p>Secret</p><!-- /wp:dtac/restricted-content -->',
            'post_type'    => 'page',
            'post_excerpt' => '',
        );

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_website' => 'yes',
        );

        $GLOBALS['dtac_give_test_post_meta'][91]['_dtac_give_restrict'] = 'no';

        $this->assertFalse(dtac_give_is_post_restricted(91));
        $this->assertTrue(dtac_give_should_bypass_restriction(91));
    }

    /**
     * Restriction is skipped on admin screens.
     *
     * @return void
     */
    public function test_admin_screens_bypass_restriction()
    {

        $GLOBALS['dtac_give_test_is_admin'] = true;

        $this->assertTrue(dtac_give_should_bypass_restriction(12));

        $GLOBALS['dtac_give_test_is_admin'] = false;

        $this->assertFalse(dtac_give_should_bypass_restriction(12));
    }

    /**
     * Give form picker returns published forms keyed by ID.
     *
     * @return void
     */
    public function test_give_form_picker_uses_form_ids()
    {

        $form            = new WP_Post();
        $form->ID        = 42;
        $form->post_title = 'General Donation';
        $form->post_type = 'give_forms';

        $GLOBALS['dtac_give_test_give_forms'] = array($form);

        $forms = dtac_give_get_give_forms_for_picker();

        $this->assertSame(array(42 => 'General Donation'), $forms);
    }

    /**
     * Donations below the minimum do not unlock content.
     *
     * @return void
     */
    public function test_min_amount_rejects_small_donation()
    {

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_min_amount' => '25',
        );

        $GLOBALS['dtac_give_test_post_meta'][501]['_give_payment_total'] = 10;

        $this->assertFalse(dtac_give_donation_unlocks_content(501, '12'));

        $GLOBALS['dtac_give_test_post_meta'][501]['_give_payment_total'] = 25;

        $this->assertTrue(dtac_give_donation_unlocks_content(501, '12'));
    }

    /**
     * Per-post minimum overrides the global default.
     *
     * @return void
     */
    public function test_per_post_min_amount_override()
    {

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_min_amount' => '5',
        );

        $GLOBALS['dtac_give_test_post_meta'][12]['_dtac_give_min_amount'] = 40;
        $GLOBALS['dtac_give_test_post_meta'][501]['_give_payment_total']  = 25;

        $this->assertFalse(dtac_give_donation_unlocks_content(501, '12'));
    }

    /**
     * Expired donations no longer unlock content.
     *
     * @return void
     */
    public function test_expiry_rejects_old_donation()
    {

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_access_expires_days' => '30',
        );

        $old                    = new WP_Post();
        $old->ID                = 502;
        $old->post_date_gmt     = gmdate('Y-m-d H:i:s', time() - (40 * DAY_IN_SECONDS));
        $GLOBALS['dtac_give_test_posts'][502] = $old;
        $GLOBALS['dtac_give_test_post_meta'][502]['_give_payment_total'] = 50;

        $this->assertFalse(dtac_give_donation_unlocks_content(502, '12'));

        $recent                    = new WP_Post();
        $recent->ID                = 503;
        $recent->post_date_gmt     = gmdate('Y-m-d H:i:s', time() - (2 * DAY_IN_SECONDS));
        $GLOBALS['dtac_give_test_posts'][503] = $recent;
        $GLOBALS['dtac_give_test_post_meta'][503]['_give_payment_total'] = 50;

        $this->assertTrue(dtac_give_donation_unlocks_content(503, '12'));
    }

    /**
     * Restrict block markup is grantable like the shortcode.
     *
     * @return void
     */
    public function test_restrict_block_is_grantable()
    {

        $GLOBALS['dtac_give_test_posts'][77] = (object) array(
            'ID'           => 77,
            'post_content' => '<!-- wp:dtac/restricted-content --><p>Secret</p><!-- /wp:dtac/restricted-content -->',
            'post_type'    => 'page',
            'post_excerpt' => '',
        );

        $this->assertTrue(dtac_give_is_grantable_content_id('77'));
        $this->assertTrue(dtac_give_is_post_restricted(77));
    }

    /**
     * Posts in a restricted category stay public on the singular view.
     *
     * @return void
     */
    public function test_category_restriction_does_not_lock_singular_posts()
    {

        $GLOBALS['dtac_give_test_posts'][64] = (object) array(
            'ID'           => 64,
            'post_content' => 'Public body in a restricted category.',
            'post_type'    => 'post',
            'post_excerpt' => 'Public excerpt.',
        );

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_access_to'      => array('cats'),
            'dtac_give_restrict_access_to_cats' => array('8'),
        );

        $GLOBALS['dtac_give_test_terms']['64|category'] = array(8);

        $this->assertFalse(dtac_give_is_post_restricted(64));
        $this->assertTrue(dtac_give_visitor_can_view_post(64));
        $this->assertTrue(dtac_give_is_grantable_content_id('c8'));
        $this->assertFalse(dtac_give_is_grantable_content_id('64'));
    }

    /**
     * Custom-taxonomy membership does not lock a singular CPT item.
     *
     * @return void
     */
    public function test_custom_tax_restriction_does_not_lock_singular_cpt()
    {

        $GLOBALS['dtac_give_test_posts'][65] = (object) array(
            'ID'           => 65,
            'post_content' => 'Book body.',
            'post_type'    => 'dtac_book',
            'post_excerpt' => '',
        );

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_access_to'            => array('ctax'),
            'dtac_give_restrict_access_to_custom_tax' => array('9'),
        );

        $GLOBALS['dtac_give_test_terms']['65|dtac_genre'] = array(9);

        $this->assertFalse(dtac_give_is_post_restricted(65));
        $this->assertTrue(dtac_give_visitor_can_view_post(65));
    }

    /**
     * Whole-site restriction still leaves the donation form and allow-list public.
     *
     * @return void
     */
    public function test_whole_site_skips_form_and_allow_pages()
    {

        $GLOBALS['dtac_give_test_posts'][10] = (object) array(
            'ID'           => 10,
            'post_content' => 'Give form',
            'post_type'    => 'give_forms',
            'post_excerpt' => '',
        );
        $GLOBALS['dtac_give_test_posts'][11] = (object) array(
            'ID'           => 11,
            'post_content' => 'Allow page',
            'post_type'    => 'page',
            'post_excerpt' => '',
        );
        $GLOBALS['dtac_give_test_posts'][12] = (object) array(
            'ID'           => 12,
            'post_content' => 'Locked page',
            'post_type'    => 'page',
            'post_excerpt' => '',
        );

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_website'             => 'yes',
            'dtac_give_restrict_access_give_form_id' => '10',
            'dtac_give_access_to_pages'              => array('11'),
        );

        $this->assertFalse(dtac_give_is_post_restricted(10));
        $this->assertFalse(dtac_give_is_post_restricted(11));
        $this->assertTrue(dtac_give_is_post_restricted(12));
    }

    /**
     * Per-post expiry of 0 never expires even when the global TTL is set.
     *
     * @return void
     */
    public function test_per_post_zero_expiry_never_expires()
    {

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_access_expires_days' => '1',
        );

        $GLOBALS['dtac_give_test_post_meta'][12]['_dtac_give_expiry_days'] = 0;

        $old                    = new WP_Post();
        $old->ID                = 504;
        $old->post_date_gmt     = gmdate('Y-m-d H:i:s', time() - (40 * DAY_IN_SECONDS));
        $GLOBALS['dtac_give_test_posts'][504] = $old;
        $GLOBALS['dtac_give_test_post_meta'][504]['_give_payment_total'] = 50;

        $this->assertSame(0, dtac_give_get_expiry_days('12'));
        $this->assertTrue(dtac_give_donation_unlocks_content(504, '12'));
    }
}
