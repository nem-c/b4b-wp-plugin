<?php

namespace B4B_Theme_Support\Lib\Post_Types;

interface Interface_Post_Type {
	function register();

	function get_rewrite();

	function get_labels();

	static function init();
}