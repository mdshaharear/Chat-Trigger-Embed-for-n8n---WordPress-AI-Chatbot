<?php
/**
 * Chatbot profile storage and resolution.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Profiles {
	public static function default_profile( array $settings = array() ): array {
		return array(
			'id'                   => 'main',
			'name'                 => 'Main Website Assistant',
			'description'          => 'Default chatbot profile.',
			'enabled'              => true,
			'is_default'           => true,
			'priority'             => 10,
			'webhook_url'          => (string) ( $settings['webhook_url'] ?? '' ),
			'bot_name'             => (string) ( $settings['bot_name'] ?? 'Chat Trigger AI' ),
			'bot_subtitle'         => (string) ( $settings['bot_subtitle'] ?? 'Powered by your n8n workflow' ),
			'theme_preset'         => (string) ( $settings['theme_preset'] ?? 'premium-glass' ),
			'launcher_label'       => (string) ( $settings['launcher_label'] ?? 'Chat with us' ),
			'initial_messages'     => $settings['initial_messages'] ?? array(),
			'quick_actions'        => $settings['quick_actions'] ?? array(),
			'visibility'           => $settings['visibility'] ?? Settings::defaults()['visibility'],
			'metadata_fields'      => $settings['metadata_fields'] ?? Settings::defaults()['metadata_fields'],
			'campaign_rules'       => array(),
			'page_rules'           => array(),
		);
	}

	public static function example_profiles( array $settings = array() ): array {
		$profiles = array( self::default_profile( $settings ) );
		$examples = array(
			'restaurant-demo' => 'Restaurant Demo',
			'dentist-demo'   => 'Dentist Demo',
			'real-estate'    => 'Real Estate Demo',
			'pricing'        => 'Pricing Assistant',
			'support'        => 'Support Assistant',
		);

		$priority = 20;
		foreach ( $examples as $id => $name ) {
			$profile = self::default_profile( $settings );
			$profile['id']          = $id;
			$profile['name']        = $name;
			$profile['description'] = 'Template profile. Enable and add a production webhook before use.';
			$profile['enabled']     = false;
			$profile['is_default']  = false;
			$profile['priority']    = $priority;
			$profile['webhook_url'] = '';
			$profiles[]             = $profile;
			$priority              += 10;
		}

		return $profiles;
	}

	public static function bootstrap_profiles_from_settings( array $settings ): array {
		return self::example_profiles( $settings );
	}

	public static function sanitize_profiles( mixed $profiles, array $settings = array() ): array {
		$profiles = is_array( $profiles ) ? array_values( $profiles ) : array();
		$output   = array();
		$seen     = array();

		foreach ( array_slice( $profiles, 0, 20 ) as $index => $profile ) {
			if ( ! is_array( $profile ) ) {
				continue;
			}

			$base = self::default_profile( $settings );
			$id   = sanitize_key( (string) ( $profile['id'] ?? '' ) );
			if ( '' === $id ) {
				$id = 'profile-' . ( $index + 1 );
			}
			while ( isset( $seen[ $id ] ) ) {
				$id .= '-' . ( $index + 1 );
			}
			$seen[ $id ] = true;

			$output[] = array(
				'id'               => $id,
				'name'             => Settings::limit_text( sanitize_text_field( (string) ( $profile['name'] ?? $base['name'] ) ), 80 ),
				'description'      => Settings::limit_text( sanitize_textarea_field( (string) ( $profile['description'] ?? '' ) ), 240 ),
				'enabled'          => ! empty( $profile['enabled'] ),
				'is_default'       => ! empty( $profile['is_default'] ),
				'priority'         => Settings::sanitize_int( $profile['priority'] ?? $base['priority'], 0, 999, ( $index + 1 ) * 10 ),
				'webhook_url'      => Helpers::sanitize_url( (string) ( $profile['webhook_url'] ?? $base['webhook_url'] ) ),
				'bot_name'         => Settings::limit_text( sanitize_text_field( (string) ( $profile['bot_name'] ?? $base['bot_name'] ) ), 80 ),
				'bot_subtitle'     => Settings::limit_text( sanitize_text_field( (string) ( $profile['bot_subtitle'] ?? $base['bot_subtitle'] ) ), 120 ),
				'theme_preset'     => Settings::sanitize_choice( (string) ( $profile['theme_preset'] ?? $base['theme_preset'] ), array( 'premium-glass', 'minimal-dark', 'clean-light', 'brand-purple', 'midnight-blue', 'soft-neutral', 'elegant-gold', 'modern-green', 'corporate-blue', 'high-contrast' ), 'premium-glass' ),
				'launcher_label'   => Settings::limit_text( sanitize_text_field( (string) ( $profile['launcher_label'] ?? $base['launcher_label'] ) ), 80 ),
				'initial_messages' => Settings::sanitize_initial_messages( $profile['initial_messages'] ?? $base['initial_messages'], 20 ),
				'quick_actions'    => Settings::sanitize_quick_actions( $profile['quick_actions'] ?? $base['quick_actions'], 30 ),
				'visibility'       => Settings::sanitize_visibility( $profile['visibility'] ?? $base['visibility'] ),
				'metadata_fields'  => Settings::sanitize_metadata( $profile['metadata_fields'] ?? $base['metadata_fields'] ),
				'campaign_rules'   => self::sanitize_campaign_rules( $profile['campaign_rules'] ?? array() ),
				'page_rules'       => self::sanitize_page_rules( $profile['page_rules'] ?? array() ),
			);
		}

		if ( empty( $output ) ) {
			$output[] = self::default_profile( $settings );
		}

		usort( $output, static fn( array $a, array $b ): int => (int) $a['priority'] <=> (int) $b['priority'] );
		self::ensure_single_default( $output );

		return $output;
	}

	public static function ensure_single_default( array &$profiles ): void {
		$default_found = false;
		foreach ( $profiles as &$profile ) {
			if ( ! empty( $profile['is_default'] ) && ! $default_found ) {
				$default_found = true;
				continue;
			}
			$profile['is_default'] = false;
		}
		unset( $profile );

		if ( ! $default_found && isset( $profiles[0] ) ) {
			$profiles[0]['is_default'] = true;
		}
	}

	public static function resolve( array $settings, array $context = array() ): array {
		$profiles = self::sanitize_profiles( $settings['profiles'] ?? array(), $settings );
		$selected = null;

		foreach ( $profiles as $profile ) {
			if ( empty( $profile['enabled'] ) ) {
				continue;
			}
			if ( self::profile_matches( $profile, $context ) ) {
				$selected = $profile;
				break;
			}
		}

		if ( null === $selected ) {
			foreach ( $profiles as $profile ) {
				if ( ! empty( $profile['is_default'] ) ) {
					$selected = $profile;
					break;
				}
			}
		}

		$selected = apply_filters( 'cten_resolved_profile', $selected ?: $profiles[0], $profiles, $settings, $context );

		return self::merge_profile_into_settings( $settings, $selected, $context );
	}

	public static function merge_profile_into_settings( array $settings, array $profile, array $context = array() ): array {
		$settings['resolved_profile_id']   = (string) $profile['id'];
		$settings['resolved_profile_name'] = (string) $profile['name'];
		foreach ( array( 'webhook_url', 'bot_name', 'bot_subtitle', 'theme_preset', 'launcher_label', 'initial_messages', 'quick_actions', 'visibility', 'metadata_fields' ) as $key ) {
			if ( array_key_exists( $key, $profile ) && '' !== $profile[ $key ] && array() !== $profile[ $key ] ) {
				$settings[ $key ] = $profile[ $key ];
			}
		}
		return $settings;
	}

	public static function profile_matches( array $profile, array $context = array() ): bool {
		$visibility = $profile['visibility'] ?? Settings::defaults()['visibility'];
		$scope      = $visibility['scope'] ?? 'entire_site';
		$page_id    = absint( $context['post_id'] ?? 0 );
		$campaign   = sanitize_text_field( (string) ( $context['campaign'] ?? '' ) );
		$device     = sanitize_key( (string) ( $context['device'] ?? '' ) );
		$logged_in  = array_key_exists( 'logged_in', $context ) ? (bool) $context['logged_in'] : is_user_logged_in();

		if ( 'homepage_only' === $scope && ! ( is_front_page() || is_home() ) ) {
			return false;
		}
		if ( 'selected_pages' === $scope && ! self::matches_page_id_list( $visibility['selected_pages'] ?? array(), $page_id ) ) {
			return false;
		}
		if ( ! empty( $visibility['excluded_pages'] ) && self::matches_page_id_list( $visibility['excluded_pages'], $page_id ) ) {
			return false;
		}
		if ( 'selected_post_types' === $scope && ! self::matches_post_type( $visibility['selected_types'] ?? array(), $context ) ) {
			return false;
		}
		if ( 'logged_in' === ( $visibility['auth'] ?? 'all' ) && ! $logged_in ) {
			return false;
		}
		if ( 'logged_out' === ( $visibility['auth'] ?? 'all' ) && $logged_in ) {
			return false;
		}

		if ( ! self::matches_device_visibility( $visibility['devices'] ?? array(), $device ) ) {
			return false;
		}

		if ( self::has_enabled_page_rules( $profile ) && ! self::matches_page_rules( $profile['page_rules'] ?? array(), $context ) ) {
			return false;
		}

		if ( self::has_enabled_campaign_rules( $profile ) && ! self::matches_campaign_rules( $profile['campaign_rules'] ?? array(), $context ) ) {
			return false;
		}

		return true;
	}

	public static function resolution_report( array $settings, array $context = array() ): array {
		$profiles = self::sanitize_profiles( $settings['profiles'] ?? array(), $settings );
		$report   = array();

		foreach ( $profiles as $profile ) {
			$report[] = array(
				'id'       => (string) $profile['id'],
				'name'     => (string) $profile['name'],
				'enabled'  => (bool) $profile['enabled'],
				'default'  => (bool) $profile['is_default'],
				'priority' => (int) $profile['priority'],
				'matches'  => ! empty( $profile['enabled'] ) && self::profile_matches( $profile, $context ),
			);
		}

		return $report;
	}

	private static function has_enabled_page_rules( array $profile ): bool {
		foreach ( $profile['page_rules'] ?? array() as $rule ) {
			if ( ! empty( $rule['enabled'] ) ) {
				return true;
			}
		}
		return false;
	}

	private static function has_enabled_campaign_rules( array $profile ): bool {
		foreach ( $profile['campaign_rules'] ?? array() as $rule ) {
			if ( ! empty( $rule['enabled'] ) ) {
				return true;
			}
		}
		return false;
	}

	private static function matches_page_rules( array $rules, array $context = array() ): bool {
		$current_id = absint( $context['post_id'] ?? get_queried_object_id() );
		foreach ( $rules as $rule ) {
			if ( empty( $rule['enabled'] ) ) {
				continue;
			}
			$page_ids = array_map( 'absint', $rule['page_ids'] ?? array() );
			if ( $current_id && in_array( $current_id, $page_ids, true ) ) {
				return true;
			}
		}
		return false;
	}

	private static function matches_campaign_rules( array $rules, array $context = array() ): bool {
		foreach ( $rules as $rule ) {
			if ( empty( $rule['enabled'] ) ) {
				continue;
			}
			$field = (string) ( $rule['field'] ?? '' );
			$value = self::campaign_value( $field, $context );
			if ( self::operator_matches( $value, (string) ( $rule['operator'] ?? 'contains' ), (string) ( $rule['value'] ?? '' ) ) ) {
				return true;
			}
		}
		return false;
	}

	private static function matches_page_id_list( array $ids, int $page_id ): bool {
		return $page_id > 0 && in_array( $page_id, array_map( 'absint', $ids ), true );
	}

	private static function matches_post_type( array $types, array $context = array() ): bool {
		$post_type = sanitize_key( (string) ( $context['post_type'] ?? get_post_type( get_queried_object_id() ) ) );
		return '' !== $post_type && in_array( $post_type, array_map( 'sanitize_key', $types ), true );
	}

	private static function matches_device_visibility( array $devices, string $device = '' ): bool {
		$devices = array_map( 'sanitize_key', $devices );
		if ( empty( $devices ) ) {
			return true;
		}

		if ( '' === $device ) {
			$device = wp_is_mobile() ? 'mobile' : 'desktop';
		}

		return in_array( $device, $devices, true );
	}

	private static function campaign_value( string $field, array $context = array() ): string {
		if ( 'referrer' === $field ) {
			return sanitize_text_field( (string) ( $context['referrer'] ?? wp_unslash( $_SERVER['HTTP_REFERER'] ?? '' ) ) );
		}
		if ( 'campaign' === $field ) {
			$field = 'utm_campaign';
		}
		$allowed = array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'industry' );
		if ( ! in_array( $field, $allowed, true ) ) {
			return '';
		}
		if ( isset( $context[ $field ] ) ) {
			return sanitize_text_field( (string) $context[ $field ] );
		}
		return isset( $_GET[ $field ] ) ? sanitize_text_field( wp_unslash( $_GET[ $field ] ) ) : '';
	}

	private static function operator_matches( string $actual, string $operator, string $expected ): bool {
		$actual_lc   = strtolower( $actual );
		$expected_lc = strtolower( $expected );
		if ( 'is_present' === $operator ) {
			return '' !== $actual_lc;
		}
		if ( '' === $expected_lc ) {
			return false;
		}
		if ( 'equals' === $operator ) {
			return $actual_lc === $expected_lc;
		}
		if ( 'starts_with' === $operator ) {
			return str_starts_with( $actual_lc, $expected_lc );
		}
		return str_contains( $actual_lc, $expected_lc );
	}

	public static function sanitize_campaign_rules( mixed $rules ): array {
		$rules = is_array( $rules ) ? array_values( $rules ) : array();
		$output = array();
		foreach ( array_slice( $rules, 0, 30 ) as $index => $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}
			$output[] = array(
				'name'     => Settings::limit_text( sanitize_text_field( (string) ( $rule['name'] ?? 'Campaign Rule' ) ), 80 ),
				'enabled'  => ! empty( $rule['enabled'] ),
				'field'    => Settings::sanitize_choice( sanitize_key( (string) ( $rule['field'] ?? 'utm_campaign' ) ), array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'referrer', 'industry', 'campaign' ), 'utm_campaign' ),
				'operator' => Settings::sanitize_choice( sanitize_key( (string) ( $rule['operator'] ?? 'contains' ) ), array( 'equals', 'contains', 'starts_with', 'is_present' ), 'contains' ),
				'value'    => Settings::limit_text( sanitize_text_field( (string) ( $rule['value'] ?? '' ) ), 120 ),
				'priority' => Settings::sanitize_int( $rule['priority'] ?? ( $index + 1 ) * 10, 0, 999, ( $index + 1 ) * 10 ),
			);
		}
		return $output;
	}

	public static function sanitize_page_rules( mixed $rules ): array {
		$rules = is_array( $rules ) ? array_values( $rules ) : array();
		$output = array();
		foreach ( array_slice( $rules, 0, 30 ) as $index => $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}
			$output[] = array(
				'name'     => Settings::limit_text( sanitize_text_field( (string) ( $rule['name'] ?? 'Page Rule' ) ), 80 ),
				'enabled'  => ! empty( $rule['enabled'] ),
				'page_ids' => array_map( 'absint', is_array( $rule['page_ids'] ?? null ) ? $rule['page_ids'] : array() ),
				'priority' => Settings::sanitize_int( $rule['priority'] ?? ( $index + 1 ) * 10, 0, 999, ( $index + 1 ) * 10 ),
			);
		}
		return $output;
	}
}
