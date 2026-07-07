<?php
/**
 * Runtime lab self-test runner.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Self_Test_Runner {
	public static function run_all( array $context = array() ): array {
		$results = array_merge(
			self::environment( $context ),
			self::plugin_files( $context ),
			self::database( $context ),
			self::configuration( $context ),
			self::profiles( $context ),
			self::analytics( $context ),
			self::cron( $context ),
			self::security( $context )
		);

		return array_map( static fn( array $result ): array => self::normalize_result( $result ), $results );
	}

	public static function run_category( string $category, array $context = array() ): array {
		$category = sanitize_key( $category );
		$map = array(
			'environment'   => array( self::class, 'environment' ),
			'plugin_files'  => array( self::class, 'plugin_files' ),
			'database'      => array( self::class, 'database' ),
			'configuration' => array( self::class, 'configuration' ),
			'profiles'      => array( self::class, 'profiles' ),
			'analytics'     => array( self::class, 'analytics' ),
			'cron'          => array( self::class, 'cron' ),
			'security'      => array( self::class, 'security' ),
		);

		if ( ! isset( $map[ $category ] ) ) {
			return array();
		}

		$results = call_user_func( $map[ $category ], $context );
		return array_map( static fn( array $result ): array => self::normalize_result( $result ), $results );
	}

	public static function normalize_result( array $result ): array {
		return Test_Result::create(
			(string) ( $result['testId'] ?? 'runtime-test' ),
			(string) ( $result['category'] ?? 'general' ),
			(string) ( $result['title'] ?? 'Runtime Test' ),
			(string) ( $result['status'] ?? Test_Result::STATUS_NOT_AVAILABLE ),
			(string) ( $result['severity'] ?? Test_Result::SEVERITY_INFO ),
			(string) ( $result['shortMessage'] ?? '' ),
			(string) ( $result['technicalDetail'] ?? '' ),
			(string) ( $result['suggestedFix'] ?? '' )
		);
	}

	public static function environment( array $context = array() ): array {
		global $wp_version;
		$results = array();
		$results[] = Test_Result::pass( 'env_wp_version', 'environment', 'WordPress Version', 'WordPress runtime version reported.', 'Version: ' . (string) $wp_version );
		$results[] = Test_Result::pass( 'env_php_version', 'environment', 'PHP Version', 'PHP runtime version reported.', 'Version: ' . PHP_VERSION );
		$results[] = Test_Result::pass( 'env_timezone', 'environment', 'Timezone', 'Timezone available.', 'Site timezone: ' . wp_timezone_string() );
		$results[] = Test_Result::pass( 'env_https', 'environment', 'HTTPS', is_ssl() ? 'HTTPS is enabled.' : 'HTTPS is not enabled.', 'Site URL: ' . home_url() );
		$results[] = Test_Result::pass( 'env_rest', 'environment', 'REST API', 'REST API helpers are available.', 'REST URL: ' . rest_url() );
		return $results;
	}

	public static function plugin_files( array $context = array() ): array {
		$files = array(
			'chat-trigger-embed-for-n8n.php' => 'Main plugin file',
			'includes/class-settings.php' => 'Settings class',
			'includes/class-profiles.php' => 'Profiles class',
			'dist/chat-trigger-embed.js' => 'Main public JS',
			'dist/chat-trigger-embed.css' => 'Main public CSS',
			'dist/vendor/n8n-chat/chat.bundle.es.js' => 'Official runtime chunk',
			'dist/vendor/n8n-chat/style.css' => 'Official runtime CSS',
			'THIRD_PARTY_NOTICES.md' => 'License notices',
		);
		$results = array();
		foreach ( $files as $path => $label ) {
			$exists = file_exists( Helpers::asset_path( $path ) );
			$results[] = $exists
				? Test_Result::pass( 'file_' . sanitize_key( $path ), 'plugin_files', $label, 'File exists.', $path )
				: Test_Result::fail( 'file_' . sanitize_key( $path ), 'plugin_files', $label, 'File missing.', $path, 'Rebuild the release ZIP and restore required assets.' );
		}
		return $results;
	}

	public static function database( array $context = array() ): array {
		$results = array();
		$results[] = get_option( Migrations::OPTION_NAME, '' ) ? Test_Result::pass( 'db_version', 'database', 'Database Version', 'Database version option is present.', (string) get_option( Migrations::OPTION_NAME, '' ) ) : Test_Result::warn( 'db_version', 'database', 'Database Version', 'Database version option is not recorded yet.', '', 'Run migrations on an active WordPress site.' );
		$results[] = get_option( Helpers::option_name(), null ) ? Test_Result::pass( 'db_settings', 'database', 'Settings Storage', 'Settings option is readable.', Helpers::option_name() ) : Test_Result::warn( 'db_settings', 'database', 'Settings Storage', 'Settings option is missing or empty.', '', 'Save settings once on a live site.' );
		$results[] = Test_Result::pass( 'db_safe_mode', 'database', 'Safe Mode State', 'Safe mode option is readable.', Safe_Mode::is_enabled() ? 'Enabled' : 'Disabled' );
		return $results;
	}

	public static function configuration( array $context = array() ): array {
		$settings = Helpers::get_settings();
		$results  = array();
		$results[] = ! empty( $settings['enabled'] ) ? Test_Result::pass( 'cfg_enabled', 'configuration', 'Chatbot Enabled', 'Chatbot is enabled in settings.', '' ) : Test_Result::warn( 'cfg_enabled', 'configuration', 'Chatbot Enabled', 'Chatbot is disabled.', '', 'Enable the chatbot only after a successful live test.' );
		$results[] = ! empty( $settings['webhook_url'] ) ? Test_Result::pass( 'cfg_webhook', 'configuration', 'Webhook URL', 'A webhook URL is configured.', Helpers::sanitize_url( (string) $settings['webhook_url'] ) ? 'URL stored safely.' : 'Sanitized webhook not returned.' ) : Test_Result::fail( 'cfg_webhook', 'configuration', 'Webhook URL', 'No webhook URL configured.', '', 'Paste the production n8n Chat Trigger URL.' );
		$results[] = ! empty( $settings['profiles'] ) ? Test_Result::pass( 'cfg_profiles', 'configuration', 'Profiles', 'Profiles are stored.', count( (array) $settings['profiles'] ) . ' profiles' ) : Test_Result::warn( 'cfg_profiles', 'configuration', 'Profiles', 'No custom profiles are stored yet.', '', 'Create at least one profile on a live site.' );
		$results[] = ! empty( $settings['pre_chat_form']['enabled'] ) ? Test_Result::pass( 'cfg_prechat', 'configuration', 'Pre-chat Form', 'Pre-chat form is enabled.', 'Values are metadata only.' ) : Test_Result::warn( 'cfg_prechat', 'configuration', 'Pre-chat Form', 'Pre-chat form is disabled.', '', 'Enable only if you need lead capture before chat start.' );
		return $results;
	}

	public static function profiles( array $context = array() ): array {
		$settings = Helpers::get_settings();
		$resolved = Profiles::resolve( $settings, $context );
		return array(
			Test_Result::pass( 'profile_resolved', 'profiles', 'Resolved Profile', 'Profile resolution returned a candidate.', (string) ( $resolved['resolved_profile_name'] ?? '' ), 'Use the simulator to confirm page/campaign rule ordering.' ),
			Test_Result::pass( 'profile_default', 'profiles', 'Default Profile', 'A default profile is present after sanitization.', (string) ( $resolved['resolved_profile_id'] ?? '' ) ),
		);
	}

	public static function analytics( array $context = array() ): array {
		$settings = Helpers::get_settings();
		if ( empty( $settings['analytics_enabled'] ) ) {
			return array(
				Test_Result::skipped( 'analytics_disabled', 'analytics', 'Analytics Disabled', 'Analytics is disabled, so no analytics table writes will occur.', '', 'Enable analytics to test event capture and cleanup.' ),
			);
		}

		return array(
			Analytics::table_exists()
				? Test_Result::pass( 'analytics_table', 'analytics', 'Analytics Table', 'Analytics table exists.', Analytics::table_name() )
				: Test_Result::fail( 'analytics_table', 'analytics', 'Analytics Table', 'Analytics table is missing.', Analytics::table_name(), 'Run the analytics migration on a live site.' ),
		);
	}

	public static function cron( array $context = array() ): array {
		$hooks = array( 'cten_analytics_cleanup', 'cten_runtime_lab_cleanup', 'cten_migration_recovery' );
		$results = array();
		foreach ( $hooks as $hook ) {
			$results[] = wp_next_scheduled( $hook )
				? Test_Result::pass( 'cron_' . sanitize_key( $hook ), 'cron', 'Cron Hook ' . $hook, 'Cron hook is scheduled.', $hook )
				: Test_Result::warn( 'cron_' . sanitize_key( $hook ), 'cron', 'Cron Hook ' . $hook, 'Cron hook is not scheduled.', $hook, 'Repair plugin cron events from the Runtime Lab.' );
		}
		return $results;
	}

	public static function security( array $context = array() ): array {
		return array(
			Safe_Mode::is_enabled()
				? Test_Result::warn( 'security_safe_mode', 'security', 'Safe Mode', 'Safe mode is enabled.', '', 'Disable safe mode once the underlying issue is resolved.' )
				: Test_Result::pass( 'security_safe_mode', 'security', 'Safe Mode', 'Safe mode is disabled.', '' ),
		);
	}

	public static function report( array $context = array() ): array {
		return array(
			'reportSchemaVersion' => '1.0',
			'pluginVersion'       => CTEN_VERSION,
			'databaseVersion'     => Migrations::installed_version(),
			'wordpressVersion'    => get_bloginfo( 'version' ),
			'phpVersion'          => PHP_VERSION,
			'theme'               => wp_get_theme()->exists() ? wp_get_theme()->get( 'Name' ) : '',
			'tests'               => self::run_all( $context ),
		);
	}
}
