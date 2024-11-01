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

class Basic extends Library_Document {

	public function get_name(){
		return static::$name;
	}
}
