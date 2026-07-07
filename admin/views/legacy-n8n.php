<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cten-grid">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Legacy n8n', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'This mode preserves the existing 1.5.0 n8n embed behavior for backward compatibility during the 2.0 release line.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<ul class="cten-stats">
			<li><?php esc_html_e( 'Existing profiles and settings stay in the current storage schema.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'The official @n8n/chat runtime remains the compatibility path for legacy bots.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Native UI bots are introduced separately and do not auto-convert legacy chatbots.', 'chat-trigger-embed-for-n8n' ); ?></li>
		</ul>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-profiles' ) ); ?>"><?php esc_html_e( 'Open Legacy Profiles', 'chat-trigger-embed-for-n8n' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-dashboard' ) ); ?>"><?php esc_html_e( 'Back to Dashboard', 'chat-trigger-embed-for-n8n' ); ?></a>
		</p>
	</section>
</div>
