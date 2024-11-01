<?php

namespace WPDaddy\Builder;
defined('ABSPATH') OR exit;

use DOMDocument;
use DOMXPath;
use Elementor\Plugin as Elementor_Plugin;

class Buffer {
	private static $instance = null;

	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		if($this->is_work()) {
			ob_start(array( $this, 'ob_finish' ));
			add_action('wp_head', array( $this, 'wp_head' ), 50);
		}
	}

	private function is_work(){
		static $answer = null;
		if(!is_null($answer)) {
			return $answer;
		}
		$elementor = Elementor_Plugin::instance();
		$settings  = Settings::instance()->get_settings();
		$post_id   = $settings['current_header'];
		if(empty($post_id) || !$post_id) {
			return ($answer = false);
		}
		$is_elementor = $elementor->db->is_built_with_elementor($post_id);
		if(!$is_elementor || empty($settings['header_area']) || !$settings['header_area']) {
			return ($answer = false);
		}

		$is_rest             = defined('REST_REQUEST') && REST_REQUEST;
		$is_elementor_editor = $elementor->editor->is_edit_mode();
		$is_preview          = $elementor->preview->is_preview_mode();
		$is_editor           = false;//$is_preview || $is_rest || $is_elementor_editor;
		if($is_editor || is_admin() || defined('WPDA_PANEL_ENABLED')) {
			return ($answer = false);
		}

		$is_condition = ($settings['condition'] !== 'none');
		if($is_condition) {
			$is_condition = (($settings['condition'] === 'all') || (function_exists($settings['condition']) && call_user_func($settings['condition'])));
		}
		if(!$is_condition) {
			return ($answer = false);
		}

		$answer = true;

		return $answer;
	}

	public function wp_head(){
		if(!$this->is_work()) {
			return;
		}
		$elementor = Elementor_Plugin::instance();
		$settings  = Settings::instance()->get_settings();
		$post_id   = $settings['current_header'];
		$content   = $elementor->frontend->get_builder_content_for_display($post_id);
		$elementor->frontend->enqueue_styles();
		$elementor->frontend->enqueue_scripts();
		wp_cache_delete('render_header', 'wpda_builder');
		wp_cache_set('render_header', $content, 'wpda_builder', 5);
	}

	public function ob_finish($buffer){

		if(!$this->is_work()) {
			return false;
		}

		$changed = false;

		$func = function_exists('mb_strpos') ? 'mb_strpos' : 'strpos';
		if (call_user_func($func, $buffer, '<html') === false) {
			return false;
		}

		$settings = Settings::instance()->get_settings();
		if(empty($settings['header_area']) || !$settings['current_header']) {
			return false;
		}

		if (call_user_func($func, $buffer, '<noscript') !== false) {
			$buffer = preg_replace('#<noscript>(.*)</noscript>#', '', $buffer);
		}

		$document = new \WPDaddy\Dom\HTMLDocument($buffer);
		$oldNode  = $document->querySelector($settings['header_area']);

		if(null !== $oldNode) {
			$content = wp_cache_get('render_header', 'wpda_builder');
			wp_cache_delete('render_header', 'wpda_builder');
			if(false === $content) {
				return false;
			}
			$replacement = $document->createDocumentFragment();
			$replacement->appendHTML($content);
			$oldNode->replaceWith($replacement);
		}

		return $changed ? $document : $buffer;
	}

}
