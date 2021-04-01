<?php

namespace B4B_Theme_Support\Lib\Meta_Boxes;

use WC_Order;

class Woocommerce_Order_Scramble_Team_Meta_Box extends Meta_Box implements Interface_Meta_Box {
	protected $id = "woocommerce-order-scramble-team";
	protected $title = "Scramble Team";
	protected $screen = "shop_order";

	protected $context = Meta_Box::CONTEXT_NORMAL;
	protected $priority = Meta_Box::PRIORITY_DEFAULT;

	protected $nonce_name = "b4b_shop_order_scramble_team";
	protected $nonce_action = "b4b_shop_order_scramble_team_action";

	public function add_metabox() {
		global $post;
		if ( b4b_have_event_in_order( $post->ID, "event_scramble" ) ) {
			parent::add_metabox();
		}
	}

	public function render_metabox( $post ) {
		wp_nonce_field( $this->nonce_action, $this->nonce_name );
		$order = new WC_Order( $post->ID );

		$team_meta = get_post_meta( $order->get_id(), "_b4b_scramble_team_members", true );
		if ( empty( $team_meta ) === true ) {
			$team_meta = b4b_scramble_team_meta_blueprint();
		}

		echo $this->render( "woocommerce-order-scramble-team-meta-box", [
			"b4b_scramble_team_members" => $team_meta,
		] );
	}

	public function save_metabox( $post_id, $post ) {
		if ( $this->check_nonce_and_privileges( $post ) === false ) {
			return false;
		}

		array_walk_recursive( $_REQUEST["b4b_scramble_team_members"], "sanitize_text_field" );
		update_post_meta( $post_id, sprintf( "_%s", "b4b_scramble_team_members" ), $_REQUEST["b4b_scramble_team_members"] );
	}
}