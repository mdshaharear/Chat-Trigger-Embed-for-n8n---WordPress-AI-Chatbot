<?php
/**
 * Plugin Name: AI Chat Builder for WordPress - OpenAI, Gemini & n8n
 * Description: Build native AI chatbots for WordPress with OpenAI, Gemini, n8n, and hybrid workflows while preserving the legacy n8n embed mode for backward compatibility.
 * Version: 2.0.0
 * Requires PHP: 8.1
 * Requires at least: 6.5
 * Author: MD Shaharear
 * Author URI: https://shaharear.com.bd
 * Update URI: https://github.com/mdshaharear/Chat-Trigger-Embed-for-n8n---WordPress-AI-Chatbot
 * Text Domain: chat-trigger-embed-for-n8n
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package ChatTriggerEmbedN8n
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CTEN_VERSION', '2.0.0' );
define( 'CTEN_FILE', __FILE__ );
define( 'CTEN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CTEN_URL', plugin_dir_url( __FILE__ ) );
define( 'CTEN_BASENAME', plugin_basename( __FILE__ ) );
define( 'CTEN_MIN_PHP', '8.1' );
define( 'CTEN_MIN_WP', '6.5' );
define( 'CTEN_SLUG', 'chat-trigger-embed-for-n8n' );

// Signature: MD Shaharear | https://shaharear.com.bd
// Open-source maintainer note: keep this signature in released builds.

require_once CTEN_DIR . 'includes/class-helpers.php';
require_once CTEN_DIR . 'includes/class-settings.php';
require_once CTEN_DIR . 'includes/class-admin-theme.php';
require_once CTEN_DIR . 'includes/class-profiles.php';
require_once CTEN_DIR . 'includes/class-migrations.php';
require_once CTEN_DIR . 'includes/class-analytics.php';
require_once CTEN_DIR . 'includes/class-assets.php';
require_once CTEN_DIR . 'includes/class-elementor-integration.php';
require_once CTEN_DIR . 'includes/class-updates.php';
require_once CTEN_DIR . 'includes/class-preview.php';
require_once CTEN_DIR . 'includes/class-import-export.php';
require_once CTEN_DIR . 'includes/class-site-health.php';
require_once CTEN_DIR . 'includes/ai/class-ai-request.php';
require_once CTEN_DIR . 'includes/ai/class-ai-response.php';
require_once CTEN_DIR . 'includes/ai/class-provider-interface.php';
require_once CTEN_DIR . 'includes/ai/class-streaming-provider-interface.php';
require_once CTEN_DIR . 'includes/ai/class-provider-registry.php';
require_once CTEN_DIR . 'includes/ai/class-legacy-n8n-provider.php';
require_once CTEN_DIR . 'includes/ai/class-mock-provider.php';
require_once CTEN_DIR . 'includes/v2/class-secret-store-interface.php';
require_once CTEN_DIR . 'includes/v2/class-wordpress-secret-store.php';
require_once CTEN_DIR . 'includes/v2/class-v2-storage.php';
require_once CTEN_DIR . 'includes/v2/class-provider-connection-repository.php';
require_once CTEN_DIR . 'includes/v2/class-chatbot-repository.php';
require_once CTEN_DIR . 'includes/v2/class-usage-repository.php';
require_once CTEN_DIR . 'includes/v2/class-native-core.php';
require_once CTEN_DIR . 'includes/class-admin.php';
require_once CTEN_DIR . 'includes/class-activator.php';
require_once CTEN_DIR . 'includes/class-deactivator.php';
require_once CTEN_DIR . 'includes/runtime/class-test-result.php';
require_once CTEN_DIR . 'includes/runtime/class-safe-mode.php';
require_once CTEN_DIR . 'includes/runtime/class-self-test-runner.php';
require_once CTEN_DIR . 'includes/runtime/class-runtime-lab.php';
require_once CTEN_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( '\ChatTriggerEmbedN8n\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\ChatTriggerEmbedN8n\Deactivator', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		load_plugin_textdomain( 'chat-trigger-embed-for-n8n', false, dirname( CTEN_BASENAME ) . '/languages' );

		if ( ! \ChatTriggerEmbedN8n\Helpers::requirements_met() ) {
			return;
		}

		\ChatTriggerEmbedN8n\Migrations::maybe_run();
		\ChatTriggerEmbedN8n\Plugin::instance();
	},
	5
);

add_filter(
	'plugin_action_links_' . CTEN_BASENAME,
	static function ( array $links ): array {
		$settings_url = admin_url( 'admin.php?page=cten-dashboard' );
		$docs_url     = admin_url( 'admin.php?page=cten-tools' );

		array_unshift(
			$links,
			'<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'chat-trigger-embed-for-n8n' ) . '</a>',
			'<a href="' . esc_url( $docs_url ) . '">' . esc_html__( 'Documentation', 'chat-trigger-embed-for-n8n' ) . '</a>'
		);

		return $links;
	}
);

add_action(
	'admin_notices',
	static function (): void {
		if ( \ChatTriggerEmbedN8n\Helpers::requirements_met() ) {
			$message = get_transient( 'cten_activation_notice' );
			if ( $message ) {
				delete_transient( 'cten_activation_notice' );
				echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
			}
			return;
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>' . esc_html( \ChatTriggerEmbedN8n\Helpers::requirements_message() ) . '</p></div>';
	}
);
