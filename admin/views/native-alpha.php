<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ChatTriggerEmbedN8n\V2\Chatbot_Repository;
use ChatTriggerEmbedN8n\V2\Native_Core;
use ChatTriggerEmbedN8n\V2\Provider_Connection_Repository;

$connection_repo = new Provider_Connection_Repository();
$chatbot_repo    = new Chatbot_Repository();

$connections = $connection_repo->all();
$chatbots    = $chatbot_repo->all();
$current     = $current_page ?? 'cten-chatbots';
$editing_connection_id = isset( $_GET['edit_connection'] ) ? sanitize_key( wp_unslash( $_GET['edit_connection'] ) ) : '';
$editing_chatbot_id    = isset( $_GET['edit_chatbot'] ) ? sanitize_key( wp_unslash( $_GET['edit_chatbot'] ) ) : '';
$editing_connection    = $editing_connection_id ? $connection_repo->get( $editing_connection_id ) : null;
$editing_chatbot       = $editing_chatbot_id ? $chatbot_repo->get( $editing_chatbot_id ) : null;
$editing_connection    = is_array( $editing_connection ) ? $editing_connection : array();
$editing_chatbot       = is_array( $editing_chatbot ) ? $editing_chatbot : array();

$provider_options = array(
	'openai' => 'OpenAI',
	'gemini' => 'Gemini',
	'n8n'    => 'n8n',
	'mock'   => 'Mock',
);

$secret_source_options = array(
	'none'             => __( 'No secret', 'chat-trigger-embed-for-n8n' ),
	'constant'         => __( 'Stored constant', 'chat-trigger-embed-for-n8n' ),
	'environment'      => __( 'Environment variable name', 'chat-trigger-embed-for-n8n' ),
	'encrypted_option'  => __( 'Encrypted option', 'chat-trigger-embed-for-n8n' ),
);

$visibility_options = array(
	'entire_site'    => __( 'Entire site', 'chat-trigger-embed-for-n8n' ),
	'homepage'       => __( 'Homepage only', 'chat-trigger-embed-for-n8n' ),
	'selected_pages' => __( 'Selected pages', 'chat-trigger-embed-for-n8n' ),
	'excluded_pages'  => __( 'Excluded pages', 'chat-trigger-embed-for-n8n' ),
);

$default_quick_actions = array(
	array( 'id' => 'qa-1', 'enabled' => true, 'label' => '', 'message' => '', 'sort' => 10 ),
	array( 'id' => 'qa-2', 'enabled' => false, 'label' => '', 'message' => '', 'sort' => 20 ),
	array( 'id' => 'qa-3', 'enabled' => false, 'label' => '', 'message' => '', 'sort' => 30 ),
	array( 'id' => 'qa-4', 'enabled' => false, 'label' => '', 'message' => '', 'sort' => 40 ),
);

$connection_quick = array(
	'id' => '',
	'name' => '',
	'provider' => 'mock',
	'enabled' => true,
	'secret_source' => 'none',
	'secret_value' => '',
	'project_id' => '',
	'organization_id' => '',
	'default_model' => '',
	'timeout' => 30,
);

$chatbot_quick = array(
	'id' => '',
	'name' => '',
	'internal_name' => '',
	'enabled' => true,
	'engine' => 'mock',
	'provider_connection_id' => '',
	'model_id' => '',
	'system_instructions' => '',
	'welcome_message' => '',
	'input_placeholder' => '',
	'error_message' => '',
	'static_fallback_message' => '',
	'quick_actions' => $default_quick_actions,
	'theme_preset' => 'premium-glass',
	'launcher_label' => '',
	'page_visibility_mode' => 'entire_site',
	'selected_page_ids' => array(),
	'maximum_input_characters' => 1000,
	'maximum_output_tokens' => 256,
	'messages_per_session' => 50,
	'requests_per_minute' => 30,
	'daily_request_limit' => 0,
);

$connection_values = wp_parse_args( $editing_connection, $connection_quick );
$chatbot_values    = wp_parse_args( $editing_chatbot, $chatbot_quick );

$connection_secret_hint = ! empty( $editing_connection['secret_value'] ) ? $connection_repo->mask_secret( $editing_connection ) : '';

function cten_native_hidden( string $name, string $value ): void {
	printf(
		'<input type="hidden" name="%1$s" value="%2$s">',
		esc_attr( $name ),
		esc_attr( $value )
	);
}

