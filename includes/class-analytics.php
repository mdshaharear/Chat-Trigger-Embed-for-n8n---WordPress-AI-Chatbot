<?php
/**
 * Privacy-conscious local analytics.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Analytics {
	public static function hooks(): void {
		add_action( 'init', array( __CLASS__, 'maybe_schedule_cleanup' ) );
		add_action( 'cten_analytics_cleanup', array( __CLASS__, 'cleanup_hook' ) );
	}

	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'cten_analytics_events';
	}

	public static function maybe_create_table( array $settings ): void {
		if ( empty( $settings['analytics_enabled'] ) ) {
			return;
		}

		self::create_table();
	}

	public static function create_table(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$table           = self::table_name();

		$sql = "CREATE TABLE {$table} (
			event_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			session_hash char(64) NOT NULL DEFAULT '',
			event_type varchar(64) NOT NULL DEFAULT '',
			page_id bigint(20) unsigned NOT NULL DEFAULT 0,
			page_path varchar(255) NOT NULL DEFAULT '',
			device_group varchar(20) NOT NULL DEFAULT '',
			utm_source varchar(100) NOT NULL DEFAULT '',
			utm_campaign varchar(100) NOT NULL DEFAULT '',
			created_at datetime NOT NULL,
			PRIMARY KEY  (event_id),
			KEY event_type_created_at (event_type, created_at),
			KEY created_at (created_at),
			KEY page_id (page_id)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	public static function delete_table(): void {
		global $wpdb;

		$table = self::table_name();
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	public static function table_exists(): bool {
		global $wpdb;

		$table = self::table_name();
		return $table === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	}

	public static function retention_days( array $settings ): int {
		$days = absint( $settings['analytics_retention_days'] ?? 30 );
		return max( 7, min( 180, $days ) );
	}

	public static function cleanup( array $settings ): int {
		if ( empty( $settings['analytics_enabled'] ) || ! self::table_exists() ) {
			return 0;
		}

		global $wpdb;

		$table  = self::table_name();
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS * self::retention_days( $settings ) );

		return (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s LIMIT 500", $cutoff ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	public static function cleanup_hook(): void {
		self::cleanup( Helpers::get_settings() );
	}

	public static function maybe_schedule_cleanup(): void {
		$settings = Helpers::get_settings();
		if ( empty( $settings['analytics_enabled'] ) ) {
			return;
		}

		if ( ! wp_next_scheduled( 'cten_analytics_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'cten_analytics_cleanup' );
		}
	}

	public static function summary(): array {
		if ( ! self::table_exists() ) {
			return array();
		}

		global $wpdb;

		$table = self::table_name();
		$since = gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS * 30 );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT event_type, COUNT(*) AS total FROM {$table} WHERE created_at >= %s GROUP BY event_type ORDER BY total DESC LIMIT 20",
				$since
			),
			ARRAY_A
		); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return is_array( $rows ) ? $rows : array();
	}
}
