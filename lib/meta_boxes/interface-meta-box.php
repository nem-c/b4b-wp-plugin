<?php

namespace B4B_Theme_Support\Lib\Meta_Boxes;

interface Interface_Meta_Box {
	function render_metabox( $post );

	function save_metabox( $post_id, $post );
}