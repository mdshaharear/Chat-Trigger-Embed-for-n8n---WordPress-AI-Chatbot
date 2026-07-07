<?php
/**
 * Alpha usage counters.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Usage_Repository {
	public function get_today( string $chatbot_id ): array {
		$usage = V2_Storage::all( V2_Storage::USAGE );
		$key = gmdate( 'Y-m-d' ) . ':' . sanitize_key( $chatbot_id );
		return is_array( $usage[ $key ] ?? null ) ? $usage[ $key ] : array(
			'requests_today' => 0,
			'successful_requests_today' => 0,
			'failed_requests_today' => 0,
			'estimated_input_tokens' => null,
			'estimated_output_tokens' => null,
			'last_request_timestamp' => '',
		);
	}

	public function bump( string $chatbot_id, bool $success, ?int $input_tokens = null, ?int $output_tokens = null ): array {
		$usage = V2_Storage::all( V2_Storage::USAGE );
		$key = gmdate( 'Y-m-d' ) . ':' . sanitize_key( $chatbot_id );
		$row = $this->get_today( $chatbot_id );
		$row['requests_today'] = (int) ( $row['requests_today'] ?? 0 ) + 1;
		$row[ $success ? 'successful_requests_today' : 'failed_requests_today' ] = (int) ( $row[ $success ? 'successful_requests_today' : 'failed_requests_today' ] ?? 0 ) + 1;
		$row['estimated_input_tokens'] = null === $input_tokens ? ( $row['estimated_input_tokens'] ?? null ) : (int) $input_tokens;
		$row['estimated_output_tokens'] = null === $output_tokens ? ( $row['estimated_output_tokens'] ?? null ) : (int) $output_tokens;
		$row['last_request_timestamp'] = gmdate( 'c' );
		$usage[ $key ] = $row;
		V2_Storage::save( V2_Storage::USAGE, $usage );
		return $row;
	}
}
