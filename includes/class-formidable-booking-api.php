<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Formidable_Booking_API class.
 * @var [type]
 */
class Formidable_Booking_API {
	/**
	 * The single instance of the class.
	 * @var [type]
	 */
	protected static $_instance = null;

	/**
	 * Main Formidable_Booking_API instance.
	 * Ensures only one instance of Formidable_Booking_API is loaded or can be loaded.
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
	 * Formidable_Booking_API constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Init hooks.
	 * @return [type] [description]
	 */
	private function hooks() {
		add_action( 'init', [ $this, 'fetch_api_documentation' ] );
		add_action( 'init', [ $this, 'fetch_access_token' ] );
	}

	/**
	 * [fetch_api_documentation description]
	 * @return [type] [description]
	 */
	public function fetch_api_documentation() {
		if ( isset( $_GET['fetch_api_documentation'] ) ) {
			$response = wp_remote_get( 'https://factswsuat.au.cevalogistics.com/carCarrying/generic/documentation' );

			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$headers = $response['headers']; // array of http header lines
				$body    = $response['body']; // use the content
				echo $body;
				die;
			}

			$this->fetch_rates();
			die;
		}
	}

	/**
	 * [fetch_access_token description]
	 * @return [type] [description]
	 */
	public function fetch_access_token() {
		$access_token = get_transient( 'ceva_logistics_access_token' );
		if ( $access_token ) {
			if ( $this->is_api_token_valid( $access_token ) ) {
				return $access_token;
			}
		}

		$curl = curl_init();

		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => 'https://factswsuat.au.cevalogistics.com/carCarrying/generic/api/auth/login',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_POSTFIELDS     => '{
					"Password": ")ek2c=S>",
					"Username": "INTRAF1"
				}',
				CURLOPT_HTTPHEADER     => array(
					'Content-Type: application/json',
				),
			)
		);

		$response = curl_exec( $curl );

		curl_close( $curl );

		$decoded_response = json_decode( $response, true );
		if ( isset( $decoded_response['Status'], $decoded_response['Status']['Code'], $decoded_response['Data'], $decoded_response['Data']['AccessToken'] ) && ( 200 === $decoded_response['Status']['Code'] ) ) {
			$access_token = $decoded_response['Data']['AccessToken'];
			set_transient( 'ceva_logistics_access_token', $access_token, 8 * HOUR_IN_SECONDS );
			return $access_token;
		}

		return false;
	}

	/**
	 * [fetch_rates description]
	 * @return [type] [description]
	 */
	public function fetch_rates( $postfields = [] ) {
		$access_token = $this->fetch_access_token();
		$curl         = curl_init();

		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => 'https://factswsuat.au.cevalogistics.com/carCarrying/generic/api/rates/list',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_POSTFIELDS     => json_encode( $postfields ),
				CURLOPT_HTTPHEADER     => array(
					'Content-Type: application/json',
					'Authorization: Bearer ' . $access_token,
				),
			)
		);

		$response = curl_exec( $curl );

		curl_close( $curl );

		$decoded_response = json_decode( $response, true );
		if ( isset( $decoded_response['Status'], $decoded_response['Status']['Code'], $decoded_response['Data'] ) && ( 200 === $decoded_response['Status']['Code'] ) ) {
			return $decoded_response['Data'];
		}

		return [];
	}

	/**
	 * [fetch_suburbs description]
	 * @param  string $suburb_name [description]
	 * @return [type]              [description]
	 */
	public function fetch_suburbs( $suburb_name = 'north sh' ) {
		$suburbs = get_transient( 'ceva_logistics_suburbs_' . $suburb_name );
		if ( $suburbs ) {
			return $suburbs;
		}

		$access_token = $this->fetch_access_token();
		$response     = $this->get_api_response(
			[
				'url'    => 'https://factswsuat.au.cevalogistics.com/carCarrying/generic/api/suburbs/list/' . $suburb_name,
				'method' => 'GET',
			],
			$access_token
		);

		$decoded_response = json_decode( $response, true );
		if ( isset( $decoded_response['Status'], $decoded_response['Status']['Code'], $decoded_response['Data'] ) && ( 200 === $decoded_response['Status']['Code'] ) ) {
			$suburbs = $decoded_response['Data'];
			set_transient( 'ceva_logistics_suburbs_' . $suburb_name, $suburbs, 8 * HOUR_IN_SECONDS );
			return $suburbs;
		}

		return [];
	}

	/**
	 * Undocumented function
	 *
	 * @param array $filters
	 * @return void
	 */
	public function filter_spreadsheet_items( $filters = [] ) {
		$spreadsheet = get_option( 'ceva_booking_spreadsheet' );
		if ( ! file_exists( $spreadsheet ) ) {
			return [];
		}

		$items = formidable_booking()->spreadsheet->parse_spreadsheet( $spreadsheet );
		$items = wp_list_filter( $items, $filters );

		return $items;
	}

	/**
	 * [fetch_depots description]
	 * @return [type] [description]
	 */
	public function fetch_depots() {
		$depots = get_transient( 'ceva_logistics_depots' );
		if ( $depots ) {
			return $depots;
		}

		$access_token = $this->fetch_access_token();
		$response     = $this->get_api_response(
			[
				'url'    => 'https://factswsuat.au.cevalogistics.com/carCarrying/generic/api/depots/list',
				'method' => 'GET',
			],
			$access_token
		);

		$decoded_response = json_decode( $response, true );
		if ( isset( $decoded_response['Status'], $decoded_response['Status']['Code'], $decoded_response['Data'] ) && ( 200 === $decoded_response['Status']['Code'] ) ) {
			$depots = $decoded_response['Data'];
			set_transient( 'ceva_logistics_depots', $depots, 8 * HOUR_IN_SECONDS );
			return $depots;
		}

		return [];
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function track( $booking_number ) {
		$access_token = $this->fetch_access_token();
		$response     = $this->get_api_response(
			[
				'url'    => 'https://factsws.au.cevalogistics.com/carCarrying/Generic/api/tracking/track?bookingNumber=' . $booking_number,
				'method' => 'GET',
			],
			$access_token
		);

		$decoded_response = json_decode( $response, true );
		if ( isset( $decoded_response['Status'], $decoded_response['Status']['Code'], $decoded_response['Data'] ) && ( 200 === $decoded_response['Status']['Code'] ) ) {
			return $decoded_response['Data'];
		}

		return [];
	}

	/**
	 * [get_api_response description]
	 * @param  array  $args         [description]
	 * @param  string $access_token [description]
	 * @return [type]               [description]
	 */
	public function get_api_response( $args = [], $access_token = '' ) {
		$curl = curl_init();

		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => esc_url( $args['url'] ),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => $args['method'],
				CURLOPT_HTTPHEADER     => array(
					'Authorization: Bearer ' . $access_token,
				),
			)
		);

		$response = curl_exec( $curl );

		curl_close( $curl );

		return $response;
	}

	/**
	 * [is_api_token_valid description]
	 * @param  string  $token [description]
	 * @return boolean        [description]
	 */
	public function is_api_token_valid( $token = '' ) {
		if ( '' === $token ) {
			return false;
		}

		$response = $this->get_api_response(
			[
				'url'    => 'https://factswsuat.au.cevalogistics.com/carCarrying/generic/api/depots/list',
				'method' => 'GET',
			],
			$token
		);

		$decoded_response = json_decode( $response, true );
		if ( isset( $decoded_response['Status'], $decoded_response['Status']['Code'], $decoded_response['Data'] ) && ( 200 === $decoded_response['Status']['Code'] ) ) {
			if ( count( $decoded_response['Data'] ) ) {
				return true;
			}
		}

		return false;
	}
}
