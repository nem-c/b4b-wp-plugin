<?php

namespace B4B_Theme_Support\Lib\Acf_Blocks;

class Sponsors_Block {
	public function __construct() {
		$this->init();
	}

	public function init() {
		// check function exists
		if ( function_exists( "acf_register_block" ) ) {

			// register a testimonial block
			acf_register_block( [
				"name"            => "acf/sponsors_grid",
				"title"           => __( "Sponsors Grid" ),
				"description"     => __( "A custom sponsors grid block." ),
				"render_callback" => [ $this, "block_render" ],
				"category"        => "layout",
				"icon"            => "grid_on",
				"keywords"        => [ "sponsors", "grid" ],
			] );
		}
	}

	public function block_render( $block ) {

		$slug             = str_replace( "acf/", "", $block["name"] );
		$theme_file_path  = STYLESHEETPATH . "/template-parts/blocks/{$slug}.php";
		$plugin_file_path = B4B_PLUGIN_PATH . "/templates/blocks/{$slug}.php";

		if ( file_exists( $theme_file_path ) ) {
			$return_template = $theme_file_path;
		} else {
			$return_template = $plugin_file_path;
		}

		$sponsors_query = new \WP_Query( [
			"post_type"      => "sponsor",
			"order"			 => "ASC",
			"posts_per_page" => - 1,
		] );

		$sponsors = [];

		foreach ( $sponsors_query->posts as $item ) {

			$sponsor_id = $item->ID;

			$sponsor_info   = get_post_meta( $sponsor_id, "b4b_sponsor_info", true );
			$sponsor_social = get_post_meta( $sponsor_id, "b4b_sponsor_social", true );

			$sponsor_data = [
				"title"         => get_the_title( $item ),
				"subtitle"      => $sponsor_info["b4b_sponsor_subtitle"],
				"website_url"   => $sponsor_info["b4b_sponsor_url"],
				"facebook_url"  => $sponsor_social["b4b_sponsor_social_facebook_url"],
				"instagram_url" => $sponsor_social["b4b_sponsor_social_instagram_url"],
				"twitter_url"   => $sponsor_social["b4b_sponsor_social_twitter_url"],
				"linkedin_url"  => $sponsor_social["b4b_sponsor_social_linkedin_url"],
			];
			if ( has_post_thumbnail( $item ) ) {
				$sponsor_data["logo_url"] = current( wp_get_attachment_image_src( get_post_thumbnail_id( $item ), $size = 'b4b_sponsor_logo_listing' ) );
			}
			array_push( $sponsors, $sponsor_data );
		}

		include( $return_template );
	}
}