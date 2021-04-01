<?php

/**
 * @var $venue_name string
 * @var $venue_address1 string
 * @var $venue_address2 string
 * @var $venue_city string
 * @var $venue_state string
 * @var $venue_zip string
 */

?>

<div id="event_location_product_data" class="panel woocommerce_options_panel">
    <div class="options_group hide show_if_event_100_holes show_if_event_scramble show_if_event_party">
		<?php woocommerce_wp_text_input( [
			"label" => __( "Venue Name", B4B_TEXT_DOMAIN ),
			"class" => "normal",
			"value" => $venue_name,
			"name"  => "_b4b_event_venue_name",
		] ) ?>
		<?php woocommerce_wp_text_input( [
			"label" => __( "Address 1", B4B_TEXT_DOMAIN ),
			"class" => "normal",
			"value" => $venue_address1,
			"name"  => "_b4b_event_venue_address1",
		] ) ?>
		<?php woocommerce_wp_text_input( [
			"label" => __( "Address 2", B4B_TEXT_DOMAIN ),
			"class" => "normal",
			"value" => $venue_address2,
			"name"  => "_b4b_event_venue_address2",
		] ) ?>
		<?php woocommerce_wp_text_input( [
			"label" => __( "City", B4B_TEXT_DOMAIN ),
			"class" => "normal",
			"value" => $venue_city,
			"name"  => "_b4b_event_venue_city",
		] ) ?>
		<?php woocommerce_wp_text_input( [
			"label" => __( "ZIP Code", B4B_TEXT_DOMAIN ),
			"class" => "normal",
			"value" => $venue_zip,
			"name"  => "_b4b_event_venue_zip",
		] ) ?>
		<?php woocommerce_wp_text_input( [
			"label" => __( "State 2-letter Code", B4B_TEXT_DOMAIN ),
			"class" => "small",
			"value" => $venue_state,
			"name"  => "_b4b_event_venue_state",
		] ) ?>
    </div>
</div>
