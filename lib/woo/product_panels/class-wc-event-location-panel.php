<?php

namespace B4B_Theme_Support\Lib\Woo\Product_Panels;

class WC_Event_Location_Panel extends WC_Product_Panel implements Interface_WC_Product_Panel {
	public function add_tabs( $options ) {
		$options["event_location"] = [
			"label"    => __( "Event Location", B4B_TEXT_DOMAIN ),
			"target"   => "event_location_product_data",
			"class"    => [
				"hide_if_simple",
				"hide_if_grouped",
				"hide_if_external",
				"hide_if_variable",
				"show_if_event_100_holes",
				"show_if_event_scramble",
				"show_if_event_party",
				"hide_if_donation",
			],
			"priority" => 110,
		];

		return $options;
	}

	public function add_panel() {

		global $post;

		echo $this->render( "event-location-panel", [
			"venue_name"     => ( $date = get_post_meta( $post->ID, "_b4b_event_venue_name", true ) ) ? $date : "",
			"venue_address1" => ( $date = get_post_meta( $post->ID, "_b4b_event_venue_address1", true ) ) ? $date : "",
			"venue_address2" => ( $date = get_post_meta( $post->ID, "_b4b_event_venue_address2", true ) ) ? $date : "",
			"venue_city"     => ( $date = get_post_meta( $post->ID, "_b4b_event_venue_city", true ) ) ? $date : "",
			"venue_state"    => ( $date = get_post_meta( $post->ID, "_b4b_event_venue_state", true ) ) ? $date : "",
			"venue_zip"      => ( $date = get_post_meta( $post->ID, "_b4b_event_venue_zip", true ) ) ? $date : "",
		] );
	}

	public function save( $post_id ) {
		if ( in_array( sanitize_text_field( $_REQUEST["product-type"] ), [
			"event_100_holes",
			"event_scramble",
			"event_party",
		] ) ) {

			$fields = [
				"_b4b_event_venue_name",
				"_b4b_event_venue_address1",
				"_b4b_event_venue_address2",
				"_b4b_event_venue_city",
				"_b4b_event_venue_state",
				"_b4b_event_venue_zip",
			];

			foreach ( $fields as $field_name ) {
				update_post_meta( $post_id, $field_name, sanitize_text_field( $_REQUEST[ $field_name ] ) );
			}
		}
	}
}