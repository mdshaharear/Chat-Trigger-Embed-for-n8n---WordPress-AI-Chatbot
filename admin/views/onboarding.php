<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wizard = \ChatTriggerEmbedN8n\Settings::sanitize_onboarding_status( $settings['onboarding_status'] ?? array() );
$steps  = array(
	'welcome'       => __( 'Welcome', 'chat-trigger-embed-for-n8n' ),
	'connect'       => __( 'Connect n8n', 'chat-trigger-embed-for-n8n' ),
	'identity'      => __( 'Chat Identity', 'chat-trigger-embed-for-n8n' ),
	'appearance'    => __( 'Appearance', 'chat-trigger-embed-for-n8n' ),
	'quick_actions' => __( 'Quick Actions', 'chat-trigger-embed-for-n8n' ),
	'visibility'    => __( 'Visibility', 'chat-trigger-embed-for-n8n' ),
	'preview'       => __( 'Test Preview', 'chat-trigger-embed-for-n8n' ),
	'enable'        => __( 'Enable Chatbot', 'chat-trigger-embed-for-n8n' ),
);
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
	<?php wp_nonce_field( 'cten_save_settings', 'cten_settings_nonce' ); ?>
	<input type="hidden" name="action" value="cten_save_settings">
	<input type="hidden" name="onboarding_status[started]" value="1">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Setup Wizard', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Use this checklist to configure n8n safely. You can set the webhook, choose where the chat appears, and pick a light or dark theme without exposing visitor contact details.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<ul class="cten-checklist">
			<?php foreach ( $steps as $key => $label ) : ?>
				<li class="<?php echo esc_attr( $wizard['step'] === $key ? 'is-done' : 'is-pending' ); ?>"><?php echo esc_html( $label ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Connection Setup', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Paste the production webhook URL from your active n8n Chat Trigger. This wizard can do the first setup without sending you to three different pages.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php cten_render_text( __( 'Production Webhook URL', 'chat-trigger-embed-for-n8n' ), 'webhook_url', (string) $settings['webhook_url'], 'url', __( 'Use the production URL, not the test URL.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_select( __( 'Display Mode', 'chat-trigger-embed-for-n8n' ), 'render_mode', array( 'global_footer' => 'Global Footer', 'elementor_widget' => 'Elementor Widget', 'both' => 'Both' ), (string) $settings['render_mode'], __( 'Elementor Widget is best when you want the chat only on specific pages.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_select( __( 'Theme Mode', 'chat-trigger-embed-for-n8n' ), 'theme_mode', array( 'system' => 'System', 'light' => 'Light', 'dark' => 'Dark' ), (string) $settings['theme_mode'], __( 'System follows the visitor device theme automatically.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_checkbox( __( 'Enable Chatbot', 'chat-trigger-embed-for-n8n' ), 'enabled', (bool) $settings['enabled'], __( 'Only turn this on after the webhook is saved and the workflow is active.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<p><strong><?php esc_html_e( 'Website origin', 'chat-trigger-embed-for-n8n' ); ?></strong> <code id="cten-origin"><?php echo esc_html( \ChatTriggerEmbedN8n\Helpers::get_origin() ); ?></code></p>
		<p><button type="button" class="button" data-cten-copy-origin><?php esc_html_e( 'Copy Origin', 'chat-trigger-embed-for-n8n' ); ?></button></p>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Wizard Progress', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<?php cten_render_select( __( 'Current Step', 'chat-trigger-embed-for-n8n' ), 'onboarding_status[step]', $steps, (string) $wizard['step'] ); ?>
		<?php cten_render_checkbox( __( 'Mark Wizard Complete', 'chat-trigger-embed-for-n8n' ), 'onboarding_status[completed]', (bool) $wizard['completed'], __( 'This hides first-run setup reminders but keeps the wizard available here.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<ul class="cten-list">
			<li><?php esc_html_e( 'Activate the workflow before enabling the public chatbot.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Add your site origin to Allowed Origins in n8n.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Connect supported memory before enabling previous-session loading.', 'chat-trigger-embed-for-n8n' ); ?></li>
		</ul>
	</section>
	<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Wizard Progress', 'chat-trigger-embed-for-n8n' ); ?></button></p>
</form>
