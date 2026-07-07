<?php
/**
 * Mock provider for internal tests.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Mock_Provider implements Provider_Interface {
	public function get_id(): string {
		return 'mock';
	}

	public function get_name(): string {
		return 'Mock Provider';
	}

	public function validate_configuration( array $connection ): array {
		return array( 'status' => 'ok', 'message' => 'Mock provider configuration accepted.' );
	}

	public function validate_connection( array $connection ): array {
		return $this->test_connection( $connection );
	}

	public function test_connection( array $connection ): array {
		return array( 'status' => 'ok', 'message' => 'Mock provider connection accepted.' );
	}

	public function list_models( array $connection ): array {
		return array( 'mock-chat-mini' );
	}

	public function get_capabilities( string $model ): array {
		return array( 'streaming' => true, 'structured_output' => true, 'function_calling' => true );
	}

	public function send_message( AI_Request $request ): AI_Response {
		return new AI_Response(
			$request->request_id,
			$request->session_id,
			'Mock provider response.',
			array(),
			null,
			false,
			array(),
			array(),
			array(),
			$this->get_id(),
			'mock-chat-mini',
			'stop',
			null
		);
	}

	public function normalize_response( mixed $response, AI_Request $request ): AI_Response {
		if ( $response instanceof AI_Response ) {
			return $response;
		}
		return new AI_Response( $request->request_id, $request->session_id, 'Mock provider response.', array(), null, false, array(), array(), array(), $this->get_id(), 'mock-chat-mini', 'stop', null );
	}

	public function normalize_error( mixed $error ): array {
		return is_array( $error ) ? $error : array( 'code' => 'unknown_error', 'message' => 'Unknown mock error.' );
	}
}
