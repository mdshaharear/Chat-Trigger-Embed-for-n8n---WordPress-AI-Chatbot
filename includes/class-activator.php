<?php
/**
 * Activation logic.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Activator {
	public static function activate(): void {
		if ( ! Helpers::requirements_met() ) {
			set_transient( 'cten_activation_notice', Helpers::requirements_message(), 30 );
			return;
		}

		$defaults = Settings::defaults();
		$current  = get_option( Helpers::option_name(), null );
		if ( null === $current ) {
			add_option( Helpers::option_name(), $defaults, '', false );
			add_option( Migrations::OPTION_NAME, CTEN_VERSION, '', false );
			update_option( 'cten_show_onboarding', true, false );
		}

		$public_css = Helpers::asset_path( 'dist/chat-trigger-embed.css' );
		$public_js  = Helpers::asset_path( 'dist/chat-trigger-embed.js' );
		if ( ! file_exists( $public_css ) || ! file_exists( $public_js ) ) {
			set_transient( 'cten_activation_notice', __( 'The plugin is installed, but compiled frontend assets are missing from the ZIP.', 'chat-trigger-embed-for-n8n' ), 30 );
		}
	}
}
