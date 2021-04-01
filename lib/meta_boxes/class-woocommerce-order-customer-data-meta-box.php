<?php

namespace B4B_Theme_Support\Lib\Meta_Boxes;

use WC_Order;

class Woocommerce_Order_Customer_Data_Meta_Box extends Meta_Box implements Interface_Meta_Box {
	protected $id = "woocommerce-order-customer-data";
	protected $title = "Customer Data";
	protected $screen = "shop_order";

	protected $context = Meta_Box::CONTEXT_SIDE;
	protected $priority = Meta_Box::PRIORITY_DEFAULT;

	protected $nonce_name = "b4b_shop_order_customer_data";
	protected $nonce_action = "b4b_shop_order_customer_data_action";

	public function add_metabox() {
		global $post;
		if ( b4b_have_event_in_order( $post->ID, [ "event_100_holes", "event_scramble" ] ) ) {
			parent::add_metabox();
		}
	}

	public function render_metabox( $post ) {
		wp_nonce_field( $this->nonce_action, $this->nonce_name );
		$order = new WC_Order( $post->ID );

		echo $this->render( "woocommerce-order-customer-data-meta-box", [
			"b4b_tshirt_size" => get_post_meta( $order->get_id(), "_b4b_tshirt_size", true ),
			"b4b_hat_size"    => get_post_meta( $order->get_id(), "_b4b_hat_size", true ),
		] );
	}

	public function save_metabox( $post_id, $post ) {
		if ( $this->check_nonce_and_privileges( $post ) === false ) {
			return false;
		}

		if ( isset( $_REQUEST["b4b_tshirt_size"] ) ) {
			update_post_meta( $post->ID, "_b4b_tshirt_size", sanitize_text_field( $_REQUEST["b4b_tshirt_size"] ) );
		}
		if ( isset( $_REQUEST["b4b_hat_size"] ) ) {
			update_post_meta( $post->ID, "_b4b_hat_size", sanitize_text_field( $_REQUEST["b4b_hat_size"] ) );
		}
	}
}
