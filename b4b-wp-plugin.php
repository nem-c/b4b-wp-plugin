<?php

namespace B4B_Theme_Support;

use B4B_Theme_Support\Lib\Loader;

/**
 * @link              b4b-theme-support
 * @since             1.0.0
 * @package           b4b-theme-support
 *
 * @wordpress-plugin
 * Plugin Name:       Birdies 4 Brains theme support plugin
 * Description:       Define custom functionalities for B4B theme
 * Version:           1.0.0
 * Author:            Eutelnet
 * Author URI:        https://eutelnet.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       b4b-theme-support
 */

defined( 'ABSPATH' ) || die();

define( "B4B_TEXT_DOMAIN", "b4b-theme-support" );
define( "B4B_PLUGIN_PATH", dirname( __FILE__ ) );

require_once dirname( __FILE__ ) . "/lib/autoload.php";

/**
 * Init loader
 */
$loader = new Loader();
$loader->register_taxonomies();
$loader->register_post_types();
$loader->register_metaboxes();
$loader->customize_woo();

add_action( "acf/init", [ $loader, "register_blocks" ] );
add_action( "init", [ $loader, "load_shortcodes" ] );

require_once dirname( __FILE__ ) . "/lib/wc-b4b-functions.php";