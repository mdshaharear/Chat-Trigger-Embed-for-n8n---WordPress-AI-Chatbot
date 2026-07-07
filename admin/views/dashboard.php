<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$installed_version = '1.26.0';
$configured        = ! empty( $settings['webhook_url'] );
$enabled           = ! empty( $settings['enabled'] );
$display_position  = $settings['launcher_position'];
$devices           = \ChatTriggerEmbedN8n\Helpers::device_visibility_label( $settings );
$webhook_health    = \ChatTriggerEmbedN8n\Helpers::webhook_health( (string) $settings['webhook_url'] );
$completion        = \ChatTriggerEmbedN8n\Helpers::setup_completion( $settings );
?>
<div class="cten-grid">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Get Started Fast', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'For a smooth first setup, only configure Connection, choose a Display Mode, and then enable the chatbot.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<ol class="cten-stats">
			<li><?php esc_html_e( 'Open Settings and paste the production webhook URL.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Choose Global Footer, Elementor Widget, or Both.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Leave advanced options alone until the bot is working.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Test in an incognito browser before publishing.', 'chat-trigger-embed-for-n8n' ); ?></li>
		</ol>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-settings' ) ); ?>"><?php esc_html_e( 'Open Connection', 'chat-trigger-embed-for-n8n' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-onboarding' ) ); ?>"><?php esc_html_e( 'Open Wizard', 'chat-trigger-embed-for-n8n' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-tools' ) ); ?>"><?php esc_html_e( 'Reset / Export', 'chat-trigger-embed-for-n8n' ); ?></a>
		</p>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'AI Chat Builder Status', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<ul class="cten-stats">
			<li><strong><?php esc_html_e( 'Plugin version', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( CTEN_VERSION ); ?></li>
			<li><strong><?php esc_html_e( 'Installed @n8n/chat version', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $installed_version ); ?></li>
			<li><strong><?php esc_html_e( 'Chatbot', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $enabled ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Webhook configured', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $configured ? __( 'Yes', 'chat-trigger-embed-for-n8n' ) : __( 'No', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Production URL validation', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $webhook_health['label'] ); ?></li>
			<li><strong><?php esc_html_e( 'Display mode', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( ucwords( str_replace( '_', ' ', (string) $settings['render_mode'] ) ) ); ?></li>
			<li><strong><?php esc_html_e( 'Previous-session setting', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $settings['load_previous_session'] ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Streaming setting', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $settings['enable_streaming'] ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Device visibility status', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $devices ); ?></li>
			<li><strong><?php esc_html_e( 'Current appearance preset', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( (string) $settings['theme_preset'] ); ?></li>
			<li><strong><?php esc_html_e( 'Setup completion', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $completion . '%' ); ?></li>
		</ul>
		<p class="description"><?php esc_html_e( 'The plugin keeps the public bot off until you configure the webhook and enable it yourself.', 'chat-trigger-embed-for-n8n' ); ?></p>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Recommended Defaults', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<ul class="cten-checklist">
			<li class="<?php echo esc_attr( ! empty( $settings['theme_mode'] ) ? 'is-done' : 'is-pending' ); ?>"><?php esc_html_e( 'Theme mode is available for light, dark, or system.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li class="<?php echo esc_attr( ! empty( $settings['render_mode'] ) ? 'is-done' : 'is-pending' ); ?>"><?php esc_html_e( 'Display mode supports footer, Elementor widget, or both.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li class="<?php echo esc_attr( ! empty( $settings['load_previous_session'] ) ? 'is-done' : 'is-pending' ); ?>"><?php esc_html_e( 'Previous session loading stays off unless you need it.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li class="<?php echo esc_attr( ! empty( $settings['quick_actions'] ) ? 'is-done' : 'is-pending' ); ?>"><?php esc_html_e( 'Quick actions are prefilled for first-time users.', 'chat-trigger-embed-for-n8n' ); ?></li>
		</ul>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-tools' ) ); ?>"><?php esc_html_e( 'Restore Starter Defaults', 'chat-trigger-embed-for-n8n' ); ?></a></p>
	</section>
</div>

<?php if ( ! $configured ) : ?>
	<div class="notice notice-warning inline"><p><?php esc_html_e( 'You are not connected yet. Start on Settings, paste the production Chat Trigger URL, then choose where the widget should appear.', 'chat-trigger-embed-for-n8n' ); ?></p></div>
<?php endif; ?>
