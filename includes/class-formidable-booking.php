<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use FrmAppHelper;
use FrmProFormsHelper;
use FrmForm;
use FrmField;
use FrmEntry;

/**
 * Formidable_Booking class.
 * @var [type]
 */
final class Formidable_Booking {
	/**
	 * The single instance of the class.
	 * @var [type]
	 */
	protected static $_instance = null;

	/**
	 * Main Formidable_Booking instance.
	 * Ensures only one instance of Formidable_Booking is loaded or can be loaded.
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
	 * Formidable_Booking constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * Include required files used in admin and on the frontend.
	 * @return [type] [description]
	 */
	private function includes() {
		require plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . '/includes/template-functions.php';
		require plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . '/includes/class-formidable-booking-api.php';
		require plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . '/includes/class-formidable-booking-ajax.php';
		require plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . '/includes/class-formidable-booking-custom-fields.php';
		require plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . '/includes/class-formidable-booking-shortcodes.php';
		require plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . '/includes/class-formidable-booking-spreadsheet.php';
		require plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . '/includes/class-formidable-booking-logger.php';
		require plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . '/includes/class-formidable-booking-settings.php';
	}

	/**
	 * Init hooks.
	 * @return [type] [description]
	 */
	private function hooks() {
		// add_action( 'frm_enqueue_form_scripts', [ $this, 'enqueue_form_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_form_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'get_rates' ] );
		add_action( 'frm_after_entry_processed', [ $this, 'display_entry_preview' ], 10 );

		// Classes.
		$this->logger        = new Formidable_Booking_Logger();
		$this->spreadsheet   = new Formidable_Booking_Spreadsheet();
		$this->api           = Formidable_Booking_API::instance();
		$this->ajax          = Formidable_Booking_Ajax::instance();
		$this->custom_fields = Formidable_Booking_Custom_Fields::instance();
		$this->shortcodes    = Formidable_Booking_Shortcodes::instance();

		if ( is_admin() ) {
			$this->settings = new Formidable_Booking_Settings();
		}
	}

	/**
	 * Enqueue scripts.
	 * @return [type] [description]
	 */
	public function enqueue_form_scripts() {
		$asset_file = include plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . 'build/index.asset.php';

		wp_register_script(
			'select2',
			'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
			[ 'jquery' ],
			'4.1.0-rc.0'
		);

		wp_register_script(
			'formidable-booking',
			plugins_url( 'build/index.js', FORMIDABLE_BOOKING_FILE ),
			$asset_file['dependencies'],
			$asset_file['version']
		);

		wp_localize_script(
			'formidable-booking',
			'formidable_booking',
			[ 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ]
		);

		wp_register_style(
			'select2',
			'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
			[],
			'4.1.0-rc.0'
		);

		wp_register_style(
			'formidable-booking',
			plugins_url( 'build/index.css', FORMIDABLE_BOOKING_FILE ),
			[],
			$asset_file['version']
		);

		// FrmProFormsHelper::get_the_page_number( $form->id )
		// public static function process_entry

		wp_enqueue_style( 'select2' );
		wp_enqueue_style( 'formidable-booking' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'formidable-booking' );
	}

	/**
	 * [get_rates description]
	 * @return [type] [description]
	 */
	public function get_rates() {
		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
		if ( FrmAppHelper::is_admin() || empty( $_POST ) || empty( $form_id ) || ! isset( $_POST['item_key'] ) ) {
			return;
		}

		global $frm_vars;

		$form = FrmForm::getOne( $form_id );
		if ( ! $form ) {
			return;
		}

		$page_number = FrmProFormsHelper::get_the_page_number( $form_id );
		$form_fields = FrmField::get_all_for_form( $form_id );

		$is_booking_form = false;
		foreach ( $form_fields as $form_field ) {
			if ( ( 'accountcode' === $form_field->field_key ) || ! ( false === strpos( $form_field->field_key, 'accountcode' ) ) ) {
				$is_booking_form = true;
			}
		}

		$formidable_booking_rates = [
			'pageNumber'            => $page_number,
			'transitDays'           => 0,
			'totalRateIncludingGst' => 0,
		];

		if ( $is_booking_form ) {
			foreach ( [
				'pickupsuburb',
				'deliverysuburb',
				'make',
				'model',
				'year',
				'badge',
				'body',
			] as $field_key ) {
				$form_field                             = formidable_booking()->get_field_by_key( $form_fields, $field_key );
				$formidable_booking_rates[ $field_key ] = $_POST['item_meta'][ $form_field->id ];
			}
		}

		if ( $is_booking_form && ( 2 === $page_number ) ) {
			$postfields = [
				'AccountCode'      => '',
				'BodyType'         => '',
				'DeliveryPostcode' => '',
				'DeliveryState'    => '',
				'DeliverySuburb'   => '',
				'IsDamaged'        => false,
				'IsDriveable'      => true,
				'IsModified'       => false,
				'Make'             => '',
				'Model'            => '',
				'PickupPostcode'   => '',
				'PickupState'      => '',
				'PickupSuburb'     => '',
				'VehicleValue'     => 0,
				'ServiceType'      => '',
				'PickupType'       => '',
				'DeliveryType'     => '',
			];

			foreach ( $postfields as $field_key => $default_field_value ) {
				$field       = formidable_booking()->get_field_by_key( $form_fields, strtolower( $field_key ) );
				$field_id    = $field->id;
				$field_value = $_POST['item_meta'][ $field_id ];

				if ( in_array( $field_key, [ 'IsDamaged', 'IsDriveable', 'IsModified' ], true ) ) {
					$field_value = 'Yes' === $_POST['item_meta'][ $field_id ] ? true : false;
				}

				$postfields[ $field_key ] = $field_value;
			}

			$rates = formidable_booking()->api->fetch_rates( $postfields );

			if ( isset( $rates['Vehicle'], $rates['Vehicle']['Rates'] ) ) {
				$rate = reset( $rates['Vehicle']['Rates'] );

				$formidable_booking_rates['transitDays']           = $rate['TransitDays'];
				$formidable_booking_rates['totalRateIncludingGst'] = $rate['TotalRateIncludingGst'];
			}

			$filters = [
				'make'  => '',
				'model' => '',
				'year'  => '',
				'badge' => '',
				'body'  => '',
			];
			foreach ( $filters as $field_key => $default_field_value ) {
				$field                 = formidable_booking()->get_field_by_key( $form_fields, strtolower( $field_key ) );
				$field_id              = $field->id;
				$filters[ $field_key ] = $_POST['item_meta'][ $field_id ];
			}

			$spreadsheet_items                            = formidable_booking()->api->filter_spreadsheet_items( $filters );
			$formidable_booking_rates['spreadsheet_item'] = reset( $spreadsheet_items );
		}

		wp_localize_script( 'formidable-booking', 'formidable_booking_rates', $formidable_booking_rates );
	}

