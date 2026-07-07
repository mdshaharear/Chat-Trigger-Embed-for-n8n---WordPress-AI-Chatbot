<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$webhook_health = \ChatTriggerEmbedN8n\Helpers::webhook_health( (string) $settings['webhook_url'] );
$asset_js       = \ChatTriggerEmbedN8n\Helpers::asset_path( 'dist/chat-trigger-embed.js' );
$asset_css      = \ChatTriggerEmbedN8n\Helpers::asset_path( 'dist/chat-trigger-embed.css' );
$theme          = wp_get_theme();
$resolved       = \ChatTriggerEmbedN8n\Profiles::resolve( $settings );
$profile_report = \ChatTriggerEmbedN8n\Profiles::resolution_report( $settings );
?>
<section class="cten-card">
	<h2><?php esc_html_e( 'Diagnostics', 'chat-trigger-embed-for-n8n' ); ?></h2>
	<p class="description"><?php esc_html_e( 'This report avoids visitor data and masks operational details where possible.', 'chat-trigger-embed-for-n8n' ); ?></p>
	<ul class="cten-stats">
		<li><strong><?php esc_html_e( 'Plugin version', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( CTEN_VERSION ); ?></li>
		<li><strong><?php esc_html_e( 'Database version', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( \ChatTriggerEmbedN8n\Migrations::installed_version() ?: __( 'Not recorded', 'chat-trigger-embed-for-n8n' ) ); ?></li>
		<li><strong><?php esc_html_e( 'Selected profile', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( (string) ( $resolved['resolved_profile_name'] ?? 'Main Website Assistant' ) ); ?></li>
		<li><strong><?php esc_html_e( '@n8n/chat version', 'chat-trigger-embed-for-n8n' ); ?></strong> 1.26.0</li>
		<li><strong><?php esc_html_e( 'WordPress version', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( get_bloginfo( 'version' ) ); ?></li>
		<li><strong><?php esc_html_e( 'PHP version', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( PHP_VERSION ); ?></li>
		<li><strong><?php esc_html_e( 'Active theme', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $theme->exists() ? $theme->get( 'Name' ) : __( 'Unknown', 'chat-trigger-embed-for-n8n' ) ); ?></li>
		<li><strong><?php esc_html_e( 'HTTPS status', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( is_ssl() ? __( 'HTTPS', 'chat-trigger-embed-for-n8n' ) : __( 'Not HTTPS', 'chat-trigger-embed-for-n8n' ) ); ?></li>
		<li><strong><?php esc_html_e( 'Website origin', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( \ChatTriggerEmbedN8n\Helpers::get_origin() ); ?></li>
		<li><strong><?php esc_html_e( 'Webhook status', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $webhook_health['label'] ); ?></li>
		<li><strong><?php esc_html_e( 'Previous session', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $settings['load_previous_session'] ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></li>
		<li><strong><?php esc_html_e( 'Streaming', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $settings['enable_streaming'] ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></li>
		<li><strong><?php esc_html_e( 'Public JS present', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( file_exists( $asset_js ) ? __( 'Yes', 'chat-trigger-embed-for-n8n' ) : __( 'No', 'chat-trigger-embed-for-n8n' ) ); ?></li>
		<li><strong><?php esc_html_e( 'Public CSS present', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( file_exists( $asset_css ) ? __( 'Yes', 'chat-trigger-embed-for-n8n' ) : __( 'No', 'chat-trigger-embed-for-n8n' ) ); ?></li>
		<li><strong><?php esc_html_e( 'Analytics table', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( \ChatTriggerEmbedN8n\Analytics::table_exists() ? __( 'Present', 'chat-trigger-embed-for-n8n' ) : __( 'Not present', 'chat-trigger-embed-for-n8n' ) ); ?></li>
		<li><strong><?php esc_html_e( 'Runtime lazy-load setting', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( $settings['lazy_load_runtime'] ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></li>
	</ul>
	<h3><?php esc_html_e( 'Profile Resolution', 'chat-trigger-embed-for-n8n' ); ?></h3>
	<ul class="cten-checklist">
		<?php foreach ( $profile_report as $profile_row ) : ?>
			<li class="<?php echo esc_attr( ! empty( $profile_row['matches'] ) ? 'is-done' : 'is-pending' ); ?>">
				<?php
				echo esc_html(
					sprintf(
						'%1$s (%2$s) - priority %3$d - %4$s%5$s',
						$profile_row['name'],
						$profile_row['id'],
						$profile_row['priority'],
						! empty( $profile_row['matches'] ) ? __( 'matched', 'chat-trigger-embed-for-n8n' ) : __( 'not matched', 'chat-trigger-embed-for-n8n' ),
						! empty( $profile_row['default'] ) ? ' - ' . __( 'default fallback', 'chat-trigger-embed-for-n8n' ) : ''
					)
				);
				?>
			</li>
		<?php endforeach; ?>
	</ul>
	<p><button type="button" class="button" data-cten-copy-diagnostics><?php esc_html_e( 'Copy Diagnostics Report', 'chat-trigger-embed-for-n8n' ); ?></button></p>
</section>

<section class="cten-card">
	<h2><?php esc_html_e( 'Safe Configuration Check', 'chat-trigger-embed-for-n8n' ); ?></h2>
	<ul class="cten-checklist">
		<li class="<?php echo esc_attr( ! empty( $settings['enabled'] ) ? 'is-done' : 'is-pending' ); ?>"><?php esc_html_e( 'Chatbot enabled intentionally', 'chat-trigger-embed-for-n8n' ); ?></li>
		<li class="<?php echo esc_attr( 'ok' === $webhook_health['state'] ? 'is-done' : 'is-pending' ); ?>"><?php esc_html_e( 'Production webhook URL shape', 'chat-trigger-embed-for-n8n' ); ?></li>
		<li class="<?php echo esc_attr( file_exists( $asset_js ) && file_exists( $asset_css ) ? 'is-done' : 'is-pending' ); ?>"><?php esc_html_e( 'Compiled assets present', 'chat-trigger-embed-for-n8n' ); ?></li>
	</ul>
</section>
