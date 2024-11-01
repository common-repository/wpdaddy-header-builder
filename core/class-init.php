<?php

namespace WPDaddy\Builder;
defined('ABSPATH') OR exit;

use Elementor\Plugin;
use WPDaddy\Builder\Library\Header;

class Init {
	use Trait_REST;
	private static $instance = null;

	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		Settings::instance();
		// Admin
		Menu::instance();

		Elementor::instance();

		add_action('wp', array( __CLASS__, 'action_wp' ));

		add_filter(
			'elementor/document/urls/preview', function($url, $post){
			if(isset($_GET['template_post']) && !empty($_GET['template_post'])) {
				$url = add_query_arg(
					array(
						'template_post' => $_GET['template_post'],
					), $url
				);
			}

			return $url;
		}, 999, 2
		);

		add_action('elementor/documents/register', [ $this, 'register_default_types' ], 0);
		add_filter('single_template', array( $this, 'load_canvas_template' ));

		$this->init_trait_rest();
		Plugin::instance()->documents->register_document_type('wpda-header', Header::class);
	}


	public function register_default_types(){
		if(isset($_REQUEST['elementor_library_type']) && $_REQUEST['elementor_library_type'] === Header::$name) {
			add_action('manage_elementor_library_posts_custom_column', array( Header::class, 'manage_posts_custom_column' ), 10, 2);
			add_filter('manage_elementor_library_posts_columns', array( Header::class, 'manage_posts_columns' ));
		}
	}

	public static function action_wp(){
		// Frontend
		Frontend::instance();
		Buffer::instance();
	}

	function load_canvas_template($single_template){
		global $post;
		$_elementor_template_type = get_metadata('post', $post->ID, '_elementor_template_type', true);

		if($post->post_type === 'elementor_library' && $_elementor_template_type === 'wpda-header') {
			$single_template = __DIR__.'/library/template.php';
		}

		return $single_template;
	}
}
