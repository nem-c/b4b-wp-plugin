<?php

namespace B4B_Theme_Support\Lib\Woo\Product_Panels;

use WP_Query;

class WC_Donation_Panel extends WC_Product_Panel implements Interface_WC_Product_Panel {
	public function add_tabs( $options ) {
		$options["donation"] = [
			"label"    => __( "Donation Details", B4B_TEXT_DOMAIN ),
			"target"   => "donation_product_data",
			"class"    => [
				"hide_if_simple",
				"hide_if_grouped",
				"hide_if_external",
				"hide_if_variable",
				"hide_if_event_100_holes",
				"hide_if_event_scramble",
				"hide_if_event_party",
				"show_if_donation",
			],
			"priority" => 120,
		];

		return $options;
	}

	public function add_panel() {

		global $post;

		$events = new WP_Query( [
			"post_type" => "product",
			"tax_query" => [
				[
					"taxonomy" => "product_type",
					"field"    => "slug",
					"terms"    => "event_100_holes",
				],
			],
			"posts_per_page" => - 1,
		] );

		$event_list = [];
		foreach ( $events->get_posts() as $event ) {
			$event_list[ $event->ID ] = sprintf( "%s ([%s] - [%s])",
				$event->post_title,
				get_post_meta( $event->ID, "_b4b_event_registration_start_date", true ),
				get_post_meta( $event->ID, "_b4b_event_registration_end_date", true ) );
		}

		echo $this->render( "donation-panel", [
			"events"              => $event_list,
			"donation_goal"       => intval( get_post_meta( $post->ID, "_b4b_donation_goal", true ) ),
			"donation_event_id"   => intval( get_post_meta( $post->ID, "_b4b_donation_event_id", true ) ),
			"donation_start_date" => ( $date = get_post_meta( $post->ID, "_b4b_donation_start_date", true ) ) ? $date : "",
			"donation_end_date"   => ( $date = get_post_meta( $post->ID, "_b4b_donation_end_date", true ) ) ? $date : "",
		] );

	}

	public function save( $post_id ) {

		if ( in_array( sanitize_text_field( $_REQUEST["product-type"] ), [
			"donation",
		] ) ) {

			$fields = [
				"_b4b_donation_goal",
				"_b4b_donation_event_id",
				"_b4b_donation_start_date",
				"_b4b_donation_end_date",
			];

			foreach ( $fields as $field_name ) {
				update_post_meta( $post_id, $field_name, sanitize_text_field( $_REQUEST[ $field_name ] ) );
			}
		}
	}
}