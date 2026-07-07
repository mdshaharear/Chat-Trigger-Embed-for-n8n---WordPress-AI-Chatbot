<?php
/**
 * OpenAI provider adapter.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class OpenAI_Provider implements Provider_Interface {
	public function get_id(): string {
		return 'openai';
	}

	public function get_name(): string {
		return 'OpenAI';
	}

	public function validate_configuration( array $connection ): array {
		$secret = (string) ( $connection['secret_value'] ?? '' );
		if ( '' === $secret ) {
			return array( 'status' => 'error', 'category' => 'invalid_credentials', 'message' => 'OpenAI API key is missing.' );
		}

		return array( 'status' => 'ok', 'message' => 'OpenAI configuration looks valid.' );
	}

	public function validate_connection( array $connection ): array {
		return $this->test_connection( $connection );
	}

	public function test_connection( array $connection ): array {
		$validation = $this->validate_configuration( $connection );
		if ( 'ok' !== (string) ( $validation['status'] ?? 'error' ) ) {
			return $validation;
		}

		$request = $this->build_request(
			new AI_Request(
				request_id: 'test',
				session_id: 'test',
				user_message: 'Reply with OK.',
				system_instructions: 'You are a test assistant.',
				maximum_output_tokens: 16
			),
			$connection
		);
		$response = wp_remote_post( 'https://api.openai.com/v1/responses', $request );
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
		$response = wp_remote_post( 'https://api.openai.com/v1/responses', $this->build_request( $request, $connection ) );
		return $this->normalize_response( $response, $request );
	}

	public function normalize_response( mixed $response, AI_Request $request ): AI_Response {
		if ( is_wp_error( $response ) ) {
			return new AI_Response( $request->request_id, $request->session_id, '', array(), null, false, array(), array(), null, $this->get_id(), (string) ( $request->provider_settings['connection']['default_model'] ?? '' ), null, array( 'category' => 'network_error', 'visitor_message' => $response->get_error_message(), 'retryable' => true ) );
		}
		$raw = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		$status = (int) wp_remote_retrieve_response_code( $response );
		if ( $status < 200 || $status >= 300 || ! is_array( $raw ) ) {
			return new AI_Response( $request->request_id, $request->session_id, '', array(), null, false, array(), array(), null, $this->get_id(), (string) ( $request->provider_settings['connection']['default_model'] ?? '' ), null, array( 'category' => $this->map_error_category( $status, is_array( $raw ) ? $raw : array() ), 'visitor_message' => (string) ( $raw['error']['message'] ?? 'OpenAI request failed.' ), 'retryable' => $status >= 500 ) );
		}

		$message = (string) ( $raw['output_text'] ?? $this->extract_output_text( $raw ) );
		$options = $this->extract_options( $raw );
		$usage = is_array( $raw['usage'] ?? null ) ? $raw['usage'] : array();
		return new AI_Response(
			$request->request_id,
			$request->session_id,
			$message,
			$options,
			null,
			false,
			array(),
			array(),
			array(
				'input_tokens' => isset( $usage['input_tokens'] ) ? (int) $usage['input_tokens'] : null,
				'output_tokens' => isset( $usage['output_tokens'] ) ? (int) $usage['output_tokens'] : null,
				'total_tokens' => isset( $usage['total_tokens'] ) ? (int) $usage['total_tokens'] : null,
			),
			$this->get_id(),
			(string) ( $raw['model'] ?? ( $request->provider_settings['connection']['default_model'] ?? '' ) ),
			(string) ( $raw['status'] ?? 'success' ),
			null
		);
	}

	public function normalize_error( mixed $error ): array {
		if ( is_wp_error( $error ) ) {
			return array( 'category' => 'network_error', 'visitor_message' => $error->get_error_message(), 'retryable' => true );
		}
		return array( 'category' => 'unknown_error', 'visitor_message' => 'OpenAI request failed.', 'retryable' => true );
	}

	private function build_request( AI_Request $request, array $connection ): array {
		$body = array(
			'model' => (string) ( $connection['default_model'] ?? '' ),
			'instructions' => $request->system_instructions,
			'input' => array(
				array(
					'role' => 'user',
					'content' => array(
						array( 'type' => 'input_text', 'text' => $request->user_message ),
					),
				),
			),
			'max_output_tokens' => $request->maximum_output_tokens > 0 ? $request->maximum_output_tokens : 256,
			'store' => false,
		);
		if ( ! empty( $request->output_schema_preference ) ) {
			$body['text'] = array( 'format' => array( 'type' => 'json_schema', 'name' => 'cten_response', 'schema' => array(
				'type' => 'object',
				'properties' => array(
					'message' => array( 'type' => 'string' ),
					'options' => array( 'type' => 'array' ),
					'lead_status' => array( 'type' => array( 'string', 'null' ) ),
					'handoff' => array( 'type' => 'boolean' ),
				),
				'required' => array( 'message', 'options', 'lead_status', 'handoff' ),
				'additionalProperties' => false,
			) ) );
		}
		if ( ! empty( $request->safety_identifier ) ) {
			$body['safety_identifier'] = $request->safety_identifier;
		}
		$request_args = array(
			'timeout' => max( 5, min( 120, (int) ( $request->timeout ?: (int) ( $connection['timeout'] ?? 30 ) ) ) ),
			'headers' => array(
				'Authorization' => 'Bearer ' . (string) ( $connection['secret_value'] ?? '' ),
				'Content-Type' => 'application/json',
			),
			'body' => wp_json_encode( $body ),
		);
		if ( ! empty( $connection['organization_id'] ) ) {
			$request_args['headers']['OpenAI-Organization'] = (string) $connection['organization_id'];
		}
		if ( ! empty( $connection['project_id'] ) ) {
			$request_args['headers']['OpenAI-Project'] = (string) $connection['project_id'];
		}
		return $request_args;
	}

	private function normalize_transport_result( mixed $response ): array {
		if ( is_wp_error( $response ) ) {
			return array( 'status' => 'error', 'category' => 'network_error', 'message' => $response->get_error_message() );
		}
		$status = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( $status < 200 || $status >= 300 ) {
			return array( 'status' => 'error', 'category' => $this->map_error_category( $status, $body ), 'message' => (string) ( $body['error']['message'] ?? 'OpenAI request failed.' ) );
		}
		return array( 'status' => 'ok', 'message' => 'Connection accepted.' );
	}

	private function extract_output_text( array $raw ): string {
		$output = '';
		foreach ( (array) ( $raw['output'] ?? array() ) as $item ) {
			foreach ( (array) ( $item['content'] ?? array() ) as $content ) {
				if ( 'output_text' === ( $content['type'] ?? '' ) ) {
					$output .= (string) ( $content['text'] ?? '' );
				}
			}
		}
		return trim( $output );
	}

	private function extract_options( array $raw ): array {
		$options = array();
		$schema = $raw['structured'] ?? $raw['output'] ?? array();
		if ( is_array( $schema ) && isset( $schema['options'] ) && is_array( $schema['options'] ) ) {
			foreach ( $schema['options'] as $option ) {
				if ( ! is_array( $option ) ) {
					continue;
				}
				$options[] = array(
					'label' => substr( sanitize_text_field( (string) ( $option['label'] ?? '' ) ), 0, 80 ),
					'value' => substr( sanitize_text_field( (string) ( $option['value'] ?? '' ) ), 0, 80 ),
				);
			}
		}
		return $options;
	}

	private function map_error_category( int $status, array $body ): string {
		if ( 401 === $status || 403 === $status ) {
			return 'invalid_credentials';
		}
		if ( 429 === $status ) {
			return 'rate_limited';
		}
		if ( 400 === $status ) {
			return 'invalid_request';
		}
		if ( $status >= 500 ) {
			return 'provider_unavailable';
		}
		return 'unknown_error';
	}
}
