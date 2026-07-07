<?php
/**
 * Provider-neutral response object.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AI_Response {
	public function __construct(
		public readonly string $request_id = '',
		public readonly string $session_id = '',
		public readonly string $message = '',
		public readonly array $options = array(),
		public readonly ?string $lead_status = null,
		public readonly bool $handoff = false,
		public readonly array $actions = array(),
		public readonly array $citations = array(),
		public readonly array $usage = array(),
		public readonly string $provider = '',
		public readonly string $model = '',
		public readonly ?string $finish_reason = null,
		public readonly ?array $error = null
	) {}
}
