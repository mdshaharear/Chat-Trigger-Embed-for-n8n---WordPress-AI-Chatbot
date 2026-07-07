<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
	<?php wp_nonce_field( 'cten_save_settings', 'cten_settings_nonce' ); ?>
	<input type="hidden" name="action" value="cten_save_settings">
	<input type="hidden" name="enabled" value="0">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Connection', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Start with the production URL and a display mode. Leave advanced options alone until the chatbot works on one page.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php cten_render_checkbox( __( 'Enable Chatbot', 'chat-trigger-embed-for-n8n' ), 'enabled', (bool) $settings['enabled'], __( 'The chatbot stays disabled until this is turned on and a production webhook is saved.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_select( __( 'Display Mode', 'chat-trigger-embed-for-n8n' ), 'render_mode', array( 'global_footer' => 'Global Footer', 'elementor_widget' => 'Elementor Widget', 'both' => 'Both' ), (string) $settings['render_mode'], __( 'Use Elementor Widget when you want the chat to appear only inside a page builder layout.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_select( __( 'Theme Mode', 'chat-trigger-embed-for-n8n' ), 'theme_mode', array( 'system' => 'System', 'light' => 'Light', 'dark' => 'Dark' ), (string) $settings['theme_mode'], __( 'System follows the visitor/browser preference and looks best for most sites.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_text( __( 'n8n Chat Trigger Production URL', 'chat-trigger-embed-for-n8n' ), 'webhook_url', (string) $settings['webhook_url'], 'url', __( 'Use the production webhook URL from an active workflow configured for Embedded Chat.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<div class="cten-diagnostics">
			<div class="cten-diagnostics__row">
				<div>
					<strong><?php esc_html_e( 'Production URL validation result', 'chat-trigger-embed-for-n8n' ); ?></strong>
					<p id="cten-webhook-diagnostic" class="description"><?php echo esc_html( \ChatTriggerEmbedN8n\Helpers::webhook_health( (string) $settings['webhook_url'] )['help'] ); ?></p>
				</div>
				<button type="button" class="button" data-cten-validate-webhook><?php esc_html_e( 'Validate URL', 'chat-trigger-embed-for-n8n' ); ?></button>
			</div>
			<ul class="cten-list">
				<li><?php esc_html_e( 'Workflow Active reminder: the URL must come from an active workflow.', 'chat-trigger-embed-for-n8n' ); ?></li>
				<li><?php esc_html_e( 'Allowed Origins reminder: add the website origin inside n8n.', 'chat-trigger-embed-for-n8n' ); ?></li>
				<li><?php esc_html_e( 'Embedded Chat mode reminder: the Chat Trigger must use embedded chat.', 'chat-trigger-embed-for-n8n' ); ?></li>
				<li><?php esc_html_e( 'Previous Session memory reminder: connect supported memory if loading prior sessions.', 'chat-trigger-embed-for-n8n' ); ?></li>
				<li><?php esc_html_e( 'Streaming compatibility warning: only enable streaming if the workflow supports it.', 'chat-trigger-embed-for-n8n' ); ?></li>
			</ul>
			<p class="description"><?php esc_html_e( 'Browser CORS troubleshooting: if messages fail, confirm the origin, production URL, and workflow state before testing again.', 'chat-trigger-embed-for-n8n' ); ?></p>
		</div>
		<?php cten_render_checkbox( __( 'Enable Previous Session Loading', 'chat-trigger-embed-for-n8n' ), 'load_previous_session', (bool) $settings['load_previous_session'] ); ?>
		<?php cten_render_checkbox( __( 'Enable Streaming', 'chat-trigger-embed-for-n8n' ), 'enable_streaming', (bool) $settings['enable_streaming'], __( 'Only enable this if the workflow response mode supports streaming.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_select( __( 'Request Method', 'chat-trigger-embed-for-n8n' ), 'request_method', array( 'POST' => 'POST', 'GET' => 'GET' ), (string) $settings['request_method'] ); ?>
		<?php cten_render_textarea( __( 'Optional Public Request Headers', 'chat-trigger-embed-for-n8n' ), 'public_request_headers_json', wp_json_encode( $settings['public_request_headers'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ), __( 'Enter JSON object format. Only public headers are sent.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_text( __( 'Chat Input Key', 'chat-trigger-embed-for-n8n' ), 'chat_input_key', (string) $settings['chat_input_key'] ); ?>
		<?php cten_render_text( __( 'Chat Session Key', 'chat-trigger-embed-for-n8n' ), 'chat_session_key', (string) $settings['chat_session_key'] ); ?>
		<?php cten_render_text( __( 'Default Language', 'chat-trigger-embed-for-n8n' ), 'default_language', 'en', 'text', __( 'English is the only supported Phase 1 language.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_checkbox( __( 'Debug Mode', 'chat-trigger-embed-for-n8n' ), 'debug_mode', (bool) $settings['debug_mode'] ); ?>
		<?php cten_render_select( __( 'Connection Test Mode', 'chat-trigger-embed-for-n8n' ), 'connection_test_mode', array( 'url_only' => 'URL analysis only', 'manual_message' => 'Manual message test only' ), (string) $settings['connection_test_mode'], __( 'The plugin never sends a fake user message automatically.', 'chat-trigger-embed-for-n8n' ) ); ?>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Setup Guidance', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<ul class="cten-list">
			<li><?php esc_html_e( 'Open the n8n Chat Trigger.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Enable Make Chat Publicly Available.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Select Embedded Chat.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Add the WordPress origin to Allowed Origins.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Activate the workflow before pasting the production chat webhook URL.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'If your workflow uses memory, connect Redis Chat Memory or the supported memory node before loading the previous session.', 'chat-trigger-embed-for-n8n' ); ?></li>
		</ul>
		<p><strong><?php esc_html_e( 'Current origin', 'chat-trigger-embed-for-n8n' ); ?></strong> <code id="cten-origin"><?php echo esc_html( \ChatTriggerEmbedN8n\Helpers::get_origin() ); ?></code></p>
		<p><button type="button" class="button" data-cten-copy-origin><?php esc_html_e( 'Copy Origin', 'chat-trigger-embed-for-n8n' ); ?></button></p>
	</section>
	<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Connection', 'chat-trigger-embed-for-n8n' ); ?></button></p>
</form>
