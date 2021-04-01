<?php

namespace B4B_Theme_Support\Lib\Meta_Boxes;

class Sponsor_Info_Meta_Box extends Meta_Box implements Interface_Meta_Box {

	protected $id = "b4b_sponsor_info";
	protected $title = "Sponsor Info";
	protected $screen = "sponsor";

	protected $context = Meta_Box::CONTEXT_NORMAL;
	protected $priority = Meta_Box::PRIORITY_LOW;

	protected $nonce_name = "b4b_sponsor_info_nonce";
	protected $nonce_action = "b4b_sponsor_info_nonce_action";

	public function render_metabox( $post ) {
		wp_nonce_field( $this->nonce_action, $this->nonce_name );

		$sponsor_info = get_post_meta( $post->ID, "b4b_sponsor_info", true );
		echo $this->render( "sponsor-info-meta-box", $sponsor_info );
	}

	public function save_metabox( $post_id, $post ) {
		if ( $this->check_nonce_and_privileges( $post ) === false ) {
			return false;
		}

		$sponsor_info = [];
		foreach ( [ "b4b_sponsor_subtitle", "b4b_sponsor_url" ] as $field_name ) {
			$sponsor_info[ $field_name ] = sanitize_text_field( $_POST[ $field_name ] );
		}

		update_post_meta( $post->ID, "b4b_sponsor_info", $sponsor_info );
	}
}