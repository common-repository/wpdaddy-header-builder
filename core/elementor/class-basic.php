<?php

namespace WPDaddy\Builder\Elementor;

if(!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Modules;
use Elementor\Widget_Common;

abstract class Basic extends Widget_Base {

	public function get_categories(){
		$category = 'wpda_builder_hidden';

		$editor = Plugin::$instance->editor->is_edit_mode();
		if($editor) {
			$post = get_post();
			if($post->post_type === 'elementor_library' &&
			   get_post_meta($post->ID, '_elementor_template_type', true) === 'wpda-header') {
				$category = 'wpda_builder';
			}
		}

		return array( $category );
	}

	public function start_controls_section($section_id, array $args = []){
		//$section_id   .= '_section';
		$default_args = array(
			'condition' => apply_filters('wpda-builder/elementor/start_controls_section/'.$section_id, null)
		);
		$args         = array_merge($default_args, $args);
		parent::start_controls_section($section_id, $args);
	}

	/**
	 * @param array $data
	 * @param null  $args
	 *
	 * @throws \Exception
	 */
	public function __construct(array $data = array(), $args = null){
		parent::__construct($data, $args);

		$this->construct();
	}

	protected function construct(){
	}

	public function get_repeater_key($setting_key, $repeater_key, $repeater_item_index){
		return $this->get_repeater_setting_key($setting_key, $repeater_key, $repeater_item_index);
	}

	protected function _register_controls(){
		do_action('wpda-builder/elementor/register_control/before/'.$this->get_name(), $this);
		$this->init_controls();

		//parent::_register_controls();

		do_action('wpda-builder/elementor/register_control/after/'.$this->get_name(), $this);
	}

	// php
	protected function render(){
		do_action('wpda-builder/elementor/render/before/'.$this->get_name(), $this);
		$this->render_widget();
		do_action('wpda-builder/elementor/render/after/'.$this->get_name(), $this);
	}

	protected function init_controls(){
	}

	protected function render_widget(){
	}


}
