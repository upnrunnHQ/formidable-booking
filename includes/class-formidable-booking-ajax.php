<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Formidable_Booking_Ajax class.
 * @var [type]
 */
class Formidable_Booking_Ajax {
	/**
	 * The single instance of the class.
	 * @var [type]
	 */
	protected static $_instance = null;

	/**
	 * Main Formidable_Booking_Ajax instance.
	 * Ensures only one instance of Formidable_Booking_Ajax is loaded or can be loaded.
	 *
	 * @return [type] [description]
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Formidable_Booking_Ajax constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Init hooks.
	 * @return [type] [description]
	 */
	private function hooks() {
		add_action( 'wp_ajax_fetch_ceva_makes', [ $this, 'fetch_ceva_makes' ] );
		add_action( 'wp_ajax_nopriv_fetch_ceva_makes', [ $this, 'fetch_ceva_makes' ] );

		add_action( 'wp_ajax_fetch_ceva_models', [ $this, 'fetch_ceva_models' ] );
		add_action( 'wp_ajax_nopriv_fetch_ceva_models', [ $this, 'fetch_ceva_models' ] );

		add_action( 'wp_ajax_fetch_ceva_years', [ $this, 'fetch_ceva_years' ] );
		add_action( 'wp_ajax_nopriv_fetch_ceva_years', [ $this, 'fetch_ceva_years' ] );

		add_action( 'wp_ajax_fetch_ceva_badges', [ $this, 'fetch_ceva_badges' ] );
		add_action( 'wp_ajax_nopriv_fetch_ceva_badges', [ $this, 'fetch_ceva_badges' ] );

		add_action( 'wp_ajax_fetch_ceva_bodytypes', [ $this, 'fetch_ceva_bodytypes' ] );
		add_action( 'wp_ajax_nopriv_fetch_ceva_bodytypes', [ $this, 'fetch_ceva_bodytypes' ] );

		add_action( 'wp_ajax_fetch_ceva_suburbs', [ $this, 'fetch_ceva_suburbs' ] );
		add_action( 'wp_ajax_nopriv_fetch_ceva_suburbs', [ $this, 'fetch_ceva_suburbs' ] );

		add_action( 'wp_ajax_fetch_ceva_depots', [ $this, 'fetch_ceva_depots' ] );
		add_action( 'wp_ajax_nopriv_fetch_ceva_depots', [ $this, 'fetch_ceva_depots' ] );

		add_action( 'wp_ajax_fetch_info', [ $this, 'fetch_info' ] );
		add_action( 'wp_ajax_nopriv_fetch_info', [ $this, 'fetch_info' ] );

		add_action( 'wp_ajax_ceva_track', [ $this, 'track' ] );
		add_action( 'wp_ajax_nopriv_ceva_track', [ $this, 'track' ] );
	}

	/**
	 * [fetch_ceva_makes description]
	 * @return [type] [description]
	 */
	public function fetch_ceva_makes() {
		$filters           = [];
		$spreadsheet_items = formidable_booking()->api->filter_spreadsheet_items( $filters );
		$makes             = wp_list_pluck( $spreadsheet_items, 'make' );
		$makes             = array_unique( $makes );

		wp_send_json( $this->prepare_select2_response( $makes ) );
	}

