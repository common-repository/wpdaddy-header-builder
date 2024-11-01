<?php

namespace WPDaddy\Builder;
defined('ABSPATH') OR exit;

final class Menu {
	private static $instance = null;

	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		add_action('admin_menu', array( $this, 'admin_menu' ), 800);
	}

	public function admin_menu(){
		// Settings
		add_submenu_page(
			'edit.php?post_type=elementor_library',
			__('WPDaddy Header Builder Settings', 'wpda-builder'),
			__('WPDaddy Builder', 'wpda-builder'),
			'manage_options',
			Settings::menu_slug,
			array( Settings::class, 'settings_page' )
		);

	}
}
