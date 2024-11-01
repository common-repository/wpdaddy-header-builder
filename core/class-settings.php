<?php

namespace WPDaddy\Builder;
defined('ABSPATH') OR exit;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class Settings {
	private static $instance = null;
	const REST_NAMESPACE = 'wpda-builder/v2/settings';
	const permission = 'manage_options';
	const menu_slug = 'wpda-builder-settings';
	const settings_option_key = 'wpda-builder-settings';

	private $settings = array(
		'header_area'    => '',
		'footer_area'    => '',
		'current_header' => '',
		'current_footer' => '',
		'conditions'     => '',
		'condition'      => '',
	);

	/** @return self */
	public static function instance() {
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct() {
		$this->load_settings();

		if (static::is_user_can()) {

			add_action('rest_api_init', array($this, 'action_rest_api_init'));
			add_action('admin_print_scripts-elementor_library_page_wpda-builder-settings', array($this, 'action_admin_enqueue_scripts'));
		}
	}

	public static function is_user_can() {
		return (is_user_logged_in() && current_user_can(static::permission));
	}

	public function action_rest_api_init() {
		if (!static::is_user_can()) {
			return;
		}

		register_rest_route(
			static::REST_NAMESPACE,
			'/save',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'permission_callback' => array(__CLASS__, 'is_user_can'),
					'callback'            => array($this, 'rest_save_settings'),
				)
			)
		);

		register_rest_route(
			static::REST_NAMESPACE,
			'/get',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => array(__CLASS__, 'is_user_can'),
					'callback'            => array($this, 'rest_get_settings'),
				)
			)
		);
	}

	public function rest_save_settings(WP_REST_Request $request) {
		$params = $request->get_params();

		$response = new WP_REST_Response();
		if (!static::is_user_can() || !wp_verify_nonce($params['_wpda_nonce'], '_wpda_nonce_settings')) {
			$response->set_status(403);
			$response->set_data(
				array(
					'msg' => 'not authorized'
				)
			);
		} else {
			$options = $params['settings'];

			$saved = $this->set_settings($options);
			if ($saved) {
				if (isset($this->settings['current_header'])) {
					$current_header = $this->get_settings('current_header');
					$_post          = !empty($current_header) ? get_post($current_header) : null;
					if ($_post) {
						if (key_exists('conditions', $options)) {
							$conditions = array(
								array(
									'type'  => 'include',
									'key'   => $options['conditions'],
									'value' => [],
								)
							);

							update_post_meta($_post->ID, '_wpda-builder-conditions', json_encode($conditions));
						}
						if (key_exists('header_area', $options)) {
							update_post_meta($_post->ID, '_wpda-builder-container', $options['header_area']);
						}
						$this->reset_active_header();
						update_post_meta($_post->ID, '_wpda-builder-active', true);
					}
				}

				$response->set_status(200);
				$response->set_data(
					array(
						'saved' => $saved,
						'msg'   => __('Saved', 'wpda-builder'),
					)
				);
			} else {
				$response->set_status(403);
				$response->set_data(
					array(
						'msg' => __('Error', 'wpda-builder'),
					)
				);
			}
		}

		return rest_ensure_response($response);
	}

	function rest_get_settings() {
		$response = new WP_REST_Response();

		if (!static::is_user_can()) {
			$response->set_status(403);
			$response->set_data(
				array(
					'msg' => 'not authorized'
				)
			);
		} else {
			$response->set_status(200);
			$response->set_data(
				array_merge(
					$this->get_settings(),
					array(
						'_wpda_nonce'      => wp_create_nonce('_wpda_nonce_settings'),
						'select_area_link' => add_query_arg(
							array(
								'wpda-show-panel' => 1
							), get_home_url()
						),
						'version'          => WPDA_HEADER_BUILDER__VERSION
					)
				)
			);
		}

		return rest_ensure_response($response);
	}

	private function reset_active_header() {
		$posts = new \WP_Query(
			array(
				'post_type'      => 'elementor_library',
				'posts_per_page' => '-1',
				'meta_query'     => array_merge(
					array(
						'relation' => 'AND',
					),
					array(
						array(
							'key'   => '_elementor_template_type',
							'value' => 'wpda-header',
						),
						array(
							'key'   => '_wpda-builder-active',
							'value' => true,
						)
					)
				),
				'fields'         => 'ids',
				'no_found_rows'  => true
			)
		);

		if (!is_wp_error($posts) && $posts->have_posts()) {
			foreach ($posts->posts as $_post) {
				update_post_meta($_post, '_wpda-builder-active', false);
			}
		}
	}

	public static function settings_page() {
		?>
		<div class="wpda-settings-main-wrapper">
			<h1>WP Daddy <span>Header Builder</span><span class="wpda_sup">FREE</span></h1>
			<div id="wpda-settings">
				<span class="spinner is-active"></span>
			</div>

		</div>
		<?php
	}

	public function action_admin_enqueue_scripts() {
		remove_all_actions('admin_notices');

		wp_enqueue_script('react');
		wp_enqueue_script('react-dom');
		wp_enqueue_script('moment');
		wp_enqueue_script('lodash');
		wp_enqueue_script('wp-components');
		wp_enqueue_script('wp-api-fetch');
		wp_enqueue_script('wp-notices');

		wp_enqueue_script('wpda-builder', plugin_dir_url(WPDA_HEADER_BUILDER__FILE).'dist/js/admin/settings.js', array(), '', true);
		wp_enqueue_style('wpda-builder', plugin_dir_url(WPDA_HEADER_BUILDER__FILE).'dist/css/admin/settings.css');
		wp_enqueue_style('wpda-settings-font', '//fonts.googleapis.com/css?family=Poppins:400,500,700%7CMontserrat:600,700');
	}

	private function get_default_settings() {
		return apply_filters('wpda/builder/settings/defaults', $this->settings);
	}

	private function load_settings() {
		$options = get_option(static::settings_option_key, '');
		try {
			if (!is_array($options) && is_string($options)) {
				$options = json_decode($options, true);
				if (json_last_error() || !is_array($options) || !count($options)) {
					$options = array();
				}
			}
		} catch (\Exception $exception) {
			$options = array();
		}

		$options        = array_replace_recursive($this->get_default_settings(), $options);
		$this->settings = $options;
	}

	/**
	 * @param string|bool $option
	 *
	 * @return array|string|mixed $settings
	 */
	public function get_settings($option = false) {
		return (false === $option) ? $this->settings :
			((is_string($option) && key_exists($option, $this->settings)) ? $this->settings[$option] : '');
	}

	/** @param array $options
	 ** @return bool
	 **/
	private function set_settings(array $options = array()) {
		if (!is_array($options)) {
			return false;
		}
		$options = array_merge(
			$this->get_settings(),
			$options
		);
		update_option(self::settings_option_key, json_encode($options));
		$this->settings = $options;

		return true;
	}
}
