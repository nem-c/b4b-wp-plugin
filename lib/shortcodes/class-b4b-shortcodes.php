<?php

namespace B4B_Theme_Support\Lib\Shortcodes;

class B4B_Shortcodes {

	protected $shortcodes = [];

	public function __construct() {
		$this->shortcodes["b4b_auth_login"]         = new Login_Shortcode();
		$this->shortcodes["b4b_auth_register"]      = new Register_Shortcode();
		$this->shortcodes["b4b_auth_lost_password"] = new Lost_Password_Shortcode();

		$this->register();
	}

	public function register() {
		foreach ( $this->shortcodes as $tag => $class ) {
			add_shortcode( $tag, [ $class, "shortcode" ] );
		}
	}
}