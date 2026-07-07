<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
	<?php wp_nonce_field( 'cten_save_settings', 'cten_settings_nonce' ); ?>
	<input type="hidden" name="action" value="cten_save_settings">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Behaviour', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<?php cten_render_checkbox( __( 'Load Previous Session', 'chat-trigger-embed-for-n8n' ), 'load_previous_session', (bool) $settings['load_previous_session'] ); ?>
		<?php cten_render_checkbox( __( 'Prevent Rapid Sends', 'chat-trigger-embed-for-n8n' ), 'prevent_rapid_sends', (bool) $settings['prevent_rapid_sends'] ); ?>
		<?php cten_render_number( __( 'Minimum Delay Between Sends', 'chat-trigger-embed-for-n8n' ), 'minimum_send_delay_ms', $settings['minimum_send_delay_ms'], __( 'Milliseconds.', 'chat-trigger-embed-for-n8n' ), 250, 5000 ); ?>
		<?php cten_render_number( __( 'Maximum Input Length', 'chat-trigger-embed-for-n8n' ), 'max_input_length', $settings['max_input_length'], '', 100, 4000 ); ?>
		<?php cten_render_checkbox( __( 'Confirm Before New Conversation', 'chat-trigger-embed-for-n8n' ), 'confirm_new_conversation', (bool) $settings['confirm_new_conversation'] ); ?>
		<?php cten_render_checkbox( __( 'Close with Escape', 'chat-trigger-embed-for-n8n' ), 'close_with_escape', (bool) $settings['close_with_escape'] ); ?>
		<?php cten_render_number( __( 'Session Expiry', 'chat-trigger-embed-for-n8n' ), 'session_expiry_days', $settings['session_expiry_days'], __( 'Days. Browser storage only.', 'chat-trigger-embed-for-n8n' ), 1, 180 ); ?>
		<?php cten_render_checkbox( __( 'Lazy Load Runtime', 'chat-trigger-embed-for-n8n' ), 'lazy_load_runtime', (bool) $settings['lazy_load_runtime'], __( 'Reserved optimization switch for environments that delay the official runtime until interaction.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_checkbox( __( 'Preload on Hover', 'chat-trigger-embed-for-n8n' ), 'preload_on_hover', (bool) $settings['preload_on_hover'] ); ?>
		<?php cten_render_number( __( 'Load After Delay', 'chat-trigger-embed-for-n8n' ), 'load_after_delay_seconds', $settings['load_after_delay_seconds'], __( 'Seconds. Use 0 to load immediately.', 'chat-trigger-embed-for-n8n' ), 0, 60 ); ?>
		<p class="description"><?php esc_html_e( 'Conversation menu, export, sounds, and unread badge controls are intentionally disabled in 1.5.0 until they can be supported without duplicating the official n8n chat UI.', 'chat-trigger-embed-for-n8n' ); ?></p>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Business Hours', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Business-hours enforcement is disabled in 1.5.0 because no schedule editor or runtime enforcement exists yet. The plugin will not save a fake offline mode.', 'chat-trigger-embed-for-n8n' ); ?></p>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Contact Fallbacks', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<?php cten_render_checkbox( __( 'Enable WhatsApp Fallback', 'chat-trigger-embed-for-n8n' ), 'enable_whatsapp_fallback', (bool) $settings['enable_whatsapp_fallback'] ); ?>
		<?php cten_render_text( __( 'WhatsApp Number', 'chat-trigger-embed-for-n8n' ), 'whatsapp_number', (string) $settings['whatsapp_number'] ); ?>
		<?php cten_render_text( __( 'WhatsApp Default Message', 'chat-trigger-embed-for-n8n' ), 'whatsapp_default_message', (string) $settings['whatsapp_default_message'] ); ?>
		<?php cten_render_checkbox( __( 'Enable Email Fallback', 'chat-trigger-embed-for-n8n' ), 'enable_email_fallback', (bool) $settings['enable_email_fallback'] ); ?>
		<?php cten_render_text( __( 'Contact Email', 'chat-trigger-embed-for-n8n' ), 'contact_email', (string) $settings['contact_email'], 'text' ); ?>
		<?php cten_render_text( __( 'Email Subject', 'chat-trigger-embed-for-n8n' ), 'email_subject', (string) $settings['email_subject'] ); ?>
		<?php cten_render_checkbox( __( 'Enable Contact Page Fallback', 'chat-trigger-embed-for-n8n' ), 'enable_contact_page_fallback', (bool) $settings['enable_contact_page_fallback'] ); ?>
		<?php cten_render_text( __( 'Contact Page URL', 'chat-trigger-embed-for-n8n' ), 'contact_page_url', (string) $settings['contact_page_url'], 'url' ); ?>
		<?php cten_render_text( __( 'Human Support Button Label', 'chat-trigger-embed-for-n8n' ), 'human_support_button_label', (string) $settings['human_support_button_label'] ); ?>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Pre-chat Form', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Disabled by default. The setup wizard can turn this on for you. Submitted values are not stored locally by default; n8n must handle storage or processing.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php cten_render_checkbox( __( 'Enable Pre-chat Form', 'chat-trigger-embed-for-n8n' ), 'pre_chat_form[enabled]', (bool) $settings['pre_chat_form']['enabled'] ); ?>
		<?php cten_render_select( __( 'Send Form Values As', 'chat-trigger-embed-for-n8n' ), 'pre_chat_form[sending]', array( 'metadata' => 'Metadata' ), (string) $settings['pre_chat_form']['sending'] ); ?>
		<?php cten_render_checkbox( __( 'Allow Skip', 'chat-trigger-embed-for-n8n' ), 'pre_chat_form[allow_skip]', (bool) $settings['pre_chat_form']['allow_skip'] ); ?>
		<?php cten_render_textarea( __( 'Privacy Text', 'chat-trigger-embed-for-n8n' ), 'pre_chat_form[privacy_text]', (string) $settings['pre_chat_form']['privacy_text'] ); ?>
		<?php foreach ( $settings['pre_chat_form']['fields'] as $index => $field ) : ?>
			<fieldset class="cten-action">
				<legend><?php echo esc_html( sprintf( __( 'Field %d', 'chat-trigger-embed-for-n8n' ), $index + 1 ) ); ?></legend>
				<input type="hidden" name="pre_chat_form[fields][<?php echo esc_attr( (string) $index ); ?>][key]" value="<?php echo esc_attr( $field['key'] ); ?>">
				<?php cten_render_checkbox( __( 'Enabled', 'chat-trigger-embed-for-n8n' ), 'pre_chat_form[fields][' . $index . '][enabled]', (bool) $field['enabled'] ); ?>
				<?php cten_render_checkbox( __( 'Required', 'chat-trigger-embed-for-n8n' ), 'pre_chat_form[fields][' . $index . '][required]', (bool) $field['required'] ); ?>
				<?php cten_render_select( __( 'Type', 'chat-trigger-embed-for-n8n' ), 'pre_chat_form[fields][' . $index . '][type]', array( 'text' => 'Text', 'email' => 'Email', 'phone' => 'Phone', 'url' => 'Website', 'select' => 'Select', 'consent' => 'Consent' ), (string) $field['type'] ); ?>
				<?php cten_render_text( __( 'Label', 'chat-trigger-embed-for-n8n' ), 'pre_chat_form[fields][' . $index . '][label]', (string) $field['label'] ); ?>
				<?php cten_render_text( __( 'Placeholder', 'chat-trigger-embed-for-n8n' ), 'pre_chat_form[fields][' . $index . '][placeholder]', (string) $field['placeholder'] ); ?>
				<?php cten_render_number( __( 'Sort Order', 'chat-trigger-embed-for-n8n' ), 'pre_chat_form[fields][' . $index . '][sort]', $field['sort'], '', 0, 999 ); ?>
			</fieldset>
		<?php endforeach; ?>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Lead Qualification', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'The plugin sends structured context only. n8n remains responsible for qualification logic.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php cten_render_checkbox( __( 'Enable Qualification Mode', 'chat-trigger-embed-for-n8n' ), 'lead_qualification[enabled]', (bool) $settings['lead_qualification']['enabled'] ); ?>
		<?php cten_render_text( __( 'Qualification Goal', 'chat-trigger-embed-for-n8n' ), 'lead_qualification[goal]', (string) $settings['lead_qualification']['goal'] ); ?>
		<?php cten_render_text( __( 'Budget Ranges', 'chat-trigger-embed-for-n8n' ), 'lead_qualification[budget_ranges]', implode( ', ', (array) $settings['lead_qualification']['budget_ranges'] ) ); ?>
		<?php cten_render_text( __( 'Timeline Options', 'chat-trigger-embed-for-n8n' ), 'lead_qualification[timeline_options]', implode( ', ', (array) $settings['lead_qualification']['timeline_options'] ) ); ?>
		<?php cten_render_text( __( 'Completion Message', 'chat-trigger-embed-for-n8n' ), 'lead_qualification[completion_message]', (string) $settings['lead_qualification']['completion_message'] ); ?>
	</section>
	<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Behaviour', 'chat-trigger-embed-for-n8n' ); ?></button></p>
</form>
