<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$summary = \ChatTriggerEmbedN8n\Analytics::summary();
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
	<?php wp_nonce_field( 'cten_save_settings', 'cten_settings_nonce' ); ?>
	<input type="hidden" name="action" value="cten_save_settings">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Local Analytics', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Analytics are disabled by default and store event counts only. Full visitor messages, raw IP addresses, emails, cookies, and fingerprints are not stored.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php cten_render_checkbox( __( 'Enable Local Analytics', 'chat-trigger-embed-for-n8n' ), 'analytics_enabled', (bool) $settings['analytics_enabled'] ); ?>
		<?php cten_render_number( __( 'Retention Days', 'chat-trigger-embed-for-n8n' ), 'analytics_retention_days', $settings['analytics_retention_days'], '', 7, 180 ); ?>
		<p><strong><?php esc_html_e( 'Database table', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( \ChatTriggerEmbedN8n\Analytics::table_exists() ? __( 'Present', 'chat-trigger-embed-for-n8n' ) : __( 'Not created', 'chat-trigger-embed-for-n8n' ) ); ?></p>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Recent Event Summary', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<?php if ( empty( $summary ) ) : ?>
			<p class="description"><?php esc_html_e( 'No local analytics events are stored yet.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php else : ?>
			<ul class="cten-list">
				<?php foreach ( $summary as $row ) : ?>
					<li><?php echo esc_html( $row['event_type'] . ': ' . $row['total'] ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>
	<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Analytics', 'chat-trigger-embed-for-n8n' ); ?></button></p>
</form>
