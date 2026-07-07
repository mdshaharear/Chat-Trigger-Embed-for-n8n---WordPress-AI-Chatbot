<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
	<?php wp_nonce_field( 'cten_save_settings', 'cten_settings_nonce' ); ?>
	<input type="hidden" name="action" value="cten_save_settings">
	<section class="cten-card">
		<h2><?php esc_html_e( 'Visibility', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<?php cten_render_select( __( 'Site Scope', 'chat-trigger-embed-for-n8n' ), 'visibility[scope]', array( 'entire_site' => 'Entire Site', 'homepage_only' => 'Homepage Only', 'selected_pages' => 'Selected Pages', 'excluded_pages' => 'Excluded Pages', 'selected_post_types' => 'Selected Post Types' ), (string) $settings['visibility']['scope'] ); ?>
		<?php cten_render_text( __( 'Selected Pages', 'chat-trigger-embed-for-n8n' ), 'visibility[selected_pages]', implode( ',', array_map( 'absint', $settings['visibility']['selected_pages'] ) ), 'text', __( 'Comma-separated page IDs.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_text( __( 'Excluded Pages', 'chat-trigger-embed-for-n8n' ), 'visibility[excluded_pages]', implode( ',', array_map( 'absint', $settings['visibility']['excluded_pages'] ) ), 'text', __( 'Comma-separated page IDs.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_text( __( 'Selected Post Types', 'chat-trigger-embed-for-n8n' ), 'visibility[selected_types]', implode( ',', array_map( 'sanitize_key', $settings['visibility']['selected_types'] ) ), 'text', __( 'Comma-separated post type slugs.', 'chat-trigger-embed-for-n8n' ) ); ?>
		<?php cten_render_select( __( 'Visitor Type', 'chat-trigger-embed-for-n8n' ), 'visibility[auth]', array( 'all' => 'All visitors', 'logged_in' => 'Logged-in visitors', 'logged_out' => 'Logged-out visitors' ), (string) $settings['visibility']['auth'] ); ?>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Device Targeting', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<?php cten_render_checkbox( __( 'Desktop', 'chat-trigger-embed-for-n8n' ), 'visibility[devices][desktop]', in_array( 'desktop', $settings['visibility']['devices'], true ) ); ?>
		<?php cten_render_checkbox( __( 'Tablet', 'chat-trigger-embed-for-n8n' ), 'visibility[devices][tablet]', in_array( 'tablet', $settings['visibility']['devices'], true ) ); ?>
		<?php cten_render_checkbox( __( 'Mobile', 'chat-trigger-embed-for-n8n' ), 'visibility[devices][mobile]', in_array( 'mobile', $settings['visibility']['devices'], true ) ); ?>
	</section>
	<section class="cten-card">
		<h2><?php esc_html_e( 'Page-aware Metadata', 'chat-trigger-embed-for-n8n' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Only enabled fields are sent. Unknown query strings, cookies, nonces, tokens, and private profile data are never included by this plugin.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php
		$metadata_labels = array(
			'page_title'     => __( 'Page title', 'chat-trigger-embed-for-n8n' ),
			'page_url'       => __( 'Page URL with safe UTM parameters only', 'chat-trigger-embed-for-n8n' ),
			'page_path'      => __( 'Page path', 'chat-trigger-embed-for-n8n' ),
			'referrer'       => __( 'Browser referrer', 'chat-trigger-embed-for-n8n' ),
			'browser_lang'   => __( 'Browser language', 'chat-trigger-embed-for-n8n' ),
			'browser_tz'     => __( 'Browser timezone', 'chat-trigger-embed-for-n8n' ),
			'utm_source'     => __( 'UTM source', 'chat-trigger-embed-for-n8n' ),
			'utm_medium'     => __( 'UTM medium', 'chat-trigger-embed-for-n8n' ),
			'utm_campaign'   => __( 'UTM campaign', 'chat-trigger-embed-for-n8n' ),
			'utm_content'    => __( 'UTM content', 'chat-trigger-embed-for-n8n' ),
			'industry'       => __( 'Industry parameter', 'chat-trigger-embed-for-n8n' ),
			'post_id'        => __( 'WordPress post ID', 'chat-trigger-embed-for-n8n' ),
			'post_type'      => __( 'WordPress post type', 'chat-trigger-embed-for-n8n' ),
			'plugin_version' => __( 'Plugin version', 'chat-trigger-embed-for-n8n' ),
			'theme_name'     => __( 'Theme name', 'chat-trigger-embed-for-n8n' ),
		);
		foreach ( $metadata_labels as $key => $label ) :
			cten_render_checkbox( $label, 'metadata_fields[' . $key . ']', ! empty( $settings['metadata_fields'][ $key ] ) );
		endforeach;
		?>
	</section>
	<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Visibility', 'chat-trigger-embed-for-n8n' ); ?></button></p>
</form>
