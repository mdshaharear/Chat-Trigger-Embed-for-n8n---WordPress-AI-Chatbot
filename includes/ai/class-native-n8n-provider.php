<?php
/**
 * Native n8n provider adapter.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Native_N8n_Provider implements Provider_Interface {
	public function get_id(): string {
		return 'n8n';
	}

	public function get_name(): string {
		return 'n8n';
	}

	public function validate_configuration( array $connection ): array {
		$url = \ChatTriggerEmbedN8n\Helpers::sanitize_url( (string) ( $connection['secret_value'] ?? '' ) );
		if ( '' === $url ) {
			return array( 'status' => 'error', 'category' => 'invalid_request', 'message' => 'Invalid Chat Trigger URL.' );
		}
		if ( ! self::is_safe_webhook_url( $url ) ) {
			return array( 'status' => 'error', 'category' => 'invalid_request', 'message' => 'The Chat Trigger URL points to a private or unsafe destination.' );
		}

		return array( 'status' => 'ok', 'message' => 'n8n configuration looks valid.' );
	}

	public function validate_connection( array $connection ): array {
		return $this->test_connection( $connection );
	}

	public function test_connection( array $connection ): array {
		$validation = $this->validate_configuration( $connection );
		if ( 'ok' !== (string) ( $validation['status'] ?? 'error' ) ) {
			return $validation;
		}

		$url = \ChatTriggerEmbedN8n\Helpers::sanitize_url( (string) ( $connection['secret_value'] ?? '' ) );
		$response = wp_remote_post(
			$url,
			array(
				'timeout' => max( 5, min( 120, absint( $connection['timeout'] ?? 30 ) ) ),
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body' => wp_json_encode(
					array(
						'action' => 'testConnection',
						'sessionId' => 'cten-test',
						'chatInput' => 'Reply with OK.',
					)
				),
				'redirection' => 0,
			)
		);
		return $this->normalize_transport_result( $response );
	}

	public function list_models( array $connection ): array {
		return array();
	}

	public function get_capabilities( string $model ): array {
		return array( 'streaming' => false, 'structured_output' => false, 'function_calling' => false );
	}

	public function send_message( AI_Request $request ): AI_Response {
		$connection = (array) ( $request->provider_settings['connection'] ?? array() );
		$url = \ChatTriggerEmbedN8n\Helpers::sanitize_url( (string) ( $connection['secret_value'] ?? '' ) );
		if ( '' === $url || ! self::is_safe_webhook_url( $url ) ) {
			return new AI_Response( $request->request_id, $request->session_id, '', array(), null, false, array(), array(), null, $this->get_id(), (string) ( $request->provider_settings['model'] ?? '' ), null, array( 'category' => 'invalid_request', 'visitor_message' => 'Invalid n8n webhook URL.', 'retryable' => false ) );
		}

		$payload = array(
			'action' => 'sendMessage',
			'sessionId' => $request->session_id,
			'chatInput' => $request->user_message,
			'metadata' => array(
				'requestId' => $request->request_id,
				'chatbotId' => $request->chatbot_id,
				'pageMeta' => $request->page_metadata,
			),
		);
		$response = wp_remote_post( $url, array(
			'timeout' => max( 5, min( 120, (int) ( $request->timeout ?: (int) ( $connection['timeout'] ?? 30 ) ) ) ),
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body' => wp_json_encode( $payload ),
			'redirection' => 0,
		) );
		return $this->normalize_response( $response, $request );
	}

	public function normalize_response( mixed $response, AI_Request $request ): AI_Response {
		if ( is_wp_error( $response ) ) {
			return new AI_Response( $request->request_id, $request->session_id, '', array(), null, false, array(), array(), null, $this->get_id(), '', null, array( 'category' => 'network_error', 'visitor_message' => $response->get_error_message(), 'retryable' => true ) );
		}
		$status = (int) wp_remote_retrieve_response_code( $response );
		$body = (string) wp_remote_retrieve_body( $response );
		if ( $status < 200 || $status >= 300 ) {
			return new AI_Response( $request->request_id, $request->session_id, '', array(), null, false, array(), array(), null, $this->get_id(), '', null, array( 'category' => $this->map_error_category( $status ), 'visitor_message' => 'n8n returned HTTP ' . $status, 'retryable' => $status >= 500 || 429 === $status ) );
		}

		$parsed = json_decode( $body, true );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			return new AI_Response( $request->request_id, $request->session_id, trim( $body ), array(), null, false, array(), array(), null, $this->get_id(), '', 'success', null );
		}

		$message = '';
		$options = array();
		if ( is_string( $parsed ) ) {
			$message = $parsed;
		} elseif ( is_array( $parsed ) ) {
			$message = (string) ( $parsed['output'] ?? $parsed['text'] ?? $parsed['response'] ?? $parsed['message'] ?? '' );
			$option_source = $parsed['options'] ?? $parsed['choices'] ?? array();
			if ( is_array( $option_source ) ) {
				foreach ( $option_source as $option ) {
					if ( is_array( $option ) ) {
						$options[] = array(
							'label' => substr( sanitize_text_field( (string) ( $option['label'] ?? $option['text'] ?? '' ) ), 0, 80 ),
							'value' => substr( sanitize_text_field( (string) ( $option['value'] ?? strtolower( str_replace( ' ', '_', (string) ( $option['label'] ?? $option['text'] ?? '' ) ) ) ) ), 0, 80 ),
						);
					} elseif ( is_string( $option ) ) {
						$options[] = array(
							'label' => substr( sanitize_text_field( $option ), 0, 80 ),
							'value' => substr( sanitize_text_field( strtolower( str_replace( ' ', '_', $option ) ) ), 0, 80 ),
						);
					}
				}
			}
		}

		return new AI_Response(
			$request->request_id,
			$request->session_id,
			trim( $message ),
			$options,
			null,
			false,
			array(),
			array(),
			array(),
			$this->get_id(),
			(string) ( $request->provider_settings['model'] ?? '' ),
			'success',
			null
		);
	}

	public function normalize_error( mixed $error ): array {
		if ( is_wp_error( $error ) ) {
			return array( 'category' => 'network_error', 'visitor_message' => $error->get_error_message(), 'retryable' => true );
		}
		return array( 'category' => 'unknown_error', 'visitor_message' => 'n8n request failed.', 'retryable' => true );
	}

	private function map_error_category( int $status ): string {
		if ( 401 === $status || 403 === $status ) {
			return 'invalid_credentials';
		}
		if ( 429 === $status ) {
			return 'rate_limited';
		}
		if ( $status >= 500 ) {
			return 'provider_unavailable';
		}
		return 'invalid_request';
	}

	private function is_safe_webhook_url( string $url ): bool {
		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) ) {
			return false;
		}

		$scheme = strtolower( (string) ( $parts['scheme'] ?? '' ) );
		$host = strtolower( (string) ( $parts['host'] ?? '' ) );
		if ( ! in_array( $scheme, array( 'http', 'https' ), true ) || '' === $host ) {
			return false;
		}
		if ( ! empty( $parts['user'] ) || ! empty( $parts['pass'] ) ) {
			return false;
		}

		if ( self::is_local_or_metadata_host( $host ) || self::is_blocked_ip_literal( $host ) ) {
			return false;
		}

		$resolved = @gethostbynamel( $host );
		if ( is_array( $resolved ) ) {
			foreach ( $resolved as $ip ) {
				if ( self::is_blocked_ip_literal( $ip ) ) {
					return false;
				}
			}
		}

		return true;
	}

	private static function is_local_or_metadata_host( string $host ): bool {
		if ( in_array( $host, array( 'localhost', 'localhost.localdomain' ), true ) ) {
			return true;
		}
		return str_starts_with( $host, 'metadata.' ) || str_starts_with( $host, '169.254.' );
	}

	private static function is_blocked_ip_literal( string $value ): bool {
		if ( false === filter_var( $value, FILTER_VALIDATE_IP ) ) {
			return false;
		}

		if ( false === filter_var( $value, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
			return true;
		}

		return in_array( $value, array( '::1', '0.0.0.0', '127.0.0.1' ), true );
	}
}
