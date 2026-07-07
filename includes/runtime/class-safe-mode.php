<?php
/**
 * Safe mode controls.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Safe_Mode {
	public const OPTION_NAME = 'cten_safe_mode';
	public const FAILURE_OPTION = 'cten_runtime_failure_state';

	public static function defaults(): array {
		return array(
			'enabled'                    => false,
			'reason'                     => '',
			'auto_enable'                => false,
			'failure_threshold'          => 3,
			'recovery_instructions'      => '',
			'fallback_link'              => '',
			'last_activated_at'          => '',
			'last_failure_at'            => '',
		);
	}

	public static function get(): array {
		$stored = get_option( self::OPTION_NAME, array() );
		return wp_parse_args( is_array( $stored ) ? $stored : array(), self::defaults() );
	}

	public static function is_enabled(): bool {
		$state = self::get();
		return ! empty( $state['enabled'] );
	}

	public static function should_block_public_chat(): bool {
		return self::is_enabled();
	}

	public static function enable( string $reason = '', bool $auto = false ): array {
		$state = self::get();
		$state['enabled']           = true;
		$state['reason']            = sanitize_text_field( $reason );
		$state['last_activated_at'] = gmdate( 'c' );
		$state['auto_enable']       = (bool) $auto;
		update_option( self::OPTION_NAME, $state, false );
		return $state;
	}

	public static function save( array $input ): array {
		$state = wp_parse_args( $input, self::defaults() );
		$sanitized = array(
			'enabled'               => ! empty( $state['enabled'] ),
			'reason'                => sanitize_text_field( (string) $state['reason'] ),
			'auto_enable'           => ! empty( $state['auto_enable'] ),
			'failure_threshold'     => max( 2, min( 10, absint( $state['failure_threshold'] ?? 3 ) ) ),
			'recovery_instructions' => sanitize_textarea_field( (string) $state['recovery_instructions'] ),
			'fallback_link'         => Helpers::sanitize_url( (string) $state['fallback_link'] ),
			'last_activated_at'     => (string) ( $state['last_activated_at'] ?? '' ),
			'last_failure_at'       => (string) ( $state['last_failure_at'] ?? '' ),
		);

		update_option( self::OPTION_NAME, $sanitized, false );
		return $sanitized;
	}

	public static function disable(): array {
		$state = self::get();
		$state['enabled'] = false;
		update_option( self::OPTION_NAME, $state, false );
		return $state;
	}

	public static function clear_failure_state(): void {
		delete_option( self::FAILURE_OPTION );
	}

	public static function record_failure(): void {
		$state = get_option( self::FAILURE_OPTION, array( 'count' => 0, 'window_started_at' => gmdate( 'c' ) ) );
		$count = absint( $state['count'] ?? 0 ) + 1;
		$state['count'] = $count;
		$state['last_failure_at'] = gmdate( 'c' );
		if ( empty( $state['window_started_at'] ) ) {
			$state['window_started_at'] = gmdate( 'c' );
		}
		update_option( self::FAILURE_OPTION, $state, false );

		$current = self::get();
		if ( ! empty( $current['auto_enable'] ) && $count >= absint( $current['failure_threshold'] ?? 3 ) ) {
			self::enable( __( 'Automatic safe mode activated after repeated initialization failures.', 'chat-trigger-embed-for-n8n' ), true );
		}
	}

	public static function clear_runtime_flags(): void {
		self::clear_failure_state();
		delete_transient( 'cten_runtime_test_state' );
		delete_option( 'cten_runtime_test_events' );
	}
}
