<?php

namespace B4B_Theme_Support\Lib\Woo;

class WC_Product_Event_Scramble extends WC_Product_Event {

	protected $object_type = "event_scramble";

	/**
	 * Get internal type.
	 *
	 * @return string
	 */
	public function get_type() {
		return "event_scramble";
	}
}