<?php
/**
 * Admin preview renderer.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Preview {
	public static function render( array $settings, string $context = 'appearance' ): void {
		$vars = Settings::get_css_variables( $settings );
		?>
		<div class="cten-preview" data-cten-preview="<?php echo esc_attr( $context ); ?>" data-preview-size="desktop" style="<?php echo esc_attr( self::css_string( $vars ) ); ?>">
			<div class="cten-preview__switcher" role="group" aria-label="<?php echo esc_attr__( 'Preview size', 'chat-trigger-embed-for-n8n' ); ?>">
				<button type="button" class="cten-preview__switcher-btn is-active" data-preview-size="desktop"><?php esc_html_e( 'Desktop', 'chat-trigger-embed-for-n8n' ); ?></button>
				<button type="button" class="cten-preview__switcher-btn" data-preview-size="tablet"><?php esc_html_e( 'Tablet', 'chat-trigger-embed-for-n8n' ); ?></button>
				<button type="button" class="cten-preview__switcher-btn" data-preview-size="mobile"><?php esc_html_e( 'Mobile', 'chat-trigger-embed-for-n8n' ); ?></button>
			</div>
			<div class="cten-preview__launcher" aria-hidden="true">
				<span class="dashicons dashicons-format-chat"></span>
			</div>
			<div class="cten-preview__window">
				<header class="cten-preview__header">
					<div>
						<div class="cten-preview__title"><?php echo esc_html( $settings['bot_name'] ); ?></div>
						<div class="cten-preview__subtitle"><?php echo esc_html( $settings['bot_subtitle'] ); ?></div>
						<div class="cten-preview__status"><span class="cten-online-indicator"></span><?php echo esc_html( $settings['online_status_text'] ); ?></div>
					</div>
					<button type="button" class="cten-preview__close" aria-label="<?php echo esc_attr( $settings['close_button_label'] ); ?>">×</button>
				</header>
				<div class="cten-preview__body">
					<div class="cten-preview__message cten-preview__message--bot"><?php echo esc_html( $settings['welcome_message'] ); ?></div>
					<div class="cten-preview__message cten-preview__message--user"><?php echo esc_html__( 'This is a sample user reply.', 'chat-trigger-embed-for-n8n' ); ?></div>
					<div class="cten-preview__actions">
						<?php foreach ( array_filter( $settings['quick_actions'], static fn( $item ) => ! empty( $item['enabled'] ) ) as $action ) : ?>
							<button type="button" class="cten-preview__action"><?php echo esc_html( $action['label'] ); ?></button>
						<?php endforeach; ?>
					</div>
					<div class="cten-preview__dynamic">
						<button type="button" class="cten-preview__dynamic-option">Customer Support</button>
						<button type="button" class="cten-preview__dynamic-option">Lead Collection</button>
						<button type="button" class="cten-preview__dynamic-option">Booking and Appointments</button>
					</div>
				</div>
				<footer class="cten-preview__footer">
					<p><?php echo esc_html( $settings['follow_up_privacy_text'] ); ?></p>
					<div class="cten-preview__input">
						<span><?php echo esc_html( $settings['input_placeholder'] ); ?></span>
						<button type="button"><?php echo esc_html( $settings['retry_button_text'] ); ?></button>
					</div>
				</footer>
			</div>
		</div>
		<?php
	}

	private static function css_string( array $vars ): string {
		$pieces = array();
		foreach ( $vars as $key => $value ) {
			$pieces[] = $key . ':' . $value;
		}
		return implode( ';', $pieces );
	}
}
