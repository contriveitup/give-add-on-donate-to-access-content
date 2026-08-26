<?php

/**
 * Tests that the plugin bootstrap stays parseable on PHP older than 8.1.
 *
 * @package DTAC_Give
 */

use PHPUnit\Framework\TestCase;

/**
 * PHP bootstrap safety tests.
 */
class DTAC_Give_Php_Bootstrap_Test extends TestCase
{

    /**
     * Absolute path to the plugin bootstrap file.
     *
     * @var string
     */
    private $bootstrap;

    /**
     * Locate the bootstrap file.
     *
     * @return void
     */
    protected function setUp(): void
    {

        parent::setUp();

        $this->bootstrap = dirname(__DIR__) . '/cip-give-donate-to-access-content.php';
    }

    /**
     * Bootstrap file exists.
     *
     * @return void
     */
    public function test_bootstrap_file_exists()
    {

        $this->assertFileExists($this->bootstrap);
    }

    /**
     * Bootstrap must not use return or parameter types that fatal on PHP 7.
     *
     * WordPress still loads an already-active plugin after an update, so this
     * file is parsed on PHP 7.4 / 8.0 before any version check can run.
     *
     * @return void
     */
    public function test_bootstrap_has_no_php81_type_syntax()
    {

        $source = file_get_contents($this->bootstrap);

        $this->assertIsString($source);
        $this->assertNotFalse($source);

        $this->assertDoesNotMatchRegularExpression(
            '/\):\s*(void|array|string|bool|int|float|mixed|never)\b/',
            $source,
            'Bootstrap must not declare return types; PHP 7 cannot parse them.'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/\((?:array|string|bool|int|float|mixed)\s+\$/',
            $source,
            'Bootstrap must not declare parameter types; PHP 7 cannot parse them in this file.'
        );
    }

    /**
     * Unsupported PHP must return before the typed main class is required.
     *
     * @return void
     */
    public function test_bootstrap_bails_before_loading_typed_class()
    {

        $source = file_get_contents($this->bootstrap);

        $this->assertIsString($source);
        $this->assertNotFalse($source);

        $check_pos = strpos($source, 'dtac_give_php_version_is_supported');
        $return_pos = strpos($source, 'return;');
        $class_pos  = strpos($source, 'class-donate-to-access-content-give-addon.php');

        $this->assertNotFalse($check_pos);
        $this->assertNotFalse($return_pos);
        $this->assertNotFalse($class_pos);
        $this->assertLessThan($class_pos, $check_pos);
        $this->assertLessThan($class_pos, $return_pos);
    }

    /**
     * Version helper matches the advertised minimum.
     *
     * @return void
     */
    public function test_php_version_helper_uses_min_php_constant()
    {

        $source = file_get_contents($this->bootstrap);

        $this->assertIsString($source);
        $this->assertMatchesRegularExpression("/define\\(\\s*'DTAC_GIVE_MIN_PHP'\\s*,\\s*'8\\.1'\\s*\\)/", $source);
        $this->assertMatchesRegularExpression("/version_compare\\(\\s*PHP_VERSION\\s*,\\s*DTAC_GIVE_MIN_PHP\\s*,\\s*'>='\\s*\\)/", $source);
        $this->assertStringContainsString('dtac_give_deactivate_for_php_version', $source);
        $this->assertStringContainsString('dtac_give_php_version_notice', $source);
    }
}
