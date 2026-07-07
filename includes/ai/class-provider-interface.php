<?php
/**
 * Provider adapter contract.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Provider_Interface {
	public function get_id(): string;
	public function get_name(): string;
	public function validate_configuration( array $connection ): array;
	public function validate_connection( array $connection ): array;
	public function test_connection( array $connection ): array;
	public function list_models( array $connection ): array;
	public function get_capabilities( string $model ): array;
	public function send_message( AI_Request $request ): AI_Response;
	public function normalize_response( mixed $response, AI_Request $request ): AI_Response;
	public function normalize_error( mixed $error ): array;
}
