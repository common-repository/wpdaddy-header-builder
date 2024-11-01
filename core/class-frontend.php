<?php

namespace WPDaddy\Builder;

use Elementor\Plugin as Elementor_Plugin;
use WPDaddy\Builder\Library\Header;

defined('ABSPATH') OR exit;

final class Frontend {
	private static $instance = null;

	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		$settings     = Settings::instance()->get_settings();
		$is_condition = ($settings['condition'] !== 'none');
		if($is_condition) {
			$is_condition = (($settings['condition'] === 'all') || (function_exists($settings['condition']) && call_user_func($settings['condition'])));
		}
		if(static::can_show() && $is_condition) {
			add_action(
				'admin_bar_menu', function(\WP_Admin_Bar $wp_admin_bar) use ($settings){
				$wp_admin_bar->add_node(
					array(
						'id'    => 'wpda_builder',
						'title' => __('Edit with WP Daddy Builder', 'wpda-builder'),
					)
				);

				$wp_admin_bar->add_menu(
					[
						'id'     => 'wpda_builder_area',
						'parent' => 'wpda_builder',
						'title'  => __('Edit Header Area', 'wpda-builder'),
						'href'   => add_query_arg(
							array(
								'wpda-show-panel' => 1
							)
						),
					]
				);

				if(!empty($settings['current_header'])) {
					$wp_admin_bar->add_menu(
						[
							'id'     => 'wpda_builder_header',
							'parent' => 'wpda_builder',
							'title'  => __('Edit Header', 'wpda-builder'),
							'href'   => add_query_arg(
								array(
									'template_post' => get_the_ID(),
								), Header::static_get_edit_url($settings['current_header'])
							)
						]
					);
				}
			}, 900
			);
		}
		if($this->show_panel()) {
			define('WPDA_PANEL_ENABLED', true);
			add_action('wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ));
		}
	}

	public static function can_show(){
		$is_rest             = defined('REST_REQUEST') && REST_REQUEST;
		$is_elementor_editor = Elementor_Plugin::$instance->editor->is_edit_mode();
		$is_preview          = Elementor_Plugin::$instance->preview->is_preview_mode();
		$is_editor           = $is_rest || $is_elementor_editor || $is_preview;

//		var_dump($is_rest, $is_preview, $is_elementor_editor, $is_editor);

		return (
			!is_admin()
			&& Settings::is_user_can()
			&& !$is_editor
		);
	}

	public static function show_panel(){
		return (
			static::can_show()
			&& key_exists('wpda-show-panel', $_GET) && $_GET['wpda-show-panel']
		);
	}

	public function action_wp_enqueue_scripts(){
		if(!static::can_show()) {
			return;
		}
		wp_enqueue_script('react');
		wp_enqueue_script('react-dom');
		wp_enqueue_script('moment');
		wp_enqueue_script('lodash');
		wp_enqueue_script('wp-components');
		wp_enqueue_script('wp-api-fetch');
		wp_enqueue_script('wp-notices');

		wp_enqueue_script('wpda-builder', plugin_dir_url(WPDA_HEADER_BUILDER__FILE).'dist/js/frontend/panel.js', array(), '', true);
		wp_enqueue_style('wpda-builder', plugin_dir_url(WPDA_HEADER_BUILDER__FILE).'dist/css/frontend/panel.css');
	}
}
