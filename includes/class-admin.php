<?php
/**
 * Admin menus and pages.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Admin {
	public static function hooks(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function menu(): void {
		add_menu_page(
			__( 'AI Chat Builder', 'chat-trigger-embed-for-n8n' ),
			__( 'AI Chat Builder', 'chat-trigger-embed-for-n8n' ),
			'manage_options',
			'cten-dashboard',
			array( __CLASS__, 'dashboard' ),
			'dashicons-format-chat',
			58
		);

		$pages = array(
			'cten-dashboard'    => __( 'Dashboard', 'chat-trigger-embed-for-n8n' ),
			'cten-chatbots'     => __( 'Chatbots', 'chat-trigger-embed-for-n8n' ),
			'cten-ai-providers' => __( 'AI Providers', 'chat-trigger-embed-for-n8n' ),
			'cten-n8n-actions'  => __( 'n8n Actions', 'chat-trigger-embed-for-n8n' ),
			'cten-conversations'=> __( 'Conversations', 'chat-trigger-embed-for-n8n' ),
			'cten-leads'        => __( 'Leads', 'chat-trigger-embed-for-n8n' ),
			'cten-usage'        => __( 'Usage', 'chat-trigger-embed-for-n8n' ),
			'cten-templates'    => __( 'Templates', 'chat-trigger-embed-for-n8n' ),
			'cten-appearance'   => __( 'Appearance', 'chat-trigger-embed-for-n8n' ),
			'cten-analytics'    => __( 'Analytics', 'chat-trigger-embed-for-n8n' ),
			'cten-runtime-lab'  => __( 'Runtime Lab', 'chat-trigger-embed-for-n8n' ),
			'cten-diagnostics'  => __( 'Diagnostics', 'chat-trigger-embed-for-n8n' ),
			'cten-settings'     => __( 'Settings', 'chat-trigger-embed-for-n8n' ),
			'cten-tools'        => __( 'Tools', 'chat-trigger-embed-for-n8n' ),
			'cten-legacy-n8n'   => __( 'Legacy n8n', 'chat-trigger-embed-for-n8n' ),
		);

		foreach ( $pages as $slug => $title ) {
			add_submenu_page( 'cten-dashboard', $title, $title, 'manage_options', $slug, array( __CLASS__, 'render' ) );
		}
	}

	public static function enqueue(): void {
		Assets::register();
		Assets::enqueue_admin();
	}

	public static function dashboard(): void {
		self::render();
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'chat-trigger-embed-for-n8n' ) );
		}

		$settings = Helpers::get_settings();
		$page     = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'cten-dashboard';
		$current_page = $page;

		$file_map = array(
			'cten-dashboard'    => 'dashboard.php',
			'cten-chatbots'     => 'native-alpha.php',
			'cten-ai-providers' => 'native-alpha.php',
			'cten-n8n-actions'  => 'native-alpha.php',
			'cten-conversations'=> 'native-alpha.php',
			'cten-leads'        => 'native-alpha.php',
			'cten-usage'        => 'native-alpha.php',
			'cten-templates'    => 'native-alpha.php',
			'cten-appearance'   => 'appearance.php',
			'cten-analytics'    => 'analytics.php',
			'cten-runtime-lab'  => 'runtime-lab.php',
			'cten-diagnostics'  => 'diagnostics.php',
			'cten-settings'     => 'settings.php',
			'cten-tools'        => 'tools.php',
			'cten-legacy-n8n'   => 'legacy-n8n.php',
			'cten-connection'   => 'settings.php',
			'cten-onboarding'   => 'onboarding.php',
			'cten-profiles'     => 'profiles.php',
			'cten-launcher'     => 'launcher.php',
			'cten-messages'     => 'messages.php',
			'cten-quick-actions'=> 'quick-actions.php',
			'cten-behaviour'    => 'behaviour.php',
			'cten-visibility'   => 'visibility.php',
		);

		$file = $file_map[ $page ] ?? 'dashboard.php';
		include CTEN_DIR . 'admin/views/header.php';
		include CTEN_DIR . 'admin/views/' . $file;
		include CTEN_DIR . 'admin/views/footer.php';
	}
}
