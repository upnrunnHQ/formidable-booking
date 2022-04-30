<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Shuchkin\SimpleXLSX;

/**
 * Formidable_Booking_Spreadsheet class.
 * @var [type]
 */
class Formidable_Booking_Spreadsheet {
	public function __construct() {
	}

	public function parse_spreadsheet( $xlsx = '' ) {
		if ( '' === $xlsx ) {
			return false;
		}

		$keys              = [ 'make', 'model', 'year', 'badge', 'body', 'engine_size_cc', 'engine_size_litres', 'cylinders', 'co2_emissions_combined_g_km', 'tare_mass_kg', 'kerb_weight_kg', 'gross_vehicle_mass_kg' ];
		$spreadsheet_items = [];

		if ( $_xlsx = SimpleXLSX::parse( $xlsx ) ) {
			foreach ( $_xlsx->rows() as $values ) {
				$row = array_combine( $keys, $values );
				if ( 'Make' === $row['make'] ) {
					continue;
				}

				$spreadsheet_items[] = $row;
			}

			return $spreadsheet_items;
		} else {
			return new WP_Error( 'parse_error', SimpleXLSX::parseError() );
		}
	}
}
