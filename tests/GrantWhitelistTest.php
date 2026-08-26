<?php

/**
 * Tests for grant-whitelist and leak-mode helpers.
 *
 * @package DTAC_Give
 */

use PHPUnit\Framework\TestCase;

/**
 * Grant whitelist tests.
 */
class DTAC_Give_Grant_Whitelist_Test extends TestCase
{

    /**
     * Reset request and settings stubs.
     *
     * @return void
     */
    protected function setUp(): void
    {

        parent::setUp();

        $GLOBALS['dtac_give_test_settings']  = array();
        $GLOBALS['dtac_give_test_posts']     = array();
        $GLOBALS['dtac_give_test_terms']     = array();
        $GLOBALS['dtac_give_test_post_meta'] = array();
    }

    /**
     * Reset globals after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {

        $GLOBALS['dtac_give_test_settings']  = array();
        $GLOBALS['dtac_give_test_posts']     = array();
        $GLOBALS['dtac_give_test_terms']     = array();
        $GLOBALS['dtac_give_test_post_meta'] = array();
        $_GET                                = array();
        $_POST                              = array();
        $_REQUEST                           = array();
        $_COOKIE                            = array();

        parent::tearDown();
    }

    /**
     * Site grants are rejected unless whole-site restriction is on.
     *
     * @return void
     */
    public function test_site_grant_requires_whole_site_setting()
    {

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_website' => 'no',
        );

        $this->assertFalse(dtac_give_is_grantable_content_id('site'));

        $GLOBALS['dtac_give_test_settings']['dtac_give_restrict_website'] = 'yes';

        $this->assertTrue(dtac_give_is_grantable_content_id('site'));
    }

    /**
     * Numeric IDs must be in the restricted pages/posts lists.
     *
     * @return void
     */
    public function test_spoofed_page_id_is_rejected()
    {

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_access_to'       => array('pages'),
            'dtac_give_restrict_access_to_pages' => array('12'),
        );

        $this->assertTrue(dtac_give_is_grantable_content_id('12'));
        $this->assertFalse(dtac_give_is_grantable_content_id('99'));
    }

    /**
     * Shortcode pages are grantable even when not in the settings lists.
     *
     * @return void
     */
    public function test_shortcode_page_is_grantable()
    {

        $GLOBALS['dtac_give_test_posts'][21] = (object) array(
            'ID'           => 21,
            'post_content' => '[cip_donate_to_access_content form_id=1]Secret[/cip_donate_to_access_content]',
            'post_type'    => 'page',
            'post_excerpt' => '',
        );

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_access_to'       => array(),
            'dtac_give_restrict_access_to_pages' => array(),
        );

        $this->assertTrue(dtac_give_is_grantable_content_id('21'));
        $this->assertTrue(dtac_give_is_post_restricted(21));
    }

    /**
     * Category and CPT identifiers must match enabled lists.
     *
     * @return void
     */
    public function test_term_and_cpt_whitelist()
    {

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_access_to'            => array('cats', 'cpt'),
            'dtac_give_restrict_access_to_cats'       => array('44'),
            'dtac_give_restrict_access_to_cpt'        => array('book'),
            'dtac_give_restrict_access_to_custom_tax' => array(),
        );

        $this->assertTrue(dtac_give_is_grantable_content_id('c44'));
        $this->assertFalse(dtac_give_is_grantable_content_id('c99'));
        $this->assertTrue(dtac_give_is_grantable_content_id('book'));
        $this->assertFalse(dtac_give_is_grantable_content_id('movie'));
    }

    /**
     * Leak mode defaults to hide and accepts excerpt.
     *
     * @return void
     */
    public function test_leak_mode_default_and_excerpt()
    {

        $GLOBALS['dtac_give_test_settings'] = array();

        $this->assertSame('hide', dtac_give_get_leak_mode());

        $GLOBALS['dtac_give_test_settings']['dtac_give_leak_mode'] = 'excerpt';

        $this->assertSame('excerpt', dtac_give_get_leak_mode());

        $GLOBALS['dtac_give_test_settings']['dtac_give_leak_mode'] = 'invalid';

        $this->assertSame('hide', dtac_give_get_leak_mode());
    }

    /**
     * ID lists are normalized to unique strings.
     *
     * @return void
     */
    public function test_normalize_id_list()
    {

        $this->assertSame(array('12', '8'), dtac_give_normalize_id_list(array(12, '8', 0, '')));
        $this->assertSame(array('12'), dtac_give_normalize_id_list('12'));
        $this->assertSame(array(), dtac_give_normalize_id_list(''));
    }
}
