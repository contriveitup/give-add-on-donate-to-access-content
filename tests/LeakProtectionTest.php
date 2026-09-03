<?php

/**
 * Tests for REST, feed, search, and oEmbed leak protection.
 *
 * @package DTAC_Give
 */

use PHPUnit\Framework\TestCase;

/**
 * Leak protection tests.
 */
class DTAC_Give_Leak_Protection_Test extends TestCase
{

    /**
     * Leak protection instance.
     *
     * @var \DTAC\Frontend\Leak_Protection
     */
    private $leak;

    /**
     * Reset stubs and construct the class under test.
     *
     * @return void
     */
    protected function setUp(): void
    {

        parent::setUp();

        $GLOBALS['dtac_give_test_settings']       = array();
        $GLOBALS['dtac_give_test_posts']          = array();
        $GLOBALS['dtac_give_test_terms']          = array();
        $GLOBALS['dtac_give_test_post_meta']      = array();
        $GLOBALS['dtac_give_test_can_edit_posts'] = array();
        $GLOBALS['dtac_give_test_is_admin']       = false;
        $GLOBALS['dtac_give_test_is_search']      = false;
        $GLOBALS['dtac_give_test_the_id']         = 0;

        require_once dirname(__DIR__) . '/src/Frontend/Functions.php';
        require_once dirname(__DIR__) . '/src/Frontend/Leak_Protection.php';

        $this->leak = new \DTAC\Frontend\Leak_Protection();
    }

    /**
     * Reset globals after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {

        $GLOBALS['dtac_give_test_settings']       = array();
        $GLOBALS['dtac_give_test_posts']          = array();
        $GLOBALS['dtac_give_test_terms']          = array();
        $GLOBALS['dtac_give_test_post_meta']      = array();
        $GLOBALS['dtac_give_test_can_edit_posts'] = array();
        $GLOBALS['dtac_give_test_is_admin']       = false;
        $GLOBALS['dtac_give_test_is_search']      = false;
        $GLOBALS['dtac_give_test_the_id']         = 0;

        if (defined('REST_REQUEST') && REST_REQUEST) {
            // Constant cannot be undefined; tests that need it set it once.
        }

        parent::tearDown();
    }

    /**
     * Seed a restricted page for leak tests.
     *
     * @param int $post_id Post ID.
     * @return void
     */
    private function seed_restricted_page(int $post_id = 53): void
    {

        $GLOBALS['dtac_give_test_posts'][$post_id] = (object) array(
            'ID'           => $post_id,
            'post_content' => 'Secret REST body.',
            'post_type'    => 'page',
            'post_excerpt' => 'Restricted excerpt.',
            'post_title'   => 'Restricted Page',
        );

        $GLOBALS['dtac_give_test_settings'] = array(
            'dtac_give_restrict_access_to'       => array('pages'),
            'dtac_give_restrict_access_to_pages' => array((string) $post_id),
            'dtac_give_leak_mode'                => 'hide',
        );
    }

    /**
     * REST hide mode returns 401 for guests.
     *
     * @return void
     */
    public function test_rest_hides_restricted_item_for_guests()
    {

        $this->seed_restricted_page();

        $request        = new WP_REST_Request();
        $request->route = '/wp/v2/pages/53';

        $result = $this->leak->maybe_block_rest_item(array('id' => 53), null, $request);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertSame('dtac_give_restricted', $result->get_error_code());
        $this->assertSame(401, $result->get_error_data()['status']);
    }

    /**
     * REST excerpt mode leaves the payload for the excerpt filter to redact.
     *
     * @return void
     */
    public function test_rest_excerpt_mode_does_not_401()
    {

        $this->seed_restricted_page();
        $GLOBALS['dtac_give_test_settings']['dtac_give_leak_mode'] = 'excerpt';

        $request        = new WP_REST_Request();
        $request->route = '/wp/v2/pages/53';

        $payload = array('id' => 53, 'content' => 'Secret REST body.');
        $result  = $this->leak->maybe_block_rest_item($payload, null, $request);

        $this->assertSame($payload, $result);
    }

