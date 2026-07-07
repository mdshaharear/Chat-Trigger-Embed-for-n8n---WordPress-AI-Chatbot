<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/helpers.php';

$notice = get_transient( 'cten_admin_message' );
$error  = get_transient( 'cten_admin_error' );
$migration_error = get_transient( 'cten_migration_error' );
$native_connection_notice = get_transient( 'cten_v2_connection_notice' );
$native_chatbot_notice = get_transient( 'cten_v2_chatbot_notice' );
delete_transient( 'cten_admin_message' );
delete_transient( 'cten_admin_error' );
delete_transient( 'cten_v2_connection_notice' );
delete_transient( 'cten_v2_chatbot_notice' );
$admin_theme = \ChatTriggerEmbedN8n\Admin_Theme::current_mode();

$pages = array(
	'cten-dashboard'     => __( 'Dashboard', 'chat-trigger-embed-for-n8n' ),
	'cten-chatbots'      => __( 'Chatbots', 'chat-trigger-embed-for-n8n' ),
	'cten-ai-providers'  => __( 'AI Providers', 'chat-trigger-embed-for-n8n' ),
	'cten-n8n-actions'   => __( 'n8n Actions', 'chat-trigger-embed-for-n8n' ),
	'cten-conversations' => __( 'Conversations', 'chat-trigger-embed-for-n8n' ),
	'cten-leads'         => __( 'Leads', 'chat-trigger-embed-for-n8n' ),
	'cten-usage'         => __( 'Usage', 'chat-trigger-embed-for-n8n' ),
	'cten-templates'     => __( 'Templates', 'chat-trigger-embed-for-n8n' ),
	'cten-appearance'    => __( 'Appearance', 'chat-trigger-embed-for-n8n' ),
	'cten-analytics'     => __( 'Analytics', 'chat-trigger-embed-for-n8n' ),
	'cten-runtime-lab'   => __( 'Runtime Lab', 'chat-trigger-embed-for-n8n' ),
	'cten-diagnostics'   => __( 'Diagnostics', 'chat-trigger-embed-for-n8n' ),
	'cten-settings'      => __( 'Settings', 'chat-trigger-embed-for-n8n' ),
	'cten-tools'         => __( 'Tools', 'chat-trigger-embed-for-n8n' ),
	'cten-legacy-n8n'    => __( 'Legacy n8n', 'chat-trigger-embed-for-n8n' ),
);
?>
<div class="wrap cten-admin">
	<div class="cten-topbar">
		<div>
			<h1><?php esc_html_e( 'Chat Trigger Embed for n8n', 'chat-trigger-embed-for-n8n' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Independent third-party WordPress integration for the official n8n Chat Trigger.', 'chat-trigger-embed-for-n8n' ); ?></p>
		</div>
		<form class="cten-theme-switcher" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'cten_save_admin_theme', 'cten_admin_theme_nonce' ); ?>
			<input type="hidden" name="action" value="cten_save_admin_theme">
			<label class="screen-reader-text" for="cten-admin-theme-mode"><?php esc_html_e( 'Admin theme mode', 'chat-trigger-embed-for-n8n' ); ?></label>
			<select id="cten-admin-theme-mode" name="admin_theme_mode" onchange="this.form.submit()">
				<option value="system" <?php selected( $admin_theme, 'system' ); ?>><?php esc_html_e( 'System', 'chat-trigger-embed-for-n8n' ); ?></option>
				<option value="light" <?php selected( $admin_theme, 'light' ); ?>><?php esc_html_e( 'Light', 'chat-trigger-embed-for-n8n' ); ?></option>
				<option value="dark" <?php selected( $admin_theme, 'dark' ); ?>><?php esc_html_e( 'Dark', 'chat-trigger-embed-for-n8n' ); ?></option>
			</select>
		</form>
	</div>
	<?php if ( $notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
	<?php endif; ?>
	<?php if ( $native_connection_notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $native_connection_notice ); ?></p></div>
	<?php endif; ?>
	<?php if ( $native_chatbot_notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $native_chatbot_notice ); ?></p></div>
	<?php endif; ?>
	<?php if ( $error ) : ?>
		<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
	<?php endif; ?>
	<?php if ( $migration_error ) : ?>
		<div class="notice notice-error"><p><?php echo esc_html__( 'A plugin migration did not complete:', 'chat-trigger-embed-for-n8n' ); ?> <?php echo esc_html( $migration_error ); ?></p></div>
	<?php endif; ?>
	<nav class="cten-nav">
		<?php foreach ( $pages as $slug => $title ) : ?>
			<a class="cten-nav__link <?php echo esc_attr( $page === $slug ? 'is-active' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $slug ) ); ?>"><?php echo esc_html( $title ); ?></a>
		<?php endforeach; ?>
	</nav>
	<div class="cten-start-card">
		<div>
			<strong><?php esc_html_e( 'New here?', 'chat-trigger-embed-for-n8n' ); ?></strong>
			<p><?php esc_html_e( 'Start with the Connection page, copy your site origin, switch to Elementor Widget or Global Footer, then enable the chatbot.', 'chat-trigger-embed-for-n8n' ); ?></p>
		</div>
		<p class="cten-start-card__actions">
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-settings' ) ); ?>"><?php esc_html_e( 'Open Connection', 'chat-trigger-embed-for-n8n' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-onboarding' ) ); ?>"><?php esc_html_e( 'Open Wizard', 'chat-trigger-embed-for-n8n' ); ?></a>
		</p>
	</div>
	<div class="cten-page">
