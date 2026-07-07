<?php
/**
 * Shared alpha storage helpers.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class V2_Storage {
	public const PROVIDER_CONNECTIONS = 'cten_v2_provider_connections';
	public const CHATBOTS = 'cten_v2_chatbots';
	public const USAGE = 'cten_v2_usage';
	public const MIGRATION_STATE = 'cten_v2_migration_state';

	public static function all( string $option ): array {
		$value = get_option( $option, array() );
		return is_array( $value ) ? $value : array();
	}

	public static function save( string $option, array $value ): void {
		update_option( $option, $value, false );
	}

	public static function remove( string $option ): void {
		delete_option( $option );
	}
}
