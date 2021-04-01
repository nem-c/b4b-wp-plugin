<?php

function b4b_scramble_team_meta_blueprint() {
	return [
		2 => [
			"name"        => null,
			"tshirt_size" => null,
			"hat_size"    => null,
		],
		3 => [
			"name"        => null,
			"tshirt_size" => null,
			"hat_size"    => null,
		],
		4 => [
			"name"        => null,
			"tshirt_size" => null,
			"hat_size"    => null,
		]
	];
}

function b4b_have_event_in_order( $order_id, $event_type = [ "event_scramble", "event_100_holes", "event_party" ] ) {
	return b4b_have_product_type_in_order( $order_id, $event_type );
}

function b4b_have_donation_in_order( $order_id ) {
	return b4b_have_product_type_in_order( $order_id, "donation" );
}

function b4b_have_product_type_in_order( $order_id, $product_type ) {
	$have_event = false;

	$order = wc_get_order( $order_id );
	if ( $order ) {
		foreach ( $order->get_items() as $order_item ) {
			$product    = $order_item->get_product();
			$have_event = b4b_is_product_event( $product, $product_type );
		}
	}

	return $have_event;
}

function b4b_tshirt_sizes( $first_empty = true, $first_empty_text = "Select" ) {
	$sizes = [];
	if ( $first_empty === true ) {
		$sizes[""] = __( $first_empty_text, B4B_TEXT_DOMAIN );
	}
	$sizes["S"]   = __( "Small", B4B_TEXT_DOMAIN );
	$sizes["M"]   = __( "Medium", B4B_TEXT_DOMAIN );
	$sizes["L"]   = __( "Large", B4B_TEXT_DOMAIN );
	$sizes["XL"]  = __( "XLarge", B4B_TEXT_DOMAIN );
	$sizes["XXL"] = __( "XXLarge", B4B_TEXT_DOMAIN );

	return $sizes;
}

function b4b_hat_sizes( $first_empty = true, $first_empty_text = "Select" ) {
	$sizes = [];
	if ( $first_empty === true ) {
		$sizes[""] = __( $first_empty_text, B4B_TEXT_DOMAIN );
	}
	$sizes["M/L"]  = __( "Medium/Large", B4B_TEXT_DOMAIN );
	$sizes["L/XL"] = __( "Large/XLarge", B4B_TEXT_DOMAIN );

	return $sizes;
}

function b4b_is_product_event( $product, $event_type = [ "event_scramble", "event_100_holes", "event_party" ] ) {
	if (empty($product) === true) {
		return false;
	}
	if ( is_array( $event_type ) === false ) {
		$event_type = [ $event_type ];
	}

	return in_array( $product->get_type(), $event_type );
}

function b4b_is_production_donation( $product ) {
	if (empty($product) === true) {
		return false;
	}
	return in_array( $product->get_type(), [ "donation" ] );
}

function b4b_is_customer_registered_for_event( $product_event, $customer_id = 0 ) {
	$registered = false;

	if ( $customer_id === 0 ) {
		$customer_id = get_current_user_id();
	}

	$query = new WC_Order_Query( [
		"status"         => [ "completed", "on-hold", "processing" ],
		"type"           => [ "shop_order" ],
		"customer_id"    => $customer_id,
		"posts_per_page" => - 1,
	] );

	$orders = $query->get_orders();
	if ( count( $orders ) > 0 ) {
		//check only if customer orders exist
		/** @var $order WC_Order */
		foreach ( $orders as $order ) {
			$items = $order->get_items();
			foreach ( $items as $item ) {
				if ( $item["product_id"] === $product_event->get_id() ) {
					$registered = true;
				}
			}
		}
	}


	return $registered;
}

function b4b_have_event_in_cart( $event_type = [ "event_100_holes", "event_scramble", "event_party" ] ) {
	if ( is_array( $event_type ) === false ) {
		$event_type = [ $event_type ];
	}

	return b4b_have_product_types_in_cart( $event_type );
}

function b4b_have_donation_in_cart() {
	return b4b_have_product_types_in_cart( [ "donation" ] );
}

function b4b_have_product_types_in_cart( $product_types = [] ) {
	$event_product_in_cart = false;

	if ( WC()->cart ) {
		foreach ( WC()->cart->get_cart_contents() as $item ) {
			if ( in_array( $item["data"]->get_type(), $product_types ) ) {
				$event_product_in_cart = true;
			}
		}
	}

	return $event_product_in_cart;
}

function b4b_array_sort_by_column( &$arr, $col, $dir = SORT_ASC ) {
	$sort_col = array();
	foreach ( $arr as $key => $row ) {
		$sort_col[ $key ] = strtolower($row[ $col ]);
	}

	array_multisort( $sort_col, $dir, $arr );
}
