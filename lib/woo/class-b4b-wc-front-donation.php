<?php

namespace B4B_Theme_Support\Lib\Woo;

use WC_Product;

class B4B_WC_Front_Donation {
	public function __construct() {
		add_action( "woocommerce_donation_add_to_cart", [
			$this,
			"add_donate_button",
		] );

		add_action( "woocommerce_before_add_to_cart_button", [
			$this,
			"add_donation_amount_input",
		], 25 );

		add_action( "woocommerce_before_add_to_cart_button", [
			$this,
			"add_golfers_dropdown",
		], 20 );

		add_filter( "woocommerce_add_to_cart_validation", [
			$this,
			"validate_donation_amount"
		], 10, 3 );

		add_filter( "woocommerce_add_cart_item_data", [
			$this,
			"calculate_donation_cart_item_prices",
		], 99, 4 );

		add_action( "woocommerce_before_calculate_totals", [
			$this,
			"recalculate_donation_totals",
		], 99 );


		add_action( "woocommerce_checkout_update_order_meta ", [
			$this,
			"add_golfer_meta_to_order",
		], 99, 2 );
	}

	public function add_donate_button() {
		$template_name     = "single-product/add-to-cart/donate.php";
		$template_location = wc_locate_template( $template_name );
		if ( file_exists( $template_location ) === false ) {
			$template_name = "single-product/add-to-cart/simple.php";
		}
		wc_get_template( $template_name );
	}

	public function add_donation_amount_input() {
		/**
		 * @var $product WC_Product_Donation
		 */
		global $product;

		if ( $product->get_type() !== "donation" ) {
			return false;
		}

		return woocommerce_form_field( "donation_amount", [
			"type"        => "text",
			"label"       => "Flat Rate Donation Amount",
			"placeholder" => "Amount to donate in \$",
			"maxlength"   => 10,
			"class"       => [ "small-input" ],
		] );
	}

	public function add_golfers_dropdown( $data ) {
		/**
		 * @var $product WC_Product_Donation
		 */
		global $product;

		$event = new WC_Product_Event_100_Holes( $product->get_meta( "_b4b_donation_event_id" ) );

		if ( empty( $event ) === true || $event->get_id() < 1 ) {
			return false;
		}

		$golfers_ids_list = get_post_meta( $event->get_id(), "_b4b_event_golfers", true );
		if ( empty( ( $golfers_ids_list ) ) === false ) {
			$golfers = [
				"" => "Select Golfer",
			];
			foreach ( $golfers_ids_list as $golfer_id ) {
				$first_name = get_user_meta( $golfer_id, "first_name", true );
				$last_name  = get_user_meta( $golfer_id, "last_name", true );

				$golfers[ $golfer_id ] = sprintf( "%s %s", $first_name, $last_name );
			}

			asort( $golfers );


			woocommerce_form_field( "golfer_id", [
				"type"    => "select",
				"options" => $golfers,
			], sanitize_text_field( $_REQUEST["for"] ) );
		}
	}

	public function validate_donation_amount( $success, $product_id, $quantity ) {
		$product = wc_get_product( $product_id );
		if ( $product->get_type() === "donation" ) {
			if ( isset( $_REQUEST["golfer_id"] ) ) {
				$golfer_id = intval( sanitize_text_field( $_REQUEST["golfer_id"] ) );
				if ( $golfer_id < 1 ) {
					$success = false;
					wc_add_notice( __( "Please select Golfer", B4B_TEXT_DOMAIN ), "error" );
				}
			}

			$donation_amount = intval( sanitize_text_field( $_REQUEST["donation_amount"] ) );
			if ( ( $donation_amount < 25 || $donation_amount > 100000 ) ) {
				$success = false;
				wc_add_notice( __( "Donation amount should be between $25 and $100000", B4B_TEXT_DOMAIN ), "error" );
			}
		}

		return $success;
	}

	public function calculate_donation_cart_item_prices( $cart_item_data, $product_id, $variation_id, $quantity ) {
		$product = wc_get_product( $product_id );
		if ( $product->get_type() === "donation" ) {
			$donation_amount   = intval( sanitize_text_field( $_REQUEST["donation_amount"] ) );
			$donated_golfer_id = intval( sanitize_text_field( $_REQUEST["golfer_id"] ) );

			$first_name = get_user_meta( $donated_golfer_id, "first_name", true );
			$last_name  = get_user_meta( $donated_golfer_id, "last_name", true );

			$cart_item_data["donation_amount"]     = $donation_amount;
			$cart_item_data["donated_golfer_id"]   = $donated_golfer_id;
			$cart_item_data["donated_golfer_name"] = sprintf( "%s %s", $first_name, $last_name );
		}

		return $cart_item_data;
	}

	public function recalculate_donation_totals( $cart ) {
		foreach ( $cart->get_cart() as $cart_item ) {
			$product = wc_get_product( $cart_item['product_id'] );
			if ( $product->get_type() === "donation" ) {
				$donation_amount = $cart_item["donation_amount"];
				if ( $donation_amount > 0 ) {
					$cart_item["data"]->set_price( $cart_item["donation_amount"] );
				}
				$cart_item["data"]->set_name( "Donation for " . $cart_item["donated_golfer_name"] );
			}
		}
	}

	function add_golfer_meta_to_order( $order_id, $data ) {
		$order_id;
		$data;
	}
}