    /**
     * Editors can still open restricted REST items.
     *
     * @return void
     */
    public function test_rest_skips_editors()
    {

        $this->seed_restricted_page();
        $GLOBALS['dtac_give_test_can_edit_posts'] = array(53);

        $request        = new WP_REST_Request();
        $request->route = '/wp/v2/pages/53';

        $payload = array('id' => 53);
        $result  = $this->leak->maybe_block_rest_item($payload, null, $request);

        $this->assertSame($payload, $result);
    }

    /**
     * Hide mode drops restricted posts from search results.
     *
     * @return void
     */
    public function test_search_hides_restricted_posts()
    {

        $this->seed_restricted_page(53);

        $restricted            = new WP_Post();
        $restricted->ID        = 53;
        $restricted->post_type = 'page';
        $open                  = new WP_Post();
        $open->ID              = 12;
        $open->post_type       = 'page';

        $GLOBALS['dtac_give_test_posts'][12] = $open;

        $query         = new WP_Query();
        $query->search = true;

        $filtered = $this->leak->filter_the_posts(array($restricted, $open), $query);

        $this->assertCount(1, $filtered);
        $this->assertSame(12, $filtered[0]->ID);
    }

    /**
     * Excerpt leak mode keeps restricted posts in search so teasers can render.
     *
     * @return void
     */
    public function test_search_excerpt_mode_keeps_posts()
    {

        $this->seed_restricted_page(53);
        $GLOBALS['dtac_give_test_settings']['dtac_give_leak_mode'] = 'excerpt';

        $restricted            = new WP_Post();
        $restricted->ID        = 53;
        $restricted->post_type = 'page';

        $query         = new WP_Query();
        $query->search = true;

        $filtered = $this->leak->filter_the_posts(array($restricted), $query);

        $this->assertCount(1, $filtered);
    }

    /**
     * Hide mode still redacts feed content even if the post remains in the query.
     *
     * @return void
     */
    public function test_feed_content_is_redacted()
    {

        $this->seed_restricted_page(53);
        $GLOBALS['dtac_give_test_the_id'] = 53;

        $redacted = $this->leak->filter_feed_content('Secret REST body.');

        $this->assertStringNotContainsString('Secret REST body.', $redacted);
        $this->assertStringContainsString('restricted until a donation', $redacted);
    }

    /**
     * Excerpt leak mode never prints the secret body in feeds.
     *
     * @return void
     */
    public function test_feed_excerpt_mode_does_not_print_secret()
    {

        $this->seed_restricted_page(53);
        $GLOBALS['dtac_give_test_settings']['dtac_give_leak_mode'] = 'excerpt';
        $GLOBALS['dtac_give_test_the_id']                         = 53;

        $excerpt = $this->leak->filter_feed_content('Secret REST body.');

        $this->assertSame('Restricted excerpt.', $excerpt);
        $this->assertStringNotContainsString('Secret REST body.', $excerpt);
    }

    /**
     * oEmbed hide mode zeros the post ID.
     *
     * @return void
     */
    public function test_oembed_hide_mode_zeros_post_id()
    {

        $this->seed_restricted_page(53);

        $this->assertSame(0, $this->leak->filter_oembed_post_id(53, 'https://example.test/?p=53'));
    }

    /**
     * oEmbed excerpt mode redacts html without dropping the item.
     *
     * @return void
     */
    public function test_oembed_excerpt_mode_redacts_html()
    {

        $this->seed_restricted_page(53);
        $GLOBALS['dtac_give_test_settings']['dtac_give_leak_mode'] = 'excerpt';

        $post            = new WP_Post();
        $post->ID        = 53;
        $post->post_type = 'page';

        $data = $this->leak->filter_oembed_response(
            array(
                'title' => 'Restricted Page',
                'html'  => 'Secret REST body.',
            ),
            $post,
            600,
            400
        );

        $this->assertStringNotContainsString('Secret REST body.', $data['html']);
        $this->assertSame('Restricted excerpt.', $data['html']);
    }
}
