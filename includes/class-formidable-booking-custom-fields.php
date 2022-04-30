<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use FrmAppHelper;
use FrmProFormsHelper;
use FrmForm;
use FrmField;

/**
 * Formidable_Booking_Custom_Fields class.
 * @var [type]
 */
class Formidable_Booking_Custom_Fields {
	/**
	 * The single instance of the class.
	 * @var [type]
	 */
	protected static $_instance = null;

	/**
	 * Main Formidable_Booking_Custom_Fields instance.
	 * Ensures only one instance of Formidable_Booking_Custom_Fields is loaded or can be loaded.
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
	 * Formidable_Booking_Custom_Fields constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Init hooks.
	 * @return [type] [description]
	 */
	private function hooks() {
		add_filter( 'frm_available_fields', [ $this, 'update_available_fields' ] );
		add_action( 'frm_display_added_fields', [ $this, 'show_the_admin_field' ] );
		add_action( 'frm_form_fields', [ $this, 'show_front_field' ], 10, 3 );
	}

	/**
	 * [update_available_fields description]
	 * @param  [type] $fields [description]
	 * @return [type]         [description]
	 */
	public function update_available_fields( $fields ) {
		$fields['booking-preview'] = array(
			'name' => __( 'Booking Preview', 'formidable-booking' ),
			'icon' => 'frm_icon_font frm_pencil_icon',
		);

		return $fields;
	}

	/**
	 * [show_the_admin_field description]
	 * @param  [type] $field [description]
	 * @return [type]        [description]
	 */
	public function show_the_admin_field( $field ) {
		if ( 'booking-preview' !== $field['type'] ) {
			return;
		}
		$field_name = 'item_meta[' . $field['id'] . ']';
		?>
		<div class="frm_html_field_placeholder">
			<div class="howto frm_html_field">This is a placeholder for your Booking Preview field. <br/>View your form to see it in action.</div>
		</div>
		<?php
	}

	/**
	 * [show_front_field description]
	 * @param  [type] $field      [description]
	 * @param  [type] $field_name [description]
	 * @param  [type] $atts       [description]
	 * @return [type]             [description]
	 */
	public function show_front_field( $field, $field_name, $atts ) {
		if ( 'booking-preview' !== $field['type'] ) {
			return;
		}

		$field['value'] = current_time( 'mysql' );

		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
		if ( FrmAppHelper::is_admin() || empty( $_POST ) || empty( $form_id ) || ! isset( $_POST['item_key'] ) ) {
			return;
		}

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

		if ( $is_booking_form && ( 3 === $page_number ) ) {
			$fields = [
				'pickup'   => [
					'title'  => __( 'Pickup Address', 'formidable-booking' ),
					'fields' => [
						'pickupsuburb'   => __( 'Suburb', 'formidable-booking' ),
						'pickupstate'    => __( 'State', 'formidable-booking' ),
						'pickuppostcode' => __( 'PostCode', 'formidable-booking' ),
					],
				],
				'delivery' => [
					'title'  => __( 'Delivery Address', 'formidable-booking' ),
					'fields' => [
						'deliverysuburb'   => __( 'Suburb', 'formidable-booking' ),
						'deliverystate'    => __( 'State', 'formidable-booking' ),
						'deliverypostcode' => __( 'PostCode', 'formidable-booking' ),
					],
				],
				'vehicle'  => [
					'title'  => __( 'Vehicle', 'formidable-booking' ),
					'fields' => [
						'vehiclevalue' => __( 'VehicleValue', 'formidable-booking' ),
						'make'         => __( 'Make', 'formidable-booking' ),
						'model'        => __( 'Model', 'formidable-booking' ),
						'bodytype'     => __( 'BodyType', 'formidable-booking' ),
					],
				],
				'rates'    => [
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
								$form_field       = formidable_booking()->get_field_by_key( $form_fields, $sub_field_key );
								$form_field_id    = $form_field->id;
								$form_field_value = $_POST['item_meta'][ $form_field_id ];
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
		?>
		<input type="hidden" id="<?php echo esc_attr( $atts['html_id'] ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" />
		<?php
	}
}
