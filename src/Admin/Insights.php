<?php

/**
 * Unlock insights in admin.
 *
 * @package DTAC_Give
 *
 * @since 3.0.0
 */

namespace DTAC\Admin;

use DTAC\Give\Give_Adapter;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Donations-list column and settings-page unlock summary.
 *
 * @since 3.0.0
 */
class Insights {


	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		add_filter( 'give_payments_table_columns', array( $this, 'register_column' ) );
		add_filter( 'give_payments_table_column', array( $this, 'render_column' ), 10, 3 );
	}

	/**
	 * Add an unlocked-content column to the donations table.
	 *
	 * @since 3.0.0
	 *
	 * @param array $columns Table columns.
	 *
	 * @return array
	 */
	public function register_column( $columns ): array {

		if ( ! is_array( $columns ) ) {
			$columns = array();
		}

		$columns['dtac_unlocked'] = esc_html__( 'Unlocked Content', 'dtac-give' );

		return $columns;
	}

	/**
	 * Output unlocked content for a donation row.
	 *
	 * @since 3.0.0
	 *
	 * @param string $value       Current cell value.
	 * @param int    $donation_id Donation ID.
	 * @param string $column_name Column key.
	 *
	 * @return string
	 */
	public function render_column( $value, $donation_id, $column_name ): string {

		$value = is_string( $value ) ? $value : '';

		if ( 'dtac_unlocked' !== $column_name ) {
			return $value;
		}

		$content_id = dtac_give_sanitize_content_id(
			dtac_give_adapter()->get_donation_meta( absint( $donation_id ), Give_Adapter::CONTENT_META_KEY )
		);

		if ( '' === $content_id ) {
			return '&mdash;';
		}

		$label = dtac_give_get_content_label( $content_id );
		$url   = dtac_give_get_content_url( $content_id );

		if ( '' !== $url ) {
			return '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
		}

		return esc_html( $label );
	}

	/**
	 * Settings-page summary of unlocks per content ID.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function render_summary(): void {

		$counts = self::get_unlock_counts();

		echo '<h3>' . esc_html__( 'Unlock insights', 'dtac-give' ) . '</h3>';

		if ( empty( $counts ) ) {
			echo '<p>' . esc_html__( 'No content has been unlocked yet.', 'dtac-give' ) . '</p>';
			return;
		}

		echo '<table class="widefat striped"><thead><tr>';
		echo '<th>' . esc_html__( 'Content', 'dtac-give' ) . '</th>';
		echo '<th>' . esc_html__( 'Unlocks', 'dtac-give' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $counts as $content_id => $total ) {
			$label = dtac_give_get_content_label( (string) $content_id );
			echo '<tr><td>' . esc_html( $label ) . '</td><td>' . esc_html( (string) $total ) . '</td></tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Count donation meta rows per unlocked content ID.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string,int>
	 */
	public static function get_unlock_counts(): array {

		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) || ! method_exists( $wpdb, 'get_results' ) || ! method_exists( $wpdb, 'prepare' ) ) {
			return array();
		}

		$counts = array();
		$key    = Give_Adapter::CONTENT_META_KEY;
		$tables = array();

		if ( ! empty( $wpdb->give_donationmeta ) ) {
			$tables[] = $wpdb->give_donationmeta;
		} elseif ( ! empty( $wpdb->postmeta ) ) {
			$tables[] = $wpdb->postmeta;
		}

		foreach ( $tables as $table ) {
			$table = preg_replace( '/[^A-Za-z0-9_]/', '', (string) $table );

			if ( '' === $table ) {
				continue;
			}

			$sql  = "SELECT meta_value, COUNT(*) AS total FROM {$table} WHERE meta_key = %s GROUP BY meta_value";
			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $key ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			if ( ! is_array( $rows ) ) {
				continue;
			}

			foreach ( $rows as $row ) {
				if ( ! is_object( $row ) || empty( $row->meta_value ) ) {
					continue;
				}

				$content_id = dtac_give_sanitize_content_id( $row->meta_value );

				if ( '' === $content_id ) {
					continue;
				}

				$total = isset( $row->total ) ? absint( $row->total ) : 0;

				if ( ! isset( $counts[ $content_id ] ) ) {
					$counts[ $content_id ] = 0;
				}

				$counts[ $content_id ] += $total;
			}
		}

		arsort( $counts );

		return $counts;
	}
}
