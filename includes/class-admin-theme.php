<?php
/**
 * Admin theme mode preferences.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Admin_Theme {
	private const META_KEY = 'cten_admin_theme_mode';

	public static function hooks(): void {
		add_action( 'admin_post_cten_save_admin_theme', array( __CLASS__, 'save' ) );
		add_filter( 'admin_body_class', array( __CLASS__, 'body_class' ) );
	}

	public static function current_mode(): string {
		$mode = get_user_meta( get_current_user_id(), self::META_KEY, true );
		$mode = is_string( $mode ) ? $mode : 'system';
		return in_array( $mode, array( 'system', 'light', 'dark' ), true ) ? $mode : 'system';
	}

	public static function body_class( string $classes ): string {
		if ( ! is_admin() ) {
			return $classes;
		}

		$classes .= ' cten-admin-theme-' . self::current_mode();
		return $classes;
	}

	public static function save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'chat-trigger-embed-for-n8n' ) );
		}

		check_admin_referer( 'cten_save_admin_theme', 'cten_admin_theme_nonce' );

		$mode = isset( $_POST['admin_theme_mode'] ) ? sanitize_key( wp_unslash( $_POST['admin_theme_mode'] ) ) : 'system';
		if ( ! in_array( $mode, array( 'system', 'light', 'dark' ), true ) ) {
			$mode = 'system';
		}

		update_user_meta( get_current_user_id(), self::META_KEY, $mode );
		set_transient( 'cten_admin_message', __( 'Admin theme updated.', 'chat-trigger-embed-for-n8n' ), 60 );

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=cten-dashboard' ) );
		exit;
	}
}
