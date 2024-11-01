<?php

namespace WPDaddy\Builder\Library;
if(!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;
use Elementor\Core\DocumentTypes\Post;
use Elementor\Modules\Library\Documents\Library_Document;
use Elementor\Utils;
use WPDaddy\Builder\Elementor;
use WPDaddy\Builder\Settings;

class Header extends Basic {

	const post_type = 'elementor_library';
	public static $name = 'wpda-header';

	public function __construct(array $data = []){
		if($data) {
			$template = get_post_meta($data['post_id'], '_wp_page_template', true);

			if(empty($template)) {
				$template = 'default';
			}

			$data['settings']['template'] = $template;
		}

		parent::__construct($data);
	}

	public static function manage_posts_columns($column){
		return array(
			'cb'     => '<input type="checkbox" />',
			'title'  => esc_html__('Title', 'wpda-builder'),
			'status' => esc_html__('Status', 'wpda-builder'),
			'date'   => esc_html__('Date', 'wpda-builder'),
		);
	}

	public static function manage_posts_custom_column($column, $post_id){
		$this_url = $_SERVER['REQUEST_URI'];
		$settings = Settings::instance()->get_settings();

		$status = array(
			'active'   => '<span style="color: green">'.esc_html__('Active', 'wpda-builder').'</span>',
			'inactive' => '<span style="color: red">'.esc_html__('Inactive', 'wpda-builder').'</span>'
		);

		switch($column) {
			case 'status':
				echo ($settings['current_header'] == $post_id) ? $status['active'] : $status['inactive'];
				break;
			default:
				break;
		}
	}

	public function filter_admin_row_actions($actions){
		if($this->is_built_with_elementor() && $this->is_editable_by_current_user()) {
			$actions['edit_with_elementor'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				$this->get_edit_url(),
				__('Edit Header', 'wpda-builder')
			);
			unset($actions['edit_vc']);
		}

		return $actions;
	}

	public static function static_get_edit_url($post_id){
		$url = add_query_arg(
			[
				'post'   => $post_id,
				'action' => 'elementor',
			],
			admin_url('post.php')
		);

		return $url;
	}

	public static function get_properties(){
		$properties                    = parent::get_properties();
		$properties['admin_tab_group'] = '';

		$properties['support_wp_page_templates'] = false;
		$properties['admin_tab_group']           = 'library';
		$properties['show_in_library']           = true;
		$properties['register_type']             = true;

		return $properties;
	}

	/**
	 * @access public
	 */
	public function get_name(){
		return self::$name;
	}

	protected static function get_editor_panel_categories(){
		return Utils::array_inject(
			parent::get_editor_panel_categories(),
			'theme-elements',
			[
				'theme-elements-single' => [
					'title'  => __('Single', 'wpda-builder'),
					'active' => false,
				],
			]
		);
	}

	public function get_css_wrapper_selector(){
		return '.wpda-builder-page-'.$this->get_main_id();
	}

	protected function _register_controls(){
		parent::_register_controls();

		$this->start_injection(
			array(
				'type' => 'control',
				'at'   => 'after',
				'of'   => 'post_status'
			)
		);

		$this->start_controls_section(
			'wpda_settings',
			[
				'label' => __('WPDaddy Settings', 'wpda-builder'),
				'tab'   => Elementor::TAB_WPDA_SETTINGS,
			]
		);

		$this->add_responsive_control(
			'header_over_bg',
			array(
				'label' => __('Header Over Bg', 'wpda-builder'),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->end_controls_section();

		Post::register_style_controls($this);
	}

	/**
	 * @access public
	 * @static
	 */
	public static function get_title(){
		return __('WPDaddy Header', 'wpda-builder');
	}

	protected function get_remote_library_config(){
		$config = parent::get_remote_library_config();

		$config['category'] = '';
		$config['type']     = self::$name;

		return $config;
	}

	public function _get_initial_config(){
		$config = parent::_get_initial_config();

		return $config;
	}

	public function get_container_attributes(){
		$attributes = parent::get_container_attributes();

		if($this->get_settings('header_over_bg') === 'yes') {
			$attributes['class'] .= ' header_over_bg';
		}
		if($this->get_settings('header_over_bg_tablet') === 'yes') {
			$attributes['class'] .= ' header_over_bg_tablet';
		}
		if($this->get_settings('header_over_bg_mobile') === 'yes') {
			$attributes['class'] .= ' header_over_bg_mobile';
		}

		$attributes['class'] .= ' wpda-builder-page-'.$this->get_main_id().' wpda-builder';

		return $attributes;
	}

}
