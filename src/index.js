import "jquery";
import "./scss/index.scss";

(function ($) {
	"use strict";

	$(document).ready(function () {
		const pickupTypeField = $("select[id^=field_pickuptype]");
		const pickupSuburbField = $("select[id^=field_pickupsuburb]");
		const pickupStateField = $("input[id^=field_pickupstate]");
		const pickupPostcodeField = $("input[id^=field_pickuppostcode]");
		const deliveryTypeField = $("select[id^=field_deliverytype]");
		const deliverySuburbField = $("select[id^=field_deliverysuburb]");
		const deliveryStateField = $("input[id^=field_deliverystate]");
		const deliveryPostcodeField = $("input[id^=field_deliverypostcode]");
		const makeField = $("select[id^=field_make]");
		const modelField = $("select[id^=field_model]");
		const yearField = $("select[id^=field_year]");
		const badgeField = $("select[id^=field_badge]");
		const bodyTypeField = $("select[id^=field_bodytype]");
		const transitDaysField = $("input[id^=field_transitdays]");
		const totalRateIncludingGstField = $(
			"input[id^=field_totalrateincludinggst]"
		);

		if (typeof formidable_booking_rates !== "undefined") {
			if ("2" === formidable_booking_rates.pageNumber) {
				const additionalFields = [
					"engine_size_cc",
					"engine_size_litres",
					"cylinders",
					"co2_emissions_combined_g_km",
					"tare_mass_kg",
					"kerb_weight_kg",
					"gross_vehicle_mass_kg",
				];

				additionalFields.forEach((additionalField) => {
					const el = $(`input[id^=field_${additionalField}]`);

					if (
						el.length &&
						typeof formidable_booking_rates.spreadsheet_item[
							additionalField
						] !== "undefined"
					) {
						el.val(
							formidable_booking_rates.spreadsheet_item[
								additionalField
							]
						);
					}
				});

				if (transitDaysField.length) {
					transitDaysField.val(formidable_booking_rates.transitDays);
					transitDaysField.trigger("change");
				}

				if (totalRateIncludingGstField.length) {
					totalRateIncludingGstField.val(
						formidable_booking_rates.totalRateIncludingGst
					);
					totalRateIncludingGstField.trigger("change");
				}
			}
		}

		if (
			pickupSuburbField.length &&
			pickupSuburbField.attr("type") !== "hidden"
		) {
			const pickupTypeFieldValue = pickupTypeField.val();
			const pickupStateFieldValue = pickupStateField.val();
			const pickupPostcodeFieldValue = pickupPostcodeField.val();
			const pickupSuburbFieldArgs = {
				ajax: {
					url: formidable_booking.ajaxUrl,
					data: function (params) {
						const query = {
							search: params.term,
							action:
								"depot" === pickupTypeField.val()
									? "fetch_ceva_depots"
									: "fetch_ceva_suburbs",
						};
						return query;
					},
				},
				minimumInputLength: 1,
			};

			pickupSuburbField.select2();

			if (["customer", "depot"].includes(pickupTypeFieldValue)) {
				pickupSuburbField.select2("destroy");
				pickupSuburbField.select2(pickupSuburbFieldArgs);

				const data = {
					id: formidable_booking_rates.pickupsuburb,
					text: `${formidable_booking_rates.pickupsuburb}, ${pickupStateFieldValue}, ${pickupPostcodeFieldValue}`,
					state: pickupStateFieldValue,
					postcode: pickupPostcodeFieldValue,
				};

				const newOption = new Option(data.text, data.id, false, true);
				pickupSuburbField.append(newOption).trigger("change");
			}

			pickupTypeField.on("change", function () {
				pickupSuburbField.select2("destroy");
				pickupSuburbField.val(null).trigger("change");
				pickupStateField.val("").trigger("change");
				pickupPostcodeField.val("").trigger("change");
				pickupSuburbField.select2(pickupSuburbFieldArgs);
			});

			pickupSuburbField.on("select2:select", function (e) {
				const data = e.params.data;
				pickupStateField.val(data.state).trigger("change");
				pickupPostcodeField.val(data.postcode).trigger("change");
			});
		}

		if (
			deliverySuburbField.length &&
			deliverySuburbField.attr("type") !== "hidden"
		) {
			const deliveryTypeFieldValue = deliveryTypeField.val();
			const deliveryStateFieldValue = deliveryStateField.val();
			const deliveryPostcodeFieldValue = deliveryPostcodeField.val();
			const deliverySuburbFieldArgs = {
				ajax: {
					url: formidable_booking.ajaxUrl,
					data: function (params) {
						const query = {
							search: params.term,
							action:
								"depot" === deliveryTypeField.val()
									? "fetch_ceva_depots"
									: "fetch_ceva_suburbs",
						};

						return query;
					},
				},
				minimumInputLength: 1,
			};

			deliverySuburbField.select2();

			if (["customer", "depot"].includes(deliveryTypeFieldValue)) {
				deliverySuburbField.select2("destroy");
				deliverySuburbField.select2(deliverySuburbFieldArgs);

				const data = {
					id: formidable_booking_rates.deliverysuburb,
					text: `${formidable_booking_rates.deliverysuburb}, ${deliveryStateFieldValue}, ${deliveryPostcodeFieldValue}`,
					state: deliveryStateFieldValue,
					postcode: deliveryPostcodeFieldValue,
				};

				const newOption = new Option(data.text, data.id, false, true);
				deliverySuburbField.append(newOption).trigger("change");
			}

			deliveryTypeField.on("change", function () {
				deliverySuburbField.select2("destroy");
				deliverySuburbField.val(null).trigger("change");
				deliveryStateField.val("").trigger("change");
				deliveryPostcodeField.val("").trigger("change");
				deliverySuburbField.select2(deliverySuburbFieldArgs);
			});

			deliverySuburbField.on("select2:select", function (e) {
				const data = e.params.data;
				deliveryStateField.val(data.state).trigger("change");
				deliveryPostcodeField.val(data.postcode).trigger("change");
			});
		}

		if (makeField.length && makeField.attr("type") !== "hidden") {
			const data = {
				action: "fetch_ceva_makes",
			};

			makeField.select2();
			modelField.select2();
			yearField.select2();
			badgeField.select2();
			bodyTypeField.select2();

			if (typeof formidable_booking_rates !== "undefined") {
				const makeFieldOption = new Option(
					formidable_booking_rates.make,
					formidable_booking_rates.make,
					false,
					true
				);
				const modelFieldOption = new Option(
					formidable_booking_rates.model,
					formidable_booking_rates.model,
					false,
					true
				);
				const yearFieldOption = new Option(
					formidable_booking_rates.year,
					formidable_booking_rates.year,
					false,
					true
				);
				const badgeFieldOption = new Option(
					formidable_booking_rates.badge,
					formidable_booking_rates.badge,
					false,
					true
				);
				const bodyTypeFieldOption = new Option(
					formidable_booking_rates.body,
					formidable_booking_rates.body,
					false,
					true
				);

				makeField.append(makeFieldOption).trigger("change");
				modelField.append(modelFieldOption).trigger("change");
				yearField.append(yearFieldOption).trigger("change");
				badgeField.append(badgeFieldOption).trigger("change");
				bodyTypeField.append(bodyTypeFieldOption).trigger("change");
			}

			$.post(formidable_booking.ajaxUrl, data, function (response) {
				makeField.select2("destroy");
				makeField.select2({ data: response.results });
			});

			makeField.on("select2:select", function (e) {
				const data = {
					action: "fetch_ceva_models",
					make: e.params.data.id,
				};

				modelField.select2("destroy");
				modelField.empty();
				modelField.prop('disabled', 'disabled');

				$.post(formidable_booking.ajaxUrl, data, function (response) {
					modelField.prop('disabled', false);
					modelField.select2({
						data: [
							{
								id: "",
								text: "Select model",
							},
							...response.results,
						],
					});
					modelField.val(null).trigger("change");
				});
			});

			modelField.on("select2:select", function (e) {
				const data = {
					action: "fetch_ceva_years",
					make: makeField.val(),
					model: e.params.data.id,
				};

				yearField.select2("destroy");
				yearField.empty();
				yearField.prop('disabled', 'disabled');

				$.post(formidable_booking.ajaxUrl, data, function (response) {
					yearField.prop('disabled', false);
					yearField.select2({
						data: [
							{
								id: "",
								text: "Select year",
							},
							...response.results,
						],
					});
					yearField.val(null).trigger("change");
				});
			});

			yearField.on("select2:select", function (e) {
				const data = {
					action: "fetch_ceva_badges",
					make: makeField.val(),
					model: modelField.val(),
					year: e.params.data.id,
				};

				badgeField.select2("destroy");
				badgeField.empty();
				badgeField.prop('disabled', 'disabled');

				$.post(formidable_booking.ajaxUrl, data, function (response) {
					badgeField.prop('disabled', false);
					badgeField.select2({
						data: [
							{
								id: "",
								text: "Select badge",
							},
							...response.results,
						],
					});
					badgeField.val(null).trigger("change");
				});
			});

			badgeField.on("select2:select", function (e) {
				const data = {
					action: "fetch_ceva_bodytypes",
					make: makeField.val(),
					model: modelField.val(),
					year: yearField.val(),
					badge: e.params.data.id,
				};

				bodyTypeField.select2("destroy");
				bodyTypeField.empty();
				bodyTypeField.prop('disabled', 'disabled');

				$.post(formidable_booking.ajaxUrl, data, function (response) {
					bodyTypeField.prop('disabled', false);
					bodyTypeField.select2({
						data: [
							{
								id: "",
								text: "Select body type",
							},
							...response.results,
						],
					});
					bodyTypeField.val(null).trigger("change");
				});
			});
		}
	});
})(jQuery);
