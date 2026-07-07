<?php
/**
 * Site Health integration.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Site_Health {
	public static function hooks(): void {
		add_filter( 'site_status_tests', array( __CLASS__, 'site_status_tests' ) );
		add_filter( 'debug_information', array( __CLASS__, 'debug_information' ) );
	}

	public static function site_status_tests( array $tests ): array {
		$tests['direct']['cten_configuration'] = array(
			'label' => __( 'Chat Trigger Embed for n8n configuration', 'chat-trigger-embed-for-n8n' ),
			'test'  => array( __CLASS__, 'test_configuration' ),
		);
		$tests['direct']['cten_assets'] = array(
			'label' => __( 'Chat Trigger Embed for n8n assets', 'chat-trigger-embed-for-n8n' ),
			'test'  => array( __CLASS__, 'test_assets' ),
		);
		$tests['direct']['cten_migrations'] = array(
			'label' => __( 'Chat Trigger Embed for n8n migrations', 'chat-trigger-embed-for-n8n' ),
			'test'  => array( __CLASS__, 'test_migrations' ),
		);
		return $tests;
	}

	public static function test_configuration(): array {
		$settings = Helpers::get_settings();
		$status = ! empty( $settings['webhook_url'] ) && ! Safe_Mode::is_enabled() ? 'good' : 'recommended';
		return array(
			'label'       => __( 'Chat Trigger Embed for n8n configuration is available.', 'chat-trigger-embed-for-n8n' ),
			'status'      => $status,
			'badge'       => array( 'label' => $status === 'good' ? __( 'Good', 'chat-trigger-embed-for-n8n' ) : __( 'Recommended', 'chat-trigger-embed-for-n8n' ), 'color' => $status === 'good' ? 'blue' : 'orange' ),
			'description' => __( 'The Runtime Lab can surface detailed configuration warnings and profile resolution data.', 'chat-trigger-embed-for-n8n' ),
			'test'        => 'cten_configuration',
		);
	}

	public static function test_assets(): array {
		$ok = file_exists( Helpers::asset_path( 'dist/chat-trigger-embed.js' ) ) && file_exists( Helpers::asset_path( 'dist/chat-trigger-embed.css' ) );
		return array(
			'label'       => __( 'Chat Trigger Embed for n8n assets are present.', 'chat-trigger-embed-for-n8n' ),
			'status'      => $ok ? 'good' : 'critical',
			'badge'       => array( 'label' => $ok ? __( 'Good', 'chat-trigger-embed-for-n8n' ) : __( 'Critical', 'chat-trigger-embed-for-n8n' ), 'color' => $ok ? 'blue' : 'red' ),
			'description' => $ok ? __( 'Compiled assets are present in the release ZIP.', 'chat-trigger-embed-for-n8n' ) : __( 'One or more compiled assets are missing from the release ZIP.', 'chat-trigger-embed-for-n8n' ),
			'test'        => 'cten_assets',
		);
	}

	public static function test_migrations(): array {
		$ok = CTEN_VERSION === Migrations::installed_version();
		return array(
			'label'       => __( 'Chat Trigger Embed for n8n migration status is current.', 'chat-trigger-embed-for-n8n' ),
			'status'      => $ok ? 'good' : 'recommended',
			'badge'       => array( 'label' => $ok ? __( 'Good', 'chat-trigger-embed-for-n8n' ) : __( 'Recommended', 'chat-trigger-embed-for-n8n' ), 'color' => $ok ? 'blue' : 'orange' ),
			'description' => $ok ? __( 'Database version matches the plugin version.', 'chat-trigger-embed-for-n8n' ) : __( 'Database version differs from the plugin version and should be checked in Runtime Lab.', 'chat-trigger-embed-for-n8n' ),
			'test'        => 'cten_migrations',
		);
	}

	public static function debug_information( array $debug ): array {
		$settings = Helpers::get_settings();
		$last_live_test = get_option( 'cten_last_live_test', array() );
		$last_live_status = is_array( $last_live_test ) ? (string) ( $last_live_test['status'] ?? __( 'Not run', 'chat-trigger-embed-for-n8n' ) ) : __( 'Not run', 'chat-trigger-embed-for-n8n' );
		$debug['cten-chat-trigger'] = array(
			'label'  => __( 'Chat Trigger Embed for n8n', 'chat-trigger-embed-for-n8n' ),
			'fields' => array(
				'plugin_version'   => array( 'label' => __( 'Plugin version', 'chat-trigger-embed-for-n8n' ), 'value' => CTEN_VERSION ),
				'database_version'  => array( 'label' => __( 'Database version', 'chat-trigger-embed-for-n8n' ), 'value' => Migrations::installed_version() ),
				'n8n_runtime'      => array( 'label' => __( '@n8n/chat version', 'chat-trigger-embed-for-n8n' ), 'value' => '1.26.0' ),
				'enabled_profiles'  => array( 'label' => __( 'Enabled profiles', 'chat-trigger-embed-for-n8n' ), 'value' => count( array_filter( (array) $settings['profiles'], static fn( array $profile ): bool => ! empty( $profile['enabled'] ) ) ) ),
				'lazy_loading'     => array( 'label' => __( 'Lazy loading', 'chat-trigger-embed-for-n8n' ), 'value' => ! empty( $settings['lazy_load_runtime'] ) ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ),
				'analytics_status' => array( 'label' => __( 'Analytics status', 'chat-trigger-embed-for-n8n' ), 'value' => ! empty( $settings['analytics_enabled'] ) ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ),
				'safe_mode'        => array( 'label' => __( 'Safe mode', 'chat-trigger-embed-for-n8n' ), 'value' => Safe_Mode::is_enabled() ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ),
				'last_live_test'   => array( 'label' => __( 'Last live-test status', 'chat-trigger-embed-for-n8n' ), 'value' => $last_live_status ),
			),
		);
		return $debug;
	}
}
