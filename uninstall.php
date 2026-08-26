<?php

/**
 * Uninstall Donate To Access Content.
 *
 * @package DTAC_Give
 *
 * @since 3.0.0
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

delete_option( 'dtac_give_settings' );

if ( is_multisite() ) {
	delete_site_option( 'dtac_give_settings' );
}

if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
	return;
}

$donation_meta_key = '_dtac_give_access_to_content';
$donor_meta_key    = 'give_dtca_access_website';

$post_meta_keys = array(
	$donation_meta_key,
	'_dtac_give_restrict',
	'_dtac_give_form_id',
	'_dtac_give_min_amount',
	'_dtac_give_expiry_days',
);

if ( ! empty( $wpdb->postmeta ) ) {
	foreach ( $post_meta_keys as $meta_key ) {
		$wpdb->delete(
			$wpdb->postmeta,
			array( 'meta_key' => $meta_key ),
			array( '%s' )
		);
	}
}

if ( ! empty( $wpdb->give_donationmeta ) ) {
	$wpdb->delete(
		$wpdb->give_donationmeta,
		array( 'meta_key' => $donation_meta_key ),
		array( '%s' )
	);
}

if ( ! empty( $wpdb->give_donormeta ) ) {
	$wpdb->delete(
		$wpdb->give_donormeta,
		array( 'meta_key' => $donor_meta_key ),
		array( '%s' )
	);
}
