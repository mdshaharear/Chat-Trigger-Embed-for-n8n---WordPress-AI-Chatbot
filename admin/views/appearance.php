<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
	<?php wp_nonce_field( 'cten_save_settings', 'cten_settings_nonce' ); ?>
	<input type="hidden" name="action" value="cten_save_settings">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Appearance', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<?php cten_render_select( __( 'Theme Preset', 'chat-trigger-embed-for-n8n' ), 'theme_preset', array_combine( \ChatTriggerEmbedN8n\Settings::theme_presets(), array_map( 'ucwords', str_replace( '-', ' ', \ChatTriggerEmbedN8n\Settings::theme_presets() ) ) ), (string) $settings['theme_preset'] ); ?>
		<?php cten_render_text( __( 'Primary Color', 'chat-trigger-embed-for-n8n' ), 'primary_color', (string) $settings['primary_color'], 'text' ); ?>
		<?php cten_render_text( __( 'Secondary Color', 'chat-trigger-embed-for-n8n' ), 'secondary_color', (string) $settings['secondary_color'], 'text' ); ?>
		<?php cten_render_text( __( 'Accent Color', 'chat-trigger-embed-for-n8n' ), 'accent_color', (string) $settings['accent_color'], 'text' ); ?>
		<?php cten_render_text( __( 'Window Background', 'chat-trigger-embed-for-n8n' ), 'window_background', (string) $settings['window_background'] ); ?>
		<?php cten_render_text( __( 'Header Background', 'chat-trigger-embed-for-n8n' ), 'header_background', (string) $settings['header_background'] ); ?>
		<?php cten_render_text( __( 'Footer Background', 'chat-trigger-embed-for-n8n' ), 'footer_background', (string) $settings['footer_background'] ); ?>
		<?php cten_render_text( __( 'Text Color', 'chat-trigger-embed-for-n8n' ), 'text_color', (string) $settings['text_color'] ); ?>
		<?php cten_render_text( __( 'Muted Text Color', 'chat-trigger-embed-for-n8n' ), 'muted_text_color', (string) $settings['muted_text_color'] ); ?>
		<?php cten_render_text( __( 'Link Color', 'chat-trigger-embed-for-n8n' ), 'link_color', (string) $settings['link_color'] ); ?>
		<?php cten_render_text( __( 'Error Color', 'chat-trigger-embed-for-n8n' ), 'error_color', (string) $settings['error_color'] ); ?>
		<?php cten_render_text( __( 'Success Color', 'chat-trigger-embed-for-n8n' ), 'success_color', (string) $settings['success_color'] ); ?>
		<?php cten_render_text( __( 'Bot Message Background', 'chat-trigger-embed-for-n8n' ), 'bot_message_background', (string) $settings['bot_message_background'] ); ?>
		<?php cten_render_text( __( 'User Message Background', 'chat-trigger-embed-for-n8n' ), 'user_message_background', (string) $settings['user_message_background'] ); ?>
		<?php cten_render_text( __( 'Input Background', 'chat-trigger-embed-for-n8n' ), 'input_background', (string) $settings['input_background'] ); ?>
		<?php cten_render_text( __( 'Border Color', 'chat-trigger-embed-for-n8n' ), 'border_color', (string) $settings['border_color'] ); ?>
		<?php cten_render_number( __( 'Glass Opacity', 'chat-trigger-embed-for-n8n' ), 'glass_opacity', (int) round( $settings['glass_opacity'] * 100 ), __( 'Enter a percentage from 20 to 100.', 'chat-trigger-embed-for-n8n' ), 20, 100 ); ?>
		<?php cten_render_number( __( 'Blur Strength', 'chat-trigger-embed-for-n8n' ), 'blur_strength', $settings['blur_strength'], '', 0, 50 ); ?>
		<?php cten_render_number( __( 'Shadow Strength', 'chat-trigger-embed-for-n8n' ), 'shadow_strength', $settings['shadow_strength'], '', 0, 64 ); ?>
		<?php cten_render_number( __( 'Border Radius', 'chat-trigger-embed-for-n8n' ), 'border_radius', $settings['border_radius'], '', 0, 40 ); ?>
		<?php cten_render_number( __( 'Base Font Size', 'chat-trigger-embed-for-n8n' ), 'base_font_size', $settings['base_font_size'], '', 12, 20 ); ?>
		<?php cten_render_number( __( 'Heading Font Size', 'chat-trigger-embed-for-n8n' ), 'heading_font_size', $settings['heading_font_size'], '', 14, 28 ); ?>
		<?php cten_render_number( __( 'Message Font Size', 'chat-trigger-embed-for-n8n' ), 'message_font_size', $settings['message_font_size'], '', 12, 20 ); ?>
		<?php cten_render_number( __( 'Desktop Width', 'chat-trigger-embed-for-n8n' ), 'desktop_width', $settings['desktop_width'], '', 360, 800 ); ?>
		<?php cten_render_number( __( 'Desktop Height', 'chat-trigger-embed-for-n8n' ), 'desktop_height', $settings['desktop_height'], '', 480, 900 ); ?>
		<?php cten_render_number( __( 'Tablet Width', 'chat-trigger-embed-for-n8n' ), 'tablet_width', $settings['tablet_width'], '', 320, 700 ); ?>
		<?php cten_render_number( __( 'Tablet Height', 'chat-trigger-embed-for-n8n' ), 'tablet_height', $settings['tablet_height'], '', 420, 800 ); ?>
		<?php cten_render_select( __( 'Mobile Layout', 'chat-trigger-embed-for-n8n' ), 'mobile_layout', array( 'fullscreen' => 'Fullscreen', 'window' => 'Window' ), (string) $settings['mobile_layout'] ); ?>
		<?php cten_render_select( __( 'Launcher Position', 'chat-trigger-embed-for-n8n' ), 'launcher_position', array( 'bottom-right' => 'Bottom Right', 'bottom-left' => 'Bottom Left', 'top-right' => 'Top Right', 'top-left' => 'Top Left' ), (string) $settings['launcher_position'] ); ?>
		<?php cten_render_number( __( 'Launcher Size', 'chat-trigger-embed-for-n8n' ), 'launcher_size', $settings['launcher_size'], '', 48, 96 ); ?>
		<?php cten_render_text( __( 'Launcher Icon', 'chat-trigger-embed-for-n8n' ), 'launcher_icon', (string) $settings['launcher_icon'] ); ?>
		<?php cten_render_text( __( 'Launcher Label', 'chat-trigger-embed-for-n8n' ), 'launcher_label', (string) $settings['launcher_label'] ); ?>
		<?php cten_render_select( __( 'Launcher Animation', 'chat-trigger-embed-for-n8n' ), 'launcher_animation', array( 'pulse' => 'Pulse', 'bounce' => 'Bounce', 'fade' => 'Fade', 'none' => 'None' ), (string) $settings['launcher_animation'] ); ?>
		<?php cten_render_checkbox( __( 'Show Online Indicator', 'chat-trigger-embed-for-n8n' ), 'show_online_indicator', (bool) $settings['show_online_indicator'] ); ?>
		<?php cten_render_text( __( 'Custom Avatar Image', 'chat-trigger-embed-for-n8n' ), 'custom_avatar_image', (string) $settings['custom_avatar_image'], 'url' ); ?>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Live Preview', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<?php \ChatTriggerEmbedN8n\Preview::render( $settings, 'appearance' ); ?>
	</section>
	<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Appearance', 'chat-trigger-embed-for-n8n' ); ?></button></p>
</form>