	/**
	 * [fetch_ceva_models description]
	 * @return [type] [description]
	 */
	public function fetch_ceva_models() {
		$filters           = [
			'make' => $_POST['make'],
		];
		$spreadsheet_items = formidable_booking()->api->filter_spreadsheet_items( $filters );
		$models            = wp_list_pluck( $spreadsheet_items, 'model' );
		$models            = array_unique( $models );

		wp_send_json( $this->prepare_select2_response( $models ) );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function fetch_ceva_years() {
		$filters           = [
			'make'  => $_POST['make'],
			'model' => $_POST['model'],
		];
		$spreadsheet_items = formidable_booking()->api->filter_spreadsheet_items( $filters );
		$years             = wp_list_pluck( $spreadsheet_items, 'year' );
		$years             = array_unique( $years );

		wp_send_json( $this->prepare_select2_response( $years ) );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function fetch_ceva_badges() {
		$filters           = [
			'make'  => $_POST['make'],
			'model' => $_POST['model'],
			'year'  => $_POST['year'],
		];
		$spreadsheet_items = formidable_booking()->api->filter_spreadsheet_items( $filters );
		$badges            = wp_list_pluck( $spreadsheet_items, 'badge' );
		$badges            = array_unique( $badges );

		wp_send_json( $this->prepare_select2_response( $badges ) );
	}

	/**
	 * [fetch_ceva_bodytypes description]
	 * @return [type] [description]
	 */
	public function fetch_ceva_bodytypes() {
		$filters           = [
			'make'  => $_POST['make'],
			'model' => $_POST['model'],
			'year'  => $_POST['year'],
			'badge' => $_POST['badge'],
		];
		$spreadsheet_items = formidable_booking()->api->filter_spreadsheet_items( $filters );
		$bodytypes         = wp_list_pluck( $spreadsheet_items, 'body' );
		$bodytypes         = array_unique( $bodytypes );

		wp_send_json( $this->prepare_select2_response( $bodytypes ) );
	}

	/**
	 * [fetch_ceva_suburbs description]
	 * @return [type] [description]
	 */
	public function fetch_ceva_suburbs() {
		$results = [];
		if ( isset( $_GET['search'] ) && ( '' !== $_GET['search'] ) ) {
			$suburbs = formidable_booking()->api->fetch_suburbs( $_GET['search'] );
			foreach ( $suburbs as $suburb ) {
				$results[] = [
					'id'       => $suburb['Suburb'],
					'text'     => "{$suburb['Suburb']}, {$suburb['State']}, {$suburb['PostCode']}",
					'state'    => $suburb['State'],
					'postcode' => $suburb['PostCode'],
				];
			}
		}

		$response = [
			'results'    => $results,
			'pagination' => [
				'more' => false,
			],
		];

		wp_send_json( $response );
	}

	/**
	 * [fetch_ceva_depots description]
	 * @return [type] [description]
	 */
	public function fetch_ceva_depots() {
		$results = [];
		if ( isset( $_GET['search'] ) && ( '' !== $_GET['search'] ) ) {
			$depots          = formidable_booking()->api->fetch_depots();
			$filtered_depots = array_filter(
				$depots,
				function( $depot ) {
					$suburb     = strtolower( $depot['Suburb'] );
					$depot_name = strtolower( $depot['DepotName'] );
					$search     = strtolower( $_GET['search'] );

					return ( strpos( $suburb, $search ) !== false ) || ( strpos( $depot_name, $search ) !== false );
				}
			);

			foreach ( $filtered_depots as $depot ) {
				$results[] = [
					'id'       => $depot['Suburb'],
					'text'     => "{$depot['Suburb']} ({$depot['DepotName']}, {$depot['State']}, {$depot['Postcode']})",
					'state'    => $depot['State'],
					'postcode' => $depot['Postcode'],
				];
			}
		}

		$response = [
			'results'    => $results,
			'pagination' => [
				'more' => false,
			],
		];

		wp_send_json( $response );
	}

	/**
	 * [fetch_info description]
	 * @return [type] [description]
	 */
	public function fetch_info() {
		$_rates = [
			'accountcode'      => 'RP5700',
			'ServiceType'      => 'standard',
			'PickupType'       => 'Customer',
			'PickupSuburb'     => 'NORTH LAKES',
			'PickupPostcode'   => '4509',
			'PickupState'      => 'QLD',
			'DeliveryType'     => 'Customer',
			'DeliverySuburb'   => 'PORT MELBOUNRE(CUST)',
			'DeliveryPostcode' => '3207',
			'DeliveryState'    => 'VIC',
			'IsDriveable'      => true,
			'IsDamaged'        => false,
			'IsModified'       => false,
			'Make'             => 'TOYOTA',
			'Model'            => '4 RUNNER',
			'BodyType'         => '4WD WAGON',
			'VehicleValue'     => 50000,
		];

		$access_token = formidable_booking()->api->fetch_access_token();
		$rates        = [];
		// $rates        = formidable_booking()->api->fetch_rates( $_rates );

		wp_send_json(
			[
				$access_token,
				// $rates,
				$models,
				$body_types,
			]
		);
	}

	public function track() {
		if ( ! isset( $_GET['booking_number'] ) ) {
			$error = new WP_Error( 'invalid_booking_number', 'Invalid tracking number.' );
			wp_send_json_error( $error );
		}

		$booking_number = absint( $_GET['booking_number'] );
		$response       = formidable_booking()->api->track( $booking_number );

		wp_send_json_success(
			[
				'response' => $response,
				'html'     => display_tracking_results( $response ),
			]
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $results
	 * @return void
	 */
	public function prepare_select2_response( $items ) {
		$results = [];

		foreach ( $items as $item ) {
			$results[] = [
				'id'   => $item,
				'text' => $item,
			];
		}

		return [
			'results'    => $results,
			'pagination' => [
				'more' => false,
			],
		];
	}
}
