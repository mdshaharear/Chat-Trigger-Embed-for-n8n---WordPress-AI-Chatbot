<?php
/**
 * Gemini provider adapter.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Gemini_Provider implements Provider_Interface {
	public function get_id(): string {
		return 'gemini';
	}

	public function get_name(): string {
		return 'Gemini';
	}

	public function validate_configuration( array $connection ): array {
		$secret = (string) ( $connection['secret_value'] ?? '' );
		if ( '' === $secret ) {
			return array( 'status' => 'error', 'category' => 'invalid_credentials', 'message' => 'Gemini API key is missing.' );
		}

		return array( 'status' => 'ok', 'message' => 'Gemini configuration looks valid.' );
	}

	public function validate_connection( array $connection ): array {
		return $this->test_connection( $connection );
	}

	public function test_connection( array $connection ): array {
		$validation = $this->validate_configuration( $connection );
		if ( 'ok' !== (string) ( $validation['status'] ?? 'error' ) ) {
			return $validation;
		}
		$secret = (string) ( $connection['secret_value'] ?? '' );
		$endpoint = $this->endpoint( (string) ( $connection['default_model'] ?? 'gemini-2.0-flash' ), $secret );
		$response = wp_remote_post( $endpoint, array(
			'timeout' => max( 5, min( 120, absint( $connection['timeout'] ?? 30 ) ) ),
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body' => wp_json_encode( array( 'contents' => array( array( 'role' => 'user', 'parts' => array( array( 'text' => 'Reply with OK.' ) ) ) ) ) ),
		) );
		return $this->normalize_transport_result( $response );
	}

	public function list_models( array $connection ): array {
		return array();
	}

	public function get_capabilities( string $model ): array {
		return array( 'streaming' => false, 'structured_output' => true, 'function_calling' => true );
	}

	public function send_message( AI_Request $request ): AI_Response {
		$connection = (array) ( $request->provider_settings['connection'] ?? array() );
		$endpoint = $this->endpoint( (string) ( $connection['default_model'] ?? $request->provider_settings['model'] ?? 'gemini-2.0-flash' ), (string) ( $connection['secret_value'] ?? '' ) );
		$body = array(
			'contents' => array(
				array(
					'role' => 'user',
					'parts' => array( array( 'text' => $request->user_message ) ),
				),
			),
		);
		if ( '' !== $request->system_instructions ) {
			$body['systemInstruction'] = array( 'parts' => array( array( 'text' => $request->system_instructions ) ) );
		}
		if ( $request->maximum_output_tokens > 0 ) {
			$body['generationConfig'] = array( 'maxOutputTokens' => $request->maximum_output_tokens );
		}
		if ( ! empty( $request->output_schema_preference ) ) {
			$body['generationConfig']['responseMimeType'] = 'application/json';
		}
		$response = wp_remote_post( $endpoint, array(
			'timeout' => max( 5, min( 120, (int) ( $request->timeout ?: (int) ( $connection['timeout'] ?? 30 ) ) ) ),
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body' => wp_json_encode( $body ),
		) );
		return $this->normalize_response( $response, $request );
	}

	public function normalize_response( mixed $response, AI_Request $request ): AI_Response {
		if ( is_wp_error( $response ) ) {
			return new AI_Response( $request->request_id, $request->session_id, '', array(), null, false, array(), array(), null, $this->get_id(), (string) ( $request->provider_settings['connection']['default_model'] ?? '' ), null, array( 'category' => 'network_error', 'visitor_message' => $response->get_error_message(), 'retryable' => true ) );
		}
		$raw = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		$status = (int) wp_remote_retrieve_response_code( $response );
		if ( $status < 200 || $status >= 300 || ! is_array( $raw ) ) {
			return new AI_Response( $request->request_id, $request->session_id, '', array(), null, false, array(), array(), null, $this->get_id(), (string) ( $request->provider_settings['connection']['default_model'] ?? '' ), null, array( 'category' => $this->map_error_category( $status, is_array( $raw ) ? $raw : array() ), 'visitor_message' => (string) ( $raw['error']['message'] ?? 'Gemini request failed.' ), 'retryable' => $status >= 500 ) );
		}
		$message = (string) ( $raw['candidates'][0]['content']['parts'][0]['text'] ?? '' );
		$usage = is_array( $raw['usageMetadata'] ?? null ) ? $raw['usageMetadata'] : array();
		$options = array();
		if ( preg_match_all( '/\[\[OPTION:([^\]]{1,80})\]\]/', $message, $matches ) ) {
			foreach ( $matches[1] as $option ) {
				$options[] = array( 'label' => substr( sanitize_text_field( $option ), 0, 80 ), 'value' => substr( sanitize_text_field( strtolower( str_replace( ' ', '_', $option ) ) ), 0, 80 ) );
			}
		}
		return new AI_Response(
			$request->request_id,
			$request->session_id,
			trim( preg_replace( '/\[\[(OPTION|LEAD_STATUS):[^\]]+\]\]/', '', $message ) ),
			$options,
			null,
			false,
			array(),
			array(),
			array(
				'input_tokens' => isset( $usage['promptTokenCount'] ) ? (int) $usage['promptTokenCount'] : null,
				'output_tokens' => isset( $usage['candidatesTokenCount'] ) ? (int) $usage['candidatesTokenCount'] : null,
				'total_tokens' => isset( $usage['totalTokenCount'] ) ? (int) $usage['totalTokenCount'] : null,
			),
			$this->get_id(),
			(string) ( $raw['modelVersion'] ?? $request->provider_settings['connection']['default_model'] ?? '' ),
			(string) ( $raw['candidates'][0]['finishReason'] ?? 'stop' ),
			null
		);
	}

	public function normalize_error( mixed $error ): array {
		if ( is_wp_error( $error ) ) {
			return array( 'category' => 'network_error', 'visitor_message' => $error->get_error_message(), 'retryable' => true );
		}
		return array( 'category' => 'unknown_error', 'visitor_message' => 'Gemini request failed.', 'retryable' => true );
	}

	private function endpoint( string $model, string $secret ): string {
		return sprintf( 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s', rawurlencode( $model ), rawurlencode( $secret ) );
	}

	private function normalize_transport_result( mixed $response ): array {
		if ( is_wp_error( $response ) ) {
			return array( 'status' => 'error', 'category' => 'network_error', 'message' => $response->get_error_message() );
		}
		$status = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( $status < 200 || $status >= 300 ) {
			return array( 'status' => 'error', 'category' => $this->map_error_category( $status, is_array( $body ) ? $body : array() ), 'message' => (string) ( $body['error']['message'] ?? 'Gemini request failed.' ) );
		}
		return array( 'status' => 'ok', 'message' => 'Connection accepted.' );
	}

	private function map_error_category( int $status, array $body ): string {
		if ( 401 === $status || 403 === $status ) {
			return 'invalid_credentials';
		}
		if ( 429 === $status ) {
			return 'rate_limited';
		}
		if ( 400 === $status ) {
			if ( str_contains( strtolower( (string) ( $body['error']['message'] ?? '' ) ), 'safety' ) ) {
				return 'safety_blocked';
			}
			return 'invalid_request';
		}
		if ( $status >= 500 ) {
			return 'provider_unavailable';
		}
		return 'unknown_error';
	}
}
