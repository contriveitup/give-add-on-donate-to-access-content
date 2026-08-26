<?php
/**
 * Tests for settings-page grouping and themediaable cross-sell.
 *
 * @package DTAC_Give
 */

use PHPUnit\Framework\TestCase;

/**
 * Settings page markup and grouping tests.
 */
class DTAC_Give_Settings_Page_Test extends TestCase {

	/**
	 * Settings template exists and includes the Signals Dispatch card.
	 *
	 * @return void
	 */
	public function test_settings_template_includes_cross_sell() {

		$template = dirname( __DIR__ ) . '/includes/admin/html/settings.php';

		$this->assertFileExists( $template );

		$source = file_get_contents( $template );

		$this->assertIsString( $source );
		$this->assertStringContainsString( 'dtac-settings-hero', $source );
		$this->assertStringContainsString( '\\DTAC\\Admin\\Cross_Sell::render()', $source );
		$this->assertStringContainsString( '\\DTAC\\Admin\\Insights::render_summary()', $source );
		$this->assertStringNotContainsString( "class_exists( '\\DTAC\\Admin\\Cross_Sell', false )", $source );
	}

	/**
	 * Cross-sell class points at the themediaable Signals Dispatch plugin.
	 *
	 * @return void
	 */
	public function test_cross_sell_targets_signals_dispatch() {

		$file = dirname( __DIR__ ) . '/src/Admin/Cross_Sell.php';

		$this->assertFileExists( $file );

		$source = file_get_contents( $file );

		$this->assertIsString( $source );
		$this->assertStringContainsString( 'signals-dispatch-for-woocommerce', $source );
		$this->assertStringContainsString( 'https://wordpress.org/plugins/signals-dispatch-for-woocommerce/', $source );
		$this->assertStringContainsString( 'themediaable', $source );
	}

	/**
	 * Settings array keeps existing option IDs and uses heading groups.
	 *
	 * @return void
	 */
	public function test_settings_keep_option_ids_and_add_headings() {

		$file = dirname( __DIR__ ) . '/src/Admin/Settings.php';

		$this->assertFileExists( $file );

		$source = file_get_contents( $file );

		$this->assertIsString( $source );
		$this->assertStringContainsString( "'type' => 'heading'", $source );
		$this->assertStringContainsString( 'dtac_give_restrict_access_give_form_id', $source );
		$this->assertStringContainsString( 'dtac_give_restrict_message', $source );
		$this->assertStringContainsString( 'dtac_give_restrict_access_to', $source );
		$this->assertStringContainsString( 'dtac_give_restrict_access_to_pages', $source );
		$this->assertStringContainsString( 'dtac_give_restrict_access_to_posts', $source );
		$this->assertStringContainsString( 'dtac_give_restrict_access_to_cpt', $source );
		$this->assertStringContainsString( 'dtac_give_restrict_access_to_cats', $source );
		$this->assertStringContainsString( 'dtac_give_restrict_access_to_custom_tax', $source );
		$this->assertStringContainsString( 'dtac_give_access_to_pages', $source );
		$this->assertStringContainsString( 'dtac_give_min_amount', $source );
		$this->assertStringContainsString( 'dtac_give_access_expires_days', $source );
		$this->assertStringContainsString( 'dtac_give_leak_mode', $source );
	}

	/**
	 * Form renderer skips heading fields instead of outputting inputs for them.
	 *
	 * @return void
	 */
	public function test_form_renderer_handles_heading_fields() {

		$file = dirname( __DIR__ ) . '/src/Controllers/Form/Form.php';

		$this->assertFileExists( $file );

		$source = file_get_contents( $file );

		$this->assertIsString( $source );
		$this->assertStringContainsString( "&& 'heading' === \$settings['type']", $source );
		$this->assertStringContainsString( 'open_settings_section', $source );
		$this->assertStringContainsString( 'close_settings_section', $source );
	}

	/**
	 * Process still writes dtac_give_settings and drops private keys.
	 *
	 * @return void
	 */
	public function test_process_keeps_settings_option_name() {

		$file = dirname( __DIR__ ) . '/src/Controllers/Form/Process.php';

		$this->assertFileExists( $file );

		$source = file_get_contents( $file );

		$this->assertIsString( $source );
		$this->assertStringContainsString( "update_option( 'dtac_give_settings', \$post_data )", $source );
		$this->assertStringContainsString( "0 === strpos( \$key, '_' )", $source );
	}
}
