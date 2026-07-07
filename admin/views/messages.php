<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
	<?php wp_nonce_field( 'cten_save_settings', 'cten_settings_nonce' ); ?>
	<input type="hidden" name="action" value="cten_save_settings">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Messages', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<?php cten_render_text( __( 'Bot Name', 'chat-trigger-embed-for-n8n' ), 'bot_name', (string) $settings['bot_name'] ); ?>
		<?php cten_render_text( __( 'Bot Subtitle', 'chat-trigger-embed-for-n8n' ), 'bot_subtitle', (string) $settings['bot_subtitle'] ); ?>
		<?php cten_render_textarea( __( 'Welcome Message', 'chat-trigger-embed-for-n8n' ), 'welcome_message', (string) $settings['welcome_message'] ); ?>
		<?php cten_render_text( __( 'Input Placeholder', 'chat-trigger-embed-for-n8n' ), 'input_placeholder', (string) $settings['input_placeholder'] ); ?>
		<?php cten_render_text( __( 'Start Conversation Label', 'chat-trigger-embed-for-n8n' ), 'start_conversation_label', (string) $settings['start_conversation_label'] ); ?>
		<?php cten_render_textarea( __( 'Follow-up Privacy Text', 'chat-trigger-embed-for-n8n' ), 'follow_up_privacy_text', (string) $settings['follow_up_privacy_text'] ); ?>
		<?php cten_render_text( __( 'Online Status Text', 'chat-trigger-embed-for-n8n' ), 'online_status_text', (string) $settings['online_status_text'] ); ?>
		<?php cten_render_text( __( 'Offline or Error Text', 'chat-trigger-embed-for-n8n' ), 'offline_error_text', (string) $settings['offline_error_text'] ); ?>
		<?php cten_render_text( __( 'Retry Button Text', 'chat-trigger-embed-for-n8n' ), 'retry_button_text', (string) $settings['retry_button_text'] ); ?>
		<?php cten_render_text( __( 'New Conversation Text', 'chat-trigger-embed-for-n8n' ), 'new_conversation_text', (string) $settings['new_conversation_text'] ); ?>
		<?php cten_render_text( __( 'Loading Message', 'chat-trigger-embed-for-n8n' ), 'loading_message', (string) $settings['loading_message'] ); ?>
		<?php cten_render_text( __( 'Typing Indicator Text', 'chat-trigger-embed-for-n8n' ), 'typing_indicator_text', (string) $settings['typing_indicator_text'] ); ?>
		<?php cten_render_text( __( 'Close Button Label', 'chat-trigger-embed-for-n8n' ), 'close_button_label', (string) $settings['close_button_label'] ); ?>
		<?php cten_render_text( __( 'Launcher Accessibility Label', 'chat-trigger-embed-for-n8n' ), 'launcher_accessibility_label', (string) $settings['launcher_accessibility_label'] ); ?>
		<?php cten_render_text( __( 'Send Button Accessibility Label', 'chat-trigger-embed-for-n8n' ), 'send_button_accessibility_label', (string) $settings['send_button_accessibility_label'] ); ?>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Initial Messages', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Add up to six safe plain-text messages. Empty messages are ignored.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php for ( $index = 0; $index < 6; $index++ ) : ?>
			<?php
			$message = $settings['initial_messages'][ $index ] ?? array(
				'enabled' => false,
				'text'    => '',
				'sort'    => ( $index + 1 ) * 10,
			);
			?>
			<fieldset class="cten-action">
				<legend><?php echo esc_html( sprintf( __( 'Initial Message %d', 'chat-trigger-embed-for-n8n' ), $index + 1 ) ); ?></legend>
				<?php cten_render_checkbox( __( 'Enabled', 'chat-trigger-embed-for-n8n' ), 'initial_messages[' . $index . '][enabled]', (bool) $message['enabled'] ); ?>
				<?php cten_render_textarea( __( 'Message Text', 'chat-trigger-embed-for-n8n' ), 'initial_messages[' . $index . '][text]', (string) $message['text'] ); ?>
				<?php cten_render_number( __( 'Sort Order', 'chat-trigger-embed-for-n8n' ), 'initial_messages[' . $index . '][sort]', $message['sort'], '', 0, 999 ); ?>
			</fieldset>
		<?php endfor; ?>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Live Preview', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<?php \ChatTriggerEmbedN8n\Preview::render( $settings, 'messages' ); ?>
	</section>
	<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Messages', 'chat-trigger-embed-for-n8n' ); ?></button></p>
</form>