function cten_native_quick_action_rows( array $actions ): void {
	for ( $index = 0; $index < 4; $index++ ) {
		$action = $actions[ $index ] ?? array();
		?>
		<tr>
			<td><label><input type="checkbox" name="quick_actions[<?php echo esc_attr( (string) $index ); ?>][enabled]" value="1" <?php checked( ! empty( $action['enabled'] ) ); ?>></label></td>
			<td><input type="hidden" name="quick_actions[<?php echo esc_attr( (string) $index ); ?>][id]" value="<?php echo esc_attr( (string) ( $action['id'] ?? 'qa-' . ( $index + 1 ) ) ); ?>"><input type="text" class="regular-text" name="quick_actions[<?php echo esc_attr( (string) $index ); ?>][label]" value="<?php echo esc_attr( (string) ( $action['label'] ?? '' ) ); ?>"></td>
			<td><input type="text" class="regular-text" name="quick_actions[<?php echo esc_attr( (string) $index ); ?>][message]" value="<?php echo esc_attr( (string) ( $action['message'] ?? '' ) ); ?>"></td>
			<td><input type="number" name="quick_actions[<?php echo esc_attr( (string) $index ); ?>][sort]" value="<?php echo esc_attr( (string) ( $action['sort'] ?? ( $index + 1 ) * 10 ) ); ?>" min="0" max="999"></td>
		</tr>
		<?php
	}
}
?>
<div class="cten-grid">
	<section class="cten-card">
		<h2><?php echo esc_html( 'cten-chatbots' === $current ? __( 'Native Chatbots', 'chat-trigger-embed-for-n8n' ) : __( 'AI Provider Connections', 'chat-trigger-embed-for-n8n' ) ); ?></h2>
		<p class="description"><?php esc_html_e( 'These screens let you create native AI chatbots and their provider connections while preserving the legacy n8n settings elsewhere in the plugin.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php if ( 'cten-chatbots' === $current ) : ?>
			<p><?php esc_html_e( 'Each chatbot can route to OpenAI, Gemini, n8n, or the mock provider. Visibility rules decide where the native UI appears.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'Create a provider connection first so the chatbot can reference a stored secret and a default model.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php endif; ?>
	</section>
</div>

<div class="cten-grid">
	<section class="cten-card">
		<h3><?php esc_html_e( 'Provider Connections', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Secrets stay server-side. Use encrypted storage when available, or an environment variable name if you prefer infrastructure-managed credentials.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<form class="cten-form cten-inline-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'cten_v2_connection', 'cten_v2_connection_nonce' ); ?>
			<input type="hidden" name="action" value="cten_v2_save_connection">
			<?php cten_native_hidden( 'id', (string) $connection_values['id'] ); ?>
			<?php cten_render_text( __( 'Connection Name', 'chat-trigger-embed-for-n8n' ), 'name', (string) $connection_values['name'], 'text', __( 'A friendly label for this provider connection.', 'chat-trigger-embed-for-n8n' ), 'connection' ); ?>
			<?php cten_render_select( __( 'Provider', 'chat-trigger-embed-for-n8n' ), 'provider', $provider_options, (string) $connection_values['provider'], __( 'Choose which provider adapter will use this connection.', 'chat-trigger-embed-for-n8n' ), 'connection' ); ?>
			<?php cten_render_select( __( 'Secret Storage', 'chat-trigger-embed-for-n8n' ), 'secret_source', $secret_source_options, (string) $connection_values['secret_source'], __( 'The secret is only used by the selected provider.', 'chat-trigger-embed-for-n8n' ), 'connection' ); ?>
			<?php cten_render_text( __( 'Secret / Value', 'chat-trigger-embed-for-n8n' ), 'secret_value', '', 'text', $connection_secret_hint ? sprintf( __( 'Current value is stored securely. Saved secret looks like %s', 'chat-trigger-embed-for-n8n' ), $connection_secret_hint ) : __( 'Enter the API key, webhook URL, or environment variable name.', 'chat-trigger-embed-for-n8n' ), 'connection' ); ?>
			<?php cten_render_text( __( 'Default Model', 'chat-trigger-embed-for-n8n' ), 'default_model', (string) $connection_values['default_model'], 'text', __( 'For example: gpt-4.1-mini or gemini-2.0-flash.', 'chat-trigger-embed-for-n8n' ), 'connection' ); ?>
			<?php cten_render_text( __( 'Project ID', 'chat-trigger-embed-for-n8n' ), 'project_id', (string) $connection_values['project_id'], 'text', '', 'connection' ); ?>
			<?php cten_render_text( __( 'Organization ID', 'chat-trigger-embed-for-n8n' ), 'organization_id', (string) $connection_values['organization_id'], 'text', '', 'connection' ); ?>
			<?php cten_render_number( __( 'Timeout (seconds)', 'chat-trigger-embed-for-n8n' ), 'timeout', (int) $connection_values['timeout'], '', 5, 120, 'connection' ); ?>
			<?php cten_render_checkbox( __( 'Enabled', 'chat-trigger-embed-for-n8n' ), 'enabled', (bool) $connection_values['enabled'], __( 'Disabled connections remain saved but cannot be used by live chatbots.', 'chat-trigger-embed-for-n8n' ), 'connection' ); ?>
			<p>
				<button class="button button-primary" type="submit"><?php echo esc_html( $editing_connection_id ? __( 'Update Connection', 'chat-trigger-embed-for-n8n' ) : __( 'Create Connection', 'chat-trigger-embed-for-n8n' ) ); ?></button>
				<?php if ( $editing_connection_id ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-ai-providers' ) ); ?>"><?php esc_html_e( 'New Connection', 'chat-trigger-embed-for-n8n' ); ?></a>
				<?php endif; ?>
			</p>
		</form>

		<?php if ( $connections ) : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'chat-trigger-embed-for-n8n' ); ?></th>
						<th><?php esc_html_e( 'Provider', 'chat-trigger-embed-for-n8n' ); ?></th>
						<th><?php esc_html_e( 'Model', 'chat-trigger-embed-for-n8n' ); ?></th>
						<th><?php esc_html_e( 'Status', 'chat-trigger-embed-for-n8n' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'chat-trigger-embed-for-n8n' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $connections as $connection ) : ?>
						<tr>
							<td><?php echo esc_html( (string) $connection['name'] ); ?></td>
							<td><?php echo esc_html( (string) ( $provider_options[ (string) $connection['provider'] ] ?? (string) $connection['provider'] ) ); ?></td>
							<td><?php echo esc_html( (string) $connection['default_model'] ); ?></td>
							<td><?php echo esc_html( (string) ( $connection['last_test_status'] ?: __( 'Not tested', 'chat-trigger-embed-for-n8n' ) ) ); ?></td>
							<td>
								<a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-ai-providers&edit_connection=' . rawurlencode( (string) $connection['id'] ) ) ); ?>"><?php esc_html_e( 'Edit', 'chat-trigger-embed-for-n8n' ); ?></a>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block">
									<?php wp_nonce_field( 'cten_v2_test_connection', 'cten_v2_connection_nonce' ); ?>
									<input type="hidden" name="action" value="cten_v2_test_connection">
									<input type="hidden" name="id" value="<?php echo esc_attr( (string) $connection['id'] ); ?>">
									<button class="button button-small" type="submit"><?php esc_html_e( 'Test', 'chat-trigger-embed-for-n8n' ); ?></button>
								</form>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this connection?', 'chat-trigger-embed-for-n8n' ) ); ?>');">
									<?php wp_nonce_field( 'cten_v2_delete_connection', 'cten_v2_connection_nonce' ); ?>
									<input type="hidden" name="action" value="cten_v2_delete_connection">
									<input type="hidden" name="id" value="<?php echo esc_attr( (string) $connection['id'] ); ?>">
									<button class="button button-small" type="submit"><?php esc_html_e( 'Delete', 'chat-trigger-embed-for-n8n' ); ?></button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php esc_html_e( 'No provider connections yet.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php endif; ?>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Chatbot Builder', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<p class="description"><?php esc_html_e( 'This is the native MVP builder. Each chatbot points to one saved provider connection and can show on the entire site or only selected pages.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<form class="cten-form cten-inline-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'cten_v2_chatbot', 'cten_v2_chatbot_nonce' ); ?>
			<input type="hidden" name="action" value="cten_v2_save_chatbot">
			<?php cten_native_hidden( 'id', (string) $chatbot_values['id'] ); ?>
			<?php cten_render_text( __( 'Chatbot Name', 'chat-trigger-embed-for-n8n' ), 'name', (string) $chatbot_values['name'], 'text', __( 'Displayed to visitors and in the admin list.', 'chat-trigger-embed-for-n8n' ), 'chatbot' ); ?>
			<?php cten_render_text( __( 'Internal Name', 'chat-trigger-embed-for-n8n' ), 'internal_name', (string) $chatbot_values['internal_name'], 'text', __( 'A stable slug used internally and in exports.', 'chat-trigger-embed-for-n8n' ), 'chatbot' ); ?>
			<?php cten_render_select( __( 'Engine', 'chat-trigger-embed-for-n8n' ), 'engine', $provider_options, (string) $chatbot_values['engine'], __( 'Select the provider that will answer this chatbot.', 'chat-trigger-embed-for-n8n' ), 'chatbot' ); ?>
			<?php cten_render_select( __( 'Provider Connection', 'chat-trigger-embed-for-n8n' ), 'provider_connection_id', array_merge( array( '' => __( 'Select a connection', 'chat-trigger-embed-for-n8n' ) ), array_reduce( $connections, static function ( array $carry, array $item ): array { $carry[ (string) $item['id'] ] = (string) $item['name']; return $carry; }, array() ) ), (string) $chatbot_values['provider_connection_id'], __( 'The secret and model live in the selected connection.', 'chat-trigger-embed-for-n8n' ), 'chatbot' ); ?>
			<?php cten_render_text( __( 'Model ID', 'chat-trigger-embed-for-n8n' ), 'model_id', (string) $chatbot_values['model_id'], 'text', __( 'For example: gpt-4.1-mini or gemini-2.0-flash.', 'chat-trigger-embed-for-n8n' ), 'chatbot' ); ?>
			<?php cten_render_textarea( __( 'System Instructions', 'chat-trigger-embed-for-n8n' ), 'system_instructions', (string) $chatbot_values['system_instructions'], __( 'This is sent to the provider as the system prompt.', 'chat-trigger-embed-for-n8n' ), 'chatbot' ); ?>
			<?php cten_render_textarea( __( 'Welcome Message', 'chat-trigger-embed-for-n8n' ), 'welcome_message', (string) $chatbot_values['welcome_message'], __( 'Shown when the conversation opens or when the provider returns nothing.', 'chat-trigger-embed-for-n8n' ), 'chatbot' ); ?>
			<?php cten_render_text( __( 'Input Placeholder', 'chat-trigger-embed-for-n8n' ), 'input_placeholder', (string) $chatbot_values['input_placeholder'], 'text', '', 'chatbot' ); ?>
			<?php cten_render_text( __( 'Error Message', 'chat-trigger-embed-for-n8n' ), 'error_message', (string) $chatbot_values['error_message'], 'text', '', 'chatbot' ); ?>
			<?php cten_render_text( __( 'Fallback Message', 'chat-trigger-embed-for-n8n' ), 'static_fallback_message', (string) $chatbot_values['static_fallback_message'], 'text', '', 'chatbot' ); ?>
			<?php cten_render_select( __( 'Theme Preset', 'chat-trigger-embed-for-n8n' ), 'theme_preset', array( 'premium-glass' => 'Premium Glass', 'clean-light' => 'Clean Light', 'brand-purple' => 'Brand Purple', 'high-contrast' => 'High Contrast', 'midnight-blue' => 'Midnight Blue' ), (string) $chatbot_values['theme_preset'], __( 'The preset controls the native chat palette.', 'chat-trigger-embed-for-n8n' ), 'chatbot' ); ?>
			<?php cten_render_text( __( 'Launcher Label', 'chat-trigger-embed-for-n8n' ), 'launcher_label', (string) $chatbot_values['launcher_label'], 'text', '', 'chatbot' ); ?>
			<?php cten_render_select( __( 'Visibility', 'chat-trigger-embed-for-n8n' ), 'page_visibility_mode', $visibility_options, (string) $chatbot_values['page_visibility_mode'], __( 'Choose where this chatbot can render.', 'chat-trigger-embed-for-n8n' ), 'chatbot' ); ?>
			<?php cten_render_text( __( 'Selected Page IDs', 'chat-trigger-embed-for-n8n' ), 'selected_page_ids', implode( ',', array_map( 'absint', (array) $chatbot_values['selected_page_ids'] ) ), 'text', __( 'Comma-separated page IDs used when visibility is set to selected pages or excluded pages.', 'chat-trigger-embed-for-n8n' ), 'chatbot' ); ?>
			<?php cten_render_number( __( 'Max input characters', 'chat-trigger-embed-for-n8n' ), 'maximum_input_characters', (int) $chatbot_values['maximum_input_characters'], '', 50, 10000, 'chatbot' ); ?>
			<?php cten_render_number( __( 'Max output tokens', 'chat-trigger-embed-for-n8n' ), 'maximum_output_tokens', (int) $chatbot_values['maximum_output_tokens'], '', 16, 4096, 'chatbot' ); ?>
			<?php cten_render_number( __( 'Messages per session', 'chat-trigger-embed-for-n8n' ), 'messages_per_session', (int) $chatbot_values['messages_per_session'], '', 1, 500, 'chatbot' ); ?>
			<?php cten_render_number( __( 'Requests per minute', 'chat-trigger-embed-for-n8n' ), 'requests_per_minute', (int) $chatbot_values['requests_per_minute'], '', 1, 500, 'chatbot' ); ?>
			<?php cten_render_number( __( 'Daily request limit', 'chat-trigger-embed-for-n8n' ), 'daily_request_limit', (int) $chatbot_values['daily_request_limit'], __( 'Set to 0 for no daily limit.', 'chat-trigger-embed-for-n8n' ), 0, 100000, 'chatbot' ); ?>
			<?php cten_render_checkbox( __( 'Enabled', 'chat-trigger-embed-for-n8n' ), 'enabled', (bool) $chatbot_values['enabled'], __( 'Disabled chatbots stay saved but do not render on the site.', 'chat-trigger-embed-for-n8n' ), 'chatbot' ); ?>

			<h4><?php esc_html_e( 'Quick Actions', 'chat-trigger-embed-for-n8n' ); ?></h4>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Enabled', 'chat-trigger-embed-for-n8n' ); ?></th>
						<th><?php esc_html_e( 'Label', 'chat-trigger-embed-for-n8n' ); ?></th>
						<th><?php esc_html_e( 'Message', 'chat-trigger-embed-for-n8n' ); ?></th>
						<th><?php esc_html_e( 'Sort', 'chat-trigger-embed-for-n8n' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php cten_native_quick_action_rows( (array) $chatbot_values['quick_actions'] ); ?>
				</tbody>
			</table>
			<p>
				<button class="button button-primary" type="submit"><?php echo esc_html( $editing_chatbot_id ? __( 'Update Chatbot', 'chat-trigger-embed-for-n8n' ) : __( 'Create Chatbot', 'chat-trigger-embed-for-n8n' ) ); ?></button>
				<?php if ( $editing_chatbot_id ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-chatbots' ) ); ?>"><?php esc_html_e( 'New Chatbot', 'chat-trigger-embed-for-n8n' ); ?></a>
				<?php endif; ?>
			</p>
		</form>

		<?php if ( $chatbots ) : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'chat-trigger-embed-for-n8n' ); ?></th>
						<th><?php esc_html_e( 'Engine', 'chat-trigger-embed-for-n8n' ); ?></th>
						<th><?php esc_html_e( 'Visibility', 'chat-trigger-embed-for-n8n' ); ?></th>
						<th><?php esc_html_e( 'Status', 'chat-trigger-embed-for-n8n' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'chat-trigger-embed-for-n8n' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $chatbots as $chatbot ) : ?>
						<tr>
							<td><?php echo esc_html( (string) $chatbot['name'] ); ?></td>
							<td><?php echo esc_html( (string) ( $provider_options[ (string) $chatbot['engine'] ] ?? (string) $chatbot['engine'] ) ); ?></td>
							<td><?php echo esc_html( (string) $chatbot['page_visibility_mode'] ); ?></td>
							<td><?php echo esc_html( ! empty( $chatbot['enabled'] ) ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></td>
							<td>
								<a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-chatbots&edit_chatbot=' . rawurlencode( (string) $chatbot['id'] ) ) ); ?>"><?php esc_html_e( 'Edit', 'chat-trigger-embed-for-n8n' ); ?></a>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this chatbot?', 'chat-trigger-embed-for-n8n' ) ); ?>');">
									<?php wp_nonce_field( 'cten_v2_delete_chatbot', 'cten_v2_chatbot_nonce' ); ?>
									<input type="hidden" name="action" value="cten_v2_delete_chatbot">
									<input type="hidden" name="id" value="<?php echo esc_attr( (string) $chatbot['id'] ); ?>">
									<button class="button button-small" type="submit"><?php esc_html_e( 'Delete', 'chat-trigger-embed-for-n8n' ); ?></button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php esc_html_e( 'No native chatbots yet.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<?php endif; ?>
	</section>
</div>

<div class="cten-grid">
	<section class="cten-card">
		<h3><?php esc_html_e( 'Next Step', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<p><?php esc_html_e( 'After saving a chatbot, open the public site to confirm that the native launcher appears and that the REST gateway returns responses from your selected provider.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=cten-runtime-lab' ) ); ?>"><?php esc_html_e( 'Open Runtime Lab', 'chat-trigger-embed-for-n8n' ); ?></a></p>
	</section>
</div>
