<?php

/**
 * Tests for settings sanitization and restriction-message XSS hardening.
 *
 * @package DTAC_Give
 */

use PHPUnit\Framework\TestCase;

/**
 * Security sanitization tests.
 */
class DTAC_Give_Security_Sanitize_Test extends TestCase {

	/**
	 * Reset stubs.
	 *
	 * @return void
	 */
	protected function setUp(): void {

		parent::setUp();

		$GLOBALS['dtac_give_test_settings']  = array();
		$GLOBALS['dtac_give_test_posts']     = array();
		$GLOBALS['dtac_give_test_terms']     = array();
		$GLOBALS['dtac_give_test_post_meta'] = array();
		$_COOKIE                             = array();
	}

	/**
	 * Reset globals after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {

		$GLOBALS['dtac_give_test_settings']  = array();
		$GLOBALS['dtac_give_test_posts']     = array();
		$GLOBALS['dtac_give_test_terms']     = array();
		$GLOBALS['dtac_give_test_post_meta'] = array();
		$_GET                                = array();
		$_POST                               = array();
		$_REQUEST                            = array();
		$_COOKIE                             = array();

		parent::tearDown();
	}

	/**
	 * Sanitizer keeps known keys, drops extras, and KSES the message.
	 *
	 * @return void
	 */
	public function test_sanitize_settings_whitelists_and_kses_message() {

		$clean = dtac_give_sanitize_settings(
			array(
				'dtac_give_restrict_access_give_form_id' => '12abc',
				'dtac_give_restrict_message'             => '<p>Donate <strong>now</strong><script>alert(1)</script></p>',
				'dtac_give_restrict_access_to'           => array( 'pages', 'evil', 'posts' ),
				'dtac_give_restrict_access_to_pages'     => array( '10', '0', '11' ),
				'dtac_give_restrict_access_to_cpt'       => array( 'book', 'Bad Type' ),
				'dtac_give_min_amount'                   => '1,000.50',
				'dtac_give_access_expires_days'          => '-3',
				'dtac_give_leak_mode'                    => 'explode',
				'dtac_give_restrict_website'             => 'yes',
				'injected_option'                        => '<script>alert(1)</script>',
			)
		);

		$this->assertSame( '12', $clean['dtac_give_restrict_access_give_form_id'] );
		$this->assertStringContainsString( '<p>Donate <strong>now</strong></p>', $clean['dtac_give_restrict_message'] );
		$this->assertStringNotContainsString( '<script>', $clean['dtac_give_restrict_message'] );
		$this->assertSame( array( 'pages', 'posts' ), $clean['dtac_give_restrict_access_to'] );
		$this->assertSame( array( '10', '11' ), $clean['dtac_give_restrict_access_to_pages'] );
		$this->assertSame( array( 'book', 'badtype' ), $clean['dtac_give_restrict_access_to_cpt'] );
		$this->assertSame( '1000.5', $clean['dtac_give_min_amount'] );
		$this->assertSame( '3', $clean['dtac_give_access_expires_days'] );
		$this->assertSame( 'hide', $clean['dtac_give_leak_mode'] );
		$this->assertSame( 'yes', $clean['dtac_give_restrict_website'] );
		$this->assertArrayNotHasKey( 'injected_option', $clean );
	}

	/**
	 * Unchecked multi-selects stay empty arrays; zero amount stays "0".
	 *
	 * @return void
	 */
	public function test_sanitize_settings_preserves_empty_lists_and_zero_amount() {

		$clean = dtac_give_sanitize_settings(
			array(
				'dtac_give_restrict_access_to'       => 'none',
				'dtac_give_restrict_access_to_pages' => array(),
				'dtac_give_min_amount'               => '0',
				'dtac_give_leak_mode'                => 'excerpt',
			)
		);

		$this->assertSame( array(), $clean['dtac_give_restrict_access_to'] );
		$this->assertSame( array(), $clean['dtac_give_restrict_access_to_pages'] );
		$this->assertSame( '0', $clean['dtac_give_min_amount'] );
		$this->assertSame( 'excerpt', $clean['dtac_give_leak_mode'] );
	}

	/**
	 * Restriction message output is KSES'd and the form URL is escaped.
	 *
	 * @return void
	 */
	public function test_restriction_message_output_strips_scripts() {

		$GLOBALS['dtac_give_test_settings'] = array(
			'dtac_give_restrict_message' => '<p>Please donate <script>alert(1)</script> at %%donation_form_url%%</p>',
		);

		$output = dtac_give_get_restriction_output( 12, 'message', 99 );

		$this->assertStringNotContainsString( '<script>', $output );
		$this->assertStringContainsString( 'Please donate', $output );
		$this->assertStringContainsString( 'dtac_give_content=99', $output );
		$this->assertStringNotContainsString( '%%donation_form_url%%', $output );
	}
}
