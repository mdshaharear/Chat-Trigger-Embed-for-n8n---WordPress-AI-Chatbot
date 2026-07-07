<?php
/**
 * Secret store contract.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Secret_Store_Interface {
	public function store( string $secret ): array;
	public function resolve( array $record ): string;
	public function replace( array $record, string $secret ): array;
	public function delete( array $record ): void;
	public function can_encrypt(): bool;
	public function mask( string $secret ): string;
	public function get_status( array $record ): array;
}
