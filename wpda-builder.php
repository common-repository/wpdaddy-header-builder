<?php
/**
 ** Plugin Name: WP Daddy Header Builder
 ** Plugin URI: https://wpdaddy.com/
 ** Description: WP Daddy Header Builder
 ** Version: 1.0.2
 ** Author: WP Daddy
 ** Author URI: https://wpdaddy.com/
 ** Text Domain: wpda-builder
 ** Domain Path:  /languages
 **/

defined('ABSPATH') OR exit;
global $wp_version;

if(!function_exists('get_plugin_data')) {
	require_once(ABSPATH.'wp-admin/includes/plugin.php');
}
$plugin_info = get_plugin_data(__FILE__);
define('WPDA_HEADER_BUILDER__FILE', __FILE__);
define('WPDA_HEADER_BUILDER__VERSION', $plugin_info['Version']);

if(!version_compare(PHP_VERSION, '5.6', '>=')) {
	add_action('admin_notices', 'wpda_hb__fail_php_version');
} else if(!version_compare($wp_version, '5.3', '>=')) {
	add_action('admin_notices', 'wpda_hb__fail_wp_version');
} else {
	add_action('plugins_loaded', 'wpda_hb__plugins_loaded', 0);
}

function wpda_hb__plugins_loaded(){
	if(defined('WPDA_PRO_HEADER_BUILDER__FILE')) {
		return;
	}
	require_once __DIR__.'/core/autoload.php';
	require_once __DIR__.'/core/dom/autoload.php';

	add_action('elementor/init', array( WPDaddy\Builder\Init::class, 'instance' ));
	add_action('init', 'wpda_hb__load_textdomain');
}

function wpda_hb__load_textdomain(){
	load_plugin_textdomain('wpda-builder', false, dirname(plugin_basename(__FILE__)).'/languages/');
}

function wpda_hb__fail_php_version(){
	$message      = sprintf('WP Daddy Header Builder requires PHP version %1$s+, plugin is currently NOT ACTIVE.', '5.6');
	$html_message = sprintf('<div class="error">%s</div>', wpautop($message));
	echo wp_kses_post($html_message);
}

function wpda_hb__fail_wp_version(){
	$message      = sprintf('WP Daddy Header Builder requires WordPress version %1$s+, plugin is currently NOT ACTIVE.', '5.0');
	$html_message = sprintf('<div class="error">%s</div>', wpautop($message));
	echo wp_kses_post($html_message);
}

register_activation_hook(__FILE__, 'wpda_hb__activation_hook');

function wpda_hb__activation_hook(){
	update_option('elementor_disable_color_schemes', 'yes');
	update_option('elementor_disable_typography_schemes', 'yes');
}
