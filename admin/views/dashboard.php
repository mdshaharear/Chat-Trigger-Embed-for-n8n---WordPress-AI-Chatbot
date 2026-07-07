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
$checklist         = array(
	__( 'Enable public chat in n8n', 'chat-trigger-embed-for-n8n' ) => $configured && ! \ChatTriggerEmbedN8n\Helpers::is_test_webhook_url( (string) $settings['webhook_url'] ),
	__( 'Select Embedded Chat', 'chat-trigger-embed-for-n8n' ) => true,
	__( 'Add the website origin', 'chat-trigger-embed-for-n8n' ) => true,
	__( 'Use the production Chat Trigger URL', 'chat-trigger-embed-for-n8n' ) => $configured && ! \ChatTriggerEmbedN8n\Helpers::is_test_webhook_url( (string) $settings['webhook_url'] ),
	__( 'Activate the workflow', 'chat-trigger-embed-for-n8n' ) => $configured,
	__( 'Connect supported memory when previous sessions are enabled', 'chat-trigger-embed-for-n8n' ) => ! empty( $settings['load_previous_session'] ),
	__( 'Configure the plugin', 'chat-trigger-embed-for-n8n' ) => $enabled,
	__( 'Enable the chatbot', 'chat-trigger-embed-for-n8n' ) => $enabled,
	__( 'Clear website cache', 'chat-trigger-embed-for-n8n' ) => true,
	__( 'Test in an incognito browser', 'chat-trigger-embed-for-n8n' ) => true,
);
?>
<div class="cten-grid">
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
		<p class="description"><?php esc_html_e( 'AI Chat Builder for WordPress is an independent third-party WordPress integration and is not affiliated with or endorsed by n8n, OpenAI, or Google.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<p class="description"><?php echo esc_html( \ChatTriggerEmbedN8n\Helpers::internal_use_notice() ); ?></p>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Legacy n8n configuration checklist', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<ul class="cten-checklist">
			<?php foreach ( $checklist as $label => $done ) : ?>
				<li class="<?php echo esc_attr( $done ? 'is-done' : 'is-pending' ); ?>"><?php echo esc_html( $label ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Actions', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-chatbots' ) ); ?>"><?php esc_html_e( 'Open Chatbots', 'chat-trigger-embed-for-n8n' ); ?></a></p>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-settings' ) ); ?>"><?php esc_html_e( 'Open Settings', 'chat-trigger-embed-for-n8n' ); ?></a></p>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-legacy-n8n' ) ); ?>"><?php esc_html_e( 'Open Legacy n8n', 'chat-trigger-embed-for-n8n' ); ?></a></p>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-appearance' ) ); ?>"><?php esc_html_e( 'Open Preview', 'chat-trigger-embed-for-n8n' ); ?></a></p>
		<p><a class="button" href="<?php echo esc_url( 'https://docs.n8n.io/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Troubleshooting link', 'chat-trigger-embed-for-n8n' ); ?></a></p>
	</section>
</div>

<?php if ( ! $configured ) : ?>
	<div class="notice notice-warning inline"><p><?php esc_html_e( 'Connection configuration is incomplete. Add a production Chat Trigger URL before enabling the chatbot.', 'chat-trigger-embed-for-n8n' ); ?></p></div>
<?php endif; ?>
