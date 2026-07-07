<?php
/**
 * Legacy n8n compatibility provider.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Legacy_N8n_Provider implements Provider_Interface {
	public function get_id(): string {
		return 'legacy_n8n';
	}

	public function get_name(): string {
		return 'Legacy n8n';
	}

	public function validate_configuration( array $connection ): array {
		return array( 'status' => 'ok', 'message' => 'Legacy mode is preserved for backward compatibility.' );
	}

	public function validate_connection( array $connection ): array {
		return $this->test_connection( $connection );
	}

	public function test_connection( array $connection ): array {
		return array( 'status' => 'ok', 'message' => 'Legacy mode is preserved for backward compatibility.' );
	}

	public function list_models( array $connection ): array {
		return array();
	}

	public function get_capabilities( string $model ): array {
		return array( 'streaming' => false, 'structured_output' => false, 'function_calling' => false );
	}

	public function send_message( AI_Request $request ): AI_Response {
		return new AI_Response( $request->request_id, $request->session_id, '', array(), null, false, array(), array(), array(), $this->get_id(), '', null, array( 'code' => 'legacy_mode_only', 'message' => 'Legacy mode is not yet bridged to the new native AI gateway.' ) );
	}

	public function normalize_response( mixed $response, AI_Request $request ): AI_Response {
		if ( $response instanceof AI_Response ) {
			return $response;
		}
		return new AI_Response( $request->request_id, $request->session_id, '', array(), null, false, array(), array(), array(), $this->get_id(), '', null, array( 'code' => 'legacy_mode_only', 'message' => 'Legacy mode is not yet bridged to the new native AI gateway.' ) );
	}

	public function normalize_error( mixed $error ): array {
		return is_array( $error ) ? $error : array( 'code' => 'unknown_error', 'message' => 'Unknown legacy n8n error.' );
	}
}
