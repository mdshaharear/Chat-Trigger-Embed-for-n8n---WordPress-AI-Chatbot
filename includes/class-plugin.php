<?php
/**
 * Main plugin runtime.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	private static ?Plugin $instance = null;
	private static bool $elementor_widget_rendered = false;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		Settings::hooks();
		Import_Export::hooks();
		Analytics::hooks();
		\ChatTriggerEmbedN8n\V2\Native_Core::hooks();
		Elementor_Integration::hooks();
		Admin::hooks();
		Site_Health::hooks();
		Runtime_Lab::hooks();

		add_action( 'wp_enqueue_scripts', array( Assets::class, 'enqueue_public' ) );
		add_action( 'wp_footer', array( $this, 'render_frontend_root' ), 9 );
		add_filter( 'script_loader_tag', array( Assets::class, 'add_script_type_module' ), 10, 3 );
		add_action( 'wp_head', array( $this, 'inject_css_vars' ), 20 );
	}

	public function inject_css_vars(): void {
		$settings = Helpers::get_settings();
		if ( Safe_Mode::should_block_public_chat() || ! Settings::allows_display( $settings ) ) {
			return;
		}

		$vars = Settings::get_css_variables( $settings );
		echo '<style id="cten-css-vars">:root{' . esc_html( $this->css_string( $vars ) ) . '}</style>';
	}

	public function render_frontend_root(): void {
		if ( self::$elementor_widget_rendered ) {
			return;
		}

		$settings = Helpers::get_settings();
		if ( ! Settings::can_render_in_footer( $settings ) ) {
			return;
		}

		self::render_shell();
	}

	public static function mark_elementor_widget_rendered(): void {
		self::$elementor_widget_rendered = true;
	}

	public static function render_shell(): void {
		$settings = Helpers::get_settings();
		if ( Safe_Mode::should_block_public_chat() || ! Settings::allows_display( $settings ) ) {
			return;
		}

		$config = Settings::get_public_config( $settings );
		$config = apply_filters( 'cten_public_chat_config_render', $config, $settings );
		do_action( 'cten_before_chat_render', $config, $settings );
		?>
		<div id="cten-chat-shell" class="cten-shell">
			<div id="cten-quick-actions" class="cten-quick-actions" aria-label="<?php echo esc_attr__( 'Conversation starters', 'chat-trigger-embed-for-n8n' ); ?>"></div>
			<div id="cten-chat-root" class="n8n-chat cten-chat" data-cten-root="1"></div>
			<script type="application/json" id="cten-chat-config"><?php echo esc_html( wp_json_encode( $config, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) ); ?></script>
		</div>
		<?php
		do_action( 'cten_after_chat_render', $config, $settings );
	}

	private function css_string( array $vars ): string {
		$pieces = array();
		foreach ( $vars as $key => $value ) {
			$pieces[] = $key . ':' . $value;
		}
		return implode( ';', $pieces );
	}
}
