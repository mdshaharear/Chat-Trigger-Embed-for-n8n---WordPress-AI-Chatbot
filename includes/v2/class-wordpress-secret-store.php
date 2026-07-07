<?php
/**
 * WordPress option-backed encrypted secret store.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WordPress_Secret_Store implements Secret_Store_Interface {
	private const ALGO_SODIUM = 'sodium_crypto_secretbox';
	private const ALGO_OPENSSL = 'openssl_aes_256_gcm';
	private const OPTION_PREFIX = 'cten_secret_';

	public function store( string $secret ): array {
		$secret = trim( $secret );
		if ( '' === $secret ) {
			return array( 'status' => 'empty', 'value' => '', 'algorithm' => '' );
		}

		if ( $this->can_encrypt() && function_exists( 'sodium_crypto_secretbox' ) ) {
			$key   = $this->derive_key();
			$nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$seal  = sodium_crypto_secretbox( $secret, $nonce, $key );
			return array(
				'status'    => 'stored',
				'algorithm' => self::ALGO_SODIUM,
				'value'     => base64_encode( $nonce . $seal ),
			);
		}

		if ( $this->can_encrypt() && function_exists( 'openssl_encrypt' ) ) {
			$key = substr( hash( 'sha256', $this->derive_key(), true ), 0, 32 );
			$iv  = random_bytes( 12 );
			$tag = '';
			$cipher = openssl_encrypt( $secret, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16 );
			if ( false === $cipher ) {
				return array( 'status' => 'error', 'value' => '', 'algorithm' => '' );
			}
			return array(
				'status'    => 'stored',
				'algorithm' => self::ALGO_OPENSSL,
				'value'     => base64_encode( $iv . $tag . $cipher ),
			);
		}

		return array( 'status' => 'plaintext', 'value' => $this->mask( $secret ), 'algorithm' => 'plaintext' );
	}

	public function resolve( array $record ): string {
		$payload = (string) ( $record['value'] ?? '' );
		$algo    = (string) ( $record['algorithm'] ?? 'plaintext' );
		if ( '' === $payload || 'plaintext' === $algo ) {
			return $payload;
		}

		$decoded = base64_decode( $payload, true );
		if ( false === $decoded ) {
			return '';
		}

		try {
			if ( self::ALGO_SODIUM === $algo && function_exists( 'sodium_crypto_secretbox_open' ) ) {
				$nonce_len = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
				if ( strlen( $decoded ) <= $nonce_len ) {
					return '';
				}
				$nonce = substr( $decoded, 0, $nonce_len );
				$cipher = substr( $decoded, $nonce_len );
				$plain = sodium_crypto_secretbox_open( $cipher, $nonce, $this->derive_key() );
				return false === $plain ? '' : $plain;
			}

			if ( self::ALGO_OPENSSL === $algo && function_exists( 'openssl_decrypt' ) ) {
				if ( strlen( $decoded ) <= 28 ) {
					return '';
				}
				$iv = substr( $decoded, 0, 12 );
				$tag = substr( $decoded, 12, 16 );
				$cipher = substr( $decoded, 28 );
				$key = substr( hash( 'sha256', $this->derive_key(), true ), 0, 32 );
				$plain = openssl_decrypt( $cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag );
				return false === $plain ? '' : $plain;
			}
		} catch ( \Throwable $error ) {
			return '';
		}

		return '';
	}

	public function replace( array $record, string $secret ): array {
		$this->delete( $record );
		return $this->store( $secret );
	}

	public function delete( array $record ): void {
		if ( ! empty( $record['option_name'] ) ) {
			delete_option( (string) $record['option_name'] );
		}
	}

	public function can_encrypt(): bool {
		return function_exists( 'hash_hkdf' ) && ( function_exists( 'sodium_crypto_secretbox' ) || function_exists( 'openssl_encrypt' ) );
	}

	public function mask( string $secret ): string {
		$secret = trim( $secret );
		if ( '' === $secret ) {
			return '';
		}

		$length = strlen( $secret );
		if ( $length <= 4 ) {
			return str_repeat( '*', $length );
		}

		return substr( $secret, 0, 2 ) . str_repeat( '*', max( 4, $length - 4 ) ) . substr( $secret, -2 );
	}

	public function get_status( array $record ): array {
		if ( empty( $record['secret_source'] ) || 'none' === $record['secret_source'] ) {
			return array( 'state' => 'none', 'label' => __( 'No secret required', 'chat-trigger-embed-for-n8n' ) );
		}

		if ( ! empty( $record['secret_value'] ) ) {
			return array( 'state' => 'present', 'label' => __( 'Secret saved', 'chat-trigger-embed-for-n8n' ) );
		}

		return array( 'state' => 'missing', 'label' => __( 'Secret missing', 'chat-trigger-embed-for-n8n' ) );
	}

	private function derive_key(): string {
		$secret = wp_salt( 'auth' ) . '|' . wp_salt( 'secure_auth' ) . '|' . wp_salt( 'logged_in' );
		return hash_hkdf( 'sha256', $secret, 32, 'cten-v2-secret-store', CTEN_VERSION );
	}
}
