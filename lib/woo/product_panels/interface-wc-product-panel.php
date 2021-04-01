<?php

namespace B4B_Theme_Support\Lib\Woo\Product_Panels;

interface Interface_WC_Product_Panel {
	public function add_tabs( $options );

	public function add_panel();

	public function save( $post_id );
}