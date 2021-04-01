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
define( 'B4B_PLUGIN_DB_VERSION', '1.0' );

require_once dirname( __FILE__ ) . "/lib/autoload.php";


// Database table init
function b4b_db_install()
{
	global $wpdb;

	$table_name = $wpdb->prefix . 'b4b_mails';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id int NOT NULL AUTO_INCREMENT,
		type varchar(50) NOT NULL,
		type_label varchar(50) NOT NULL,
		frequency_type varchar(50) NOT NULL,
		frequency_value int NOT NULL,
		email_subject varchar(200) NOT NULL,
		created_at datetime NOT NULL,
		updated_at datetime NOT NULL,
		last_sent_at datetime NULL,
		next_send_at datetime NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'b4b_db_version', B4B_PLUGIN_DB_VERSION );
}
function b4b_db_install_data()
{
	global $wpdb;

	$table_name = $wpdb->prefix . 'b4b_mails';

	$wpdb->insert( $table_name, [
		'type'            => 'money-raised',
		'type_label'      => 'Money raised',
		'frequency_type'  => 'manual',
		'frequency_value' => 0,
		'email_subject'   => "You've Raised {MONEY_RAISED} for Birdies 4 Brains",
		'created_at'      => current_time( 'mysql' ),
		'updated_at'      => current_time( 'mysql' ),
	] );
	$wpdb->insert( $table_name, [
		'type'            => 'missing-info-scramble-team',
		'type_label'      => 'Missing info - scramble team',
		'frequency_type'  => 'manual',
		'frequency_value' => 0,
		'email_subject'   => "We're missing info for your scramble team - Please Take Action",
		'created_at'      => current_time( 'mysql' ),
		'updated_at'      => current_time( 'mysql' ),
	] );
	$wpdb->insert( $table_name, [
		'type'            => 'payment-past-due',
		'type_label'      => 'Payment past due',
		'frequency_type'  => 'manual',
		'frequency_value' => 0,
		'email_subject'   => "You're Payment is past due",
		'created_at'      => current_time( 'mysql' ),
		'updated_at'      => current_time( 'mysql' ),
	] );
	$wpdb->insert( $table_name, [
		'type'            => 'missing-info-100-hole-event',
		'type_label'      => 'Missing info - 100 hole event',
		'frequency_type'  => 'manual',
		'frequency_value' => 0,
		'email_subject'   => "We're missing info on you for the 100 hole event - Please Take Action",
		'created_at'      => current_time( 'mysql' ),
		'updated_at'      => current_time( 'mysql' ),
	] );
}
register_activation_hook( __FILE__, 'b4b_db_install' );
register_activation_hook( __FILE__, 'b4b_db_install_data' );
//function b4b_update_db_check() {
//    if ( get_site_option( 'b4b_db_version' ) != B4B_PLUGIN_DB_VERSION ) {
//        b4b_db_install();
//    }
//}
//add_action( 'plugins_loaded', 'b4b_update_db_check' );
//if ( $_GET['install'] === 'db' ) {
//	print_r('RADI3');
//	b4b_db_install();
//	b4b_db_install_data();
////	die;
//}

/**
 * Init loader
 */
$loader = new Loader();
$loader->register_taxonomies();
$loader->register_post_types();
$loader->register_metaboxes();
$loader->customize_woo();
$loader->init_mail();

add_action( "acf/init", [ $loader, "register_blocks" ] );
add_action( "init", [ $loader, "load_shortcodes" ] );

require_once dirname( __FILE__ ) . "/lib/wc-b4b-functions.php";
