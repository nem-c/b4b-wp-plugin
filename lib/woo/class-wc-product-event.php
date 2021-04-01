<?php

namespace B4B_Theme_Support\Lib\Woo;

use WC_Product;
use WC_Order_Query;
use WC_Order;

abstract class WC_Product_Event extends WC_Product {
	protected $post_type = "product_event";

	public function add_to_cart_text() {
		$text = $this->is_purchasable() && $this->is_in_stock() ? __( 'View event', B4B_TEXT_DOMAIN ) : __( 'View event', B4B_TEXT_DOMAIN );

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	public function single_add_to_cart_text() {
		if ( $this->is_in_cart() ) {
			return apply_filters( 'woocommerce_product_single_add_to_cart_text', _x( 'Checkout', 'placeholder', B4B_TEXT_DOMAIN ), $this );
		} else {
			return apply_filters( 'woocommerce_product_single_add_to_cart_text', _x( 'Register NOW!', 'placeholder', B4B_TEXT_DOMAIN ), $this );
		}
	}

	protected function is_in_cart() {
		$cart    = wc()->cart;
		$in_cart = false;
		foreach ( $cart->get_cart_contents() as $cart_item ) {
			if ( $in_cart === true ) {
				continue;
			}
			if ( $cart_item["product_id"] === $this->get_id() ) {
				$in_cart = true;
			}
		}

		return $in_cart;
	}

	public function is_purchasable() {
		$is_purchasable = true;

		$meta_start_date = get_post_meta( $this->get_id(), '_b4b_event_registration_start_date', true );
		$meta_end_date   = get_post_meta( $this->get_id(), '_b4b_event_registration_end_date', true );

		if ( $meta_start_date && $meta_end_date ) {
			$today      = date_create();
			$start_date = date_create_from_format( 'Y-m-d', $meta_start_date );
			$end_date   = date_create_from_format( 'Y-m-d', $meta_end_date );
			if ( $today < $start_date || $today > $end_date ) {
				$is_purchasable = false;
			}
		}

		if ( is_user_logged_in() === true ) {
			if ( b4b_is_customer_registered_for_event( $this ) ) {
				$is_purchasable = false;
			}
		}

		return $is_purchasable;
	}

}