<?php

namespace B4B_Theme_Support\Lib\Post_Types;

use B4B_Theme_Support\Lib\Meta_Boxes\Sponsor_Info_Meta_Box;
use B4B_Theme_Support\Lib\Meta_Boxes\Sponsor_Social_Meta_Box;

class Sponsor extends Post_Type implements Interface_Post_Type {

	protected $post_type = "sponsor";
	protected $label = "";
	protected $description = "";

	protected $supports = [ "title", "thumbnail" ];

	protected $rewrite = [
		"slug"       => "sponsors",
		"with_front" => true,
		"pages"      => true,
		"feeds"      => true,
	];

	protected $show_in_rest = true;

	protected $meta_boxes = [];

	public function __construct() {
		parent::__construct();
	}

	public function get_labels() {
		return [
			"name"                  => _x( "Sponsors", "Post Type General Name",
				B4B_TEXT_DOMAIN ),
			"singular_name"         => _x( "Sponsor", "Post Type Singular Name",
				B4B_TEXT_DOMAIN ),
			"menu_name"             => __( "Sponsors",
				B4B_TEXT_DOMAIN ),
			"name_admin_bar"        => __( "Sponsors",
				B4B_TEXT_DOMAIN ),
			"archives"              => __( "Item Archives",
				B4B_TEXT_DOMAIN ),
			"attributes"            => __( "Item Attributes",
				B4B_TEXT_DOMAIN ),
			"parent_item_colon"     => __( "Parent Sponsor:",
				B4B_TEXT_DOMAIN ),
			"all_items"             => __( "All Sponsors",
				B4B_TEXT_DOMAIN ),
			"add_new_item"          => __( "Add New Sponsor",
				B4B_TEXT_DOMAIN ),
			"add_new"               => __( "Add New",
				B4B_TEXT_DOMAIN ),
			"new_item"              => __( "New Sponsor",
				B4B_TEXT_DOMAIN ),
			"edit_item"             => __( "Edit Sponsor",
				B4B_TEXT_DOMAIN ),
			"update_item"           => __( "Update Sponsor",
				B4B_TEXT_DOMAIN ),
			"view_item"             => __( "View Sponsor",
				B4B_TEXT_DOMAIN ),
			"view_items"            => __( "View Sponsors",
				B4B_TEXT_DOMAIN ),
			"search_items"          => __( "Search Sponsor",
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
			"items_list"            => __( "Sponsors list",
				B4B_TEXT_DOMAIN ),
			"items_list_navigation" => __( "Sponsors list navigation",
				B4B_TEXT_DOMAIN ),
			"filter_items_list"     => __( "Filter Sponsors list",
				B4B_TEXT_DOMAIN ),
		];
	}

	public static function init() {
		if ( self::$instance instanceof Sponsor === false ) {
			self::$instance = new Sponsor();
		}

		return self::$instance;
	}
}