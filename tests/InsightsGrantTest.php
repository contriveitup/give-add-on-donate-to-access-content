<?php

/**
 * Tests for unlock insights and grant helper edge cases.
 *
 * @package DTAC_Give
 */

use PHPUnit\Framework\TestCase;

/**
 * Insights and grant helper tests.
 */
class DTAC_Give_Insights_Grant_Test extends TestCase
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
        $GLOBALS['wpdb']                     = null;
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
        unset($GLOBALS['wpdb']);

        parent::tearDown();
    }

    /**
     * Insights returns an empty list when $wpdb is unavailable.
     *
     * @return void
     */
    public function test_unlock_counts_without_wpdb()
    {

        require_once dirname(__DIR__) . '/src/Admin/Insights.php';

        $this->assertSame(array(), \DTAC\Admin\Insights::get_unlock_counts());
    }

    /**
     * Insights groups donation meta by sanitized content ID.
     *
     * @return void
     */
    public function test_unlock_counts_groups_sanitized_ids()
    {

        require_once dirname(__DIR__) . '/src/Admin/Insights.php';

        $GLOBALS['wpdb'] = new class() {
            /**
             * @var string
             */
            public $postmeta = 'wp_postmeta';

            /**
             * @param string $sql SQL.
             * @return string
             */
            public function prepare($sql)
            {
                return $sql;
            }

            /**
             * @return array
             */
            public function get_results()
            {
                return array(
                    (object) array(
                        'meta_value' => '12',
                        'total'      => '3',
                    ),
                    (object) array(
                        'meta_value' => 'c8',
                        'total'      => '1',
                    ),
                    (object) array(
                        'meta_value' => 'c-9',
                        'total'      => '9',
                    ),
                    (object) array(
                        'meta_value' => '',
                        'total'      => '4',
                    ),
                );
            }
        };

        $counts = \DTAC\Admin\Insights::get_unlock_counts();

        $this->assertSame(3, $counts['12']);
        $this->assertSame(1, $counts['c8']);
        $this->assertArrayNotHasKey('c-9', $counts);
        $this->assertArrayNotHasKey('', $counts);
    }

    /**
     * Content labels cover site, posts, and terms.
     *
     * @return void
     */
    public function test_content_labels_and_urls()
    {

        $GLOBALS['dtac_give_test_posts'][12] = (object) array(
            'ID'         => 12,
            'post_title' => 'Restricted Page',
            'post_type'  => 'page',
        );

        $term          = new WP_Term();
        $term->term_id = 8;
        $term->name    = 'DTAC Restricted';
        $GLOBALS['dtac_give_test_term_objects'][8] = $term;

        $this->assertSame('Entire website', dtac_give_get_content_label('site'));
        $this->assertSame('Restricted Page', dtac_give_get_content_label('12'));
        $this->assertSame('DTAC Restricted', dtac_give_get_content_label('c8'));
        $this->assertSame('https://example.test/?p=12', dtac_give_get_content_url('12'));
        $this->assertSame('https://example.test/?cat=8', dtac_give_get_content_url('c8'));
        $this->assertSame('https://example.test/', dtac_give_get_content_url('site'));
    }

    /**
     * Grant nonce helpers stay stable.
     *
     * @return void
     */
    public function test_grant_nonce_helpers()
    {

        $this->assertSame('dtac_give_grant_access', dtac_give_grant_nonce_action());
        $this->assertSame('dtac_give_grant_nonce', dtac_give_grant_nonce_field());
    }
}
