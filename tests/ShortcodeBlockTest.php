<?php

/**
 * Tests for shortcode and block restriction gates.
 *
 * @package DTAC_Give
 */

use PHPUnit\Framework\TestCase;

/**
 * Shortcode and block tests.
 */
class DTAC_Give_Shortcode_Block_Test extends TestCase
{

    /**
     * Reset stubs.
     *
     * @return void
     */
    protected function setUp(): void
    {

        parent::setUp();

        $GLOBALS['dtac_give_test_settings']  = array(
            'dtac_give_restrict_message'             => '<p>Please donate at %%donation_form_url%%</p>',
            'dtac_give_restrict_access_give_form_id' => '7',
        );
        $GLOBALS['dtac_give_test_posts']     = array();
        $GLOBALS['dtac_give_test_post_meta'] = array();
        $GLOBALS['dtac_give_test_is_admin']  = false;
        $_COOKIE                             = array();

        require_once dirname(__DIR__) . '/src/Frontend/Functions.php';
        require_once dirname(__DIR__) . '/src/Frontend/Shortcodes.php';
        require_once dirname(__DIR__) . '/src/Frontend/Blocks.php';
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
        $GLOBALS['dtac_give_test_post_meta'] = array();
        $GLOBALS['dtac_give_test_is_admin']  = false;
        $_COOKIE                             = array();

        parent::tearDown();
    }

    /**
     * Shortcode without a form ID outputs nothing.
     *
     * @return void
     */
    public function test_shortcode_without_form_id_is_empty()
    {

        $shortcodes = new \DTAC\Frontend\Shortcodes();

        $this->assertSame('', $shortcodes->donate_to_access_give_shortcode_func(array(), 'Secret'));
        $this->assertSame('', $shortcodes->donate_to_access_give_shortcode_func(array('form_id' => '0'), 'Secret'));
        $this->assertSame('', $shortcodes->donate_to_access_give_shortcode_func(array('form_id' => 'abc'), 'Secret'));
    }

    /**
     * Guests see the restriction message, not the wrapped secret.
     *
     * @return void
     */
    public function test_shortcode_message_mode_hides_secret_for_guests()
    {

        $shortcodes = new \DTAC\Frontend\Shortcodes();
        $output     = $shortcodes->donate_to_access_give_shortcode_func(
            array(
                'form_id' => '7',
                'show'    => 'message',
            ),
            'Secret shortcode body'
        );

        $this->assertIsString($output);
        $this->assertStringNotContainsString('Secret shortcode body', $output);
        $this->assertStringContainsString('Please donate', $output);
        $this->assertStringContainsString('dtac_give_content=', $output);
        $this->assertStringNotContainsString('%%donation_form_url%%', $output);
    }

    /**
     * Guests see the Give form shortcode in form mode.
     *
     * @return void
     */
    public function test_shortcode_form_mode_embeds_give_form()
    {

        $shortcodes = new \DTAC\Frontend\Shortcodes();
        $output     = $shortcodes->donate_to_access_give_shortcode_func(
            array(
                'form_id' => '7',
                'show'    => 'form',
            ),
            'Secret shortcode body'
        );

        $this->assertStringContainsString('[give_form id="7"]', $output);
        $this->assertStringNotContainsString('Secret shortcode body', $output);
    }

    /**
     * Admin screens bypass the shortcode gate.
     *
     * @return void
     */
    public function test_shortcode_admin_bypass_shows_secret()
    {

        $GLOBALS['dtac_give_test_is_admin'] = true;

        $shortcodes = new \DTAC\Frontend\Shortcodes();
        $output     = $shortcodes->donate_to_access_give_shortcode_func(
            array(
                'form_id' => '7',
                'show'    => 'form',
            ),
            'Secret shortcode body'
        );

        $this->assertSame('Secret shortcode body', $output);
    }

    /**
     * Restricted-content block hides InnerBlocks for guests.
     *
     * @return void
     */
    public function test_restricted_block_hides_inner_content_for_guests()
    {

        $blocks = new \DTAC\Frontend\Blocks();
        $output = $blocks->render_restricted(
            array(
                'formId' => 7,
                'show'   => 'form',
            ),
            '<p>Secret block body</p>'
        );

        $this->assertStringNotContainsString('Secret block body', $output);
        $this->assertStringContainsString('[give_form id="7"]', $output);
    }

    /**
     * Restricted-content block message mode replaces the placeholder.
     *
     * @return void
     */
    public function test_restricted_block_message_mode()
    {

        $blocks = new \DTAC\Frontend\Blocks();
        $output = $blocks->render_restricted(
            array(
                'formId' => 7,
                'show'   => 'message',
            ),
            '<p>Secret block body</p>'
        );

        $this->assertStringNotContainsString('Secret block body', $output);
        $this->assertStringContainsString('Please donate', $output);
    }

    /**
     * Unlocked-content list is empty for guests.
     *
     * @return void
     */
    public function test_unlocked_content_empty_for_guests()
    {

        $blocks = new \DTAC\Frontend\Blocks();

        $this->assertStringContainsString('No unlocked content found.', $blocks->render_unlocked());
        $this->assertStringContainsString('No unlocked content found.', dtac_give_get_unlocked_content_html());
    }
}
