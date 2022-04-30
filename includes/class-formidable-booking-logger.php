<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Formidable_Booking_Logger class.
 * @var [type]
 */
class Formidable_Booking_Logger {
	public function __construct() {
		$this->logger = new Logger( 'ceva_booking' );
		$this->logger->pushHandler( new StreamHandler( plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . '/ceva_booking.log', Logger::DEBUG ) );
	}

	/**
	 * Undocumented function
	 * 
	 * formidable_booking()->logger->info( 'Files', $movefile );
	 *
	 * @param [type] $message
	 * @param array $context
	 * @return void
	 */
	public function info( $message, $context = [] ) {
		$this->logger->info( $message, $context );
	}
}
