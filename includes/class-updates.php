<?php
/**
 * GitHub update checker.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Updates {
	private const TRANSIENT_KEY = 'cten_update_info';
	private const REPO_API = 'https://api.github.com/repos/mdshaharear/Chat-Trigger-Embed-for-n8n---WordPress-AI-Chatbot/releases/latest';

	public static function hooks(): void {
		add_filter( 'site_transient_update_plugins', array( __CLASS__, 'inject_update' ) );
		add_filter( 'plugins_api', array( __CLASS__, 'plugins_api' ), 10, 3 );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
		add_action( 'admin_post_cten_refresh_update_info', array( __CLASS__, 'refresh_from_request' ) );
	}

	public static function refresh_info( bool $force = false ): ?array {
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( is_array( $cached ) && ! $force ) {
			return $cached;
		}

		$response = wp_remote_get(
			self::REPO_API,
			array(
				'timeout' => 12,
				'headers' => array(
					'Accept' => 'application/vnd.github+json',
					'User-Agent' => 'ChatTriggerEmbedN8n/' . CTEN_VERSION,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( 200 !== $code || ! is_array( $body ) ) {
			return null;
		}

		$tag = sanitize_text_field( (string) ( $body['tag_name'] ?? '' ) );
		if ( '' === $tag ) {
			return null;
		}

		$version = ltrim( $tag, 'v' );
		$info = array(
			'version'     => $version,
			'tag'         => $tag,
			'name'        => sanitize_text_field( (string) ( $body['name'] ?? $tag ) ),
			'url'         => esc_url_raw( (string) ( $body['html_url'] ?? '' ) ),
			'zip_url'     => esc_url_raw( (string) ( $body['zipball_url'] ?? '' ) ),
			'body'        => wp_kses_post( (string) ( $body['body'] ?? '' ) ),
			'published_at' => sanitize_text_field( (string) ( $body['published_at'] ?? '' ) ),
		);

		set_transient( self::TRANSIENT_KEY, $info, HOUR_IN_SECONDS );

		return $info;
	}

	public static function inject_update( object $transient ): object {
		if ( empty( $transient->checked ) || ! is_array( $transient->checked ) ) {
			return $transient;
		}

		$info = self::refresh_info();
		if ( ! $info || empty( $info['version'] ) || version_compare( (string) $info['version'], CTEN_VERSION, '<=' ) ) {
			return $transient;
		}

		$plugin_file = CTEN_BASENAME;
		$transient->response[ $plugin_file ] = (object) array(
			'slug'        => CTEN_SLUG,
			'plugin'      => $plugin_file,
			'new_version' => $info['version'],
			'url'         => $info['url'],
			'package'     => $info['zip_url'],
			'tested'      => CTEN_MIN_WP,
			'requires'    => CTEN_MIN_PHP,
		);

		return $transient;
	}

	public static function plugins_api( mixed $result, string $action, object $args ): mixed {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || CTEN_SLUG !== $args->slug ) {
			return $result;
		}

		$info = self::refresh_info();
		if ( ! $info ) {
			return $result;
		}

		return (object) array(
			'name'          => 'AI Chat Builder for WordPress - OpenAI, Gemini & n8n',
			'slug'          => CTEN_SLUG,
			'version'       => $info['version'] ?? CTEN_VERSION,
			'author'        => '<a href="https://shaharear.com.bd">MD Shaharear</a>',
			'homepage'      => $info['url'] ?? '',
			'download_link' => $info['zip_url'] ?? '',
			'sections'      => array(
				'description' => __( 'Build native AI chatbots for WordPress with OpenAI, Gemini, n8n, and Elementor-friendly layouts.', 'chat-trigger-embed-for-n8n' ),
				'changelog'    => $info['body'] ?: __( 'See the latest release notes on GitHub.', 'chat-trigger-embed-for-n8n' ),
			),
			'tested'        => CTEN_MIN_WP,
			'requires'      => CTEN_MIN_PHP,
		);
	}

	public static function notice(): void {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		$info = self::refresh_info();
		if ( ! $info || empty( $info['version'] ) || version_compare( (string) $info['version'], CTEN_VERSION, '<=' ) ) {
			return;
		}

		$update_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=cten_refresh_update_info' ),
			'cten_refresh_update_info'
		);

		printf(
			'<div class="notice notice-info"><p>%s %s</p></div>',
			esc_html( sprintf( __( 'A newer version (%s) is available.', 'chat-trigger-embed-for-n8n' ), $info['version'] ) ),
			sprintf( '<a href="%s">%s</a>', esc_url( $update_url ), esc_html__( 'Refresh update info', 'chat-trigger-embed-for-n8n' ) )
		);
	}

	public static function refresh_from_request(): void {
		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'chat-trigger-embed-for-n8n' ) );
		}

		check_admin_referer( 'cten_refresh_update_info' );
		delete_transient( self::TRANSIENT_KEY );
		self::refresh_info( true );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'plugins.php' ) );
		exit;
	}
}
