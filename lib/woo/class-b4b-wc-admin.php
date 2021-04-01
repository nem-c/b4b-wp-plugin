<?php

namespace B4B_Theme_Support\Lib\Woo;

use B4B_Theme_Support\Lib\Post_Types\Post_Type;
use B4B_Theme_Support\Lib\Woo\Product_Panels\WC_Donation_Panel;
use B4B_Theme_Support\Lib\Woo\Product_Panels\WC_Event_Details_Panel;
use B4B_Theme_Support\Lib\Woo\Product_Panels\WC_Event_Location_Panel;

class B4B_WC_Admin {

	protected $products_panels;

	public function __construct() {
		add_filter( "product_type_options", [
			$this,
			"add_virtual_checkbox_to_events",
		] );

		add_filter( "woocommerce_product_data_tabs", [
			$this,
			"rearrange_event_data_tabs",
		] );

		add_filter( "manage_edit-shop_order_columns", [
			$this,
			"add_product_type_column_in_order"
		] );

		add_filter( "manage_shop_order_posts_custom_column", [
			$this,
			"add_product_type_column_content"
		] );

		add_action( "admin_footer", [ $this, "events_custom_js" ] );

		add_action( "admin_init", [ $this, "remove_appearance_menu_for_event_manager" ] );

		$this->products_panels["details"]  = new WC_Event_Details_Panel();
		$this->products_panels["location"] = new WC_Event_Location_Panel();
		$this->products_panels["donation"] = new WC_Donation_Panel();
	}

	public function remove_appearance_menu_for_event_manager() {
		$user = wp_get_current_user();
		$role = current( $user->roles );

		if ( in_array( $role, [ "b4b_event_manager" ] ) === true ) {
			remove_menu_page( 'themes.php' ); // Appearance
		}
	}

	public function add_product_type_column_in_order( $columns ) {

		$new_columns = array_slice( $columns, 0, 1, true ) +
					   [ "product_type" => "Product Type" ] +
					   array_slice( $columns, 1, null, true );

		return $new_columns;
	}

	public function add_product_type_column_content( $column ) {
		global $post;
		if ( $column !== "product_type" ) {
			return;
		}

		if ( b4b_have_donation_in_order( $post->ID ) ) {
			$content = "Donation";
		} elseif ( b4b_have_event_in_order( $post->ID, "event_100_holes" ) ) {
			$content = "100 Holes Registration";
		} elseif ( b4b_have_event_in_order( $post->ID, "event_scramble" ) ) {
			$content = "Scramble Registration";
		} elseif ( b4b_have_event_in_order( $post->ID, "event_party" ) ) {
			$content = "Event Ticket";
		} else {
			$order       = wc_get_order( $post->ID );
			$order_terms = [];
			foreach ( $order->get_items() as $order_item ) {
				$terms = wp_get_post_terms( $order_item->get_product_id(), "product_cat", [ "fields" => "names" ] );
				foreach ( $terms as $term ) {
					if ( in_array( $term, $order_terms ) === false ) {
						array_push( $order_terms, $term );
					}
				}
			}
			$content = implode( ",", $order_terms );
		}

		echo $content;
	}

	public function add_virtual_checkbox_to_events( $options ) {
		$options["virtual"]["wrapper_class"] = "show_if_simple show_if_event_100_holes show_if_event_scramble show_if_event_party show_if_donation";
		$options["virtual"]["default"]       = "yes";

		return $options;
	}

	public function rearrange_event_data_tabs( $options ) {
		$options["general"]["class"]        = array_merge( $options["general"]["class"], [
			"show_if_simple",
			"show_if_external",
			"show_if_variable",
			"show_if_event_100_holes",
			"show_if_event_scramble",
			"show_if_event_party",
			"show_if_donation",
		] );
		$options["inventory"]["class"]      = array_merge( $options["inventory"]["class"], [
			"show_if_event_100_holes",
			"show_if_event_scramble",
			"show_if_event_party",
			"hide_if_donation",
		] );
		$options["shipping"]["class"]       = array_merge( $options["shipping"]["class"], [
			"hide_if_event_100_holes",
			"hide_if_event_scramble",
			"hide_if_event_party",
			"hide_if_donation",
		] );
		$options["linked_product"]["class"] = array_merge( $options["linked_product"]["class"], [
			"hide_if_event_100_holes",
			"hide_if_event_scramble",
			"hide_if_event_party",
			"hide_if_donation",
		] );
		$options["attribute"]["class"]      = array_merge( $options["attribute"]["class"], [
			"hide_if_event_100_holes",
			"hide_if_event_scramble",
			"hide_if_event_party",
			"hide_if_donation",
		] );
		$options["variations"]["class"]     = array_merge( $options["variations"]["class"], [
			"hide_if_event_100_holes",
			"hide_if_event_scramble",
			"hide_if_event_party",
			"hide_if_donation",
		] );

		return $options;
	}

	public function events_custom_js() {
		if ( get_post_type() !== "product" ) {
			return false;
		}

		?>
		<script type="text/javascript">
            jQuery(document).ready(function ($) {
                $("#product-type").on("change", function () {
                    $(".show_if_" + $(this).val()).show();
                    $(".hide_if_" + $(this).val()).hide();
                });

                jQuery(".options_group.pricing, .wc-tabs .general_options.general_tab")
                    .addClass("show_if_event_100_holes")
                    .addClass("show_if_event_scramble")
                    .addClass("show_if_event_party")
                    .addClass("show_if_donation")
                    .show();
                $("#inventory_product_data .options_group, .form-field._sold_individually_field, .form-field._manage_stock_field")
                    .addClass("show_if_event_100_holes")
                    .addClass("show_if_event_scramble")
                    .addClass("show_if_event_party")
                    .show();
            });
		</script>
		<?php
	}
}