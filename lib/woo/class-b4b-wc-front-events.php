<?php

namespace B4B_Theme_Support\Lib\Woo;

use WC_Order;
use WC_Product;

class B4B_WC_Front_Events {

	protected $events = [
		"event_scramble",
		"event_100_holes",
		"event_party",
	];

	public function __construct() {
		add_action( "woocommerce_event_100_holes_add_to_cart", [
			$this,
			"add_register_button",
		] );
		add_action( "woocommerce_event_scramble_add_to_cart", [
			$this,
			"add_register_button",
		] );
		add_action( "woocommerce_event_party_add_to_cart", [
			$this,
			"add_register_button",
		] );

		add_filter( "woocommerce_add_to_cart_sold_individually_found_in_cart", [
			$this,
			"allow_duplicate_for_events_in_cart",
		], 15 );

		//digital goods can"t be shipped
		add_filter( "woocommerce_cart_needs_shipping", [
			$this,
			"events_disable_shipping",
		] );

		add_filter( "woocommerce_product_tabs", [
			$this,
			"add_location_tab_for_events",
		] );

		add_action( "woocommerce_payment_complete", [
			$this,
			"add_customer_to_golfer_list_after_payment",
		] );

		add_filter( "woocommerce_add_to_cart_validation", [
			$this,
			"allow_max_of_4_tickets_in_cart",
		], 1, 5 );

		add_filter( "woocommerce_update_cart_validation", [
			$this,
			"allow_max_of_4_tickets_on_cart_update",
		], 1, 4 );
	}

	public function add_register_button() {
		$template_name     = "single-product/add-to-cart/event.php";
		$template_location = wc_locate_template( $template_name );
		if ( file_exists( $template_location ) === false ) {
			$template_name = "single-product/add-to-cart/simple.php";
		}
		wc_get_template( $template_name );
	}

	public function allow_duplicate_for_events_in_cart() {
		return false;
	}

	public function events_disable_shipping() {
		return false;
	}

	public function add_location_tab_for_events( $tabs ) {
		global $product;

		if ( in_array( $product->get_type(), $this->events ) ) {
			$tabs["location"] = [
				"title"    => __( "Location", B4B_TEXT_DOMAIN ),
				"priority" => 50,
				"callback" => [ $this, "product_location_tab_content" ],
			];
		}

		return $tabs;
	}

	public function product_location_tab_content() {
		global $product;
		$maps_api_key = "AIzaSyBGimPbGb9CemYZzlkFfxFUpX7b38JcZeM";

		    //Place on the map will be located by venue name and not by address so we could pin precise place
		//$address1     = ( $val = get_post_meta( $product->get_id(), "_b4b_event_venue_address1", true ) ) ? $val : "";
        $name     = ( $val = get_post_meta( $product->get_id(), "_b4b_event_venue_name", true ) ) ? $val : "";
		$city         = ( $val = get_post_meta( $product->get_id(), "_b4b_event_venue_city", true ) ) ? $val : "";
		$state        = ( $val = get_post_meta( $product->get_id(), "_b4b_event_venue_state", true ) ) ? $val : "";
		$maps_address = str_replace( " ", "%20", "$name,$city $state" );

		wc_get_template(
			"woocommerce/single-product/tabs/location.php",
			[
				"maps_api_key" => $maps_api_key,
				"maps_address" => $maps_address,
			],
			"", B4B_PLUGIN_PATH . "/templates/" );
	}

	public function add_customer_to_golfer_list_after_payment( $order_id ) {

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

			if ( is_array( $golfers_list ) === false ) {
				$golfers_list = [];
				array_push( $golfers_list, $order->get_customer_id() );
			} else if ( in_array( $order->get_customer_id(), $golfers_list ) === false ) {
				array_push( $golfers_list, $order->get_customer_id() );
			}



			update_post_meta( $event_id, "_b4b_event_golfers", $golfers_list );
		}

		return $order_id;
	}

	public function allow_max_of_4_tickets_in_cart( $passed, $product_id, $quantity, $variation_id = "", $variations = "" ) {
		$product = wc_get_product( $product_id );
		if ( b4b_is_product_event( $product, "event_party" ) ) {
			if ( $quantity > 4 ) {
				$passed = false;
				wc_add_notice( "You can buy up to 4 tickets for this event", "error" );
			} else {
				//check for other cart items
				foreach ( WC()->cart->get_cart_contents() as $item ) {
					if ( $item["product_id"] === $product_id ) {
						if ( ( $item["quantity"] + $quantity ) > 4 ) {
							$passed = false;
							wc_add_notice( "You can buy up to 4 tickets for this event", "error" );
						}
					}
				}
			}
		}

		return $passed;
	}

	public function allow_max_of_4_tickets_on_cart_update( $passed, $cart_item_key, $values, $quantity ) {
		$product = wc_get_product( $values['product_id'] );
		if ( b4b_is_product_event( $product, "event_party" ) === true ) {
			if ($quantity > 4) {
				$passed = false;
				wc_add_notice( "You can buy up to 4 tickets for this event", "error" );
			}
		}

		return $passed;
	}
}