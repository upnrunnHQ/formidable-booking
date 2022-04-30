<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Formidable_Booking_Shortcodes class.
 * @var [type]
 */
class Formidable_Booking_Shortcodes {
	/**
	 * The single instance of the class.
	 * @var [type]
	 */
	protected static $_instance = null;

	/**
	 * Main Formidable_Booking_Shortcodes instance.
	 * Ensures only one instance of Formidable_Booking_Shortcodes is loaded or can be loaded.
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
	 * Formidable_Booking_Shortcodes constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'add_shortcodes' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function add_shortcodes() {
		add_shortcode( 'ceva-track', [ $this, 'ceva_track_html' ] );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function ceva_track_html() {
		wp_enqueue_script( 'formidable-booking-track' );
		wp_enqueue_style( 'formidable-booking-track' );

		\ob_start();
		?>
		<div class="ceva-track">
			<form id="ceva-track">
				<input type="number" id="booking-number" name="booking-number" placeholder="Enter booking number">	
				<input type="submit" id="booking-submit" value="Track">
			</form>
			<div id="tracking-results">
				<?php echo display_tracking_results(); ?>
			</div>
		</div>
		<?php
		return \ob_get_clean();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$asset_file = include( plugin_dir_path( FORMIDABLE_BOOKING_FILE ) . 'build/tracking.asset.php' );

		wp_register_script(
			'formidable-booking-track',
			plugins_url( 'build/tracking.js', FORMIDABLE_BOOKING_FILE ),
			$asset_file['dependencies'],
			$asset_file['version']
		);

		wp_localize_script(
			'formidable-booking-track',
			'formidable_booking_track',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_register_style(
			'formidable-booking-track',
			plugins_url( 'build/tracking.css', FORMIDABLE_BOOKING_FILE ),
			[],
			$asset_file['version']
		);
	}
}
