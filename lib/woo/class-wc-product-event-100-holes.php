<?php

namespace B4B_Theme_Support\Lib\Woo;

use WC_Order_Query;

class WC_Product_Event_100_Holes extends WC_Product_Event {

	protected $object_type = "event_100_holes";

	/**
	 * Get internal type.
	 *
	 * @return string
	 */
	public function get_type() {
		return "event_100_holes";
	}

	/**
	 * Will get list of account who have registered before for this event
	 */
	public function get_registered_users()
	{
		new WC_Order_Query();
	}
}