<?php
namespace Upnrunn;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function display_tracking_results( $data = [] ) {
	$fields = [
		'BookingNumber'  => __( 'Booking Number', 'formidable-booking' ),
		'BookingDate'    => __( 'Booking Date', 'formidable-booking' ),
		'PickupSuburb'   => __( 'Pickup Suburb', 'formidable-booking' ),
		'PickupState'    => __( 'Pickup State', 'formidable-booking' ),
		'DeliverySuburb' => __( 'Delivery Suburb', 'formidable-booking' ),
		'DeliveryState'  => __( 'Delivery State', 'formidable-booking' ),
		'PickupTime'     => __( 'Pickup Time', 'formidable-booking' ),
		'DeliveryTime'   => __( 'Delivery Time', 'formidable-booking' ),
	];

	$vehicle_fields = [
		'BookingVehicleNumber' => __( 'Booking Vehicle Number', 'formidable-booking' ),
		'VehicleId'            => __( 'Vehicle Id', 'formidable-booking' ),
		'Make'                 => __( 'Make', 'formidable-booking' ),
		'Model'                => __( 'Model', 'formidable-booking' ),
	];

	$movement_fields = [
		'FromLocation'   => __( 'From', 'formidable-booking' ),
		'ToLocation'     => __( 'To', 'formidable-booking' ),
		'DepartureDate'  => __( 'Departure Date', 'formidable-booking' ),
		'ArrivalDate'    => __( 'Arrival Date', 'formidable-booking' ),
		'MovementStatus' => __( 'Status', 'formidable-booking' ),
		'Mode'           => __( 'Mode', 'formidable-booking' ),
	];

	if ( ! isset( $data['Value'] ) ) {
		return __( 'No tracking information found.', 'formidable-booking' );
	}

	$data = $data['Value'];
	// $data = get_dummy_data();

	\ob_start();
	?>
	<h2><?php _e( 'Tracking Details', 'formidable-booking' ); ?></h2>
	<div class="booking-details">
		<h3><?php _e( 'Booking', 'formidable-booking' ); ?></h3>
		<ul>
			<?php foreach ( $fields as $key => $field ) : ?>
				<li>
					<span class="key"><?php echo $field; ?></span>
					<span class="value"><?php echo format_field_value( $data[ $key ], $key ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>


	<div class="vehicles">
		<h2><?php _e( 'Vehicles', 'formidable-booking' ); ?></h2>

		<?php foreach ( $data['BookingVehicles'] as $vehicle ) : ?>
			<div class="vehicle">
				<h3><?php _e( 'Vehicle', 'formidable-booking' ); ?></h3>

				<ul class="vehicle-details">
					<?php foreach ( $vehicle_fields as $key => $field ) : ?>
						<li>
							<span class="key"><?php echo $field; ?></span>
							<span class="value""><?php echo $vehicle[ $key ]; ?></span>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="vehicle-movements">
					<h3><?php _e( 'Movements', 'formidable-booking' ); ?></h3>

					<table>
						<tr>
							<?php foreach ( $movement_fields as $key => $field ) : ?>
								<th><?php echo $field; ?></th>
							<?php endforeach; ?>
						</tr>

						<?php foreach ( $vehicle['MovementDetails'] as $movement ) : ?>
							<tr>
								<?php foreach ( $movement_fields as $key => $field ) : ?>
									<td><?php echo format_field_value( $movement[ $key ], $key ); ?></td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return \ob_get_clean();
}

/**
 * Undocumented function
 *
 * @return void
 */
function get_dummy_data() {
	$dummy         = '{ "Data": { "Value": { "BookingNumber": 3006144, "BookingDate": "2022-02-14T13:50:45", "CustomerName": "KENT MOVING & STORAGE", "PickupSuburb": "BRISBANE", "PickupState": "QLD", "DeliverySuburb": "PERTH", "DeliveryState": "WA ", "PickupTime": "2022-02-16T00:00:00", "DeliveryTime": "2022-03-11T00:00:00", "RevisedDeliveryTime": "9999-12-31T23:59:59", "BookingVehicles": [ { "BookingVehicleNumber": 1, "VehicleId": "418ZHK", "Make": "MAZDA", "Model": "MAZDA3", "ServiceCode": "CEVA STANDARD", "MovementDetails": [ { "MovementLegId": 1, "PickupAlias": "BRISBANE, QLD", "DeliveryAlias": "PINKENBA (VL), QLD", "FromLocation": "BRISBANE, QLD", "ToLocation": "PINKENBA (VL), QLD", "DepartureDate": "2022-02-16T14:00:00", "ArrivalDate": "2022-02-17T05:39:29", "MovementStatus": "Completed", "Mode": "Road", "LocationStatus": "Arrived At PINKENBA (VL), QLD 17/02/2022" }, { "MovementLegId": 2, "PickupAlias": "PINKENBA (VL), QLD", "DeliveryAlias": "FISHERMAN ISLE(WHF), QLD", "FromLocation": "PINKENBA (VL), QLD", "ToLocation": "FISHERMAN ISLE(WHF), QLD", "DepartureDate": "2022-02-18T09:19:00", "ArrivalDate": "2022-02-18T10:32:00", "MovementStatus": "Completed", "Mode": "Road", "LocationStatus": "Arrived At FISHERMAN ISLE(WHF), QLD 18/02/2022" }, { "MovementLegId": 3, "PickupAlias": "FISHERMAN ISLE(WHF), QLD", "DeliveryAlias": "FREMANTLE(WHF), WA", "FromLocation": "FISHERMAN ISLE(WHF), QLD", "ToLocation": "FREMANTLE(WHF), WA", "DepartureDate": "2022-02-18T07:35:00", "ArrivalDate": "9999-12-31T23:59:59", "MovementStatus": "Planned", "Mode": "Road", "LocationStatus": "Planned To Depart FISHERMAN ISLE(WHF), QLD 18/02/2022" }, { "MovementLegId": 4, "PickupAlias": "FREMANTLE(WHF), WA", "DeliveryAlias": "PERTH, WA", "FromLocation": "FREMANTLE(WHF), WA", "ToLocation": "PERTH, WA", "DepartureDate": null, "ArrivalDate": null, "MovementStatus": "YetToMove", "Mode": "", "LocationStatus": null } ] } ] }, "ApiMessage": null }, "Status": { "Code": 200, "Name": "OK", "Timestamp": "2022-02-18T14:58:30" } }';
	$decoded_dummy = json_decode( $dummy, true );
	return $decoded_dummy['Data']['Value'];
}

function format_field_value( $field_value, $field_id ) {
	if ( in_array( $field_id, [ 'BookingDate', 'PickupTime', 'DeliveryTime' ], true ) ) {
		$date = date_create( $field_value );
		return date_format( $date, 'j F, Y' );
	}

	if ( in_array( $field_id, [ 'DepartureDate', 'ArrivalDate' ], true ) ) {
		$date = date_create( $field_value );
		return date_format( $date, 'j F, Y - H:i:s' );
	}

	return $field_value;
}
