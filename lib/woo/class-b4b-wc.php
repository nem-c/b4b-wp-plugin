<?php

namespace B4B_Theme_Support\Lib\Woo;

class B4B_WC {

	protected $admin;
	protected $front;
	protected $products_panels = [];
	protected $plugins = [];

	public function __construct() {
		add_filter( "woocommerce_product_class", [
			$this,
			"assign_classes",
		], 15, 2 );
		add_filter( "product_type_selector", [
			$this,
			"add_event_types",
		] );

		add_filter( "woocommerce_customer_meta_fields", [
			$this,
			"add_tshirt_and_hat_fields_to_user_profile",
		] );

		add_action( "woocommerce_new_order", [
			$this,
			"update_order_meta",
		], 60 );

		add_filter( "woocommerce_order_data_store_cpt_get_orders_query", [
			$this,
			"handle_donated_golfer_query_var",
		], 10, 2 );

		add_filter( "woocommerce_product_data_store_cpt_get_products_query", [
			$this,
			"handle_donation_opened_range_query_var",
		], 10, 2 );

		add_filter( "woocommerce_product_data_store_cpt_get_products_query", [
			$this,
			"handle_event_year_query_var",
		], 10, 2 );

		add_filter( "woocommerce_product_data_store_cpt_get_products_query", [
			$this,
			"handle_donation_event_id_query_var",
		], 10, 2 );

		if ( is_admin() === true && ! wp_doing_ajax() ) {
			$this->admin = new B4B_WC_Admin();
		} else {
			$this->front = new B4B_WC_Front();
		}

		if ( is_admin() ) {
			$this->plugins["events"]["admin"] = new B4B_WC_Admin_Events();

			new B4B_WC_Admin_Dashboard_Widget();
		} else {
			$this->plugins["events"]["front"]   = new B4B_WC_Front_Events();
			$this->plugins["donation"]["front"] = new B4B_WC_Front_Donation();
		}
	}

	public function add_event_types( $types ) {
		$types["event_100_holes"] = __( "100 Holes Event" );
		$types["event_scramble"]  = __( "Scramble Captain Event" );
		$types["event_party"]     = __( "Event Party" );
		$types["donation"]        = __( "Donation" );

		return $types;
	}

	public function assign_classes( $classname, $product_type ) {
		switch ( $product_type ) {
			case "event_100_holes":
				$classname = "B4B_Theme_Support\Lib\Woo\WC_Product_Event_100_Holes";
				break;
			case "event_scramble":
				$classname = "B4B_Theme_Support\Lib\Woo\WC_Product_Event_Scramble";
				break;
			case "event_party":
				$classname = "B4B_Theme_Support\Lib\Woo\WC_Product_Event_Party";
				break;
			case "donation":
				$classname = "B4B_Theme_Support\Lib\Woo\WC_Product_Donation";
				break;
		}

		return $classname;
	}

	public function add_tshirt_and_hat_fields_to_user_profile( $fields ) {

		$fields["customer"] = [
			"title"  => __( "B4B golfer profile", B4B_TEXT_DOMAIN ),
			"fields" => [
				"b4b_tshirt_size" => [
					"label"       => "TShirt Size",
					"description" => "",
					"type"        => "select",
					"options"     => b4b_tshirt_sizes(),
				],
				"b4b_hat_size"    => [
					"label"       => "Hat Size",
					"description" => "",
					"type"        => "select",
					"options"     => b4b_hat_sizes(),
				],
			],
		];

		if ( current_user_can( 'administrator' ) ) {
			$fields['customer']['fields']['b4b_name_slug'] = [
				'type'        => 'text',
				'label'       => 'Donation name slug',
				'description' => 'WARNING: If you change this value, old links will stop working!',
			];
		}

		return $fields;
	}

	public function update_order_meta( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( empty( $order ) === true ) {
			return;
		}

		$request = $_REQUEST;

		if ( b4b_have_donation_in_cart() === true ) {
			foreach ( WC()->cart->get_cart() as $item ) {
				//mess with request a little
				$request["b4b_donated_golfer_id"] = $item["donated_golfer_id"];
				$request["b4b_dont_know_golfer"] = $item["dont_know_golfer"];
			}
		}

		$look_for_fields = [
			"b4b_tshirt_size",
			"b4b_hat_size",
			"b4b_scramble_team_members",
			"b4b_donated_golfer_id",
			"b4b_dont_know_golfer",
		];

		foreach ( $look_for_fields as $field ) {
			if ( isset( $request[ $field ] ) ) {
				if ( is_array( $request[ $field ] ) === true ) {
					array_walk_recursive( $request[ $field ], "sanitize_text_field" );
				} else {
					$request[ $field ] = sanitize_text_field( $request[ $field ] );
				}
				update_post_meta( $order->get_id(), sprintf( "_%s", $field ), $request[ $field ] );
			}
		}
	}

	public function handle_donated_golfer_query_var( $query, $query_vars ) {
		if ( empty( $query_vars["donated_golfer_id"] ) === false ) {
			$query["meta_query"][] = [
				"key"   => "_b4b_donated_golfer_id",
				"value" => esc_attr( $query_vars["donated_golfer_id"] ),
			];
		}

		return $query;
	}

	function handle_donation_opened_range_query_var( $query, $query_vars ) {
		if ( empty( $query_vars["donation_opened_range"] ) === false ) {
			$query["meta_query"][] = [
				"relation" => "and",
				[
					"key"     => "_b4b_donation_start_date",
					"value"   => explode( "...", $query_vars["donation_opened_range"] ),
					"compare" => "between",
					"type"    => "date",
				],
				[
					"key"     => "_b4b_donation_end_date",
					"value"   => explode( "...", $query_vars["donation_opened_range"] ),
					"compare" => "between",
					"type"    => "date",
				],
			];
		}

		return $query;
	}

	function handle_event_year_query_var( $query, $query_vars ) {

		if ( empty( $query_vars["event_year"] ) === false ) {
			$query["meta_query"][] = [
				"key"   => "_b4b_event_date",
				"value" => [
					sprintf( "%d-01-01", $query_vars["event_year"] ),
					sprintf( "%d-12-31", $query_vars["event_year"] )
				],
			];
		}

		return $query;
	}

	function handle_donation_event_id_query_var( $query, $query_vars ) {
		if ( empty( $query_vars["donation_event_id"] ) === false ) {
			$query["meta_query"][] = [
				"key"   => "_b4b_donation_event_id",
				"value" => intval( $query_vars["donation_event_id"] ),
				"type"  => "unsigned",

			];
		}

		return $query;
	}
}