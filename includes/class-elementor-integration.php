<?php
/**
 * Elementor integration bootstrap.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Integration {
	public static function hooks(): void {
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widget' ) );
	}

	public static function register_widget( $widgets_manager ): void {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		require_once CTEN_DIR . 'includes/elementor/class-chat-widget.php';

		$widget = new \ChatTriggerEmbedN8n\Elementor\Chat_Widget();

		if ( method_exists( $widgets_manager, 'register' ) ) {
			$widgets_manager->register( $widget );
			return;
		}

		if ( method_exists( $widgets_manager, 'register_widget_type' ) ) {
			$widgets_manager->register_widget_type( $widget );
		}
	}
}
