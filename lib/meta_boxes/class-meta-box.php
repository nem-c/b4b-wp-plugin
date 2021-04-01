<?php

namespace B4B_Theme_Support\Lib\Meta_Boxes;

abstract class Meta_Box {

	protected $id;
	protected $title;
	protected $screen;
	protected $context = Meta_Box::CONTEXT_ADVANCED;
	protected $priority = Meta_Box::PRIORITY_DEFAULT;

	protected $nonce_name;
	protected $nonce_action;

	const CONTEXT_ADVANCED = "advanced";
	const CONTEXT_NORMAL = "normal";
	const CONTEXT_SIDE = "side";

	const PRIORITY_DEFAULT = "default";
	const PRIORITY_HIGH = "high";
	const PRIORITY_LOW = "low";

	public function __construct( $post_type = false ) {
		if ( empty( $post_type ) === false ) {
			$this->screen = $post_type;
		}

		add_action( "add_meta_boxes", [ $this, "add_metabox" ], 25 );
		add_action( "save_post", [ $this, "save_metabox" ], 15, 2 );
	}

	public function add_metabox() {

		add_meta_box(
			$this->id,
			__( $this->title, "B4B_TEXT_DOMAIN" ),
			[ $this, "render_metabox" ],
			$this->screen,
			$this->context,
			$this->priority
		);

	}

	public function render( $template_filename, $vars = [] ) {
		$include_template_path = "";
		$html                  = "";
		$template_path         = B4B_PLUGIN_PATH . "/templates/admin/meta-boxes/" . $template_filename . ".php";

		if ( file_exists( $template_path ) ) {
			$include_template_path = $template_path;
		}

		if ( empty( $include_template_path ) === false ) {
			ob_start();
			if ( is_array( $vars ) === false ) {
				$vars = [];
			}
			foreach ( $vars as $name => $value ) {
				$$name = $value;
			}

			include( $include_template_path );
			$html = ob_get_clean();
		}

		return $html;
	}

	protected function check_nonce_and_privileges( $post ) {
		$can = true;
		// Add nonce for security and authentication.
		$nonce_name = null;
		// Check if a nonce is set.
		if ( isset( $_POST[ $this->nonce_name ] ) === false ) {
			$can = false;
		} else {
			$nonce_name = $_POST[ $this->nonce_name ];
		}

		// Check if a nonce is valid.
		if ( wp_verify_nonce( $nonce_name, $this->nonce_action ) === false ) {
			$can = false;
		}

		// Check if the user has permissions to save data.
		if ( current_user_can( 'edit_post', $post->ID ) === false ) {
			$can = false;
		}

		// Check if it's not an autosave.
		if ( wp_is_post_autosave( $post ) === true ) {
			$can = false;
		}

		// Check if it's not a revision.
		if ( wp_is_post_revision( $post ) === true ) {
			$can = false;
		}

		return $can;
	}
}