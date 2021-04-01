<?php

namespace B4B_Theme_Support\Lib\Post_Types;

abstract class Post_Type implements Interface_Post_Type {
	protected $post_type = "";
	protected $label = "";
	protected $description = "";

	protected $supports = [ "title", "editor", "thumbnail" ];
	protected $hierarchical = true;
	protected $public = true;
	protected $show_ui = true;
	protected $show_in_menu = true;
	protected $menu_position = 99;
	protected $show_in_admin_bar = true;
	protected $show_in_nav_menus = true;
	protected $can_export = true;
	protected $has_archive = true;
	protected $exclude_from_search = false;
	protected $publicly_queryable = true;
	protected $capability_type = "post";
	protected $show_in_rest = false;

	protected $rewrite = [
		"slug"       => "",
		"with_front" => true,
		"pages"      => true,
		"feeds"      => true,
	];

	public static $instance;

	public function __construct() {
		add_action( "init", [ $this, "register" ], 1 );
	}

	public function register() {
		register_post_type( $this->post_type, [
			"label"               => __( $this->label,
				B4B_TEXT_DOMAIN ),
			"description"         => __( $this->description,
				B4B_TEXT_DOMAIN ),
			"labels"              => $this->get_labels(),
			"supports"            => $this->supports,
			"hierarchical"        => $this->hierarchical,
			"public"              => $this->public,
			"show_ui"             => $this->show_ui,
			"show_in_menu"        => $this->show_in_menu,
			"menu_position"       => $this->menu_position,
			"show_in_admin_bar"   => $this->show_in_admin_bar,
			"show_in_nav_menus"   => $this->show_in_nav_menus,
			"can_export"          => $this->can_export,
			"has_archive"         => $this->has_archive,
			"exclude_from_search" => $this->exclude_from_search,
			"publicly_queryable"  => $this->publicly_queryable,
			"rewrite"             => $this->get_rewrite(),
			"capability_type"     => $this->capability_type,
			'show_in_rest'        => $this->show_in_rest,
		] );
	}

	public function get_rewrite() {
		return $this->rewrite;
	}

	public function get_post_type_name() {
		return $this->post_type;
	}
}