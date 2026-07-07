<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
	<?php wp_nonce_field( 'cten_save_settings', 'cten_settings_nonce' ); ?>
	<input type="hidden" name="action" value="cten_save_settings">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Launcher', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Control where the launcher appears and how assertive it feels. Auto-open remains disabled by default.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php cten_render_select( __( 'Launcher Position', 'chat-trigger-embed-for-n8n' ), 'launcher_position', array( 'bottom-right' => 'Bottom Right', 'bottom-left' => 'Bottom Left', 'top-right' => 'Top Right', 'top-left' => 'Top Left' ), (string) $settings['launcher_position'] ); ?>
		<?php cten_render_number( __( 'Launcher Size', 'chat-trigger-embed-for-n8n' ), 'launcher_size', $settings['launcher_size'], '', 48, 96 ); ?>
		<?php cten_render_text( __( 'Launcher Icon', 'chat-trigger-embed-for-n8n' ), 'launcher_icon', (string) $settings['launcher_icon'] ); ?>
		<?php cten_render_text( __( 'Launcher Label Text', 'chat-trigger-embed-for-n8n' ), 'launcher_label', (string) $settings['launcher_label'] ); ?>
		<?php cten_render_select( __( 'Launcher Animation', 'chat-trigger-embed-for-n8n' ), 'launcher_animation', array( 'pulse' => 'Pulse', 'bounce' => 'Bounce', 'fade' => 'Fade', 'none' => 'None' ), (string) $settings['launcher_animation'] ); ?>
		<?php cten_render_number( __( 'Show Launcher After Delay', 'chat-trigger-embed-for-n8n' ), 'launcher_delay_seconds', $settings['launcher_delay_seconds'], __( 'Seconds. Use 0 to show immediately.', 'chat-trigger-embed-for-n8n' ), 0, 60 ); ?>
		<?php cten_render_checkbox( __( 'Show Online Dot', 'chat-trigger-embed-for-n8n' ), 'show_online_indicator', (bool) $settings['show_online_indicator'] ); ?>
		<?php cten_render_text( __( 'Online Dot Color', 'chat-trigger-embed-for-n8n' ), 'online_indicator_color', (string) $settings['online_indicator_color'] ); ?>
		<?php cten_render_checkbox( __( 'Close on Outside Click', 'chat-trigger-embed-for-n8n' ), 'close_on_outside_click', (bool) $settings['close_on_outside_click'] ); ?>
		<?php cten_render_checkbox( __( 'Open Chat Automatically', 'chat-trigger-embed-for-n8n' ), 'auto_open_enabled', (bool) $settings['auto_open_enabled'], __( 'Disabled by default to avoid aggressive interruptions.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_number( __( 'Auto-open Delay', 'chat-trigger-embed-for-n8n' ), 'auto_open_delay_seconds', $settings['auto_open_delay_seconds'], __( 'Seconds before automatic opening when enabled.', 'chat-trigger-embed-for-n8n' ), 3, 120 ); ?>
	</section>
	<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Launcher', 'chat-trigger-embed-for-n8n' ); ?></button></p>
</form>