	/**
	 * [display_entry_preview description]
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public function display_entry_preview( $args ) {
		global $frm_vars;

		// $entry = FrmEntry::getOne( $entry_id );
		// print_r( FrmEntry::get_meta($entry ));
		// print_r( $frm_vars );

		$form_fields     = FrmField::get_all_for_form( $args['form']->id );
		$is_booking_form = false;
		foreach ( $form_fields as $form_field ) {
			if ( ( 'accountcode' === $form_field->field_key ) || ! ( false === strpos( $form_field->field_key, 'accountcode' ) ) ) {
				$is_booking_form = true;
			}
		}

		if ( ! $is_booking_form ) {
			return;
		}

		$fields = [
			'information' => [
				'title'  => __( 'Booking Information', 'formidable-booking' ),
				'fields' => [
					'entry_id'     => __( 'ID', 'formidable-booking' ),
					'emailaddress' => __( 'Email', 'formidable-booking' ),
				],
			],
			'pickup'      => [
				'title'  => __( 'Pickup Address', 'formidable-booking' ),
				'fields' => [
					'pickupsuburb'    => __( 'Suburb', 'formidable-booking' ),
					'pickupstate'     => __( 'State', 'formidable-booking' ),
					'pickuppostcode%' => __( 'PostCode', 'formidable-booking' ),
				],
			],
			'delivery'    => [
				'title'  => __( 'Delivery Address', 'formidable-booking' ),
				'fields' => [
					'deliverysuburb'   => __( 'Suburb', 'formidable-booking' ),
					'deliverystate'    => __( 'State', 'formidable-booking' ),
					'deliverypostcode' => __( 'PostCode', 'formidable-booking' ),
				],
			],
			'vehicle'     => [
				'title'  => __( 'Vehicle', 'formidable-booking' ),
				'fields' => [
					'vehiclevalue' => __( 'VehicleValue', 'formidable-booking' ),
					'make'         => __( 'Make', 'formidable-booking' ),
					'model'        => __( 'Model', 'formidable-booking' ),
					'bodytype'     => __( 'BodyType', 'formidable-booking' ),
				],
			],
			'rates'       => [
				'title'  => __( 'Rates', 'formidable-booking' ),
				'fields' => [
					'transitdays'           => __( 'TransitDays', 'formidable-booking' ),
					'totalrateincludinggst' => __( 'TotalRateIncludingGst', 'formidable-booking' ),
				],
			],
		];
		?>
		<div class="booking-preview">
			<?php foreach ( $fields as $field_key => $field ) : ?>
				<div class="booking-preview__item">
					<h4><?php echo $field['title']; ?></h4>
					<ul>
						<?php foreach ( $field['fields'] as $sub_field_key => $sub_field ) : ?>
							<?php
							if ( 'entry_id' === $sub_field_key ) {
								$form_field_value = $frm_vars['created_entries'][ $args['form']->id ][ $sub_field_key ];
							} else {
								$form_field       = formidable_booking()->get_field_by_key( $form_fields, $sub_field_key );
								$form_field_id    = $form_field->id;
								$form_field_value = $_POST['item_meta'][ $form_field_id ];
							}
							?>
							<li>
								<span class="label"><?php echo $sub_field; ?></span>
								<span class="value"><?php echo $form_field_value; ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public function get_field_by_key( $form_fields, $sub_field_key ) {
		$field = false;
		foreach ( $form_fields as $form_field ) {
			if ( ( $sub_field_key === $form_field->field_key ) || ! ( false === strpos( $form_field->field_key, $sub_field_key ) ) ) {
				$field = $form_field;
			}
		}

		return $field;
	}
}
