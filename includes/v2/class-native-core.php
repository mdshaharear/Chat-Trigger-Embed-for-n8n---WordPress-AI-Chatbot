<?php
/**
 * Native AI core bridge.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\V2;

use ChatTriggerEmbedN8n\AI\AI_Request;
use ChatTriggerEmbedN8n\AI\AI_Response;
use ChatTriggerEmbedN8n\AI\Provider_Registry;
use ChatTriggerEmbedN8n\Helpers;
use ChatTriggerEmbedN8n\Safe_Mode;
use ChatTriggerEmbedN8n\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Native_Core {
	private const REST_NAMESPACE = 'cten/v2';
	private const REST_ROUTE = '/chat';
	private const CONNECTION_NOTICE = 'cten_v2_connection_notice';
	private const CHATBOT_NOTICE = 'cten_v2_chatbot_notice';
	private const RATE_LIMIT_PREFIX = 'cten_v2_rate_';
	private const SESSION_LIMIT_PREFIX = 'cten_v2_session_';

	public static function hooks(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
		add_filter( 'cten_chat_should_display', array( __CLASS__, 'filter_should_display' ), 20, 2 );
		add_filter( 'cten_public_chat_config_render', array( __CLASS__, 'filter_public_config' ), 20, 2 );
		add_filter( 'cten_appearance_css_vars', array( __CLASS__, 'filter_css_vars' ), 20, 2 );
		add_action( 'admin_post_cten_v2_save_connection', array( __CLASS__, 'handle_save_connection' ) );
		add_action( 'admin_post_cten_v2_delete_connection', array( __CLASS__, 'handle_delete_connection' ) );
		add_action( 'admin_post_cten_v2_test_connection', array( __CLASS__, 'handle_test_connection' ) );
		add_action( 'admin_post_cten_v2_save_chatbot', array( __CLASS__, 'handle_save_chatbot' ) );
		add_action( 'admin_post_cten_v2_delete_chatbot', array( __CLASS__, 'handle_delete_chatbot' ) );
	}

	public static function register_rest_routes(): void {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE,
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'rest_chat' ),
			)
		);
	}

	public static function filter_should_display( bool $allowed, array $settings ): bool {
		if ( $allowed || Safe_Mode::should_block_public_chat() ) {
			return $allowed;
		}

		return null !== self::resolve_active_chatbot();
	}

	public static function filter_css_vars( array $vars, array $settings ): array {
		$chatbot = self::resolve_active_chatbot();
		if ( ! $chatbot ) {
			return $vars;
		}

		$palette = self::theme_palette( (string) ( $chatbot['theme_preset'] ?? 'premium-glass' ) );
		foreach ( $palette as $key => $value ) {
			$vars[ $key ] = $value;
		}

		$vars['--cten-theme-preset'] = (string) ( $chatbot['theme_preset'] ?? 'premium-glass' );
		$vars['--chat--border-radius'] = '22px';
		$vars['--chat--window--width'] = '520px';
		$vars['--chat--window--height'] = '720px';

		return $vars;
	}

	public static function filter_public_config( array $config, array $settings ): array {
		$chatbot = self::resolve_active_chatbot();
		if ( ! $chatbot ) {
			return $config;
		}

		$quick_actions = array_values(
			array_filter(
				array_map(
					static function ( array $action ): array {
						return array(
							'id'      => (string) ( $action['id'] ?? '' ),
							'enabled' => ! empty( $action['enabled'] ),
							'label'   => (string) ( $action['label'] ?? '' ),
							'message' => (string) ( $action['message'] ?? '' ),
							'sort'    => (int) ( $action['sort'] ?? 0 ),
						);
					},
					(array) ( $chatbot['quick_actions'] ?? array() )
				),
				static fn( array $action ): bool => ! empty( $action['enabled'] ) && '' !== trim( (string) $action['label'] )
			)
		);

		usort( $quick_actions, static fn( array $a, array $b ): int => (int) $a['sort'] <=> (int) $b['sort'] );

		$chatbot_config = array(
			'id'                       => (string) $chatbot['id'],
			'name'                     => (string) $chatbot['name'],
			'internalName'             => (string) $chatbot['internal_name'],
			'enabled'                  => (bool) $chatbot['enabled'],
			'engine'                   => (string) $chatbot['engine'],
			'uiMode'                   => 'native',
			'providerConnectionId'     => (string) $chatbot['provider_connection_id'],
			'modelId'                  => (string) $chatbot['model_id'],
			'systemInstructions'       => (string) $chatbot['system_instructions'],
			'welcomeMessage'           => (string) $chatbot['welcome_message'],
			'inputPlaceholder'         => (string) $chatbot['input_placeholder'],
			'errorMessage'             => (string) $chatbot['error_message'],
			'staticFallbackMessage'    => (string) $chatbot['static_fallback_message'],
			'quickActions'             => $quick_actions,
			'themePreset'              => (string) $chatbot['theme_preset'],
			'launcherLabel'            => (string) $chatbot['launcher_label'],
			'pageVisibilityMode'       => (string) $chatbot['page_visibility_mode'],
			'selectedPageIds'          => array_values( array_map( 'absint', (array) ( $chatbot['selected_page_ids'] ?? array() ) ) ),
			'maximumInputCharacters'   => (int) $chatbot['maximum_input_characters'],
			'maximumOutputTokens'      => (int) $chatbot['maximum_output_tokens'],
			'messagesPerSession'       => (int) $chatbot['messages_per_session'],
			'requestsPerMinute'        => (int) $chatbot['requests_per_minute'],
			'dailyRequestLimit'        => (int) $chatbot['daily_request_limit'],
		);

		$config['mode'] = 'native';
		$config['webhookUrl'] = '';
		$config['restUrl'] = rest_url( self::REST_NAMESPACE . self::REST_ROUTE );
		$config['chatbotId'] = (string) $chatbot['id'];
		$config['chatbotName'] = (string) $chatbot['name'];
		$config['chatbot'] = $chatbot_config;
		$config['launcherAccessibilityLabel'] = (string) ( $chatbot['launcher_label'] ?: $chatbot['name'] );
		$config['launcherLabel'] = (string) ( $chatbot['launcher_label'] ?: $chatbot['name'] );
		$config['welcomeMessage'] = (string) $chatbot['welcome_message'];
		$config['inputPlaceholder'] = (string) $chatbot['input_placeholder'];
		$config['errorMessage'] = (string) $chatbot['error_message'];
		$config['staticFallbackMessage'] = (string) $chatbot['static_fallback_message'];
		$config['quickActions'] = $quick_actions;
		$config['themePreset'] = (string) $chatbot['theme_preset'];
		$config['maximumInputCharacters'] = (int) $chatbot['maximum_input_characters'];
		$config['maximumOutputTokens'] = (int) $chatbot['maximum_output_tokens'];
		$config['messagesPerSession'] = (int) $chatbot['messages_per_session'];
		$config['requestsPerMinute'] = (int) $chatbot['requests_per_minute'];
		$config['dailyRequestLimit'] = (int) $chatbot['daily_request_limit'];
		$config['runtimeTest'] = $config['runtimeTest'] ?? array();
		$config['i18n']['en']['title'] = (string) $chatbot['name'];
		$config['i18n']['en']['subtitle'] = sprintf(
			/* translators: %s: provider name */
			__( 'Native AI chat powered by %s.', 'chat-trigger-embed-for-n8n' ),
			ucfirst( (string) $chatbot['engine'] )
		);
		$config['i18n']['en']['footer'] = __( 'Your messages are handled by the selected provider.', 'chat-trigger-embed-for-n8n' );

		return $config;
	}

	public static function handle_save_connection(): void {
		self::require_manage_options();
		check_admin_referer( 'cten_v2_connection', 'cten_v2_connection_nonce' );

		$repo = new Provider_Connection_Repository();
		$record = $repo->save( self::sanitize_connection_input( wp_unslash( $_POST ) ) );
		set_transient( self::CONNECTION_NOTICE, sprintf( __( 'Saved connection "%s".', 'chat-trigger-embed-for-n8n' ), $record['name'] ?: $record['id'] ), 60 );

		wp_safe_redirect( self::admin_redirect( 'cten-ai-providers', $record['id'], 'edit_connection' ) );
		exit;
	}

	public static function handle_delete_connection(): void {
		self::require_manage_options();
		check_admin_referer( 'cten_v2_delete_connection', 'cten_v2_connection_nonce' );

		$id = sanitize_key( (string) ( $_POST['id'] ?? '' ) );
		if ( '' !== $id ) {
			( new Provider_Connection_Repository() )->delete( $id );
			set_transient( self::CONNECTION_NOTICE, __( 'Connection deleted.', 'chat-trigger-embed-for-n8n' ), 60 );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=cten-ai-providers' ) );
		exit;
	}

	public static function handle_test_connection(): void {
		self::require_manage_options();
		check_admin_referer( 'cten_v2_test_connection', 'cten_v2_connection_nonce' );

		$id = sanitize_key( (string) ( $_POST['id'] ?? '' ) );
		$repo = new Provider_Connection_Repository();
		$record = $repo->get( $id );
		if ( ! $record ) {
			set_transient( self::CONNECTION_NOTICE, __( 'Connection not found.', 'chat-trigger-embed-for-n8n' ), 60 );
			wp_safe_redirect( admin_url( 'admin.php?page=cten-ai-providers' ) );
			exit;
		}

		$provider = Provider_Registry::get( (string) $record['provider'] );
		if ( ! $provider ) {
			set_transient( self::CONNECTION_NOTICE, __( 'No provider adapter is available for this connection.', 'chat-trigger-embed-for-n8n' ), 60 );
			wp_safe_redirect( admin_url( 'admin.php?page=cten-ai-providers&edit_connection=' . rawurlencode( $id ) ) );
			exit;
		}

		$runtime_connection = $repo->runtime_connection( $record );
		$validation = $provider->validate_configuration( $runtime_connection );
		if ( 'ok' !== (string) ( $validation['status'] ?? 'error' ) ) {
			$repo->test_status( $record, (string) ( $validation['status'] ?? 'error' ), (string) ( $validation['category'] ?? '' ) );
			set_transient(
				self::CONNECTION_NOTICE,
				esc_html( (string) ( $validation['message'] ?? __( 'Connection validation failed.', 'chat-trigger-embed-for-n8n' ) ) ),
				60
			);
			wp_safe_redirect( admin_url( 'admin.php?page=cten-ai-providers&edit_connection=' . rawurlencode( $id ) ) );
			exit;
		}

		$result = $provider->test_connection( $runtime_connection );
		$repo->test_status( $record, (string) ( $result['status'] ?? 'error' ), (string) ( $result['category'] ?? '' ) );
		set_transient(
			self::CONNECTION_NOTICE,
			esc_html( (string) ( $result['message'] ?? __( 'Connection test completed.', 'chat-trigger-embed-for-n8n' ) ) ),
			60
		);

		wp_safe_redirect( admin_url( 'admin.php?page=cten-ai-providers&edit_connection=' . rawurlencode( $id ) ) );
		exit;
	}

	public static function handle_save_chatbot(): void {
		self::require_manage_options();
		check_admin_referer( 'cten_v2_chatbot', 'cten_v2_chatbot_nonce' );

		$repo = new Chatbot_Repository();
		$record = $repo->save( self::sanitize_chatbot_input( wp_unslash( $_POST ) ) );
		set_transient( self::CHATBOT_NOTICE, sprintf( __( 'Saved chatbot "%s".', 'chat-trigger-embed-for-n8n' ), $record['name'] ?: $record['id'] ), 60 );

		wp_safe_redirect( self::admin_redirect( 'cten-chatbots', $record['id'], 'edit_chatbot' ) );
		exit;
	}

	public static function handle_delete_chatbot(): void {
		self::require_manage_options();
		check_admin_referer( 'cten_v2_delete_chatbot', 'cten_v2_chatbot_nonce' );

		$id = sanitize_key( (string) ( $_POST['id'] ?? '' ) );
		if ( '' !== $id ) {
			( new Chatbot_Repository() )->delete( $id );
			set_transient( self::CHATBOT_NOTICE, __( 'Chatbot deleted.', 'chat-trigger-embed-for-n8n' ), 60 );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=cten-chatbots' ) );
		exit;
	}

	public static function rest_chat( \WP_REST_Request $request ): \WP_REST_Response {
		$chatbot_id = sanitize_key( (string) $request->get_param( 'chatbot_id' ) );
		$chatbot    = self::resolve_chatbot_by_id( $chatbot_id ) ?: self::resolve_active_chatbot();
		if ( ! $chatbot || empty( $chatbot['enabled'] ) ) {
			return self::rest_error( 'chatbot_not_found', __( 'The selected chatbot is not available.', 'chat-trigger-embed-for-n8n' ), 404, false );
		}

		if ( ! self::is_chatbot_visible( $chatbot ) ) {
			return self::rest_error( 'chatbot_not_visible', __( 'This chatbot is not available on the current page.', 'chat-trigger-embed-for-n8n' ), 404, false );
		}

		$message = self::sanitize_message( (string) $request->get_param( 'message' ), (int) $chatbot['maximum_input_characters'] );
		if ( '' === $message ) {
			return self::rest_error( 'empty_message', __( 'Please enter a message.', 'chat-trigger-embed-for-n8n' ), 400, false );
		}
		if ( '' !== trim( (string) $request->get_param( 'honeypot' ) ) ) {
			return self::rest_error( 'spam_detected', __( 'Request rejected.', 'chat-trigger-embed-for-n8n' ), 400, false );
		}

		$session_id = self::sanitize_session_id( (string) $request->get_param( 'session_id' ) );
		if ( '' === $session_id ) {
			$session_id = 'cten-' . wp_generate_password( 16, false, false );
		}
		$request_id = self::sanitize_session_id( (string) $request->get_param( 'request_id' ) );
		if ( '' === $request_id ) {
			$request_id = 'req-' . wp_generate_password( 12, false, false );
		}

		$rate = self::check_rate_limit( $chatbot, $session_id );
		if ( is_wp_error( $rate ) ) {
			return self::rest_error( 'rate_limited', $rate->get_error_message(), 429, true );
		}

		$usage_repo = new Usage_Repository();
		$usage      = $usage_repo->get_today( (string) $chatbot['id'] );
		if ( (int) $chatbot['daily_request_limit'] > 0 && (int) $usage['requests_today'] >= (int) $chatbot['daily_request_limit'] ) {
			return self::rest_error( 'daily_limit_reached', __( 'This chatbot has reached its daily request limit.', 'chat-trigger-embed-for-n8n' ), 429, false );
		}

		$session_state = self::session_state( $chatbot, $session_id );
		if ( (int) $chatbot['messages_per_session'] > 0 && (int) ( $session_state['requests'] ?? 0 ) >= (int) $chatbot['messages_per_session'] ) {
			return self::rest_error( 'session_limit_reached', __( 'This conversation has reached its session limit.', 'chat-trigger-embed-for-n8n' ), 429, false );
		}

		$provider_repo = new Provider_Connection_Repository();
		$provider_id   = (string) ( $chatbot['engine'] ?: 'mock' );
		$provider      = Provider_Registry::get( $provider_id ) ?: Provider_Registry::get( 'mock' );
		if ( ! $provider ) {
			return self::rest_error( 'provider_missing', __( 'No provider adapter is available.', 'chat-trigger-embed-for-n8n' ), 500, true );
		}
		$connection = array();
		if ( 'mock' !== $provider_id ) {
			$connection_record = $provider_repo->get( (string) $chatbot['provider_connection_id'] );
			if ( ! $connection_record ) {
				return self::rest_error( 'connection_missing', __( 'This chatbot does not have a provider connection yet.', 'chat-trigger-embed-for-n8n' ), 400, false );
			}
			$connection = $provider_repo->runtime_connection( $connection_record );
		}

		$history = self::sanitize_history( $request->get_param( 'history' ) );
		$ai_request = new AI_Request(
			chatbot_id: (string) $chatbot['id'],
			session_id: $session_id,
			user_message: $message,
			conversation_messages: $history,
			system_instructions: (string) $chatbot['system_instructions'],
			page_metadata: self::sanitize_metadata( $request->get_param( 'metadata' ) ),
			allowed_tools: array(),
			output_schema_preference: 'json',
			maximum_output_tokens: (int) $chatbot['maximum_output_tokens'],
			timeout: max( 5, min( 120, (int) ( $connection['timeout'] ?? 30 ) ) ),
			provider_settings: array(
				'connection' => $connection,
				'model'      => (string) $chatbot['model_id'],
			),
			safety_identifier: $chatbot['id'] . ':' . $session_id,
			request_id: $request_id
		);

		try {
			$response = $provider->send_message( $ai_request );
		} catch ( \Throwable $error ) {
			$usage_repo->bump( (string) $chatbot['id'], false );
			return self::rest_error( 'provider_exception', __( 'The provider request failed.', 'chat-trigger-embed-for-n8n' ), 500, true );
		}

		if ( $response->error ) {
			$usage_repo->bump( (string) $chatbot['id'], false );
			$status = self::error_status_from_response( $response->error );
			return new \WP_REST_Response(
				array(
					'status'        => 'error',
					'chatbotId'     => (string) $chatbot['id'],
					'sessionId'     => $session_id,
					'requestId'     => $request_id,
					'error'         => self::public_error( $response->error, (string) $chatbot['error_message'] ),
				),
				$status
			);
		}

		$usage_repo->bump(
			(string) $chatbot['id'],
			true,
			isset( $response->usage['input_tokens'] ) ? (int) $response->usage['input_tokens'] : null,
			isset( $response->usage['output_tokens'] ) ? (int) $response->usage['output_tokens'] : null
		);
		self::session_state( $chatbot, $session_id, true );

		$message_out = trim( $response->message );
		if ( '' === $message_out ) {
			$message_out = (string) $chatbot['static_fallback_message'];
		}
		if ( '' === $message_out ) {
			$message_out = (string) $chatbot['welcome_message'];
		}

		return rest_ensure_response(
			array(
				'status'      => 'ok',
				'chatbotId'   => (string) $chatbot['id'],
				'sessionId'   => $session_id,
				'requestId'   => $request_id,
				'provider'    => (string) $response->provider,
				'model'       => (string) $response->model,
				'message'     => $message_out,
				'options'     => array_values( (array) $response->options ),
				'leadStatus'  => $response->lead_status,
				'handoff'     => (bool) $response->handoff,
				'actions'     => array_values( (array) $response->actions ),
				'citations'   => array_values( (array) $response->citations ),
				'usage'       => (array) $response->usage,
			)
		);
	}

	public static function resolve_active_chatbot(): ?array {
		$repo = new Chatbot_Repository();
		foreach ( $repo->all() as $chatbot ) {
			if ( empty( $chatbot['enabled'] ) ) {
				continue;
			}
			if ( self::is_chatbot_visible( $chatbot ) ) {
				return $chatbot;
			}
		}

		return null;
	}

	public static function resolve_chatbot_by_id( string $id ): ?array {
		if ( '' === $id ) {
			return null;
		}
		return ( new Chatbot_Repository() )->get( $id );
	}

	public static function is_chatbot_visible( array $chatbot ): bool {
		$mode = (string) ( $chatbot['page_visibility_mode'] ?? 'entire_site' );
		$ids  = array_values( array_map( 'absint', (array) ( $chatbot['selected_page_ids'] ?? array() ) ) );

		switch ( $mode ) {
			case 'homepage':
				$visible = is_front_page() || is_home();
				break;
			case 'selected_pages':
				$visible = ! empty( $ids ) && is_page( $ids );
				break;
			case 'excluded_pages':
				$visible = empty( $ids ) || ! is_page( $ids );
				break;
			case 'entire_site':
			default:
				$visible = true;
		}

		return (bool) apply_filters( 'cten_v2_chatbot_visible', $visible, $chatbot );
	}

	public static function sanitize_connection_input( array $input ): array {
		return array(
			'id' => sanitize_key( (string) ( $input['id'] ?? '' ) ),
			'name' => sanitize_text_field( (string) ( $input['name'] ?? '' ) ),
			'provider' => sanitize_key( (string) ( $input['provider'] ?? 'mock' ) ),
			'enabled' => ! empty( $input['enabled'] ),
			'secret_source' => sanitize_key( (string) ( $input['secret_source'] ?? 'none' ) ),
			'secret_value' => (string) ( $input['secret_value'] ?? '' ),
			'project_id' => sanitize_text_field( (string) ( $input['project_id'] ?? '' ) ),
			'organization_id' => sanitize_text_field( (string) ( $input['organization_id'] ?? '' ) ),
			'default_model' => sanitize_text_field( (string) ( $input['default_model'] ?? '' ) ),
			'timeout' => absint( $input['timeout'] ?? 30 ),
		);
	}

	public static function sanitize_chatbot_input( array $input ): array {
		$quick_actions = self::sanitize_quick_actions( $input['quick_actions'] ?? array() );
		$selected_pages = self::sanitize_page_ids( $input['selected_page_ids'] ?? array() );

		return array(
			'id' => sanitize_key( (string) ( $input['id'] ?? '' ) ),
			'name' => sanitize_text_field( (string) ( $input['name'] ?? '' ) ),
			'internal_name' => sanitize_text_field( (string) ( $input['internal_name'] ?? '' ) ),
			'enabled' => ! empty( $input['enabled'] ),
			'engine' => sanitize_key( (string) ( $input['engine'] ?? 'mock' ) ),
			'provider_connection_id' => sanitize_key( (string) ( $input['provider_connection_id'] ?? '' ) ),
			'model_id' => sanitize_text_field( (string) ( $input['model_id'] ?? '' ) ),
			'system_instructions' => sanitize_textarea_field( (string) ( $input['system_instructions'] ?? '' ) ),
			'welcome_message' => sanitize_textarea_field( (string) ( $input['welcome_message'] ?? '' ) ),
			'input_placeholder' => sanitize_text_field( (string) ( $input['input_placeholder'] ?? '' ) ),
			'error_message' => sanitize_text_field( (string) ( $input['error_message'] ?? '' ) ),
			'static_fallback_message' => sanitize_text_field( (string) ( $input['static_fallback_message'] ?? '' ) ),
			'quick_actions' => $quick_actions,
			'theme_preset' => sanitize_key( (string) ( $input['theme_preset'] ?? 'premium-glass' ) ),
			'launcher_label' => sanitize_text_field( (string) ( $input['launcher_label'] ?? '' ) ),
			'page_visibility_mode' => sanitize_key( (string) ( $input['page_visibility_mode'] ?? 'entire_site' ) ),
			'selected_page_ids' => $selected_pages,
			'maximum_input_characters' => absint( $input['maximum_input_characters'] ?? 1000 ),
			'maximum_output_tokens' => absint( $input['maximum_output_tokens'] ?? 256 ),
			'messages_per_session' => absint( $input['messages_per_session'] ?? 50 ),
			'requests_per_minute' => absint( $input['requests_per_minute'] ?? 30 ),
			'daily_request_limit' => absint( $input['daily_request_limit'] ?? 0 ),
		);
	}

	private static function sanitize_quick_actions( mixed $actions ): array {
		$actions = is_array( $actions ) ? $actions : array();
		$output  = array();
		foreach ( array_slice( $actions, 0, 12 ) as $index => $action ) {
			if ( ! is_array( $action ) ) {
				continue;
			}
			$output[] = array(
				'id'      => sanitize_key( (string) ( $action['id'] ?? 'qa-' . ( $index + 1 ) ) ),
				'enabled' => ! empty( $action['enabled'] ),
				'label'   => sanitize_text_field( (string) ( $action['label'] ?? '' ) ),
				'message' => sanitize_textarea_field( (string) ( $action['message'] ?? '' ) ),
				'sort'    => absint( $action['sort'] ?? ( $index + 1 ) * 10 ),
			);
		}

		return $output;
	}

	private static function sanitize_page_ids( mixed $value ): array {
		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}
		$value = is_array( $value ) ? $value : array();
		return array_values( array_filter( array_map( 'absint', $value ) ) );
	}

	private static function sanitize_metadata( mixed $value ): array {
		return is_array( $value ) ? $value : array();
	}

	private static function sanitize_history( mixed $history ): array {
		if ( ! is_array( $history ) ) {
			return array();
		}

		$output = array();
		foreach ( array_slice( $history, -12 ) as $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}
			$output[] = array(
				'role' => sanitize_key( (string) ( $entry['role'] ?? '' ) ),
				'content' => sanitize_textarea_field( (string) ( $entry['content'] ?? '' ) ),
			);
		}

		return $output;
	}

	private static function sanitize_message( string $message, int $max_chars ): string {
		$message = sanitize_textarea_field( $message );
		$max_chars = max( 50, min( 10000, $max_chars ) );
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $message, 0, $max_chars );
		}
		return substr( $message, 0, $max_chars );
	}

	private static function sanitize_session_id( string $value ): string {
		$value = sanitize_text_field( $value );
		return preg_match( '/^[A-Za-z0-9_-]{6,128}$/', $value ) ? $value : '';
	}

	private static function session_state( array $chatbot, string $session_id, bool $increment = false ): array {
		$key = self::SESSION_LIMIT_PREFIX . md5( (string) $chatbot['id'] . ':' . $session_id );
		$state = get_transient( $key );
		$state = is_array( $state ) ? $state : array( 'requests' => 0, 'updated_at' => time() );
		if ( $increment ) {
			$state['requests'] = (int) ( $state['requests'] ?? 0 ) + 1;
			$state['updated_at'] = time();
			set_transient( $key, $state, DAY_IN_SECONDS );
		}
		return $state;
	}

	private static function check_rate_limit( array $chatbot, string $session_id ) {
		$limit = max( 1, (int) ( $chatbot['requests_per_minute'] ?? 30 ) );
		$key = self::RATE_LIMIT_PREFIX . md5( (string) $chatbot['id'] . ':' . $session_id );
		$state = get_transient( $key );
		$state = is_array( $state ) ? $state : array( 'count' => 0, 'window_start' => time() );
		$now = time();
		if ( $now - (int) ( $state['window_start'] ?? 0 ) >= MINUTE_IN_SECONDS ) {
			$state = array( 'count' => 0, 'window_start' => $now );
		}
		if ( (int) ( $state['count'] ?? 0 ) >= $limit ) {
			return new \WP_Error( 'rate_limited', __( 'Please wait a moment before sending another message.', 'chat-trigger-embed-for-n8n' ) );
		}
		$state['count'] = (int) ( $state['count'] ?? 0 ) + 1;
		set_transient( $key, $state, MINUTE_IN_SECONDS );
		return true;
	}

	private static function rest_error( string $code, string $message, int $status, bool $retryable ): \WP_REST_Response {
		return new \WP_REST_Response(
			array(
				'status' => 'error',
				'error'  => array(
					'code' => $code,
					'visitor_message' => $message,
					'retryable' => $retryable,
				),
			),
			$status
		);
	}

	private static function public_error( array $error, string $fallback_message ): array {
		return array(
			'code' => (string) ( $error['code'] ?? $error['category'] ?? 'unknown_error' ),
			'visitor_message' => (string) ( $error['visitor_message'] ?? $error['message'] ?? $fallback_message ),
			'retryable' => ! empty( $error['retryable'] ),
		);
	}

	private static function error_status_from_response( ?array $error ): int {
		$category = (string) ( $error['category'] ?? $error['code'] ?? '' );
		return in_array( $category, array( 'invalid_credentials', 'invalid_request', 'spam_detected', 'chatbot_not_found', 'chatbot_not_visible', 'empty_message' ), true ) ? 400 : 500;
	}

	private static function theme_palette( string $preset ): array {
		$palettes = array(
			'premium-glass' => array(
				'--chat--color--primary' => '#6d28d9',
				'--chat--color--secondary' => '#7c3aed',
				'--chat--message--user--background' => '#6d28d9',
				'--chat--message--bot--background' => '#111827',
				'--chat--body--background' => '#0f172a',
				'--chat--header--background' => '#111827',
				'--chat--footer--background' => '#111827',
				'--chat--input--background' => '#111827',
				'--chat--input--container--background' => '#111827',
			),
			'clean-light' => array(
				'--chat--color--primary' => '#0f766e',
				'--chat--color--secondary' => '#14b8a6',
				'--chat--message--user--background' => '#0f766e',
				'--chat--message--bot--background' => '#ffffff',
				'--chat--body--background' => '#f8fafc',
				'--chat--header--background' => '#ffffff',
				'--chat--footer--background' => '#ffffff',
				'--chat--header--color' => '#0f172a',
				'--chat--footer--color' => '#475569',
				'--chat--input--background' => '#ffffff',
				'--chat--input--container--background' => '#ffffff',
			),
			'brand-purple' => array(
				'--chat--color--primary' => '#7c3aed',
				'--chat--color--secondary' => '#a855f7',
				'--chat--message--user--background' => '#7c3aed',
				'--chat--message--bot--background' => '#1f2937',
				'--chat--body--background' => '#111827',
				'--chat--header--background' => '#1f2937',
				'--chat--footer--background' => '#1f2937',
			),
			'high-contrast' => array(
				'--chat--color--primary' => '#111827',
				'--chat--color--secondary' => '#0f172a',
				'--chat--message--user--background' => '#111827',
				'--chat--message--bot--background' => '#000000',
				'--chat--body--background' => '#ffffff',
				'--chat--header--background' => '#000000',
				'--chat--footer--background' => '#000000',
				'--chat--header--color' => '#ffffff',
				'--chat--footer--color' => '#f8fafc',
				'--chat--message--bot--color' => '#ffffff',
				'--chat--message--user--color' => '#ffffff',
			),
		);

		return $palettes[ $preset ] ?? $palettes['premium-glass'];
	}

	private static function admin_redirect( string $page, string $id, string $key ): string {
		return add_query_arg(
			array_filter(
				array(
					'page' => $page,
					$key => $id,
				)
			),
			admin_url( 'admin.php' )
		);
	}

	private static function require_manage_options(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'chat-trigger-embed-for-n8n' ) );
		}
	}
}
