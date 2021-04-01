<?php

namespace B4B_Theme_Support\Lib\Shortcodes;

abstract class Shortcode {
	public function render( $template_filename, $vars = [] ) {
		$template_name        = "myaccount/" . $template_filename . ".php";
		$plugin_template_path = B4B_PLUGIN_PATH . "/templates/woocommerce/" . $template_name;
		$html                 = "";

		$template_location = wc_locate_template( $template_name );
		if ( file_exists( $template_location ) === true ) {
			ob_start();
			wc_get_template( $template_name, $vars );
			$html = ob_get_clean();
		} elseif ( file_exists( $plugin_template_path ) === true ) {
			ob_start();
			if ( is_array( $vars ) === false ) {
				$vars = [];
			}
			foreach ( $vars as $name => $value ) {
				$$name = $value;
			}

			include( $plugin_template_path );
			$html = ob_get_clean();
		}

		return $html;
	}
}