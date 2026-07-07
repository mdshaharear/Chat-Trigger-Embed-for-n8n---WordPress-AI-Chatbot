<?php
/**
 * Chatbot repository.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Chatbot_Repository {
	public function all(): array {
		$rows = V2_Storage::all( V2_Storage::CHATBOTS );
		return array_values( array_map( array( $this, 'sanitize_record' ), $rows ) );
	}

	public function get( string $id ): ?array {
		foreach ( $this->all() as $row ) {
			if ( (string) $row['id'] === $id ) {
				return $row;
			}
		}
		return null;
	}

	public function save( array $input ): array {
		$rows = V2_Storage::all( V2_Storage::CHATBOTS );
		$now  = gmdate( 'c' );
		$id   = sanitize_key( (string) ( $input['id'] ?? '' ) );
		if ( '' === $id ) {
			$id = 'chatbot-' . wp_generate_password( 10, false, false );
		}
		$existing = $this->get( $id ) ?: array();
		$record = array(
			'id' => $id,
			'name' => $this->limit( sanitize_text_field( (string) ( $input['name'] ?? '' ) ), 80 ),
			'internal_name' => $this->limit( sanitize_text_field( (string) ( $input['internal_name'] ?? '' ) ), 80 ),
			'enabled' => ! empty( $input['enabled'] ),
			'engine' => in_array( (string) ( $input['engine'] ?? 'mock' ), array( 'openai', 'gemini', 'n8n', 'mock' ), true ) ? (string) $input['engine'] : 'mock',
			'ui_mode' => 'native',
			'provider_connection_id' => sanitize_key( (string) ( $input['provider_connection_id'] ?? '' ) ),
			'model_id' => $this->limit( sanitize_text_field( (string) ( $input['model_id'] ?? '' ) ), 120 ),
			'system_instructions' => $this->limit( sanitize_textarea_field( (string) ( $input['system_instructions'] ?? '' ) ), 5000 ),
			'welcome_message' => $this->limit( sanitize_textarea_field( (string) ( $input['welcome_message'] ?? '' ) ), 500 ),
			'input_placeholder' => $this->limit( sanitize_text_field( (string) ( $input['input_placeholder'] ?? '' ) ), 120 ),
			'error_message' => $this->limit( sanitize_text_field( (string) ( $input['error_message'] ?? '' ) ), 240 ),
			'static_fallback_message' => $this->limit( sanitize_text_field( (string) ( $input['static_fallback_message'] ?? '' ) ), 240 ),
			'quick_actions' => $this->sanitize_quick_actions( $input['quick_actions'] ?? array() ),
			'theme_preset' => sanitize_key( (string) ( $input['theme_preset'] ?? 'premium-glass' ) ),
			'launcher_label' => $this->limit( sanitize_text_field( (string) ( $input['launcher_label'] ?? '' ) ), 80 ),
			'page_visibility_mode' => in_array( (string) ( $input['page_visibility_mode'] ?? 'entire_site' ), array( 'entire_site', 'homepage', 'selected_pages', 'excluded_pages' ), true ) ? (string) $input['page_visibility_mode'] : 'entire_site',
			'selected_page_ids' => array_values( array_filter( array_map( 'absint', (array) ( $input['selected_page_ids'] ?? array() ) ) ) ),
			'maximum_input_characters' => max( 50, min( 10000, absint( $input['maximum_input_characters'] ?? 1000 ) ) ),
			'maximum_output_tokens' => max( 16, min( 4096, absint( $input['maximum_output_tokens'] ?? 256 ) ) ),
			'messages_per_session' => max( 1, min( 500, absint( $input['messages_per_session'] ?? 50 ) ) ),
			'requests_per_minute' => max( 1, min( 500, absint( $input['requests_per_minute'] ?? 30 ) ) ),
			'daily_request_limit' => max( 0, absint( $input['daily_request_limit'] ?? 0 ) ),
			'created_at' => (string) ( $existing['created_at'] ?? $now ),
			'updated_at' => $now,
			'public_status' => array(),
		);

		if ( ! empty( $existing['public_status'] ) ) {
			$record['public_status'] = $existing['public_status'];
		}

		$rows = array_values( array_filter( $rows, static fn( array $row ): bool => (string) ( $row['id'] ?? '' ) !== $id ) );
		$rows[] = $record;
		V2_Storage::save( V2_Storage::CHATBOTS, $rows );
		return $this->sanitize_record( $record );
	}

	public function delete( string $id ): void {
		$rows = array_values( array_filter( V2_Storage::all( V2_Storage::CHATBOTS ), static fn( array $row ): bool => (string) ( $row['id'] ?? '' ) !== $id ) );
		V2_Storage::save( V2_Storage::CHATBOTS, $rows );
	}

	public function duplicate( string $id ): ?array {
		$source = $this->get( $id );
		if ( ! $source ) {
			return null;
		}
		unset( $source['id'], $source['created_at'], $source['updated_at'] );
		$source['name'] = ( $source['name'] ?: 'Chatbot' ) . ' Copy';
		$source['internal_name'] = ( $source['internal_name'] ?: 'chatbot' ) . '-copy';
		$source['enabled'] = false;
		return $this->save( $source );
	}

	public function enable( string $id, bool $enabled ): ?array {
		$record = $this->get( $id );
		if ( ! $record ) {
			return null;
		}
		$record['enabled'] = $enabled;
		return $this->save( $record );
	}

	public function public_summary(): array {
		return array_values(
			array_map(
				static fn( array $row ): array => array(
					'id' => (string) $row['id'],
					'name' => (string) $row['name'],
					'enabled' => (bool) $row['enabled'],
					'engine' => (string) $row['engine'],
					'ui_mode' => (string) $row['ui_mode'],
					'page_visibility_mode' => (string) $row['page_visibility_mode'],
					'selected_page_ids' => array_values( array_map( 'absint', (array) ( $row['selected_page_ids'] ?? array() ) ) ),
					'requests_per_minute' => (int) $row['requests_per_minute'],
					'daily_request_limit' => (int) $row['daily_request_limit'],
				),
				array_filter( $this->all(), static fn( array $row ): bool => ! empty( $row['enabled'] ) )
			)
		);
	}

	private function sanitize_record( array $record ): array {
		$record['id'] = sanitize_key( (string) ( $record['id'] ?? '' ) );
		$record['name'] = $this->limit( sanitize_text_field( (string) ( $record['name'] ?? '' ) ), 80 );
		$record['internal_name'] = $this->limit( sanitize_text_field( (string) ( $record['internal_name'] ?? '' ) ), 80 );
		$record['enabled'] = ! empty( $record['enabled'] );
		$record['engine'] = in_array( (string) ( $record['engine'] ?? 'mock' ), array( 'openai', 'gemini', 'n8n', 'mock' ), true ) ? (string) $record['engine'] : 'mock';
		$record['ui_mode'] = 'native';
		$record['provider_connection_id'] = sanitize_key( (string) ( $record['provider_connection_id'] ?? '' ) );
		$record['model_id'] = $this->limit( sanitize_text_field( (string) ( $record['model_id'] ?? '' ) ), 120 );
		$record['system_instructions'] = $this->limit( sanitize_textarea_field( (string) ( $record['system_instructions'] ?? '' ) ), 5000 );
		$record['welcome_message'] = $this->limit( sanitize_textarea_field( (string) ( $record['welcome_message'] ?? '' ) ), 500 );
		$record['input_placeholder'] = $this->limit( sanitize_text_field( (string) ( $record['input_placeholder'] ?? '' ) ), 120 );
		$record['error_message'] = $this->limit( sanitize_text_field( (string) ( $record['error_message'] ?? '' ) ), 240 );
		$record['static_fallback_message'] = $this->limit( sanitize_text_field( (string) ( $record['static_fallback_message'] ?? '' ) ), 240 );
		$record['quick_actions'] = $this->sanitize_quick_actions( $record['quick_actions'] ?? array() );
		$record['theme_preset'] = sanitize_key( (string) ( $record['theme_preset'] ?? 'premium-glass' ) );
		$record['launcher_label'] = $this->limit( sanitize_text_field( (string) ( $record['launcher_label'] ?? '' ) ), 80 );
		$record['page_visibility_mode'] = in_array( (string) ( $record['page_visibility_mode'] ?? 'entire_site' ), array( 'entire_site', 'homepage', 'selected_pages', 'excluded_pages' ), true ) ? (string) $record['page_visibility_mode'] : 'entire_site';
		$record['selected_page_ids'] = array_values( array_filter( array_map( 'absint', (array) ( $record['selected_page_ids'] ?? array() ) ) ) );
		$record['maximum_input_characters'] = max( 50, min( 10000, absint( $record['maximum_input_characters'] ?? 1000 ) ) );
		$record['maximum_output_tokens'] = max( 16, min( 4096, absint( $record['maximum_output_tokens'] ?? 256 ) ) );
		$record['messages_per_session'] = max( 1, min( 500, absint( $record['messages_per_session'] ?? 50 ) ) );
		$record['requests_per_minute'] = max( 1, min( 500, absint( $record['requests_per_minute'] ?? 30 ) ) );
		$record['daily_request_limit'] = max( 0, absint( $record['daily_request_limit'] ?? 0 ) );
		$record['created_at'] = (string) ( $record['created_at'] ?? gmdate( 'c' ) );
		$record['updated_at'] = (string) ( $record['updated_at'] ?? gmdate( 'c' ) );
		$record['public_status'] = is_array( $record['public_status'] ?? null ) ? $record['public_status'] : array();
		return $record;
	}

	private function sanitize_quick_actions( mixed $actions ): array {
		$actions = is_array( $actions ) ? array_values( $actions ) : array();
		$output = array();
		foreach ( array_slice( $actions, 0, 12 ) as $index => $action ) {
			if ( ! is_array( $action ) ) {
				continue;
			}
			$output[] = array(
				'id' => sanitize_key( (string) ( $action['id'] ?? 'qa-' . ( $index + 1 ) ) ),
				'enabled' => ! empty( $action['enabled'] ),
				'label' => $this->limit( sanitize_text_field( (string) ( $action['label'] ?? '' ) ), 80 ),
				'message' => $this->limit( sanitize_textarea_field( (string) ( $action['message'] ?? '' ) ), 240 ),
				'sort' => max( 0, min( 999, absint( $action['sort'] ?? ( $index + 1 ) * 10 ) ) ),
			);
		}
		return $output;
	}

	private function limit( string $value, int $max ): string {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $max );
		}
		return substr( $value, 0, $max );
	}
}
