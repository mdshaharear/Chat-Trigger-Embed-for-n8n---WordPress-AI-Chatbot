<?php
/**
 * Provider-neutral request object.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AI_Request {
	public function __construct(
		public readonly string $chatbot_id = '',
		public readonly string $session_id = '',
		public readonly string $user_message = '',
		public readonly array $conversation_messages = array(),
		public readonly string $system_instructions = '',
		public readonly array $knowledge_context = array(),
		public readonly array $lead_context = array(),
		public readonly array $page_metadata = array(),
		public readonly array $allowed_tools = array(),
		public readonly string $output_schema_preference = '',
		public readonly int $maximum_output_tokens = 0,
		public readonly int $timeout = 0,
		public readonly array $provider_settings = array(),
		public readonly string $safety_identifier = '',
		public readonly string $request_id = ''
	) {}
}
