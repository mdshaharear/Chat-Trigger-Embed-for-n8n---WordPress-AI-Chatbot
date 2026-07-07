<?php
/**
 * Versioned database migrations.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Migrations {
	public const OPTION_NAME = 'cten_db_version';
	public const BACKUP_OPTION = 'cten_settings_migration_backup';

	public static function installed_version(): string {
		$version = get_option( self::OPTION_NAME, '' );
		return is_string( $version ) ? $version : '';
	}

	public static function maybe_run(): void {
		$installed = self::installed_version();
		if ( CTEN_VERSION === $installed ) {
			return;
		}

		$settings = Helpers::get_settings();
		update_option(
			self::BACKUP_OPTION,
			array(
				'created_at' => gmdate( 'c' ),
				'from'       => $installed ?: 'unknown',
				'to'         => CTEN_VERSION,
				'settings'   => $settings,
			),
			false
		);

		try {
			if ( '' === $installed || version_compare( $installed, '1.3.0', '<' ) ) {
				$settings = self::migrate_to_130( $settings );
			}
			if ( version_compare( $installed ?: '0.0.0', '1.4.0', '<' ) ) {
				$settings = self::migrate_to_140( $settings );
			}

			update_option( Helpers::option_name(), Settings::sanitize( $settings ), false );
			update_option( self::OPTION_NAME, CTEN_VERSION, false );
			delete_transient( 'cten_migration_error' );
		} catch ( \Throwable $error ) {
			set_transient( 'cten_migration_error', self::safe_error_message( $error ), 300 );
		}
	}

	public static function migrate_to_130( array $settings ): array {
		$settings['quick_actions']    = Settings::sanitize_quick_actions( $settings['quick_actions'] ?? array() );
		$settings['initial_messages'] = Settings::sanitize_initial_messages( $settings['initial_messages'] ?? array() );
		$settings['metadata_fields']  = wp_parse_args( $settings['metadata_fields'] ?? array(), Settings::defaults()['metadata_fields'] );
		return $settings;
	}

	public static function migrate_to_140( array $settings ): array {
		if ( empty( $settings['profiles'] ) || ! is_array( $settings['profiles'] ) ) {
			$settings['profiles'] = Profiles::bootstrap_profiles_from_settings( $settings );
		}

		$settings['profiles']          = Profiles::sanitize_profiles( $settings['profiles'], $settings );
		$settings['onboarding_status'] = isset( $settings['onboarding_status'] ) && is_array( $settings['onboarding_status'] )
			? $settings['onboarding_status']
			: array(
				'started'   => false,
				'completed' => false,
				'step'      => 'welcome',
			);

		$settings['pre_chat_form']      = Settings::sanitize_pre_chat_form( $settings['pre_chat_form'] ?? array() );
		$settings['lead_qualification'] = Settings::sanitize_lead_qualification( $settings['lead_qualification'] ?? array() );

		return $settings;
	}

	private static function safe_error_message( \Throwable $error ): string {
		return substr( sanitize_text_field( $error->getMessage() ), 0, 240 );
	}
}
