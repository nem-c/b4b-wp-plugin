<?php

namespace B4B_Theme_Support\Lib\Woo;

use WC_Order;

class B4B_WC_Admin_Events {

	protected $events = [
		"event_scramble",
		"event_100_holes",
		"event_party",
	];

	public function __construct() {
		add_action( "woocommerce_order_edit_status", [
			$this,
			"maybe_add_customer_to_event_golfers",
		], 10, 2 );
	}

	public function maybe_add_customer_to_event_golfers( $order_id, $status ) {

		if ( in_array( $status, [
				"processing",
				"completed",
				"on-hold"
			] ) === false ) { //for on-hold, processing,completed status
			return false;
		}

		$order    = new WC_Order( $order_id );
		$event_id = null;
		foreach ( $order->get_items() as $item ) {
			$event = $item->get_product();
			if ( empty( $event ) === false && in_array( $event->get_type(), $this->events ) ) {
				$event_id = $event->get_id();
			}
		}

		if ( $event_id ) {
			$golfers_list = get_post_meta( $event_id, "_b4b_event_golfers", true );

			$customer_id = $order->get_customer_id();
			if ( empty( $customer_id ) === true ) {
				// check for post
				$post_order_customer = intval( sanitize_text_field( $_POST["customer_user"] ) );
				if ( empty( $post_order_customer ) === false ) {
					$customer_id = $post_order_customer;
				}
			}

			if ( is_array( $golfers_list ) === false ) {
				$golfers_list = [];
			}

			if ( in_array( $order->get_customer_id(), $golfers_list ) === false ) {
				array_push( $golfers_list, $order->get_customer_id() );
			}

			$golfers_list = array_filter( $golfers_list ); // remove 0 values from array;

			update_post_meta( $event_id, "_b4b_event_golfers", $golfers_list );
		}

		return $order_id;
	}
}