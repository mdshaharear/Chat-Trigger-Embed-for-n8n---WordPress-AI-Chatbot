<?php
/**
 * Import/export handlers.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Import_Export {
	public static function hooks(): void {
		add_action( 'admin_post_cten_export_settings', array( __CLASS__, 'export' ) );
		add_action( 'admin_post_cten_import_settings', array( __CLASS__, 'import' ) );
		add_action( 'admin_post_cten_reset_settings', array( __CLASS__, 'reset' ) );
	}

	public static function export(): void {
		self::guard();
		$settings = Helpers::get_settings();

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="chat-trigger-embed-settings.json"' );
		echo wp_json_encode(
			array(
				'version'  => CTEN_VERSION,
				'exported' => gmdate( 'c' ),
				'settings' => $settings,
			),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		);
		exit;
	}

	public static function import(): void {
		self::guard();

		$raw = isset( $_POST['cten_import_json'] ) ? wp_unslash( $_POST['cten_import_json'] ) : '';
		$data = json_decode( (string) $raw, true );
		if ( ! is_array( $data ) || empty( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
			self::redirect_with_error( __( 'Invalid settings file.', 'chat-trigger-embed-for-n8n' ) );
		}

		$version  = (string) ( $data['version'] ?? '' );
		$settings = Settings::sanitize( (array) $data['settings'] );
		update_option( Helpers::option_name(), $settings, false );

		self::redirect_with_message( sprintf( __( 'Settings imported from version %s.', 'chat-trigger-embed-for-n8n' ), $version ?: CTEN_VERSION ) );
	}

	public static function reset(): void {
		self::guard();
		update_option( Helpers::option_name(), Settings::defaults(), false );
		self::redirect_with_message( __( 'Settings were reset to defaults.', 'chat-trigger-embed-for-n8n' ) );
	}

	private static function guard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'chat-trigger-embed-for-n8n' ) );
		}
		check_admin_referer( 'cten_tools_action', 'cten_tools_nonce' );
	}

	private static function redirect_with_message( string $message ): void {
		set_transient( 'cten_admin_message', $message, 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=cten-tools' ) );
		exit;
	}

	private static function redirect_with_error( string $message ): void {
		set_transient( 'cten_admin_error', $message, 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=cten-tools' ) );
		exit;
	}
}
