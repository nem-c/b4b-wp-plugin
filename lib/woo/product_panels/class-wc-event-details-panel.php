<?php

namespace B4B_Theme_Support\Lib\Woo\Product_Panels;

class WC_Event_Details_Panel extends WC_Product_Panel implements Interface_WC_Product_Panel {
	public function add_tabs( $options ) {
		$options["event_details"] = [
			"label"    => __( "Event Details", B4B_TEXT_DOMAIN ),
			"target"   => "event_details_product_data",
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
			"priority" => 100,
		];

		return $options;
	}

	public function add_panel() {

		global $post;

		echo $this->render( "event-details-panel", [
			"event_date"                    => ( $date = get_post_meta( $post->ID, "_b4b_event_date", true ) ) ? $date : "",
			"event_registration_start_date" => ( $date = get_post_meta( $post->ID, "_b4b_event_registration_start_date", true ) ) ? $date : "",
			"event_registration_end_date"   => ( $date = get_post_meta( $post->ID, "_b4b_event_registration_end_date", true ) ) ? $date : "",
		] );

	}

	public function save( $post_id ) {

		if ( in_array( sanitize_text_field( $_REQUEST["product-type"] ), [
			"event_100_holes",
			"event_scramble",
			"event_party",
		] ) ) {

			$fields = [
				"_b4b_event_date",
				"_b4b_event_registration_start_date",
				"_b4b_event_registration_end_date",
			];

			foreach ( $fields as $field_name ) {
				update_post_meta( $post_id, $field_name, sanitize_text_field( $_REQUEST[ $field_name ] ) );
			}
		}
	}
}

