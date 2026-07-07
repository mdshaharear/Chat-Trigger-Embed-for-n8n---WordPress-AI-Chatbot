<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$profiles = \ChatTriggerEmbedN8n\Profiles::sanitize_profiles( $settings['profiles'] ?? array(), $settings );
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
	<?php wp_nonce_field( 'cten_save_settings', 'cten_settings_nonce' ); ?>
	<input type="hidden" name="action" value="cten_save_settings">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Chatbot Profiles', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Profiles let one plugin installation serve different chatbot configurations by priority and page rules. Only one profile renders by default.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<p><input type="search" class="regular-text" data-cten-profile-search placeholder="<?php esc_attr_e( 'Search profiles', 'chat-trigger-embed-for-n8n' ); ?>"></p>
		<?php foreach ( $profiles as $index => $profile ) : ?>
			<fieldset class="cten-action" data-cten-profile-card>
				<legend><?php echo esc_html( $profile['name'] ); ?></legend>
				<input type="hidden" name="profiles[<?php echo esc_attr( (string) $index ); ?>][id]" value="<?php echo esc_attr( $profile['id'] ); ?>">
				<?php cten_render_checkbox( __( 'Enabled', 'chat-trigger-embed-for-n8n' ), 'profiles[' . $index . '][enabled]', (bool) $profile['enabled'] ); ?>
				<?php cten_render_checkbox( __( 'Default Profile', 'chat-trigger-embed-for-n8n' ), 'profiles[' . $index . '][is_default]', (bool) $profile['is_default'] ); ?>
				<?php cten_render_text( __( 'Profile Name', 'chat-trigger-embed-for-n8n' ), 'profiles[' . $index . '][name]', (string) $profile['name'] ); ?>
				<?php cten_render_textarea( __( 'Internal Description', 'chat-trigger-embed-for-n8n' ), 'profiles[' . $index . '][description]', (string) $profile['description'] ); ?>
				<?php cten_render_number( __( 'Priority', 'chat-trigger-embed-for-n8n' ), 'profiles[' . $index . '][priority]', $profile['priority'], __( 'Lower numbers resolve first.', 'chat-trigger-embed-for-n8n' ), 0, 999 ); ?>
				<?php cten_render_text( __( 'Production Chat Trigger URL', 'chat-trigger-embed-for-n8n' ), 'profiles[' . $index . '][webhook_url]', (string) $profile['webhook_url'], 'url' ); ?>
				<?php cten_render_text( __( 'Bot Name', 'chat-trigger-embed-for-n8n' ), 'profiles[' . $index . '][bot_name]', (string) $profile['bot_name'] ); ?>
				<?php cten_render_text( __( 'Subtitle', 'chat-trigger-embed-for-n8n' ), 'profiles[' . $index . '][bot_subtitle]', (string) $profile['bot_subtitle'] ); ?>
				<?php cten_render_select( __( 'Theme Preset', 'chat-trigger-embed-for-n8n' ), 'profiles[' . $index . '][theme_preset]', array_combine( \ChatTriggerEmbedN8n\Settings::theme_presets(), array_map( 'ucwords', str_replace( '-', ' ', \ChatTriggerEmbedN8n\Settings::theme_presets() ) ) ), (string) $profile['theme_preset'] ); ?>
				<?php cten_render_text( __( 'Launcher Label', 'chat-trigger-embed-for-n8n' ), 'profiles[' . $index . '][launcher_label]', (string) $profile['launcher_label'] ); ?>
				<?php cten_render_select( __( 'Site Scope', 'chat-trigger-embed-for-n8n' ), 'profiles[' . $index . '][visibility][scope]', array( 'entire_site' => 'Entire Site', 'homepage_only' => 'Homepage Only', 'selected_pages' => 'Selected Pages', 'excluded_pages' => 'Excluded Pages', 'selected_post_types' => 'Selected Post Types' ), (string) $profile['visibility']['scope'] ); ?>
			</fieldset>
		<?php endforeach; ?>
	</section>
	<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Profiles', 'chat-trigger-embed-for-n8n' ); ?></button></p>
</form>
