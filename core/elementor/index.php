<?php

namespace WPDaddy\Builder;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Controls_Manager as Elementor_Controls_Manager;
use Elementor\Element_Section;
use Elementor\Plugin;
use Elementor\Widgets_Manager;
use WPDaddy\Builder\Elementor\Modify\Section;
use WPDaddy\Builder\Elementor\Widgets\Cart;

class Elementor {
	public static $JS_URL    = 'js';
	public static $CSS_URL   = 'css';
	public static $IMAGE_URL = 'img';
	public static $PATH      = '/';

	const TAB_WPDA_SETTINGS = 'wpda_settings';

	private $widgets = array(
		'Logo',
		'Menu',
		'Search',
		'Delimiter',
	);

	private $group_controls = array(// Controls
	);

	private static $instance = null;

	public static function instance(){
		if(!self::$instance instanceof self) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct(){
		if(class_exists('WooCommerce')) {
			$this->widgets[] = 'Cart';
		}

		self::$JS_URL    = plugins_url('/assets/js/', __FILE__);
		self::$CSS_URL   = plugins_url('/assets/css/', __FILE__);
		self::$IMAGE_URL = plugins_url('/assets/img/', __FILE__);
		self::$PATH      = plugin_dir_path(__FILE__);
		$this->actions();
		Elementor_Controls_Manager::add_tab(self::TAB_WPDA_SETTINGS, 'WP Daddy');

	}

	private function actions(){
		$this->elementor_init();
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ));
		add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ));

		add_action('elementor/frontend/after_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ));
		add_action('elementor/frontend/after_enqueue_styles', array( $this, 'frontend_enqueue_styles' ));
		add_action('elementor/editor/after_enqueue_scripts', array( $this, 'editor_enqueue_styles' ));

		add_action('elementor/widgets/widgets_registered', array( $this, 'widgets_registered' ));

		Section::instance();

		add_filter('woocommerce_add_to_cart_fragments', array( Cart::class, 'woocommerce_add_to_cart_fragments' ));

	}

	/** @param Widgets_Manager $widgets_manager */
	public function widgets_registered($widgets_manager){
		$this->widgets = apply_filters('wpda-builder/elementor/widgets', $this->widgets);

		if(is_array($this->widgets) && !empty($this->widgets)) {
			foreach($this->widgets as $module) {
				$module = sprintf('WPDaddy\\Builder\\Elementor\\Widgets\\%s', $module);
				if(class_exists($module)) {
					$widgets_manager->register_widget_type(new $module());
				}
			}
		}
	}

	public function elementor_init(){
		$elements_manager = Plugin::instance()->elements_manager;
		$categories       = $elements_manager->get_categories();
		if(!key_exists('wpda_builder', $categories)) {
			$elements_manager->add_category(
				'wpda_builder',
				array(
					'title' => esc_html__('WPDaddy Builder', 'wpda-builder'),
					'icon'  => 'fa fa-plug'
				)
			);
		}
	}


	public function frontend_enqueue_styles(){
		wp_enqueue_style('wpda-elementor-core-frontend', plugin_dir_url(WPDA_HEADER_BUILDER__FILE).'dist/css/frontend/frontend.css');
	}

	public function frontend_enqueue_scripts(){
		wp_enqueue_script('wpda-elementor-core-frontend', plugin_dir_url(WPDA_HEADER_BUILDER__FILE).'dist/js/frontend/frontend.js', array(), '', true);
	}

	public function editor_enqueue_styles(){
		wp_enqueue_script('wpda-builder/editor', plugin_dir_url(WPDA_HEADER_BUILDER__FILE).'dist/js/admin/elementor.js', array(), '', true);
		wp_enqueue_style('wpda-builder/editor', plugin_dir_url(WPDA_HEADER_BUILDER__FILE).'dist/css/admin/elementor.css');

	}

	public function enqueue_scripts(){
		/* CSS */
		/*wp_enqueue_style('slick',
			plugins_url('/assets/css/slick.css', __FILE__)
		);*/

		/* JS */
		$translation_array = array(
			'ajaxurl' => esc_url(admin_url('admin-ajax.php'))
		);
		wp_localize_script('jquery', 'wpda_builder', $translation_array);

		/* wp_register_script('slick',
			 plugins_url('/assets/js/slick.js', __FILE__),
			 array('jquery'),
			 true
		 );*/

	}
}

