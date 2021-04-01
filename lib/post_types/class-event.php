<?php

namespace B4B_Theme_Support\Lib\Post_Types;

class Event extends Post_Type implements Interface_Post_Type {

	protected $post_type = "event";
	protected $label = "";
	protected $description = "";

	protected $supports = [ "title", "editor", "thumbnail", "custom-fields" ];

	protected $rewrite = [
		"slug"       => "events",
		"with_front" => true,
		"pages"      => true,
		"feeds"      => true,
	];

	protected $show_in_rest = true;

	public function get_labels() {
		return [
			"name"                  => _x( "Events", "Post Type General Name",
				B4B_TEXT_DOMAIN ),
			"singular_name"         => _x( "Event", "Post Type Singular Name",
				B4B_TEXT_DOMAIN ),
			"menu_name"             => __( "Events",
				B4B_TEXT_DOMAIN ),
			"name_admin_bar"        => __( "Events",
				B4B_TEXT_DOMAIN ),
			"archives"              => __( "Item Archives",
				B4B_TEXT_DOMAIN ),
			"attributes"            => __( "Item Attributes",
				B4B_TEXT_DOMAIN ),
			"parent_item_colon"     => __( "Parent Event:",
				B4B_TEXT_DOMAIN ),
			"all_items"             => __( "All Events",
				B4B_TEXT_DOMAIN ),
			"add_new_item"          => __( "Add New Event",
				B4B_TEXT_DOMAIN ),
			"add_new"               => __( "Add New",
				B4B_TEXT_DOMAIN ),
			"new_item"              => __( "New Event",
				B4B_TEXT_DOMAIN ),
			"edit_item"             => __( "Edit Event",
				B4B_TEXT_DOMAIN ),
			"update_item"           => __( "Update Event",
				B4B_TEXT_DOMAIN ),
			"view_item"             => __( "View Event",
				B4B_TEXT_DOMAIN ),
			"view_items"            => __( "View Events",
				B4B_TEXT_DOMAIN ),
			"search_items"          => __( "Search Event",
				B4B_TEXT_DOMAIN ),
			"not_found"             => __( "Not found",
				B4B_TEXT_DOMAIN ),
			"not_found_in_trash"    => __( "Not found in Trash",
				B4B_TEXT_DOMAIN ),
			"featured_image"        => __( "Featured Image",
				B4B_TEXT_DOMAIN ),
			"set_featured_image"    => __( "Set featured image",
				B4B_TEXT_DOMAIN ),
			"remove_featured_image" => __( "Remove featured image",
				B4B_TEXT_DOMAIN ),
			"use_featured_image"    => __( "Use as featured image",
				B4B_TEXT_DOMAIN ),
			"insert_into_item"      => __( "Insert into item",
				B4B_TEXT_DOMAIN ),
			"uploaded_to_this_item" => __( "Uploaded to this item",
				B4B_TEXT_DOMAIN ),
			"items_list"            => __( "Events list",
				B4B_TEXT_DOMAIN ),
			"items_list_navigation" => __( "Events list navigation",
				B4B_TEXT_DOMAIN ),
			"filter_items_list"     => __( "Filter events list",
				B4B_TEXT_DOMAIN ),
		];
	}

	public static function init() {
		if ( self::$instance instanceof Event === false ) {
			self::$instance = new Event();
		}

		return self::$instance;
	}
}