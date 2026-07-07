<?php
/**
 * Provider connection repository.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Provider_Connection_Repository {
	private Secret_Store_Interface $secrets;

	public function __construct( ?Secret_Store_Interface $secrets = null ) {
		$this->secrets = $secrets ?: new WordPress_Secret_Store();
	}

	public function all(): array {
		$rows = V2_Storage::all( V2_Storage::PROVIDER_CONNECTIONS );
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
		$rows = V2_Storage::all( V2_Storage::PROVIDER_CONNECTIONS );
		$now  = gmdate( 'c' );
		$id   = sanitize_key( (string) ( $input['id'] ?? '' ) );
		if ( '' === $id ) {
			$id = 'conn-' . wp_generate_password( 10, false, false );
		}
		$existing = $this->get( $id ) ?: array();
		$record   = array(
			'id'                      => $id,
			'name'                    => $this->limit( sanitize_text_field( (string) ( $input['name'] ?? '' ) ), 80 ),
			'provider'                => in_array( (string) ( $input['provider'] ?? '' ), array( 'openai', 'gemini', 'n8n', 'mock' ), true ) ? (string) $input['provider'] : 'mock',
			'enabled'                 => ! empty( $input['enabled'] ),
			'secret_source'           => in_array( (string) ( $input['secret_source'] ?? 'none' ), array( 'constant', 'environment', 'encrypted_option', 'none' ), true ) ? (string) $input['secret_source'] : 'none',
			'secret_value'            => '',
			'secret_status'           => array( 'state' => 'none', 'label' => __( 'No secret required', 'chat-trigger-embed-for-n8n' ) ),
			'project_id'              => $this->limit( sanitize_text_field( (string) ( $input['project_id'] ?? '' ) ), 80 ),
			'organization_id'         => $this->limit( sanitize_text_field( (string) ( $input['organization_id'] ?? '' ) ), 80 ),
			'default_model'           => $this->limit( sanitize_text_field( (string) ( $input['default_model'] ?? '' ) ), 120 ),
			'timeout'                 => max( 5, min( 120, absint( $input['timeout'] ?? 30 ) ) ),
			'created_at'              => (string) ( $existing['created_at'] ?? $now ),
			'updated_at'              => $now,
			'last_test_status'        => (string) ( $existing['last_test_status'] ?? '' ),
			'last_test_timestamp'     => (string) ( $existing['last_test_timestamp'] ?? '' ),
			'last_safe_error_category'=> (string) ( $existing['last_safe_error_category'] ?? '' ),
			'extra'                   => array(),
		);

		$secret = trim( (string) ( $input['secret_value'] ?? '' ) );
		if ( '' !== $secret ) {
			if ( 'constant' === $record['secret_source'] ) {
				$record['secret_value'] = $this->limit( sanitize_text_field( $secret ), 2048 );
			} elseif ( 'environment' === $record['secret_source'] ) {
				$record['secret_value'] = $this->limit( sanitize_text_field( $secret ), 2048 );
			} elseif ( 'encrypted_option' === $record['secret_source'] ) {
				$stored = $this->secrets->store( $secret );
				$record['secret_value'] = (string) ( $stored['value'] ?? '' );
				$record['secret_status'] = $this->secrets->get_status( $record );
				$record['secret_algorithm'] = (string) ( $stored['algorithm'] ?? '' );
			}
		} elseif ( ! empty( $existing['secret_value'] ) ) {
			$record['secret_value'] = (string) $existing['secret_value'];
			$record['secret_algorithm'] = (string) ( $existing['secret_algorithm'] ?? '' );
		}

		if ( 'mock' === $record['provider'] ) {
			$record['secret_source'] = 'none';
			$record['secret_value'] = '';
			$record['secret_algorithm'] = '';
			$record['secret_status'] = array( 'state' => 'none', 'label' => __( 'No secret required', 'chat-trigger-embed-for-n8n' ) );
		}

		$rows = array_values( array_filter( $rows, static fn( array $row ): bool => (string) ( $row['id'] ?? '' ) !== $id ) );
		$rows[] = $record;
		V2_Storage::save( V2_Storage::PROVIDER_CONNECTIONS, $rows );

		return $this->sanitize_record( $record );
	}

	public function delete( string $id ): void {
		$rows = array_values( array_filter( V2_Storage::all( V2_Storage::PROVIDER_CONNECTIONS ), static fn( array $row ): bool => (string) ( $row['id'] ?? '' ) !== $id ) );
		V2_Storage::save( V2_Storage::PROVIDER_CONNECTIONS, $rows );
	}

	public function mask_secret( array $record ): string {
		$source = (string) ( $record['secret_source'] ?? 'none' );
		$value  = (string) ( $record['secret_value'] ?? '' );
		if ( '' === $value ) {
			return '';
		}
		if ( 'encrypted_option' === $source ) {
			return __( 'Stored securely', 'chat-trigger-embed-for-n8n' );
		}
		return $this->secrets->mask( $value );
	}

	public function test_status( array $record, string $status, string $safe_error = '' ): array {
		$record['last_test_status'] = $status;
		$record['last_test_timestamp'] = gmdate( 'c' );
		$record['last_safe_error_category'] = $safe_error;
		return $this->save( $record );
	}

	public function public_summary(): array {
		return array_values(
			array_map(
				static fn( array $row ): array => array(
					'id' => (string) $row['id'],
					'name' => (string) $row['name'],
					'provider' => (string) $row['provider'],
					'enabled' => (bool) $row['enabled'],
					'default_model' => (string) $row['default_model'],
				),
				array_filter( $this->all(), static fn( array $row ): bool => ! empty( $row['enabled'] ) )
			)
		);
	}

	public function runtime_connection( array $record ): array {
		$record = $this->sanitize_record( $record );
		$record['secret_value'] = $this->resolve_secret_value( $record );
		return $record;
	}

	private function sanitize_record( array $record ): array {
		$record['id'] = sanitize_key( (string) ( $record['id'] ?? '' ) );
		$record['name'] = $this->limit( sanitize_text_field( (string) ( $record['name'] ?? '' ) ), 80 );
		$record['provider'] = in_array( (string) ( $record['provider'] ?? '' ), array( 'openai', 'gemini', 'n8n', 'mock' ), true ) ? (string) $record['provider'] : 'mock';
		$record['enabled'] = ! empty( $record['enabled'] );
		$record['secret_source'] = in_array( (string) ( $record['secret_source'] ?? 'none' ), array( 'constant', 'environment', 'encrypted_option', 'none' ), true ) ? (string) $record['secret_source'] : 'none';
		$record['secret_value'] = $this->limit( sanitize_text_field( (string) ( $record['secret_value'] ?? '' ) ), 2048 );
		$record['project_id'] = $this->limit( sanitize_text_field( (string) ( $record['project_id'] ?? '' ) ), 80 );
		$record['organization_id'] = $this->limit( sanitize_text_field( (string) ( $record['organization_id'] ?? '' ) ), 80 );
		$record['default_model'] = $this->limit( sanitize_text_field( (string) ( $record['default_model'] ?? '' ) ), 120 );
		$record['timeout'] = max( 5, min( 120, absint( $record['timeout'] ?? 30 ) ) );
		$record['created_at'] = (string) ( $record['created_at'] ?? gmdate( 'c' ) );
		$record['updated_at'] = (string) ( $record['updated_at'] ?? gmdate( 'c' ) );
		$record['last_test_status'] = $this->limit( sanitize_text_field( (string) ( $record['last_test_status'] ?? '' ) ), 80 );
		$record['last_test_timestamp'] = $this->limit( sanitize_text_field( (string) ( $record['last_test_timestamp'] ?? '' ) ), 80 );
		$record['last_safe_error_category'] = $this->limit( sanitize_text_field( (string) ( $record['last_safe_error_category'] ?? '' ) ), 80 );
		$record['secret_status'] = is_array( $record['secret_status'] ?? null ) ? $record['secret_status'] : array();
		if ( ! isset( $record['secret_status']['state'] ) ) {
			$record['secret_status'] = $this->secrets->get_status( $record );
		}
		$record['extra'] = is_array( $record['extra'] ?? null ) ? $record['extra'] : array();
		return $record;
	}

	private function resolve_secret_value( array $record ): string {
		$source = (string) ( $record['secret_source'] ?? 'none' );
		$value  = (string) ( $record['secret_value'] ?? '' );

		if ( 'constant' === $source ) {
			return $value;
		}

		if ( 'environment' === $source ) {
			$resolved = getenv( $value );
			if ( false !== $resolved && '' !== $resolved ) {
				return (string) $resolved;
			}
			if ( isset( $_ENV[ $value ] ) ) {
				return (string) $_ENV[ $value ];
			}
			if ( isset( $_SERVER[ $value ] ) ) {
				return (string) $_SERVER[ $value ];
			}
			return '';
		}

		if ( 'encrypted_option' === $source ) {
			return $this->secrets->resolve( $record );
		}

		return '';
	}

	private function limit( string $value, int $max ): string {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $max );
		}
		return substr( $value, 0, $max );
	}
}
