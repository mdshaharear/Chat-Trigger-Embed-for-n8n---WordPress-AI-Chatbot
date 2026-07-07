<?php
/**
 * Elementor widget for the chatbot shell.
 *
 * @package ChatTriggerEmbedN8n
 */

namespace ChatTriggerEmbedN8n\Elementor;

use ChatTriggerEmbedN8n\Helpers;
use ChatTriggerEmbedN8n\Plugin;
use ChatTriggerEmbedN8n\Safe_Mode;
use ChatTriggerEmbedN8n\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Chat_Widget extends \Elementor\Widget_Base {
	public function get_name(): string {
		return 'cten-chat-widget';
	}

	public function get_title(): string {
		return __( 'AI Chat Builder', 'chat-trigger-embed-for-n8n' );
	}

	public function get_icon(): string {
		return 'eicon-chat';
	}

	public function get_categories(): array {
		return array( 'general' );
	}

	public function get_keywords(): array {
		return array( 'chat', 'n8n', 'ai', 'bot', 'support', 'elementor' );
	}

	public function get_script_depends(): array {
		return array( 'cten-public' );
	}

	public function get_style_depends(): array {
		return array( 'cten-public-vendor', 'cten-public' );
	}

	protected function render(): void {
		$settings = Helpers::get_settings();

		if ( Safe_Mode::should_block_public_chat() || ! Settings::allows_display( $settings ) || ! Settings::can_render_in_elementor_widget( $settings ) ) {
			if ( $this->is_editor_context() ) {
				echo '<div class="cten-elementor-widget cten-elementor-widget--notice">';
				echo esc_html__( 'Configure the AI Chat Builder connection, enable the chatbot, and switch Display Mode to Elementor Widget or Both to preview it here.', 'chat-trigger-embed-for-n8n' );
				echo '</div>';
			}
			return;
		}

		Plugin::mark_elementor_widget_rendered();
		Plugin::render_shell();
	}

	private function is_editor_context(): bool {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		$plugin = \Elementor\Plugin::$instance;
		if ( isset( $plugin->editor ) && method_exists( $plugin->editor, 'is_edit_mode' ) && $plugin->editor->is_edit_mode() ) {
			return true;
		}

		if ( isset( $plugin->preview ) && method_exists( $plugin->preview, 'is_preview_mode' ) && $plugin->preview->is_preview_mode() ) {
			return true;
		}

		return false;
	}
}
