<?php
/**
 * Admin runtime lab and reports.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Runtime_Lab {
	public const QUERY_TOKEN = 'cten_rt';
	public const TEST_OPTION = 'cten_runtime_test_state';
	public const EVENTS_OPTION = 'cten_runtime_test_events';

	public static function hooks(): void {
		add_action( 'admin_post_cten_runtime_lab_action', array( __CLASS__, 'handle_action' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
		add_action( 'init', array( __CLASS__, 'maybe_capture_runtime_test_context' ) );
		add_action( 'init', array( __CLASS__, 'maybe_schedule_cleanup' ) );
		add_action( 'cten_runtime_lab_cleanup', array( __CLASS__, 'cleanup_hook' ) );
	}

	public static function menu(): void {
		add_submenu_page(
			'cten-dashboard',
			__( 'Runtime Lab', 'chat-trigger-embed-for-n8n' ),
			__( 'Runtime Lab', 'chat-trigger-embed-for-n8n' ),
			'manage_options',
			'cten-runtime-lab',
			array( __CLASS__, 'render' )
		);
	}

	public static function enqueue(): void {
		if ( ! isset( $_GET['page'] ) || 'cten-runtime-lab' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		Assets::register();
		Assets::enqueue_admin();
		wp_localize_script(
			'cten-admin',
			'ctenRuntimeLab',
			array(
				'restUrl'   => rest_url( 'cten/v1' ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	public static function render(): void {
		$settings = Helpers::get_settings();
		$results  = Self_Test_Runner::report();
		$state    = self::get_test_state();
		include CTEN_DIR . 'admin/views/header.php';
		include CTEN_DIR . 'admin/views/runtime-lab.php';
		include CTEN_DIR . 'admin/views/footer.php';
	}

	public static function handle_action(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'chat-trigger-embed-for-n8n' ) );
		}

		check_admin_referer( 'cten_runtime_lab_action', 'cten_runtime_lab_nonce' );
		$action = sanitize_key( (string) ( $_POST['cten_runtime_lab_action_name'] ?? '' ) );
		$notice = '';

		switch ( $action ) {
			case 'save_safe_mode':
				Safe_Mode::save(
					array(
						'enabled'              => ! empty( $_POST['safe_mode_enabled'] ),
						'reason'               => (string) ( $_POST['safe_mode_reason'] ?? '' ),
						'auto_enable'          => ! empty( $_POST['safe_mode_auto_enable'] ),
						'failure_threshold'    => absint( $_POST['safe_mode_failure_threshold'] ?? 3 ),
						'recovery_instructions' => (string) ( $_POST['safe_mode_recovery_instructions'] ?? '' ),
						'fallback_link'        => (string) ( $_POST['safe_mode_fallback_link'] ?? '' ),
					)
				);
				$notice = __( 'Safe mode settings saved.', 'chat-trigger-embed-for-n8n' );
				break;
			case 'enable_safe_mode':
				Safe_Mode::enable( sanitize_text_field( (string) ( $_POST['safe_mode_reason'] ?? __( 'Administrator enabled safe mode from Runtime Lab.', 'chat-trigger-embed-for-n8n' ) ) ) );
				$notice = __( 'Safe mode enabled.', 'chat-trigger-embed-for-n8n' );
				break;
			case 'disable_safe_mode':
				Safe_Mode::disable();
				$notice = __( 'Safe mode disabled.', 'chat-trigger-embed-for-n8n' );
				break;
			case 'repair_cron':
				self::repair_cron();
				$notice = __( 'Cron events repaired.', 'chat-trigger-embed-for-n8n' );
				break;
			case 'run_migrations':
				Migrations::maybe_run();
				$notice = __( 'Pending migrations were checked.', 'chat-trigger-embed-for-n8n' );
				break;
			case 'clear_transients':
				Helpers::clear_temporary_notices();
				$notice = __( 'Plugin transients cleared.', 'chat-trigger-embed-for-n8n' );
				break;
			case 'generate_token':
				self::create_test_state( sanitize_text_field( (string) ( $_POST['runtime_test_page'] ?? '' ) ) );
				$notice = __( 'Runtime test token generated.', 'chat-trigger-embed-for-n8n' );
				break;
		}

		set_transient( 'cten_admin_message', $notice, 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=cten-runtime-lab' ) );
		exit;
	}

	public static function register_rest_routes(): void {
		register_rest_route(
			'cten/v1',
			'/runtime-lab/run',
			array(
				'methods'             => 'POST',
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'callback'            => array( __CLASS__, 'rest_run_tests' ),
			)
		);
		register_rest_route(
			'cten/v1',
			'/runtime-lab/report',
			array(
				'methods'             => 'GET',
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'callback'            => array( __CLASS__, 'rest_report' ),
			)
		);
		register_rest_route(
			'cten/v1',
			'/runtime-lab/mock',
			array(
				'methods'             => 'POST',
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'callback'            => array( __CLASS__, 'rest_mock_chat' ),
			)
		);
		register_rest_route(
			'cten/v1',
			'/runtime-lab/live-test',
			array(
				'methods'             => 'POST',
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'callback'            => array( __CLASS__, 'rest_live_test' ),
			)
		);
		register_rest_route(
			'cten/v1',
			'/runtime-lab/profile-simulate',
			array(
				'methods'             => 'POST',
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'callback'            => array( __CLASS__, 'rest_profile_simulate' ),
			)
		);
		register_rest_route(
			'cten/v1',
			'/runtime-lab/event',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'rest_event_beacon' ),
			)
		);
	}

	public static function rest_run_tests( \WP_REST_Request $request ): \WP_REST_Response {
		$category = (string) $request->get_param( 'category' );
		$context  = self::context_from_request( $request );
		$results  = '' !== $category ? Self_Test_Runner::run_category( $category, $context ) : Self_Test_Runner::run_all( $context );
		return rest_ensure_response( array( 'results' => $results ) );
	}

	public static function rest_report( \WP_REST_Request $request ): \WP_REST_Response {
		return rest_ensure_response( self::sanitized_report() );
	}

	public static function rest_mock_chat( \WP_REST_Request $request ): \WP_REST_Response {
		$scenario = sanitize_key( (string) $request->get_param( 'scenario' ) );
		$payload  = self::mock_response( $scenario );
		return rest_ensure_response( $payload );
	}

	public static function rest_live_test( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! filter_var( $request->get_param( 'confirm' ), FILTER_VALIDATE_BOOLEAN ) ) {
			return new \WP_REST_Response( array( 'status' => 'failed', 'safeErrorCategory' => 'confirmation_required' ), 400 );
		}

		$settings = Helpers::get_settings();
		$url      = Profiles::resolve( $settings )['webhook_url'] ?? '';
		$url      = Helpers::sanitize_url( (string) $url );
		if ( '' === $url || ! self::is_safe_webhook_url( $url ) ) {
			return new \WP_REST_Response( array( 'status' => 'failed', 'safeErrorCategory' => 'invalid_url' ), 400 );
		}

		$session_id = 'cten-live-' . wp_generate_password( 16, false, false );
		$body       = array(
			'action'     => 'sendMessage',
			'chatInput'  => 'Connection test from Chat Trigger Embed for n8n WordPress plugin admin.',
			'sessionId'  => $session_id,
			'metadata'   => array(
				'pluginVersion' => CTEN_VERSION,
				'profileId'     => (string) ( Profiles::resolve( $settings )['resolved_profile_id'] ?? '' ),
			),
		);

		$start = microtime( true );
		$response = wp_remote_post( $url, array(
			'timeout' => 10,
			'body'    => wp_json_encode( $body ),
			'headers' => array( 'Content-Type' => 'application/json' ),
		) );
		$duration = (int) round( ( microtime( true ) - $start ) * 1000 );

		if ( is_wp_error( $response ) ) {
			Safe_Mode::record_failure();
			return rest_ensure_response( array( 'status' => 'failed', 'safeErrorCategory' => 'network', 'durationMs' => $duration, 'message' => $response->get_error_message() ) );
		}

		update_option(
			'cten_last_live_test',
			array(
				'timestamp'        => gmdate( 'c' ),
				'status'           => (int) wp_remote_retrieve_response_code( $response ),
				'durationMs'       => $duration,
				'safeErrorCategory'=> 200 === (int) wp_remote_retrieve_response_code( $response ) ? '' : 'http_error',
				'profileId'        => (string) ( Profiles::resolve( $settings )['resolved_profile_id'] ?? '' ),
			),
			false
		);

		$result = array(
			'status'      => 200 === (int) wp_remote_retrieve_response_code( $response ) ? 'passed' : 'warning',
			'durationMs'  => $duration,
			'httpStatus'  => (int) wp_remote_retrieve_response_code( $response ),
			'safeErrorCategory' => 200 === (int) wp_remote_retrieve_response_code( $response ) ? '' : 'http_error',
		);

		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			Safe_Mode::record_failure();
		}

		return rest_ensure_response( $result );
	}

	public static function rest_profile_simulate( \WP_REST_Request $request ): \WP_REST_Response {
		$settings = Helpers::get_settings();
		$context  = self::context_from_request( $request );
		$resolved = Profiles::resolve( $settings, $context );
		return rest_ensure_response(
			array(
				'selectedProfile'     => array(
					'id'            => (string) ( $resolved['resolved_profile_id'] ?? '' ),
					'name'          => (string) ( $resolved['resolved_profile_name'] ?? '' ),
					'botName'       => (string) ( $resolved['bot_name'] ?? '' ),
					'welcomeMessage' => (string) ( $resolved['welcome_message'] ?? '' ),
					'quickActions'   => count( array_filter( (array) ( $resolved['quick_actions'] ?? array() ), static fn( array $item ): bool => ! empty( $item['enabled'] ) ) ),
					'theme'         => (string) ( $resolved['theme_preset'] ?? '' ),
					'launcherLabel' => (string) ( $resolved['launcher_label'] ?? '' ),
					'metadata'      => (array) ( $resolved['metadata_fields'] ?? array() ),
					'webhookStatus' => ! empty( $resolved['webhook_url'] ) ? 'configured' : 'missing',
				),
				'matchingRules'       => Profiles::resolution_report( $settings, $context ),
				'finalConfiguration'   => Settings::get_public_config( $resolved ),
				'context'             => array(
					'pageUrl'  => (string) ( $context['page_url'] ?? '' ),
					'pagePath'  => (string) ( $context['page_path'] ?? '' ),
					'campaign'  => (string) ( $context['campaign'] ?? '' ),
					'industry'  => (string) ( $context['industry'] ?? '' ),
				),
			)
		);
	}

	public static function rest_event_beacon( \WP_REST_Request $request ): \WP_REST_Response {
		$token = sanitize_text_field( (string) $request->get_param( 'token' ) );
		$state = self::get_test_state();
		if ( empty( $state['enabled'] ) || empty( $state['token'] ) || ! hash_equals( (string) $state['token'], $token ) || self::is_test_expired( $state ) ) {
			return new \WP_REST_Response( array( 'status' => 'rejected' ), 403 );
		}

		$event = self::sanitize_event_name( (string) $request->get_param( 'event' ) );
		if ( '' === $event ) {
			return new \WP_REST_Response( array( 'status' => 'invalid_event' ), 400 );
		}

		$events = get_option( self::EVENTS_OPTION, array() );
		$events[] = array(
			'event'     => $event,
			'timestamp' => gmdate( 'c' ),
		);
		update_option( self::EVENTS_OPTION, array_slice( $events, -250 ), false );
		if ( 'Error Displayed' === $event ) {
			Safe_Mode::record_failure();
		}
		if ( 'Runtime Initialized' === $event ) {
			Safe_Mode::clear_failure_state();
		}
		return rest_ensure_response( array( 'status' => 'ok' ) );
	}

	public static function can_manage(): bool {
		return current_user_can( 'manage_options' ) && wp_verify_nonce( sanitize_text_field( (string) ( $_SERVER['HTTP_X_WP_NONCE'] ?? $_REQUEST['_wpnonce'] ?? '' ) ), 'wp_rest' );
	}

	public static function sanitized_report(): array {
		$settings = Helpers::get_settings();
		$report = array(
			'reportSchemaVersion' => '1.0',
			'pluginVersion'       => CTEN_VERSION,
			'databaseVersion'     => Migrations::installed_version(),
			'wordpressVersion'    => get_bloginfo( 'version' ),
			'phpVersion'          => PHP_VERSION,
			'theme'               => wp_get_theme()->exists() ? wp_get_theme()->get( 'Name' ) : '',
			'https'               => is_ssl(),
			'timezone'            => wp_timezone_string(),
			'testResults'         => Self_Test_Runner::run_all(),
			'configurationWarnings' => self::configuration_warnings( $settings ),
			'profileResolutionTest' => Profiles::resolution_report( $settings ),
			'mockTestSummary'     => self::mock_summary(),
			'liveTestSummary'     => get_option( 'cten_last_live_test', array() ),
			'analyticsTestSummary' => Analytics::summary(),
			'cronSummary'         => self::cron_summary(),
			'assetSummary'        => self::asset_summary(),
			'safeMode'            => Safe_Mode::get(),
			'runtimeEvents'       => self::runtime_events_summary(),
			'unverifiedTests'     => array(
				'realWordPressRuntime',
				'realN8n',
				'redis',
				'browserResponsiveness',
			),
		);

		return self::mask_report( $report );
	}

	public static function mask_report( array $report ): array {
		$report['configurationWarnings'] = array_map( static fn( string $warning ): string => sanitize_text_field( $warning ), (array) ( $report['configurationWarnings'] ?? array() ) );
		return $report;
	}

	public static function create_test_state( string $page_url = '' ): array {
		$token = wp_generate_password( 16, false, false );
		$state = array(
			'enabled'    => true,
			'token'      => $token,
			'pageUrl'    => esc_url_raw( $page_url ),
			'expiresAt'  => gmdate( 'c', time() + 30 * MINUTE_IN_SECONDS ),
		);
		update_option( self::TEST_OPTION, $state, false );
		return $state;
	}

	public static function get_test_state(): array {
		$state = get_option( self::TEST_OPTION, array() );
		return is_array( $state ) ? $state : array();
	}

	public static function is_test_expired( array $state ): bool {
		$expires = strtotime( (string) ( $state['expiresAt'] ?? '' ) );
		return ! $expires || $expires < time();
	}

	public static function sanitize_event_name( string $event ): string {
		$allowed = array(
			'Controller Loaded',
			'Vendor Runtime Requested',
			'Vendor Runtime Loaded',
			'Runtime Initialized',
			'Launcher Rendered',
			'Chat Opened',
			'Chat Closed',
			'Message Submitted',
			'Assistant Response Rendered',
			'Quick Action Clicked',
			'Dynamic Option Rendered',
			'Dynamic Option Clicked',
			'Error Displayed',
			'Retry Clicked',
			'Session Restored',
		);
		$event = sanitize_text_field( $event );
		return in_array( $event, $allowed, true ) ? $event : '';
	}

	public static function maybe_capture_runtime_test_context(): void {
		$token = sanitize_text_field( (string) ( $_GET[ self::QUERY_TOKEN ] ?? '' ) );
		if ( '' === $token ) {
			return;
		}

		$state = self::get_test_state();
		if ( empty( $state['enabled'] ) || empty( $state['token'] ) || ! hash_equals( (string) $state['token'], $token ) || self::is_test_expired( $state ) ) {
			return;
		}

		add_filter(
			'cten_public_chat_config',
			static function ( array $config ) use ( $state ): array {
				$config['runtimeTest'] = array(
					'enabled'    => true,
					'token'      => (string) $state['token'],
					'endpoint'   => rest_url( 'cten/v1/runtime-lab/event' ),
					'expiresAt'  => (string) ( $state['expiresAt'] ?? '' ),
				);
				return $config;
			}
		);
	}

	public static function mock_response( string $scenario ): array {
		switch ( $scenario ) {
			case 'delayed_success':
				return array( 'status' => 'ok', 'response' => array( 'text' => 'Delayed success.' ), 'delayMs' => 1500 );
			case 'dynamic_options':
				return array( 'status' => 'ok', 'response' => array( 'text' => "Choose one:\n[[OPTION:Customer Support]]\n[[OPTION:Lead Collection]]" ) );
			case 'lead_status_hot':
				return array( 'status' => 'ok', 'response' => array( 'text' => 'Priority lead detected. [[LEAD_STATUS:hot]]' ) );
			case 'empty_response':
				return array( 'status' => 'ok', 'response' => array() );
			case 'invalid_response':
				return array( 'status' => 'ok', 'response' => '{invalid' );
			case 'rate_limited':
				return new \WP_REST_Response( array( 'status' => 'rate_limited' ), 429 );
			case 'server_error':
				return new \WP_REST_Response( array( 'status' => 'server_error' ), 500 );
			case 'timeout':
				return array( 'status' => 'timeout' );
			case 'previous_history':
				return array( 'status' => 'ok', 'response' => array( 'data' => array( array( 'role' => 'assistant', 'content' => 'Previous history restored.' ) ) ) );
			case 'expired_session':
				return array( 'status' => 'ok', 'response' => array( 'text' => 'Session expired, please start again.' ) );
			case 'success':
			default:
				return array( 'status' => 'ok', 'response' => array( 'text' => 'Mock response received.' ) );
		}
	}

	private static function configuration_warnings( array $settings ): array {
		$warnings = array();
		if ( empty( $settings['enabled'] ) ) {
			$warnings[] = __( 'Chatbot is currently disabled.', 'chat-trigger-embed-for-n8n' );
		}
		if ( empty( $settings['webhook_url'] ) ) {
			$warnings[] = __( 'No production webhook URL is configured.', 'chat-trigger-embed-for-n8n' );
		}
		if ( Safe_Mode::is_enabled() ) {
			$warnings[] = __( 'Safe mode is active.', 'chat-trigger-embed-for-n8n' );
		}
		return $warnings;
	}

	private static function mock_summary(): array {
		return array(
			'status' => 'available',
			'scenarios' => array( 'success', 'delayed_success', 'dynamic_options', 'lead_status_hot', 'empty_response', 'invalid_response', 'rate_limited', 'server_error', 'timeout', 'previous_history', 'expired_session' ),
		);
	}

	private static function cron_summary(): array {
		$hooks = array( 'cten_analytics_cleanup', 'cten_runtime_lab_cleanup', 'cten_migration_recovery' );
		$out = array();
		foreach ( $hooks as $hook ) {
			$out[] = array(
				'hook'     => $hook,
				'next'     => wp_next_scheduled( $hook ) ? gmdate( 'c', (int) wp_next_scheduled( $hook ) ) : '',
				'recurrence'=> wp_get_schedule( $hook ) ?: '',
				'status'   => wp_next_scheduled( $hook ) ? 'scheduled' : 'missing',
			);
		}
		return $out;
	}

	private static function asset_summary(): array {
		return array(
			'mainJs' => filesize( Helpers::asset_path( 'dist/chat-trigger-embed.js' ) ) ?: 0,
			'mainCss' => filesize( Helpers::asset_path( 'dist/chat-trigger-embed.css' ) ) ?: 0,
			'vendorJs' => filesize( Helpers::asset_path( 'dist/vendor/n8n-chat/chat.bundle.es.js' ) ) ?: 0,
		);
	}

	private static function runtime_events_summary(): array {
		$events = get_option( self::EVENTS_OPTION, array() );
		$events = is_array( $events ) ? $events : array();
		return array_slice( $events, -50 );
	}

	private static function repair_cron(): void {
		$hooks = array( 'cten_analytics_cleanup' => 'daily', 'cten_runtime_lab_cleanup' => 'hourly' );
		foreach ( $hooks as $hook => $recurrence ) {
			if ( ! wp_next_scheduled( $hook ) ) {
				wp_schedule_event( time() + MINUTE_IN_SECONDS, $recurrence, $hook );
			}
		}
	}

	public static function cleanup_hook(): void {
		$state = self::get_test_state();
		if ( ! empty( $state['expiresAt'] ) && self::is_test_expired( $state ) ) {
			delete_option( self::TEST_OPTION );
			delete_option( self::EVENTS_OPTION );
		}
	}

	public static function maybe_schedule_cleanup(): void {
		if ( ! wp_next_scheduled( 'cten_runtime_lab_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'cten_runtime_lab_cleanup' );
		}
	}

	private static function context_from_request( \WP_REST_Request $request ): array {
		return array(
			'page_url'    => esc_url_raw( (string) $request->get_param( 'pageUrl' ) ),
			'page_path'   => sanitize_text_field( (string) $request->get_param( 'pagePath' ) ),
			'post_id'     => absint( $request->get_param( 'postId' ) ),
			'post_type'   => sanitize_key( (string) $request->get_param( 'postType' ) ),
			'category'    => sanitize_text_field( (string) $request->get_param( 'category' ) ),
			'tag'         => sanitize_text_field( (string) $request->get_param( 'tag' ) ),
			'referrer'    => esc_url_raw( (string) $request->get_param( 'referrer' ) ),
			'device'      => sanitize_key( (string) $request->get_param( 'device' ) ),
			'logged_in'   => filter_var( $request->get_param( 'loggedIn' ), FILTER_VALIDATE_BOOLEAN ),
			'user_role'   => sanitize_key( (string) $request->get_param( 'userRole' ) ),
			'utm_source'  => sanitize_text_field( (string) $request->get_param( 'utmSource' ) ),
			'utm_medium'  => sanitize_text_field( (string) $request->get_param( 'utmMedium' ) ),
			'utm_campaign'=> sanitize_text_field( (string) $request->get_param( 'utmCampaign' ) ),
			'utm_content' => sanitize_text_field( (string) $request->get_param( 'utmContent' ) ),
			'industry'    => sanitize_text_field( (string) $request->get_param( 'industry' ) ),
			'campaign'    => sanitize_text_field( (string) $request->get_param( 'campaign' ) ),
		);
	}

	private static function is_safe_webhook_url( string $url ): bool {
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		$host   = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		if ( ! in_array( $scheme, array( 'https', 'http' ), true ) ) {
			return false;
		}
		if ( in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
			return defined( 'WP_DEBUG' ) && WP_DEBUG;
		}
		if ( preg_match( '/^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|169\.254\.|0\.0\.0\.0|127\.)/', $host ) ) {
			return false;
		}
		return true;
	}
}
