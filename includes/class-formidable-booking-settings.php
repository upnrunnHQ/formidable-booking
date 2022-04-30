<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Formidable_Booking_Settings class.
 * @var [type]
 */
class Formidable_Booking_Settings {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	public function __construct() {
		// Set class property
		$this->options = get_option( 'ceva_booking_options' );

		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'admin_init', array( $this, 'handle_upload' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			__( 'CEVA Booking', 'formidable-booking' ),
			__( 'CEVA Booking', 'formidable-booking' ),
			'manage_options',
			'ceva-booking',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php" enctype="multipart/form-data">
			<?php
				// This prints out all hidden setting fields
				settings_fields( 'ceva_booking' );
				do_settings_sections( 'ceva-booking-admin' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		register_setting(
			'ceva_booking',
			'ceva_booking_options',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'ceva_booking_spreadsheet',
			__( 'Spreadsheet', 'formidable-booking' ),
			array( $this, 'spreadsheet_section_callback' ),
			'ceva-booking-admin'
		);

		add_settings_field(
			'spreadsheet',
			__( 'Spreadsheet File', 'formidable-booking' ),
			array( $this, 'spreadsheet_field_callback' ),
			'ceva-booking-admin',
			'ceva_booking_spreadsheet'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function handle_upload() {
		if ( ! isset( $_FILES['spreadsheet'] ) ) {
			return;
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$uploadedfile = $_FILES['spreadsheet'];
		$file_type    = strtolower( pathinfo( $uploadedfile['name'], PATHINFO_EXTENSION ) );

		if ( 'xlsx' === $file_type ) {
			$upload_overrides = array(
				'test_form' => false,
			);

			$spreadsheet = get_option( 'ceva_booking_spreadsheet' );
			if ( file_exists( $spreadsheet ) ) {
				wp_delete_file( $spreadsheet );
			}

			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

			if ( $movefile && ! isset( $movefile['error'] ) ) {
				$items = formidable_booking()->spreadsheet->parse_spreadsheet( $movefile['file'] );

				update_option(
					'ceva_booking_spreadsheet',
					$movefile['file']
				);
			}
		} else {
			add_settings_error(
				'invalid_spreadsheet',
				esc_attr( 'settings_updated' ),
				__( 'Sorry, only xlsx file is allowed.', 'formidable-booking' ),
				'error'
			);
		}
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		return $input;
	}

	/**
	 * Print the Section text
	 */
	public function spreadsheet_section_callback() {
		echo __( 'Please upload the database spreadsheet, it will overwrite the pevious one.', 'formidable-booking' );
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function spreadsheet_field_callback() {
		$spreadsheet = get_option( 'ceva_booking_spreadsheet' );
		echo '<input type="file" id="spreadsheet" name="spreadsheet" />';
		echo  basename( $spreadsheet );
	}
}
