<?php
/**
 * Uninstall handler.
 *
 * @package ChatTriggerEmbedN8n
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$temporary_transients = array(
	'cten_activation_notice',
	'cten_admin_message',
	'cten_admin_error',
);
foreach ( $temporary_transients as $transient ) {
	delete_transient( $transient );
}

$settings = get_option( 'cten_settings', array() );
if ( is_array( $settings ) && ! empty( $settings['delete_data_on_uninstall'] ) ) {
	delete_option( 'cten_settings' );
	global $wpdb;
	$table = $wpdb->prefix . 'cten_analytics_events';
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}
