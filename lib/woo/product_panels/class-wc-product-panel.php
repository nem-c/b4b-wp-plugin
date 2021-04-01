<?php

namespace B4B_Theme_Support\Lib\Woo\Product_Panels;

abstract class WC_Product_Panel {
	public function __construct() {
		add_filter( "woocommerce_product_data_tabs", [
			$this,
			"add_tabs",
		] );

		add_action( "woocommerce_product_data_panels", [
			$this,
			"add_panel",
		] );

		add_action( "woocommerce_process_product_meta", [
			$this,
			"save",
		] );

		add_action( "admin_footer", [ $this, "custom_js" ] );
	}

	public function custom_js() {
		if ( get_post_type() === "product" ) {
			echo $this->render( "footer-js", [
				"class" => get_class( $this ),
			] );
		}
	}

	protected function render( $template_filename, $vars = [] ) {
		$include_template_path = "";
		$html                  = "";
		$template_path         = B4B_PLUGIN_PATH . "/templates/admin/product-panel/" . $template_filename . ".php";

		if ( file_exists( $template_path ) ) {
			$include_template_path = $template_path;
		}

		if ( empty( $include_template_path ) === false ) {
			ob_start();
			if ( is_array( $vars ) === false ) {
				$vars = [];
			}
			foreach ( $vars as $name => $value ) {
				$$name = $value;
			}

			include( $include_template_path );
			$html = ob_get_clean();
		}

		return $html;
	}
}