<?php

namespace B4B_Theme_Support\Lib;

use B4B_Theme_Support\Lib\Meta_Boxes\Sponsor_Info_Meta_Box;
use B4B_Theme_Support\Lib\Meta_Boxes\Sponsor_Social_Meta_Box;
use B4B_Theme_Support\Lib\Meta_Boxes\Woocommerce_Order_Customer_Data_Meta_Box;
use B4B_Theme_Support\Lib\Meta_Boxes\Woocommerce_Order_Donated_Golfer_Meta_Box;
use B4B_Theme_Support\Lib\Meta_Boxes\Woocommerce_Order_Scramble_Team_Meta_Box;
use B4B_Theme_Support\Lib\Post_Types\Sponsor;
use B4B_Theme_Support\Lib\Acf_Blocks\Sponsors_Block;
use B4B_Theme_Support\Lib\Shortcodes\B4B_Shortcodes;
use B4B_Theme_Support\Lib\Woo\B4B_WC;

class Loader {

	public static $instance;

	public function register_taxonomies() {
	}

	public function register_post_types() {
		Sponsor::init();
	}

	public function register_metaboxes() {
		$sponsor_info_meta_box                     = new Sponsor_Info_Meta_Box( "sponsor" );
		$sponsor_social_meta_box                   = new Sponsor_Social_Meta_Box( "sponsor" );
		$woocommerce_order_customer_data_meta_box  = new Woocommerce_Order_Customer_Data_Meta_Box();
		$woocommerce_order_scramble_team_meta_box  = new Woocommerce_Order_Scramble_Team_Meta_Box();
		$woocommerce_order_donated_golfer_meta_box = new Woocommerce_Order_Donated_Golfer_Meta_Box();
	}

	public function register_blocks() {
		if ( function_exists( 'acf_register_block' ) === false ) {
			return false;
		}
		$sponsors_block = new Sponsors_Block();
	}

	public function load_shortcodes() {
		$b4b_shortcodes = new B4B_Shortcodes();
	}

	public function customize_woo() {
		$b4b_wc = new B4B_WC();
	}

	public static function init() {
		if ( self::$instance instanceof Loader === false ) {
			self::$instance = new Loader();
		}

		return self::$instance;
	}

	public static function instance() {
		return self::init();
	}

	public function activated() {
	}

	public function deactivated() {
	}
}