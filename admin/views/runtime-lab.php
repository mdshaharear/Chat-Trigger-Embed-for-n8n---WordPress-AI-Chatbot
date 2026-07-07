<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings        = \ChatTriggerEmbedN8n\Helpers::get_settings();
$safe_mode       = \ChatTriggerEmbedN8n\Safe_Mode::get();
$runtime_state   = \ChatTriggerEmbedN8n\Runtime_Lab::get_test_state();
$report          = \ChatTriggerEmbedN8n\Runtime_Lab::sanitized_report();
$context_preview = \ChatTriggerEmbedN8n\Profiles::resolve( $settings );
$results         = $report['testResults'];
$runtime_test_url = ! empty( $runtime_state['token'] ) ? add_query_arg( \ChatTriggerEmbedN8n\Runtime_Lab::QUERY_TOKEN, rawurlencode( (string) $runtime_state['token'] ), home_url( '/' ) ) : home_url( '/' );
?>
<div class="cten-runtime-lab">
	<h2><?php esc_html_e( 'Runtime Lab', 'chat-trigger-embed-for-n8n' ); ?></h2>
	<p class="description"><?php esc_html_e( 'Administrator-only lab for safe runtime verification, report export, mock sandboxing, and recovery actions.', 'chat-trigger-embed-for-n8n' ); ?></p>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Environment', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<ul class="cten-stats">
			<li><strong><?php esc_html_e( 'WordPress version', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( get_bloginfo( 'version' ) ); ?></li>
			<li><strong><?php esc_html_e( 'PHP version', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( PHP_VERSION ); ?></li>
			<li><strong><?php esc_html_e( 'Timezone', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( wp_timezone_string() ); ?></li>
			<li><strong><?php esc_html_e( 'Home URL', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( home_url() ); ?></li>
			<li><strong><?php esc_html_e( 'HTTPS', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( is_ssl() ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Safe mode', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( ! empty( $safe_mode['enabled'] ) ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></li>
		</ul>
		<button type="button" class="button button-primary" data-cten-run-tests data-cten-category="all"><?php esc_html_e( 'Run All Safe Tests', 'chat-trigger-embed-for-n8n' ); ?></button>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Plugin Files', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<p><?php esc_html_e( 'Verifies compiled assets, license files, and the release ZIP shape without executing arbitrary code.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<button type="button" class="button" data-cten-run-tests data-cten-category="plugin_files"><?php esc_html_e( 'Run Plugin File Tests', 'chat-trigger-embed-for-n8n' ); ?></button>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Database', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<ul class="cten-stats">
			<li><strong><?php esc_html_e( 'Stored DB version', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( \ChatTriggerEmbedN8n\Migrations::installed_version() ?: __( 'Not recorded', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Analytics table', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( \ChatTriggerEmbedN8n\Analytics::table_exists() ? __( 'Present', 'chat-trigger-embed-for-n8n' ) : __( 'Missing', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Migration backup', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( get_option( \ChatTriggerEmbedN8n\Migrations::BACKUP_OPTION, array() ) ? __( 'Available', 'chat-trigger-embed-for-n8n' ) : __( 'Not available', 'chat-trigger-embed-for-n8n' ) ); ?></li>
		</ul>
		<div class="cten-actions">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'cten_runtime_lab_action', 'cten_runtime_lab_nonce' ); ?>
				<input type="hidden" name="action" value="cten_runtime_lab_action">
				<input type="hidden" name="cten_runtime_lab_action_name" value="run_migrations">
				<button type="submit" class="button"><?php esc_html_e( 'Re-run Pending Migrations', 'chat-trigger-embed-for-n8n' ); ?></button>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'cten_runtime_lab_action', 'cten_runtime_lab_nonce' ); ?>
				<input type="hidden" name="action" value="cten_runtime_lab_action">
				<input type="hidden" name="cten_runtime_lab_action_name" value="clear_transients">
				<button type="submit" class="button"><?php esc_html_e( 'Clear Plugin Transients', 'chat-trigger-embed-for-n8n' ); ?></button>
			</form>
		</div>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Configuration', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<ul class="cten-stats">
			<li><strong><?php esc_html_e( 'Enabled', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( ! empty( $settings['enabled'] ) ? __( 'Yes', 'chat-trigger-embed-for-n8n' ) : __( 'No', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Webhook', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( ! empty( $settings['webhook_url'] ) ? __( 'Configured', 'chat-trigger-embed-for-n8n' ) : __( 'Missing', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Lazy load', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( ! empty( $settings['lazy_load_runtime'] ) ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Auto-open', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( ! empty( $settings['auto_open_enabled'] ) ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Default profile', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( (string) ( $context_preview['resolved_profile_name'] ?? '' ) ); ?></li>
		</ul>
		<button type="button" class="button" data-cten-run-tests data-cten-category="configuration"><?php esc_html_e( 'Run Configuration Tests', 'chat-trigger-embed-for-n8n' ); ?></button>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Profile Resolution', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<p><?php esc_html_e( 'Use the same production resolver with simulated page and campaign context.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<form class="cten-form" data-cten-profile-simulator>
			<?php wp_nonce_field( 'cten_runtime_lab_action', 'cten_runtime_lab_nonce' ); ?>
			<div class="cten-grid">
				<?php cten_render_text( __( 'Page URL', 'chat-trigger-embed-for-n8n' ), 'runtime_context[page_url]', home_url( '/' ) ); ?>
				<?php cten_render_text( __( 'Page Path', 'chat-trigger-embed-for-n8n' ), 'runtime_context[page_path]', '/' ); ?>
				<?php cten_render_text( __( 'Post ID', 'chat-trigger-embed-for-n8n' ), 'runtime_context[post_id]', '0', 'number' ); ?>
				<?php cten_render_text( __( 'Post Type', 'chat-trigger-embed-for-n8n' ), 'runtime_context[post_type]', 'page' ); ?>
				<?php cten_render_text( __( 'Category', 'chat-trigger-embed-for-n8n' ), 'runtime_context[category]', '' ); ?>
				<?php cten_render_text( __( 'Tag', 'chat-trigger-embed-for-n8n' ), 'runtime_context[tag]', '' ); ?>
				<?php cten_render_text( __( 'Referrer', 'chat-trigger-embed-for-n8n' ), 'runtime_context[referrer]', '' ); ?>
				<?php cten_render_select( __( 'Device', 'chat-trigger-embed-for-n8n' ), 'runtime_context[device]', array( 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile' ), 'desktop' ); ?>
				<?php cten_render_select( __( 'Logged-in State', 'chat-trigger-embed-for-n8n' ), 'runtime_context[logged_in]', array( '1' => 'Logged in', '0' => 'Logged out' ), '0' ); ?>
				<?php cten_render_text( __( 'User Role', 'chat-trigger-embed-for-n8n' ), 'runtime_context[user_role]', '' ); ?>
				<?php cten_render_text( __( 'UTM Source', 'chat-trigger-embed-for-n8n' ), 'runtime_context[utm_source]', '' ); ?>
				<?php cten_render_text( __( 'UTM Medium', 'chat-trigger-embed-for-n8n' ), 'runtime_context[utm_medium]', '' ); ?>
				<?php cten_render_text( __( 'UTM Campaign', 'chat-trigger-embed-for-n8n' ), 'runtime_context[utm_campaign]', '' ); ?>
				<?php cten_render_text( __( 'UTM Content', 'chat-trigger-embed-for-n8n' ), 'runtime_context[utm_content]', '' ); ?>
				<?php cten_render_text( __( 'Industry', 'chat-trigger-embed-for-n8n' ), 'runtime_context[industry]', '' ); ?>
				<?php cten_render_text( __( 'Campaign', 'chat-trigger-embed-for-n8n' ), 'runtime_context[campaign]', '' ); ?>
			</div>
			<p><button type="button" class="button" data-cten-profile-simulate><?php esc_html_e( 'Simulate Profile Resolution', 'chat-trigger-embed-for-n8n' ); ?></button></p>
			<pre class="cten-json" data-cten-profile-output><?php echo esc_html( wp_json_encode( $context_preview, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></pre>
		</form>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Mock Chat', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<p><?php esc_html_e( 'Launch a safe sandbox that never calls n8n and never stores visitor content permanently.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<select data-cten-mock-scenario>
			<option value="success"><?php esc_html_e( 'Success', 'chat-trigger-embed-for-n8n' ); ?></option>
			<option value="delayed_success"><?php esc_html_e( 'Delayed Success', 'chat-trigger-embed-for-n8n' ); ?></option>
			<option value="dynamic_options"><?php esc_html_e( 'Dynamic Options', 'chat-trigger-embed-for-n8n' ); ?></option>
			<option value="lead_status_hot"><?php esc_html_e( 'Lead Status Hot', 'chat-trigger-embed-for-n8n' ); ?></option>
			<option value="empty_response"><?php esc_html_e( 'Empty Response', 'chat-trigger-embed-for-n8n' ); ?></option>
			<option value="invalid_response"><?php esc_html_e( 'Invalid Response', 'chat-trigger-embed-for-n8n' ); ?></option>
			<option value="rate_limited"><?php esc_html_e( 'Rate Limited', 'chat-trigger-embed-for-n8n' ); ?></option>
			<option value="server_error"><?php esc_html_e( 'Server Error', 'chat-trigger-embed-for-n8n' ); ?></option>
			<option value="timeout"><?php esc_html_e( 'Timeout Simulation', 'chat-trigger-embed-for-n8n' ); ?></option>
			<option value="previous_history"><?php esc_html_e( 'Previous History', 'chat-trigger-embed-for-n8n' ); ?></option>
			<option value="expired_session"><?php esc_html_e( 'Expired Session', 'chat-trigger-embed-for-n8n' ); ?></option>
		</select>
		<button type="button" class="button button-primary" data-cten-run-mock><?php esc_html_e( 'Run Mock Chat', 'chat-trigger-embed-for-n8n' ); ?></button>
		<button type="button" class="button" data-cten-download-mock-report><?php esc_html_e( 'Download Mock Test Report', 'chat-trigger-embed-for-n8n' ); ?></button>
		<div class="cten-runtime-output" data-cten-mock-output></div>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Live n8n Test', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<p class="description"><?php esc_html_e( 'This never runs automatically. It sends one confirmed test message to the stored, validated production webhook URL only.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<ul class="cten-stats">
			<li><strong><?php esc_html_e( 'Masked URL', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( ! empty( $settings['webhook_url'] ) ? preg_replace( '#://[^/@]+@?#', '://***@', (string) $settings['webhook_url'] ) : __( 'Missing', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Test session ID', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( 'cten-live-' . substr( wp_hash( home_url( '/' ) . 'live-test' ), 0, 12 ) ); ?></li>
		</ul>
		<form class="cten-form" data-cten-live-test>
			<?php wp_nonce_field( 'cten_runtime_lab_action', 'cten_runtime_lab_nonce' ); ?>
			<p><label><input type="checkbox" data-cten-live-confirm> <?php esc_html_e( 'I confirm that this test message may be sent to the stored production webhook URL.', 'chat-trigger-embed-for-n8n' ); ?></label></p>
			<p><button type="button" class="button button-primary" data-cten-run-live-test><?php esc_html_e( 'Run Live n8n Test', 'chat-trigger-embed-for-n8n' ); ?></button></p>
		</form>
		<pre class="cten-json" data-cten-live-output></pre>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Frontend Browser Test', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<p><?php esc_html_e( 'Generate a temporary public-page URL for manual browser verification with safe event beaconing.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<p><code data-cten-runtime-test-url><?php echo esc_html( $runtime_test_url ); ?></code></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
			<?php wp_nonce_field( 'cten_runtime_lab_action', 'cten_runtime_lab_nonce' ); ?>
			<input type="hidden" name="action" value="cten_runtime_lab_action">
			<input type="hidden" name="cten_runtime_lab_action_name" value="generate_token">
			<input type="hidden" name="runtime_test_page" value="<?php echo esc_attr( home_url( '/' ) ); ?>">
			<button type="submit" class="button"><?php esc_html_e( 'Generate Runtime Test Token', 'chat-trigger-embed-for-n8n' ); ?></button>
		</form>
		<ul class="cten-stats">
			<?php foreach ( (array) ( $report['runtimeEvents'] ?? array() ) as $event ) : ?>
				<li><?php echo esc_html( sprintf( '%s - %s', $event['timestamp'] ?? '', $event['event'] ?? '' ) ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Analytics', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<ul class="cten-stats">
			<li><strong><?php esc_html_e( 'Analytics status', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( ! empty( $settings['analytics_enabled'] ) ? __( 'Enabled', 'chat-trigger-embed-for-n8n' ) : __( 'Disabled', 'chat-trigger-embed-for-n8n' ) ); ?></li>
			<li><strong><?php esc_html_e( 'Retention days', 'chat-trigger-embed-for-n8n' ); ?></strong> <?php echo esc_html( (string) $settings['analytics_retention_days'] ); ?></li>
		</ul>
		<button type="button" class="button" data-cten-run-tests data-cten-category="analytics"><?php esc_html_e( 'Run Analytics Tests', 'chat-trigger-embed-for-n8n' ); ?></button>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Scheduled Tasks', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<ul class="cten-stats">
			<?php foreach ( (array) $report['cronSummary'] as $cron ) : ?>
				<li><?php echo esc_html( sprintf( '%s - %s - %s', $cron['hook'] ?? '', $cron['next'] ?? '', $cron['status'] ?? '' ) ); ?></li>
			<?php endforeach; ?>
		</ul>
		<div class="cten-actions">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'cten_runtime_lab_action', 'cten_runtime_lab_nonce' ); ?>
				<input type="hidden" name="action" value="cten_runtime_lab_action">
				<input type="hidden" name="cten_runtime_lab_action_name" value="repair_cron">
				<button type="submit" class="button"><?php esc_html_e( 'Repair Missing Plugin Cron Events', 'chat-trigger-embed-for-n8n' ); ?></button>
			</form>
		</div>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Security and Privacy', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<ul class="cten-stats">
			<li><?php esc_html_e( 'Runtime Lab is administrator-only and does not expose public visitor data.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Live tests require a stored production profile URL and a nonce-confirmed button click.', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Mock tests never call n8n and never store cookies or raw messages permanently.', 'chat-trigger-embed-for-n8n' ); ?></li>
		</ul>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cten-form">
			<?php wp_nonce_field( 'cten_runtime_lab_action', 'cten_runtime_lab_nonce' ); ?>
			<input type="hidden" name="action" value="cten_runtime_lab_action">
			<input type="hidden" name="cten_runtime_lab_action_name" value="save_safe_mode">
			<p><label><input type="checkbox" name="safe_mode_enabled" value="1" <?php checked( ! empty( $safe_mode['enabled'] ) ); ?>> <?php esc_html_e( 'Enable Safe Mode', 'chat-trigger-embed-for-n8n' ); ?></label></p>
			<p><label><input type="checkbox" name="safe_mode_auto_enable" value="1" <?php checked( ! empty( $safe_mode['auto_enable'] ) ); ?>> <?php esc_html_e( 'Automatically enable Safe Mode after repeated initialization failures', 'chat-trigger-embed-for-n8n' ); ?></label></p>
			<p><label><?php esc_html_e( 'Failure Threshold', 'chat-trigger-embed-for-n8n' ); ?><br><input type="number" name="safe_mode_failure_threshold" min="2" max="10" value="<?php echo esc_attr( (string) ( $safe_mode['failure_threshold'] ?? 3 ) ); ?>"></label></p>
			<p><label><?php esc_html_e( 'Safe Mode Reason', 'chat-trigger-embed-for-n8n' ); ?><br><input type="text" class="regular-text" name="safe_mode_reason" value="<?php echo esc_attr( (string) ( $safe_mode['reason'] ?? '' ) ); ?>"></label></p>
			<p><label><?php esc_html_e( 'Recovery Instructions', 'chat-trigger-embed-for-n8n' ); ?><br><textarea name="safe_mode_recovery_instructions" rows="4" class="large-text"><?php echo esc_textarea( (string) ( $safe_mode['recovery_instructions'] ?? '' ) ); ?></textarea></label></p>
			<p><label><?php esc_html_e( 'Fallback Link', 'chat-trigger-embed-for-n8n' ); ?><br><input type="url" class="regular-text" name="safe_mode_fallback_link" value="<?php echo esc_attr( (string) ( $safe_mode['fallback_link'] ?? '' ) ); ?>"></label></p>
			<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Safe Mode Settings', 'chat-trigger-embed-for-n8n' ); ?></button></p>
		</form>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Export Report', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<p><?php esc_html_e( 'Exports a sanitized report without cookies, nonces, or raw visitor data.', 'chat-trigger-embed-for-n8n' ); ?></p>
		<div class="cten-actions">
			<button type="button" class="button button-primary" data-cten-copy-report><?php esc_html_e( 'Copy Report Summary', 'chat-trigger-embed-for-n8n' ); ?></button>
			<button type="button" class="button" data-cten-download-report data-format="json"><?php esc_html_e( 'Download JSON Report', 'chat-trigger-embed-for-n8n' ); ?></button>
			<button type="button" class="button" data-cten-download-report data-format="text"><?php esc_html_e( 'Download Plain Text Report', 'chat-trigger-embed-for-n8n' ); ?></button>
		</div>
		<pre class="cten-json" data-cten-report><?php echo esc_html( wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></pre>
	</section>

	<section class="cten-card">
		<h3><?php esc_html_e( 'Deployment Checklist', 'chat-trigger-embed-for-n8n' ); ?></h3>
		<ol class="cten-checklist">
			<li><?php esc_html_e( 'Verify backup', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Check WordPress requirements', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Check plugin assets', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Validate default profile', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Run mock chat test', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Optionally run live n8n test', 'chat-trigger-embed-for-n8n' ); ?></li>
			<li><?php esc_html_e( 'Verify mobile and cache behavior', 'chat-trigger-embed-for-n8n' ); ?></li>
		</ol>
	</section>
</div>
