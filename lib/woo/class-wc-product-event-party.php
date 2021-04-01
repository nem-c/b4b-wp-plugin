<?php

namespace B4B_Theme_Support\Lib\Woo;

class WC_Product_Event_Party extends WC_Product_Event {

	protected $object_type = "event_party";

	/**
	 * Get internal type.
	 *
	 * @return string
	 */
	public function get_type() {
		return "event_party";
	}
}