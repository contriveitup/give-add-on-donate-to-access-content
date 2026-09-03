<?php
/**
 * PSR-4 autoloader for the DTAC namespace.
 *
 * Ships with the plugin so WordPress.org packages do not need Composer vendor.
 *
 * @package DTAC_Give
 *
 * @since 3.0.0
 */

defined( 'ABSPATH' ) || exit;

spl_autoload_register(
	static function ( $class_name ) {
		$prefix = 'DTAC\\';

		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( $prefix ) );
		$relative = str_replace( '\\', '/', $relative );
		$file     = dirname( __DIR__ ) . '/src/' . $relative . '.php';

		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);
