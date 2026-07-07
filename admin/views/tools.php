<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="cten-card">
	<h2><?php esc_html_e( 'Tools', 'chat-trigger-embed-for-n8n' ); ?></h2>
	<p><?php esc_html_e( 'Export, import, or reset settings. All actions require capability checks and nonces.', 'chat-trigger-embed-for-n8n' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-inline-form">
		<?php wp_nonce_field( 'cten_tools_action', 'cten_tools_nonce' ); ?>
		<input type="hidden" name="action" value="cten_export_settings">
		<button type="submit" class="button"><?php esc_html_e( 'Export JSON', 'chat-trigger-embed-for-n8n' ); ?></button>
	</form>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-inline-form">
		<?php wp_nonce_field( 'cten_tools_action', 'cten_tools_nonce' ); ?>
		<input type="hidden" name="action" value="cten_import_settings">
		<textarea name="cten_import_json" rows="10" class="large-text code" placeholder="<?php esc_attr_e( 'Paste exported JSON here', 'chat-trigger-embed-for-n8n' ); ?>"></textarea>
		<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Import JSON', 'chat-trigger-embed-for-n8n' ); ?></button></p>
	</form>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-inline-form">
		<?php wp_nonce_field( 'cten_tools_action', 'cten_tools_nonce' ); ?>
		<input type="hidden" name="action" value="cten_reset_settings">
		<button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Reset all settings to defaults?', 'chat-trigger-embed-for-n8n' ) ); ?>');"><?php esc_html_e( 'Reset to Defaults', 'chat-trigger-embed-for-n8n' ); ?></button>
	</form>
</section>

<section class="cten-card">
	<h2><?php esc_html_e( 'Uninstall', 'chat-trigger-embed-for-n8n' ); ?></h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
		<?php wp_nonce_field( 'cten_save_settings', 'cten_settings_nonce' ); ?>
		<input type="hidden" name="action" value="cten_save_settings">
		<?php cten_render_checkbox( __( 'Delete Plugin Data on Uninstall', 'chat-trigger-embed-for-n8n' ), 'delete_data_on_uninstall', (bool) $settings['delete_data_on_uninstall'], __( 'When enabled, the plugin removes its own settings during uninstall.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Uninstall Preference', 'chat-trigger-embed-for-n8n' ); ?></button></p>
	</form>
</section>

<section class="cten-card">
	<h2><?php esc_html_e( 'Documentation', 'chat-trigger-embed-for-n8n' ); ?></h2>
	<ul class="cten-list">
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/INSTALLATION.md' ) ); ?>" target="_blank" rel="noopener noreferrer">INSTALLATION.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/QUICK_START.md' ) ); ?>" target="_blank" rel="noopener noreferrer">QUICK_START.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/PRODUCTION_CHECKLIST.md' ) ); ?>" target="_blank" rel="noopener noreferrer">PRODUCTION_CHECKLIST.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/ROLLBACK.md' ) ); ?>" target="_blank" rel="noopener noreferrer">ROLLBACK.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/V2_MIGRATION_PLAN.md' ) ); ?>" target="_blank" rel="noopener noreferrer">V2_MIGRATION_PLAN.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/NATIVE_AI_ARCHITECTURE.md' ) ); ?>" target="_blank" rel="noopener noreferrer">NATIVE_AI_ARCHITECTURE.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/NATIVE_AI_PRIVACY.md' ) ); ?>" target="_blank" rel="noopener noreferrer">NATIVE_AI_PRIVACY.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/V2_ALPHA_LIMITATIONS.md' ) ); ?>" target="_blank" rel="noopener noreferrer">V2_ALPHA_LIMITATIONS.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/SYSTEM_MESSAGE_SNIPPETS.md' ) ); ?>" target="_blank" rel="noopener noreferrer">SYSTEM_MESSAGE_SNIPPETS.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/N8N_SETUP.md' ) ); ?>" target="_blank" rel="noopener noreferrer">N8N_SETUP.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/N8N_CHAT_COMPATIBILITY.md' ) ); ?>" target="_blank" rel="noopener noreferrer">N8N_CHAT_COMPATIBILITY.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/ARCHITECTURE.md' ) ); ?>" target="_blank" rel="noopener noreferrer">ARCHITECTURE.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/TESTING.md' ) ); ?>" target="_blank" rel="noopener noreferrer">TESTING.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/PRIVACY.md' ) ); ?>" target="_blank" rel="noopener noreferrer">PRIVACY.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/PROFILES.md' ) ); ?>" target="_blank" rel="noopener noreferrer">PROFILES.md</a></li>
		<li><a href="<?php echo esc_url( \ChatTriggerEmbedN8n\Helpers::asset_url( 'docs/MIGRATIONS.md' ) ); ?>" target="_blank" rel="noopener noreferrer">MIGRATIONS.md</a></li>
		<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=cten-runtime-lab' ) ); ?>">Runtime Lab</a></li>
	</ul>
</section>
