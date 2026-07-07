<?php
/**
 * Provider registry for native AI mode.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Provider_Registry {
	public static function providers(): array {
		return array(
			'openai'     => new OpenAI_Provider(),
			'gemini'     => new Gemini_Provider(),
			'n8n'        => new Native_N8n_Provider(),
			'legacy_n8n' => new Legacy_N8n_Provider(),
			'mock'       => new Mock_Provider(),
		);
	}

	public static function get( string $provider_id ): ?Provider_Interface {
		$providers = self::providers();
		return $providers[ $provider_id ] ?? null;
	}
}
