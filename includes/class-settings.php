<?php
/**
 * Settings schema and sanitizers.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Settings {
	public static function hooks(): void {
		add_action( 'admin_post_cten_save_settings', array( __CLASS__, 'save' ) );
	}

	public static function save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'chat-trigger-embed-for-n8n' ) );
		}

		check_admin_referer( 'cten_save_settings', 'cten_settings_nonce' );

		$existing = Helpers::get_settings();
		$input    = self::collect_post_data();
		$raw_url  = (string) ( $input['webhook_url'] ?? '' );
		$settings = self::sanitize( array_merge( $existing, $input ) );
		update_option( Helpers::option_name(), $settings, false );
		Analytics::maybe_create_table( $settings );

		$warning = '';
		if ( '' !== trim( $raw_url ) && '' === $settings['webhook_url'] ) {
			$warning = __( 'The webhook URL looks malformed. Use the production Chat Trigger URL from an active workflow.', 'chat-trigger-embed-for-n8n' );
		} elseif ( str_contains( strtolower( $raw_url ), 'webhook-test' ) ) {
			$warning = __( 'You appear to have pasted a test webhook. Use the production Chat Trigger URL instead.', 'chat-trigger-embed-for-n8n' );
		}

		if ( '' !== $warning ) {
			set_transient( 'cten_admin_error', $warning, 60 );
		}

		if ( '' === $warning ) {
			set_transient( 'cten_admin_message', __( 'Settings saved.', 'chat-trigger-embed-for-n8n' ), 60 );
		}

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=cten-dashboard' ) );
		exit;
	}

	private static function collect_post_data(): array {
		$posted = wp_unslash( $_POST );
		unset( $posted['cten_settings_nonce'], $posted['action'] );

		if ( isset( $posted['public_request_headers_json'] ) ) {
			$headers = json_decode( (string) $posted['public_request_headers_json'], true );
			$posted['public_request_headers'] = is_array( $headers ) ? $headers : array();
			unset( $posted['public_request_headers_json'] );
		}

		if ( isset( $posted['visibility']['selected_pages'] ) && is_string( $posted['visibility']['selected_pages'] ) ) {
			$posted['visibility']['selected_pages'] = array_filter( array_map( 'absint', explode( ',', $posted['visibility']['selected_pages'] ) ) );
		}
		if ( isset( $posted['visibility']['excluded_pages'] ) && is_string( $posted['visibility']['excluded_pages'] ) ) {
			$posted['visibility']['excluded_pages'] = array_filter( array_map( 'absint', explode( ',', $posted['visibility']['excluded_pages'] ) ) );
		}
		if ( isset( $posted['visibility']['selected_types'] ) && is_string( $posted['visibility']['selected_types'] ) ) {
			$posted['visibility']['selected_types'] = array_filter( array_map( 'sanitize_key', array_map( 'trim', explode( ',', $posted['visibility']['selected_types'] ) ) ) );
		}
		if ( isset( $posted['visibility']['devices'] ) && is_array( $posted['visibility']['devices'] ) ) {
			$posted['visibility']['devices'] = array_keys( array_filter( $posted['visibility']['devices'] ) );
		}

		return is_array( $posted ) ? $posted : array();
	}

	public static function defaults(): array {
		return array(
			'enabled'                     => false,
			'webhook_url'                 => '',
			'render_mode'                 => 'global_footer',
			'theme_mode'                  => 'system',
			'load_previous_session'       => false,
			'enable_streaming'            => false,
			'request_method'              => 'POST',
			'public_request_headers'      => array(),
			'chat_input_key'             => 'chatInput',
			'chat_session_key'           => 'sessionId',
			'default_language'           => 'en',
			'debug_mode'                 => false,
			'connection_test_mode'       => 'url_only',
			'theme_preset'               => 'premium-glass',
			'primary_color'              => '#6d28d9',
			'secondary_color'            => '#7c3aed',
			'accent_color'               => '#22c55e',
			'window_background'          => '#0f172a',
			'header_background'          => '#111827',
			'footer_background'          => '#111827',
			'text_color'                 => '#e5e7eb',
			'muted_text_color'           => '#9ca3af',
			'link_color'                 => '#93c5fd',
			'error_color'                => '#ef4444',
			'success_color'              => '#22c55e',
			'online_indicator_color'     => '#4ade80',
			'bot_message_background'     => '#111827',
			'user_message_background'    => '#6d28d9',
			'input_background'           => '#111827',
			'border_color'               => 'rgba(255,255,255,0.12)',
			'glass_opacity'              => 0.82,
			'blur_strength'              => 18,
			'shadow_strength'            => 28,
			'border_radius'              => 20,
			'base_font_size'             => 16,
			'heading_font_size'          => 20,
			'message_font_size'          => 15,
			'desktop_width'              => 480,
			'desktop_height'             => 720,
			'tablet_width'               => 420,
			'tablet_height'              => 640,
			'mobile_layout'              => 'fullscreen',
			'launcher_position'          => 'bottom-right',
			'launcher_size'              => 64,
			'launcher_icon'              => 'sparkles',
			'launcher_label'             => __( 'Chat with us', 'chat-trigger-embed-for-n8n' ),
			'launcher_animation'         => 'pulse',
			'launcher_delay_seconds'     => 0,
			'auto_open_enabled'          => false,
			'auto_open_delay_seconds'    => 8,
			'close_on_outside_click'     => true,
			'show_online_indicator'      => true,
			'custom_avatar_image'        => '',
			'bot_name'                   => 'Chat Trigger AI',
			'bot_subtitle'               => 'Powered by your n8n workflow',
			'welcome_message'            => 'Welcome! Tell me what you would like help with, or choose an option below.',
			'input_placeholder'          => 'Type your message...',
			'start_conversation_label'    => 'Start Conversation',
			'follow_up_privacy_text'      => 'Share your best email for a tailored follow-up. No spam - only relevant communication.',
			'online_status_text'          => 'Online - AI replies instantly',
			'offline_error_text'         => 'The chatbot is temporarily unavailable.',
			'retry_button_text'          => 'Retry',
			'new_conversation_text'      => 'Start New Conversation',
			'typing_indicator_text'      => 'Typing...',
			'loading_message'            => 'Loading conversation...',
			'close_button_label'         => 'Close chat',
			'launcher_accessibility_label' => 'Open chatbot',
			'send_button_accessibility_label' => 'Send message',
			'initial_messages'           => array(
				array(
					'enabled' => true,
					'text'    => 'Welcome! Tell me what you would like help with, or choose an option below.',
					'sort'    => 10,
				),
			),
			'quick_actions'              => array(
				array(
					'enabled'  => true,
					'label'    => 'Try a Live Demo',
					'message'  => 'Show me a live chatbot demo for my type of business.',
					'icon'     => 'play',
					'sort'     => 10,
				),
				array(
					'enabled'  => true,
					'label'    => 'View Pricing',
					'message'  => 'Show me the current pricing for available services.',
					'icon'     => 'tag',
					'sort'     => 20,
				),
				array(
					'enabled'  => true,
					'label'    => 'Recommend a Solution',
					'message'  => 'Recommend the best chatbot or automation solution for my business.',
					'icon'     => 'sparkles',
					'sort'     => 30,
				),
				array(
					'enabled'  => true,
					'label'    => 'Request Follow-up',
					'message'  => 'I would like a personal follow-up.',
					'icon'     => 'mail',
					'sort'     => 40,
				),
			),
			'visibility'                 => array(
				'scope'            => 'entire_site',
				'selected_pages'   => array(),
				'excluded_pages'   => array(),
				'selected_types'   => array(),
				'devices'          => array( 'desktop', 'tablet', 'mobile' ),
				'auth'             => 'all',
			),
			'metadata_fields'            => array(
				'page_title'     => true,
				'page_url'       => true,
				'page_path'      => true,
				'referrer'       => true,
				'browser_lang'   => true,
				'browser_tz'     => true,
				'utm_source'     => true,
				'utm_medium'     => true,
				'utm_campaign'   => true,
				'utm_content'    => true,
				'industry'       => true,
				'post_id'        => true,
				'post_type'      => true,
				'plugin_version' => true,
				'theme_name'     => false,
			),
			'analytics_enabled'          => false,
			'analytics_retention_days'   => 30,
			'business_hours_enabled'     => false,
			'business_hours_timezone'    => wp_timezone_string(),
			'allow_ai_when_offline'      => true,
			'enable_whatsapp_fallback'   => false,
			'whatsapp_number'            => '',
			'whatsapp_default_message'   => 'Hello, I need help.',
			'enable_email_fallback'      => false,
			'contact_email'              => '',
			'email_subject'              => 'Chat support request',
			'enable_contact_page_fallback' => false,
			'contact_page_url'           => '',
			'human_support_button_label' => 'Contact support',
			'max_input_length'           => 1000,
			'prevent_rapid_sends'        => true,
			'minimum_send_delay_ms'      => 900,
			'confirm_new_conversation'   => true,
			'close_with_escape'          => true,
			'session_expiry_days'        => 30,
			'lazy_load_runtime'          => false,
			'preload_on_hover'           => false,
			'load_after_delay_seconds'   => 0,
			'conversation_menu_enabled'  => false,
			'conversation_export_enabled'=> false,
			'sound_enabled'              => false,
			'unread_indicator_enabled'   => false,
			'onboarding_status'          => array(
				'started'   => false,
				'completed' => false,
				'step'      => 'welcome',
			),
			'pre_chat_form'              => self::default_pre_chat_form(),
			'lead_qualification'         => self::default_lead_qualification(),
			'profiles'                   => array(),
			'delete_data_on_uninstall'   => false,
		);
	}

	public static function sanitize( array $input ): array {
		$defaults = self::defaults();
		$output   = $defaults;

		$output['enabled']               = ! empty( $input['enabled'] );
		$output['webhook_url']           = Helpers::sanitize_url( (string) ( $input['webhook_url'] ?? '' ) );
		$output['render_mode']           = self::sanitize_choice( (string) ( $input['render_mode'] ?? 'global_footer' ), self::render_modes(), 'global_footer' );
		$output['theme_mode']            = self::sanitize_choice( (string) ( $input['theme_mode'] ?? 'system' ), self::theme_modes(), 'system' );
		$output['load_previous_session']  = ! empty( $input['load_previous_session'] );
		$output['enable_streaming']       = ! empty( $input['enable_streaming'] );
		$output['request_method']         = in_array( strtoupper( (string) ( $input['request_method'] ?? 'POST' ) ), array( 'GET', 'POST' ), true ) ? strtoupper( (string) $input['request_method'] ) : 'POST';
		$output['public_request_headers']  = self::sanitize_headers( $input['public_request_headers'] ?? array() );
		$output['chat_input_key']         = self::sanitize_token( (string) ( $input['chat_input_key'] ?? 'chatInput' ), 'chatInput' );
		$output['chat_session_key']       = self::sanitize_token( (string) ( $input['chat_session_key'] ?? 'sessionId' ), 'sessionId' );
		$output['default_language']       = 'en';
		$output['debug_mode']             = ! empty( $input['debug_mode'] );
		$output['connection_test_mode']   = self::sanitize_choice( (string) ( $input['connection_test_mode'] ?? 'url_only' ), array( 'url_only', 'manual_message' ), 'url_only' );

		$output['theme_preset']           = self::sanitize_choice( (string) ( $input['theme_preset'] ?? 'premium-glass' ), self::theme_presets(), 'premium-glass' );
		$output['primary_color']          = sanitize_hex_color( $input['primary_color'] ?? '' ) ?: $defaults['primary_color'];
		$output['secondary_color']        = sanitize_hex_color( $input['secondary_color'] ?? '' ) ?: $defaults['secondary_color'];
		$output['accent_color']           = sanitize_hex_color( $input['accent_color'] ?? '' ) ?: $defaults['accent_color'];
		$output['window_background']      = self::sanitize_color( $input['window_background'] ?? $defaults['window_background'] );
		$output['header_background']      = self::sanitize_color( $input['header_background'] ?? $defaults['header_background'] );
		$output['footer_background']      = self::sanitize_color( $input['footer_background'] ?? $defaults['footer_background'] );
		$output['text_color']             = sanitize_hex_color( $input['text_color'] ?? '' ) ?: $defaults['text_color'];
		$output['muted_text_color']        = sanitize_hex_color( $input['muted_text_color'] ?? '' ) ?: $defaults['muted_text_color'];
		$output['link_color']             = sanitize_hex_color( $input['link_color'] ?? '' ) ?: $defaults['link_color'];
		$output['error_color']            = sanitize_hex_color( $input['error_color'] ?? '' ) ?: $defaults['error_color'];
		$output['success_color']          = sanitize_hex_color( $input['success_color'] ?? '' ) ?: $defaults['success_color'];
		$output['online_indicator_color'] = sanitize_hex_color( $input['online_indicator_color'] ?? '' ) ?: $defaults['online_indicator_color'];
		$output['bot_message_background']  = self::sanitize_color( $input['bot_message_background'] ?? $defaults['bot_message_background'] );
		$output['user_message_background'] = self::sanitize_color( $input['user_message_background'] ?? $defaults['user_message_background'] );
		$output['input_background']        = self::sanitize_color( $input['input_background'] ?? $defaults['input_background'] );
		$output['border_color']            = self::sanitize_color( $input['border_color'] ?? $defaults['border_color'] );
		$glass = is_numeric( $input['glass_opacity'] ?? null ) ? (float) $input['glass_opacity'] : (float) $defaults['glass_opacity'];
		if ( $glass > 1 ) {
			$glass = $glass / 100;
		}
		$output['glass_opacity']           = self::sanitize_float( $glass, 0.2, 1, 0.82 );
		$output['blur_strength']           = self::sanitize_int( $input['blur_strength'] ?? $defaults['blur_strength'], 0, 50, 18 );
		$output['shadow_strength']         = self::sanitize_int( $input['shadow_strength'] ?? $defaults['shadow_strength'], 0, 64, 28 );
		$output['border_radius']           = self::sanitize_int( $input['border_radius'] ?? $defaults['border_radius'], 0, 40, 20 );
		$output['base_font_size']          = self::sanitize_int( $input['base_font_size'] ?? $defaults['base_font_size'], 12, 20, 16 );
		$output['heading_font_size']       = self::sanitize_int( $input['heading_font_size'] ?? $defaults['heading_font_size'], 14, 28, 20 );
		$output['message_font_size']       = self::sanitize_int( $input['message_font_size'] ?? $defaults['message_font_size'], 12, 20, 15 );
		$output['desktop_width']           = self::sanitize_int( $input['desktop_width'] ?? $defaults['desktop_width'], 360, 800, 480 );
		$output['desktop_height']          = self::sanitize_int( $input['desktop_height'] ?? $defaults['desktop_height'], 480, 900, 720 );
		$output['tablet_width']            = self::sanitize_int( $input['tablet_width'] ?? $defaults['tablet_width'], 320, 700, 420 );
		$output['tablet_height']           = self::sanitize_int( $input['tablet_height'] ?? $defaults['tablet_height'], 420, 800, 640 );
		$output['mobile_layout']           = self::sanitize_choice( (string) ( $input['mobile_layout'] ?? 'fullscreen' ), array( 'fullscreen', 'window' ), 'fullscreen' );
		$output['launcher_position']       = self::sanitize_choice( (string) ( $input['launcher_position'] ?? 'bottom-right' ), array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' ), 'bottom-right' );
		$output['launcher_size']           = self::sanitize_int( $input['launcher_size'] ?? $defaults['launcher_size'], 48, 96, 64 );
		$output['launcher_icon']           = sanitize_text_field( (string) ( $input['launcher_icon'] ?? 'sparkles' ) ) ?: 'sparkles';
		$output['launcher_label']          = sanitize_text_field( (string) ( $input['launcher_label'] ?? $defaults['launcher_label'] ) );
		$output['launcher_animation']       = self::sanitize_choice( (string) ( $input['launcher_animation'] ?? 'pulse' ), array( 'pulse', 'bounce', 'fade', 'none' ), 'pulse' );
		$output['launcher_delay_seconds']   = self::sanitize_int( $input['launcher_delay_seconds'] ?? 0, 0, 60, 0 );
		$output['auto_open_enabled']        = ! empty( $input['auto_open_enabled'] );
		$output['auto_open_delay_seconds']  = self::sanitize_int( $input['auto_open_delay_seconds'] ?? 8, 3, 120, 8 );
		$output['close_on_outside_click']   = ! empty( $input['close_on_outside_click'] );
		$output['show_online_indicator']    = ! empty( $input['show_online_indicator'] );
		$output['custom_avatar_image']      = esc_url_raw( (string) ( $input['custom_avatar_image'] ?? '' ) );

		$output['bot_name']                = sanitize_text_field( (string) ( $input['bot_name'] ?? $defaults['bot_name'] ) );
		$output['bot_subtitle']            = sanitize_text_field( (string) ( $input['bot_subtitle'] ?? $defaults['bot_subtitle'] ) );
		$output['welcome_message']         = sanitize_textarea_field( (string) ( $input['welcome_message'] ?? $defaults['welcome_message'] ) );
		$output['input_placeholder']       = sanitize_text_field( (string) ( $input['input_placeholder'] ?? $defaults['input_placeholder'] ) );
		$output['start_conversation_label'] = sanitize_text_field( (string) ( $input['start_conversation_label'] ?? $defaults['start_conversation_label'] ) );
		$output['follow_up_privacy_text']   = sanitize_textarea_field( (string) ( $input['follow_up_privacy_text'] ?? $defaults['follow_up_privacy_text'] ) );
		$output['online_status_text']       = sanitize_text_field( (string) ( $input['online_status_text'] ?? $defaults['online_status_text'] ) );
		$output['offline_error_text']       = sanitize_text_field( (string) ( $input['offline_error_text'] ?? $defaults['offline_error_text'] ) );
		$output['retry_button_text']        = sanitize_text_field( (string) ( $input['retry_button_text'] ?? $defaults['retry_button_text'] ) );
		$output['new_conversation_text']    = sanitize_text_field( (string) ( $input['new_conversation_text'] ?? $defaults['new_conversation_text'] ) );
		$output['typing_indicator_text']    = sanitize_text_field( (string) ( $input['typing_indicator_text'] ?? $defaults['typing_indicator_text'] ) );
		$output['loading_message']          = sanitize_text_field( (string) ( $input['loading_message'] ?? $defaults['loading_message'] ) );
		$output['close_button_label']       = sanitize_text_field( (string) ( $input['close_button_label'] ?? $defaults['close_button_label'] ) );
		$output['launcher_accessibility_label'] = sanitize_text_field( (string) ( $input['launcher_accessibility_label'] ?? $defaults['launcher_accessibility_label'] ) );
		$output['send_button_accessibility_label'] = sanitize_text_field( (string) ( $input['send_button_accessibility_label'] ?? $defaults['send_button_accessibility_label'] ) );
		$output['initial_messages']         = self::sanitize_initial_messages( $input['initial_messages'] ?? $defaults['initial_messages'] );

		$output['quick_actions']            = self::sanitize_quick_actions( $input['quick_actions'] ?? array(), 30 );
		$output['visibility']               = self::sanitize_visibility( $input['visibility'] ?? array() );
		$output['metadata_fields']          = self::sanitize_metadata( $input['metadata_fields'] ?? array() );
		$output['analytics_enabled']        = ! empty( $input['analytics_enabled'] );
		$output['analytics_retention_days'] = self::sanitize_int( $input['analytics_retention_days'] ?? 30, 7, 180, 30 );
		$output['business_hours_enabled']   = false;
		$output['business_hours_timezone']  = sanitize_text_field( (string) ( $input['business_hours_timezone'] ?? $defaults['business_hours_timezone'] ) );
		$output['allow_ai_when_offline']    = true;
		$output['enable_whatsapp_fallback'] = ! empty( $input['enable_whatsapp_fallback'] );
		$output['whatsapp_number']          = preg_replace( '/[^0-9+]/', '', (string) ( $input['whatsapp_number'] ?? '' ) );
		$output['whatsapp_default_message'] = sanitize_text_field( (string) ( $input['whatsapp_default_message'] ?? $defaults['whatsapp_default_message'] ) );
		$output['enable_email_fallback']    = ! empty( $input['enable_email_fallback'] );
		$output['contact_email']            = sanitize_email( (string) ( $input['contact_email'] ?? '' ) );
		$output['email_subject']            = sanitize_text_field( (string) ( $input['email_subject'] ?? $defaults['email_subject'] ) );
		$output['enable_contact_page_fallback'] = ! empty( $input['enable_contact_page_fallback'] );
		$output['contact_page_url']         = Helpers::sanitize_url( (string) ( $input['contact_page_url'] ?? '' ) );
		$output['human_support_button_label'] = sanitize_text_field( (string) ( $input['human_support_button_label'] ?? $defaults['human_support_button_label'] ) );
		$output['max_input_length']         = self::sanitize_int( $input['max_input_length'] ?? 1000, 100, 4000, 1000 );
		$output['prevent_rapid_sends']      = ! empty( $input['prevent_rapid_sends'] );
		$output['minimum_send_delay_ms']    = self::sanitize_int( $input['minimum_send_delay_ms'] ?? 900, 250, 5000, 900 );
		$output['confirm_new_conversation'] = ! empty( $input['confirm_new_conversation'] );
		$output['close_with_escape']        = ! empty( $input['close_with_escape'] );
		$output['session_expiry_days']      = self::sanitize_int( $input['session_expiry_days'] ?? 30, 1, 180, 30 );
		$output['lazy_load_runtime']        = ! empty( $input['lazy_load_runtime'] );
		$output['preload_on_hover']         = ! empty( $input['preload_on_hover'] );
		$output['load_after_delay_seconds'] = self::sanitize_int( $input['load_after_delay_seconds'] ?? 0, 0, 60, 0 );
		$output['conversation_menu_enabled'] = false;
		$output['conversation_export_enabled'] = false;
		$output['sound_enabled']            = false;
		$output['unread_indicator_enabled'] = false;
		$output['onboarding_status']        = self::sanitize_onboarding_status( $input['onboarding_status'] ?? $defaults['onboarding_status'] );
		$output['pre_chat_form']            = self::sanitize_pre_chat_form( $input['pre_chat_form'] ?? array() );
		$output['lead_qualification']       = self::sanitize_lead_qualification( $input['lead_qualification'] ?? array() );
		$output['profiles']                 = Profiles::sanitize_profiles( $input['profiles'] ?? array(), $output );
		$output['delete_data_on_uninstall']  = ! empty( $input['delete_data_on_uninstall'] );

		return apply_filters( 'cten_sanitized_settings', $output, $input );
	}

	public static function sanitize_headers( mixed $headers ): array {
		$output = array();
		if ( ! is_array( $headers ) ) {
			return $output;
		}

		foreach ( $headers as $name => $value ) {
			$key = sanitize_text_field( (string) $name );
			if ( '' === $key ) {
				continue;
			}
			$output[ $key ] = sanitize_text_field( (string) $value );
		}

		return $output;
	}

	public static function sanitize_quick_actions( mixed $actions, int $limit = 30 ): array {
		$defaults = self::defaults()['quick_actions'];
		$output   = array();
		$actions  = is_array( $actions ) ? array_values( $actions ) : array();

		for ( $i = 0; $i < $limit; $i++ ) {
			$raw             = $actions[ $i ] ?? array();
			$default         = $defaults[ $i ] ?? array(
				'enabled' => false,
				'label'   => '',
				'message' => '',
				'icon'    => 'sparkles',
				'sort'    => ( $i + 1 ) * 10,
			);
			$label           = self::limit_text( sanitize_text_field( (string) ( $raw['label'] ?? $default['label'] ) ), 80 );
			$message         = self::limit_text( sanitize_textarea_field( (string) ( $raw['message'] ?? $default['message'] ) ), 280 );
			$output[ $i ]     = array(
				'enabled' => ! empty( $raw['enabled'] ) && '' !== $label && '' !== $message,
				'label'   => $label,
				'message' => $message,
				'icon'    => self::sanitize_choice( sanitize_key( (string) ( $raw['icon'] ?? $default['icon'] ) ), array( 'play', 'tag', 'sparkles', 'mail', 'help', 'calendar', 'phone', 'download' ), 'sparkles' ),
				'sort'    => self::sanitize_int( $raw['sort'] ?? $default['sort'], 0, 999, $default['sort'] ),
			);
		}

		return $output;
	}

	public static function sanitize_initial_messages( mixed $messages, int $limit = 20 ): array {
		$messages = is_array( $messages ) ? array_values( $messages ) : array();
		$output   = array();

		foreach ( array_slice( $messages, 0, $limit ) as $index => $message ) {
			$raw_text = is_array( $message ) ? ( $message['text'] ?? '' ) : $message;
			$text     = self::limit_text( sanitize_textarea_field( (string) $raw_text ), 300 );
			if ( '' === $text ) {
				continue;
			}
			$output[] = array(
				'enabled' => ! is_array( $message ) || ! empty( $message['enabled'] ),
				'text'    => $text,
				'sort'    => self::sanitize_int( is_array( $message ) ? ( $message['sort'] ?? ( $index + 1 ) * 10 ) : ( $index + 1 ) * 10, 0, 999, ( $index + 1 ) * 10 ),
			);
		}

		if ( empty( $output ) ) {
			return self::defaults()['initial_messages'];
		}

		usort( $output, static fn( array $a, array $b ): int => (int) $a['sort'] <=> (int) $b['sort'] );
		return $output;
	}

	public static function default_pre_chat_form(): array {
		return array(
			'enabled'      => false,
			'sending'      => 'metadata',
			'allow_skip'   => true,
			'privacy_text' => 'Your name, email, phone number, and other selected details are sent to your webhook when you start the chat.',
			'fields'       => array(
				array( 'key' => 'name', 'type' => 'text', 'enabled' => true, 'required' => false, 'label' => 'Name', 'placeholder' => 'Your name', 'sort' => 10 ),
				array( 'key' => 'email', 'type' => 'email', 'enabled' => true, 'required' => false, 'label' => 'Email', 'placeholder' => 'you@example.com', 'sort' => 20 ),
				array( 'key' => 'phone', 'type' => 'phone', 'enabled' => true, 'required' => false, 'label' => 'Phone', 'placeholder' => '+1 555 123 4567', 'sort' => 30 ),
				array( 'key' => 'whatsapp', 'type' => 'phone', 'enabled' => true, 'required' => false, 'label' => 'WhatsApp', 'placeholder' => '+880 1...', 'sort' => 40 ),
				array( 'key' => 'company', 'type' => 'text', 'enabled' => true, 'required' => false, 'label' => 'Company', 'placeholder' => 'Company name', 'sort' => 50 ),
				array( 'key' => 'website', 'type' => 'url', 'enabled' => true, 'required' => false, 'label' => 'Website', 'placeholder' => 'https://example.com', 'sort' => 60 ),
				array( 'key' => 'business_type', 'type' => 'select', 'enabled' => true, 'required' => false, 'label' => 'Business Type', 'placeholder' => '', 'sort' => 70, 'options' => array( 'Agency', 'Ecommerce', 'Healthcare', 'Restaurant' ) ),
				array( 'key' => 'country', 'type' => 'select', 'enabled' => true, 'required' => false, 'label' => 'Country', 'placeholder' => '', 'sort' => 80, 'options' => array( 'Bangladesh', 'United States', 'United Kingdom', 'Canada' ) ),
				array( 'key' => 'custom_text', 'type' => 'text', 'enabled' => false, 'required' => false, 'label' => 'Custom Text', 'placeholder' => 'Custom text', 'sort' => 90 ),
				array( 'key' => 'custom_select', 'type' => 'select', 'enabled' => false, 'required' => false, 'label' => 'Custom Select', 'placeholder' => '', 'sort' => 100, 'options' => array( 'Option A', 'Option B', 'Option C' ) ),
				array( 'key' => 'consent', 'type' => 'consent', 'enabled' => true, 'required' => true, 'label' => 'I agree to be contacted about my request.', 'placeholder' => '', 'sort' => 110 ),
			),
		);
	}

	public static function sanitize_pre_chat_form( mixed $form ): array {
		$defaults = self::default_pre_chat_form();
		$form     = is_array( $form ) ? $form : array();
		$output   = array(
			'enabled'      => ! empty( $form['enabled'] ),
			'sending'      => 'metadata',
			'allow_skip'   => ! empty( $form['allow_skip'] ),
			'privacy_text' => self::limit_text( sanitize_textarea_field( (string) ( $form['privacy_text'] ?? $defaults['privacy_text'] ) ), 300 ),
			'fields'       => array(),
		);

		$fields = is_array( $form['fields'] ?? null ) ? array_values( $form['fields'] ) : $defaults['fields'];
		foreach ( array_slice( $fields, 0, 12 ) as $index => $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			$key = sanitize_key( (string) ( $field['key'] ?? 'field_' . ( $index + 1 ) ) );
			if ( '' === $key || in_array( $key, array( 'password', 'token', 'nonce', 'cookie' ), true ) ) {
				continue;
			}
			$output['fields'][] = array(
				'key'         => $key,
				'type'        => self::sanitize_choice( sanitize_key( (string) ( $field['type'] ?? 'text' ) ), array( 'text', 'email', 'phone', 'url', 'select', 'consent' ), 'text' ),
				'enabled'     => ! empty( $field['enabled'] ),
				'required'    => ! empty( $field['required'] ),
				'label'       => self::limit_text( sanitize_text_field( (string) ( $field['label'] ?? ucfirst( $key ) ) ), 80 ),
				'placeholder' => self::limit_text( sanitize_text_field( (string) ( $field['placeholder'] ?? '' ) ), 120 ),
				'help'        => self::limit_text( sanitize_text_field( (string) ( $field['help'] ?? '' ) ), 160 ),
				'sort'        => self::sanitize_int( $field['sort'] ?? ( $index + 1 ) * 10, 0, 999, ( $index + 1 ) * 10 ),
				'options'     => self::sanitize_string_list( $field['options'] ?? array(), 10, 80 ),
			);
		}

		usort( $output['fields'], static fn( array $a, array $b ): int => (int) $a['sort'] <=> (int) $b['sort'] );
		return $output;
	}

	public static function default_lead_qualification(): array {
		return array(
			'enabled'            => false,
			'goal'               => 'Understand visitor needs and route follow-up.',
			'budget_ranges'      => array( 'Under $1,000', '$1,000-$5,000', '$5,000+' ),
			'timeline_options'   => array( 'ASAP', 'This month', 'Later' ),
			'completion_message' => 'Thanks. I have enough context to recommend the next step.',
			'human_followup_cta' => 'Request human follow-up',
		);
	}

	public static function sanitize_lead_qualification( mixed $input ): array {
		$defaults = self::default_lead_qualification();
		$input    = is_array( $input ) ? $input : array();
		return array(
			'enabled'            => ! empty( $input['enabled'] ),
			'goal'               => self::limit_text( sanitize_text_field( (string) ( $input['goal'] ?? $defaults['goal'] ) ), 160 ),
			'budget_ranges'      => self::sanitize_string_list( $input['budget_ranges'] ?? $defaults['budget_ranges'], 10, 60 ),
			'timeline_options'   => self::sanitize_string_list( $input['timeline_options'] ?? $defaults['timeline_options'], 10, 60 ),
			'completion_message' => self::limit_text( sanitize_text_field( (string) ( $input['completion_message'] ?? $defaults['completion_message'] ) ), 180 ),
			'human_followup_cta' => self::limit_text( sanitize_text_field( (string) ( $input['human_followup_cta'] ?? $defaults['human_followup_cta'] ) ), 80 ),
		);
	}

	public static function sanitize_string_list( mixed $items, int $limit, int $item_length ): array {
		$items = is_array( $items ) ? $items : explode( ',', (string) $items );
		$output = array();
		foreach ( array_slice( $items, 0, $limit ) as $item ) {
			$value = self::limit_text( sanitize_text_field( (string) $item ), $item_length );
			if ( '' !== $value ) {
				$output[] = $value;
			}
		}
		return $output;
	}

	public static function sanitize_onboarding_status( mixed $status ): array {
		$status = is_array( $status ) ? $status : array();
		return array(
			'started'   => ! empty( $status['started'] ),
			'completed' => ! empty( $status['completed'] ),
			'step'      => self::sanitize_choice( sanitize_key( (string) ( $status['step'] ?? 'welcome' ) ), array( 'welcome', 'connect', 'identity', 'appearance', 'quick_actions', 'visibility', 'preview', 'enable' ), 'welcome' ),
		);
	}

	public static function theme_presets(): array {
		return array( 'premium-glass', 'minimal-dark', 'clean-light', 'brand-purple', 'midnight-blue', 'soft-neutral', 'elegant-gold', 'modern-green', 'corporate-blue', 'high-contrast' );
	}

	public static function theme_modes(): array {
		return array( 'system', 'light', 'dark' );
	}

	public static function render_modes(): array {
		return array( 'global_footer', 'elementor_widget', 'both' );
	}

	public static function can_render_in_footer( array $settings ): bool {
		return in_array( (string) ( $settings['render_mode'] ?? 'global_footer' ), array( 'global_footer', 'both' ), true );
	}

	public static function can_render_in_elementor_widget( array $settings ): bool {
		return in_array( (string) ( $settings['render_mode'] ?? 'global_footer' ), array( 'elementor_widget', 'both' ), true );
	}

	public static function resolve_theme_mode( array $settings = array() ): string {
		$mode = (string) ( $settings['theme_mode'] ?? 'system' );
		return in_array( $mode, self::theme_modes(), true ) ? $mode : 'system';
	}

	public static function sanitize_visibility( mixed $visibility ): array {
		$defaults = self::defaults()['visibility'];
		$visibility = is_array( $visibility ) ? $visibility : array();

		return array(
			'scope'          => self::sanitize_choice( (string) ( $visibility['scope'] ?? $defaults['scope'] ), array( 'entire_site', 'homepage_only', 'selected_pages', 'excluded_pages', 'selected_post_types' ), $defaults['scope'] ),
			'selected_pages'  => array_map( 'absint', isset( $visibility['selected_pages'] ) && is_array( $visibility['selected_pages'] ) ? $visibility['selected_pages'] : array() ),
			'excluded_pages'  => array_map( 'absint', isset( $visibility['excluded_pages'] ) && is_array( $visibility['excluded_pages'] ) ? $visibility['excluded_pages'] : array() ),
			'selected_types'  => array_map( 'sanitize_key', isset( $visibility['selected_types'] ) && is_array( $visibility['selected_types'] ) ? $visibility['selected_types'] : array() ),
			'devices'         => array_values( array_intersect( array( 'desktop', 'tablet', 'mobile' ), isset( $visibility['devices'] ) && is_array( $visibility['devices'] ) ? array_map( 'sanitize_key', $visibility['devices'] ) : array( 'desktop', 'tablet', 'mobile' ) ) ),
			'auth'            => self::sanitize_choice( (string) ( $visibility['auth'] ?? $defaults['auth'] ), array( 'all', 'logged_in', 'logged_out' ), $defaults['auth'] ),
		);
	}

	public static function sanitize_metadata( mixed $metadata ): array {
		$allowed = array_keys( self::defaults()['metadata_fields'] );
		$metadata = is_array( $metadata ) ? $metadata : array();
		$output   = array();
		foreach ( $allowed as $key ) {
			$output[ $key ] = ! empty( $metadata[ $key ] );
		}
		return $output;
	}

	public static function sanitize_choice( string $value, array $choices, string $default ): string {
		return in_array( $value, $choices, true ) ? $value : $default;
	}

	public static function sanitize_color( mixed $value ): string {
		$value = is_string( $value ) ? trim( $value ) : '';
		if ( '' === $value ) {
			return '';
		}

		if ( preg_match( '/^rgba?\([0-9.,\s%]+\)$/i', $value ) ) {
			return $value;
		}

		return sanitize_hex_color( $value ) ?: '';
	}

	public static function sanitize_token( string $value, string $default ): string {
		$value = sanitize_text_field( $value );
		return preg_match( '/^[A-Za-z0-9_-]+$/', $value ) ? $value : $default;
	}

	public static function sanitize_int( mixed $value, int $min, int $max, int $default ): int {
		$value = absint( $value );
		if ( $value < $min || $value > $max ) {
			return $default;
		}
		return $value;
	}

	public static function sanitize_float( mixed $value, float $min, float $max, float $default ): float {
		$value = is_numeric( $value ) ? (float) $value : $default;
		if ( $value < $min || $value > $max ) {
			return $default;
		}
		return $value;
	}

	public static function limit_text( string $value, int $max_length ): string {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $max_length );
		}
		return substr( $value, 0, $max_length );
	}

	public static function get_public_config( array $settings = array() ): array {
		$settings = $settings ? $settings : self::defaults();
		if ( empty( $settings['resolved_profile_id'] ) || empty( $settings['resolved_profile_name'] ) ) {
			$settings = Profiles::resolve( $settings );
		}
		$quick    = array_values(
			array_filter(
				$settings['quick_actions'],
				static fn ( array $item ): bool => ! empty( $item['enabled'] )
			)
		);
		usort(
			$quick,
			static fn ( array $a, array $b ): int => (int) $a['sort'] <=> (int) $b['sort']
		);

		$metadata = self::build_metadata( $settings );

		return apply_filters(
			'cten_public_chat_config',
			array(
				'pluginVersion'      => CTEN_VERSION,
				'profileId'          => (string) ( $settings['resolved_profile_id'] ?? 'main' ),
				'profileName'        => (string) ( $settings['resolved_profile_name'] ?? 'Main Website Assistant' ),
				'target'             => '#cten-chat-root',
				'mode'               => 'legacy_n8n',
				'webhookUrl'         => (string) $settings['webhook_url'],
				'webhookConfig'      => array(
					'method'  => $settings['request_method'],
					'headers'  => (array) $settings['public_request_headers'],
				),
				'launcherPosition'   => (string) $settings['launcher_position'],
				'launcherSize'       => (int) $settings['launcher_size'],
				'launcherDelaySeconds' => (int) $settings['launcher_delay_seconds'],
				'autoOpenEnabled'    => (bool) $settings['auto_open_enabled'],
				'autoOpenDelaySeconds' => (int) $settings['auto_open_delay_seconds'],
				'closeOnOutsideClick' => (bool) $settings['close_on_outside_click'],
				'mobileLayout'       => (string) $settings['mobile_layout'],
				'desktopWidth'       => (int) $settings['desktop_width'],
				'desktopHeight'      => (int) $settings['desktop_height'],
				'tabletWidth'        => (int) $settings['tablet_width'],
				'tabletHeight'       => (int) $settings['tablet_height'],
				'launcherAccessibilityLabel' => (string) $settings['launcher_accessibility_label'],
				'showOnlineIndicator' => (bool) $settings['show_online_indicator'],
				'customAvatarImage'   => (string) $settings['custom_avatar_image'],
				'themeMode'           => self::resolve_theme_mode( $settings ),
				'chatInputKey'       => (string) $settings['chat_input_key'],
				'chatSessionKey'     => (string) $settings['chat_session_key'],
				'loadPreviousSession'=> (bool) $settings['load_previous_session'],
				'defaultLanguage'    => 'en',
				'enableStreaming'    => (bool) $settings['enable_streaming'],
				'initialMessages'    => self::get_initial_messages( $settings ),
				'i18n'               => self::build_i18n( $settings ),
				'metadata'           => $metadata,
				'metadataFields'      => (array) $settings['metadata_fields'],
				'showWelcomeScreen'  => false,
				'showWindowCloseButton' => true,
				'enableMessageActions' => true,
				'allowFileUploads'   => false,
				'offlineErrorText'   => (string) $settings['offline_error_text'],
				'retryButtonText'    => (string) $settings['retry_button_text'],
				'newConversationText'=> (string) $settings['new_conversation_text'],
				'debugMode'          => (bool) $settings['debug_mode'],
				'preventRapidSends'  => (bool) $settings['prevent_rapid_sends'],
				'minimumSendDelayMs' => (int) $settings['minimum_send_delay_ms'],
				'maxInputLength'     => (int) $settings['max_input_length'],
				'confirmNewConversation' => (bool) $settings['confirm_new_conversation'],
				'closeWithEscape'    => (bool) $settings['close_with_escape'],
				'sessionExpiryDays'   => (int) $settings['session_expiry_days'],
				'contactFallback'    => self::get_contact_fallback( $settings ),
				'preChatForm'        => (array) $settings['pre_chat_form'],
				'leadQualification'  => (array) $settings['lead_qualification'],
				'lazyLoadRuntime'    => (bool) $settings['lazy_load_runtime'],
				'preloadOnHover'     => (bool) $settings['preload_on_hover'],
				'loadAfterDelaySeconds' => (int) $settings['load_after_delay_seconds'],
				'quickActions'       => $quick,
				'visibility'         => $settings['visibility'],
				'appearance'         => self::get_css_variables( $settings ),
			),
			$settings
		);
	}

	public static function get_initial_messages( array $settings ): array {
		$messages = $settings['initial_messages'] ?? array();
		$messages = is_array( $messages ) ? $messages : array();
		$output   = array();

		foreach ( $messages as $message ) {
			if ( is_array( $message ) && ! empty( $message['enabled'] ) && ! empty( $message['text'] ) ) {
				$output[] = (string) $message['text'];
			}
		}

		return $output ?: array( (string) $settings['welcome_message'] );
	}

	public static function get_contact_fallback( array $settings ): array {
		return array(
			'whatsappEnabled' => (bool) $settings['enable_whatsapp_fallback'],
			'whatsappUrl'     => self::build_whatsapp_url( $settings ),
			'emailEnabled'    => (bool) $settings['enable_email_fallback'],
			'emailUrl'        => self::build_email_url( $settings ),
			'contactPageEnabled' => (bool) $settings['enable_contact_page_fallback'],
			'contactPageUrl'  => esc_url_raw( (string) $settings['contact_page_url'] ),
			'label'           => (string) $settings['human_support_button_label'],
		);
	}

	public static function build_whatsapp_url( array $settings ): string {
		$number = preg_replace( '/[^0-9]/', '', (string) $settings['whatsapp_number'] );
		if ( '' === $number ) {
			return '';
		}
		return 'https://wa.me/' . $number . '?text=' . rawurlencode( (string) $settings['whatsapp_default_message'] );
	}

	public static function build_email_url( array $settings ): string {
		$email = sanitize_email( (string) $settings['contact_email'] );
		if ( '' === $email ) {
			return '';
		}
		return 'mailto:' . $email . '?subject=' . rawurlencode( (string) $settings['email_subject'] );
	}

	public static function build_i18n( array $settings ): array {
		return array(
			'en' => array(
				'title'             => (string) $settings['bot_name'],
				'subtitle'          => (string) $settings['bot_subtitle'],
				'footer'            => (string) $settings['follow_up_privacy_text'],
				'getStarted'        => (string) $settings['start_conversation_label'],
				'inputPlaceholder'  => (string) $settings['input_placeholder'],
				'closeButtonTooltip'=> (string) $settings['close_button_label'],
				'retryButton'       => (string) $settings['retry_button_text'],
			),
		);
	}

	public static function build_metadata( array $settings ): array {
		$metadata = array();
		$flags    = is_array( $settings['metadata_fields'] ?? array() ) ? $settings['metadata_fields'] : array();

		if ( ! empty( $flags['page_title'] ) ) {
			$metadata['pageTitle'] = wp_get_document_title();
		}
		if ( ! empty( $flags['page_url'] ) ) {
			$metadata['pageUrl'] = self::current_url_without_unknown_query();
		}
		if ( ! empty( $flags['page_path'] ) ) {
			$metadata['pagePath'] = wp_parse_url( home_url( add_query_arg( array(), wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) ), PHP_URL_PATH );
		}
		if ( ! empty( $flags['utm_source'] ) ) {
			$metadata['utmSource'] = isset( $_GET['utm_source'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_source'] ) ) : '';
		}
		if ( ! empty( $flags['utm_medium'] ) ) {
			$metadata['utmMedium'] = isset( $_GET['utm_medium'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_medium'] ) ) : '';
		}
		if ( ! empty( $flags['utm_campaign'] ) ) {
			$metadata['utmCampaign'] = isset( $_GET['utm_campaign'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_campaign'] ) ) : '';
		}
		if ( ! empty( $flags['utm_content'] ) ) {
			$metadata['utmContent'] = isset( $_GET['utm_content'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_content'] ) ) : '';
		}
		if ( ! empty( $flags['industry'] ) ) {
			$metadata['industry'] = isset( $_GET['industry'] ) ? sanitize_text_field( wp_unslash( $_GET['industry'] ) ) : '';
		}
		if ( ! empty( $flags['post_id'] ) && is_singular() ) {
			$metadata['postId'] = get_queried_object_id();
		}
		if ( ! empty( $flags['post_type'] ) && is_singular() ) {
			$metadata['postType'] = get_post_type( get_queried_object_id() );
		}
		if ( ! empty( $flags['plugin_version'] ) ) {
			$metadata['pluginVersion'] = CTEN_VERSION;
		}
		if ( ! empty( $flags['theme_name'] ) ) {
			$theme = wp_get_theme();
			$metadata['themeName'] = $theme->exists() ? $theme->get( 'Name' ) : '';
		}

		return apply_filters( 'cten_public_metadata', array_filter( $metadata, static fn( $value ) => '' !== $value && null !== $value ), $settings );
	}

	public static function current_url_without_unknown_query(): string {
		$request_uri = (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' );
		$parts       = wp_parse_url( home_url( $request_uri ) );
		$path        = isset( $parts['path'] ) ? (string) $parts['path'] : '/';
		$allowed     = array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'industry' );
		$query       = array();

		foreach ( $allowed as $key ) {
			if ( isset( $_GET[ $key ] ) ) {
				$query[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
			}
		}

		return home_url( add_query_arg( $query, $path ) );
	}

	public static function get_css_variables( array $settings ): array {
		$preset = (string) $settings['theme_preset'];
		$vars   = array(
			'--chat--color--primary'             => $settings['primary_color'],
			'--chat--color--secondary'           => $settings['secondary_color'],
			'--cten-color-accent'                => $settings['accent_color'],
			'--chat--color--primary-shade-50'    => self::shade( $settings['primary_color'], -12 ),
			'--chat--color--primary--shade-100'   => self::shade( $settings['primary_color'], -20 ),
			'--chat--color-secondary-shade-50'    => self::shade( $settings['secondary_color'], -12 ),
			'--chat--color-light'                => '#ffffff',
			'--chat--color-light-shade-50'       => self::alpha( '#ffffff', 0.14 ),
			'--chat--color-light-shade-100'      => self::alpha( '#ffffff', 0.22 ),
			'--chat--color-medium'               => self::alpha( '#ffffff', 0.32 ),
			'--chat--color-dark'                 => $settings['text_color'],
			'--chat--color-disabled'             => self::alpha( $settings['muted_text_color'], 0.35 ),
			'--chat--color-typing'               => $settings['muted_text_color'],
			'--chat--spacing'                    => '1rem',
			'--chat--border-radius'              => $settings['border_radius'] . 'px',
			'--chat--window--width'              => $settings['desktop_width'] . 'px',
			'--chat--window--height'             => $settings['desktop_height'] . 'px',
			'--chat--header--background'         => $settings['header_background'],
			'--chat--header--color'              => $settings['text_color'],
			'--chat--heading--font-size'         => $settings['heading_font_size'] . 'px',
			'--chat--message--font-size'         => $settings['message_font_size'] . 'px',
			'--chat--message--bot--background'   => $settings['bot_message_background'],
			'--chat--message--bot--color'        => $settings['text_color'],
			'--chat--message--user--background'  => $settings['user_message_background'],
			'--chat--message--user--color'       => '#ffffff',
			'--chat--input--background'          => $settings['input_background'],
			'--chat--input--container--background'=> $settings['input_background'],
			'--chat--body--background'           => $settings['window_background'],
			'--chat--footer--background'         => $settings['footer_background'],
			'--chat--footer--color'              => $settings['muted_text_color'],
			'--cten-link-color'                  => $settings['link_color'],
			'--cten-error-color'                 => $settings['error_color'],
			'--cten-success-color'               => $settings['success_color'],
			'--cten-online-indicator-color'      => $settings['online_indicator_color'],
			'--chat--toggle--size'               => $settings['launcher_size'] . 'px',
		);

		$vars['--cten-glass-opacity'] = (string) $settings['glass_opacity'];
		$vars['--cten-blur-strength']  = $settings['blur_strength'] . 'px';
		$vars['--cten-shadow-strength']= $settings['shadow_strength'] . 'px';
		$vars['--cten-theme-preset']    = $preset;

		return apply_filters( 'cten_appearance_css_vars', $vars, $settings );
	}

	public static function shade( string $hex, int $percent ): string {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$rgb = array_map( 'hexdec', str_split( $hex, 2 ) );
		foreach ( $rgb as &$channel ) {
			$channel = max( 0, min( 255, (int) round( $channel + ( $percent / 100 ) * 255 ) ) );
		}
		return sprintf( '#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2] );
	}

	public static function alpha( string $color, float $alpha ): string {
		$color = sanitize_hex_color( $color ) ?: '#ffffff';
		$alpha = max( 0, min( 1, $alpha ) );
		$hex   = ltrim( $color, '#' );
		return sprintf(
			'rgba(%d,%d,%d,%.2f)',
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
			$alpha
		);
	}

	public static function allows_display( array $settings ): bool {
		if ( Safe_Mode::should_block_public_chat() ) {
			return false;
		}
		$settings = Profiles::resolve( $settings );
		$allowed = (bool) $settings['enabled'] && ! empty( $settings['webhook_url'] );
		$allowed = (bool) apply_filters( 'cten_chat_should_display', $allowed, $settings );
		if ( ! $allowed ) {
			return false;
		}

		$visibility = $settings['visibility'];
		$scope      = $visibility['scope'] ?? 'entire_site';
		$show       = true;

		switch ( $scope ) {
			case 'homepage_only':
				$show = is_front_page() || is_home();
				break;
			case 'selected_pages':
				$show = is_page( $visibility['selected_pages'] );
				break;
			case 'excluded_pages':
				$show = ! is_page( $visibility['excluded_pages'] );
				break;
			case 'selected_post_types':
				$show = is_singular( $visibility['selected_types'] );
				break;
			case 'entire_site':
			default:
				$show = true;
		}

		if ( 'logged_in' === $visibility['auth'] && ! is_user_logged_in() ) {
			$show = false;
		}

		if ( 'logged_out' === $visibility['auth'] && is_user_logged_in() ) {
			$show = false;
		}

		return (bool) apply_filters( 'cten_should_render_chat', $show, $settings );
	}
}
