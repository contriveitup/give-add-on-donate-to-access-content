<?php

/**
 * Tests for metabox persistence and donation grant hooks.
 *
 * @package DTAC_Give
 */

use PHPUnit\Framework\TestCase;

/**
 * Metabox and grant-hook tests.
 */
class DTAC_Give_Metabox_Hooks_Test extends TestCase
{

    /**
     * Reset stubs.
     *
     * @return void
     */
    protected function setUp(): void
    {

        parent::setUp();

        $GLOBALS['dtac_give_test_settings']  = array();
        $GLOBALS['dtac_give_test_posts']     = array();
        $GLOBALS['dtac_give_test_post_meta'] = array();
        $_POST                               = array();
        $_GET                                = array();
        $_REQUEST                            = array();
        $_COOKIE                             = array();

        require_once dirname(__DIR__) . '/src/Admin/Metabox.php';
        require_once dirname(__DIR__) . '/src/Frontend/Functions.php';
        require_once dirname(__DIR__) . '/src/Frontend/Hooks.php';
    }

    /**
     * Reset globals.
     *
     * @return void
     */
    protected function tearDown(): void
    {

        $GLOBALS['dtac_give_test_settings']  = array();
        $GLOBALS['dtac_give_test_posts']     = array();
        $GLOBALS['dtac_give_test_post_meta'] = array();
        $_POST                               = array();
        $_GET                                = array();
        $_REQUEST                            = array();
        $_COOKIE                             = array();

        parent::tearDown();
    }

    /**
     * Invalid metabox nonce does not persist restriction meta.
     *
     * @return void
     */
    public function test_metabox_save_rejects_invalid_nonce()
    {

        $post            = new WP_Post();
        $post->ID        = 88;
        $post->post_type = 'post';

        $_POST['dtac_give_restrict']       = 'yes';
        $_POST['dtac_give_metabox_nonce']  = 'bad';

        $metabox = new \DTAC\Admin\Metabox();
        $metabox->save(88, $post);

        $this->assertArrayNotHasKey(88, $GLOBALS['dtac_give_test_post_meta']);
    }

    /**
     * Hidden form fields are omitted for spoofed content IDs.
     *
     * @return void
     */
    public function test_form_fields_skip_ungrantable_content()
    {

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_access_to'       => array('pages'),
            'dtac_give_restrict_access_to_pages' => array('12'),
        );

        $_GET['dtac_give_content'] = '999999';

        $hooks = new \DTAC\Frontend\Hooks();

        ob_start();
        $hooks->dtac_give_form_fields(7);
        $html = (string) ob_get_clean();

        $this->assertSame('', $html);
    }

    /**
     * Hidden form fields are emitted for grantable page IDs.
     *
     * @return void
     */
    public function test_form_fields_include_grantable_page()
    {

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_access_to'       => array('pages'),
            'dtac_give_restrict_access_to_pages' => array('12'),
        );

        $_GET['dtac_give_content'] = '12';

        $hooks = new \DTAC\Frontend\Hooks();

        ob_start();
        $hooks->dtac_give_form_fields(7);
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('name="dtac_give_content"', $html);
        $this->assertStringContainsString('value="12"', $html);
        $this->assertStringContainsString('name="dtac_give_process_donate_to_access"', $html);
    }
}
