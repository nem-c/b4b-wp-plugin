<?php

namespace B4B_Theme_Support\Lib\Shortcodes;

class Login_Shortcode extends Shortcode implements Interface_Shortcode {

	public function shortcode( $attributes ) {
		return $this->render("form-b4b-login");
	}

}