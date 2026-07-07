<?php
/**
 * Optional streaming provider contract.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Streaming_Provider_Interface {
	public function supports_streaming( string $model ): bool;
	public function stream_message( AI_Request $request, callable $emitter ): array;
}
