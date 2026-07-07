<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
	<?php wp_nonce_field( 'cten_save_settings', 'cten_settings_nonce' ); ?>
	<input type="hidden" name="action" value="cten_save_settings">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Quick Actions', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Conversation starters are shown for a new conversation and hidden after the first interaction.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php foreach ( $settings['quick_actions'] as $index => $action ) : ?>
			<fieldset class="cten-action">
				<legend><?php echo esc_html( sprintf( __( 'Action %d', 'chat-trigger-embed-for-n8n' ), $index + 1 ) ); ?></legend>
				<?php cten_render_checkbox( __( 'Enabled', 'chat-trigger-embed-for-n8n' ), 'quick_actions[' . $index . '][enabled]', (bool) $action['enabled'] ); ?>
				<?php cten_render_text( __( 'Label', 'chat-trigger-embed-for-n8n' ), 'quick_actions[' . $index . '][label]', (string) $action['label'] ); ?>
				<?php cten_render_textarea( __( 'Message Sent to n8n', 'chat-trigger-embed-for-n8n' ), 'quick_actions[' . $index . '][message]', (string) $action['message'] ); ?>
				<?php cten_render_text( __( 'Icon', 'chat-trigger-embed-for-n8n' ), 'quick_actions[' . $index . '][icon]', (string) $action['icon'] ); ?>
				<?php cten_render_number( __( 'Sort Order', 'chat-trigger-embed-for-n8n' ), 'quick_actions[' . $index . '][sort]', $action['sort'], '', 0, 999 ); ?>
			</fieldset>
		<?php endforeach; ?>
	</section>
	<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Quick Actions', 'chat-trigger-embed-for-n8n' ); ?></button></p>
</form>
