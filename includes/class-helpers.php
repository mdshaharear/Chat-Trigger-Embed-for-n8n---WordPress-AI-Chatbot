<?php
/**
 * Helper functions.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Helpers {
	public static function requirements_met(): bool {
		global $wp_version;

		return version_compare( PHP_VERSION, CTEN_MIN_PHP, '>=' ) && version_compare( (string) $wp_version, CTEN_MIN_WP, '>=' );
	}

	public static function requirements_message(): string {
		global $wp_version;

		return sprintf(
			/* translators: 1: PHP version, 2: WordPress version */
			__( 'Chat Trigger Embed for n8n requires PHP %1$s or later and WordPress %2$s or later.', 'chat-trigger-embed-for-n8n' ),
			CTEN_MIN_PHP,
			CTEN_MIN_WP
		);
	}

	public static function option_name(): string {
		return 'cten_settings';
	}

	public static function get_settings(): array {
		$saved = get_option( self::option_name(), array() );
		$settings = wp_parse_args( is_array( $saved ) ? $saved : array(), Settings::defaults() );
		$settings['quick_actions'] = Settings::sanitize_quick_actions( $settings['quick_actions'] ?? array(), 30 );
		$settings['initial_messages'] = Settings::sanitize_initial_messages( $settings['initial_messages'] ?? array(), 20 );
		$settings['metadata_fields'] = wp_parse_args( $settings['metadata_fields'] ?? array(), Settings::defaults()['metadata_fields'] );
		$settings['visibility'] = wp_parse_args( $settings['visibility'] ?? array(), Settings::defaults()['visibility'] );
		$settings['pre_chat_form'] = Settings::sanitize_pre_chat_form( $settings['pre_chat_form'] ?? array() );
		$settings['lead_qualification'] = Settings::sanitize_lead_qualification( $settings['lead_qualification'] ?? array() );
		$settings['profiles'] = Profiles::sanitize_profiles( $settings['profiles'] ?? array(), $settings );
		return $settings;
	}

	public static function get_origin(): string {
		return home_url();
	}

	public static function internal_use_notice(): string {
		return __( 'Open-source release: bundled third-party components retain their original licenses. See THIRD_PARTY_NOTICES.md for details.', 'chat-trigger-embed-for-n8n' );
	}

	public static function clear_temporary_notices(): void {
		delete_transient( 'cten_activation_notice' );
		delete_transient( 'cten_admin_message' );
		delete_transient( 'cten_admin_error' );
	}

	public static function is_test_webhook_url( string $url ): bool {
		return '' !== $url && str_contains( strtolower( $url ), 'webhook-test' );
	}

	public static function webhook_health( string $url ): array {
		$url   = trim( $url );
		$label = __( 'Not configured', 'chat-trigger-embed-for-n8n' );
		$state = 'missing';
		$help  = __( 'Paste the production Chat Trigger URL from an active workflow.', 'chat-trigger-embed-for-n8n' );

		if ( '' === $url ) {
			return array( 'state' => $state, 'label' => $label, 'help' => $help );
		}

		if ( self::is_test_webhook_url( $url ) ) {
			return array(
				'state' => 'test',
				'label' => __( 'Looks like a test URL', 'chat-trigger-embed-for-n8n' ),
				'help'  => __( 'Use the production Chat Trigger URL instead of the test webhook.', 'chat-trigger-embed-for-n8n' ),
			);
		}

		$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
		if ( 'https' !== $scheme && ! self::is_localhost_url( $url ) ) {
			return array(
				'state' => 'insecure',
				'label' => __( 'HTTPS recommended', 'chat-trigger-embed-for-n8n' ),
				'help'  => __( 'Use HTTPS for production sites. Localhost is allowed for local development.', 'chat-trigger-embed-for-n8n' ),
			);
		}

		if ( ! wp_http_validate_url( $url ) ) {
			return array(
				'state' => 'invalid',
				'label' => __( 'Invalid URL', 'chat-trigger-embed-for-n8n' ),
				'help'  => __( 'The URL structure is not valid. Copy the production URL directly from n8n.', 'chat-trigger-embed-for-n8n' ),
			);
		}

		return array(
			'state' => 'ok',
			'label' => __( 'Production URL looks valid', 'chat-trigger-embed-for-n8n' ),
			'help'  => __( 'Final end-to-end verification still requires a real chat message against an active workflow.', 'chat-trigger-embed-for-n8n' ),
		);
	}

	public static function setup_completion( array $settings ): int {
		$checks = array(
			! empty( $settings['webhook_url'] ),
			! empty( $settings['enabled'] ),
			! self::is_test_webhook_url( (string) ( $settings['webhook_url'] ?? '' ) ),
			! empty( $settings['load_previous_session'] ) || ! empty( $settings['enable_streaming'] ),
			! empty( $settings['quick_actions'] ) && ! empty( array_filter( (array) $settings['quick_actions'], static fn( array $item ): bool => ! empty( $item['enabled'] ) ) ),
			! empty( $settings['theme_preset'] ),
			! empty( $settings['visibility'] ) && is_array( $settings['visibility'] ),
			file_exists( self::asset_path( 'dist/chat-trigger-embed.js' ) ) && file_exists( self::asset_path( 'dist/chat-trigger-embed.css' ) ),
		);

		$completed = count( array_filter( $checks ) );
		return (int) round( ( $completed / count( $checks ) ) * 100 );
	}

	public static function device_visibility_label( array $settings ): string {
		$devices = isset( $settings['visibility']['devices'] ) && is_array( $settings['visibility']['devices'] ) ? array_map( 'sanitize_key', $settings['visibility']['devices'] ) : array();
		if ( ! $devices ) {
			return __( 'No devices selected', 'chat-trigger-embed-for-n8n' );
		}

		return implode( ', ', array_map( 'ucfirst', $devices ) );
	}

	public static function is_localhost_url( string $url ): bool {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		return in_array( strtolower( (string) $host ), array( 'localhost', '127.0.0.1', '::1' ), true );
	}

	public static function sanitize_url( string $url ): string {
		$url = trim( $url );
		if ( '' === $url ) {
			return '';
		}

		$url = esc_url_raw( $url );
		if ( '' === $url ) {
			return '';
		}

		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		if ( 'https' !== $scheme && ! self::is_localhost_url( $url ) ) {
			$allow_http = (bool) apply_filters( 'cten_allow_insecure_webhook', false, $url );
			if ( ! $allow_http ) {
				return '';
			}
		}

		if ( self::is_localhost_url( $url ) ) {
			return $url;
		}

		return wp_http_validate_url( $url ) ? $url : '';
	}

	public static function allowed_http_url( string $url ): bool {
		return self::is_localhost_url( $url ) || (bool) apply_filters( 'cten_allow_insecure_webhook', false, $url );
	}

	public static function file_version( string $path ): string {
		return file_exists( $path ) ? (string) filemtime( $path ) : CTEN_VERSION;
	}

	public static function asset_url( string $relative_path ): string {
		return CTEN_URL . ltrim( $relative_path, '/' );
	}

	public static function asset_path( string $relative_path ): string {
		return CTEN_DIR . ltrim( $relative_path, '/' );
	}

	public static function is_plugin_screen(): bool {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		return $screen && str_contains( (string) $screen->id, 'cten' );
	}
}
