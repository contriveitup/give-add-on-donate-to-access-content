<?php
/**
 * Tests for content-ID helpers used by the dual grant flow.
 *
 * @package DTAC_Give
 */

use PHPUnit\Framework\TestCase;

/**
 * Content ID helper tests.
 */
class DTAC_Give_Content_Id_Test extends TestCase {

	/**
	 * Reset request globals between tests.
	 *
	 * @return void
	 */
	protected function tearDown() : void {

		$_GET     = array();
		$_POST    = array();
		$_REQUEST = array();
		$_COOKIE  = array();
		$_SERVER  = array();

		parent::tearDown();
	}

	/**
	 * Sanitize accepts the plugin's existing identifier shapes.
	 *
	 * @return void
	 */
	public function test_sanitize_content_id_accepts_known_shapes() {

		$this->assertSame( '12', dtac_give_sanitize_content_id( 12 ) );
		$this->assertSame( '12', dtac_give_sanitize_content_id( '12' ) );
		$this->assertSame( 'site', dtac_give_sanitize_content_id( 'site' ) );
		$this->assertSame( 'c44', dtac_give_sanitize_content_id( 'c44' ) );
		$this->assertSame( 'book', dtac_give_sanitize_content_id( 'book' ) );
	}

	/**
	 * Sanitize rejects empty or junk values.
	 *
	 * @return void
	 */
	public function test_sanitize_content_id_rejects_empty_values() {

		$this->assertSame( '', dtac_give_sanitize_content_id( 0 ) );
		$this->assertSame( '', dtac_give_sanitize_content_id( '' ) );
		$this->assertSame( '', dtac_give_sanitize_content_id( 'c' ) );
	}

	/**
	 * Query-string helper reads dtac_give_content from a form URL.
	 *
	 * @return void
	 */
	public function test_content_id_from_url() {

		$url = 'https://example.com/donate/?dtac_give_content=88&utm_source=test';

		$this->assertSame( '88', dtac_give_content_id_from_url( $url ) );
		$this->assertSame( '', dtac_give_content_id_from_url( 'https://example.com/donate/' ) );
	}

	/**
	 * Requested content ID prefers sanitized POST, then GET, then cookie.
	 *
	 * @return void
	 */
	public function test_requested_content_id_reads_post_then_cookie() {

		$_POST['dtac_give_content'] = '15';

		$this->assertSame( '15', dtac_give_get_requested_content_id() );

		unset( $_POST['dtac_give_content'] );
		$_COOKIE['dtac_give_pending_content'] = 'site';

		$this->assertSame( 'site', dtac_give_get_requested_content_id() );
	}
}
