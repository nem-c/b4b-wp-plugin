<?php

namespace B4B_Theme_Support\Lib\Meta_Boxes;

use WC_Order;

class Woocommerce_Order_Donated_Golfer_Meta_Box extends Meta_Box implements Interface_Meta_Box {
	protected $id = "woocommerce-order-donated-golfer";
	protected $title = "Donated Golfer";
	protected $screen = "shop_order";

	protected $context = Meta_Box::CONTEXT_SIDE;
	protected $priority = Meta_Box::PRIORITY_DEFAULT;

	protected $nonce_name = "b4b_shop_order_donated_golfer";
	protected $nonce_action = "b4b_shop_order_donated_golfer_action";

	public function add_metabox() {
		global $post;
		if ( b4b_have_donation_in_order( $post->ID ) ) {
			parent::add_metabox();
		}
	}

	public function render_metabox( $post ) {
		wp_nonce_field( $this->nonce_action, $this->nonce_name );
		$order = new WC_Order( $post->ID );

		$order_items   = $order->get_items();
		$donation_item = current( $order_items );
		$product       = wc_get_product( $donation_item->get_data()["product_id"] );
		$event         = wc_get_product( $product->get_meta( "_b4b_donation_event_id" ) );

		$registered_golfers = [];
		$golfers_ids_list   = get_post_meta( $event->get_id(), "_b4b_event_golfers", true );
		if ( empty( ( $golfers_ids_list ) ) === false ) {
			foreach ( $golfers_ids_list as $golfer_id ) {
				$first_name = get_user_meta( $golfer_id, "first_name", true );
				$last_name  = get_user_meta( $golfer_id, "last_name", true );

				$registered_golfers[ $golfer_id ] = sprintf( "%s %s", $first_name, $last_name );
			}
		}

		asort( $registered_golfers );

		echo $this->render( "woocommerce-order-donated-golfer-meta-box", [
			"registered_golfers"    => $registered_golfers,
			"b4b_donated_golfer_id" => intval( get_post_meta( $order->get_id(), "_b4b_donated_golfer_id", true ) ),
			"b4b_dont_know_golfer" => intval( get_post_meta( $order->get_id(), "_b4b_dont_know_golfer", true ) ),
		] );
	}

	public function save_metabox( $post_id, $post ) {
		if ( $this->check_nonce_and_privileges( $post ) === false ) {
			return false;
		}

		if ( isset( $_REQUEST["b4b_donated_golfer_id"] ) ) {
			update_post_meta( $post->ID, "_b4b_donated_golfer_id", sanitize_text_field( $_REQUEST["b4b_donated_golfer_id"] ) );
		}
	}
}
