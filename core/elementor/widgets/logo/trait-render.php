<?php

namespace WPDaddy\Builder\Elementor\Widgets\Logo;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;

trait Trait_Render {

	protected function render_widget(){
		$settings = array(
			'header_logo'        => array( 'url' => '', ),
			'logo_height_custom' => '',
			'logo_sticky'        => array( 'url' => '', ),
		);

		$settings = wp_parse_args($this->get_settings(), $settings);

		$this->add_render_attribute(
			'wrapper', 'class', array(
			'wpda-builder-logo_container',
			($settings['header_logo']['url'] && $settings['logo_sticky']['url']) ? 'has_sticky_logo' : '',
		)
		);
		$enable_sticky = false;
		if(isset($settings['header_logo']['url']) && !empty($settings['header_logo']['url'])) {
			$header_logo = $settings['header_logo']['url'];
			if($header_logo) {
				$header_logo   = '<img class="wpda-builder-logo" src="'.esc_url($header_logo).'" alt="" />';
				$enable_sticky = true;
			}
		} else {
			$header_logo = '<span class="wpda-builder-site_title">'.get_bloginfo('name').'</span>';
		}
		if(isset($settings['logo_sticky']['url']) && !empty($settings['logo_sticky']['url'])) {
			$logo_sticky = $settings['logo_sticky']['url'];
			if($logo_sticky) {
				$logo_sticky = '<img class="wpda-builder-logo_sticky" src="'.esc_url($logo_sticky).'" alt="" />';
			}
		} else {
			$logo_sticky = '';
		}
		if(!$enable_sticky) {
			$logo_sticky = '';
		}
		?>
		<div <?php $this->print_render_attribute_string('wrapper') ?>>
			<a href="<?php echo esc_url(home_url('/')); ?>">
				<?php echo $header_logo.$logo_sticky; ?>
			</a>
		</div>
		<?php
	}
}

