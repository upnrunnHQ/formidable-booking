<?php
class SpreadsheetTest extends WP_UnitTestCase {
	const SPREADSHEET = '/Australia-Car-Database-Intraffic-DB.xlsx';
	const TOTAL_ROWS  = 47953;

	public function test_parse_spreadsheet() {
		$spreadsheet = Upnrunn\formidable_booking()->spreadsheet->parse_spreadsheet( plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . self::SPREADSHEET );
		$this->assertCount( self::TOTAL_ROWS, $spreadsheet );
	}
}
