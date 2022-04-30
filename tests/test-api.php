<?php
class ApiTest extends WP_UnitTestCase {
	const SPREADSHEET = '/Australia-Car-Database-Intraffic-DB.xlsx';
	const TOTAL_ROWS  = 47953;

	public function test_filter_spreadsheet_items() {
		update_option( 'ceva_booking_spreadsheet', plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . self::SPREADSHEET );
		$spreadsheet_items = Upnrunn\formidable_booking()->api->filter_spreadsheet_items();
		$this->assertCount( self::TOTAL_ROWS, $spreadsheet );
	}
}
