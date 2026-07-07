<?php
/**
 * Asset registration.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Assets {
	public static function register(): void {
		wp_register_style(
			'cten-public-vendor',
			Helpers::asset_url( 'dist/vendor/n8n-chat/style.css' ),
			array(),
			Helpers::file_version( Helpers::asset_path( 'dist/vendor/n8n-chat/style.css' ) )
		);
		wp_register_style(
			'cten-public',
			Helpers::asset_url( 'dist/chat-trigger-embed.css' ),
			array( 'cten-public-vendor' ),
			Helpers::file_version( Helpers::asset_path( 'dist/chat-trigger-embed.css' ) )
		);
		wp_register_script(
			'cten-public',
			Helpers::asset_url( 'dist/chat-trigger-embed.js' ),
			array(),
			Helpers::file_version( Helpers::asset_path( 'dist/chat-trigger-embed.js' ) ),
			true
		);

		wp_register_style(
			'cten-admin',
			Helpers::asset_url( 'admin/css/admin.css' ),
			array(),
			Helpers::file_version( Helpers::asset_path( 'admin/css/admin.css' ) )
		);
		wp_register_script(
			'cten-admin',
			Helpers::asset_url( 'admin/js/admin.js' ),
			array(),
			Helpers::file_version( Helpers::asset_path( 'admin/js/admin.js' ) ),
			true
		);
	}

	public static function enqueue_public(): void {
		self::register();

		$settings = Helpers::get_settings();
		if ( ! Settings::allows_display( $settings ) ) {
			return;
		}

		wp_enqueue_style( 'cten-public-vendor' );
		wp_enqueue_style( 'cten-public' );
		wp_enqueue_script( 'cten-public' );
	}

	public static function enqueue_admin(): void {
		if ( ! Helpers::is_plugin_screen() ) {
			return;
		}

		wp_enqueue_style( 'cten-admin' );
		wp_enqueue_script( 'cten-admin' );
		wp_localize_script(
			'cten-admin',
			'ctenAdmin',
			array(
				'origin'         => Helpers::get_origin(),
				'previewSelector' => '#cten-admin-preview',
				'quickActionIcon' => 'dashicons-admin-links',
			)
		);
	}

	public static function add_script_type_module( string $tag, string $handle, string $src ): string {
		if ( 'cten-public' === $handle ) {
			return sprintf( '<script type="module" src="%s" id="%s-js"></script>', esc_url( $src ), esc_attr( $handle ) );
		}

		return $tag;
	}
}
